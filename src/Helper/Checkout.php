<?php

namespace Fatchip\ComputopPayments\Helper;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Order;
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

    /**
     * Cancel current order because it failed i.e. because customer canceled payment
     *
     * @return void
     */
    public function cancelCurrentOrder()
    {
        $sSessChallenge = Registry::getSession()->getVariable('sess_challenge');

        $oOrder = oxNew(Order::class);
        if ($oOrder->load($sSessChallenge) === true) {
            if ($oOrder->oxorder__oxtransstatus->value != 'OK') {
                $oOrder->cancelOrder();
            }
        }
        Registry::getSession()->deleteVariable('sess_challenge');
    }
}
