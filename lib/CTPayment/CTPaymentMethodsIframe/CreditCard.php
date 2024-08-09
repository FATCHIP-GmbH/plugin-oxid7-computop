<?php
/** @noinspection PhpUnused */

/**
 * The Computop Shopware Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Shopware Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5.6, 7.0 , 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @subpackage CTPaymentMethodsIframe
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment\CTPaymentMethodsIframe;

use Fatchip\CTPayment\CTAddress\CTAddress;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethodIframe;
/**
 * Class CreditCard
 */
class CreditCard extends CTPaymentMethodIframe
{
    const paymentClass = 'CreditCard';

    /**
     * Bestimmt Art und Zeitpunkt der Buchung (engl. Capture).
     * AUTO: Buchung so-fort nach Autorisierung (Standardwert).
     * MANUAL: Buchung erfolgt durch den Händler.
     * <Zahl>: Verzögerung in Stunden bis zur Buchung (ganze Zahl; 1 bis 696).
     *
     * @var string
     */
    protected $capture = 'MANUAL';

    /**
     * @var string
     */
    protected $msgVer;

    /**
     * Creditcard acquirer
     * @var string
     */
    protected $acquirer;

    /**
     * Ein von Händler zu setzender Wert, um Informationen wieder unverschlüsselt zurückzugeben, zB die MID
     *
     * @var string
     */
    protected $plain;

    /*FIELD FOR AVS*/

    /**
     * Rechnungsadresse
     *
     * @var string
     */
    protected $billingAddress;

    /**
     * Lieferaddresse
     *
     * @var string
     */
    protected $shippingAddress;

    /**
     * Rechnungskunde
     *
     * @var string
     */
    protected $billToCustomer;

    /**
     * Lieferkunde
     *
     * @var string
     */
    protected $shipToCustomer;

    /* FIELDS FOR AMEX/CAPN*/
    /**
     * Prepaid-Karte: Tatsächlich autorisierter Betrag in der kleinsten Währungsein-heit.
     *
     * @var int
     */
    protected $AmountAuth;

    /**
     * Vorname des Kunden (für AVS)
     *
     * @var string
     */
    protected $FirstName;

    /**
     * Nachname des Kunden (für AVS)
     *
     * @var string
     */
    protected $LastName;

    /**
     * Vorname in der Lieferadresse (für AVS)
     *
     * @var string
     */
    protected $sdFirstName;

    /**
     * Nachname in der Lieferadresse (für AVS)     *
     *
     * @var string
     */
    protected $sdLastName;

    /**
     * Straßenname und Hausnummer in der Lieferadresse, z.B. 4102~N~289~PL (für AVS)
     *
     * @var string
     */
    protected $sdStreet;


    /**
     * Ländercode der Lieferadresse im Format ISO-3166-1, numerisch 3-stellig (für AVS)
     *
     * @var string
     */

    protected $sdCountryCode;

    /**
     * For Paynow/Silentmode:
     * Kreditkarteninhaber
     * @var string
     *
     */
    protected $CreditCardHolder;

    /**
     * For Paynow/Silentmode:
     * Kreditkartennummer: mindestens 12stellig ohne Leerzeichen
     * @var string
     *
     */
    protected $CCNr;

    /**
     * For Paynow/Silentmode:
     * Kartenprüfnummer: Die letzten 3 Ziffern auf dem Unterschriftsfeld der Kredit-karte     *
     * @var string
     */
    protected $CCCVC;

    /**
     * For Paynow/Silentmode:
     * Ablaufdatum der Kreditkarte im Format YYYYMM, z.B. 201807
     * @var string
     */
    protected $CCExpiry;

    /**
     * For Paynow/Silentmode:
     * Kreditkartenmarke. MasterCard, VISA oder AMEX
     * @var string
     */
    protected  $CCBrand;

    /**
     * Übergeben Sie „Order“, um eine Zahlung zu initialisieren und diese später über die Schnittstelle authorize.aspx zu autorisieren.
     * Bitte beachten Sie, dass in Ver-bindung mit dem genutzten 3D-Secure-Verfahren eine separate Einstellung notwendig ist.
     *
     * @var string
     *
     */
    protected  $TxType;

    /**
     * JSON Object specifying type and series of transactions using payment account credentials
     * (e.g. account number or payment token) that is stored by a merchant to process future purchases
     * for a customer. Required if applicable.
     *
     * @var $credentialOnFile
     */
    protected $credentialOnFile;

    protected $Custom;


    /**
     * Send the user sessionid in the custom field
     * CT returns the custom parameter unencrypted in the reuqests response.
     * This is only used for restoring the session after iframe payments as a workaround for Safari 6+ browsers
     */
    public function setCustom()
    {
        /* $module = Shopware()->Container()->get('front')->Request()->getModuleName();
        if ($module !== 'backend') {
            $this->Custom = 'session=' . Shopware()->Session()->get('sessionId');
        } else {
            $this->Custom = '';
        }
        */
        $this->Custom = '';
    }

    /**
     * returns paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/payssl.aspx';
    }

    /**
     * returns PayNowURL
     * @return string
     */
    public function getCTPayNowURL()
    {
        return 'https://www.computop-paygate.com/paynow.aspx';
    }

    /**
     * returns paymentURL
     * @return string
     */
    public function getCTRecurringURL()
    {
        return 'https://www.computop-paygate.com/direct.aspx';
    }


    /**
     * Creditcard constructor
     *
     * @param array $config
     * @param CTOrder|null $order
     * @param null|string $urlSuccess
     * @param null|string $urlFailure
     * @param $urlNotify
     * @param $orderDesc
     * @param $userData
     * @param $eventToken
     * @param $isFirm
     * @param null $klarnainvoice
     * @param null $urlBack
     */
    public function __construct(
        $config,
        $order,
        $urlSuccess,
        $urlFailure,
        $urlNotify,
        $orderDesc,
        $userData,
        $eventToken = null,
        $isFirm = null,
        $klarnainvoice = null,
        $urlBack
    )
    {
        parent::__construct($config, $order, $orderDesc, $userData);

        $this->setUrlSuccess($urlSuccess);
        $this->setUrlFailure($urlFailure);
        $this->setUrlNotify($urlNotify);
        $this->setUrlBack($urlBack);

        $this->setMsgVer('2.0');
        $this->setUserData(base64_encode($userData));

        if($config['creditCardTestMode']) {
            $this->setOrderDesc('Test:0000');
        }
        else {
            $this->setOrderDesc($orderDesc);
        }


        $this->setBillingAddress($order->getBillingAddress());
        $this->setShippingAddress($order->getShippingAddress());
        if ($config['creditCardAcquirer'] === 'CAPN') {
            $this->setAmountAuth($order->getAmount());
            $this->setBillToCustomer($order);
            $this->setShipToCustomer($order);
        }

        //we will handle all captures manually
        $this->setCapture('MANUAL');

        $this->setCustom();

    }

    /**
     * @ignore <description>
     * @param string $capture
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getMsgVer()
    {
        return $this->msgVer;
    }

    /**
     * @ignore <description>
     * @param string $msgVer
     */
    public function setMsgVer($msgVer)
    {
        $this->msgVer = $msgVer;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @ignore <description>
     * @param CTAddress $CTAddress
     */
    public function setBillingAddress($CTAddress)
    {
        $this->billingAddress = base64_encode(json_encode($this->declareAddress($CTAddress)));
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @ignore <description>
     * @param CTAddress $CTAddress
     */
    public function setShippingAddress($CTAddress)
    {
        $this->shippingAddress = base64_encode(json_encode($this->declareAddress($CTAddress)));
    }

    /**
     * @param CTAddress $CTAddress
     * @return array
     */
    protected function declareAddress($CTAddress)
    {
        $address['city'] = $CTAddress->getCity();
        $address['country']['countryA3'] = $CTAddress->getCountryCodeIso3();
        $address['addressLine1']['street'] = $CTAddress->getStreet();
        $address['addressLine1']['streetNumber'] = $CTAddress->getStreetNr();
        $address['postalCode'] = $CTAddress->getZip();
        return $address;
    }

    /**
     * @return string
     */
    public function getBillToCustomer()
    {
        return $this->billToCustomer;
    }

    /**
     * @param CTOrder $ctOrder
     */
    public function setBillToCustomer($ctOrder)
    {
        #$customer['consumer']['salutation'] = $ctOrder->getBillingAddress()->getSalutation();
        $customer['consumer']['firstName'] = $ctOrder->getBillingAddress()->getFirstName();
        $customer['consumer']['lastName'] = $ctOrder->getBillingAddress()->getLastName();
        $customer['email'] = $ctOrder->getEmail();
        $this->billToCustomer = base64_encode(json_encode($customer));
    }

    /**
     * @return string
     */
    public function getShipToCustomer()
    {
        return $this->shipToCustomer;
    }

    /**
     * @param CTOrder $ctOrder
     */
    public function setShipToCustomer($ctOrder)
    {
        #$customer['consumer']['salutation'] = $ctOrder->getShippingAddress()->getSalutation();
        $customer['consumer']['firstName'] = $ctOrder->getShippingAddress()->getFirstName();
        $customer['consumer']['lastName'] = $ctOrder->getShippingAddress()->getLastName();
        $customer['email'] = $ctOrder->getEmail();
        $customer['customerNumber'] = $ctOrder->getCustomerID();
        $this->shipToCustomer = base64_encode(json_encode($customer));
    }

    /**
     * @return string
     */
    public function getCredentialsOnFile()
    {
        return $this->credentialOnFile;
    }

    /**
     * return string
     */
    public function setCredentialsOnFile($unscheduled = 'CIT', $initalPayment = true)
    {
        $credentialsOnFile['type']['unscheduled'] = $unscheduled;
        $credentialsOnFile['initialPayment'] = $initalPayment;
        $this->credentialOnFile = base64_encode(json_encode($credentialsOnFile));
    }

    /**
     * @ignore <description>
     * @param int $amountAuth
     */
    public function setAmountAuth($amountAuth)
    {
        $this->AmountAuth = $amountAuth;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getAmountAuth()
    {
        return $this->AmountAuth;
    }

    /**
     * @ignore <description>
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->FirstName = $firstName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @ignore <description>
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->LastName = $lastName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @ignore <description>
     * @param string $sdCountryCode
     */
    public function setSdCountryCode($sdCountryCode)
    {
        $this->sdCountryCode = $sdCountryCode;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdCountryCode()
    {
        return $this->sdCountryCode;
    }

    /**
     * @ignore <description>
     * @param string $sdFirstName
     */
    public function setSdFirstName($sdFirstName)
    {
        $this->sdFirstName = $sdFirstName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdFirstName()
    {
        return $this->sdFirstName;
    }

    /**
     * @ignore <description>
     * @param string $sdLastName
     */
    public function setSdLastName($sdLastName)
    {
        $this->sdLastName = $sdLastName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdLastName()
    {
        return $this->sdLastName;
    }

    /**
     * @ignore <description>
     * @param string $sdStreet
     */
    public function setSdStreet($sdStreet)
    {
        $this->sdStreet = $sdStreet;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdStreet()
    {
        return $this->sdStreet;
    }

    /**
     * @ignore <description>
     * @param string $CCBrand
     */
    public function setCCBrand($CCBrand) {
        $this->CCBrand = $CCBrand;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCCBrand() {
        return $this->CCBrand;
    }

    /**
     * @ignore <description>
     * @param string $CCCVC
     */
    public function setCCCVC($CCCVC) {
        $this->CCCVC = $CCCVC;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCCCVC() {
        return $this->CCCVC;
    }

    /**
     * @ignore <description>
     * @param string $CCExpiry
     */
    public function setCCExpiry($CCExpiry) {
        $this->CCExpiry = $CCExpiry;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCCExpiry() {
        return $this->CCExpiry;
    }

    /**
     * @ignore <description>
     * @param string $CreditCardHolder
     */
    public function setCreditCardHolder($CreditCardHolder) {
        $this->CreditCardHolder = $CreditCardHolder;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCreditCardHolder() {
        return $this->CreditCardHolder;
    }

    /**
     * @ignore <description>
     * @param string $CCNr
     */
    public function setCCNr($CCNr) {
        $this->CCNr = $CCNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCCNr() {
        return $this->CCNr;
    }

    /**
     * @ignore <description>
     * @param string $TxType
     */
    public function setTxType($TxType) {
        $this->TxType = $TxType;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getTxType() {
        return $this->TxType;
    }

    public function getBrowserInfo() {

        $browserInfoParams = [
            'javaScriptEnabled' => true, // This is already correct
            'javaEnabled' => ($_SERVER['HTTP_USER_AGENT'] ?? '') ? filter_var(strpos($_SERVER['HTTP_USER_AGENT'], 'Java') !== false, FILTER_VALIDATE_BOOLEAN) : false, // Check for Java in User-Agent
            'colorDepth' => (int)($_COOKIE['colorDepth'] ?? '24'), // Default to 24-bit if not found
            'screenHeight' => (int)($_COOKIE['screenHeight'] ?? '1080'), // Default to 1080p if not found
            'screenWidth' => (int)($_COOKIE['screenWidth'] ?? '1920'), // Default to 1080p if not found
            'timeZoneOffset' => '300',  // Get timezone offset from JavaScript
            'acceptHeaders' => $_SERVER['HTTP_ACCEPT'] ?? '',      // Get from Accept header
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? '',           // Get client's IP address
            'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',  // Get preferred language
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',       // Get the User-Agent string
        ];

        return $browserInfoParams;
    }

    /**
     * returns encrypted url for preauthorizations for paynow silent mode
     *
     * @param $ctRequest
     * @return string
     */
    public function getPaynowURL($ctRequest)
    {
        return $this->prepareComputopRequest($ctRequest, $this->getCTPayNowURL());
    }
    public function getPaynowURLasJson($ctRequest)
    {
        $ctRequest['browserInfo'] = base64_encode(json_encode($this->getBrowserInfo()));
       // $ctRequest['billingAddress'] = base64_encode(json_encode($ctRequest['billingAddress']));

        $silentRequestParams = $this->prepareSilentRequest($ctRequest);
        return json_encode($silentRequestParams);
    }
    /**
     * returns url for preauthorizations for paynow silent mode
     *
     * @return string
     */
    public function getAuthorizeURL()
    {
        return 'https://www.computop-paygate.com/authorize.aspx';
    }

    /**
     * returns encoded url for a request with encoded Data and LEN queryparameters
     * Overridden, because for CreditCard we need to put the template param in the undecrypted part of the querystring
     * @param $ctRequest
     * @param string $addTemplate
     * @return string
     */
    public function getHTTPGetURL($ctRequest, $addTemplate = '')
    {
        return parent::prepareComputopRequest($ctRequest, $this->getCTPaymentURL(), $addTemplate);
    }

    /**
     * sets and returns request parameters for server-to-server authorization calls
     *
     * @param $payID
     * @param $transID
     * @param $amount
     * @param $currency
     * @param $capture
     * @return array
     */
    public function getAuthorizeParams($payID, $transID, $amount, $currency, $capture)
    {
        $params = [
            'merchantID' => $this->merchantID,
            'amount' => $amount,
            'currency' => $currency,
            'Capture' => $capture,
        ];

        return $params;
    }

}
