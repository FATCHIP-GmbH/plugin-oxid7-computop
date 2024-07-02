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
     * @ignore <description>
     * @param mixed $Amount
     */
    public function setAmount($Amount)
    {
        $this->amount = $Amount;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @ignore <description>
     * @param mixed $Currency
     */
    public function setCurrency($Currency)
    {
        $this->currency = $Currency;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @ignore <description>
     * @param mixed $PayId
     */
    public function setPayId($PayId)
    {
        $this->payId = $PayId;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getPayId()
    {
        return $this->payId;
    }

    /**
     * @ignore <description>
     * @param mixed $orderDescription
     */
    public function setOrderDesc($orderDescription)
    {
        $this->orderDesc = $orderDescription;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getOrderDesc()
    {
        return $this->orderDesc;
    }

    /**
     * @ignore <description>
     * @param \Fatchip\CTPayment\CTAddress\CTAddress $billingAddress
     */
    public function setBillingAddress($billingAddress) {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @ignore <description>
     * @return \Fatchip\CTPayment\CTAddress\CTAddress
     */
    public function getBillingAddress() {
        return $this->billingAddress;
    }

    /**
     * @ignore <description>
     * @param \Fatchip\CTPayment\CTAddress\CTAddress $shippingAddress
     */
    public function setShippingAddress($shippingAddress) {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @ignore <description>
     * @return \Fatchip\CTPayment\CTAddress\CTAddress
     */
    public function getShippingAddress() {
        return $this->shippingAddress;
    }

    /**
     * @ignore <description>
     * @param mixed $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @ignore <description>
     * @param mixed $customerID
     */
    public function setCustomerID($customerID) {
        $this->customerID = $customerID;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getCustomerID() {
        return $this->customerID;
    }

}
