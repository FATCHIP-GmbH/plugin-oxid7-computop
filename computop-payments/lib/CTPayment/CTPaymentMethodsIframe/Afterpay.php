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
class Afterpay extends CTPaymentMethodIframe
{
    const paymentClass = 'Afterpay';

    /**
     * Zahlungsmethode
     * „Invoice“ für Rechnung
     * „Account“ für flexibler Zahlungsplan
     * „Installment“ für Ratenzahlungen
     * „ConsolidatedInvoice“ für konsolidierte Rechnung
     *
     * Manadatory Parameter
     *
     * @var string
     */
    protected $PayType;

    /**
     * Bank Identifier Code (Gültig bei AddrCountryCode = “DE”)
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $BIC;

    /**
     * International Bank Account Number (Gültig bei AddrCountryCode = “DE”)
     * Optional Parameter
     *
     * @var string
     */
    protected $IBAN;

    /**
     * Profilnummer
     * Pflicht, wenn PayType = „Account“ oder „Installment“
     * Conditional Parameter
     *
     * @var string
     */
    protected $ProductNr;

    /**
     * Datum der Rechnungsstellung im Format YYYY-MM-DD.
     * Nur bei PayType = „ConsolidatedInvoice“
     * Conditional Parameter
     *
     * @var string
     */
    protected $InvoiceDate;

    /**
     * Kundenkategorie:
     * „Company“ für Unternehmen
     * „Person“ für Personen (Standardwert)
     * Optional Parameter
     *
     * @var string
     */
    protected $bdCompanyOrPerson;

    /**
     * Sozialversicherungsnummer bei bdCompanyOrPerson = „Person“
     * Registrierungsnummer bei bdCompanyOrPerson = „Company“
     * Gültig bei AddrCountryCode = „FI“, „SE“ oder „NO“
     *
     * Conditional Parameter
     *
     * @var string
     */
    protected $SocialSecurityNumber;

    /**
     * Ländercode:
     * „NO“, „SE“, „FI“, „DK“, „DE“, „AT“, “CH”, “NL”, “BE”
     *
     * Mandatory Parameter
     *
     * @var string
     */
    protected $AddrCountryCode;

    /**
     * Postleitzahl
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $AddrZip;

    /**
     * Postleitzahl
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdAddrZip;

    /**
     * Straße oder Packstation
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $AddrStreet;

    /**
     * Haus- oder Packstationsnummer
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $AddrStreetNr;

    /**
     * Straßennummernzusatz
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $AddrStreetNr2;

    /**
     * Ortsname
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $AddrCity;

    /**
     * Vorname des Kunden
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $FirstName;

    /**
     * Nachname des Kunden
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $LastName;

    /**
     * Eindeutige Kundennummer
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $CustomerID;

    /**
     * Anrede:
     * „Mr“ für Herr
     * „Mrs“ für Frau
     * „Miss“ für Fräulein
     * Gültig bei AddrCountryCode = „DE“, „DK“,“NL“, „BE“, „AT“ oder „CH“
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $Salutation;

    /**
     * E-Mail-Adresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $bdEmail;

    /**
     * Telefonnummer
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $bdPhone;

    /**
     * Handynummer
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $bdMobileNo;

    /**
     * Geburtsdatum im Format YYYY-MM-DD.
     * Gültig bei AddrCountryCode = „DE“, „NL“, „DK“, „BE“ oder „AT“
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $DateOfBirth;

    /**
     * Risikorelevante Daten als JSON-String im Base64-Format (siehe JSON-Objekte
     * / CustomerRisk)
     * Händler können externe Risikoprüfungen durchführen und AfterPay diese Infor-
     * mationen zusenden.
     * Gültig bei AddrCountryCode = „DE“
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $CustomerRisk;

    /**
     * Sprache: „NO“, „SE“, „FI“, „DK”, “EN”, “DE”, “NL”, “FR” zulässig
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $Language;

    /**
     * Kundenkategorie:
     * „Company“ für Unternehmen
     * „Person“ für Personen (Standardwert)
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $CompanyOrPerson;

    /**
     * Ländercode der Lieferadresse: „NO“, „SE“, „FI“, „DK“, „DE“, „AT“, “CH”, “NL”,
     * “BE”
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdCountryCode;

    /**
     * Straße oder Packstation der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdStreet;

    /**
     * Haus- oder Packstationsnummer der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdStreetNr;

    /**
     * Straßennummernzusatz der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdStreetNr2;

    /**
     * Ort der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdCity;

    /**
     * Vorname der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdFirstName;

    /**
     * Nachname der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdLastName;

    /**
     * Anrede der Lieferadresse
     *  „Mr“ für Herr
     * „Mrs“ für Frau
     * „Miss“ für Fräulein
     * Gültig bei AddrCountryCode = „DE“, „DK“,“NL“, „BE“, „AT“ oder „CH“
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $sdSalutation;

    /**
     * E-Mail-Adresse der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $Email;

    /**
     * Telefonnummer der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $Phone;

    /**
     * Handynummer der Lieferadresse
     *
     * Optional Parameter
     *
     * @var string
     */
    protected $MobileNo;

    /**
     * Bestellinformationen als JSON-String im Base64-Format.
     * Siehe JSON-Objekt / Order
     *
     * Mandatory Parameter
     *
     * @var string
     */
    protected $Order;

    /**
     * Afterpay constructor
     *
     * @param array $config
     * @param CTOrder|null $order
     * @param null|string $urlSuccess
     * @param null|string $urlFailure
     * @param $urlNotify
     * @param $orderDesc
     * @param $userData
     */
    public function __construct(
        $config,
        $order,
        $urlSuccess,
        $urlFailure,
        $urlNotify,
        $orderDesc,
        $userData
    )
    {
        parent::__construct($config, $order, $orderDesc, $userData);
        $this->setUrlSuccess($urlSuccess);
        $this->setUrlFailure($urlFailure);
        $this->setUrlNotify($urlNotify);
        $this->setBillingAddress($order->getBillingAddress());
        $this->setShippingAddress($order->getShippingAddress());
        $this->setBdEmail($order->getEmail());
        $this->setEmail($order->getEmail());

    }

    /**
     * returns PaymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/afterpay.aspx';
    }

    /**
     * sets all address fields for shipping address
     * @param CTAddress $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->CompanyOrPerson = $shippingAddress->getCompany() == '' ? 'Person' : 'Company';
        $this->sdSalutation = $shippingAddress->getSalutation() == 'Herr' ? 'Mr' : 'Mrs';
        $this->setSdFirstName($shippingAddress->getFirstName());
        $this->setSdLastName($shippingAddress->getLastName());
        $this->setSdStreet($shippingAddress->getStreet());
        $this->setSdStreetNr($shippingAddress->getStreetNr());
        $this->setSdZip($shippingAddress->getZip());
        $this->setSdCity($shippingAddress->getCity());
        $this->setSdCountryCode($shippingAddress->getCountryCode());
    }

    /**
     * sets all address fields for shipping address
     * @param CTAddress $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {

        $this->bdCompanyOrPerson = $billingAddress->getCompany() == '' ? 'Person' : 'Company';
        $this->Salutation = $billingAddress->getSalutation() == 'Herr' ? 'Mr' : 'Mrs';
        $this->setFirstName($billingAddress->getFirstName());
        $this->setLastName($billingAddress->getLastName());
        $this->setAddrStreet($billingAddress->getStreet());
        $this->setAddrStreetNr($billingAddress->getStreetNr());
        $this->setAddrZip($billingAddress->getZip());
        $this->setAddrCity($billingAddress->getCity());
        $this->setAddrCountryCode($billingAddress->getCountryCode());
    }

    /**
     * sets mandatory JSON orderinfo
     *
     * @param $basket
     */
    public function setOrder($basket)
    {
        $order = [];
        $orderItem = [];
        $orderVariables = Shopware()->Session()->offsetGet('sOrderVariables')->getArrayCopy();

        $order['totalGrossAmount'] = (string) $orderVariables['sAmount'];
        $order['totalNetAmount'] = (string) $orderVariables['sAmountNet'];
        $order['currency'] = "EUR";

        $i = 0;
        foreach ($basket['content'] as $article) {
            $orderItem[$i]['productId'] = $article['ordernumber'];
            $orderItem[$i]['description'] = $article['articlename'];
            $orderItem[$i]['grossUnitPrice'] = str_replace(',', '.',$article['price']);
            $orderItem[$i]['netUnitPrice'] = round($article['netprice'], 2);
            $orderItem[$i]['vatAmount'] = $orderItem[$i]['grossUnitPrice'] - $orderItem[$i]['netUnitPrice'] ;
            $orderItem[$i]['vatPercent'] = $article['tax_rate'];
            $orderItem[$i]['quantity'] = $article['quantity'];
            $i++;
        }

        // handle shipping costs
        $shipping = $orderVariables['sDispatch'];
        if (is_array($shipping)) {
            $taxFactor = (float)('1.' . $shipping['max_tax']);

            $orderItem[$i]['productId'] = (string)$shipping['id'];
            $orderItem[$i]['description'] = $shipping['name'];
            $orderItem[$i]['grossUnitPrice'] = (string)$orderVariables['sShippingcosts'];
            $orderItem[$i]['netUnitPrice'] = round($orderVariables['sShippingcosts'] / $taxFactor, 2);
            $orderItem[$i]['vatAmount'] = (float)($orderVariables['sShippingcosts'] - $orderItem[$i]['netUnitPrice']);
            $orderItem[$i]['vatPercent'] = (string)$shipping['max_tax'];
            $orderItem[$i]['quantity'] = (string)1;
        }
        $order['items'] = $orderItem;
        $jsonTmp = json_encode($order);
        $this->Order = base64_encode($jsonTmp);
    }

    /**
     * @return string
     */
    public function getPayType()
    {
        return $this->PayType;
    }

    /**
     * @param string $PayType
     */
    public function setPayType(string $PayType)
    {
        $this->PayType = $PayType;
    }

    /**
     * @return string
     */
    public function getBIC()
    {
        return $this->BIC;
    }

    /**
     * @param string $BIC
     */
    public function setBIC(string $BIC)
    {
        $this->BIC = $BIC;
    }

    /**
     * @return string
     */
    public function getIBAN()
    {
        return $this->IBAN;
    }

    /**
     * @param string $IBAN
     */
    public function setIBAN(string $IBAN)
    {
        $this->IBAN = $IBAN;
    }

    /**
     * @return string
     */
    public function getProductNr()
    {
        return $this->ProductNr;
    }

    /**
     * @param string $ProductNr
     */
    public function setProductNr(string $ProductNr)
    {
        $this->ProductNr = $ProductNr;
    }

    /**
     * @return string
     */
    public function getInvoiceDate()
    {
        return $this->InvoiceDate;
    }

    /**
     * @param string $InvoiceDate
     */
    public function setInvoiceDate(string $InvoiceDate)
    {
        $this->InvoiceDate = $InvoiceDate;
    }

    /**
     * @return string
     */
    public function getBdCompanyOrPerson()
    {
        return $this->bdCompanyOrPerson;
    }

    /**
     * @param string $bdCompanyOrPerson
     */
    public function setBdCompanyOrPerson(string $bdCompanyOrPerson)
    {
        $this->bdCompanyOrPerson = $bdCompanyOrPerson;
    }

    /**
     * @return string
     */
    public function getSocialSecurityNumber()
    {
        return $this->SocialSecurityNumber;
    }

    /**
     * @param string $SocialSecurityNumber
     */
    public function setSocialSecurityNumber(string $SocialSecurityNumber)
    {
        $this->SocialSecurityNumber = $SocialSecurityNumber;
    }

    /**
     * @return string
     */
    public function getAddrCountryCode()
    {
        return $this->AddrCountryCode;
    }

    /**
     * @param string $AddrCountryCode
     */
    public function setAddrCountryCode(string $AddrCountryCode)
    {
        $this->AddrCountryCode = $AddrCountryCode;
    }

    /**
     * @return string
     */
    public function getAddrZip()
    {
        return $this->AddrZip;
    }

    /**
     * @param string $AddrZip
     */
    public function setAddrZip(string $AddrZip)
    {
        $this->AddrZip = $AddrZip;
    }

    /**
     * @return string
     */
    public function getAddrStreet()
    {
        return $this->AddrStreet;
    }

    /**
     * @param string $AddrStreet
     */
    public function setAddrStreet(string $AddrStreet)
    {
        $this->AddrStreet = $AddrStreet;
    }

    /**
     * @return string
     */
    public function getAddrStreetNr()
    {
        return $this->AddrStreetNr;
    }

    /**
     * @param string $AddrStreetNr
     */
    public function setAddrStreetNr(string $AddrStreetNr)
    {
        $this->AddrStreetNr = $AddrStreetNr;
    }

    /**
     * @return string
     */
    public function getAddrStreetNr2()
    {
        return $this->AddrStreetNr2;
    }

    /**
     * @param string $AddrStreetNr2
     */
    public function setAddrStreetNr2(string $AddrStreetNr2)
    {
        $this->AddrStreetNr2 = $AddrStreetNr2;
    }

    /**
     * @return string
     */
    public function getAddrCity()
    {
        return $this->AddrCity;
    }

    /**
     * @param string $AddrCity
     */
    public function setAddrCity(string $AddrCity)
    {
        $this->AddrCity = $AddrCity;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @param string $FirstName
     */
    public function setFirstName(string $FirstName)
    {
        $this->FirstName = $FirstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param string $LastName
     */
    public function setLastName(string $LastName)
    {
        $this->LastName = $LastName;
    }

    /**
     * @return string
     */
    public function getCustomerID()
    {
        return $this->CustomerID;
    }

    /**
     * @param string $CustomerID
     */
    public function setCustomerID(string $CustomerID)
    {
        $this->CustomerID = $CustomerID;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->Salutation;
    }

    /**
     * @param string $Salutation
     */
    public function setSalutation(string $Salutation)
    {
        $this->Salutation = $Salutation;
    }

    /**
     * @return string
     */
    public function getBdEmail()
    {
        return $this->bdEmail;
    }

    /**
     * @param string $bdEmail
     */
    public function setBdEmail(string $bdEmail)
    {
        $this->bdEmail = $bdEmail;
    }

    /**
     * @return string
     */
    public function getBdPhone()
    {
        return $this->bdPhone;
    }

    /**
     * @param string $bdPhone
     */
    public function setBdPhone(string $bdPhone)
    {
        $this->bdPhone = $bdPhone;
    }

    /**
     * @return string
     */
    public function getBdMobileNo()
    {
        return $this->bdMobileNo;
    }

    /**
     * @param string $bdMobileNo
     */
    public function setBdMobileNo(string $bdMobileNo)
    {
        $this->bdMobileNo = $bdMobileNo;
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->DateOfBirth;
    }

    /**
     * @param string $DateOfBirth
     */
    public function setDateOfBirth(string $DateOfBirth)
    {
        $this->DateOfBirth = $DateOfBirth;
    }

    /**
     * @return string
     */
    public function getCustomerRisk()
    {
        return $this->CustomerRisk;
    }

    /**
     * @param string $CustomerRisk
     */
    public function setCustomerRisk(string $CustomerRisk)
    {
        $this->CustomerRisk = $CustomerRisk;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->Language;
    }

    /**
     * @param string $Language
     */
    public function setLanguage(string $Language)
    {
        $this->Language = $Language;
    }

    /**
     * @return string
     */
    public function getCompanyOrPerson()
    {
        return $this->CompanyOrPerson;
    }

    /**
     * @param string $CompanyOrPerson
     */
    public function setCompanyOrPerson(string $CompanyOrPerson)
    {
        $this->CompanyOrPerson = $CompanyOrPerson;
    }

    /**
     * @return string
     */
    public function getSdCountryCode()
    {
        return $this->sdCountryCode;
    }

    /**
     * @param string $sdCountryCode
     */
    public function setSdCountryCode(string $sdCountryCode)
    {
        $this->sdCountryCode = $sdCountryCode;
    }

    /**
     * @return string
     */
    public function getSdAddrZip()
    {
        return $this->sdAddrZip;
    }

    /**
     * @param string $sdAddrZip
     */
    public function setSdAddrZip(string $sdAddrZip)
    {
        $this->sdAddrZip = $sdAddrZip;
    }

    /**
     * @return string
     */
    public function getSdStreet()
    {
        return $this->sdStreet;
    }

    /**
     * @param string $sdStreet
     */
    public function setSdStreet(string $sdStreet)
    {
        $this->sdStreet = $sdStreet;
    }

    /**
     * @return string
     */
    public function getSdStreetNr()
    {
        return $this->sdStreetNr;
    }

    /**
     * @param string $sdStreetNr
     */
    public function setSdStreetNr(string $sdStreetNr)
    {
        $this->sdStreetNr = $sdStreetNr;
    }

    /**
     * @return string
     */
    public function getSdStreetNr2()
    {
        return $this->sdStreetNr2;
    }

    /**
     * @param string $sdStreetNr2
     */
    public function setSdStreetNr2(string $sdStreetNr2)
    {
        $this->sdStreetNr2 = $sdStreetNr2;
    }

    /**
     * @return string
     */
    public function getSdCity()
    {
        return $this->sdCity;
    }

    /**
     * @param string $sdCity
     */
    public function setSdCity(string $sdCity)
    {
        $this->sdCity = $sdCity;
    }

    /**
     * @return string
     */
    public function getSdFirstName()
    {
        return $this->sdFirstName;
    }

    /**
     * @param string $sdFirstName
     */
    public function setSdFirstName(string $sdFirstName)
    {
        $this->sdFirstName = $sdFirstName;
    }

    /**
     * @return string
     */
    public function getSdLastName()
    {
        return $this->sdLastName;
    }

    /**
     * @param string $sdLastName
     */
    public function setSdLastName(string $sdLastName)
    {
        $this->sdLastName = $sdLastName;
    }

    /**
     * @return string
     */
    public function getSdSalutation()
    {
        return $this->sdSalutation;
    }

    /**
     * @param string $sdSalutation
     */
    public function setSdSalutation(string $sdSalutation)
    {
        $this->sdSalutation = $sdSalutation;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * @param string $Email
     */
    public function setEmail(string $Email)
    {
        $this->Email = $Email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->Phone;
    }

    /**
     * @param string $Phone
     */
    public function setPhone(string $Phone)
    {
        $this->Phone = $Phone;
    }

    /**
     * @return string
     */
    public function getMobileNo()
    {
        return $this->MobileNo;
    }

    /**
     * @param string $MobileNo
     */
    public function setMobileNo(string $MobileNo)
    {
        $this->MobileNo = $MobileNo;
    }

}
