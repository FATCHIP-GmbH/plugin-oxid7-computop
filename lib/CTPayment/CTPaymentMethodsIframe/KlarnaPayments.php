<?php /** @noinspection PhpUnused */

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
use Monolog\Registry;

/**
 * Class PaypalStandard
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class KlarnaPayments extends CTPaymentMethodIframe
{

    const paymentClass = 'KlarnaPayments';

    /**
     * Auto oder Manual: bestimmt, ob der angefragte Betrag sofort oder erst später abgebucht wird.
     * Wichtiger Hinweis: Bitte kontaktieren Sie den Computop Support für Manual,
     * um die unterschiedlichen Einsatzmöglichkeiten abzuklären.
     *
     * @var string
     */
    protected $capture;

    /**
     * Pflicht bei Capture=Manual:
     * Transaktionstyp mit den möglichen Werten Order oder Auth sowie BAID (BillingAgreementID)
     *
     * @var string
     */
    protected $TxType;

    /**
     * optional, plficht für USA und Canada:
     * Entweder nur der Vorname oder Vor- und Nach-name, falls ein Firmenname als Lieferadresse genutzt wird.
     *
     * @var string
     */
    protected $FirstName;

    /**
     * optional, plficht für USA und Canada: Nachname oder Firmenbezeichnung der Lieferad-resse
     *
     * @var string
     */
    protected $LastName;

    /**
     * optional, plficht für USA und Canada: Straßenname der Lieferadresse
     * @var string
     */
    protected $AddrStreet;

    /**
     * optional: Straßenname der Lieferadresse
     *
     * @var string
     */
    protected $AddrStreet2;

    /**
     * optional, plficht für USA und Canada:
     * Ortsname der Lieferadresse
     *
     * @var string
     */
    protected $AddrCity;

    /**
     * optional, plficht für USA und Canada:
     * Bundesland (Bundesstaat) der Lieferadresse. Die in addrCity übergebene Stadt muss im angegebenen Bundesstaat
     * liegen, sonst lehnt PayPal die Zahlung ab.
     * Mögliche Werte entnehmen Sie bitte der PayPal-API-Reference Dokumentation.
     *
     * @var string
     */
    protected $AddrState;
    /**
     * Rechnungsadresse
     *
     * @var string
     */
    protected $billingAddress;

    protected $billToCustomer;
    /**
     * optional, plficht für USA und Canada:
     * Postleitzahl der Lieferadresse
     *
     * @var string
     */
    protected $AddrZip;

    /**
     * optional, plficht für USA und Canada:
     * Ländercode des Lieferlandes (2stellig)
     *
     * @var string
     */
    protected $AddrCountryCode;

    protected $NoShipping = 1;

    protected $mode = 'redirect';

    /**
     * PaypalStandard constructor
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
        //TODO: Check if this should always be order
        $this->setTxType('Order');
        $this->setCapture('MANUAL');
    }

    /**
     * @param string $capture
     * @ignore <description>
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * @param string $txType
     * @ignore <description>
     */
    public function setTxType($txType)
    {
        $this->TxType = $txType;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getTxType()
    {
        return $this->TxType;
    }

    /**
     * @param string $addrCity
     * @ignore <description>
     */
    public function setAddrCity($addrCity)
    {
        $this->AddrCity = $addrCity;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getAddrCity()
    {
        return $this->AddrCity;
    }

    /**
     * @param string $addrCountryCode
     * @ignore <description>
     */
    public function setAddrCountryCode($addrCountryCode)
    {
        $this->AddrCountryCode = $addrCountryCode;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getAddrCountryCode()
    {
        return $this->AddrCountryCode;
    }

    /**
     * @param string $addrState
     * @ignore <description>
     */
    public function setAddrState($addrState)
    {
        $this->AddrState = $addrState;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getAddrState()
    {
        return $this->AddrState;
    }

    /**
     * @param string $addrStreet
     * @ignore <description>
     */
    public function setAddrStreet($addrStreet)
    {
        $this->AddrStreet = $addrStreet;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getAddrStreet()
    {
        return $this->AddrStreet;
    }

    /**
     * @param string $addrStreet2
     * @ignore <description>
     */
    public function setAddrStreet2($addrStreet2)
    {
        $this->AddrStreet2 = $addrStreet2;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getAddrStreet2()
    {
        return $this->AddrStreet2;
    }

    /**
     * @param string $addrZip
     * @ignore <description>
     */
    public function setAddrZip($addrZip)
    {
        $this->AddrZip = $addrZip;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getAddrZip()
    {
        return $this->AddrZip;
    }

    /**
     * @param string $firstName
     * @ignore <description>
     */
    public function setFirstName($firstName)
    {
        $this->FirstName = $firstName;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @return string
     */
    public function getBillToCustomer()
    {
        return $this->billToCustomer;
    }

    /**
     * @param string $lastName
     * @ignore <description>
     */
    public function setLastName($lastName)
    {
        $this->LastName = $lastName;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param int $NoShipping
     * @ignore <description>
     */
    public function setNoShipping($NoShipping)
    {
        $this->NoShipping = $NoShipping;
    }

    /**
     * @return int
     * @ignore <description>
     */
    public function getNoShipping()
    {
        return $this->NoShipping;
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

    public function setBillingAddress($CTAddress)
    {
        $this->billingAddress = base64_encode(json_encode($this->declareAddress($CTAddress)));
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
     * sets all addressfields for shipping address
     * @param $shippingAddress CTAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->setFirstName($shippingAddress->getFirstName());
        $this->setLastName($shippingAddress->getLastName());
        if (strlen($shippingAddress->getStreetNr() > 0)) {
            $this->setAddrStreet($shippingAddress->getStreet() . ' ' . $shippingAddress->getStreetNr());
        } else {
            $this->setAddrStreet($shippingAddress->getStreet());
        }

        $this->setAddrZip($shippingAddress->getZip());
        $this->setAddrCity($shippingAddress->getCity());
        $this->setAddrCountryCode($shippingAddress->getCountryCode());
    }


    /**
     * returns encoded url for a request with encoded Data and LEN queryparameters
     * @param $ctRequest
     * @return string
     */
    public function getHTTPGetURL($ctRequest)
    {
        return $this->prepareComputopRequest($ctRequest, $this->getCTPaymentURL());
    }


    /**
     * returns paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/KlarnaPaymentsHPP.aspx';
    }

    /**
     * returns captureURL
     * @return string
     */
    public function getCaptureURL()
    {
        return 'https://www.computop-paygate.com/capture.aspx';
    }

    /**
     * returns ReverseURL
     * @return string
     */
    public function getReverseURL()
    {
        return 'https://www.computop-paygate.com/reverse.aspx';
    }

    /**
     * used for recurring payments
     * returns RecurringURL
     * @return string
     */
    public function getRecurringURL()
    {
        return 'https://www.computop-paygate.com/KlarnaPayments.aspx';
    }

    public function getCTRefNrChangeURL()
    {
        return 'https://www.computop-paygate.com/RefNrChange.aspx';
    }

    /**
     * Sets and returns request parameters for reference number change api call
     *
     * @param $payId
     * @param $refNr
     * @return array
     * @deprecated
     *
     */
    public function getRefNrChangeParams($payId, $refNr)
    {
        return [
            'MerchantID' => $this->getMerchantID(),
            'TransID' => $this->getTransID(),
            'payID' => $payId,
            'RefNr' => $refNr
        ];
    }

}
