<?php

namespace Fatchip\ComputopPayments\Controller;

class FatchipComputopRedirect extends FatchipComputopPayments
{
    public function init()
    {
        ini_set('session.cookie_samesite', 'None');
        ini_set('session.cookie_secure', true);
    }
}
