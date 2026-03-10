<?php

namespace Fatchip\ComputopPayments\Controller;

use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Helper\Checkout;
use Fatchip\ComputopPayments\Helper\Config;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Core\Registry;

class FatchipComputopCancel extends FatchipComputopRedirect
{
    public function init()
    {
        parent::init();

        $sSessChallenge = Registry::getSession()->getVariable('sess_challenge');
        if (!empty($sSessChallenge)) {
            Checkout::getInstance()->cancelCurrentOrder();
        }
    }
}
