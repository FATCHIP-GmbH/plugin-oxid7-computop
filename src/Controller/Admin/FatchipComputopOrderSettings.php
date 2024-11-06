<?php

namespace Fatchip\ComputopPayments\Controller\Admin;

use Exception;
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\DeliverySet;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Field;

class FatchipComputopOrderSettings extends AdminDetailsController
{
    /**
     * Template to be used
     *
     * @var string
     */
    protected $_sTemplate = "@fatchip_computop_payments/fatchip_computop_order_settings";

    /**
     * Order object
     *
     * @var Order|null
     */
    protected $_oOrder = null;

    /**
     * Error message property
     *
     * @var string|bool
     */
    protected $_sErrorMessage = false;

    /**
     * Compuop ApiOrder
     *
     */
    protected $_oCompuopApiOrder = null;

    /**
     * Flag if a successful refund was executed
     *
     * @var bool|null
     */
    protected $_blSuccessfulRefund = null;

    /**
     * Array of refund items
     *
     * @var array|null
     */
    protected $_aRefundItems = null;

    /**
     * All voucher types
     *
     * @var array
     */
    protected $_aVoucherTypes = ['voucher', 'discount'];
    /**
     * Returns errormessage
     *
     * @return bool|string
     */
    public function getErrorMessage()
    {
        return $this->_sErrorMessage;
    }
    /**
     * Sets error message
     *
     * @param string $sError
     */
    public function setErrorMessage($sError)
    {
        $this->_sErrorMessage = $sError;
    }
    /**
     * Loads current order
     *
     * @return null|object|Order
     */
    public function getOrder()
    {
        if ($this->_oOrder === null) {
            $oOrder = oxNew(Order::class);

            $soxId = $this->getEditObjectId();
            if (isset($soxId) && $soxId != "-1") {
                $oOrder->load($soxId);

                $this->_oOrder = $oOrder;
            }
        }

        return $this->_oOrder;
    }

    /**
     * Returns if refund was successful
     *
     * @return bool
     */
    public function wasRefundSuccessful()
    {
        return $this->_blSuccessfulRefund;
    }

    /**
     * Main render method
     *
     * @return string
     */
    public function render()
    {
        parent::render();
        $oOrder = $this->getOrder();
        if ($oOrder) {
            $this->_aViewData["edit"] = $oOrder;
        }

        return $this->_sTemplate;
    }

    /**
     * Format prices to always have 2 decimal places
     *
     * @param double $dPrice
     * @return string
     */
    protected function formatPrice($dPrice)
    {
        return number_format($dPrice, 2, '.', '');
    }


    /**
     * Checks if this order has had a free amount refund
     *
     * @return bool
     */
    public function hasHadFreeAmountRefund()
    {
        $oOrder = $this->getOrder();
        /*   foreach ($oOrder->getOrderArticles() as $oOrderArticle) {
               if (((double)$oOrderArticle->oxorderarticles__Compuopamountrefunded->value > 0 && $oOrderArticle->oxorderarticles__Compuopquantityrefunded->value == 0)
                   || ($oOrderArticle->oxorderarticles__Compuopquantityrefunded->value * $oOrderArticle->oxorderarticles__oxbprice->value != $oOrderArticle->oxorderarticles__Compuopamountrefunded->value)) {
                   return true;
               }
           }*/
        return false;
    }

    /**
     * Get refund type - quantity or amount
     *
     * @return string
     */
    public function getRefundType()
    {
        $sType = "amount"; // Payment API
        if ($this->isComputopOrderApi() === true && $this->hasHadFreeAmountRefund() === false) {
            $sType = "quantity"; // Order API
        }
        return $sType;
    }
    public function refundSpecificArticles() {
        $oOrder = $this->getOrder();
        $aArticleArray = Registry::getRequest()->getRequestParameter('aArtId');
        $oOrderArticles = $oOrder->getOrderArticles()->getArray();
        $refundAmount = 0;
        $articleChecked = false;

        foreach ($aArticleArray as $article) {
            if ($this->isArticleSelectedForRefund($article)) {
                $articleChecked = true;
                $refundAmount += $this->processArticleRefund($oOrderArticles, $article);

                if ($this->shouldRefundShipping($article)) {
                    $refundAmount += $this->processShippingRefund($oOrder);
                }
            }
        }

        $this->handleRefundOutcome($articleChecked, $refundAmount);
    }

    private function isArticleSelectedForRefund($article) {
        return isset($article['refundthis']) && $article['refundthis'] === 'on';
    }

    private function shouldRefundShipping($article) {
        return $article['shipping'] === '1' && $article['refundthis'] === 'on';
    }

    private function processArticleRefund($aOrderArticle, $article) {
        $refundAmount = 0;

        if (isset($aOrderArticle[$article['oxid']])) {
            $orderArticle = $aOrderArticle[$article['oxid']];
            if ($orderArticle->getFieldData('fatchip_computop_amount_refunded') != 1) {
                $refundAmount = $orderArticle->getFieldData('oxorderarticles__oxbrutprice');
                $orderArticle->assign(['fatchip_computop_amount_refunded' => 1]);
                $orderArticle->save();
            }
        }

        return $refundAmount;
    }

    private function processShippingRefund($oOrder) {
        $oOrder->assign(['fatchip_computop_shipping_amount_refunded' => 1]);
        $oOrder->save();
        return $oOrder->getFormattedDeliveryCost();
    }

    private function handleRefundOutcome($articleChecked, $refundAmount) {
        if (!$articleChecked) {
            $this->setErrorMessage(Registry::getLang()->translateString('COMPUTOP_ARTICLE_REFUNDED_NO_ARTICLES_CHECKED'));
        } elseif ($refundAmount > 0) {
            $refundAmount = $this->getAmountForComputop($refundAmount);
            $this->refundOrderArticles($refundAmount);
        } else {
            $this->setErrorMessage(Registry::getLang()->translateString('COMPUTOP_ARTICLE_REFUNDED_NO_ARTICLES_TO_REFUND'));
        }
    }
    public function refundOrderArticles($amount = false) {
        try {

            $configCT = new Config();
            $config = Registry::getConfig();
            $paymentService = new CTPaymentService($configCT->toArray());
            $oOrder = $this->getOrder();
            $ctOrder = $this->createCTOrder($oOrder, $config);
            if ($amount === false) {
                $amount = $this->getAmountForComputop( $oOrder->getTotalOrderSum());
            }
            $payId = $oOrder->getFieldData('fatchip_computop_payid');
            $transId = $oOrder->getFieldData('fatchip_computop_transid');
            $currency = $oOrder->getOrderCurrency()->name;
            $orderDesc = $config->getActiveShop()->oxshops__oxname->value . ' ' . $config->getActiveShop()->oxshops__oxversion->value;
            if($configCT->getCreditCardTestMode()) {
                $ctOrder->setOrderDesc('Test:0000');
            } else {
                $ctOrder->setOrderDesc($orderDesc);

            }
            $params = $this->getRefundParams($payId, $amount, $currency, $transId, null, $orderDesc);
            $paymentId = $oOrder->getFieldData('OXPAYMENTTYPE');
            $paymentClass = Constants::getPaymentClassfromId($paymentId);

            $payment = null;
            if($paymentClass === 'PayPalExpress'){
                $payment = $paymentService->getPaymentClass($paymentClass);
            }else{
                $payment = $paymentService->getIframePaymentClass(
                    $paymentClass,
                    $configCT->toArray(),
                    $ctOrder
                );
            }


            $response = $this->callComputopRefundService($oOrder, $params, $payment);
            $this->handleRefundResponse($oOrder,$response, $amount);
        } catch (Exception $e) {
            $this->setErrorMessage($e->getMessage());
        }
    }

    protected function handleRefundResponse($oOrder, $response, $amount) {
        if ($response->getStatus() === 'OK') {
            if ($oldAmount = (double)$oOrder->getFieldData('fatchip_computop_amount_refunded') / 100) {
                $amount = $oldAmount + (double)$amount;
            }
            $oOrder->assign(['fatchip_computop_amount_refunded' => $this->getAmountForComputop($amount)]);
            $oOrder->save();
            $this->_blSuccessfulRefund = true;
        } else {
            $this->setErrorMessage('Refund Status:'. $response->getStatus());
        }
    }
    protected function createCTOrder($oOrder) {
        $ctOrder = new CTOrder();
        $config = Registry::getConfig();
        $oUser = $oOrder->getUser();
        $configCT = oxNew(Config::class);

        $ctOrder->setAmount((int)(round($oOrder->getFieldData('oxtotalordersum') * 100)));
        $ctOrder->setCurrency($oOrder->getFieldData('oxcurrency'));

        try {
            $ctOrder->setBillingAddress($oOrder->getCTAddress());
            $delAddressExists = !empty($oOrder->getFieldData('oxdelstreet'));
            $ctOrder->setShippingAddress($oOrder->getCTAddress($delAddressExists));
        } catch (Exception $e) {
            $this->handleAddressError();
        }

        $ctOrder->setEmail($oUser->oxuser__oxusername->value);
        $ctOrder->setCustomerID($oUser->oxuser__oxcustnr->value);
        $orderDesc = $config->getActiveShop()->oxshops__oxname->value . ' ' . $config->getActiveShop()->oxshops__oxversion->value;
        if($configCT->getCreditCardTestMode()) {
            $ctOrder->setOrderDesc('Test:0000');
        } else {
            $ctOrder->setOrderDesc($orderDesc);

        }
        return $ctOrder;
    }

    protected function handleAddressError() {
        Registry::getUtilsView()->addErrorToDisplay('FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_ERROR_ADDRESS');
    }

    protected function getAmountForComputop($amount) {
        return $amount * 100;
    }

    public function getRefundParams($payId, $amount, $currency, $transId = null, $xId = null, $orderDesc = null, $klarnaInvNo = null, $schemeReferenceId = null, $orderAmount = null) {
        $reason = $amount < $orderAmount ? 'WIDERRUF_TEILWEISE' : 'WIDERRUF_VOLLSTAENDIG';
        return [
            'payID' => $payId,
            'amount' => $amount,
            'currency' => $currency,
            'Date' => date("Y-m-d"),
            'transID' => $transId,
            'xID' => $xId,
            'orderDesc' => $orderDesc,
            'invNo' => $klarnaInvNo,
            'schemeReferenceID' => $schemeReferenceId,
            'Reason' => $reason,
            'Custom' => $this->Custom,
        ];
    }

    protected function callComputopRefundService($oOrder, $params, $payment) {
        try {
            $response = $oOrder->callComputopService($params, $payment, 'REFUND', $payment->getCTRefundURL());

            if (!$response) {
                $this->setErrorMessage('Refund service failed');
            }
            return $response;
        } catch (Exception $e) {
            $this->setErrorMessage($e->getMessage());
        }
    }
    protected function getRefundItemsFromRequest()
    {
        $sSelectKey = $sSelectKey = 'refund_'.$this->getRefundType();
        $aRefundItems = Registry::getRequest()->getRequestEscapedParameter('aOrderArticles');
        foreach ($aRefundItems as $sKey => $aRefundItem) {
            foreach ($aRefundItem as $sItemKey => $item) {
                $aRefundItem[$sItemKey] = str_replace(',', '.', $aRefundItem[$sItemKey]);
            }
            if (isset($aRefundItem[$sSelectKey])) {
                $dValue = $aRefundItem[$sSelectKey];
                $aRefundItems[$sKey] = [$sSelectKey => $aRefundItem[$sSelectKey]];
            } else {
                $dValue = $aRefundItem['refund_amount'];
            }
            if ($dValue <= 0) {
                unset($aRefundItems[$sKey]);
            }
            $aBasketItem = $this->getRefundItemById($sKey);

            if ($aBasketItem['type'] == 'product') {
                $sCounterSelectKey = 'refund_amount';
                if ($sSelectKey == 'refund_amount') {
                    $sCounterSelectKey = 'refund_quantity';
                }
                if (isset($aRefundItem[$sCounterSelectKey])) {
                    unset($aRefundItems[$sKey][$sCounterSelectKey]);
                }
            }
        }
        return $aRefundItems;
    }

    /**
     * Returns single refund item by id
     *
     * @param string $sId
     * @return array|null
     */
    protected function getRefundItemById($sId)
    {
        $aRefundItems = $this->getRefundItems();
        foreach ($aRefundItems as $aRefundItem) {
            if ($aRefundItem['id'] == $sId) {
                return $aRefundItem;
            }
        }
        return null;
    }

    /**
     * Compuop needs its own line id for the refund so we have to collect it
     *
     * @param string $sId
     * @return string
     */
    protected function getCompuopLineIdFromApi($sId)
    {
        $oCompuopApiOrder = $this->getCompuopApiOrder();
        if ($oCompuopApiOrder instanceof \Compuop\Api\Resources\Order) {
            $aLines = $oCompuopApiOrder->lines();
            foreach ($aLines as $oLine) {
                if ($oLine->sku == $sId) {
                    return $oLine->id;
                }
            }
        }
        return $sId;
    }

    /**
     * Returns remaining refundable amount from Compuop Api
     *
     * @return double
     */
    public function getRemainingRefundableAmount()
    {
        $oCompuopApiOrder = $this->getCompuopApiOrder(true);

        $dAmount = 0;
        if ($oCompuopApiOrder && $oCompuopApiOrder->amount && $oCompuopApiOrder->amount->value) {
            $dAmount = $oCompuopApiOrder->amount->value;
        }

        $dAmountRefunded = 0;
        if ($oCompuopApiOrder && $oCompuopApiOrder->amountRefunded && $oCompuopApiOrder->amountRefunded->value) {
            $dAmountRefunded = $oCompuopApiOrder->amountRefunded->valu;
        }
        return ($dAmount - $dAmountRefunded);
    }

    /**
     * Generate refund lines for the Compuop API request
     *
     * @return array
     */
    protected function getPartialRefundParameters()
    {
        $aParams = ['lines' => []];
        $dAmount = 0;

        $aRefundItems = $this->getRefundItemsFromRequest();
        foreach ($aRefundItems as $sId => $aRefundItem) {
            $aBasketItem = $this->getRefundItemById($sId);
            if ($aBasketItem['type'] == 'product') {
                $sId = $aBasketItem['artnum']; // Compuop doesnt know the orderarticles id - only the artnum
            }

            $aLine = ['id' =>$sId];
            if (isset($aRefundItem['refund_amount'])) {
                if ($aRefundItem['refund_amount'] > $aBasketItem['totalPrice']) { // check if higher amount than payed
                    $aRefundItem['refund_amount'] = $aBasketItem['totalPrice'];
                }
                if (in_array($aBasketItem['type'], $this->_aVoucherTypes)) {
                    $aRefundItem['refund_amount'] = $aRefundItem['refund_amount'] * -1;
                }
                $aLine['amount'] = [
                    "currency" => $this->getOrder()->oxorder__oxcurrency->value,
                    "value" => $this->formatPrice($aRefundItem['refund_amount'])
                ];
                $dAmount += $aRefundItem['refund_amount'];
            } elseif (isset($aRefundItem['refund_quantity'])) {
                if ($aRefundItem['refund_quantity'] > $aBasketItem['quantity']) { // check if higher quantity than payed
                    $aRefundItem['refund_quantity'] = $aBasketItem['quantity'];
                }
                $aLine['quantity'] = $aRefundItem['refund_quantity'];
                $dAmount = $aRefundItem['refund_quantity'] * $aBasketItem['singlePrice'];
            }
            $aParams['lines'][] = $aLine;
        }
        $aParams['amount'] = [
            "currency" => $this->getOrder()->oxorder__oxcurrency->value,
            "value" => $this->formatPrice($dAmount)
        ];

        return $aParams;
    }

    /**
     * Generate request parameter array
     *
     * @param bool   $blFull
     * @param double $dFreeAmount
     * @return array
     */
    protected function getRefundParameters($blFull = true, $dFreeAmount = null)
    {
        if(!empty($dFreeAmount)) {
            $aParams = ["amount" => [
                "currency" => $this->getOrder()->oxorder__oxcurrency->value,
                "value" => $this->formatPrice($dFreeAmount)
            ]];
        } elseif($blFull === false) {
            $aParams = $this->getPartialRefundParameters();
        } else {
            $dAmount = $this->getOrder()->oxorder__oxtotalordersum->value;
            if (!empty(Registry::getRequest()->getRequestEscapedParameter('refundRemaining'))) {
                $dAmount = $this->getRemainingRefundableAmount();
            }

            $aParams = ["amount" => [
                "currency" => $this->getOrder()->oxorder__oxcurrency->value,
                "value" => $this->formatPrice($dAmount)
            ]];
        }

        $sDescription = Registry::getRequest()->getRequestEscapedParameter('refund_description');
        if (!empty($sDescription)) {
            $aParams['description'] = $sDescription;
        }
        return $aParams;
    }

    /**
     * Fills refunded db-fields for partially refunded products and costs
     *
     * @return void
     */
    protected function markOrderPartially()
    {
        $aRefundItems = $this->getRefundItemsFromRequest();
        $aOrderArticles = $this->getOrder()->getOrderArticles();

        $oOrder = $this->getOrder();

        foreach ($aRefundItems as $sId => $aRefundItem) {
            foreach ($aOrderArticles as $oOrderArticle) {
                if ($oOrderArticle->getId() == $sId) {
                    if (isset($aRefundItem['refund_amount'])) {
                        if ($aRefundItem['refund_amount'] > $oOrderArticle->oxorderarticles__oxbrutprice->value) {
                            $aRefundItem['refund_amount'] = $oOrderArticle->oxorderarticles__oxbrutprice->value;
                        }
                        $oOrderArticle->oxorderarticles__Compuopamountrefunded = new Field((double)$oOrderArticle->oxorderarticles__Compuopamountrefunded->value += $aRefundItem['refund_amount']);
                    } elseif (isset($aRefundItem['refund_quantity'])) {
                        if ($aRefundItem['refund_quantity'] > $oOrderArticle->oxorderarticles__oxamount->value) {
                            $aRefundItem['refund_quantity'] = $oOrderArticle->oxorderarticles__oxamount->value;
                        }
                        $oOrderArticle->oxorderarticles__Compuopquantityrefunded = new Field((int)$oOrderArticle->oxorderarticles__Compuopquantityrefunded->value += $aRefundItem['refund_quantity']);
                        $oOrderArticle->oxorderarticles__Compuopamountrefunded = new Field((double)$oOrderArticle->oxorderarticles__Compuopamountrefunded->value += $aRefundItem['refund_quantity'] * $oOrderArticle->oxorderarticles__oxbprice->value);
                    }
                    $oOrderArticle->save();
                    continue 2;
                }
            }

            if (isset($aRefundItem['refund_amount'])) {
                $aBasketItem = $this->getRefundItemById($sId);
                if ($aRefundItem['refund_amount'] > $aBasketItem['totalPrice']) { // check if higher amount than payed
                    $aRefundItem['refund_amount'] = $aBasketItem['totalPrice'];
                }

                $oOrder = $this->updateRefundedAmounts($oOrder, $aBasketItem['type'], $aRefundItem['refund_amount']);
            }

        }
        $oOrder->save();

        $this->_oOrder = $oOrder; // update order for renderering the page
        $this->_aRefundItems = null;
    }

    /**
     * Updated refunded amounts of order object
     *
     * @param object $oOrder
     * @param string $sType
     * @param double $dAmount
     * @return object
     */
    protected function updateRefundedAmounts($oOrder, $sType, $dAmount)
    {
        if ($sType == 'shipping_fee') {
            $oOrder->oxorder__Compuopdelcostrefunded = new Field((double)$oOrder->oxorder__Compuopdelcostrefunded->value + $dAmount);
        } elseif ($sType == 'payment_fee') {
            $oOrder->oxorder__Compuoppaycostrefunded = new Field((double)$oOrder->oxorder__Compuoppaycostrefunded->value + $dAmount);
        } elseif ($sType == 'wrapping') {
            $oOrder->oxorder__Compuopwrapcostrefunded = new Field((double)$oOrder->oxorder__Compuopwrapcostrefunded->value + $dAmount);
        } elseif ($sType == 'giftcard') {
            $oOrder->oxorder__Compuopgiftcardrefunded = new Field((double)$oOrder->oxorder__Compuopgiftcardrefunded->value + $dAmount);
        } elseif ($sType == 'voucher') {
            $oOrder->oxorder__Compuopvoucherdiscountrefunded = new Field((double)$oOrder->oxorder__Compuopvoucherdiscountrefunded->value + $dAmount);
        } elseif ($sType == 'discount') {
            $oOrder->oxorder__Compuopdiscountrefunded = new Field((double)$oOrder->oxorder__Compuopdiscountrefunded->value + $dAmount);
        }
        return $oOrder;
    }

    /**
     * Fills refunded db-fields with full costs
     *
     * @return void
     */
    protected function markOrderAsFullyRefunded()
    {
        $oOrder = $this->getOrder();
        $oOrder->oxorder__Compuopdelcostrefunded = new Field($oOrder->oxorder__oxdelcost->value);
        $oOrder->oxorder__Compuoppaycostrefunded = new Field($oOrder->oxorder__oxpaycost->value);
        $oOrder->oxorder__Compuopwrapcostrefunded = new Field($oOrder->oxorder__oxwrapcost->value);
        $oOrder->oxorder__Compuopgiftcardrefunded = new Field($oOrder->oxorder__oxgiftcardcost->value);
        $oOrder->oxorder__Compuopvoucherdiscountrefunded = new Field($oOrder->oxorder__oxvoucherdiscount->value);
        $oOrder->oxorder__Compuopdiscountrefunded = new Field($oOrder->oxorder__oxdiscount->value);
        $oOrder->save();

        foreach ($this->getOrder()->getOrderArticles() as $oOrderArticle) {
            $oOrderArticle->oxorderarticles__Compuopamountrefunded = new Field($oOrderArticle->oxorderarticles__oxbrutprice->value);
            $oOrderArticle->save();
        }

        $this->_oOrder = $oOrder; // update order for renderering the page
        $this->_aRefundItems = null;
    }

    /**
     * Fills refunded db-fields with free amount
     *
     * @param double $dFreeAmount
     * @return void
     */
    protected function markOrderWithFreeAmount($dFreeAmount)
    {
        $oOrder = $this->getOrder();
        foreach ($oOrder->getOrderArticles() as $oOrderArticle) {
            if ($oOrderArticle->oxorderarticles__Compuopamountrefunded->value < $oOrderArticle->oxorderarticles__oxbrutprice->value) {
                $dRemaining = $oOrderArticle->oxorderarticles__oxbrutprice->value - $oOrderArticle->oxorderarticles__Compuopamountrefunded->value;
                if ($dRemaining > $dFreeAmount) {
                    $oOrderArticle->oxorderarticles__Compuopamountrefunded->value = new Field($oOrderArticle->oxorderarticles__Compuopamountrefunded->value + $dFreeAmount);
                    $oOrderArticle->save();
                    break;
                } else {
                    $oOrderArticle->oxorderarticles__Compuopamountrefunded = new Field($oOrderArticle->oxorderarticles__oxbrutprice->value);
                    $oOrderArticle->save();
                    $dFreeAmount -= $dRemaining;
                }
            }
        }

        $this->_oOrder = null; // update order for renderering the page
        $this->_aRefundItems = null;
    }

    /**
     * Returns Compuop payment object or in case of Order API the method retrieves the payment object from the order object
     *
     * @return \Compuop\Api\Resources\Order|Payment
     */
    protected function getCompuopPaymentTransaction()
    {
        $oApiObject = $this->getCompuopApiOrder();
        if ($oApiObject instanceof \Compuop\Api\Resources\Order) {
            $aPayments = $oApiObject->payments();
            if (!empty($aPayments)) {
                $oApiObject = $aPayments[0];
            }
        }
        return $oApiObject;
    }


    public function captureManual() {
        $oUser = $this->getOrder()->getUser();
        $this->getOrder()->fatchipComputopPaymentId = $this->getOrder()->getFieldData('oxpaymenttype');

        if  ($this->getOrder()->isAutoCaptureEnabled()) {
        //    $this->setErrorMessage('Capture Status: Autocapture Disabled');
  //          return false;
        }
        $result =   $this->getOrder()->autoCapture($oUser, true);
        if ($result) {
            if ($result->getStatus() === 'FAILED') {
                $status = $result->getStatus();
                $description = $result->getDescription();
                $this->setErrorMessage('Capture Status: '.$status.' Description: '.$description);
            }
        }

    }
    /**
     * Return Compuop api order
     *
     * @param bool $blRefresh
     * @return \Compuop\Api\Resources\Order|Payment
     */
    protected function getCompuopApiOrder($blRefresh = false)
    {
        if ($this->_oCompuopApiOrder === null || $blRefresh === true) {
            $this->_oCompuopApiOrder = $this->getCompuopApiRequestModel()->get($this->getOrder()->oxorder__oxtransid->value, ["embed" => "payments"]);
        }
        return $this->_oCompuopApiOrder;
    }

    /**
     * Check Compuop API if order is refundable
     *
     * @return bool
     */
    public function isOrderRefundable()
    {
        if ($this->wasRefundSuccessful() === true && Registry::getRequest()->getRequestEscapedParameter('fnc') == 'fullRefund') {
            // the Compuop order is not updated instantly, so this is used to show that the order was fully refunded already
            return false;
        }

        //  $oApiOrder = $this->getCompuopApiOrder();

        //  if (empty($oApiOrder->amountRefunded) || $oApiOrder->amountRefunded->value != $oApiOrder->amount->value) {
        //       return true;
        //  }
        return false;
    }

    /**
     * Returns refunded amount from Compuop API
     *
     * @return string
     */
    public function getAmountRefunded()
    {
        $oApiOrder = $this->getCompuopApiOrder();

        $dPrice = 0;
        if ($oApiOrder && !empty($oApiOrder->amountRefunded)) {
            $dPrice = $oApiOrder->amountRefunded->value;
        }
        return $this->getFormatedPrice($dPrice);
    }

    /**
     * Returns remaining amount from Compuop API
     *
     * @return string
     */
    public function getAmountRemaining()
    {
        $oApiOrder = $this->getCompuopApiOrder();

        $dPrice = 0;
        if ($oApiOrder) {
            if (!empty($oApiOrder->amountRemaining)) {
                $dPrice = $oApiOrder->amountRemaining->value;
            } else {
                $dPrice = $this->getRemainingRefundableAmount();
            }
        }
        return $this->getFormatedPrice($dPrice);
    }

    /**
     * Checks if order was payed with Compuop
     *
     * @return bool
     */
    public function isComputopOrder()
    {

        return Constants::isFatchipComputopPayment($this->getOrder()->oxorder__oxpaymenttype->value);
    }


    /**
     * Get refunded amount formated
     *
     * @return string
     */
    public function getFormatedPrice($dPrice)
    {
        $oLang = Registry::getLang();
        $oOrder = $this->getOrder();
        $oCurrency = Registry::getConfig()->getCurrencyObject($oOrder->oxorder__oxcurrency->value);

        return $oLang->formatCurrency($dPrice, $oCurrency);
    }

    /**
     * Translate item type from basket item array
     *
     * @param  array $aBasketItem
     * @return string
     */
    protected function getTypeFromBasketItem($aBasketItem)
    {
        if (in_array($aBasketItem['type'], array('shipping_fee', 'discount'))) {
            return $aBasketItem['type'];
        }

        if (in_array($aBasketItem['sku'], array('wrapping', 'giftcard', 'voucher'))) {
            return $aBasketItem['sku'];
        }

        return 'payment_fee';
    }

    /**
     * Returns previously refunded amount by type
     *
     * @param string $sType
     * @return double
     */
    protected function getAmountRefundedByType($sType)
    {
        $oOrder = $this->getOrder();
        /*    switch ($sType) {
                case 'shipping_fee':
                    return $oOrder->oxorder__Compuopdelcostrefunded->value;
                case 'payment_fee':
                    return $oOrder->oxorder__Compuoppaycostrefunded->value;
                case 'wrapping':
                    return $oOrder->oxorder__Compuopwrapcostrefunded->value;
                case 'giftcard':
                    return $oOrder->oxorder__Compuopgiftcardrefunded->value;
                case 'voucher':
                    return $oOrder->oxorder__Compuopvoucherdiscountrefunded->value;
                case 'discount':
                    return $oOrder->oxorder__Compuopdiscountrefunded->value;
            }*/
        return 0;
    }

    /**
     * Returns still refundable amount by type
     *
     * @param string $sType
     * @return int
     */
    protected function getRefundableAmountByType($sType)
    {
        $oOrder = $this->getOrder();
        switch ($sType) {
            case 'shipping_fee':
                return $oOrder->oxorder__oxdelcost->value - $oOrder->oxorder__Compuopdelcostrefunded->value;
            case 'payment_fee':
                return $oOrder->oxorder__oxpaycost->value - $oOrder->oxorder__Compuoppaycostrefunded->value;
            case 'wrapping':
                return $oOrder->oxorder__oxwrapcost->value - $oOrder->oxorder__Compuopwrapcostrefunded->value;
            case 'giftcard':
                return $oOrder->oxorder__oxgiftcardcost->value - $oOrder->oxorder__Compuopgiftcardrefunded->value;
            case 'voucher':
                return $oOrder->oxorder__oxvoucherdiscount->value - $oOrder->oxorder__Compuopvoucherdiscountrefunded->value;
            case 'discount':
                return $oOrder->oxorder__oxdiscount->value - $oOrder->oxorder__Compuopdiscountrefunded->value;
        }
        return 0;
    }

    /**
     * Returns Compuop payment or order Api
     *
     * @return EndpointAbstract
     */
    protected function getCompuopApiRequestModel()
    {
        $oOrder = $this->getOrder();
        return $oOrder->CompuopGetPaymentModel()->getApiEndpointByOrder($oOrder);
    }

    /**
     * Generate item array for shipping, payment, etc
     *
     * @return array
     */
    protected function getOtherItemsFromOrder()
    {
        $aItems = array();

        $oOrder = $this->getOrder();
        // $oRequestModel = $oOrder->CompuopGetPaymentModel()->getApiRequestModel($oOrder);
        $aBasketItems = $oOrder->getOrderArticles()->getArray();
        foreach ($aBasketItems as $aBasketItem) {
            if (in_array($aBasketItem['type'], array('physical', 'digital'))) {
                continue; // skip order articles
            }
            $sType = $this->getTypeFromBasketItem($aBasketItem);
            if (in_array($sType, array('voucher', 'discount'))) {
                $aBasketItem['totalAmount']['value'] = $this->formatPrice($aBasketItem['totalAmount']['value'] * -1);
                $aBasketItem['unitPrice']['value'] = $aBasketItem['totalAmount']['value'];
            }
            $aItems[] = array(
                'id' => $aBasketItem['sku'],
                'type' => $sType,
                'artnum' => $aBasketItem['sku'],
                'title' => $aBasketItem['name'],
                'singlePrice' => $aBasketItem['unitPrice']['value'],
                'totalPrice' => $aBasketItem['totalAmount']['value'],
                'vat' => $aBasketItem['vatRate'],
                'amountRefunded' => $this->getAmountRefundedByType($sType),
                'refundableAmount' => $this->formatPrice($this->getRefundableAmountByType($sType)),
                'isOrderarticle' => false,
                'isPartialAllowed' => in_array($sType, ['voucher', 'discount']) ? false : true
            );
        }
        return $aItems;
    }

    /**
     * Check if quantity controls can be shown
     * Can only be shown as long as no partial refunds with a free money amount was used
     *
     * @return bool
     */
    public function isQuantityAvailable()
    {
        foreach ($this->getOrder()->getOrderArticles() as $orderArticle) {
            if ((double)$orderArticle->oxorderarticles__Compuopamountrefunded->value > 0 && fmod($orderArticle->oxorderarticles__Compuopamountrefunded->value, $orderArticle->oxorderarticles__oxbprice->value) != 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Map order article values to item array
     *
     * @return array
     */
    protected function getItemsFromOrderArticles()
    {
        $aItems = array();
        $refundYes = Registry::getLang()->translateString('COMPUTOP_ARTICLE_REFUNDED_YES');
        $refundNo = Registry::getLang()->translateString('COMPUTOP_ARTICLE_REFUNDED_NO');
        $oOrder = $this->getOrder();
        $oOrderArticles =$oOrder->getOrderArticles();
        $deliverySet = $oOrder->getDelSet();

        /** @var OrderArticle $orderArticle */
        foreach ($oOrderArticles as $orderArticle) {
            /* $quantityRefunded = $orderArticle->oxorderarticles__Compuopquantityrefunded->value;
             if ($orderArticle->oxorderarticles__Compuopamountrefunded->value == $orderArticle->oxorderarticles__oxbrutprice->value) {
                 $quantityRefunded = $orderArticle->oxorderarticles__oxamount->value;
             }*/
            $refund = $orderArticle->getFieldData('fatchip_computop_amount_refunded') === 1 ? $refundYes : $refundNo;
            $refundDel = $oOrder->getFieldData('fatchip_computop_shipping_amount_refunded') === 1 ? $refundYes : $refundNo;
            $aItems[] = array(
                'id' => $orderArticle->getId(),
                'type' => 'product',
                'refundableQuantity' => $orderArticle->getFieldData('oxamount'),
                'refundableAmount' => (float)$this->formatPrice($orderArticle->getFieldData('oxbprice')),
                'artnum' => $orderArticle->oxorderarticles__oxartnum->value,
                'title' => $orderArticle->oxorderarticles__oxtitle->value,
                'singlePrice' => $orderArticle->oxorderarticles__oxbprice->value,
                'totalPrice' => $orderArticle->oxorderarticles__oxbrutprice->value,
                'vat' => $orderArticle->oxorderarticles__oxvat->value,
                'quantity' => $orderArticle->oxorderarticles__oxamount->value,
                'refunded' => $refund,
                'isOrderarticle' => true,
                'isPartialAllowed' => true
            );
        }
        $shippingCost = $oOrder->getFormattedDeliveryCost();
        if ($shippingCost !== "0,00") {
            $aItems[] = array(
                'id' => $deliverySet->getId(),
                'type' => $deliverySet->getFieldData('oxtitle'),
                'refundableQuantity' => $orderArticle->getFieldData('oxamount'),
                'refundableAmount' => (float)$this->formatPrice( (double) $oOrder->getFormattedDeliveryCost()),
                'artnum' => $deliverySet->getFieldData('oxpos'),
                'title' => $deliverySet->getFieldData('oxtitle'),
                'singlePrice' => $oOrder->getFormattedDeliveryCost(),
                'totalPrice' => $oOrder->getFormattedDeliveryCost(),
                'vat' => $oOrder->getFieldData('oxvat'),
                'quantity' => 1,
                'refunded' => $refundDel,
                'isOrderarticle' => false,
                'isPartialAllowed' => true
            );

        }

        return $aItems;
    }

    /**
     * Returns if the order includes vouchers or discounts
     *
     * @return bool
     */
    public function hasOrderVoucher()
    {
        $aRefundItems = $this->getRefundItems();
        foreach ($aRefundItems as $aRefundItem) {
            if (in_array($aRefundItem['type'], $this->_aVoucherTypes)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Collect all refund items
     *
     * @return array
     */
    public function getRefundItems()
    {
        if ($this->_aRefundItems === null) {
            $this->_aRefundItems = $this->getItemsFromOrderArticles();
        }
        return $this->_aRefundItems;
    }

    /**
     * Triggers sending Compuop second chance email
     *
     * @return void
     */
    public function sendSecondChanceEmail()
    {
        $oOrder = $this->getOrder();
        if ($oOrder && $oOrder->CompuopIsCompuopPaymentUsed()) {
            $oOrder->CompuopSendSecondChanceEmail();
        }
    }

}
