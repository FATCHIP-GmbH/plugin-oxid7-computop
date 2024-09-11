<?php

namespace Fatchip\ComputopPayments\Core;

use Exception;
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
    protected $fatchipComputopConfig;
    protected $fatchipComputopBasket;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    protected $fatchipComputopLogger;
    protected $fatchipComputopPaymentService;
    public $fatchipComputopSilentParams;
    public $signature = '';

    /**
     * init object construction
     *
     * @return null
     */
    public function __construct()
    {
        $config = new Config();
        $this->fatchipComputopConfig = $config->toArray();
        $this->fatchipComputopSession = Registry::getSession();
        $this->fatchipComputopBasket = $this->fatchipComputopSession->getBasket();
        $this->fatchipComputopShopConfig = Registry::getConfig();
        $this->fatchipComputopPaymentId = $this->fatchipComputopBasket->getPaymentId() ?: '';
        $this->fatchipComputopShopUtils = Registry::getUtils();
        $this->fatchipComputopLogger = new Logger();
        $this->fatchipComputopPaymentService = new CTPaymentService($this->fatchipComputopConfig);
    }

    /**
     * @return Config
     */
    public function getFatchipComputopConfig(): array
    {
        return $this->fatchipComputopConfig;
    }

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
     * @param string $paymentId
     * @return bool
     */
    public function isFatchipComputopOrder(string $paymentId): bool
    {
        return Constants::isFatchipComputopPayment($paymentId);
    }

    /**
     * Get webhook controller url
     *
     * @return string
     */
    public function getCancelAmazonPaymentUrl(): string
    {
        return $this->getSelfLink() . 'cl=fatchip_computop_amazonpay&fnc=cancelFatchipComputopAmazonPayment';
    }

    /**
     * Template getter getAmazonPaymentId
     *
     * @return string
     */
    public function getAmazonPaymentId(): string
    {
        return Constants::amazonpayPaymentId;
    }

    /**
     * Template variable getter. Get payload in JSON Format
     *
     * @return string
     * @throws Exception
     */
    public function getPayload(): string
    {
        $test = $this->fatchipComputopSession->getVariable('FatchipComputopResponse');
        return $test;
    }

    public function getConfig()
    {
        return $this->fatchipComputopShopConfig;
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
        return $this->signature;
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
        /** @var CTPaymentMethods\PaypalExpress $oPaypalExpressPaypment */
        $oPaypalExpressPaypment = $this->fatchipComputopPaymentService->getPaymentClass('PayPalExpress');
        return $oPaypalExpressPaypment->getPayPalExpressConfig();
    }

    public function isPaypalActive(): bool
    {
        /** @var CTPaymentMethods\PayPalExpress $oPaypalExpressPaypment */
        $oPaypalExpressPaypment = $this->fatchipComputopPaymentService->getPaymentClass('PayPalExpress');
        return $oPaypalExpressPaypment->isActive();
    }

}
