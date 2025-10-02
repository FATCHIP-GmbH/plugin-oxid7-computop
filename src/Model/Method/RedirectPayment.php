<?php

namespace Fatchip\ComputopPayments\Model\Method;

abstract class RedirectPayment extends BaseMethod
{
    /**
     * @var string
     */
    protected $requestType = "REDIRECT";
}