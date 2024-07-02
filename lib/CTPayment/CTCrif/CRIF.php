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
 * @subpackage CTCrif
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment\CTCrif;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethodIframe;

/**
 * Class CRIF
 * @package Fatchip\CTPayment\CTCrif
 */
class CRIF extends CTPaymentMethodIframe{

    const paymentClass = 'RiskCheck';

    /**
     * Prüfmethode: <QuickCheckConsumer>, <CreditCheckConsumer>, <QuickCheckBusiness>,
     * <Cre-ditCheckBusiness>, <IdentCheckConsumer>
     *
     * @var string
     */
    protected $productName;

    /**
     * Kundennummer/Kundenreferenz
     *
     * @var string
     */
    protected $customerID;

    /**
     * Last name
     * @var string
     */
    protected $lastName;

    /**
     * first name
     * @var string
     */
    protected  $firstName;

    /**
     * Street
     * @var string
     */
    protected $addrStreet;

    /**
     * Street Nr
     * @var string
     */
    protected $addrStreetNr;

    /**
     * City
     * @var string
     */
    protected $addrCity;


    /**
     * Ländercode dreistellig gemäß ISO 3166. Nur AUT, DEU, CHE, NLD zulässig..
     *
     * @var string
     */
    protected $AddrCountryCode;

    /**
     * ZIP-code
     * @var string
     */
    protected $addrZip;

    /**
     * Last name shipping address
     * @var string
     */
    protected $sdLastName;

    /**
     * First name shipping address
     * @var string
     */
    protected $sdFirstName;

    /**
     * Gender Shipping address
     * @var
     */
    protected $sdGender;

    /**
     * Street shipping address
     * @var
     */
    protected $sdStreet;

    /**
     * street nr shipping address
     * @var string
     */
    protected $sdStreetNr;

    /**
     * City shipping address
     * @var string
     */
    protected $sdCity;


    /**
     * Ländercode dreistellig gemäß ISO 3166. Nur AUT, DEU, CHE, NLD zulässig.
     *
     * @var string
     */
    protected $sdCountryCode;


    /**
     * CRIF constructor
     * @param $config
     * @param CTOrder $order
     * @param $orderDesc
     * @param $userData
     */
    public function __construct(
      $config,
      $order,
      $orderDesc,
      $userData
    )
    {
        parent::__construct($config, $order, $orderDesc, $userData);

        $this->setLastName($order->getBillingAddress()->getLastName());
        $this->setFirstName($order->getBillingAddress()->getFirstName());
        $this->setAddrStreet($order->getBillingAddress()->getStreet());
        $this->setAddrStreetNr($order->getBillingAddress()->getStreetNr());
        $this->setAddrZip($order->getBillingAddress()->getZip());
        $this->setAddrCity($order->getBillingAddress()->getCity());
        $this->setAddrCountryCode($order->getBillingAddress()->getCountryCodeIso3());

        $this->setSdFirstName($order->getShippingAddress()->getFirstName());
        $this->setSdLastName($order->getShippingAddress()->getLastName());
        $this->setSdStreet($order->getShippingAddress()->getStreet());
        $this->setSdStreetNr($order->getShippingAddress()->getStreetNr());
        //sdZip is set in parent constructor.
        $this->setSdCity($order->getShippingAddress()->getCity());
        $this->setSdCountryCode($order->getShippingAddress()->getCountryCodeIso3());

        $basicMethod = $config['crifmethod'];
        if ($basicMethod != 'inactive') {
            $isCompany = strlen($order->getBillingAddress()->getCompany()) > 0;
            if ($isCompany) {
                $this->setProductName($basicMethod . 'Business');
                // set CompanyName in LastName field and remove Firstname field
                $this->setLastName($order->getBillingAddress()->getCompany());
                $this->setFirstName(null);
            } else {
                $this->setProductName($basicMethod . 'Consumer');
            }
        }

        $this->setOrderDesc($orderDesc);
        $this->setCustomerID($order->getCustomerID());
        $this->setEtiId($userData);

    }

    /**
     * @ignore <description>
     * @param string $addrCity
     */
    public function setAddrCity($addrCity)
    {
        $this->addrCity = $addrCity;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrCity()
    {
        return $this->addrCity;
    }

    /**
     * @ignore <description>
     * @param string $addrStreet
     */
    public function setAddrStreet($addrStreet)
    {
        $this->addrStreet = $addrStreet;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrStreet()
    {
        return $this->addrStreet;
    }

    /**
     * @ignore <description>
     * @param string $addrStreetNr
     */
    public function setAddrStreetNr($addrStreetNr)
    {
        $this->addrStreetNr = $addrStreetNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrStreetNr()
    {
        return $this->addrStreetNr;
    }

    /**
     * @ignore <description>
     * @param string $addrZip
     */
    public function setAddrZip($addrZip)
    {
        $this->addrZip = $addrZip;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrZip()
    {
        return $this->addrZip;
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
    public function getCustomerID()
    {
        return $this->customerID;
    }

    /**
     * @ignore <description>
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @ignore <description>
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @ignore <description>
     * @param string $productName
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @ignore <description>
     * @param mixed $sdCity
     */
    public function setSdCity($sdCity)
    {
        $this->sdCity = $sdCity;
    }

    /**
     * @ignore <description>
     * @return mixed
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
     * @param mixed $sdFirstName
     */
    public function setSdFirstName($sdFirstName)
    {
        $this->sdFirstName = $sdFirstName;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getSdFirstName()
    {
        return $this->sdFirstName;
    }

    /**
     * @ignore <description>
     * @param mixed $sdGender
     */
    public function setSdGender($sdGender)
    {
        $this->sdGender = $sdGender;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getSdGender()
    {
        return $this->sdGender;
    }

    /**
     * @ignore <description>
     * @param mixed $sdLastName
     */
    public function setSdLastName($sdLastName)
    {
        $this->sdLastName = $sdLastName;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getSdLastName()
    {
        return $this->sdLastName;
    }

    /**
     * @ignore <description>
     * @param mixed $sdStreet
     */
    public function setSdStreet($sdStreet)
    {
        $this->sdStreet = $sdStreet;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getSdStreet()
    {
        return $this->sdStreet;
    }

    /**
     * @ignore <description>
     * @param mixed $sdStreetNr
     */
    public function setSdStreetNr($sdStreetNr)
    {
        $this->sdStreetNr = $sdStreetNr;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getSdStreetNr()
    {
        return $this->sdStreetNr;
    }

    /**
     * @ignore <description>
     * @param string $AddrCountryCode
     */
    public function setAddrCountryCode($AddrCountryCode)
    {
        $this->AddrCountryCode = $AddrCountryCode;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrCountryCode()
    {
        return $this->AddrCountryCode;
    }


    /**
     * returns paymentURL
     * @return mixed|string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/deltavista.aspx';
    }

    /**
     * returns null for RefundURL because no refunds are possible with CRIF
     * @return null|string
     */
    public function getCTRefundURL()
    {
        return null;
    }
}
