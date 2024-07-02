<?php

namespace Fatchip\CTPayment;

/**
 * Class CTPaymentMethods
 * @package Fatchip\CTPayment
 */
class CTPaymentMethods
{
    const paymentMethods =
        [
            [
                'name' => 'fatchip_computop_creditcard',
                'shortname' => 'Kreditkarte',
                'description' => 'Computop Kreditkarte',
                'action' => 'FatchipCTCreditCard',
                'template' => '',
                'additionalDescription' => '',
                'className' => 'CreditCard',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_easycredit',
                'shortname' => 'Easycredit',
                'description' => 'Computop Easycredit',
                'action' => 'FatchipCTEasyCredit',
                'template' => 'fatchip_computopeasycredit.tpl',
                'additionalDescription' => 'Rechnungs- und Lieferadresse müssen gleich sein, damit easyCredit genutzt werden kann',
                'className' => 'EasyCredit',
                // 'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_ideal',
                'shortname' => 'iDEAL',
                'description' => 'Computop iDEAL',
                'action' => 'FatchipCTIdeal',
                'template' => 'fatchip_computopideal.tpl',
                'additionalDescription' => '',
                'className' => 'Ideal',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_giropay',
                'shortname' => 'giropay',
                'description' => 'Computop Giropay',
                'action' => 'FatchipCTIdeal',
                'template' => 'fatchip_computopideal.tpl',
                'additionalDescription' => '',
                'className' => 'Ideal',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_twint',
                'shortname' => 'Twint',
                'description' => 'Computop Twint',
                'action' => 'FatchipCTIdeal',
                'template' => 'fatchip_computopideal.tpl',
                'additionalDescription' => '',
                'className' => 'Twint',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_klarna',
                'shortname' => 'Klarna',
                'description' => 'Computop Klarna',
                'action' => 'FatchipCTKlarnaPayments',
                'template' => 'fatchip_computopklarna_direct_debit.tpl',
                'additionalDescription' => '',
                'className' => 'KlarnaPayments',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_lastschrift',
                'shortname' => 'Lastschrift',
                'description' => 'Computop Lastschrift',
                'action' => 'FatchipCTLastschrift',
                'template' => 'fatchip_computoplastschrift.tpl',
                'additionalDescription' => '',
                'className' => 'Lastschrift',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_paypal_standard',
                'shortname' => 'PayPal',
                'description' => 'Computop PayPal Standard',
                'action' => 'FatchipCTPaypalStandard',
                'template' => '',
                'additionalDescription' => '',
                'className' => 'PaypalStandard',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_paypal_express',
                'shortname' => 'PayPalExpress',
                'description' => 'Computop PayPal Express',
                'action' => 'FatchipCTPaypalExpress',
                'template' => '',
                'additionalDescription' => '',
                'className' => 'PaypalExpress',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
            [
                'name' => 'fatchip_computop_amazonpay',
                'shortname' => 'AmazonPay',
                'description' => 'Computop AmazonPay',
                'action' => 'FatchipCTAmazon',
                'template' => '',
                'additionalDescription' => '',
                'className' => 'AmazonPay',
                //'countries' => ['DE', 'NL', 'DK', 'FI', 'SE', 'NO'],
            ],
        ];
}
