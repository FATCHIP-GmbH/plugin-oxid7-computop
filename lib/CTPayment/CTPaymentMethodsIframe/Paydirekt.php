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
use Fatchip\CTPayment\CTPaymentMethodIframe;
use Fatchip\CTPayment\CTOrder\CTOrder;
use RuntimeException;

/**
 * Class Paydirekt
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class Paydirekt extends CTPaymentMethodIframe
{

    const paymentClass = 'Paydirekt';

    /**
     * Bestimmt Art und Zeitpunkt der Buchung (engl. Capture).
     * AUTO: Buchung so-fort nach der Autorisierung (Standardwert).
     * MANUAL: Buchung erfolgt durch den Händler.
     * <Zahl>: Verzögerung in Stunden bis zur Buchung (ganze Zahl; 1 bis 696).
     *
     * @var string
     */
    protected $capture;

    /**
     * API-Key des Shops bei paydirekt
     *
     * @var string
     */
    protected $ShopApiKey;
    /**
     * Warenwert der Bestellung ohne Versandkosten in der kleinsten Währungsein-heit (z.B. EUR Cent)
     * Bitte wenden Sie sich an den Helpdesk, wenn Sie Beträge < 100 (kleinste Wäh-rungseinheit) buchen möchten.
     *
     * @var int
     */
    protected $ShoppingBasketAmount;

    /**
     * Vorname in der Lieferanschrift
     * Pflicht, wenn ShoppingBasketCategory <> „AU-THORITIES_PAYMENT“ und <> „ANONYMOUS_DONATION“
     *
     * @var string
     */
    protected $sdFirstName;

    /**
     * Nachname in der Lieferanschrift.
     * Pflicht, wenn ShoppingBasketCategory <> „AUTHORITIES_PAYMENT“ und <> „ANONYMOUS_DONATION“
     *
     * @var string
     */
    protected $sdLastName;

    /**
     * Optional. Straßenname in der Lieferanschrift
     *
     * @var string
     */
    protected $sdStreet;

    /**
     * Optional: Hausnummer in der Lieferanschrift
     * @var string
     */

    protected $sdStreetNr;

    /**
     * Ort in der Lieferanschrift #
     * Pflicht, wenn ShoppingBasketCategory <> "DIGITAL" und <> "AUTHORI-TIES_PAYMENT" und <> "ANONYMOUS_DONATION"
     *
     * @var string
     */
    protected $sdCity;

    /**
     * Ländercode in der Lieferanschrift (2stellig)
     * Pflicht, wenn ShoppingBasketCategory <> "DIGITAL" und <> "AUTHORI-TIES_PAYMENT" und <> "ANONYMOUS_DONATION"
     *
     * @var string
     */
    protected $sdCountryCode;

    /**
     * E-Mail-Adresse des Empfängers
     * Pflicht, wenn ShoppingBasketCategory = „DIGITAL“
     *
     * @var string
     */
    protected $sdEmail;


    /**
     * Paydirekt constructor
     * @param array $config
     * @param CTOrder|null $order
     * @param null|string $urlSuccess
     * @param null|string $urlFailure
     * @param $urlNotify
     * @param $orderDesc
     * @param $userData
     *
     * @throws RuntimeException
     */
    public function __construct(
        $config,
        $order,
        $urlSuccess,
        $urlFailure,
        $urlNotify,
        $orderDesc,
        $userData
    ) {
        parent::__construct($config, $order, $orderDesc, $userData);

        $this->setUrlSuccess($urlSuccess);
        $this->setUrlFailure($urlFailure);
        $this->setUrlNotify($urlNotify);

        if (empty($config['payDirektShopApiKey'])) {
            throw new RuntimeException('Paydirekt ShopApiKey is not set in Plugin Config');
        }

        $this->setShopApiKey($config['payDirektShopApiKey']);

        $this->setShippingAddress($order->getShippingAddress());

        $this->setCapture('MANUAL');
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
     * @param string $shopAPIKey
     */
    public function setShopApiKey($shopAPIKey)
    {
        $this->ShopApiKey = $shopAPIKey;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getShopApiKey()
    {
        return $this->ShopApiKey;
    }

    /**
     * @ignore <description>
     * @param int $shoppingBasketAmount
     */
    public function setShoppingBasketAmount($shoppingBasketAmount)
    {
        $this->ShoppingBasketAmount = $shoppingBasketAmount;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getShoppingBasketAmount()
    {
        return $this->ShoppingBasketAmount;
    }



    /**
     * @ignore <description>
     * @param int $sdZip
     */
    public function setZip($sdZip)
    {
        $this->sdZip = $sdZip;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getZip()
    {
        return $this->sdZip;
    }

    /**
     * @ignore <description>
     * @param string $sdCity
     */
    public function setCity($sdCity)
    {
        $this->sdCity = $sdCity;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCity()
    {
        return $this->sdCity;
    }

    /**
     * @ignore <description>
     * @param string $sdCountryCode
     */
    public function setCountryCode($sdCountryCode)
    {
        $this->sdCountryCode = $sdCountryCode;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCountryCode()
    {
        return $this->sdCountryCode;
    }

    /**
     * @ignore <description>
     * @param string $sdEmail
     */
    public function setEmail($sdEmail)
    {
        $this->sdEmail = $sdEmail;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getEmail()
    {
        return $this->sdEmail;
    }

    /**
     * @ignore <description>
     * @param string $sdFirstName
     */
    public function setFirstName($sdFirstName)
    {
        $this->sdFirstName = $sdFirstName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getFirstName()
    {
        return $this->sdFirstName;
    }

    /**
     * @ignore <description>
     * @param string $sdLastName
     */
    public function setLastName($sdLastName)
    {
        $this->sdLastName = $sdLastName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getLastName()
    {
        return $this->sdLastName;
    }

    /**
     * @ignore <description>
     * @param string $sdStreet
     */
    public function setStreet($sdStreet)
    {
        $this->sdStreet = $sdStreet;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getStreet()
    {
        return $this->sdStreet;
    }

    /**
     * @ignore <description>
     * @param string $sdsdStreetNr
     */
    public function setStreetNr($sdsdStreetNr)
    {
        $this->sdStreetNr = $sdsdStreetNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getStreetNr()
    {
        return $this->sdStreetNr;
    }

    /**
     * sets all address fields for shipping address
     * @param $shippingAddress CTAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->setFirstName($shippingAddress->getFirstName());
        $this->setLastName($shippingAddress->getLastName());
        $this->setStreet($shippingAddress->getStreet());
        $this->setStreetNr($shippingAddress->getStreetNr());
        $this->setZip($shippingAddress->getZip());
        $this->setCity($shippingAddress->getCity());
        $this->setCountryCode($shippingAddress->getCountryCode());
    }

    /**
     * returns paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/paydirekt.aspx';
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

}
