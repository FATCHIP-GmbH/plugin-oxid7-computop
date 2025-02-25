<?php

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * The Computop Oxid Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Oxid Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 8.1, 8.2
 *
 * @category   Payment
 * @package    fatchip-gmbh/computop_payments
 * @subpackage Admin_FatchipComputopConfig
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop UpdateIdealIssuers
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Controller;

use Exception;
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Model\ApiLog;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethods\PayPalExpress;
use Fatchip\CTPayment\CTPaymentService;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use VIISON\AddressSplitter\AddressSplitter;

class FatchipComputopPayPalExpress extends FrontendController
{
    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypalexpress';

    protected $fatchipComputopConfig;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    protected $fatchipComputopLogger;
    public $fatchipComputopSilentParams;
    protected $fatchipComputopPaymentService;

    public function init()
    {
        // deactivated - throws warnings - not sure if needed
        #ini_set('session.cookie_samesite', 'None');
        #ini_set('session.cookie_secure', true);
        parent::init();
    }

    /**
     * Class constructor, sets all required parameters for requests.
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
        $this->fatchipComputopPaymentService = new CTPaymentService($this->fatchipComputopConfig);
    }

    /**
     * The controller renderer
     *
     * @return string
     */
    public function render()
    {
        return parent::render();
        /**
         * $len = Registry::getRequest()->getRequestParameter('Len');
         * $data = Registry::getRequest()->getRequestParameter('Data');
         * $custom = Registry::getRequest()->getRequestParameter('Custom');
         * $response = null;
         *
         * if (!empty($len) && !empty($data)) {
         * $PostRequestParams = [
         * 'Len' => $len,
         * 'Data' => $data,
         * 'Custom' => $custom,
         * ];
         * $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
         * Registry::getLogger()->error('[render] ' . print_r($response->toArray(), true));
         * }
         *
         * if ($response !== null && $response->getStatus() === 'AUTHORIZED') {
         * $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypal_express_return';
         * } else {
         * $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypal_express';
         * }
         *
         * return parent::render();
         **/
    }

    /**
     * Returns PayPal Express URL or redirects directly to it
     *
     * @return mixed
     */
    public function getPayPalExpressUrl()
    {
        $redirectUrl = $this->fatchipComputopSession->getVariable('FatchipComputopPayPalExpressURL');
        if ($redirectUrl) {
            return $redirectUrl;
        }
    }

    /**
     * PaypalExpress success hook
     * @return void
     */
    public function success()
    {
        $iLen = Registry::getRequest()->getRequestParameter('Len');
        $sData = Registry::getRequest()->getRequestParameter('Data');
        $oApiLog = oxNew(ApiLog::class);
        $aLog = [
            'request' => 'SUCCESS_HOOK',
            'response' => 'SUCCESS_HOOK',
            'creation_date' => date('Y-m-d H:i:s', time()),
            'payment_name' => PayPalExpress::paymentClass
        ];

        $aResponseLog = [
            'Len' => $iLen,
            'Data' => $sData
        ];

        $aLog['response_details'] = json_encode($aResponseLog);

        if (!empty($iLen) && !empty($sData)) {
            $oResponse = $this->fatchipComputopPaymentService->getDecryptedResponse([
                'Len' => $iLen,
                'Data' => $sData,
            ]);

            $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectResponse',$oResponse);

            $aResponseLog['raw'] = $oResponse->toArray();
            $aLog['trans_id'] = $oResponse->getTransID();
            $aLog['pay_id'] = $oResponse->getPayID();
            $aLog['response_details'] = json_encode($aResponseLog);

            if ($oResponse->getStatus() === 'OK') {
                $sOrderTransId = $oResponse->getTransID();
                $oOrder = oxNew(Order::class);
                if ($oOrder->loadByTransId($sOrderTransId)) {
                    if (!$this->updateUserAndOrderInfo($oResponse, $oOrder)) {
                        //TODO: handle in case it's required
                    }

                    //  $oOrder->oxorder__oxtransstatus = new Field('OK');
                    if (!$oOrder->save()) {
                        $aLog['request_details'] = 'NOT ABLE TO CHANGE ORDER STATUS TO OK';
                        Registry::getLogger()->error('paypal_express_success_hook: not able to change associated order\'s status, log dump: ' . print_r($aLog, true));
                    }

                    $oApiLog->assign($aLog);
                    $oApiLog->save();
                    //set the sess_challenge is needed for the ThankYouController
                    Registry::getSession()->setVariable('sess_challenge', $oOrder->getId());
                    Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX.'PpeOngoing', $oResponse->getTransID());
                    Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX.'PpeFinished',0);
                    //redirect to the order page
                    Registry::getUtils()->redirect(Registry::getConfig()->getShopUrl() . 'index.php?cl=order');
                } else {
                    $aLog['request_details'] = 'NOT ABL TO LOAD ASSOCIATED ORDER ' . $sOrderTransId;
                    Registry::getLogger()->error('paypal_express_success_hook: not able to load associated order, log dump: ' . print_r($aLog, true));
                }
            } else {
                $aLog['request_details'] = '';
                Registry::getLogger()->error('');
            }
        } else {
            $aLog['request_details'] = 'FATAL ERROR';
            Registry::getLogger()->error('paypal_express_success_hook: hook invoked with invalid params,  log dump: ' . print_r($aLog, true));
        }

        $oApiLog->assign($aLog);
        $oApiLog->save();
    }

    /**
     * PaypalExpress failure hook
     * @return void
     */
    public function failure()
    {
        $iLen = Registry::getRequest()->getRequestParameter('Len');
        $sData = Registry::getRequest()->getRequestParameter('Data');
        $oOrder = oxNew(Order::class);

        $aRequestParams = [
            'Len' => $iLen,
            'Data' => $sData,
        ];

        $oApiLog = oxNew(ApiLog::class);
        $aLog = [
            'request' => 'FAILURE_HOOK',
            'response' => 'FAILURE_HOOK',
            'creation_date' => date('Y-m-d H:i:s', time()),
            'payment_name' => PaypalExpress::paymentClass,
            'response_details' => json_encode($aRequestParams)
        ];

        $sErrorString = 'FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_ERROR';

        if (!empty($iLen) && !empty($sData)) {
            $oResponse = $this->fatchipComputopPaymentService->getDecryptedResponse($aRequestParams);
            $aRequestParams['raw'] = $oResponse->toArray();
            $aLog['response_details'] = json_encode($aRequestParams);
            $aLog['trans_id'] = $oResponse->getTransID();
            $aLog['pay_id'] = $oResponse->getPayID();
            if ($oOrder->load($oResponse->getTransID()) || $oOrder->load(Registry::getSession()->getId())) {
                $oOrder->oxorder__oxstorno = new Field(1);
                $oOrder->oxorder__oxtransstatus = new Field('ERROR');
                $oOrder->save();
            } else {
                Registry::getLogger()->error('PAYPAL_EXPRESS_FAILURE_HOOK: order not found transID: ' . $oResponse->getTransID());
            }
            if ($oResponse->getCode() === '21500053') {
                Registry::getSession()->cleanUpPPEOrder();
                $sErrorString = 'FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_CANCEL';
            }
        } else {
            Registry::getSession()->cleanUpPPEOrder();
            $aLog['request_details'] = 'INVALID PARAMS';
            $sErrorString = 'FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_FATAL_ERROR';
        }


        $oApiLog->assign($aLog);
        $oApiLog->save();

        Registry::getUtilsView()->addErrorToDisplay($sErrorString);
        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        $returnUrl = $sShopUrl . 'index.php?cl=payment';

        Registry::getUtils()->redirect($returnUrl, false, 301);
    }

    public function notify()
    {
        $iLen = Registry::getRequest()->getRequestParameter('Len');
        $sData = Registry::getRequest()->getRequestParameter('Data');

        $aRequestParams = [
            'Len' => $iLen,
            'Data' => $sData,
        ];

        $oApiLog = oxNew(ApiLog::class);
        $aLog = [
            'request' => 'NOTIFY_HOOK',
            'response' => 'NOTIFY_HOOK',
            'creation_date' => date('Y-m-d H:i:s', time()),
            'payment_name' => PayPalExpress::paymentClass,
            'response_details' => json_encode($aRequestParams)
        ];

        if (!empty($iLen) && !empty($sData)) {
            $oResponse = $this->fatchipComputopPaymentService->getDecryptedResponse($aRequestParams);
            $aRequestParams['raw'] = $oResponse->toArray();
            $aLog['response_details'] = json_encode($aRequestParams);
            $aLog['trans_id'] = $oResponse->getTransID();
            $aLog['pay_id'] = $oResponse->getPayID();
        } else {
            $aLog['request_details'] = 'INVALID PARAMS';
        }

        $oApiLog->assign($aLog);
        $oApiLog->save();

        exit;
    }

    /**
     * Update user and order info
     * @param CTResponse $oResponse
     * @param Order $oOrder
     * @return bool
     * @throws Exception
     */
    protected function updateUserAndOrderInfo(CTResponse $oResponse, Order &$oOrder): bool
    {
        $blOrderUserFound = true;
        $oUser = oxNew(User::class);
        if (!$oUser->load($oOrder->oxorder__oxuserid->value)) {
            $blOrderUserFound = false;
        }
        $oCountry = oxNew(Country::class);
        $sCountryId = $oCountry->getIdByCode($oResponse->getAddrCountryCode());
        $address =  $oResponse->getAddrStreet();
        if (empty($oResponse->getAddrStreetNr())) {
            $streetNr = $this->extractStreetNr($address);
            $street =  preg_replace('/\s*\d+.*$/', '', $address);
            $oResponse->setAddrStreet($street);
            $oResponse->setAddrStreetNr($streetNr);
        }
        if ($oResponse->getAddrStreetNr()) {
            $street =  preg_replace('/\s*\d+.*$/', '', $address);
            $oResponse->setAddrStreet($street);
        }
        //this condition if true indicate the user has been tmp created during the PaypalExpress createOrder action
        if ($this->stringStartWith($oUser->oxuser__oxusername->value, 'PAYPAL_TMP_USER')) {
            $sResponseEmailId = $oUser->getIdByUserName($oResponse->getEMail());
            if (!empty($sResponseEmailId)) {
                $oOrder->oxorder__oxuserid = new Field($sResponseEmailId);
                $oUser->delete();
                $oUser->load($sResponseEmailId);
            }
            $notApplicableFields = [
                'oxuser__oxustid'  => '',
                'oxuser__oxcompany' => '',
                'oxuser__oxaddinfo' => '',
                'oxuser__oxstateid' => '',
                'oxuser__oxaddinfo' => '',
                'oxuser__oxfon' => '',
                'oxuser__oxfax' => '',
                'oxuser__oxsal' => ''
            ];
            $billAddress = [
                'oxuser__oxusername' => $oResponse->getEMail(),
                'oxuser__oxfname' => $oResponse->getFirstName(),
                'oxuser__oxlname' => $oResponse->getLastName(),
                'oxuser__oxstreet' => $oResponse->getAddrStreet(),
                'oxuser__oxstreetnr' => $oResponse->getAddrStreetNr(),
                'oxuser__oxcity' => $oResponse->getAddrCity(),
                'oxuser__oxcountryid' => $sCountryId,
                'oxuser__oxzip' => $oResponse->getAddrZIP(),
            ];
            $oUser->assign(array_merge($notApplicableFields,$billAddress));
        }
        $delAdressPayPal = [
            'oxaddress__oxaddressuserid' => $oUser->getId(),
            'oxaddress__oxcompany' => '',
            'oxaddress__oxfname' => $this->getFirstName($oResponse),
            'oxaddress__oxlname' => $this->getLastName($oResponse),
            'oxaddress__oxstreet' => $oResponse->getAddrStreet(),
            'oxaddress__oxstreetnr' =>  $oResponse->getAddrStreetNr(),
            'oxaddress__oxcity' => $oResponse->getAddrCity(),
            'oxaddress__oxcountryid' => $sCountryId,
            'oxaddress__oxzip' => $oResponse->getAddrZIP(),
        ];
        $oAddress = oxNew(Address::class);
        $oAddress->setUser($oUser);
        $oAddress->assign($delAdressPayPal);
        $addressId = $oAddress->save();
        $oUser->setSelectedAddressId($addressId);
        Registry::getSession()->setVariable('deladrid',$addressId);
        Registry::getSession()->setVariable('blshowshipaddress','1');
        $bUserSaveState = $oUser->save();

        $oOrder->oxorder__fatchip_computop_xid = new Field($oResponse->getXID());

        // update order's bill info
        $oOrder->oxorder__oxbillemail = new Field($oResponse->getEMail());
        $oOrder->oxorder__oxbillfname = new Field($oResponse->getFirstName());
        $oOrder->oxorder__oxbilllname = new Field($oResponse->getLastName());
        $oOrder->oxorder__oxbillstreet = new Field($oResponse->getAddrStreet());
        $oOrder->oxorder__oxbillstreetnr = new Field($oResponse->getAddrStreetNr());
        $oOrder->oxorder__oxbillcity = new Field($oResponse->getAddrCity());
        $oOrder->oxorder__oxbillcountryid = new Field($sCountryId);
        $oOrder->oxorder__oxbillzip = new Field($oResponse->getAddrZIP());
        $oOrder->oxorder__oxbillcompany = new Field('');
        $oOrder->oxorder__oxbilladdinfo = new Field('');
        $oOrder->oxorder__oxbillfon = new Field('');
        $oOrder->oxorder__oxbillfax = new Field('');
        $oOrder->oxorder__oxbillsal = new Field('');
        $oOrder->oxorder__oxbillustid = new Field('');
        $oOrder->oxorder__oxbillstateid = new Field('');
        // update order's delivery info
        $oOrder->oxorder__oxdelemail = new Field($oResponse->getEMail());
        $oOrder->oxorder__oxdelfname = new Field($this->getFirstName($oResponse));
        $oOrder->oxorder__oxdellname = new Field($this->getLastName($oResponse));
        $oOrder->oxorder__oxdelstreet = new Field($oResponse->getAddrStreet());
        $oOrder->oxorder__oxdelstreetnr = new Field($oResponse->getAddrStreetNr());
        $oOrder->oxorder__oxdelcity = new Field($oResponse->getAddrCity());
        $oOrder->oxorder__oxdelcountryid = new Field($sCountryId);
        $oOrder->oxorder__oxdelzip = new Field($oResponse->getAddrZIP());
        $oOrder->oxorder__oxdelcompany = new Field('');
        $oOrder->oxorder__oxdeladdinfo = new Field('');
        $oOrder->oxorder__oxdelfon = new Field('');
        $oOrder->oxorder__oxdelfax = new Field('');
        $oOrder->oxorder__oxdelsal = new Field('');
        $oOrder->oxorder__oxdelstateid = new Field('');
        $paymentType = $oOrder->getPaymentType();

        $basket = Registry::getSession()->getBasket();
        $basket->setUser($oUser);
        $basket->setPayment($paymentType->getFieldData("oxuserpayments__oxpaymentsid"));
        Registry::getSession()->setUser($oUser);
        $oUser->login($oResponse->getEMail(),'',true);

        $bOrderSaveState = $oOrder->save();

        return $bUserSaveState && $bOrderSaveState;
    }

    protected function getFirstName(CTResponse $oResponse): string
    {
        if (!empty($oResponse->getName())) {
            return implode(' ', array_slice(explode(' ', (string)$oResponse->getName()), 0, -1));
        }
        return '';
    }

    protected function getLastName(CTResponse $oResponse): string
    {
        if (!empty($oResponse->getName())) {
            return array_slice(explode(' ', (string)$oResponse->getName()), -1)[0];
        }
        return '';
    }

    protected function extractStreetNr($address)
    {
        // Regex für eine Hausnummer (Zahlen + optionaler Buchstabe oder Zeichen)
        preg_match('/(\d+[\s]*[a-zA-Z]?)$/', $address, $matches);
        return $matches[1] ?? null;

    }

    protected function stringStartWith($haystack, $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    public function getFinishUrl()
    {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        $response = null;

        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }

        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        $stoken = $response->getRefNr();
        $returnUrl = $sShopUrl . 'index.php?cl=order&fnc=execute&FatchipComputopLen=' . $len . '&FatchipComputopData=' . $data
            . '&stoken=' . $stoken;
        return json_encode($returnUrl);
    }

    public function getResponse()
    {
        $response = false;
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');

        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }

        return $response;
    }

    /**
     * Display PayPal button
     */
    public function displayPayPalButton()
    {
        $mid = "YOUR_MERCHANTID";
        $len = "LEN_OF_UNENCRYPTED_BLOWFISH_STRING";
        $data = "BLOWFISH_ENCRYPTED_STRING";

        $params = [
            'MerchantID' => $mid,
            'Len' => $len,
            'Data' => $data,
        ];

        Registry::getUtils()->assignSmartyVariable('paypalParams', $params);

        $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypalexpress_button';
    }

    /**
     * Create PaypalExpress order
     * @ref fatchip_computop_paypalbutton.html.twig[createOrder]
     * @return void
     * @throws Exception
     */
    public function createOrder()
    {
        /** @var PaypalExpress $oPaypalExpressPaypment */
        $oPaypalExpressPaypment = $this->fatchipComputopPaymentService->getPaymentClass('PayPalExpress');
        $sTransId = $oPaypalExpressPaypment::generateTransID(12);
        $oApiLog = oxNew(ApiLog::class);
        $aLog = [
            'payment_name' => $oPaypalExpressPaypment::paymentClass,
            'creation_date' => date('Y-m-d H:i:s', time()),
            'trans_id' => $sTransId
        ];
        $oOrder = oxNew(Order::class);
        $oUser = oxNew(User::class);
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();
        $oBasket->setPayment('fatchip_computop_paypal_express');
        //TODO: make it configuraable
        $oBasket->setShipping('oxidstandard');

        if (!$oBasket->getProductsCount()) {
            Registry::getUtilsView()->addErrorToDisplay('FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_FATAL_ERROR');
            Registry::getUtils()->redirect($this->fatchipComputopShopConfig->getShopUrl() . 'index.php?=basket', false, 301);
        }
        $isLoaded = $oUser->loadActiveUser();
        //load user in case one is logged in
        if (!$isLoaded) {
            //create a temp user [paypal_guest]
            $oUser->oxuser__oxusername = new Field('PAYPAL_TMP_USER_' . $oSession->getId());
            $oUser->oxuser__oxfname = new Field('PAYPAL_TMP');
            $oUser->oxuser__oxlname = new Field('PAYPAL_TMP');
            $oUser->oxuser__oxstreet = new Field('PAYPAL_TMP');
            $oUser->oxuser__oxstreetnr = new Field('PAYPAL_TMP');
            $oUser->oxuser__oxzip = new Field('PAYPAL_TMP');
            $oUser->oxuser__oxcity = new Field('PAYPAL_TMP');
            $oUser->oxuser__oxcountryid = new Field('a7c40f631fc920687.20179984');
            $oUser->oxuser__oxcompany = new Field(' ');
            $oUser->oxuser__oxaddinfo = new Field(' ');
            $oUser->oxuser__oxustid = new Field(' ');
            $oUser->oxuser__oxstateid = new Field(' ');
            $oUser->oxuser__oxfon = new Field(' ');
            $oUser->oxuser__oxfax = new Field(' ');
            $oUser->oxuser__oxsal = new Field(' ');
            $oUser->save();
            $oBasket->setUser($oUser);
            Registry::getSession()->setUser($oUser);
            $oOrder->setUser($oUser);
        }

        try {
            $encodedDeliveryAdress = $oUser->getEncodedDeliveryAddress();
            $oDeliveryAddress = $oOrder->getDelAddressInfo();
            if ($oDeliveryAddress) {
                $encodedDeliveryAdress .= $oDeliveryAddress->getEncodedDeliveryAddress();
            }
            $_POST['sDeliveryAddressMD5'] = $encodedDeliveryAdress;
            $iOrderfinalizationState = $oOrder->finalizeOrder($oBasket, $oUser);
            //TODO: account for the possible error states
            /**
             * $iOrderfinalizationState === 1 -> email sending has failed, which is okay considering a temp user could be in use
             * ($iOrderfinalizationState === 0 ->
             */
            if ($iOrderfinalizationState === 0 || $iOrderfinalizationState === 1) {
                $oOrder->oxorder__oxtransstatus = new Field('NOT_FINISHED');
                $oOrder->save();
                //create ctorder
                $oCTOrder = new CTOrder();
                $oCTOrder->setAmount($oBasket->getPrice()->getBruttoPrice());
                $oCTOrder->setCurrency($oBasket->getBasketCurrency()->name);
                $oCTOrder->setTransID($sTransId);
                //$oCTOrder->setPayId($oOrder->oxorder__oxpaymentid->value);
                //$aLog['pay_id'] = $oCTOrder->getPayId();

                $aFrontendRequestParams = $oPaypalExpressPaypment->generateFrontendRequestParams($oCTOrder);

                $aLog['request'] = 'CREATEORDER_ACTION';
                $aLog['request_details'] = json_encode($aFrontendRequestParams);
                // raw used only for logging and therefor here will be removed.
                unset($aFrontendRequestParams['raw']);

                $aResponse = $this->httpPost($oPaypalExpressPaypment::CREATE_ORDER_URL, $aFrontendRequestParams);
                if (!empty($aResponse['response'])) {
                    parse_str($aResponse['response'], $parsedResponse);
                    $aResponse['raw'] = $parsedResponse;
                    $oOrder->oxorder__fatchip_computop_transid = new Field($aResponse['raw']['TransID'] ?? '');
                    $oOrder->oxorder__fatchip_computop_payid = new Field($aResponse['raw']['PayID'] ?? '');
                    $aLog['pay_id'] = $aResponse['raw']['PayID'] ?? '';
                    $oOrder->save();
                }

                $aLog['response_details'] = json_encode($aResponse);
                $oApiLog->assign($aLog);

                if (!$oApiLog->save()) {
                    Registry::getLogger()->error('createOrder: not able to save log, log dump - ' . print_r($aLog, true));
                }

                if ((int)$aResponse['status_code'] === 200) {
                    die($aResponse['response']);
                } else {
                    Registry::getUtilsView()->addErrorToDisplay('FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_FATAL_ERROR');
                    Registry::getUtils()->redirect($this->fatchipComputopShopConfig->getShopUrl() . 'index.php?=basket', false, 301);
                }
            } else {
                Registry::getUtilsView()->addErrorToDisplay('FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_FATAL_ERROR');
                Registry::getUtils()->redirect($this->fatchipComputopShopConfig->getShopUrl() . 'index.php?=basket', false, 301);
            }

        } catch (OutOfStockException $oEx) {
            Registry::getLogger()->error($oEx->getMessage());
            $oEx->setDestination('basket');
            Registry::getUtilsView()->addErrorToDisplay($oEx, false, true, 'basket');
        } catch (NoArticleException $oEx) {
            Registry::getLogger()->error($oEx->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($oEx);
        } catch (ArticleInputException $oEx) {
            Registry::getLogger()->error($oEx->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($oEx);
        }

        exit;
    }

    /**
     * Handle onApprove action
     * @return void
     * @throws Exception
     */
    public function onApprove()
    {
        $sMerchantId = Registry::getRequest()->getRequestParameter('merchantId');
        $sPayId = Registry::getRequest()->getRequestParameter('payId');
        $sOrderId = Registry::getRequest()->getRequestParameter('orderId');

        $aRequestParams = [
            'MerchantId' => $sMerchantId,
            'PayId' => $sPayId,
            'OrderId' => $sOrderId
        ];

        $oApiLog = oxNew(ApiLog::class);

        $aLog = [
            'request' => 'ON_APPROVE_ACTION',
            'request_details' => json_encode($aRequestParams),
            'creation_date' => date('Y-m-d H:i:s', time()),
            'payment_name' => PaypalExpress::paymentClass
        ];

        $oApiLog->assign($aLog);
        $oApiLog->save();

        $sTargetRedirectUrl = PaypalExpress::ON_APPROVE_URL . '?rd=' . base64_encode(http_build_query($aRequestParams));
        Registry::getLogger()->error('onApproveAction: ' . $sTargetRedirectUrl);
        header("Location: $sTargetRedirectUrl");
        exit;
    }

    /**
     * Handle onCancel Action
     * @return void
     */
    public function onCancel()
    {
        $mid = "YOUR_MERCHANTID";
        $payid = $data['orderID'];
        $rd = "MerchantId=$mid&PayId=$payid&OrderId=" . $data['orderID'];

        $url = 'https://www.computop-paygate.com/cbPayPal.aspx?rd=' . base64_encode($rd) . "&ua=cancel&token=" . $data['orderID'];
        header("Location: $url");
        exit;
    }

    public function onError()
    {

    }

    private function httpPost($url, $data, $headers = []): array
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Set default headers
        $defaultHeaders = [
            //'Content-Type: application/x-www-form-urlencoded'
        ];

        // Merge default headers with custom headers
        $allHeaders = array_merge($defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($error) {
            return [
                'status_code' => $statusCode,
                'error' => $error
            ];
        }

        return [
            'status_code' => $statusCode,
            'response' => $response
        ];
    }
}
