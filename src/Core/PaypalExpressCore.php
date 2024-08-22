<?php

namespace Fatchip\ComputopPayments\Core;

class PaypalExpressCore
{

    public static function gr()
    {
        $amount = 10;
        $currency = 'EUR';
        $refNr = 123456789;

        $params = [];
        $params['Currency'] = $currency;
        $params['Amount'] = $amount;
        $params['TransID'] = 123455345345;
        $params['ReqId'] = 'as342xdfqterqtqweopqwoeqw23232324j2lk34j2kl34';
        $params['EtiID'] = 'OXID7-EXPERIMENTAL-DEV1';
        $params['RefNr'] = 6412312312195680452123123;
        $params['URLSuccess'] = 'https://edo3-stage-sso.maexware-kundencloud.de/onepage/returned';
        $params['URLFailure'] = 'https://edo3-stage-sso.maexware-kundencloud.de/onepage/failure';
        $params['URLNotify']  = 'https://edo3-stage-sso.maexware-kundencloud.de/notify';

        $dataQuery = urldecode(http_build_query($params));
        $length = mb_strlen($dataQuery);

        $oBlowfish = new Blowfish();

        return [
            'MerchantID' => 'fatchip_oxid7',
            'Len' => $length,
            'Data' => $oBlowfish->ctEncrypt($dataQuery, $length, 'x!3JH9n*mD_6[2Wb'),
        ];
    }

}