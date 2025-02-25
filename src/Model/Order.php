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
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
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
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;

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

    // -----------------> START OXID CORE MODULE EXTENSIONS <-----------------

    /**
     * Class constructor, initiates parent constructor (parent::oxBase()).
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
        $isFatchipComputopPayment = Constants::isFatchipComputopPayment($paymentId);
        if ($isFatchipComputopPayment === false) {
            return $ret;
        }

        $isFatchipComputopRedirectPayment = Constants::isFatchipComputopRedirectPayment($paymentId);
        $isFatchipComputopDirectPayment = Constants::isFatchipComputopDirectPayment($paymentId);
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
        if (
            $ret < 2 &&
            !$blRecalculatingOrder &&
            $isFatchipComputopPayment
        ) {
            $this->fatchipComputopPaymentId = $paymentId;
            $this->fatchipComputopPaymentClass = Constants::getPaymentClassfromId($paymentId);
            if ($status === null) {
                if ($isFatchipComputopDirectPayment) {
                    $this->fatchipComputopPaymentService->handleDirectPaymentResponse($response);
                } else if ($isFatchipComputopRedirectPayment){
                    $response = $this->fatchipComputopSession->getVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl');
                    Registry::getUtils()->redirect($response, false);
                    return $this->handleRedirectResponse($response);
                }
            } else {
                $returning = true;
            }
        }

        if ($ret === 3 || $response !== null) {
            // check Status and set Order appropiatelay
            $ret = $this->finalizeRedirectOrder($oBasket, $oUser, $blRecalculatingOrder);
        }
        if ($returning) {
            $this->fatchipComputopPaymentClass = Constants::getPaymentClassfromId($paymentId);
            $this->fatchipComputopLogger->logRequestResponse([], $this->fatchipComputopPaymentClass, 'REDIRECT-BACK', $response);

            // $this->customizeOrdernumber($response);
            $this->updateOrderAttributes($response);
            $this->updateComputopFatchipOrderStatus(Constants::PAYMENTSTATUSRESERVED);
            $this->autocapture($oUser, false);
        }

        return $ret;
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
            $iRet = $this->sendOrderByEmail($oUser, $oBasket, $oUserPayment);
        } else {
            $iRet = self::ORDER_STATE_OK;
        }

        return $iRet;
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
            case "FATCHIP_COMPUTOP_PAYMENTSTATUS_NOT_CAPTURED":
                $this->setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->setFieldData('oxtransstatus', 'OK');
                $this->save();
                break;
            case "FATCHIP_COMPUTOP_PAYMENTSTATUS_RESERVED":
                $this->setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->setFieldData('oxtransstatus', 'OK');
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

    public function getCapturedAmount()
    {
        $capturedAmount = $this->getFieldData('fatchip_computop_amount_captured');

        if ($capturedAmount > 0.0) {
            return number_format($capturedAmount / 100, 2, '.', '');
        }

        $this->fatchipComputopPaymentClass = Constants::getPaymentClassfromId($this->getFieldData('oxpaymenttype'));
        $ctOrder = $this->createCTOrder();
        if ($this->fatchipComputopPaymentClass === 'PayPalExpress') {
            $payment = $this->fatchipComputopPaymentService->getPaymentClass($this->fatchipComputopPaymentClass);
        } else {
            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass($this->fatchipComputopPaymentClass, $this->fatchipComputopConfig, $ctOrder);
        }
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
        $requestCapture = Registry::getRequest()->getRequestParameter('captureAmount');

        if ($this->fatchipComputopPaymentClass === null) {
            $this->fatchipComputopPaymentClass = Constants::getPaymentClassfromId($this->getFieldData('oxpaymenttype'));
        }

        // Skip Auto Capture if its iDEAL
        if ($this->fatchipComputopPaymentId === 'fatchip_computop_ideal') {
            $this->logDebug('autoCapture: skipping for ' . $this->fatchipComputopPaymentId, $oUser);
            return;
        }

        $this->fatchipComputopPaymentId = $this->getFieldData('oxpaymenttype');

        // Check if auto-capture is enabled for the payment method
        if (!$force && !$this->isAutoCaptureEnabled()) {
            if ($this->fatchipComputopPaymentId === 'fatchip_computop_lastschrift') {
                $this->updateComputopFatchipOrderStatus('FATCHIP_COMPUTOP_PAYMENTSTATUS_NOT_CAPTURED');
            }
            $this->logDebug('autoCapture: skipping for ' . $this->fatchipComputopPaymentClass,[], $oUser);
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
        $autoCaptureConfigKey = false;
        $autoCaptureValue = null;

        switch ($this->fatchipComputopPaymentId) {
            case 'fatchip_computop_amazonpay':
                $autoCaptureConfigKey = 'amazonCaptureType';
                break;
            case 'fatchip_computop_paypal_standard':
                $autoCaptureConfigKey = 'paypalCaption';
                break;
            case 'fatchip_computop_creditcard':
                $autoCaptureConfigKey = 'creditCardCaption';
                break;
            case 'fatchip_computop_paypal_express':
                $autoCaptureConfigKey = 'paypalExpressCaption';
                break;
            case 'fatchip_computop_lastschrift':
                $autoCaptureConfigKey = 'lastschriftCaption';
                break;
            default:
                break;
        }

        if ($autoCaptureConfigKey !== false) {
            $autoCaptureValue = $this->fatchipComputopConfig[$autoCaptureConfigKey] ?? null;
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
            $this->updateComputopFatchipOrderStatus(
                Constants::PAYMENTSTATUSPAID,
                ['captureAmount' => $amount]
            );
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
        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $sessionID = $this->fatchipComputopSession->getId();
            $customerId = $oUser ? $oUser->getId() : null; // Get customerId if user is available

            $this->fatchipComputopLogger->log(
                'DEBUG',
                $message,
                array_merge(['UserID' => $customerId, 'SessionID' => $sessionID], $data)
            );
        }
    }

    private function logCaptureResponse(string $status, $captureResponse, $oUser)
    {
        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $sessionID = $this->fatchipComputopSession->getId();
            $customerId = $oUser ? $oUser->getFieldData('oxcustnr') : null;
            $paymentClass = Constants::getPaymentClassfromId($this->fatchipComputopPaymentId);

            $logMessage = "autoCapture: {$status} for {$paymentClass}";
            if ($status === 'FAILED') {
                $logMessage .= ', setting order status to ' . Constants::PAYMENTSTATUSREVIEWNECESSARY;
            }

            $this->fatchipComputopLogger->log(
                'DEBUG',
                $logMessage,
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


    /**
     * Update ordernumber with custom prefix and custom suffix
     *
     * @param CTResponse $response
     * @return Order $order new Order with updated ordernumber
     */
    public function customizeOrdernumber($response)
    {
        $orderNumber = $this->getFieldData('oxordernr');
        $whitelist = '/[^a-zA-Z0-9]/';
        // make sure only 4 chars are used for pre and suffix
        $orderPrefix = preg_replace($whitelist, '', substr($this->fatchipComputopConfig['prefixOrdernumber'], 0, 4));
        $orderSuffix = preg_replace($whitelist, '', substr($this->fatchipComputopConfig['suffixOrdernumber'], 0, 4));
        $orderNumberLength = 11 - (strlen($orderPrefix) + strlen($orderSuffix));
        $orderNumberCut = substr($orderNumber, 0, $orderNumberLength);
        $newOrdernumber = $orderPrefix.$orderNumberCut.$orderSuffix;

        $this->setFieldData('oxordernr', $newOrdernumber);
        $this->save();

        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $oUser = $this->getUser();
            if ($oUser === false) {
                $customerId =    $this->getFieldData('oxuserid');
            } else {
                $customerId = $oUser->getFieldData('oxcustnr');
            }
            $sessionID = $this->fatchipComputopSession->getId();
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'customizeOrdernumber: updating orderNumber ' . $orderNumber . ' to ' . $newOrdernumber,
                ['payment' => $this->fatchipComputopPaymentClass, 'UserID' => $customerId, 'SessionID' => $sessionID]
            );
        }
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
        if ($this->fatchipComputopPaymentClass !== 'PayPalExpress') {
            $payment = $this->fatchipComputopPaymentService->getIframePaymentClass($this->fatchipComputopPaymentClass, $this->fatchipComputopConfig, $ctOrder);
        } else {
            $payment = $this->fatchipComputopPaymentService->getPaymentClass($this->fatchipComputopPaymentClass);
        }
        if ($amount === null) {
            $totalOrderSum =  $this->oxorder__oxtotalordersum->value;
        } else {
            $totalOrderSum =  ((double)$amount);
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
     * @param PaymentGateway|null $oPayGateway
     * @return boolean
     */
    public function handleAuthorization($amount, PaymentGateway $oPayGateway = null)
    {
        $dynValue = $this->fatchipComputopSession->getVariable('dynvalue');
        $oUser = $this->getUser();

        $ctOrder = $this->createCTOrder($amount);

        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $this->writeOrderLog($ctOrder);
        }
        $paymentId = $this->getFieldData('oxpaymenttype');
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
            CTPaymentParams::getUserDataParam(),
            null,
            null,
            null,
            $urlParams['UrlCancel']
        );

        $classParams = $payment->getRedirectUrlParams();
        $paymentParams = $this->getPaymentParams($payment, $dynValue);
        $customParam = CTPaymentParams::getCustomParam($payment->getTransID(), $paymentId);
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
        if ($paymentClass === 'EasyCredit') {
            $response = $payment->callComputop($params, $payment->getCTCreditCheckURL());
        } else {
            $response = $payment->callComputop($params, $payment->getCTPaymentURL());
        }
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse', $response);

        return $this->handleAuthorizationResponse($response);
    }

    /**
     * Handle authorization of current order
     *
     * @param PaymentGateway|null $oPayGateway
     * @return boolean
     */
    public function handleRedirectPayment($amount, PaymentGateway $oPayGateway = null)
    {
        $dynValue = $this->fatchipComputopSession->getVariable('dynvalue');
        $this->fatchipComputopPaymentId = $this->getFieldData('oxpaymenttype');
        $this->fatchipComputopPaymentClass = Constants::getPaymentClassfromId($this->getFieldData('oxpaymenttype'));
        $oUser = $this->getUser();
        $payment = $this->getPaymentClassForGatewayAction();
        $UrlParams = CTPaymentParams::getUrlParams($this->fatchipComputopPaymentId,$this->fatchipComputopConfig);

        $ctOrder = $this->createCTOrder();
        $redirectParams = $payment->getRedirectUrlParams();
        $payment->setBillToCustomer($ctOrder);
        if ($payment instanceof PaypalStandard) {
            $payment->setShippingAddress($ctOrder->getShippingAddress());
        }
        $paymentParams = $this->getPaymentParams($oUser, $dynValue, $ctOrder);
        $paymentParams['billToCustomer'] = $payment->getBillToCustomer();
        $customParam = CTPaymentParams::getCustomParam($payment->getTransID(),$this->fatchipComputopPaymentId);
        $params = array_merge($redirectParams, $paymentParams, $customParam, $UrlParams);
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrlRequestParams', $params);
        if ($this->fatchipComputopConfig['debuglog'] === 'extended') {
            $sessionID = $this->fatchipComputopSession->getId();
            $customerId = $oUser->getFieldData('oxcustnr');
            $order = var_export('', true);
            $paymentName = $this->fatchipComputopPaymentClass;
            $this->fatchipComputopLogger->log(
                'DEBUG',
                'Calling ' . $payment->getCTPaymentURL($params),
                [
                    'payment' => $paymentName,
                    'UserID' => $customerId,
                    'SessionID' => $sessionID,
                    'params' => $params
                ]
            );
        }
        $template = $this->fatchipComputopConfig['creditCardTemplate'] ?? 'ct_responsive';
        if ($this->fatchipComputopPaymentId === 'fatchip_computop_creditcard') {
            $this->fatchipComputopPaymentClass = 'CreditCard';
            if ($this->fatchipComputopConfig['creditCardMode'] === 'IFRAME') {

                $response = $payment->getHTTPGetURL($params);
                $response .= '&template='.$template;
                $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'IFrameURL', $response);

                $this->fatchipComputopLogger->logRequestResponse($params, $this->fatchipComputopPaymentClass, 'REDIRECT-IFRAME', $payment);

                $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);
                $returnUrl = 'index.php?cl='.$this->fatchipComputopPaymentId.'&stoken='.Registry::getSession()->getSessionChallengeToken();
                Registry::getUtils()->redirect($returnUrl);
            }
            if ($this->fatchipComputopConfig['creditCardMode'] === 'PAYMENTPAGE') {
                $response = $payment->getHTTPGetURL($params);
                $response .= '&template='.$template;
                $this->fatchipComputopLogger->logRequestResponse($params, $this->fatchipComputopPaymentClass, 'REDIRECT-PAYMENTPAGE', $payment);

                $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);
                Registry::getUtils()->redirect($response, false);
            }
            if ($this->fatchipComputopConfig['creditCardMode'] === 'SILENT') {
                $response = $payment->getHTTPGetURL($params);
                $this->fatchipComputopLogger->logRequestResponse($params, $this->fatchipComputopPaymentClass, 'REDIRECT-SILENT', $payment);
                $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);
                Registry::getUtils()->redirect($response, false);
            }
        }
        $response = $payment->getHTTPGetURL($params);
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

        $this->fatchipComputopLogger->logRequestResponse($params, $this->fatchipComputopPaymentClass, 'REDIRECT', $responseDec);
        // $this->fatchipComputopLogger->logRequestResponse($params, $this->fatchipComputopPaymentClass, 'REDIRECT-STANDARD', $payment);

        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl', $response);
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectResponse', $responseDec);

        Registry::getUtils()->redirect($response, false);
    }

    public function createCTOrder()
    {
        $ctOrder = new CTOrder();
        $oUser = $this->getUser();
        $config = oxNew(Config::class);
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
        if ($config->getCreditCardTestMode()) {
            $ctOrder->setOrderDesc('Test:0000');
        } else {
            $ctOrder->setOrderDesc($orderDesc);
        }
        return $ctOrder;
    }

    public function writeOrderLog($ctOrder)
    {
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

    public function getDirectAuthUrlParams()
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


    public function getPaymentParams($oUser, $dynValue, $ctOrder = false)
    {
        $CTAddress = $ctOrder ? $ctOrder->getShippingAddress() : null;
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
                    'order' => 'AUTO',
                    'TaxAmount' => $taxAmount,
                    'ArticleList' => $aOrderlines,
                    'Account' => $this->fatchipComputopConfig['klarnaaccount'],
                    'bdCountryCode' => $oxisoalpha2,
                ];
            case "fatchip_computop_easycredit":
                $oSession = Registry::getSession();
                if ($oSession->getVariable('fatchip_computop_TransId')) {
                    $sessionDecisionPayId = Registry::getSession()->getVariable('fatchipComputopEasyCreditPayId');
                    $payId =  $sessionDecisionPayId->getPayID();
                    return [
                        'payID' => $payId,
                        'EventToken' => CTEnumEasyCredit::EVENTTOKEN_CON,
                    ];
                } else {
                    return [
                        'DateOfBirth' => $dynValue['fatchip_computop_easycredit_birthdate_year'] . '-' . $dynValue['fatchip_computop_easycredit_birthdate_month'] . '-' . $dynValue['fatchip_computop_easycredit_birthdate_day'],
                        'EventToken' => CTEnumEasyCredit::EVENTTOKEN_INIT,
                    ];
                }
            case "fatchip_computop_paypal_standard":
                return [
                    'TxType' => 'Auth',
                    'mode' => 'redirect',
                    'NoShipping' => "1",
                    'FirstName' => $CTAddress->getFirstName(),
                    'LastName' => $CTAddress->getLastName(),
                    'AddrStreet' => $CTAddress->getStreet().' '.$CTAddress->getStreetNr(),
                    'AddrStreet2' => $CTAddress->getStreet2(),
                    'AddrCity' => $CTAddress->getCity(),
                    'AddrZip' => $CTAddress->getZip(),
                    'AddrCountryCode' =>$CTAddress->getCountryCode(),
                ];
            case "fatchip_computop_ideal":
                if ($this->fatchipComputopConfig['idealDirektOderUeberSofort'] === 'PPRO') {
                    return [];
                } else {
                    return [];
                }
            case "fatchip_computop_creditcard":
                return [
                    'RefNr' => Registry::getSession()->getSessionChallengeToken(),
                    'UserData' => Registry::getSession()->getId()
                ];
            case "fatchip_computop_paypal_express":
                return [
                    'NONE YET',
                ];
        }
        return [];
    }

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

    /**
     * Returns and brings basket positions into appropriate form
     *
     * @return array<int, array{reference: mixed, name: mixed, quantity: mixed, unit_price: float|int, tax_rate: float|int, total_amount: float|int}>
     */
    public function getKlarnaOrderlinesParams(): string
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

    protected function getPaymentClassForGatewayAction()
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

        $urlParams = CTPaymentParams::getUrlParams($this->fatchipComputopPaymentId,$this->fatchipComputopConfig);
        $payment = $this->fatchipComputopPaymentService->getIframePaymentClass(
            Constants::getPaymentClassfromId($this->fatchipComputopPaymentId),
            $this->fatchipComputopConfig,
            $ctOrder,
            $urlParams['UrlSuccess'],
            $urlParams['UrlFailure'],
            $urlParams['UrlNotify'],
            $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxname->value . ' '
            . $this->fatchipComputopShopConfig->getActiveShop()->oxshops__oxversion->value,
            CTPaymentParams::getUserDataParam(),
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
}
