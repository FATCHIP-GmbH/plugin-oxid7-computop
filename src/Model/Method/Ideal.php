<?php

namespace Fatchip\ComputopPayments\Model\Method;

use Fatchip\ComputopPayments\Helper\Config;
use OxidEsales\Eshop\Application\Model\Order;

class Ideal extends RedirectPayment
{
    const ID = "fatchip_computop_ideal";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'Ideal';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "ideal.aspx";

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @return array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        if (Config::getInstance()->getConfigParam('idealDirektOderUeberSofort') === 'PPRO') {
            return [];
        }
        return [];
    }
}