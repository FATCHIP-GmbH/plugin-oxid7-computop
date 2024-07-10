<?php

use Fatchip\ComputopPayments\Controller\Admin\FatchipComputopAjaxApiLog;
use Fatchip\ComputopPayments\Controller\Admin\FatchipComputopConfig;
use Fatchip\ComputopPayments\Controller\Admin\FatchipComputopApiTest;
use Fatchip\ComputopPayments\Controller\Admin\FatchipComputopApiLog;
use Fatchip\ComputopPayments\Controller\FatchipComputopEasycredit;
use Fatchip\ComputopPayments\Controller\Admin\FatchipComputopUpdateIdealIssuers;
use Fatchip\ComputopPayments\Controller\FatchipComputopIdeal;
use Fatchip\ComputopPayments\Controller\FatchipComputopKlarna;
use Fatchip\ComputopPayments\Controller\FatchipComputopLastschrift;
use Fatchip\ComputopPayments\Controller\FatchipComputopOrder;
use Fatchip\ComputopPayments\Controller\FatchipComputopPayment;
use Fatchip\ComputopPayments\Controller\FatchipComputopPayments;
use Fatchip\ComputopPayments\Controller\FatchipComputopNotify;
use Fatchip\ComputopPayments\Controller\FatchipComputopPaypalStandard;
use Fatchip\ComputopPayments\Controller\FatchipComputopTwint;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\ViewConfig as ModuleViewConfig;
use Fatchip\ComputopPayments\Controller\FatchipComputopCreditcard;
use OxidEsales\Eshop\Application\Controller\OrderController as CoreOrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController as CorePaymentController;
use OxidEsales\Eshop\Application\Model\Order as CoreOrderModel;
use Fatchip\ComputopPayments\Model\Order as ModuleOrder;
use OxidEsales\Eshop\Application\Model\PaymentGateway as CorePaymentGateway;
use Fatchip\ComputopPayments\Model\PaymentGateway as ModulePaymentGateway;
use OxidEsales\Eshop\Core\ViewConfig as CoreViewConfig;



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
        'de' => 'Computop Zahlarten',
        'en' => 'Computop Payments',
    ],
    'description' => [
        'de' => 'Zahlarten von CT',
        'en' => 'Payments from CT',
    ],
    'thumbnail'   => 'img/computop_logo.png',
    'version'     => '0.0.2',
    'author'      => 'Fatchip-GmbH',
    'url'         => 'https://www.fatchip.de/',
    'email'       => '',
    'extend'      => [
        CoreOrderController::class =>   FatchipComputopOrder::class,
        CorePaymentController::class => FatchipComputopPayment::class,
        CoreOrderModel::class => ModuleOrder::class,
        CoreViewConfig::class => ModuleViewConfig::class,

        // Models
        CorePaymentGateway::class => ModulePaymentGateway::class,
    ],
    'controllers' => [
        // Admin
        Constants::GENERAL_PREFIX . 'config' => FatchipComputopConfig::class,
        Constants::GENERAL_PREFIX . 'apitest' => FatchipComputopApiTest::class,
        Constants::GENERAL_PREFIX . 'apilog' => FatchipComputopApiLog::class,
        Constants::GENERAL_PREFIX . 'ajaxapilog' => FatchipComputopAjaxApiLog::class,
        Constants::GENERAL_PREFIX . 'updateidealissuers' => FatchipComputopUpdateIdealIssuers::class,

        // Frontend
        Constants::GENERAL_PREFIX . 'payments' => FatchipComputopPayments::class,
        Constants::GENERAL_PREFIX . 'lastschrift' => FatchipComputopLastschrift::class,
        Constants::GENERAL_PREFIX . 'creditcard' => FatchipComputopCreditcard::class,
        Constants::GENERAL_PREFIX . 'paypal_standard' => FatchipComputopPaypalStandard::class,
        Constants::GENERAL_PREFIX . 'klarna' => FatchipComputopKlarna::class,
        Constants::GENERAL_PREFIX . 'easycredit' => FatchipComputopEasycredit::class,
        Constants::GENERAL_PREFIX . 'notify' => FatchipComputopNotify::class,
        Constants::GENERAL_PREFIX . 'ideal' => FatchipComputopIdeal::class,
        Constants::GENERAL_PREFIX . 'twint' => FatchipComputopTwint::class,
    ],
    'blocks'      => [
    ],
    'settings'    => [
        ['name' => 'merchantID', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'mac', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'blowfishPassword', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'prefixOrdernumber', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'suffixOrdernumber', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'debuglog', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'encryption', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardMode', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardTestMode', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardSilentModeBrandsVisa', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'creditCardSilentModeBrandsMaster', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'creditCardSilentModeBrandsAmex', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardCaption', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardAcquirer', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'creditCardAccVerify', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'creditCardSilentModeBrandDetection', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'creditCardTemplate', 'type' => 'str', 'value' => 'ct_responsive', 'group' => null],
        ['name' => 'idealDirektOderUeberSofort', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'lastschriftDienst', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'lastschriftCaption', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'lastschriftAnon', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'paypalCaption', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'paypalSetOrderStatus', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'amazonpayMerchantId', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'amazonpayPrivKey', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonpayPubKeyId', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonpayStoreId', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonLiveMode', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonCaptureType', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'amazonButtonType', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'amazonButtonColor', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'amazonButtonSize', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'bonitaetusereturnaddress', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'bonitaetinvalidateafterdays', 'type' => 'str', 'value' => '', 'group' => null],
        ['name' => 'crifmethod', 'type' => 'str', 'value' => '', 'group' => null],

        ['name' => 'klarnaaction', 'type' => 'string', 'value' => false, 'group' => null],
        ['name' => 'klarnaaccount', 'type' => 'str', 'value' => '', 'group' => null],

    ],
    'events'      => [
        'onActivate'   => 'Fatchip\ComputopPayments\Core\Events::onActivate',
        'onDeactivate' => 'Fatchip\ComputopPayments\Core\Events::onDeactivate',
    ],
];
