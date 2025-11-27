<?php

namespace Fatchip\ComputopPayments\Controller;

use Exception;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Helper\Api;
use Fatchip\ComputopPayments\Helper\Config;
use Fatchip\ComputopPayments\Helper\Payment;
use Fatchip\ComputopPayments\Model\ApiLog;
use Fatchip\ComputopPayments\Model\Method\AmazonPay;
use Fatchip\ComputopPayments\Model\Method\DirectDebit;
use Fatchip\ComputopPayments\Model\Method\Easycredit;
use Fatchip\ComputopPayments\Model\Method\Klarna;
use Fatchip\ComputopPayments\Model\Method\PayPal;
use Fatchip\ComputopPayments\Model\Method\PayPalExpress;
use Fatchip\ComputopPayments\Model\Method\RedirectPayment;
use Fatchip\ComputopPayments\Repository\ApiLogRepository;
use Fatchip\CTPayment\CTAddress\CTAddress;
use Fatchip\CTPayment\CTEnums\CTEnumEasyCredit;
use Fatchip\CTPayment\CTPaymentMethods\AmazonPay as CTAmazonPay;
use Fatchip\CTPayment\CTPaymentParams;
use Fatchip\CTPayment\CTPaymentService;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTEnums\CTEnumStatus;
use Fatchip\CTPayment\CTPaymentMethodsIframe\CreditCard;

/**
 * Class OrderController
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 */
class FatchipComputopOrder extends FatchipComputopOrder_parent
{
    /** @var Logger */
    protected $fatchipComputopLogger;

    public $fatchipComputopSilentParams;

    /** @var CTPaymentService fatchipComputopPaymentService */
    protected $fatchipComputopPaymentService;

    // -----------------> START OXID CORE MODULE EXTENSIONS <-----------------
    /**
     * Loads basket \OxidEsales\Eshop\Core\Session::getBasket(), sets $this->oBasket->blCalcNeeded = true to
     * recalculate, sets back basket to session \OxidEsales\Eshop\Core\Session::setBasket(), executes
     * parent::init().
     */
    public function init()
    {
        $this->fatchipComputopLogger = new Logger();
        $this->fatchipComputopPaymentService = new CTPaymentService(Config::getInstance()->getConnectionConfig());

        parent::init();
    }

    /**
     * @param $paymentId
     * @return bool
     */
    protected function canKillSessionEarly($paymentId)
    {
        if (in_array($paymentId, [Easycredit::ID, PayPalExpress::ID])) {
            return false;
        }
        return true;
    }

    /**
     * Executes parent::render(), if basket is empty - redirects to main page
     * and exits the script (\OxidEsales\Eshop\Application\Model\Order::validateOrder()). Loads and passes payment
     * info to template engine. Refreshes basket articles info by additionally loading
     * each article object (\OxidEsales\Eshop\Application\Model\Order::getProdFromBasket()), adds customer
     * addressing/delivering data (\OxidEsales\Eshop\Application\Model\Order::getDelAddressInfo()) and delivery sets
     * info (\OxidEsales\Eshop\Application\Model\Order::getShipping()).
     *
     * @return string Returns name of template to render order::_sThisTemplate
     */
    public function render()
    {
        $paymentId = $this->getBasket()->getPaymentId();
        if (Payment::getInstance()->isComputopPaymentMethod($paymentId) === false) {
            return parent::render();
        }

        if ($this->canKillSessionEarly($paymentId) === true) {
            Registry::getSession()->handlePaymentSession();
        }

        $ctPayment = $this->computopGetPaymentModel();
        if ($ctPayment instanceof Easycredit) {
            $len = Registry::getRequest()->getRequestParameter('Len');
            $data = Registry::getRequest()->getRequestParameter('Data');
            if (!empty($len) && !empty($data)) {
                $PostRequestParams = [
                    'Len' => $len,
                    'Data' => $data,
                ];
                $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
                $this->fatchipComputopLogger->logRequestResponse($PostRequestParams, $ctPayment->getLibClassName(), 'AUTHORIZE_REQUEST', $response);

                $status = $response->getStatus();
                $this->easyCreditHandle($response);
            } else {
                $dynValue = Registry::getSession()->getVariable('dynvalue');
                if ($dynValue) {
                    $this->handleEasycreditRedirect($ctPayment->getPaymentId());
                }
            }
        }

        if ($ctPayment instanceof AmazonPay) {
            Registry::getSession()->deleteVariable("ctAmazonAuthResponse");
        }

        return parent::render();
    }

    /**
     * Checks for order rules confirmation ("ord_agb", "ord_custinfo" form values)(if no
     * rules agreed - returns to order view), loads basket contents (plus applied
     * price/amount discount if available - checks for stock, checks user data (if no
     * data is set - returns to user login page). Stores order info to database
     * (\OxidEsales\Eshop\Application\Model\Order::finalizeOrder()). According to sum for items automatically assigns
     * user to special user group ( \OxidEsales\Eshop\Application\Model\User::onOrderExecute(); if this option is not
     * disabled in admin). Finally you will be redirected to next page (order::_getNextStep()).
     *
     * @return string|null
     */
    public function execute()
    {
        $paymentId = $this->getBasket()->getPaymentId();
        if (Payment::getInstance()->isComputopPaymentMethod($paymentId) === false) {
            return parent::execute();
        }

        $ctPayment = $this->computopGetPaymentModel();

        $ret = null;

        if (($ctPayment instanceof RedirectPayment && !$ctPayment instanceof PayPalExpress)) {
            if ($ctPayment instanceof AmazonPay) {
                // FCRM_REFACTOR - directly manipulating $_POST variable is bad practise
                $_POST['stoken'] = Registry::getSession()->getSessionChallengeToken();
                $_POST['FatchipComputopLen'] = Registry::getRequest()->getRequestParameter('Len');
                $_POST['FatchipComputopData'] = Registry::getRequest()->getRequestParameter('Data');
                $_POST['sDeliveryAddressMD5'] = $this->getUser()->getEncodedDeliveryAddress();
            }

            $ret = parent::execute();

            // if order is validated and finalized complete Order on thankyou
            if ($ret === 'thankyou' || $ret === 'thankyou?mailerror=1') {
                Registry::getSession()->deleteVariable(Constants::CONTROLLER_PREFIX .'RedirectUrl');
                Registry::getSession()->deleteVariable(Constants::CONTROLLER_PREFIX .'RedirectResponse');
            }
        } else {
            if ($ctPayment instanceof PayPalExpress) {
                $ret = parent::execute();
                // if order is validated and finalized complete Order on thankyou
                if ($ret === 'thankyou' || $ret === 'thankyou?mailerror=1') {
                    /** @var CTResponse $oResponse */
                    $oResponse = Registry::getSession()->getVariable(Constants::CONTROLLER_PREFIX . 'RedirectResponse');
                    if ($oResponse) {
                        $oOrder = oxNew(Order::class);
                        if($oOrder->loadByTransID($oResponse->getTransID())){
                            // $oOrder->customizeOrdernumber($oResponse);
                            $oOrder->updateOrderAttributes($oResponse);
                            $oOrder->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSRESERVED);
                            $this->updateRefNrWithComputop($oOrder);
                            $oOrder->autoCapture($oOrder->getUser(), false);
                        }
                    }
                }
            }
            if ($ctPayment instanceof Easycredit) {
                $ret = parent::execute();
                if ($ret === 'thankyou' || $ret === 'thankyou?mailerror=1') {
                    /** @var CTResponse $oResponse */
                    $oResponse = Registry::getSession()->getVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse');
                    if ($oResponse) {
                        $params = Registry::getSession()->getVariable(Constants::CONTROLLER_PREFIX . 'DirectRequest');
                        $response =  Registry::getSession()->getVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse');
                        $this->fatchipComputopLogger->logRequestResponse($params,'EasyCredit','AUTH_ACCEPT',$response);

                        $orderId = Registry::getSession()->getVariable('sess_challenge');
                        $oOrder = oxNew(Order::class);
                        if ($oOrder->load($orderId)){
                            // $oOrder->customizeOrdernumber($oResponse);
                            $oOrder->updateOrderAttributes($oResponse);
                            $oOrder->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSRESERVED);
                            $this->updateRefNrWithComputop($oOrder);
                            $oOrder->autoCapture($oOrder->getUser(), false);
                        }
                    }
                }
            }

            // PPE and EasyCredit were excluded from handling in render() before, so do it now
            Registry::getSession()->handlePaymentSession();
        }

        // in all other cases return parent
        if (!$ret) {
            $ret = parent::execute();
        }

        if (Registry::getSession()->getVariable(Constants::CONTROLLER_PREFIX.'PpeOngoing')) {
            Registry::getSession()->deleteVariable(Constants::CONTROLLER_PREFIX.'PpeOngoing');
            Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX.'PpeFinished',1);
        }
        return $ret;
    }

    // -----------------> END OXID CORE MODULE EXTENSIONS <-----------------

    // -----------------> START CUSTOM MODULE FUNCTIONS <-----------------
    // @TODO: They ALL need a module function name prefix to not cross paths with other modules

    public function handleEasycreditRedirect($paymentId)
    {
        $ctPayment = Payment::getInstance()->getComputopPaymentModel($paymentId);

        $oUser = $this->getUser();
        $payment = $this->getPaymentClassForGatewayAction();

        $ctOrder = $this->createCTOrder();

        $payment->setBillToCustomer($ctOrder);

        $urlParams = CTPaymentParams::getUrlParams($paymentId);
        $redirectParams = $payment->getRedirectUrlParams();
        $paymentParams = $this->getPaymentParams($oUser, Registry::getSession()->getVariable('dynvalue'));
        $paymentParams['billToCustomer'] = $payment->getBillToCustomer();
        $customParam = CTPaymentParams::getCustomParam($payment->getTransID(), $paymentId);
        $params = array_merge($redirectParams, $paymentParams, $customParam, $urlParams);

        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrlRequestParams', $params);
        if (Config::getInstance()->getConfigParam('debuglog') === 'extended') {
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'Calling ' . $payment->getCTPaymentURL($params),
                [
                    'payment' => $ctPayment->getLibClassName(),
                    'UserID' => $oUser->getFieldData('oxcustnr'),
                    'SessionID' => Registry::getSession()->getId(),
                    'params' => $params
                ]
            );
        }
        $response = $payment->getHTTPGetURL($params);
        $parts = parse_url($response);
        parse_str($parts['query'], $query);
        $len = $query['Len'];
        $data = $query['Data'];
        $PostRequestParams = [
            'Len'    => $len,
            'Data'   => $data,
        ];
        $responseDec = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        $this->fatchipComputopLogger->logRequestResponse($params, $ctPayment->getLibClassName(), 'AUTHORIZE_REQUEST_REDIRECT', $responseDec);
        // $this->fatchipComputopLogger->logRequestResponse($params, $ctPayment->getLibClassName(), 'REDIRECT-STANDARD', $payment);
        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);
        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectResponse', $responseDec);

        Registry::getUtils()->redirect($response, false);
    }

    /**
     * @param CTResponse $response
     * @return void
     */
    protected function easyCreditHandle($response)
    {
        $ctPayment = $this->computopGetPaymentModel();

        $dynValue = Registry::getSession()->getVariable('dynvalue');
        $status = $response->getStatus();
        if (CTEnumStatus::AUTHORIZE_REQUEST === $status) {
            $oUser = $this->getUser();
            $UrlParams =  CTPaymentParams::getUrlParams($ctPayment->getPaymentId());
            $payment = $this->getPaymentClassForGatewayAction();
            $redirectParams = $payment->getRedirectUrlParams();
            $paymentParams = $this->getPaymentParams($oUser, $dynValue);
            $customParam = CTPaymentParams::getCustomParam($payment->getTransID(), $ctPayment->getPaymentId());
            $redirectParams = $payment->getRedirectUrlParams();
            $params = array_merge($redirectParams, $paymentParams, $customParam, $UrlParams);
            $ctOrder = $this->createCTOrder();

            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass(
                $ctPayment->getLibClassName(),
                Config::getInstance()->getConnectionConfig(),
                $ctOrder,
                '',
                '',
                '',
                'Test',
                CTPaymentParams::getUserDataParam(),
                CTEnumEasyCredit::EVENTTOKEN_GET
            );

            $oBasket = $this->getBasket();
            $oDelivery = $oBasket->getCosts('oxdelivery');
            $sDeliveryCosts = $oDelivery === null ? 0.0 : (int)($oDelivery->getBruttoPrice() * 100);

            $sDeliveryCosts = (double)str_replace(
                ',',
                '.',
                $sDeliveryCosts
            );
            $amount = ($oBasket->getBruttoSum() * 100)  + ($sDeliveryCosts);
            $amount = (int)($amount);

            $redirectResponse = Registry::getSession()->getVariable('FatchipComputopRedirectResponse');
            $decisionParams = $payment->getDecisionParams($response->getPayID(), $response->getTransID(), $amount, $oBasket->getBasketCurrency()->name);
            $mac = $redirectResponse->getMAC();
            $decisionParams['mac'] = Config::getInstance()->getConfigParam('mac');
            $responseObject = $this->callComputopService($decisionParams, $payment, 'GET', $payment->getCTCreditCheckURL());

            $decision = json_decode($responseObject->getFinancing(), true);

            if (!($decision['decision']['decisionOutcome'] === 'POSITIVE')) {
                die(var_dump($decision)); ///@TODO: Needs error handling!
            } else {
                $this->addTplParam('FatchipComputopEasyCreditInformation', $this->getConfirmPageInformation($responseObject));
                Registry::getSession()->setVariable('FatchipComputopEasyCreditInformation', $this->getConfirmPageInformation($responseObject));
                Registry::getSession()->setVariable('fatchipComputopEasyCreditPayId',$responseObject);
            }
        } else {
            die(var_dump($response)); ///@TODO: Needs error handling!
        }
    }

    public function creditCardSilent()
    {
        try {
            $ctOrder = $this->createCTOrder();
            $amount = $this->calculateTotalAmount();

            $paymentId = $this->getBasket()->getPaymentId();
            $ctPayment = Payment::getInstance()->getComputopPaymentModel($paymentId);
            $paymentClass = $ctPayment->getLibClassName();

            /** @var CreditCard $payment */
            $payment = $this->initializePayment($ctOrder, $paymentClass);
            $currency = $this->getBasket()->getBasketCurrency()->name;
            $request = $this->createAuthorizeRequest($payment, $paymentClass, $amount, $currency, $ctOrder);
            $this->fatchipComputopLogger->logRequestResponse($request, $paymentClass, 'AUTH-REQUEST', []);

            Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'DirectRequest', $request);
            $jsonEncoded = $payment->getPaynowURLasJson($request);
            echo $jsonEncoded;
            exit;
        } catch (Exception $e) {
            error_log('Error in creditCardSilent: ' . $e->getMessage());
            echo json_encode(['error' => 'An error occurred while processing the payment.']);
            exit;
        }
    }

    private function calculateTotalAmount()
    {
        try {
            $oDelivery = $this->getBasket()->getCosts('oxdelivery');
            $sDeliveryCosts = $oDelivery === null ? 0.0 : (int)($oDelivery->getBruttoPrice() * 100);
            $sDeliveryCosts = (double)str_replace(',', '.', $sDeliveryCosts);

            $totalAmount = (int)(($this->getBasket()->getBruttoSum() * 100) + $sDeliveryCosts);
            return $totalAmount;
        } catch (Exception $e) {
            throw new Exception('Error calculating total amount: ' . $e->getMessage());
        }
    }

    private function initializePayment($ctOrder, $paymentClass)
    {
        try {
            $orderDesc = '';
            if ((bool)Config::getInstance()->getConfigParam('creditCardTestMode') === true) {
                $orderDesc = 'Test:0000';
            }

            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass(
                $paymentClass,
                Config::getInstance()->getConnectionConfig(),
                $ctOrder,
                '',
                '',
                '',
                $orderDesc,
                CTPaymentParams::getUserDataParam(),
                CTEnumEasyCredit::EVENTTOKEN_GET
            );
            if ($paymentClass !=='AmazonPay') {
                $payment->setCredentialsOnFile();
            }
            return $payment;
        } catch (Exception $e) {
            throw new Exception('Error initializing payment: ' . $e->getMessage());
        }
    }

    /**
     * @param CreditCard $payment
     * @param $paymentClass
     * @param $amount
     * @param $currency
     * @param $ctOrder
     * @return array
     * @throws Exception
     */
    private function createAuthorizeRequest($payment, $paymentClass, $amount, $currency, $ctOrder)
    {
        $captureMode = 'MANUAL';
        try {
            $ctPayment = $this->computopGetPaymentModel();

            $request = $payment->getAuthorizeParams(
                $paymentClass,
                Registry::getSession()->getSessionChallengeToken(),
                $amount,
                $currency,
                $captureMode
            );

            $request = $this->addAdditionalRequestParameters($request, $payment, $ctOrder);

            $urlParams =  CTPaymentParams::getUrlParams($ctPayment->getPaymentId());
            $request = array_merge($request, $urlParams);

            return $request;
        } catch (Exception $e) {
            throw new Exception('Error creating authorize request: ' . $e->getMessage());
        }
    }

    /**
     * @param $request
     * @param $payment CreditCard
     * @param $ctOrder CTOrder
     * @return array
     */
    private function addAdditionalRequestParameters($request, $payment, $ctOrder)
    {
        $ctPayment = $this->computopGetPaymentModel();

        $payment->setBillToCustomer($ctOrder);
        $request['EtiId'] = $payment->getEtiId();
        $request['ReqId'] = $payment->getTransID();
        $request['transID'] = $payment->getTransID();
        $customParam = CTPaymentParams::getCustomParam($payment->getTransID(), $ctPayment->getPaymentId());
        $request['custom'] = $customParam['custom'];
        $request['billingAddress'] = $payment->getBillingAddress();
        $request['shippingAddress'] = $payment->getShippingAddress();
        $request['billToCustomer'] = $payment->getBillToCustomer();
        $request['msgVer'] = '2.0';
        $request['orderDesc'] = $payment->getOrderDesc();
        $request['credentialOnFile'] = $payment->getCredentialsOnFile();
        $request['template'] = 'ct_responsive';
        $request['Response'] = 'encrypt';
        return $request;
    }

    private function isAutoCaptureEnabled()
    {
        $ctPayment = $this->computopGetPaymentModel();

        $autoCaptureConfigKey = 'creditCardCaption';
        if ($ctPayment instanceof AmazonPay) {
            $autoCaptureConfigKey = 'amazonCaptureType';
        }

        $autoCaptureValue = Config::getInstance()->getConfigParam($autoCaptureConfigKey);

        return ($autoCaptureValue === 'AUTO');
    }

    public function lastschriftAction()
    {
        $ctPayment = $this->computopGetPaymentModel();

        $dynValue = Registry::getSession()->getVariable('dynvalue');
        $oUser = $this->getUser();
        $payment = $this->getPaymentClassForGatewayAction();
        $payment->setAccBank($dynValue['fatchip_computop_lastschrift_bankname']);
        $payment->setAccOwner($dynValue['fatchip_computop_lastschrift_bank_account_holder']);
        $payment->setIBAN($dynValue['fatchip_computop_lastschrift_iban']);
        $params = $payment->getRedirectUrlParams();
        $customParam = CTPaymentParams::getCustomParam($payment->getTransID(), $ctPayment->getPaymentId());
        $params['custom'] = $customParam['custom'];

        if (Config::getInstance()->getConfigParam('debuglog') === 'extended') {
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'Redirecting to ' . $payment->getCTPaymentURL($params),
                [
                    'payment' => $ctPayment->getLibClassName(),
                    'UserID' => $oUser->getId(),
                    'basket' => '',
                    'SessionID' => Registry::getSession()->getId(),
                    'params' => $params
                ]
            );
        }
        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'DirectRequest', $params);
        $response = $payment->callComputop($params, $payment->getCTPaymentURL());

        $this->fatchipComputopLogger->logRequestResponse($params, $paymentName,'AUTH', $response);
        return $response;
    }

    /**
     * @param $className
     * @return CTPaymentMethod
     */
    public function getPaymentClass($className)
    {
        $class = 'Fatchip\\CTPayment\\CTPaymentMethods\\' . $className;
        return new $class();
    }

    /**
     * TODO : move to helper or order
     * @param Basket $basket
     * @return CTOrder
     */
    protected function createCTOrder()
    {
        $ctOrder = new CTOrder();
        $oUser = $this->getUser();
        /** @var Basket $oBasket */
        $oBasket = $this->getBasket();

        $ctOrder->setAmount((int)($oBasket->getPrice()->getBruttoPrice() * 100));
        $ctOrder->setCurrency($oBasket->getBasketCurrency()->name);
        // try catch in case Address Splitter retrun exceptions
        try {
            $ctOrder->setBillingAddress($this->getCTAddress($oUser));
            $delAddressId = Registry::getSession()->getVariable('deladrid');
            if ($delAddressId) {
                $deliveryAddress = oxNew(Address::class);
                $deliveryAddress->load($delAddressId);
                $ctOrder->setShippingAddress($this->getCTAddress($oUser, $deliveryAddress));
            } else {
                $ctOrder->setShippingAddress($this->getCTAddress($oUser));
            }
        } catch (Exception $e) {
            $ctError = [];
            $ctError['CTErrorCode'] = $e->getMessage();
            return $this->forward('shippingPayment', 'checkout', null, ['CTError' => $ctError]);
        }
        $ctOrder->setEmail($oUser->oxuser__oxusername->value);
        $ctOrder->setCustomerID($oUser->oxuser__oxcustnr->value);

        $shop = Registry::getConfig()->getActiveShop();

        // Mandatory for paypalStandard
        $orderDesc = $shop->oxshops__oxname->value.' '.$shop->oxshops__oxversion->value;
        if(Config::getInstance()->getConfigParam('creditCardTestMode')) {
            $ctOrder->setOrderDesc('Test:0000');
        } else {
            $ctOrder->setOrderDesc($orderDesc);

        }
        return $ctOrder;
    }

    /**
     * creates a CTAddress object from a Oxid address array
     * @param $user
     * @return CTAddress
     * @throws \Exception
     */
    public function getCTAddress($oUser, $deliveryAdress = null)
    {
        $oxcountryid = $oUser->getFieldData('oxcountryid');
        $oCountry = oxNew(Country::class);
        $oCountry->load($oxcountryid);
        $oxisoalpha2 = $oCountry->getFieldData('oxisoalpha2');
        $oxisoalpha3 = $oCountry->getFieldData('oxisoalpha3');
        if ($deliveryAdress) {
            return new CTAddress(
                ($deliveryAdress->oxaddress__oxsal->value == 'MR') ? 'Herr' : 'Frau',
                $deliveryAdress->oxaddress__oxcompany->value,
                $deliveryAdress->oxaddress__oxfname->value,
                $deliveryAdress->oxaddress__oxlname->value,
                $deliveryAdress->oxaddress__oxstreet->value,
                $deliveryAdress->oxaddress__oxstreetnr->value,
                $deliveryAdress->oxaddress__oxzip->value,
                $deliveryAdress->oxaddress__oxcity->value,
                $oxisoalpha2,
                $oxisoalpha3,
                $deliveryAdress->oxaddress__oxaddinfo->value
            );
        } else {
            return new CTAddress(
                ($oUser->oxuser__oxsal->value == 'MR') ? 'Herr' : 'Frau',
                $oUser->oxuser__oxcompany->value,
                $oUser->oxuser__oxfname->value,
                $oUser->oxuser__oxlname->value,
                $oUser->oxuser__oxstreet->value,
                $oUser->oxuser__oxstreetnr->value,
                $oUser->oxuser__oxzip->value,
                $oUser->oxuser__oxcity->value,
                $oxisoalpha2,
                $oxisoalpha3,
                $oUser->oxuser__oxaddinfo->value
            );
        }
    }

    protected function getParamBrowserInfo($browserData)
    {
        // @see
        $acceptHeaders = $_SERVER['HTTP_ACCEPT'];
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $javaEnabled = $browserData['fatchip_computop_creditcard_javaEnabled'];
        $javaScriptEnabled = $browserData['fatchip_computop_creditcard_javascriptEnabled']; // see above
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $acceptedLangs = explode(',', $acceptLang);
        $language = array_shift($acceptedLangs);
        $colorDepth = $this->getParamColorDepth((int)$browserData['fatchip_computop_creditcard_colorDepth']);
        $screenHeight = $browserData['fatchip_computop_creditcard_screenHeight'];
        $screenWidth = $browserData['fatchip_computop_creditcard_screenWidth'];
        $timeZoneOffset = $browserData['fatchip_computop_creditcard_timeZoneOffset'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if ($browserData['javaScriptEnabled'] === "false") {
            $browserInfoParams = array(
                'acceptHeaders' => $acceptHeaders,
                'ipAddress' => $ipAddress,
                'javaScriptEnabled' => ($javaScriptEnabled === "true") ? true : false,
                'language' => $language,
                'userAgent' => $userAgent,
            );
        } else {
            $browserInfoParams = array(
                'acceptHeaders' => $acceptHeaders,
                'ipAddress' => $ipAddress,
                'javaEnabled' => ($javaEnabled === "true") ? true : false,
                'javaScriptEnabled' => ($javaScriptEnabled === "true") ? true : false,
                'language' => $language,
                'colorDepth' => (int)$colorDepth,
                'screenHeight' => (int)$screenHeight,
                'screenWidth' => (int)$screenWidth,
                'timeZoneOffset' => $timeZoneOffset,
                'userAgent' => $userAgent,
            );
        }
        return base64_encode(json_encode($browserInfoParams));
    }

    /**
     * The computop API only accepts the values
     * 1,4,8,15,16,24,32 and 48
     * This method returns the next higher fitting value
     * when there is no exect match
     *
     * @param int $colorDepth
     * @return int $apiColorDepth
     */
    private function getParamColorDepth($colorDepth)
    {
        $acceptedValues = [1, 4, 8, 15, 16, 24, 32, 48];

        if (in_array($colorDepth, $acceptedValues, true)) {
            $apiColorDepth = $colorDepth;
        } elseif ($colorDepth <= 1) {
            $apiColorDepth = 1;
        } elseif ($colorDepth <= 4) {
            $apiColorDepth = 4;
        } elseif ($colorDepth <= 8) {
            $apiColorDepth = 8;
        } elseif ($colorDepth <= 15) {
            $apiColorDepth = 15;
        } elseif ($colorDepth <= 24) {
            $apiColorDepth = 24;
        } elseif ($colorDepth <= 32) {
            $apiColorDepth = 32;
        } else {
            $apiColorDepth = 48;
        }
        return $apiColorDepth;
    }

    public function getSilentParams()
    {
        return $this->fatchipComputopSilentParams;
    }

    public function twintAction()
    {
        $payment = $this->getPaymentClassForGatewayAction();
        $params = $payment->getRedirectUrlParams();
        $oUser = $this->getUser();
        $dynValue = Registry::getSession()->getVariable('dynvalue');

        if (Config::getInstance()->getConfigParam('debuglog') === 'extended') {
            $ctPayment = $this->computopGetPaymentModel();
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'Redirecting to ' . $payment->getHTTPGetURL($params),
                [
                    'payment' => $ctPayment->getLibClassName(),
                    'UserID' => $oUser->getId(),
                    'SessionID' => Registry::getSession()->getId(),
                    'parmas' => $params
                ]
            );
        }
        $requestParams = $payment->getRedirectUrlParams();
        $response = $payment->prepareComputopRequest($requestParams, $payment->getCTPaymentURL());
        Registry::getUtils()->redirect($response, false, 302);
    }

    /**
     * Returns and brings basket positions into appropriate form
     *
     *
     * @return array<int, array{reference: mixed, name: mixed, quantity: mixed, unit_price: float|int, tax_rate: float|int, total_amount: float|int}>
     */
    public function getKlarnaOrderlinesParams(): string
    {
        foreach ($this->getBasket()->getContents() as $oBasketItem) {
            $oArticle = $oBasketItem->getArticle();

            $articleListArray['order_lines'][] = [
                'reference' => $oArticle->oxarticles__oxartnum->value,
                'name' => $oBasketItem->getTitle(),
                'quantity' => (int)$oBasketItem->getAmount(),
                'unit_price' => (int)($oBasketItem->getUnitPrice()->getBruttoPrice() * 100),
                'tax_rate' => (int)($oBasketItem->getVatPercent() * 100),
                'total_amount' => (int)($oBasketItem->getPrice()->getBruttoPrice() * 100 * $oBasketItem->getAmount()),
                'total_tax_amount' => (int)round(
                    (($oBasketItem->getPrice()->getBruttoPrice() - $oBasketItem->getPrice()->getNettoPrice()) * 100)
                )
            ];
        }

        $oDelivery = $this->getBasket()->getCosts('oxdelivery');
        $sDeliveryCosts = $oDelivery === null ? 0.0 : $oDelivery->getBruttoPrice();

        $sDeliveryCosts = (double)str_replace(
            ',',
            '.',
            $sDeliveryCosts
        );

        if ($sDeliveryCosts > 0) {
            $deliveryTax = (int)(round(($sDeliveryCosts / 1.19 * 0.19 * 100), 2));
            $articleListArray['order_lines'][] = [
                'name' => $oBasketItem->getTitle(),
                'quantity' => 1,
                'unit_price' => (int)($sDeliveryCosts * 100),
                'total_amount' => (int)($sDeliveryCosts * 100),
                'tax_rate' => (int)($oDelivery->getVat() * 100),
                'total_tax_amount' => $deliveryTax
            ];
        }

        $articleList = base64_encode(json_encode($articleListArray));

        return $articleList;
    }

    /**
     * @param $params
     * @return CTResponse
     */
    public function requestKlarna($params, $payment)
    {
        $response = $payment->prepareComputopRequest($params, $payment->getCTPaymentURL());

        return $response;
    }

    /**
     * Calculates the Klarna tax amount by adding the tax amounts of each position in the article list.
     *
     * @param $articleList
     *
     * @return float
     */
    public static function calculateTaxAmount($articleList)
    {
        $taxAmount = 0;
        $articleList = json_decode(base64_decode($articleList), true);
        foreach ($articleList['order_lines'] as $article) {
            $itemTaxAmount = $article['total_tax_amount'];
            $taxAmount += $itemTaxAmount;
        }

        return $taxAmount;
    }

    public function getFatchipComputopShopCreditcardMode()
    {
        return Config::getInstance()->getConfigParam('creditCardMode');

    }

    /**
     * The RefNr for Computop has to be equal to the ordernumber.
     * Because the ordernumber is only known after successful payments
     * and successful saveOrder() call update the RefNr AFTER order creation
     *
     * @param Order $order Oxid order
     * @param string $paymentClass name of the payment class
     *
     * @return CTResponse
     * @throws Exception
     */
    private function updateRefNrWithComputop($order)
    {
        if (!$order) {
            return null;
        }

        $ctOrder = $this->createCTOrder();

        $ctPayment = $this->computopGetPaymentModel();
        if ($ctPayment->isIframeLibMethod() === true) {
            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass($ctPayment->getLibClassName(), Config::getInstance()->getConnectionConfig(), $ctOrder);
        } else {
            $payment = $this->fatchipComputopPaymentService->getPaymentClass($ctPayment->getLibClassName());
        }

        if ($ctPayment instanceof Klarna) {
            $payment->setTransID($order->getFieldData('fatchip_computop_transid'));
        }

        $RefNrChangeParams = $payment->getRefNrChangeParams($order->getFieldData('fatchip_computop_payid'), Api::getInstance()->getReferenceNumber($order->getFieldData('oxordernr')));

        $RefNrChangeParams['EtiId'] = CTPaymentParams::getUserDataParam();

        return $this->callComputopService(
            $RefNrChangeParams,
            $payment,
            'REFNRCHANGE',
            $payment->getCTRefNrChangeURL()
        );
    }

    protected function getPaymentClassForGatewayAction()
    {
        $ctOrder = $this->createCTOrder();
        $ctPayment = $this->computopGetPaymentModel();

        if (Config::getInstance()->getConfigParam('debuglog') === 'extended') {
            $order = var_export($ctOrder, true);
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'creating Order : ',
                [
                    'payment' => $ctPayment->getLibClassName(),
                    'UserID' => $this->getUser()->getId(),
                    'order' => $order,
                    'SessionID' => Registry::getSession()->getId()
                ]
            );
        }

        $shop = Registry::getConfig()->getActiveShop();

        $urlParams = CTPaymentParams::getUrlParams($ctPayment->getPaymentId());
        $payment = $this->fatchipComputopPaymentService->getIframePaymentClass(
            $ctPayment->getLibClassName(),
            Config::getInstance()->getConnectionConfig(),
            $ctOrder,
            $urlParams['UrlSuccess'],
            $urlParams['UrlFailure'],
            $urlParams['UrlNotify'],
            $shop->oxshops__oxname->value.' '.$shop->oxshops__oxversion->value,
            CTPaymentParams::getUserDataParam(),
            null,
            null,
            null,
            isset($urlParams['UrlCancel']) ? $urlParams['UrlCancel'] : null,
        );
        return $payment;
    }

    public function getPaymentParams($oUser, $dynValue)
    {
        $ctPayment = $this->computopGetPaymentModel();
        switch ($ctPayment->getPaymentId()) {
            case DirectDebit::ID:
                return [
                    'AccBank' => $dynValue['fatchip_computop_lastschrift_bankname'],
                    'AccOwner' => $dynValue['fatchip_computop_lastschrift_bank_account_holder'],
                    'IBAN' => $dynValue['fatchip_computop_lastschrift_iban'],
                ];
            case Klarna::ID;
                $aOrderlines = $this->getKlarnaOrderlinesParams();
                $taxAmount = $this->calculateTaxAmount($aOrderlines);
                $oxcountryid = $oUser->getFieldData('oxcountryid');
                $oCountry = oxNew(Country::class);
                $oCountry->load($oxcountryid);
                $oxisoalpha2 = $oCountry->getFieldData('oxisoalpha2');

                return [
                    'TaxAmount' => $taxAmount,
                    'ArticleList' => $aOrderlines,
                    'Account' => Config::getInstance()->getConfigParam('klarnaaccount'),
                    'bdCountryCode' => $oxisoalpha2,
                ];

            case Easycredit::ID:
                return [
                    'DateOfBirth' => $dynValue['fatchip_computop_easycredit_birthdate_year'] . '-' . $dynValue['fatchip_computop_easycredit_birthdate_month'] . '-' . $dynValue['fatchip_computop_easycredit_birthdate_day'],
                    'EventToken' => CTEnumEasyCredit::EVENTTOKEN_INIT,
                ];

            case PayPal::ID:
                return [
                    'TxType' => 'Order',
                    'Account' => '',
                ];

            case "FATCHIP_COMPUTOP_PAYMENTSTATUS_PAID":
                break;
        }
        return [];
    }

    public function callComputopService($requestParams, $payment, $requestType, $url)
    {
        $repository = oxNew(ApiLogRepository::class);
        $paymentName = $payment::paymentClass;
        // $paymentName = Constants::getPaymentClassfromId($paymentName);
        $response = $payment->callComputop($requestParams, $url);
        $logMessage = oxNew(ApiLog::class);
        $logMessage->setPaymentName($paymentName);
        $logMessage->setRequest($requestType);
        $logMessage->setRequestDetails(json_encode($requestParams));
        $logMessage->setTransId($response->getTransID());
        $logMessage->setPayId($response->getPayID());
        $logMessage->setXId($response->getXID());
        $logMessage->setResponse($response->getStatus());
        $logMessage->setResponseDetails(json_encode($response->toArray()));
        $logMessage->setCreationDate(date('Y-m-d H:i:s'));

        $repository->saveApiLog($logMessage);
        try {
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->logError('Unable to save API Log', [
                'error' => $e->getMessage()
            ]);
        }
        return $response;
    }

    /**
     * Gets inforation from response to be displayed on the order confirmation page
     *
     * @param \Fatchip\CTPayment\CTResponse $responseObject Easycredit financing information
     *
     * @return array
     */
    private function getConfirmPageInformation($responseObject)
    {
        $easyCreditInformation = [];
        $financing = json_decode($responseObject->getFinancing(), true);
        $easyCreditInformation['anzahlRaten'] = $financing['decision']['numberOfInstallments'];
        $easyCreditInformation['tilgungsplanText'] = $financing['decision']['amortizationPlanText'];
        $easyCreditInformation['bestellwert'] = $financing['decision']['orderValue'];
        $easyCreditInformation['anfallendeZinsen'] = $financing['decision']['interest'];
        $easyCreditInformation['gesamtsumme'] = $financing['decision']['totalValue'];
        $easyCreditInformation['effektivzins'] = $financing['decision']['effectiveInterest'];
        $easyCreditInformation['nominalzins'] = $financing['decision']['nominalInterest'];
        $easyCreditInformation['betragRate'] = $financing['decision']['installment'];
        $easyCreditInformation['betragLetzteRate'] = $financing['decision']['lastInstallment'];
        $easyCreditInformation['urlVorvertraglicheInformationen'] = $financing['decision']['urlPreContractualInformation'];
        return $easyCreditInformation;
    }

    /**
     * @return \Fatchip\ComputopPayments\Model\Method\BaseMethod|false
     * @throws Exception
     */
    public function computopGetPaymentModel()
    {
        $paymentId = $this->getBasket()->getPaymentId();
        if (Payment::getInstance()->isComputopPaymentMethod($paymentId) === true) {
            return Payment::getInstance()->getComputopPaymentModel($paymentId);
        }
        return false;
    }
}
