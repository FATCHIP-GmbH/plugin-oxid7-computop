<?php

namespace Fatchip\ComputopPayments\Model\Method;

use OxidEsales\Eshop\Application\Model\Order;

class PayPal extends RedirectPayment
{
    const ID = "fatchip_computop_paypal_standard";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'PaypalStandard';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "ExternalServices/paypalorders.aspx";

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @return array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        $params = [
            'TxType' => 'Auth',
            'mode' => 'redirect',
            'NoShipping' => "1",
        ];

        $ctAddress = $ctOrder ? $ctOrder->getShippingAddress() : null;
        if (!empty($ctAddress)) {
            $params['FirstName'] = $ctAddress->getFirstName();
            $params['LastName'] = $ctAddress->getLastName();
            $params['AddrStreet'] = $ctAddress->getStreet().' '.$ctAddress->getStreetNr();
            $params['AddrStreet2'] = $ctAddress->getStreet2();
            $params['AddrCity'] = $ctAddress->getCity();
            $params['AddrZip'] = $ctAddress->getZip();
            $params['AddrCountryCode'] = $ctAddress->getCountryCode();
        }

        return $params;
    }
}