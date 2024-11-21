<?php
/** @noinspection PhpUnused */

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
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

namespace Fatchip\CTPayment\CTAddress;
/**
 * Class CTAddress
 * @package Fatchip\CTPayment\CTAddress
 */
class CTAddress
{
    /**
     * Salutation
     * @var string
     */
    private $salutation;

    /**
     * company
     * @var
     */
    private $company;

    /**
     * First name
     * @var string
     */
    private $firstName;

    /**
     * Last name
     * @var string
     */
    private $lastName;

    /**
     * street
     * @var string
     */
    private $street;

    /**
     * street2
     * @var string
     */
    private $street2;

    /**
     * streetNr
     * @var string
     */
    private $streetNr;
    /**
     * ZIP code
     * @var string
     */
    private $zip;

    /**
     * City
     * @var string
     */
    private $city;

    /**
     * State
     * @var string
     */
    private $state;

    /**
     * country Code
     * @var string
     */
    private $countryCode;

    /**
     * Country code ISO 3
     * @var string
     */
    private $countryCodeIso3;

    /**
     * CTAddress constructor
     *
     * @param $salutation
     * @param $company
     * @param $firstName
     * @param $lastName
     * @param $street
     * @param $streetNr
     * @param $zip
     * @param $city
     * @param $countryCode
     * @param $countryCodeIso3
     * @param string $street2
     * @param string $state
     */
    public function __construct(
        $salutation,
        $company,
        $firstName,
        $lastName,
        $street,
        $streetNr,
        $zip,
        $city,
        $countryCode,
        $countryCodeIso3,
        $street2 = '',
        $state = ''
    ) {
        $this->setSalutation($salutation);
        $this->setCompany($company);
        $this->city = $city;
        $this->countryCode = $countryCode;
        $this->countryCodeIso3 = $countryCodeIso3;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->state = $state;
        $this->street = $street;
        $this->street2 = $street2;
        $this->streetNr = $streetNr;
        $this->zip = $zip;
    }




    /**
     * @ignore <description>
     * @param mixed $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @ignore <description>
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @ignore <description>
     * @param mixed $countryCocde
     */
    public function setCountryCode($countryCocde)
    {
        $this->countryCode = $countryCocde;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @ignore <description>
     * @param mixed $countryCodeIso3
     */
    public function setCountryCodeIso3($countryCodeIso3) {
        $this->countryCodeIso3 = $countryCodeIso3;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getCountryCodeIso3() {
        return $this->countryCodeIso3;
    }

    /**
     * @ignore <description>
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @ignore <description>
     * @param mixed $salutation
     */
    public function setSalutation($salutation) {
        $this->salutation = $salutation;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getSalutation() {
        return $this->salutation;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @ignore <description>
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @ignore <description>
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @ignore <description>
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @ignore <description>
     * @param mixed $street2
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * @ignore <description>
     * @param mixed $streetNr
     */
    public function setStreetNr($streetNr)
    {
        $this->streetNr = $streetNr;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getStreetNr()
    {
        return $this->streetNr;
    }

    /**
     * @ignore <description>
     * @param mixed $company
     */
    public function setCompany($company) {
        $this->company = $company;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getCompany() {
        return $this->company;
    }


}
