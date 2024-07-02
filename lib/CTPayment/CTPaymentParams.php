<?php

namespace Fatchip\CTPayment;

/**
 * Class CTPaymentMethods
 * @package Fatchip\CTPayment
 */
class CTPaymentParams
{
    const authParams = [
        'merchantId',
        'blowfishPassword',
        'hmac',
        'encryption'
    ];
    const generalParams =
        [
            'amount',
            'currency',
            'language',
            'userData',
            'urlSuccess',
            'urlSuccess',
            'urlFailure',
            'urlNotify',
            'urlBack',
            'orderDesc',
            'transId',
            'payId',
            'reqId',
            'ipAddr',
            'sdZip',
            'rtf',
            'response',
            'refNr',
            'email',
            'customerId'
        ];
}
