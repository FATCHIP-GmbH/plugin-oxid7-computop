<?php

namespace Fatchip\ComputopPayments\Core;

use Exception;
use Fatchip\ComputopPayments\Helper\Config;
use Fatchip\ComputopPayments\Helper\Payment;
use Fatchip\ComputopPayments\Model\Api\Encryption\AES;
use Fatchip\ComputopPayments\Model\Method\PayPalExpress;
use Fatchip\CTPayment\CTPaymentMethods;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Core\Registry;

/**
 * FatchipComputop Payment getters for templates
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    private $b = [];

    protected $fatchipComputopPaymentService;

    public $signature = '';

    // -----------------> START OXID CORE MODULE EXTENSIONS <-----------------

    /**
     * init object construction
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->fatchipComputopPaymentService = new CTPaymentService(Config::getInstance()->getConnectionConfig());
    }

    // -----------------> END OXID CORE MODULE EXTENSIONS <-----------------

    // -----------------> START CUSTOM MODULE FUNCTIONS <-----------------
    // @TODO: They ALL need a module function name prefix to not cross paths with other module

    /**
     * @return bool
     */
    public function isFatchipComputopCreditcardActive(): bool
    {
        $oPayment = oxNew('oxPayment');
        $oPayment->load('fatchip_computop_creditcard');
        return (bool)($oPayment->oxpayments__oxactive->value);
    }

    /**
     * prevent showing the amazon button in last order step in apex theme
     * @return bool
     */
    public function isLastCheckoutStep(): bool
    {
        return $this->getTopActionClassName() === 'order';
    }

    /**
     * prevent showing the amazon button in last order step in apex theme
     * @return bool
     */
    public function isPaymentCheckoutStep(): bool
    {
        return $this->getTopActionClassName() === 'payment';
    }

    /**
     * Template variable getter. Get payload in JSON Format
     *
     * @return string
     * @throws Exception
     */
    public function getPayload(): string
    {
        // Sicherstellen, dass wir eine gültige Antwort bekommen
        $payload = Registry::getSession()->getVariable('FatchipComputopResponse');
        $signature = $payload->getButtonsignature();
        $payloadButton = $payload->getButtonpayload();
        $this->signature = $payload->getButtonsignature();


        return $payload->getButtonpayload();
    }

    public function getButtonPubKey()
    {
        $payload = Registry::getSession()->getVariable('FatchipComputopResponse');
        $buttonPublicKey = $payload->getButtonpublickeyid();
        return $buttonPublicKey;
    }

    /**
     * Template variable getter. Get Signature for Payload
     *
     * @param string $payload
     * @return string
     * @throws Exception
     */
    public function getSignature(string $payload): string
    {
        $test = $this->signature;
        return $test;
    }

    /**
     * Template variable getter. Get Signature for Payload
     *
     * @param string $payload
     * @return string
     * @throws Exception
     */
    public function getLedgerCurrency(): string
    {
        return 'EUR';
    }

    /**
     * Template variable getter. Get Signature for Payload
     *
     * @param string $payload
     * @return string
     * @throws Exception
     */
    public function getCheckoutLanguage(): string
    {
        return 'de_DE';
    }

    public function getPayPalExpressConfig(): array
    {
        /** @var PayPalExpress $paymentModel */
        $paymentModel = Payment::getInstance()->getComputopPaymentModel(PayPalExpress::ID);
        return $paymentModel->getPayPalExpressConfig();
    }

    public function isPaypalActive(): bool
    {
        /** @var CTPaymentMethods\PayPalExpress $oPaypalExpressPaypment */
        $oPaypalExpressPaypment = $this->fatchipComputopPaymentService->getPaymentClass('PayPalExpress');
        return $oPaypalExpressPaypment->isActive();
    }

    public function ctGetShopUrl(): string
    {
        return Registry::getConfig()->getCurrentShopUrl();
    }
}
