<?php

namespace Fatchip\ComputopPayments\Core;

use Exception;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethods;
use Fatchip\CTPayment\CTPaymentService;
use Fatchip\CTPayment\Encryption;
use OxidEsales\Eshop\Core\Registry;

/**
 * FatchipComputop Payment getters for templates
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    private $b  = [];
    protected $fatchipComputopConfig;
    protected $fatchipComputopBasket;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    protected $fatchipComputopLogger;
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

    /**
     * Liefert die PayPal Express Parameter.
     *
     * @return array
     */
    public function getPayPalExpressParams()
    {
        /** @var CTPaymentMethods\PaypalExpress $payment */
        $payment = $this->fatchipComputopPaymentService->getPaymentClass('PaypalExpress');

        $session = Registry::getSession();
        $basket = $session->getBasket();

        //$order = $this->createOrderFromBasket($basket); //Pseudo

        $tmpOrder = new CTOrder(); // In the moment CTOrder is not overloading the oxorder Model, this has to be added/made - maybe not, because in that case the order is not set yet, because first in the express-button to PayPal happening.
        $tmpOrder->setAmount(10);
        $tmpOrder->setCurrency('EUR');
        $tmpOrder->setTransID('123456789');
        $tmpOrder->setPayId('123456789');

        $params = $payment->getPaypalExpressShortcutParams(
            $tmpOrder,
            $this->fatchipComputopConfig['urlSuccess'],
            $this->fatchipComputopConfig['urlFailure'],
            $this->fatchipComputopConfig['urlBack'],
            '',
            'de_DE',
        );

        return $payment->prepareComputopRequest($params, $payment->getCTPaymentShortcut(), '', true); // this attempt finishes direct on that point, because the former prepareComputopRequest seems to make it totally wron;
    }

    public function getPayPalExpressConfig(){
        //ToDo: Has to be taken from the Module Configuration
        return [
            'clientId' => 'AUeU8a0ihEF4KCezWdehyi7IbSSrVjr7cis1dKM2jeoX2MZ-bTDwnTQv75_n8ZAbnOJHpFd1Rc6PGO4H',
            'merchantId' => 'YA9TB6DNRNNUW',
        ];
    }

    public function toto()
    {
        if(empty($this->b)){
            $this->b = PaypalExpressCore::gr();
        }else return $this->b;
    }
    //public function toto()
    //{
    //    $oCTPaypalExpress = new CTPaymentMethods\PaypalExpress();
    //    return $oCTPaypalExpress->prepareComputopRequest([
    //        'TransID' => mt_rand(100000, 999999),
    //        'refnr' => 6412312312195680452123123,
    //        'Amount' => 10,
    //        'Currency' => 'EUR',
    //        'Capture' => 'Auto',
    //        'OrderDesc' => 'Just a simple description',
    //        'ItemTotal' => 1,
    //        'URLSuccess' => '',
    //        'URLFailure' => '',
    //        'Response' => 'encrypt',
    //        'URLNotify' => '',
    //        'UserData' => 'HeyIts333222',
    //        'ReqId' => 'as342xdfqterqtqweopqwoeqw23232324j2lk34j2kl34',
    //        'Language' => 'DE',
    //        'FirstName' => 'Hasan',
    //        'LastName' => 'Kara',
    //        'AddrStreet' => 'Street 1',
    //        'AddrStreet2' => '',
    //        'AddrCity' => 'Emmendingen',
    //        'AddrState' => 'BW',
    //        'AddrZip' => '79312',
    //        'AddrCountryCode' => 'DE',
    //        'Phone' => '',
    //    ], 'https://www.computop-paygate.com/ExternalServices/paypalorders.aspx');
    //}
}
