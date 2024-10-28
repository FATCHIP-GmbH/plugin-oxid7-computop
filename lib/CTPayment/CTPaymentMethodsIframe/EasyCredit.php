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

use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethodIframe;
use Fatchip\CTPayment\CTAddress\CTAddress;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class EasyCredit
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class EasyCredit extends CTPaymentMethodIframe
{
    const paymentClass = 'EasyCredit';

    /**
     * Definiert die bei easyCredit auszuführende Anfrage: <INT> zur Initialisierung eines Vorgangs
     *
     * @var
     */
    protected $eventToken;

    protected $billToCustomer;

    /**
     * Anrede HERR oder FRAU
     *
     * @var string
     */
    protected $salutation;

    /**
     * Vorname
     *
     * @var string
     */
    protected $FirstName;

    /**
     * Nachname
     *
     * @var string
     */
    protected $LastName;

    /**
     * Vorname
     *
     * @var string
     */
    protected $bdFirstName;

    /**
     * Nachname
     *
     * @var string
     */
    protected $bdLastName;

    /**
     * Geburtsdatum im Format YYYY-MM-DD
     *
     * @var string
     */
    protected $DateOfBirth;

    //Billingaddress
    /**
     * Straße
     *
     * @var string
     */
    protected $bdStreet;

    /**
     * Hausnummer
     *
     * @var string
     */
    protected $bdStreetNr;

    /**
     * Adresszusatz
     *
     * @var string
     */
    protected $bdAddressAddition;

    /**
     * Postleitzahl
     *
     * @var int
     */
    protected $bdZip;

    /**
     * Stadt
     *
     * @var string
     */
    protected $bdCity;


    /**
     * Ländercode in der Rechnungsadresse gemäß ISO 3166, zweistellig. Derzeit ist nur DE erlaubt.
     *
     * @var string
     */
    protected $bdCountryCode;

    //Shippingaddress
    /**
     * Packstation
     *
     * @var string
     */
    protected $PackingStation;

    /**
     * Vorname
     *
     * @var string
     */
    protected $sdFirstName;

    /**
     * Nachname
     *
     * @var string
     */
    protected $sdLastName;

    /**
     * Straße
     *
     * @var string
     */
    protected $sdStreet;

    /**
     * Hausnummer
     *
     * @var string
     */
    protected $sdStreetNr;

    /**
     * Adresszusatz
     * @var string
     */
    protected $sdAddressAddition;

    /**
     * Stadt
     *
     * @var string
     */
    protected $sdCity;

    /**
     * Ländercode in der Lieferdresse gemäß ISO 3166, zweistellig. Derzeit ist nur DE erlaubt.
     *
     * @var string
     */
    protected $sdCountryCode;

    //Kontaktdaten
    /**
     * E-Mail-Adresse des Kunden
     *
     * @var string
     */
    protected $Email;

    /**
     * Mobiltelefonnummer des Kunden
     *
     * @var string
     */
    protected $MobileNr;

    protected $version;

    /**
     * @ignore <description>
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->Email = $email;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * @ignore <description>
     * @param mixed $eventToken
     */
    public function setEventToken($eventToken)
    {
        $this->eventToken = $eventToken;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getEventToken()
    {
        return $this->eventToken;
    }

    /**
     * @ignore <description>
     * @param string $lastName
     */
    public function setFirstName($firstName)
    {
        $this->FirstName = $firstName;
    }
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
     * Geburtsdatum im Format YYYY-MM-DD
     *
     * @param string $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->DateOfBirth = $dateOfBirth;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->DateOfBirth;
    }

    /**
     * @ignore <description>
     * @param string $firstName
     */
    public function setBdFirstName($firstName)
    {
        $this->bdFirstName = $firstName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBdFirstName()
    {
        return $this->bdFirstName;
    }

    /**
     * @ignore <description>
     * @param string $lastName
     */
    public function setBdLastName($lastName)
    {
        $this->bdLastName = $lastName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBdLastName()
    {
        return $this->bdLastName;
    }

    /**
     * @ignore <description>
     * @param string $salutation
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }


    /**
     * @ignore <description>
     * @param string $mobileNr
     */
    public function setMobileNr($mobileNr)
    {
        $this->MobileNr = $mobileNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getMobileNr()
    {
        return $this->MobileNr;
    }

    /**
     * @ignore <description>
     * @param string $packingStation
     */
    public function setPackingStation($packingStation)
    {
        $this->PackingStation = $packingStation;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getPackingStation()
    {
        return $this->PackingStation;
    }

    /**
     * @ignore <description>
     * @param string $bdAddressAddition
     */
    public function setBdAddressAddition($bdAddressAddition)
    {
        $this->bdAddressAddition = $bdAddressAddition;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBdAddressAddition()
    {
        return $this->bdAddressAddition;
    }

    /**
     * @ignore <description>
     * @param string $firstName
     */
    public function setSdFirstName($firstName)
    {
        $this->sdFirstName = $firstName;
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
     * @param string $lastName
     */
    public function setSdLastName($lastName)
    {
        $this->sdLastName = $lastName;
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
     * @param string $bdCity
     */
    public function setBdCity($bdCity)
    {
        $this->bdCity = $bdCity;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBdCity()
    {
        return $this->bdCity;
    }

    /**
     * @ignore <description>
     * @param string $bdCountryCode
     */
    public function setBdCountryCode($bdCountryCode)
    {
        $this->bdCountryCode = $bdCountryCode;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBdCountryCode()
    {
        return $this->bdCountryCode;
    }

    /**
     * @ignore <description>
     * @param string $bdStreet
     */
    public function setBdStreet($bdStreet)
    {
        $this->bdStreet = $bdStreet;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBdStreet()
    {
        return $this->bdStreet;
    }

    /**
     * @ignore <description>
     * @param string $bdStreetNr
     */
    public function setBdStreetNr($bdStreetNr)
    {
        $this->bdStreetNr = $bdStreetNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBdStreetNr()
    {
        return $this->bdStreetNr;
    }

    /**
     * @ignore <description>
     * @param int $bdZip
     */
    public function setBdZip($bdZip)
    {
        $this->bdZip = $bdZip;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getBdZip()
    {
        return $this->bdZip;
    }

    /**
     * @ignore <description>
     * @param string $sdAddressAddition
     */
    public function setSdAddressAddition($sdAddressAddition)
    {
        $this->sdAddressAddition = $sdAddressAddition;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdAddressAddition()
    {
        return $this->sdAddressAddition;
    }

    /**
     * @ignore <description>
     * @param string $sdCity
     */
    public function setSdCity($sdCity)
    {
        $this->sdCity = $sdCity;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdCity()
    {
        return $this->sdCity;
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
     * @param string $sdStreetNr
     */
    public function setSdStreetNr($sdStreetNr)
    {
        $this->sdStreetNr = $sdStreetNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdStreetNr()
    {
        return $this->sdStreetNr;
    }

    /**
     * @ignore <description>
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }



    /**
     * EasyCredit constructor
     *
     * @param $config
     * @param CTOrder $order
     * @param $urlSuccess
     * @param $urlFailure
     * @param $urlNotify
     * @param $orderDesc
     * @param $userData
     * @param $eventToken
     */
    public function __construct(
        $config,
        $order,
        $urlSuccess,
        $urlFailure,
        $urlNotify,
        $orderDesc,
        $userData,
        $eventToken
    )
    {
        parent::__construct($config, $order, $orderDesc, $userData);

        $this->setUrlSuccess($urlSuccess);
        $this->setUrlFailure($urlFailure);
        $this->setUrlNotify($urlNotify);

        $this->setShippingAddress($order->getShippingAddress());
        $this->setBillingAddress($order->getBillingAddress());
        $this->setEmail($order->getEmail());

        if ($order->getBillingAddress()) {
            $this->setFirstName($order->getBillingAddress()->getFirstName());
            $this->setLastName($order->getBillingAddress()->getLastName());
            $Salutation = $order->getBillingAddress()->getSalutation() == 'Herr' ? 'Mr' : 'Mrs';
            $this->setSalutation($Salutation);
        }
        $this->setEventToken($eventToken);
        $this->setVersion('v3');
    }

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
    public function getBillToCustomer()
    {
        return $this->billToCustomer;
    }

    /**
     * Sets all address fields for shipping address
     * @param $shippingAddress CTAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        if (isset($shippingAddress)) {
            $Salutation = $shippingAddress->getSalutation() == 'Herr' ? 'Mr' : 'Mrs';
            $this->setSalutation($Salutation);
            $this->setSdFirstName($shippingAddress->getFirstName());
            $this->setSdLastName($shippingAddress->getLastName());
            $this->setSdStreet($shippingAddress->getStreet());
            $this->setSdStreetNr($shippingAddress->getStreetNr());
            $this->setSdZip($shippingAddress->getZip());
            $this->setSdCity($shippingAddress->getCity());
            $this->setSdCountryCode($shippingAddress->getCountryCode());
        }
    }

    /**
     * Sets all address fields for billing address
     * @param $billingAddress CTAddress
     */
    public function setBillingAddress($billingAddress)
    {
        if (isset($billingAddress)) {
            $Salutation = $billingAddress->getSalutation() == 'Herr' ? 'Mr' : 'Mrs';
            $this->setBdFirstName($billingAddress->getFirstName());
            $this->setBdLastName($billingAddress->getLastName());
            $this->setSalutation($Salutation);
            $this->setBdStreet($billingAddress->getStreet());
            $this->setBdStreetNr($billingAddress->getStreetNr());
            $this->setBdZip($billingAddress->getZip());
            $this->setBdCity($billingAddress->getCity());
            $this->setBdCountryCode($billingAddress->getCountryCode());
            $this->setSalutation($billingAddress->getSalutation());
        }
    }

    /**
     * returns paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/easyCredit.aspx';
    }

    /**
     * returns creditcheckurl
     * @return string
     */
    public function getCTCreditCheckURL()
    {
        return 'https://www.computop-paygate.com/easyCreditDirect.aspx';
    }

    /**
     * Gets parameters to prepare for the getDecision server to server call
     *
     * @param $payID
     * @param $transID
     * @param $amount
     * @param $currency
     * @return array
     */
    public function getDecisionParams($payID, $transID, $amount, $currency)
    {
        $params = [
            'payID' => $payID,
            'merchantID' => $this->merchantID,
            'transID' => $transID,
            'Amount' => $amount,
            'currency' => $currency,
            'RefNr' => Registry::getSession()->getId(),
            'EventToken' => 'GET'
        ];
        return $params;
    }

    /**
     * gets params to prepare for confirm call
     * @param $payID
     * @return array
     */
    public function getConfirmParams($payID)
    {
        $this->setPayID($payID);
        return $this->getRedirectUrlParams();
    }

    /**
     * Gets the decision from Easycredit by making a server to server call
     * @param $ctRequest
     * @return string
     */
    public function getDecision($ctRequest)
    {
        return $this->prepareComputopRequest($ctRequest, $this->getCTCreditCheckURL());
    }

    /**
     * Calls confirm as a server to server call for EasyCredit
     * @param $ctRequest
     * @return string
     */
    public function confirm($ctRequest)
    {
        return  $this->prepareComputopRequest($ctRequest, $this->getCTCreditCheckURL());
    }
}
