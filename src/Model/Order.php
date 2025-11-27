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
 * along with Computop Oxid Plugin. If not, see <http://www.gnu.org/licenses/>.
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
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Helper\Api;
use Fatchip\ComputopPayments\Helper\Config;
use Fatchip\ComputopPayments\Helper\Payment;
use Fatchip\ComputopPayments\Model\Method\AmazonPay;
use Fatchip\ComputopPayments\Model\Method\Creditcard;
use Fatchip\ComputopPayments\Model\Method\DirectDebit;
use Fatchip\ComputopPayments\Model\Method\Easycredit;
use Fatchip\ComputopPayments\Model\Method\Ideal;
use Fatchip\ComputopPayments\Model\Method\Klarna;
use Fatchip\ComputopPayments\Model\Method\PayPal;
use Fatchip\ComputopPayments\Model\Method\PayPalExpress;
use Fatchip\ComputopPayments\Model\Method\Ratepay\Base;
use Fatchip\ComputopPayments\Model\Method\RedirectPayment;
use Fatchip\ComputopPayments\Model\Method\ServerToServerPayment;
use Fatchip\ComputopPayments\Repository\ApiLogRepository;
use Fatchip\CTPayment\CTAddress\CTAddress;
use Fatchip\CTPayment\CTEnums\CTEnumEasyCredit;
use Fatchip\CTPayment\CTEnums\CTEnumStatus;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethod;
use Fatchip\CTPayment\CTPaymentMethodsIframe\PaypalStandard;
use Fatchip\CTPayment\CTPaymentParams;
use Fatchip\CTPayment\CTPaymentService;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\PaymentGateway;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ModuleConfigurationDao;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;

use function date;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    protected $fatchipComputopLogger;

    protected $fatchipComputopPaymentService;

    /**
     * @var bool
     */
    protected $blComputopIsPPEInit = false;

    public $oxorder__fatchip_computop_transid;

    public $oxorder__fatchip_computop_payid;

    public $oxorder__fatchip_computop_xid;

    public $oxorder__fatchip_computop_lastschrift_mandateid;

    public $oxorder__fatchip_computop_lastschrift_dos;

    public $oxorder__fatchip_computop_creditcard_schemereferenceid;

    public $oxorder__fatchip_computop_amount_captured;

    public $oxorder__fatchip_computop_amount_refunded;

    public $oxorder__fatchip_computop_remark;

    // -----------------> START OXID CORE MODULE EXTENSIONS <-----------------

    /**
     * Class constructor, initiates parent constructor (parent::oxBase()).
     */
    public function __construct()
    {
        parent::__construct();

        $this->fatchipComputopLogger = new Logger();
        $this->fatchipComputopPaymentService = new CTPaymentService(Config::getInstance()->getConnectionConfig());
    }

    /**
     * Order checking, processing and saving method.
     * Before saving performed checking if order is still not executed (checks in
     * database oxorder table for order with know ID), if yes - returns error code 3,
     * if not - loads payment data, assigns all info from basket to new Order object
     * and saves full order with error status. Then executes payment. On failure -
     * deletes order and returns error code 2. On success - saves order (\OxidEsales\Eshop\Application\Model\Order::save()),
     * removes article from wishlist (\OxidEsales\Eshop\Application\Model\Order::_updateWishlist()), updates voucher data
     * (\OxidEsales\Eshop\Application\Model\Order::_markVouchers()). Finally sends order confirmation email to customer
     * (\OxidEsales\Eshop\Core\Email::SendOrderEMailToUser()) and shop owner (\OxidEsales\Eshop\Core\Email::SendOrderEMailToOwner()).
     * If this is order recalculation, skipping payment execution, marking vouchers as used
     * and sending order by email to shop owner and user
     * Mailing status (1 if OK, 0 on error) is returned.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket              Basket object
     * @param object                                     $oUser                Current User object
     * @param bool                                       $blRecalculatingOrder Order recalculation
     *
     * @return integer
     */
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        $ret = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        $paymentId = $oBasket->getPaymentId() ?: '';
        if (Payment::getInstance()->isComputopPaymentMethod($paymentId) === false || $ret === self::ORDER_STATE_PAYMENTERROR) {
            return $ret;
        }

        $ctPayment = $this->computopGetPaymentModel($paymentId);

        $len = Registry::getRequest()->getRequestParameter('FatchipComputopLen');
        $data = Registry::getRequest()->getRequestParameter('FatchipComputopData');
        $PostRequestParams = [
            'Len' => $len,
            'Data' => $data,
        ];

        /** @var CTResponse $responseDirect */
        $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);

        $status = $response->getStatus();
        $returning = false;
        if ($status !== null) {
            $returning = true;
        }

        if ($blRecalculatingOrder === false && $ret < 2) {
            if ($status === null) {
                if ($ctPayment instanceof ServerToServerPayment && !$ctPayment instanceof Easycredit) {
                    $this->fatchipComputopPaymentService->handleDirectPaymentResponse($response);
                } elseif ($ctPayment instanceof RedirectPayment && !$ctPayment instanceof PayPalExpress) {
                    $response = Registry::getSession()->getVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl');
                    Registry::getUtils()->redirect($response, false);
                    return $this->handleRedirectResponse($response);
                }
            } else {
                $returning = true;
            }
        }

        if ($ret === self::ORDER_STATE_ORDEREXISTS || $status !== null) {
            // check Status and set Order appropiatelay
            $ret = $this->finalizeRedirectOrder($oBasket, $oUser, $blRecalculatingOrder);
        }

        if ($returning) {
            $this->fatchipComputopLogger->logRequestResponse([], $ctPayment->getLibClassName(), 'REDIRECT-BACK', $response);

            // $this->customizeOrdernumber($response);
            $this->updateOrderAttributes($response);

            if ($ctPayment->isRefNrUpdateNeeded() === true) {
                $this->updateRefNrWithComputop();
            }

            $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSRESERVED);
            $this->autoCapture($oUser, false);
        }

        return $ret;
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
    public function updateRefNrWithComputop()
    {
        $ctPayment = $this->computopGetPaymentModel();
        if ($ctPayment->isIframeLibMethod() === true) {
            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass($ctPayment->getLibClassName(), Config::getInstance()->getConnectionConfig(), );
        } else {
            $payment = $this->fatchipComputopPaymentService->getPaymentClass($ctPayment->getLibClassName());
        }

        if ($ctPayment instanceof Klarna) {
            $payment->setTransID($this->getFieldData('fatchip_computop_transid'));
        }

        $RefNrChangeParams = $payment->getRefNrChangeParams($this->getFieldData('fatchip_computop_payid'), Api::getInstance()->getReferenceNumber($this->getFieldData('oxordernr')));
        $RefNrChangeParams['EtiId'] = CTPaymentParams::getUserDataParam();

        return $this->callComputopService(
            $RefNrChangeParams,
            $payment,
            'REFNRCHANGE',
            $payment->getCTRefNrChangeURL()
        );
    }

    /**
     * Checks if delivery set used for current order is available and active.
     * Throws exception if not available
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket basket object
     *
     * @return null
     */
    public function validateDelivery($oBasket)
    {
        if ($oBasket->getPaymentId() == 'fatchip_computop_paypal_express') {
            return;
        }
        return parent::validateDelivery($oBasket);
    }

    // -----------------> END OXID CORE MODULE EXTENSIONS <-----------------

    // -----------------> START CUSTOM MODULE FUNCTIONS <-----------------
    // @TODO: They ALL need a module function name prefix to not cross paths with other modules

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
            $this->computopPopulateBasket($oBasket);

            $iRet = $this->sendOrderByEmail($oUser, $oBasket, $oUserPayment);
        } else {
            $iRet = self::ORDER_STATE_OK;
        }

        return $iRet;
    }

    /**
     * Populates article property in Basketitems without checking stock because this has already been done before customer was redirected
     * Needed for scenario where last stock item of a product is bought.
     * Only execute when customer returned from payment.
     *
     * @param Basket $oBasket
     * @return void
     */
    protected function computopPopulateBasket($oBasket)
    {
        foreach ($oBasket->getContents() as $key => $oContent) {
            $oProd = $oContent->getArticle(false);
        }
    }

    public function updateComputopFatchipOrderStatus(string $orderStatus, array $data = [])
    {
        switch ($orderStatus) {
            case Constants::PAYMENTSTATUSPAID:
                $this->setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->setFieldData('oxpaid', date('Y-m-d H:i:s'));
                $this->setFieldData('oxtransstatus', 'OK');
                if (!empty($data)) {
                    $this->setFieldData('fatchip_computop_amount_captured', $data['captureAmount']);
                    $this->setFieldData('fatchip_computop_remark', 'Payment Completed');
                }
                $this->save();
                break;
            case Constants::PAYMENTSTATUSNOTCAPTURED:
                $this->setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->setFieldData('oxtransstatus', 'OK');
                $this->save();
                break;
            case Constants::PAYMENTSTATUSRESERVED:
                $this->setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->setFieldData('oxtransstatus', 'OK');
                if (!empty($data)) {
                    $this->setFieldData('fatchip_computop_remark', 'Auth OK  Capture pending');
                }
                $this->save();
                break;
            case Constants::PAYMENTSTATUSREVIEWNECESSARY:
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
        return Payment::getInstance()->isComputopPaymentMethod($this->getFieldData('oxpaymenttype'));
    }

    public function getCapturedAmount()
    {
        $capturedAmount = $this->getFieldData('fatchip_computop_amount_captured');

        if ($capturedAmount > 0.0) {
            return number_format($capturedAmount / 100, 2, '.', '');
        }

        $ctOrder = $this->createCTOrder();

        $ctPayment = $this->computopGetPaymentModel();
        if($ctPayment->isIframeLibMethod() === true) {
            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass($ctPayment->getLibClassName(), Config::getInstance()->getConnectionConfig(), $ctOrder);
        } else {
            $payment = $this->fatchipComputopPaymentService->getPaymentClass($ctPayment->getLibClassName());
        }

        $param = $payment->getInquireParams($this->getFieldData('fatchip_computop_payid'));

        try {
            $response = $payment->callComputop($param, $payment->getCTInquireURL());
        } catch (StandardException $e) {
            return 0.0;
        }

        $capturedAmount = (double) $response->getAmountCap();
        $this->assign(['fatchip_computop_amount_captured' => $capturedAmount]);
        $this->save();
        return number_format($capturedAmount / 100, 2, '.', '');
    }
    public function getRefundedAmount()
    {
        $refundedAmount = $this->getFieldData('fatchip_computop_amount_refunded');

        if ($refundedAmount > 0.0) {
            return number_format($refundedAmount / 10000, 2, '.', '');
        }
    }

    public function autoCapture($oUser = false, $force = false)
    {
        $captureAmount = $this->getFieldData('fatchip_computop_amount_captured');
        // captureAmount form field was set to readonly so that only the full amount can be captured, since no followup capture can be done as of now
        // So don't work with the value sent from the form at all for now
        #$requestCapture = Registry::getRequest()->getRequestParameter('captureAmount');
        $requestCapture = $this->oxorder__oxtotalordersum->value;


        $ctPayment = $this->computopGetPaymentModel();
        if ($ctPayment instanceof Ideal) { // Skip Auto Capture for these types TODO: Add property to paymentModel
            $this->logDebug('autoCapture: skipping for '.$ctPayment->getPaymentId());
            return;
        }

        // Check if auto-capture is enabled for the payment method
        if (!$force && !$this->isAutoCaptureEnabled()) {
            if ($ctPayment instanceof DirectDebit) {
                $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSNOTCAPTURED);
            }
            $this->logDebug('autoCapture: skipping for '.$ctPayment->getLibClassName());
            return;
        }

        if ($ctPayment->isRealAutoCaptureMethod() === true && $this->isAutoCaptureEnabled() === true) {
            // Order has already been captured
            $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSPAID);
            return;
        }

        if (empty($captureAmount) || $requestCapture !== null) {
            $captureResponse = $this->captureOrder($requestCapture);

            $this->handleCaptureResponse($captureResponse, $oUser);
            return $captureResponse;
        } else {
            $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSPAID);
        }
    }

    public function isAutoCaptureEnabled()
    {
        //FCRM_REFACTOR put into payment model
        $autoCaptureConfigKey = false;
        $autoCaptureValue = null;

        $ctPayment = $this->computopGetPaymentModel();

        switch ($ctPayment->getPaymentId()) {
            case AmazonPay::ID:
                $autoCaptureConfigKey = "amazonCaptureType"; // Amazon Pay does a real autocapture, no fake autocapture needed
                break;
            case Creditcard::ID:
                $autoCaptureConfigKey = 'creditCardCaption';
                break;
            case PayPal::ID:
                $autoCaptureConfigKey = 'paypalCaption';
                break;
            case PayPalExpress::ID:
                $autoCaptureConfigKey = 'paypalExpressCaption';
                break;
            case DirectDebit::ID:
                $autoCaptureConfigKey = 'lastschriftCaption';
                break;
            default:
                break;
        }

        if ($autoCaptureConfigKey !== false) {
            $autoCaptureValue = Config::getInstance()->getConfigParam($autoCaptureConfigKey);
        }
        return ($autoCaptureValue === 'AUTO');
    }

    private function handleCaptureResponse($captureResponse, $oUser)
    {
        $status = $captureResponse->getStatus();
        if ($captureResponse->getAmountCap() == "0") {
            $amount = $captureResponse->getAmountAuth();
        } else {
            $amount = $captureResponse->getAmountCap();
        }
        if ($status === 'OK') {
            $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSPAID, ['captureAmount' => $amount]);
        } elseif ($status === 'FAILED') {
            $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSREVIEWNECESSARY,
                $data = [
                    'errorCode' => $captureResponse->getCode(),
                    'errorMessage' => $captureResponse->getDescription()
                ]
            );
        }

        $this->logCaptureResponse($status, $captureResponse, $oUser); // Logging
    }

    private function logDebug(string $message, array $data = [], $oUser = null)  // Added $oUser parameter
    {
        if (Config::getInstance()->getConfigParam('debuglog') === 'extended') {
            if ($oUser === null) {
                $oUser = $this->getUser();
            }
            $customerId = $oUser ? $oUser->getId() : null; // Get customerId if user is available

            $logData = array_merge(
                [
                    'UserID' => $customerId,
                    'SessionID' => Registry::getSession()->getId()
                ],
                $data
            );

            $ctPayment = $this->computopGetPaymentModel();
            if (!empty($ctPayment)) {
                $logData['payment'] = $ctPayment->getLibClassName();
            }

            $this->fatchipComputopLogger->log(
                'DEBUG',
                $message,
                $logData
            );
        }
    }

    private function logCaptureResponse(string $status, $captureResponse, $oUser)
    {
        $logMessage = "autoCapture: ".$status." for ".$this->computopGetPaymentModel()->getLibClassName();
        if ($status === 'FAILED') {
            $logMessage .= ', setting order status to ' . Constants::PAYMENTSTATUSREVIEWNECESSARY;
        }

        $this->logDebug($logMessage, ['order' => $this->getFieldData('oxordernr'), 'CaptureResponse' => var_export($captureResponse, true)], $oUser);
    }


    /**
     * Update ordernumber with custom prefix and custom suffix
     *
     * @param CTResponse $response
     * @return Order $order new Order with updated ordernumber
     */
    public function customizeOrdernumber($response)
    {
        $ctPayment = $this->computopGetPaymentModel();

        $orderNumber = $this->getFieldData('oxordernr');
        $whitelist = '/[^a-zA-Z0-9]/';
        // make sure only 4 chars are used for pre and suffix
        $orderPrefix = preg_replace($whitelist, '', substr(Config::getInstance()->getConfigParam('prefixOrdernumber'), 0, 4));
        $orderSuffix = preg_replace($whitelist, '', substr(Config::getInstance()->getConfigParam('suffixOrdernumber'), 0, 4));
        $orderNumberLength = 11 - (strlen($orderPrefix) + strlen($orderSuffix));
        $orderNumberCut = substr($orderNumber, 0, $orderNumberLength);
        $newOrdernumber = $orderPrefix.$orderNumberCut.$orderSuffix;

        $this->setFieldData('oxordernr', $newOrdernumber);
        $this->save();

        $this->logDebug('customizeOrdernumber: updating orderNumber '.$orderNumber.' to '.$newOrdernumber);
    }

    public function updateOrderAttributes($response)
    {
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
    protected function captureOrder($amount = null)
    {
        $ctOrder = $this->createCTOrder();

        $ctPayment = $this->computopGetPaymentModel();
        if ($ctPayment->isIframeLibMethod() === true) {
            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass($ctPayment->getLibClassName(), Config::getInstance()->getConnectionConfig(), $ctOrder);
        } else {
            $payment = $this->fatchipComputopPaymentService->getPaymentClass($ctPayment->getLibClassName());
        }

        if ($amount === null) {
            $totalOrderSum = $this->oxorder__oxtotalordersum->value;
        } else {
            $totalOrderSum = ((double)$amount);
        }
        $orderSum = intval(round($totalOrderSum * 100));
        $payId = $this->getFieldData('fatchip_computop_payid');
        $transId = $this->getFieldData('fatchip_computop_transid');
        $xid = $this->getFieldData('fatchip_computop_xid');
        $schemerefid = $this->getFieldData('fatchip_computop_creditcard_schemereferenceid');

        $requestParams = $payment->getCaptureParams(
            $payId,
            $orderSum,
            $this->getFieldData('oxorder__oxcurrency'),
            $transId,
            $xid,
            'none',
            $schemerefid
        );

        $response = $this->callComputopService($requestParams, $payment, 'CAPTURE', $payment->getCTCaptureURL());
        if ($response->getStatus() !== 'FAILED') {
            $payId = $this->getFieldData('fatchip_computop_payid');
            $param = $payment->getInquireParams($payId);

            try {
                $response = $payment->callComputop($param, $payment->getCTInquireURL());
            } catch (StandardException $e) {
                return 0.0;
            }

            $capturedAmount = (double) $response->getAmountCap();
            $this->assign(['fatchip_computop_amount_captured' => $capturedAmount]);
            $this->save();
        }
        return $response;
    }

    /**
     * creates a CTAddress object from an oxid order
     * @return CTAddress
     * @throws \Exception
     */
    public function getCTAddress($deliveryAddress = false)
    {
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

    public function callComputopService($requestParams, $payment, $requestType, $url)
    {
        $repository = oxNew(ApiLogRepository::class);
        $paymentName = $payment::paymentClass;

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

        try {
            $repository->saveApiLog($logMessage);
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
     * @return boolean
     */
    public function handleAuthorization($amount)
    {
        $ctPayment = $this->computopGetPaymentModel();

        $dynValue = Registry::getSession()->getVariable('dynvalue');

        $shop = Registry::getConfig()->getActiveShop();
        $urlParams = $this->getDirectAuthUrlParams();
        $ctOrder = $this->createCTOrder();
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
            $urlParams['UrlCancel']
        );

        $classParams = $payment->getRedirectUrlParams();
        $paymentParams = $this->getAuthorizationParameters($dynValue, $payment, $ctOrder);
        $customParam = CTPaymentParams::getCustomParam($payment->getTransID(), $ctPayment->getPaymentId());
        $params = array_merge($classParams,$paymentParams, $customParam);

        $this->logDebug('Calling '.$payment->getCTPaymentURL($params), ['order' => var_export($ctOrder, true), 'params' => $params]);

        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'DirectRequest', $params);

        $url = $payment->getCTPaymentURL();
        if ($payment instanceof \Fatchip\CTPayment\CTPaymentMethodsIframe\EasyCredit) {
            $url = $payment->getCTCreditCheckURL();
        }

        $response = $payment->callComputop($params, $url);

        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse', $response);

        $this->fatchipComputopLogger->logRequestResponse($params, $ctPayment->getLibClassName(), 'DIRECT', $response);

        $success = $this->handleAuthorizationResponse($response);
        if ($success === true) {
            $this->updateOrderAttributes($response);

            $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSRESERVED);
            $this->autoCapture($this->getUser(), false);
        }

        return $success;
    }

    /**
     * Handle authorization of current order
     *
     * @return boolean
     */
    public function handleRedirectPayment($amount)
    {
        $ctPayment = $this->computopGetPaymentModel();

        $dynValue = Registry::getSession()->getVariable('dynvalue');
        $payment = $this->getPaymentClassForGatewayAction();
        $UrlParams = CTPaymentParams::getUrlParams($ctPayment->getPaymentId());

        $ctOrder = $this->createCTOrder();

        $redirectParams = $payment->getRedirectUrlParams();
        $payment->setBillToCustomer($ctOrder);
        if ($payment instanceof PaypalStandard) {
            $payment->setShippingAddress($ctOrder->getShippingAddress());
        }
        $paymentParams = $this->getAuthorizationParameters($dynValue, $payment, $ctOrder);
        $paymentParams['billToCustomer'] = $payment->getBillToCustomer();
        $customParam = CTPaymentParams::getCustomParam($payment->getTransID(), $ctPayment->getPaymentId());
        $params = array_merge($redirectParams, $paymentParams, $customParam, $UrlParams);

        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrlRequestParams', $params);

        $this->logDebug('Calling '.$payment->getCTPaymentURL($params), ['params' => $params]);

        $response = $payment->getHTTPGetURL($params);

        if ($ctPayment instanceof Creditcard) {
            if (in_array(Config::getInstance()->getConfigParam('creditCardMode'), ['IFRAME', 'PAYMENTPAGE'])) {
                $template = Config::getInstance()->getConfigParam('creditCardTemplate') ?? 'ct_responsive';

                $response .= '&template='.$template;
            }

            $this->fatchipComputopLogger->logRequestResponse($params, $ctPayment->getLibClassName(), $ctPayment->getRequestType(), $payment);

            Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);

            if (Config::getInstance()->getConfigParam('creditCardMode') === 'IFRAME') {
                $response = 'index.php?cl='.$ctPayment->getPaymentId().'&stoken='.Registry::getSession()->getSessionChallengeToken();
            }

            Registry::getUtils()->redirect($response, false);
        }

        $parts = parse_url($response);
        parse_str($parts['query'], $query);

        // Die Len und Data Werte ausgeben
        $len = $query['Len'];
        $data = $query['Data'];
        $PostRequestParams = [
            'Len'    => $len,
            'Data'   => $data,
        ];
        $responseDec = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);

        $this->fatchipComputopLogger->logRequestResponse($params, $ctPayment->getLibClassName(), $ctPayment->getRequestType(), $responseDec);

        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);
        Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectResponse', $responseDec);

        Registry::getUtils()->redirect($response, false);
    }

    public function createCTOrder()
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
            $sShopUrl = Registry::getConfig()->getShopUrl();
            $returnUrl = $sShopUrl . 'index.php?cl=payment';
            Registry::getUtils()->redirect($returnUrl, false, 301);
        }
        $ctOrder->setEmail($oUser->oxuser__oxusername->value);
        $ctOrder->setCustomerID($oUser->oxuser__oxcustnr->value);

        $shop = Registry::getConfig()->getActiveShop();

        // Mandatory for paypalStandard
        $orderDesc = $shop->oxshops__oxname->value.' '.$shop->oxshops__oxversion->value;
        if((bool)Config::getInstance()->getConfigParam('creditCardTestMode') === true) {
            $ctOrder->setOrderDesc('Test:0000');
        } else {
            $ctOrder->setOrderDesc($orderDesc);
        }
        return $ctOrder;
    }

    public function getDirectAuthUrlParams()
    {
        $sShopUrl = Registry::getConfig()->getShopUrl();
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

    /**
     * Instantiate an a object of the current payment model
     *
     * @return Method\BaseMethod
     * @throws Exception
     */
    public function computopGetPaymentModel($sPaymentType = null)
    {
        if ($sPaymentType === null) {
            $sPaymentType = $this->getFieldData('oxpaymenttype');
        }
        return Payment::getInstance()->getComputopPaymentModel($sPaymentType);
    }

    /**
     * @param array $dynValue
     * @param CTOrder $ctOrder
     * @return array
     * @throws Exception
     */
    public function getPaymentParams($dynValue, $ctOrder = false)
    {
        $paymentModel = $this->computopGetPaymentModel();

        return $paymentModel->getPaymentSpecificParameters($this, $dynValue, $ctOrder);
    }

    protected function getAuthorizationParameters($dynValue, $payment, $ctOrder = false)
    {
        $params = [];

        $ctPaymentModel = $this->computopGetPaymentModel();
        if ($ctPaymentModel->isBillingAddressDataNeeded() === true) {
            $params = array_merge($params, $this->getAddressParameters(true, 'bd'));
        }

        if ($ctPaymentModel->isShippingAddressDataNeeded() === true) {
            $billingAsDeliveryAddress = false;
            if ($this->oxorder__oxdellname && $this->oxorder__oxdellname->value == '') { // no delivery address given
                $billingAsDeliveryAddress = true;
            }
            $params = array_merge($params, $this->getAddressParameters($billingAsDeliveryAddress, 'sd'));
        }

        if (!empty($this->oxorder__oxordernr->value)) {
            $params['RefNr'] = Api::getInstance()->getReferenceNumber($this->oxorder__oxordernr->value);
        }

        $params['orderDesc'] = $payment->getTransID();

        $params = array_merge($params, $this->getPaymentParams($dynValue, $ctOrder));
        return $params;
    }

    /**
     * @param string $countryId
     * @return string
     */
    protected function getCountryCode($countryId)
    {
        $country = oxNew('oxcountry');
        $country->load($countryId);
        return $country->oxcountry__oxisoalpha2->value;
    }

    /**
     * @param OrderAddress|QuoteAddress $address
     * @param string                    $apiPrefix
     * @return array
     */
    protected function getAddressParameters($blBillingAddress = true, $apiPrefix = '')
    {
        $oxidPrefix = 'oxdel';
        if ($blBillingAddress === true) {
            $oxidPrefix = 'oxbill';
        }

        $params = [
            $apiPrefix.'FirstName' => $this->getFieldData($oxidPrefix.'fname'),
            $apiPrefix.'LastName' => $this->getFieldData($oxidPrefix.'lname'),
            $apiPrefix.'Zip' => $this->getFieldData($oxidPrefix.'zip'),
            $apiPrefix.'City' => $this->getFieldData($oxidPrefix.'city'),
            $apiPrefix.'CountryCode' => $this->getCountryCode($this->oxorder__oxbillcountryid->value),
            $apiPrefix.'Street' => $this->getFieldData($oxidPrefix.'street'),
            $apiPrefix.'StreetNr' => $this->getFieldData($oxidPrefix.'streetnr'),
        ];

        if (!empty($this->getFieldData($oxidPrefix.'company'))) {
            $params[$apiPrefix.'CompanyName'] = $this->getFieldData($oxidPrefix.'company');
        }

        if ($this->computopGetPaymentModel() instanceof Base) {
            $params[$apiPrefix.'ZIPCode'] = $this->getFieldData($oxidPrefix.'zip');
        }

        return $params;
    }

    /**
     * @param array $response
     * @return bool
     */
    public function handleAuthorizationResponse($response)
    {
        switch ($response->getStatus()) {
            case CTEnumStatus::OK:
            case CTEnumStatus::AUTHORIZED:
            case CTEnumStatus::AUTHORIZE_REQUEST:
                // TODO check for Code 000000
                return true;
        }
        return false;
    }

    public function handleRedirectResponse($response)
    {
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

    protected function getPaymentClassForGatewayAction()
    {
        // used
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
                ],
            );
        }

        $shop = Registry::getConfig()->getActiveShop();

        $urlParams =  CTPaymentParams::getUrlParams($ctPayment->getPaymentId());
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
            $urlParams['UrlCancel'] ?? null
        );
        return $payment;
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
     * @param $params
     * @return CTResponse
     */
    public function requestKlarna($params, $payment)
    {
        return $payment->prepareComputopRequest($params, $payment->getCTPaymentURL());
    }

    public function loadByTransId(string $transID) : bool
    {
        $aResult = DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC)
            ->select('SELECT `OXID` FROM `oxorder` WHERE `fatchip_computop_transid` = :transID',[
                'transID' => $transID
            ])->fetchAll();

        if (empty($aResult)) {
            return false;
        } else {
            return $this->load($aResult[0]['OXID']);
        }
    }

    /**
     * Used set ordernr a bit earlier than in Oxid core
     *
     * @return void
     */
    public function ctSetOrderNumber()
    {
        if (empty($this->getFieldData('oxordernr'))) {
            $this->setNumber();
        }
    }

    /**
     * @return bool
     */
    public function getComputopIsPPEInit()
    {
        return $this->blComputopIsPPEInit;
    }

    /**
     * @param  bool $blComputopIsPPEInit
     * @return void
     */
    public function setComputopIsPPEInit($blComputopIsPPEInit)
    {
        $this->blComputopIsPPEInit = $blComputopIsPPEInit;
    }

    /**
     * Send order to shop owner and user
     *
     * @param \OxidEsales\Eshop\Application\Model\User        $oUser    order user
     * @param \OxidEsales\Eshop\Application\Model\Basket      $oBasket  current order basket
     * @param \OxidEsales\Eshop\Application\Model\UserPayment $oPayment order payment
     *
     * @return bool
     */
    protected function sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null)
    {
        if ($this->getComputopIsPPEInit() === true) {
            // Dont send emails in PPE init mode
            return self::ORDER_STATE_OK;
        }
        return parent::sendOrderByEmail($oUser, $oBasket, $oPayment);
    }
}
