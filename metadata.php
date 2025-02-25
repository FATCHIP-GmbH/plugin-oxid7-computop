<?php

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

/**
 * Module information
 */
$aModule = [
    'id'          => 'fatchip_computop_payments',
    'title'       => [
        'de' => 'Computop Payment Connector',
        'en' => 'Computop Payment Connector',
    ],
    'description' => [
        'de' => '<p><b>Über Computop</b><br><br>Die Computop GmbH ist ein weltweit führender Anbieter von innovativen Zahlungslösungen. Seit ihrer Gründung hat sich die Computop GmbH als verlässlicher Partner im Bereich des Zahlungsverkehrs etabliert und bietet maßgeschneiderte Lösungen für E-Commerce, POS (Point of Sale), Mobile Payment und Omnichannel-Zahlungen. Mit ihrer hochsicheren und flexiblen Payment-Plattform, dem Computop Paygate, ermöglicht die Computop GmbH Händlern und Dienstleistern eine nahtlose Integration verschiedenster Zahlungsarten und Services. Dies fördert nicht nur die Kundenbindung, sondern optimiert auch die Transaktionsprozesse und erhöht die Effizienz im Zahlungsmanagement. <br><br>Das Zahlarten-Plugin der Computop GmbH integriert diese umfassenden Zahlungslösungen nahtlos in bestehende E-Commerce-Systeme und bietet somit eine breite Palette an Zahlungsmethoden, darunter Kredit- und Debitkarten, E-Wallets, Banküberweisungen und lokale Zahlungsmethoden. Diese Integration ermöglicht es Händlern, ihren Kunden eine komfortable und sichere Zahlungserfahrung zu bieten, was letztlich zu einer höheren Kundenzufriedenheit und einer verbesserten Konversionsrate führt. Mit der Unterstützung durch die Computop GmbH können Unternehmen weltweit ihre Zahlungsprozesse optimieren und sich auf ihr Kerngeschäft konzentrieren.</p>
                <p><b>Verfügbare Zahlarten</b><ul><li>Amazon Pay</li><li>Kreditkarte</li><li>Lastschrift</li><li>EasyCredit</li><li>iDeal</li><li>Klarna</li><li>PayPal</li></ul></p>
                <p><b>Nutzung von Computop Payments</b><br><br>Um Computop Payments über das Plugin zu nutzen, benötigen Sie einen Vertrag mit Computop. Bitte kontaktieren Sie hierfür den Computop Vertrieb unter https://www.computop.com/de/. Falls Sie bereits Kunde sind, bitten wir Sie, sich direkt an Ihren Account Manager zu wenden. Im Anschluss erhalten Sie Ihre Zugangsdaten mit denen Sie das Plugin in Betrieb nehmen können.</p>
                <p><b>Support</b><br><br>Persönlicher Support via E-Mail an <a href="mailto:helpdesk@computop.com">helpdesk@computop.com</a></p>',
        'en' => '<p><b>About Computop</b><br><br>Computop GmbH is a leading global provider of innovative payment solutions. Since its foundation, Computop GmbH has established itself as a reliable partner in the field of payment transactions and offers customized solutions for e-commerce, POS (point of sale), mobile payment and omnichannel payments. With its highly secure and flexible payment platform, Computop Paygate, Computop GmbH enables merchants and service providers to seamlessly integrate a wide range of payment methods and services. This not only boosts customer loyalty, but also optimizes transaction processes and increases efficiency in payment management. <br><br>Computop GmbH\'s payment method plugin seamlessly integrates these comprehensive payment solutions into existing e-commerce systems and thus offers a wide range of payment methods, including credit and debit cards, e-wallets, bank transfers and local payment methods. This integration enables merchants to offer their customers a convenient and secure payment experience, which ultimately leads to higher customer satisfaction and an improved conversion rate. With the assistance of Computop GmbH, companies worldwide can optimize their payment processes and concentrate on their core business.</p>
                <p><b>Available payment methods</b><ul><li>Amazon Pay</li><li>Credit Card</li><li>Direct Debit</li><li>EasyCredit</li><li>iDeal</li><li>Klarna</li><li>PayPal</li></ul></p>
                <p><b>Using Computop Payments</b><br><br>To use Computop Payments via the plugin, you need a contract with Computop. Please contact the Computop sales department at https://www.computop.com/de/. If you are already a customer, please contact your account manager directly. You will then receive your access data with which you can put the plugin into operation.</p>
                <p><b>Support</b><br><br>Personal support via e-mail to <a href="mailto:helpdesk@computop.com">helpdesk@computop.com</a></p>',
    ],
    'thumbnail'   => 'img/computop_logo.png',
    'version'     => '1.0.0',
    'author'      => 'Fatchip-GmbH',
    'url'         => 'https://www.fatchip.de/',
    'email'       => '',
    'extend'      => [
        // Controllers
        \OxidEsales\Eshop\Application\Controller\OrderController::class => \Fatchip\ComputopPayments\Controller\FatchipComputopOrder::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => \Fatchip\ComputopPayments\Controller\FatchipComputopPayment::class,

        // Models
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => \Fatchip\ComputopPayments\Model\PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => \Fatchip\ComputopPayments\Model\Order::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => \Fatchip\ComputopPayments\Core\ViewConfig::class,
        \OxidEsales\Eshop\Core\Session::class => \Fatchip\ComputopPayments\Core\FatchipComputopSession::class,
    ],
    'controllers' => [
        // Admin
        'fatchip_computop_config'               => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopConfig::class,
        'fatchip_computop_apitest'              => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopApiTest::class,
        'fatchip_computop_apilog'               => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopApiLog::class,
        'fatchip_computop_apilog_main'          => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopApiLogMain::class,
        'fatchip_computop_apilog_list'          => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopApiLogList::class,
        'fatchip_computop_ajaxapilog'           => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopAjaxApiLog::class,
        'fatchip_computop_updateidealissuers'   => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopUpdateIdealIssuers::class,
        'fatchip_computop_order_settings'       => \Fatchip\ComputopPayments\Controller\Admin\FatchipComputopOrderSettings::class,

        // Frontend
        'fatchip_computop_payments'             => \Fatchip\ComputopPayments\Controller\FatchipComputopPayments::class,
        'fatchip_computop_lastschrift'          => \Fatchip\ComputopPayments\Controller\FatchipComputopLastschrift::class,
        'fatchip_computop_creditcard'           => \Fatchip\ComputopPayments\Controller\FatchipComputopCreditcard::class,
        'fatchip_computop_paypal_standard'      => \Fatchip\ComputopPayments\Controller\FatchipComputopPaypalStandard::class,
        'fatchip_computop_paypal_express'       => \Fatchip\ComputopPayments\Controller\FatchipComputopPaypalExpress::class,
        'fatchip_computop_klarna'               => \Fatchip\ComputopPayments\Controller\FatchipComputopKlarna::class,
        'fatchip_computop_easycredit'           => \Fatchip\ComputopPayments\Controller\FatchipComputopEasycredit::class,
        'fatchip_computop_amazonpay'            => \Fatchip\ComputopPayments\Controller\FatchipComputopAmazonpay::class,
        'fatchip_computop_notify'               => \Fatchip\ComputopPayments\Controller\FatchipComputopNotify::class,
        'fatchip_computop_ideal'                => \Fatchip\ComputopPayments\Controller\FatchipComputopIdeal::class,
        'fatchip_computop_twint'                => \Fatchip\ComputopPayments\Controller\FatchipComputopTwint::class,
        'fatchip_computop_redirect'             => \Fatchip\ComputopPayments\Controller\FatchipComputopRedirect::class
    ],
    'blocks'      => [
    ],
    'settings'    => [
        ['name' => 'merchantID', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'mac', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'blowfishPassword', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'debuglog', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'encryption', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardMode', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardTestMode', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardSilentModeBrandsVisa', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'creditCardSilentModeBrandsMaster', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'creditCardSilentModeBrandsAmex', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardCaption', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardAcquirer', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'creditCardTemplate', 'type' => 'str', 'value' => 'ct_responsive', 'group' => null],
        ['name' => 'idealDirektOderUeberSofort', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'lastschriftDienst', 'type' => 'str', 'value' => 'DIREKT', 'group' => null],
        ['name' => 'lastschriftCaption', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'paypalCaption', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'paypalExpressCaption', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'paypalExpressClientID', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'paypalExpressMerchantID', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'paypalExpressPartnerAttributionID', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'paypalExpressTestMode', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'amazonpayMerchantId', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'amazonpayPubKeyId', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonLiveMode', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonCaptureType', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonButtonColor', 'type' => 'string', 'value' => false, 'group' => null],

        ['name' => 'klarnaaccount', 'type' => 'str', 'value' => '', 'group' => null],

    ],
    'events'      => [
        'onActivate'   => 'Fatchip\ComputopPayments\Core\Events::onActivate',
        'onDeactivate' => 'Fatchip\ComputopPayments\Core\Events::onDeactivate',
    ]
];
