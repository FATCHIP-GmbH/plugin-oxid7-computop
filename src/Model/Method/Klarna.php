<?php

namespace Fatchip\ComputopPayments\Model\Method;

use Fatchip\ComputopPayments\Helper\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;

class Klarna extends RedirectPayment
{
    const ID = "fatchip_computop_klarna";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'KlarnaPayments';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "KlarnaPaymentsHPP.aspx";

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @return array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        $aOrderlines = $this->getKlarnaOrderlinesParams();
        $taxAmount = $this->calculateTaxAmount($aOrderlines);

        return [
            'order' => 'AUTO',
            'TaxAmount' => $taxAmount,
            'ArticleList' => $aOrderlines,
            'Account' => Config::getInstance()->getConfigParam('klarnaaccount'),
            'bdCountryCode' => $this->getCountryCode($order),
        ];
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

    /**
     * Returns and brings basket positions into appropriate form
     *
     * @return array<int, array{reference: mixed, name: mixed, quantity: mixed, unit_price: float|int, tax_rate: float|int, total_amount: float|int}>
     */
    public function getKlarnaOrderlinesParams(): string
    {
        $basket = Registry::getSession()->getBasket();
        foreach ($basket->getContents() as $oBasketItem) {
            $oArticle = $oBasketItem->getArticle();
            $articleListArray['order_lines'][] = [
                'reference' => $oArticle->oxarticles__oxartnum->value,
                'name' => $oBasketItem->getTitle(),
                'quantity' => (int)$oBasketItem->getAmount(),
                'unit_price' => (int)($oBasketItem->getUnitPrice()->getBruttoPrice() * 100),
                'tax_rate' => (int)($oBasketItem->getVatPercent() * 100),
                'total_amount' => (int)($oBasketItem->getPrice()->getBruttoPrice() * 100 * $oBasketItem->getAmount()),
                'total_tax_amount' => (int)round((($oBasketItem->getPrice()->getBruttoPrice() - $oBasketItem->getPrice()->getNettoPrice()) * 100))
            ];
        }

        $oDelivery = $basket->getCosts('oxdelivery');
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

    protected function getCountryCode(Order $order)
    {
        $oCountry = oxNew(Country::class);
        $oCountry->load($order->getUser()->getFieldData('oxcountryid'));
        return $oCountry->getFieldData('oxisoalpha2');
    }
}
