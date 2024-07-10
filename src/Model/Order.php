<?php

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Oxid Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 8.1, 8.2
 *
 * @category   Payment
 * @package    fatchip-gmbh/computop_payments
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Model;

use Exception;
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Repository\ApiLogRepository;
use Fatchip\CTPayment\CTAddress\CTAddress;
use Fatchip\CTPayment\CTEnums\CTEnumEasyCredit;
use Fatchip\CTPayment\CTEnums\CTEnumStatus;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethod;
use Fatchip\CTPayment\CTPaymentService;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\PaymentGateway;
use OxidEsales\Eshop\Core\Registry;

use function date;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    protected $fatchipComputopConfig;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    public $fatchipComputopSilentParams;
    protected $fatchipComputopLogger;
    protected $fatchipComputopBasket;
    protected $fatchipComputopPaymentService;

    public $oxorder__fatchip_computop_transid;
    public $oxorder__fatchip_computop_payid;
    public $oxorder__fatchip_computop_xid;
    public $oxorder__fatchip_computop_lastschrift_mandateid;
    public $oxorder__fatchip_computop_lastschrift_dos;
    public $oxorder__fatchip_computop_creditcard_schemereferenceid;
    public $oxorder__fatchip_computop_amount_captured;
    public $oxorder__fatchip_computop_amount_refunded;
    public $oxorder__fatchip_computop_remark;


    /**
     * init object construction
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();
        $config = new Config();
        $this->fatchipComputopConfig = $config->toArray();
        $this->fatchipComputopSession = Registry::getSession();
        $this->fatchipComputopShopConfig = Registry::getConfig();
        $this->fatchipComputopShopUtils = Registry::getUtils();
        $this->fatchipComputopLogger = new Logger();
        $this->fatchipComputopBasket = $this->fatchipComputopSession->getBasket();
        $this->fatchipComputopPaymentService = new CTPaymentService($this->fatchipComputopConfig);
    }

    /**
     * Order checking, processing and saving method.
     *
     * @param Basket $oBasket Basket object
     * @param object $oUser Current User object
     * @param bool $blRecalculatingOrder Order recalculation
     *
     * @return int|null
     */
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        $ret = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
        $paymentId = $oBasket->getPaymentId() ?: '';
        $isFatchipComputopPayment = Constants::isFatchipComputopPayment($paymentId);
        $isFatchipComputopRedirectPayment = Constants::isFatchipComputopRedirectPayment($paymentId);
        $isFatchipComputopDirectPayment = Constants::isFatchipComputopDirectPayment($paymentId);

        if (
            $ret < 2 &&
            !$blRecalculatingOrder &&
            $isFatchipComputopPayment
        ) {
            $this->fatchipComputopPaymentId = $paymentId;
            $this->fatchipComputopPaymentClass = Constants::getPaymentClassfromId($paymentId);
            if ($isFatchipComputopDirectPayment) {
                $response = $this->fatchipComputopSession->getVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse');
            } else if ($isFatchipComputopRedirectPayment){
                $response = $this->fatchipComputopSession->getVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl');
                Registry::getUtils()->redirect($response, false);
                return $this->handleRedirectResponse($response);
            }

            $returning = false;
            if ($returning) {
                $this->customizeOrdernumber($response);
                $this->updateOrderAttributes($response);
                $this->updateComputopFatchipOrderStatus('FATCHIP_COMPUTOP_PAYMENTSTATUS_RESERVED');
                $this->autocapture($oUser, false);
            }
        }
        if ($ret === 3) {
            // check Status and set Order appropiatelay
            $ret = $this->finalizeRedirectOrder($oBasket, $oUser, $blRecalculatingOrder);
        }


        return $ret;
    }

    public function updateComputopFatchipOrderStatus(string $orderStatus, array $data = [])
    {
        switch ($orderStatus) {
            case "FATCHIP_COMPUTOP_PAYMENTSTATUS_PAID":
                $this->setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->setFieldData('oxpaid', date('Y-m-d H:i:s'));
                $this->setFieldData('oxtransstatus', 'OK');
                if (!empty($data)) {
                    $this->setFieldData('fatchip_computop_amount_captured', $data['captureAmount']);
                    $this->setFieldData('fatchip_computop_remark', 'Payment Completed');
                }
                $this->save();
                break;

            case "FATCHIP_COMPUTOP_PAYMENTSTATUS_RESERVED":
                $this->setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->setFieldData('oxtransstatus', 'NOT_FINISHED');
                if (!empty($data)) {
                    $this->setFieldData('fatchip_computop_remark', 'Auth OK  Capture pending');
                }
                $this->save();
                break;

            case "FATCHIP_COMPUTOP_PAYMENTSTATUS_REVIEW_NECESSARY":
                $this->setFieldData('oxtransstatus', 'NOT_FINISHED');
                $this->setFieldData('oxfolder', 'ORDERFOLDER_PROBLEMS');
                if (!empty($data)) {
                    $this->setFieldData(
                        'fatchip_computop_remark',
                        'Code: ' . $data['errorCode'] . '-' . $data['errorMessage']
                    );
                }
                $this->save();
                break;
        }
    }

    /**
     * @param string $oxid
     * @return bool
     */
    public function isFatchipComputopOrder(): bool
    {
        /** @var string $paymentId */
        $paymentId = $this->getFieldData('oxpaymenttype');
        return Constants::isFatchipComputopPayment($paymentId);
    }

    /**
     * @param string $oxid
     * @return bool
     */
    public function isFatchipComputopRedirectPayment(): bool
    {
        /** @var string $paymentId */
        $paymentId = $this->getFieldData('oxpaymenttype');
        return Constants::isFatchipComputopRedirectPayment($paymentId);
    }

    /**
     * @param string $oxid
     * @return bool
     */
    public function isFatchipComputopDirectPayment(): bool
    {
        /** @var string $paymentId */
        $paymentId = $this->getFieldData('oxpaymenttype');
        return Constants::isFatchipComputopDirectPayment($paymentId);
    }


    /**
     * @param $force
     *
     */
    public function autoCapture($oUser, $force = false)
    {
        if ($this->fatchipComputopPaymentId === 'fatchip_computop_ideal') {
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'autoCapture: skipping for ' . $this->fatchipComputopPaymentId,
                []
            );
            return;
        }

        if ($force !== true && (!
            (($this->fatchipComputopPaymentId === 'fatchip_computop_creditcard' && $this->fatchipComputopConfig['creditCardCaption'] === 'AUTO')
                || ($this->fatchipComputopPaymentId === 'fatchip_computop_lastschrift' && $this->fatchipComputopConfig['lastschriftCaption'] === 'AUTO')
                || ($this->fatchipComputopPaymentId === 'fatchip_computop_paypal_standard' && $this->fatchipComputopConfig['paypalCaption'] === 'AUTO')
                || ($this->fatchipComputopPaymentId === 'fatchip_computop_paypal_express' && $this->fatchipComputopConfig['paypalCaption'] === 'AUTO')
                || ($this->fatchipComputopPaymentId === 'fatchip_computop_amazonpay' && $this->fatchipComputopConfig['amazonCaptureType'] === 'AUTO')
            ))
        ) {
            if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
                $sessionID = $this->fatchipComputopSession->getId();
                $customerId = $oUser->getId();
                $this->fatchipComputopLogger->log(
                    'DEBUG',
                    'autoCapture: skipping for ' . $this->fatchipComputopPaymentClass,
                    ['UserID' => $customerId, 'SessionID' => $sessionID]
                );
            }
            return;
        }

        $captureResponse = $this->captureOrder();
        $captureResponseStatus = $captureResponse->getStatus();
        if ($captureResponseStatus === 'OK') {
            $this->updateComputopFatchipOrderStatus(
                CONSTANTS::PAYMENTSTATUSPAID,
                ['captureAmount' => $captureResponse->getAmountCap()]
            );
            if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
                $oUser = $this->getUser();
                $customerId = $oUser->getFieldData('oxcustnr');
                $sessionID = $this->fatchipComputopSession->getId();
                $paymentClass = Constants::getPaymentClassfromId($this->fatchipComputopPaymentClass);
                $this->fatchipComputopLogger->log(
                    'DEBUG',
                    'autoCapture: success for ' . $paymentClass . 'order status is' . Constants::PAYMENTSTATUSPAID,
                    [
                        'order' => $this->getFieldData('oxordernr'),
                        'payment' => $paymentClass,
                        'UserID' => $customerId,
                        'SessionID' => $sessionID,
                        'CaptureResponse' => var_export($captureResponse, true)
                    ]
                );
            }
        } else {
            if ($captureResponseStatus === 'FAILED') {
                $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSREVIEWNECESSARY,
                    $data = [
                        'errorCode' => $captureResponse->getCode(),
                        'errorMessage' => $captureResponse->getDescription()
                    ]
                );
                if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
                    $sessionID = $this->fatchipComputopSession->getId();
                    $customerId = $oUser->getFieldData('oxcustnr');
                    $paymentClass = Constants::getPaymentClassfromId($this->fatchipComputopPaymentId);
                    $this->fatchipComputopLogger->log(
                        'DEBUG',
                        'autoCapture: failure for ' . $paymentClass . 'setting order status to ' . CONSTANTS::PAYMENTSTATUSREVIEWNECESSARY,
                        [
                            'order' => $this->getFieldData('oxordernr'),
                            'payment' => $paymentClass,
                            'UserID' => $customerId,
                            'SessionID' => $sessionID,
                            'CaptureResponse' => var_export($captureResponse, true)
                        ]
                    );
                }
            }
        }
    }


    /**
     * Update ordernumber with custom prefix and custom suffix
     *
     * @param CTResponse $response
     * @return Order $order new Order with updated ordernumber
     */
    public
    function customizeOrdernumber(
        $response
    ) {
        $payID = $response->getPayID();
        $transID = $response->getTransID();
        $xID = $response->getXID();
        $orderPrefix = $this->fatchipComputopConfig['prefixOrdernumber'];
        $orderSuffix = $this->fatchipComputopConfig['suffixOrdernumber'];
        $orderNumber = $this->getFieldData('oxordernr');
        $newOrdernumber = $orderPrefix . $orderNumber . $orderSuffix;

        // replace placeholders
        $newOrdernumber = str_replace('%transid%', $transID, $newOrdernumber);
        $newOrdernumber = str_replace('%payid%', $payID, $newOrdernumber);
        $newOrdernumber = str_replace('%xid%', $xID, $newOrdernumber);

        $this->setFieldData('oxordernr', $newOrdernumber);
        $this->save();

        // TODO Use sessionID from CustomParm response
        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $oUser = $this->getUser();
            $customerId = $oUser->getFieldData('oxcustnr');
            $sessionID = $this->fatchipComputopSession->getId();
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'customizeOrdernumber: updating orderNumber ' . $orderNumber . ' to ' . $newOrdernumber,
                ['payment' => $this->fatchipComputopPaymentClass, 'UserID' => $customerId, 'SessionID' => $sessionID]
            );
        }
    }

    public
    function updateOrderAttributes(
        $response
    ) {
        $payID = $response->getPayID();
        $transID = $response->getTransID();
        $xID = $response->getXID();
        $schemeRefId = $response->getSchemeReferenceID();
        $mandateId = $response->getMandateid();


        $this->setFieldData('fatchip_computop_payid', $payID);
        $this->setFieldData('fatchip_computop_transid', $transID);
        $this->setFieldData('fatchip_computop_xid', $xID);
        $this->setFieldData('fatchip_computop_creditcard_schemereferenceid', $schemeRefId);
        $this->setFieldData('fatchip_computop_lastschrift_mandateid', $mandateId);
        $this->save();
    }

    /**
     * @param \OxidEsales\Eshop\Application\Model\Order $order
     *
     * @return CTResponse
     */
    protected
    function captureOrder()
    {
        $ctOrder = $this->createCTOrder();

        if ($this->fatchipComputopPaymentClass !== 'PaypalExpress'
            && $this->fatchipComputopPaymentClass !== 'AmazonPay'
        ) {
            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass(
                $this->fatchipComputopPaymentClass,
                $this->fatchipComputopConfig,
                $ctOrder
            );
        } else {
            $payment = $this->fatchipComputopPaymentService->getPaymentClass($this->fatchipComputopPaymentClass);
        }

        $test = $this->getFieldData('oxorder__fatchip_computop_payid');
        $test2 = $this->getFieldData('fatchip_computop_payid');
        $orderSum = ((double)$this->oxorder__oxtotalordersum) * 100;
        $payId = $this->getFieldData('fatchip_computop_payid');
        $transId = $this->getFieldData('fatchip_computop_transid');
        $xid = $this->getFieldData('fatchip_computop_xid');
        $schemerefid = $this->getFieldData('fatchip_computop_creditcard_schemereferenceid');

        $requestParams = $payment->getCaptureParams(
            $payId,
            round($orderSum, 2),
            $this->getFieldData('oxorder__oxcurrency'),
            $transId,
            $xid,
            'none',
            $schemerefid
        );

        return $this->callComputopService($requestParams, $payment, 'CAPTURE', $payment->getCTCaptureURL());
    }

    /**
     * creates a CTAddress object from an oxid order
     * @return CTAddress
     * @throws \Exception
     */
    public
    function getCTAddress(
        $deliveryAddress = false
    ) {
        $orderId = Registry::getSession()->getVariable('sess_challenge');
        $this->load($orderId);

        if (!$deliveryAddress) {
            $oxcountryid = $this->getFieldData('oxbillcountryid');
            $oCountry = oxNew(Country::class);
            $oCountry->load($oxcountryid);
            $oxisoalpha2 = $oCountry->getFieldData('oxisoalpha2');
            $oxisoalpha3 = $oCountry->getFieldData('oxisoalpha3');
            return new CTAddress(
                ($this->getFieldData('oxbillsal') === 'MR') ? 'Herr' : 'Frau',
                $this->getFieldData('oxbillcompany'),
                $this->getFieldData('oxbillfname'),
                $this->getFieldData('oxbilllname'),
                $this->getFieldData('oxbillstreet'),
                $this->getFieldData('oxbillstreetnr'),
                $this->getFieldData('oxorder__oxbillzip'),
                $this->getFieldData('oxbillcity'),
                $oxisoalpha2,
                $oxisoalpha3,
                $this->getFieldData('oxbilladdinfo')
            );
        } else {
            $oxcountryid = $this->getFieldData('oxdelcountryid');
            $oCountry = oxNew(Country::class);
            $oCountry->load($oxcountryid);
            $oxisoalpha2 = $oCountry->getFieldData('oxisoalpha2');
            $oxisoalpha3 = $oCountry->getFieldData('oxisoalpha3');
            return new CTAddress(
                ($this->getFieldData('oxdelsal') === 'MR') ? 'Herr' : 'Frau',
                $this->getFieldData('oxdelcompany'),
                $this->getFieldData('oxdelfname'),
                $this->getFieldData('oxdellname'),
                $this->getFieldData('oxdelstreet'),
                $this->getFieldData('oxdelstreetnr'),
                $this->getFieldData('oxorder__oxdelzip'),
                $this->getFieldData('oxdelcity'),
                $oxisoalpha2,
                $oxisoalpha3,
                $this->getFieldData('oxdeladdinfo')
            );
        }
    }

    public
    function callComputopService(
        $requestParams,
        $payment,
        $requestType,
        $url
    ) {
        $repository = oxNew(ApiLogRepository::class);
        $paymentName = $payment::paymentClass;

        $response = $payment->callComputop($requestParams, $url);
        $logMessage = oxNew(Apilog::class);
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
     * Handle authorization of current order
     *
     * @param PaymentGateway|null $oPayGateway
     * @return boolean
     */
    public
    function handleAuthorization(
        $amount,
        PaymentGateway $oPayGateway = null
    ) {
        $dynValue = $this->fatchipComputopSession->getVariable('dynvalue');
        $oUser = $this->getUser();

        $ctOrder = $this->createCTOrder($amount);

        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $this->writeOrderLog($ctOrder);
        }
        $urlParams = $this->getDirectAuthUrlParams();
        $paymentClass = Constants::getPaymentClassfromId($this->getFieldData('oxpaymenttype'));
        $payment = $this->fatchipComputopPaymentService->getIframePaymentClass(
            $paymentClass,
            $this->fatchipComputopConfig,
            $ctOrder,
            $urlParams['UrlSuccess'],
            $urlParams['UrlFailure'],
            $urlParams['UrlNotify'],
            $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxname->value . ' '
            . $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxversion->value,
            $this->getUserDataParam(),
            null,
            null,
            null,
            $urlParams['UrlCancel']
        );

        $classParams = $payment->getRedirectUrlParams();
        $paymentParams = $this->getPaymentParams($payment, $dynValue);
        $customParam = $this->getCustomParam($payment->getTransID());
        $params = array_merge($classParams,$paymentParams, $customParam);

        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $sessionID = $this->fatchipComputopSession->getId();
            $customerId = $oUser->getFieldData('oxcustnr');
            $order = var_export($ctOrder, true);
            $paymentName = $this->fatchipComputopPaymentClass;
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'Calling ' . $payment->getCTPaymentURL($params),
                [
                    'payment' => $paymentName,
                    'UserID' => $customerId,
                    'order' => $order,
                    'SessionID' => $sessionID,
                    'params' => $params
                ]
            );
        }
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'DirectRequest', $params);
        $response = $payment->callComputop($params, $payment->getCTPaymentURL());
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse', $response);

        return $this->handleAuthorizationResponse($response);
    }

    /**
     * Handle authorization of current order
     *
     * @param PaymentGateway|null $oPayGateway
     * @return boolean
     */
    public
    function handleRedirectPayment(
        $amount,
        PaymentGateway $oPayGateway = null
    ) {
        $dynValue = $this->fatchipComputopSession->getVariable('dynvalue');
        $this->fatchipComputopPaymentId = $this->getFieldData('oxpaymenttype');
        $this->fatchipComputopPaymentClass = Constants::getPaymentClassfromId($this->getFieldData('oxpaymenttype'));
        $oUser = $this->getUser();
        $payment = $this->getPaymentClassForGatewayAction();
        $UrlParams = $this->getUrlParams();
        $redirectParams = $payment->getRedirectUrlParams();
        $paymentParams = $this->getPaymentParams($oUser, $dynValue);
        $customParam = $this->getCustomParam($payment->getTransID());
        $params = array_merge($redirectParams, $paymentParams, $customParam, $UrlParams);
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrlRequestParams', $params);
        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $sessionID = $this->fatchipComputopSession->getId();
            $customerId = $oUser->getFieldData('oxcustnr');
            $order = var_export($ctOrder, true);
            $paymentName = $this->fatchipComputopPaymentClass;
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'Calling ' . $payment->getCTPaymentURL($params),
                [
                    'payment' => $paymentName,
                    'UserID' => $customerId,
                    'order' => $order,
                    'SessionID' => $sessionID,
                    'params' => $params
                ]
            );
        }

        $response = $payment->getHTTPGetURL($params);
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);
        Registry::getUtils()->redirect($response, false);
        // return true;
    }

    protected
    function createCTOrder()
    {
        $ctOrder = new CTOrder();
        $oUser = $this->getUser();

        $ctOrder->setAmount((int)(round($this->getFieldData('oxtotalordersum') * 100)));
        $ctOrder->setCurrency($this->getFieldData('oxcurrency'));
        // try catch in case Address Splitter returns exceptions
        try {
            $ctOrder->setBillingAddress($this->getCTAddress());
            $delAddressExists = !empty($this->getFieldData('oxdelstreet'));
            $ctOrder->setShippingAddress($this->getCTAddress($delAddressExists));

        } catch (Exception $e) {
            Registry::getUtilsView()->addErrorToDisplay('FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_ERROR_ADDRESS');
            $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
            $returnUrl = $sShopUrl . 'index.php?cl=payment';
            Registry::getUtils()->redirect($returnUrl, false, 301);
        }
        $ctOrder->setEmail($oUser->oxuser__oxusername->value);
        $ctOrder->setCustomerID($oUser->oxuser__oxcustnr->value);
        // Mandatory for paypalStandard
        $orderDesc = $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxname->value . ' '
            . $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxversion->value;
        $ctOrder->setOrderDesc($orderDesc);
        return $ctOrder;
    }

    public
    function writeOrderLog(
        $ctOrder
    ) {
        $oUser = $this->getUser();
        $customerNr = $oUser->getFieldData('oxcustnr');
        $order = var_export($ctOrder, true);
        $paymentId = $this->getFieldData('oxpaymenttype');
        $paymentClass = Constants::getPaymentClassfromId($paymentId);
        $this->fatchipComputopLogger->log(
            'DEBUG',
            'creating Order : ',
            [
                'payment' => $paymentClass,
                'CustomerNr' => $customerNr,
                'order' => $order,
                'SessionID' => $this->fatchipComputopSession->getId()
            ]
        );
    }

    public
    function getUrlParams()
    {
        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        $URLSuccess = $sShopUrl . 'index.php?cl=' . $this->fatchipComputopPaymentId;
        $URLFailure = $sShopUrl . 'index.php?cl=' . $this->fatchipComputopPaymentId;
        $URLCancel = $sShopUrl . 'index.php?cl=' . $this->fatchipComputopPaymentId;
        $URLNotify = $sShopUrl . 'index.php?cl=' . Constants::GENERAL_PREFIX . 'notify';
        return [
            'UrlSuccess' => $URLSuccess,
            'UrlFailure' => $URLFailure,
            'UrlNotify' => $URLNotify,
            'UrlCancel' => $URLCancel,
        ];
    }

    public
    function getDirectAuthUrlParams()
    {
        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        $URLSuccess = $sShopUrl . 'index.php?cl=thankyou';
        $URLFailure = $sShopUrl . 'index.php?cl=order';
        $URLCancel = $sShopUrl . 'index.php?cl=order';
        $URLNotify = $sShopUrl . 'index.php?cl=' . Constants::GENERAL_PREFIX . 'notify';
        return [
            'UrlSuccess' => $URLSuccess,
            'UrlFailure' => $URLFailure,
            'UrlNotify' => $URLNotify,
            'UrlCancel' => $URLCancel,
        ];
    }

    public
    function getCustomParam($transid)
    {
        $this->fatchipComputopSession->setVariable(Constants::GENERAL_PREFIX . 'TransId', $transid);
        $orderOxId = Registry::getSession()->getVariable('sess_challenge');
        $custom = base64_encode('session='.$orderOxId . '&transid=' . $transid);
        // return ['custom' => $custom];
        return ['custom' => 'Custom=' . $custom];
    }

    /**
     * Sets the userData paramater for Computop calls to Shopware Version and Module Version
     *
     * @return string
     * @throws Exception
     */
    public
    function getUserDataParam()
    {
        return $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxname->value . ' '
            . $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxversion->value;
    }

    public
    function getPaymentParams(
        $oUser,
        $dynValue
    ) {

        switch ($this->getFieldData('oxpaymenttype')) {
            case "fatchip_computop_lastschrift":
                return [
                    'AccBank' => $dynValue['fatchip_computop_lastschrift_bankname'],
                    'AccOwner' => $dynValue['fatchip_computop_lastschrift_bank_account_holder'],
                    'IBAN' => $dynValue['fatchip_computop_lastschrift_iban'],
                ];
            case "fatchip_computop_klarna";
                    $aOrderlines = $this->getKlarnaOrderlinesParams();
                    $taxAmount = $this->calculateTaxAmount($aOrderlines);
                    $oxcountryid = $oUser->getFieldData('oxcountryid');
                    $oCountry = oxNew(Country::class);
                    $oCountry->load($oxcountryid);
                    $oxisoalpha2 = $oCountry->getFieldData('oxisoalpha2');

                    return [
                        'TaxAmount' => $taxAmount,
                        'ArticleList' => $aOrderlines,
                        'Account' => $this->fatchipComputopConfig['klarnaaccount'],
                        'bdCountryCode' => $oxisoalpha2,
                    ];

            case "fatchip_computop_easycredit":
                return [
                    'DateOfBirth' => $dynValue['fatchip_computop_easycredit_birthdate_year'] . '-' . $dynValue['fatchip_computop_easycredit_birthdate_month'] . '-' . $dynValue['fatchip_computop_easycredit_birthdate_day'],
                    'EventToken' => CTEnumEasyCredit::EVENTTOKEN_INIT,
                ];

            case "fatchip_computop_paypal_standard":
                return [
                    'TxType' => 'Order',
                    'Account' => '',
                ];

            case "fatchip_computop_ideal":
                if ($this->fatchipComputopConfig['idealDirektOderUeberSofort'] === 'PPRO') {
                    return [];
                } else {
                    return [
                        'issuerID' => $dynValue['fatchip_computop_ideal_bankname'],
                    ];
                }
        }
        return [];
    }

    public
    function handleAuthorizationResponse(
        $response
    ) {
        switch ($response->getStatus()) {
            case CTEnumStatus::OK:
            case CTEnumStatus::AUTHORIZED:
            case CTEnumStatus::AUTHORIZE_REQUEST:
                // TODO check for Code 000000
                return true;
        }
        return false;
    }

    public
    function handleRedirectResponse(
        $response
    ) {
        if (gettype($response) === 'string') {
            Registry::getUtils()->redirect($response, false, 302);
        } else {
            switch ($response->getStatus()) {
                case CTEnumStatus::OK:
                case CTEnumStatus::AUTHORIZED:
                case CTEnumStatus::AUTHORIZE_REQUEST:
                    // TODO check for Code 000000
                    return true;
            }
        }
        return false;
    }


    /**
     * Returns and brings basket positions into appropriate form
     *
     *
     * @return array<int, array{reference: mixed, name: mixed, quantity: mixed, unit_price: float|int, tax_rate: float|int, total_amount: float|int}>
     */
    public
    function getKlarnaOrderlinesParams(): string
    {
        $aOrderlines = [];
        foreach ($this->fatchipComputopBasket->getContents() as $oBasketItem) {
            $oArticle = $oBasketItem->getArticle();
            $test = round(
                (($oBasketItem->getPrice()->getBruttoPrice() - $oBasketItem->getPrice()->getNettoPrice()) * 100)
            );
            $test2 = (int)((($oBasketItem->getPrice()->getBruttoPrice() - $oBasketItem->getPrice()->getNettoPrice(
                    )) * 100));
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

        $oDelivery = $this->fatchipComputopBasket->getCosts('oxdelivery');
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
        $test = json_encode($articleListArray);

        $articleList = base64_encode(json_encode($articleListArray));

        return $articleList;
    }

    /**
     * Calculates the Klarna tax amount by adding the tax amounts of each position in the article list.
     *
     * @param $articleList
     *
     * @return float
     */
    public
    static function calculateTaxAmount(
        $articleList
    ) {
        $taxAmount = 0;
        $articleList = json_decode(base64_decode($articleList), true);
        foreach ($articleList['order_lines'] as $article) {
            $itemTaxAmount = $article['total_tax_amount'];
            $taxAmount += $itemTaxAmount;
        }

        return $taxAmount;
    }

    protected
    function getPaymentClassForGatewayAction()
    {
        // used
        $ctOrder = $this->createCTOrder();

        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $sessionID = $this->fatchipComputopSession->getId();
            $oUser = $this->getUser();
            $customerId = $oUser->getId();
            $paymentClass = Constants::getPaymentClassfromId($this->fatchipComputopPaymentId);
            $order = var_export($ctOrder, true);
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'creating Order : ',
                ['payment' => $paymentClass, 'UserID' => $customerId, 'order' => $order, 'SessionID' => $sessionID]
            );
        }

        $urlParams = $this->getUrlParams();
        $payment = $this->fatchipComputopPaymentService->getIframePaymentClass(
            Constants::getPaymentClassfromId($this->fatchipComputopPaymentId),
            $this->fatchipComputopConfig,
            $ctOrder,
            $urlParams['UrlSuccess'],
            $urlParams['UrlFailure'],
            $urlParams['UrlNotify'],
            $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxname->value . ' '
            . $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxversion->value,
            $this->getUserDataParam(),
            null,
            null,
            null,
            $urlParams['UrlCancel']
        );
        return $payment;
    }

    /**
     * @param $className
     * @return CTPaymentMethod
     */
    public
    function getPaymentClass(
        $className
    ) {
        $class = 'Fatchip\\CTPayment\\CTPaymentMethods\\' . $className;
        return new $class();
    }

    /**
     * @param $params
     * @return CTResponse
     */
    public
    function requestKlarna(
        $params,
        $payment
    ) {
        $requestType = 'KLARNA';

        $response = $payment->prepareComputopRequest($params, $payment->getCTPaymentURL());

        return $response;
    }

    public function finalizeRedirectOrder(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        // check if this order is already stored
        $orderId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable('sess_challenge');
        $this->load($orderId);

        // payment information
        $oUserPayment = $this->setPayment($oBasket->getPaymentId());

        if (!isset($this->oxorder__oxordernr->value) || !$this->oxorder__oxordernr->value) {
            $this->setNumber();
        } else {
            oxNew(\OxidEsales\Eshop\Core\Counter::class)->update($this->getCounterIdent(), $this->oxorder__oxordernr->value);
        }

        // deleting remark info only when order is finished
        \OxidEsales\Eshop\Core\Registry::getSession()->deleteVariable('ordrem');

        //#4005: Order creation time is not updated when order processing is complete
        if (!$blRecalculatingOrder) {
            $this->updateOrderDate();
        }

        // updating order trans status (success status)
        $this->setOrderStatus('OK');

        // store orderid
        $oBasket->setOrderId($this->getId());

        // updating wish lists
        $this->updateWishlist($oBasket->getContents(), $oUser);

        // updating users notice list
        $this->updateNoticeList($oBasket->getContents(), $oUser);

        // marking vouchers as used and sets them to $this->_aVoucherList (will be used in order email)
        // skipping this action in case of order recalculation
        if (!$blRecalculatingOrder) {
            $this->markVouchers($oBasket, $oUser);
        }

        // send order by email to shop owner and current user
        // skipping this action in case of order recalculation
        if (!$blRecalculatingOrder) {
            $iRet = $this->sendOrderByEmail($oUser, $oBasket, $oUserPayment);
        } else {
            $iRet = self::ORDER_STATE_OK;
        }

        return $iRet;
    }
}
