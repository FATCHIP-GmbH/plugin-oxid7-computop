<?php

namespace Fatchip\ComputopPayments\Helper;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Core\Registry;


class Checkout
{
    /**
     * @var Checkout
     */
    protected static $instance = null;

    /**
     * Create singleton instance of this payment helper
     *
     * @return Checkout
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = oxNew(self::class);
        }
        return self::$instance;
    }

    public function getShippingAddressFromSession()
    {
        if (empty(Registry::getSession()->getVariable('deladrid'))) {
            return null;
        }

        $oDelAddress = oxNew(Address::class);
        $oDelAddress->load(Registry::getSession()->getVariable('deladrid'));
        return $oDelAddress;
    }
}
