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

/**
 * Class LastschriftDirekt
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class Giropay extends CTPaymentMethodIframe
{
    const paymentClass = 'Giropay';
    /**
     * 2. Zeile der Warenbeschreibung, die auf dem Kontoauszug erscheint (27 Zei-chen).
     *
     * @var string
     */
    protected $orderDesc2;

    /**
     * Bezeichnung Bank
     * @var string
     */
    protected $AccBank;

    /**
     * Kontoinhaber
     * @var string
     */
    protected $AccOwner;

    /**
     * International Bank Account Number
     *
     * @var string
     */
    protected $IBAN;


    /**
     * LastschriftDirekt constructor
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
        $orderDesc2
    ) {
        parent::__construct($config, $order, $urlSuccess, $urlFailure, $urlNotify, $orderDesc, $userData, $capture);
        $this->setOrderDesc2($orderDesc2);
    }

    /**
     * @ignore <description>
     * @param string $orderDesc2
     */
    public function setOrderDesc2($orderDesc2)
    {
        $this->orderDesc2 = $orderDesc2;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getOrderDesc2()
    {
        return $this->orderDesc2;
    }

    /**
     * returns the paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/giropay.aspx';
    }

    /**
     * returns the paymentURL
     * @return string
     */
    public function getHTTPGetURL()
    {
        return 'https://www.computop-paygate.com/giropay.aspx';
    }

    /**
     * @ignore <description>
     * @param string $AccBank
     */
    public function setAccBank($AccBank) {
        $this->AccBank = $AccBank;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAccBank() {
        return $this->AccBank;
    }

    /**
     * @ignore <description>
     * @param string $AccOwner
     */
    public function setAccOwner($AccOwner) {
        $this->AccOwner = $AccOwner;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAccOwner() {
        return $this->AccOwner;
    }

    /**
     * @ignore <description>
     * @param string $IBAN
     */
    public function setIBAN($IBAN) {
        $this->IBAN = $IBAN;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getIBAN() {
        return $this->IBAN;
    }
}
