<?php

namespace Fatchip\ComputopPayments\Model\Method\Ratepay;

use Fatchip\ComputopPayments\Helper\Api;
use Fatchip\ComputopPayments\Model\Method\ServerToServerPayment;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

abstract class Base extends ServerToServerPayment
{
    const METHOD_DIRECT_DEBIT = 'PAY_NOW';

    const METHOD_INVOICE = 'OPEN_INVOICE';

    const DEBIT_PAY_TYPE_DIRECT_DEBIT = 'SEPA_DIRECT_DEBIT';

    const DEBIT_PAY_TYPE_BANK_TRANSFER = 'BANK_TRANSFER';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "ratepay.aspx";

    /**
     * Determines if auth requests adds billing address parameters to the request
     *
     * @var bool
     */
    protected $addBillingAddressData = true;

    /**
     * Determines if auth requests adds shipping address parameters to the request
     *
     * @var bool
     */
    protected $addShippingAddressData = true;

    /**
     * @var string|null
     */
    protected $rpMethod = null;

    /**
     * @var string|null
     */
    protected $rpDebitPayType = null;

    /**
     * @param Order $order
     * @param       $dynValue
     * @return string
     */
    protected function getBirthday(Order $order, $dynValue)
    {
        $dynValueDay = $this->getDynValue($dynValue, "birthday");
        $dynValueMonth = $this->getDynValue($dynValue, "birthmonth");
        $dynValueYear = $this->getDynValue($dynValue, "birthyear");
        if (!empty($dynValueDay) && !empty($dynValueMonth) && !empty($dynValueYear)) {
            return $dynValueYear."-".$dynValueMonth."-".$dynValueDay;
        }

        return $order->getUser()->oxuser__oxbirthdate->value;
    }

    /**
     * @param string $artNr
     * @param string $name
     * @param float $price
     * @param string $currency
     * @param float $qty
     * @param float $taxRate
     * @return array
     */
    protected function getBasketItem($artNr, $name, $price, $currency, $qty, $taxRate)
    {
        return [
            'artNr' => $artNr,
            'name' => $name,
            'unitPriceGross' => Api::getInstance()->formatAmount($price, $currency),
            'quantity' => $qty,
            'taxRate' => $taxRate,
        ];
    }

    /**
     * Get basket items array for auth request
     *
     * @param Order $order
     * @return array
     */
    protected function getBasketItems(Order $order)
    {
        $items = [];

        $currency = $order->oxorder__oxcurrency->value;

        $orderItemList = $order->getOrderArticles();
        foreach ($orderItemList->getArray() as $orderItem) {
                $items[] = $this->getBasketItem(
                    $orderItem->oxorderarticles__oxartnum->value,
                    $orderItem->oxorderarticles__oxtitle->value,
                    $orderItem->oxorderarticles__oxbprice->value,
                    $currency,
                    $orderItem->oxorderarticles__oxamount->value,
                    $orderItem->oxorderarticles__oxvat->value,
                );
        }

        if ($order->oxorder__oxdelcost->value != 0) {
            $items[] = $this->getBasketItem(
                $order->oxorder__oxdeltype->value,
                Registry::getLang()->translateString('FATCHIP_COMPUTOP_SHIPPINGCOST').': '.$order->getDelSet()->oxdeliveryset__oxtitle->value,
                $order->oxorder__oxdelcost->value,
                $currency,
                1,
                $order->oxorder__oxdelvat->value,
            );
        }

        if ($order->oxorder__oxpaycost->value != 0) {
            $items[] = $this->getBasketItem(
                $order->oxorder__oxpaymenttype->value,
                Registry::getLang()->translateString('FATCHIP_COMPUTOP_PAYMENTTYPESURCHARGE').': '.$order->getPaymentType()->oxpayments__oxdesc->value,
                $order->oxorder__oxpaycost->value,
                $currency,
                1,
                $order->oxorder__oxpayvat->value,
            );
        }

        if ($order->oxorder__oxwrapcost->value != 0) {
            $items[] = $this->getBasketItem(
                'wrapping',
                Registry::getLang()->translateString('FATCHIP_COMPUTOP_WRAPPING'),
                $order->oxorder__oxwrapcost->value,
                $currency,
                1,
                round($order->oxorder__oxwrapvat->value, 0),
            );
        }

        if ($order->oxorder__oxgiftcardcost->value != 0) {
            $items[] = $this->getBasketItem(
                'giftcard',
                Registry::getLang()->translateString('FATCHIP_COMPUTOP_GIFTCARD').': '.$order->getGiftCard()->oxwrapping__oxname->value,
                $order->oxorder__oxgiftcardcost->value,
                $currency,
                1,
                $order->oxorder__oxgiftcardvat->value,
            );
        }

        if ($order->oxorder__oxvoucherdiscount->value != 0) {
            $items[] = $this->getBasketItem(
                'voucher',
                Registry::getLang()->translateString('FATCHIP_COMPUTOP_VOUCHER'),
                ($order->oxorder__oxvoucherdiscount->value * -1),
                $currency,
                1,
                $order->oxorder__oxartvat1->value,
            );
        }

        if ($order->oxorder__oxdiscount->value != 0) {
            $items[] = $this->getBasketItem(
                'discount',
                Registry::getLang()->translateString('FATCHIP_COMPUTOP_DISCOUNT'),
                ($order->oxorder__oxdiscount->value * -1),
                $currency,
                1,
                $order->oxorder__oxartvat1->value,
            );
        }

        return $items;
    }

    /**
     * Get shopping basket array for auth request
     *
     * @param Order $order
     * @return array
     */
    protected function getShoppingBasket(Order $order)
    {
        $items = $this->getBasketItems($order);

        $currency = $order->oxorder__oxcurrency->value;

        $shoppingBasket = [
            [ // Nested array is strange here, but it doesn't work without it
                'shoppingBasketAmount' => Api::getInstance()->formatAmount($order->oxorder__oxtotalordersum->value, $currency),
                'items' => $items,
                'vats' => [
                    [ // Nested array is strange here, but it doesn't work without it
                        'netAmount' => Api::getInstance()->formatAmount($order->oxorder__oxtotalnetsum->value, $currency),
                        'taxAmount' => Api::getInstance()->formatAmount($order->oxorder__oxartvatprice1->value + $order->oxorder__oxartvatprice2->value, $currency),
                        'taxRate' => $order->oxorder__oxartvat1->value,
                    ]
                ],
            ]
        ];
        return $shoppingBasket;
    }

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @return array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        $baseParams = [
            'RPMethod' => $this->rpMethod,
            'DebitPayType' => $this->rpDebitPayType,
            'Email' => $order->oxorder__oxbillemail->value,
            'Phone' => $this->getTelephoneNumber($dynValue),
            'shoppingBasket' => Api::getInstance()->encodeArray($this->getShoppingBasket($order)),
            'IPAddr' => Registry::getUtilsServer()->getRemoteAddress(),
            'Language' => Registry::getLang()->translateString('FATCHIP_COMPUTOP_LANGUAGE'),
        ];

        if (empty($order->oxorder__oxbillcompany->value)) {
            $birthday = $this->getBirthday($order, $dynValue);
            if (!empty($birthday)) {
                $baseParams['DateOfBirth'] = $birthday;
            }
        }

        $subTypeParams = $this->getSubTypeSpecificParameters($order, $dynValue);
        $params = array_merge($baseParams, $subTypeParams);

        return $params;
    }
}