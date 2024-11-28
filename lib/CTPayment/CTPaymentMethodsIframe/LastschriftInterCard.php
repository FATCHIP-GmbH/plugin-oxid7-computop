<?php
/** @noinspection PhpUnused */

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Oxid Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Oxid Plugin. If not, see <http://www.gnu.org/licenses/>.
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

/**
 * Class LastschriftInterCard
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class LastschriftInterCard extends Lastschrift
{
    const paymentClass = 'LastschriftInterCard';

    /**
     * Bestimmt Art und Zeitpunkt der Buchung (engl. Capture).
     * AUTO: Buchung so-fort nach Autorisierung (Standardwert).
     * MANUAL: Buchung erfolgt durch den Händler.
     * <Zahl>: Verzögerung in Stunden bis zur Buchung (ganze Zahl; 1 bis 696).
     *
     * @var string
     */
    protected $capture; //AUTO, MANUAL, ZAHL

    /**
     * Kundennummer beim Händler
     *
     * @var string
     */
    protected $customerID;

    /**
     * Vorname der Rechnungsanschrift
     *
     * @var string
     */
    protected $bdFirstName;

    /**
     *  Nachname der Rechnungsanschrift
     *
     * @var string
     */
    protected $bdLastName;

    /**
     * Straßenname der Rechnungsanschrift
     *
     * @var string
     */
    protected $bdStreet;

    /**
     * Hausnummer der Rechnungsanschrift
     *
     * @var string
     */
    protected $bdStreetNr;

    /**
     * Postleitzahl der Rechnungsanschrift
     *
     * @var int
     */
    protected $bdZip;

    /**
     * Ortsname der Rechnungsanschrift
     *
     * @var string
     */
    protected $bdCity;

    /**
     * LastschriftIntercard constructor
     *
     * @param array $config
     * @param CTOrder|null $order
     * @param null|string $urlSuccess
     * @param null|string $urlFailure
     * @param $urlNotify
     * @param $orderDesc
     * @param $userData
     * @param $capture
     * @param $orderDesc2
     */
    public function __construct(
        $config,
        $order,
        $urlSuccess,
        $urlFailure,
        $urlNotify,
        $orderDesc,
        $userData,
        $capture,
        /** @noinspection PhpUnusedParameterInspection */
        $orderDesc2
    ) {
        parent::__construct($config, $order, $urlSuccess, $urlFailure, $urlNotify, $orderDesc, $userData, $capture);
        $this->setCustomerID($order->getCustomerID());
        $this->setBillingAddress($order->getBillingAddress());
    }

    /**
     * Sets all adressfields for billing address
     * @param CTAddress $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        //for companies, first name must be empty
        $this->setBdLastName($billingAddress->getLastName());
        $this->setBdStreet($billingAddress->getStreet());
        $this->setBdStreetNr($billingAddress->getStreetNr());
        $this->setBdZip($billingAddress->getZip());
        $this->setBdCity($billingAddress->getCity());
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCustomerID()
    {
        return $this->customerID;
    }

    /**
     * @ignore <description>
     * @param string $customerID
     */
    public function setCustomerID($customerID)
    {
        $this->customerID = $customerID;
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
     * @param string $bdFirstName
     */
    public function setBdFirstName($bdFirstName)
    {
        $this->bdFirstName = $bdFirstName;
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
     * @param string $bdLastName
     */
    public function setBdLastName($bdLastName)
    {
        $this->bdLastName = $bdLastName;
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
    public function getBdStreetNr()
    {
        return $this->bdStreetNr;
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
     * @return int
     */
    public function getBdZip()
    {
        return $this->bdZip;
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
     * @return string
     */
    public function getBdCity()
    {
        return $this->bdCity;
    }

    /**
     * @ignore <description>
     * @param string $bdCity
     */
    public function setBdCity($bdCity)
    {
        $this->bdCity = $bdCity;
    }
}
