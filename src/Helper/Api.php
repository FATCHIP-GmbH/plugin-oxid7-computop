<?php

namespace Fatchip\ComputopPayments\Helper;

use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\EshopCommunity\Core\ShopVersion;
use OxidEsales\Facts\Facts;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;
use OxidEsales\Eshop\Core\Registry;

class Api
{
    /**
     * @var Api
     */
    protected static $instance = null;

    /**
     * Source: https://en.wikipedia.org/wiki/ISO_4217#Active_codes_(List_One)
     *
     * @var array
     */
    protected $nonDecimalCurrencies = [
        'BIF', // Burundian franc	 Burundi
        'CLP', // Chilean peso	 Chile
        'DJF', // Djiboutian franc	 Djibouti
        'GNF', // Guinean franc	 Guinea
        'ISK', // Icelandic króna (plural: krónur)	 Iceland
        'JPY', // Japanese yen	 Japan
        'KMF', // Comoro franc	 Comoros
        'KRW', // South Korean won	 South Korea
        'PYG', // Paraguayan guaraní	 Paraguay
        'RWF', // Rwandan franc	 Rwanda
        'UGX', // Ugandan shilling	 Uganda
        'UYI', // Uruguay Peso en Unidades Indexadas (URUIURUI) (funds code)	 Uruguay
        'VND', // Vietnamese đồng	 Vietnam
        'VUV', // Vanuatu vatu	 Vanuatu
        'XAF', // CFA franc BEAC	 Cameroon (CM),  Central African Republic (CF),  Republic of the Congo (CG),  Chad (TD),  Equatorial Guinea (GQ),  Gabon (GA)
        'XOF', // CFA franc BCEAO	 Benin (BJ),  Burkina Faso (BF),  Côte d'Ivoire (CI),  Guinea-Bissau (GW),  Mali (ML),  Niger (NE),  Senegal (SN),  Togo (TG)
        'XPF', // CFP franc (franc Pacifique)	French territories of the Pacific Ocean:  French Polynesia (PF),  New Caledonia (NC),  Wallis and Futuna (WF)
    ];

    /**
     * Create singleton instance of this payment helper
     *
     * @return Payment
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = oxNew(self::class);
        }
        return self::$instance;
    }

    /**
     * Generates a request id
     * Doc says: To avoid double payments or actions (e.g. by ETM), enter an alphanumeric value which identifies your transaction and may be assigned only once.
     * If the transaction or action is submitted again with the same ReqID, Computop Paygate will not carry out the payment or new action,
     * but will just return the status of the original transaction or action.
     *
     * @return string
     */
    public function getRequestId()
    {
        mt_srand(intval(microtime(true) * 1000000));
        $reqID = (string)mt_rand();
        $reqID .= date('yzGis');
        return $reqID;
    }

    /**
     * Get identification string for requests
     *
     * @return string
     */
    public function getIdentString()
    {
        $moduleVersion = '';

        try {
            $shopConfig =  ContainerFactory::getInstance()->getContainer()->get(ShopConfigurationDaoBridgeInterface::class)->get();
            try {
                $moduleConfig = $shopConfig->getModuleConfiguration(Constants::MODULE_ID);
                $moduleVersion = 'ModuleVersion: '.$moduleConfig->getVersion();
            } catch (ModuleConfigurationNotFoundException $e) {
                Registry::getLogger()->error('ModuleConfig not found: ' . $e->getMessage());
            }
        } catch (Exception $e) {
            Registry::getLogger()->error('ModuleConfig fetch error: ' . $e->getMessage());
        }

        $shopName = "Oxid ".(new Facts())->getEdition();
        $shopVersion = ShopVersion::getVersion();

        return sprintf('%s %s %s', $shopName, $shopVersion, $moduleVersion);
    }

    /**
     * Formats amount for API
     * Docs say: Amount in the smallest currency unit (e.g. EUR Cent)
     *
     * @param double $amount
     * @param string $currencyCode
     * @return float|int
     */
    public function formatAmount($amount, $currencyCode = 'EUR')
    {
        $decimalMultiplier = 100;
        if (in_array($currencyCode, $this->nonDecimalCurrencies)) {
            $decimalMultiplier = 1;
        }
        return number_format($amount * $decimalMultiplier, 0, '.', '');
    }

    /**
     * Encode array in json and then in base64 for api requests
     *
     * @param  array $array
     * @return string
     */
    public function encodeArray($array)
    {
        return base64_encode(json_encode($array));
    }

    public function addLogEntry($request, $response, $paymentName, $requestType)
    {
        if (!$response instanceof CTResponse) {
            $response = new CTResponse($response);
        }

        $logger = new Logger();
        $logger->logRequestResponse($request, $paymentName, $requestType, $response);
    }

    /**
     * Returns reference number
     *
     * @param  string $orderNr
     * @return string
     */
    public function getReferenceNumber($orderNr)
    {
        return trim(Config::getInstance()->getConfigParam('refnr_prefix') ?? '').$orderNr.trim(Config::getInstance()->getConfigParam('refnr_suffix') ?? '');
    }
}
