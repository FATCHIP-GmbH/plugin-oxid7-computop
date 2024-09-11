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
 * @subpackage CTOrder
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment\CTOrder;

use Fatchip\CTPayment\CTAddress\CTAddress;

/**
 * Class CTOrder
 * @property mixed $transID
 * @package Fatchip\CTOrder
 */
class CTOrder
{
    /**
     * amount in cents
     * @var
     */
    protected $amount;
    /**
     * Currency (Iso-wert in 3 characters)
     * @var string
     */
    protected $currency;

    /**
     * Order description, will show up on customer statements
     * @var string
     */
    protected $orderDesc;

    /**
     * Vom Paygate vergebene ID für die Zahlung, z.B. zur Referenzierung von Stor-nos, Buchungen und Gutschriften
     * @var string
     */
    protected $payId;

    /**
     * @var
     */
    protected $transID;

    /**
     * Emailaddress
     * @var string
     */
    protected $email;

    /**
     * Kundennummer/Kundenreferenz
     * @var string
     */
    protected $customerID;

    /**
     * Billing address
     * @var CTAddress
     */
    protected $billingAddress;
    /**
     * Shipping address
     * @var CTAddress
     */
    protected $shippingAddress;

    /**
     * CTOrder constructor
     */
    public function __construct()
    {
    }


    /**
     * @param mixed $Amount
     * @ignore <description>
     */
    public function setAmount($Amount)
    {
        $this->amount = $Amount;
    }

    /**
     * @return mixed
     * @ignore <description>
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $Currency
     * @ignore <description>
     */
    public function setCurrency($Currency)
    {
        $this->currency = $Currency;
    }

    /**
     * @return mixed
     * @ignore <description>
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $PayId
     * @ignore <description>
     */
    public function setPayId($PayId)
    {
        $this->payId = $PayId;
    }

    /**
     * @param mixed $TransID
     * @ignore <description>
     */
    public function setTransID($TransID)
    {
        $this->transID = $TransID;
    }

    public function getTransID()
    {
        return $this->transID;
    }

    /**
     * @return mixed
     * @ignore <description>
     */
    public function getPayId()
    {
        return $this->payId;
    }

    /**
     * @param mixed $orderDescription
     * @ignore <description>
     */
    public function setOrderDesc($orderDescription)
    {
        $this->orderDesc = $orderDescription;
    }

    /**
     * @return mixed
     * @ignore <description>
     */
    public function getOrderDesc()
    {
        return $this->orderDesc;
    }

    /**
     * @param \Fatchip\CTPayment\CTAddress\CTAddress $billingAddress
     * @ignore <description>
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return \Fatchip\CTPayment\CTAddress\CTAddress
     * @ignore <description>
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param \Fatchip\CTPayment\CTAddress\CTAddress $shippingAddress
     * @ignore <description>
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return \Fatchip\CTPayment\CTAddress\CTAddress
     * @ignore <description>
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param mixed $email
     * @ignore <description>
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     * @ignore <description>
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $customerID
     * @ignore <description>
     */
    public function setCustomerID($customerID)
    {
        $this->customerID = $customerID;
    }

    /**
     * @return mixed
     * @ignore <description>
     */
    public function getCustomerID()
    {
        return $this->customerID;
    }

}
