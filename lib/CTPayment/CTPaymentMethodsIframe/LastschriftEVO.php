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

use Fatchip\CTPayment\CTOrder\CTOrder;

/**
 * Class LastschriftEVO
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class LastschriftEVO extends Lastschrift
{
    const paymentClass = 'LastschriftEVO';
    /**
     * 2. Zeile der Warenbeschreibung, die auf dem Kontoauszug erscheint (27 Zei-chen).
     *
     * @var string
     */
    public $orderDesc2;

     /**
     * für SEPA: Anzahl Banktage>0, die für das Ausführungsdatum einer Lastschrift zum aktuellen Datum addiert werden
     *
     * @var int
     */
    public $DebitDelay;

    /**
     * LastschriftEVO constructor
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
        $this->setDebitDelay($config['lastschriftEvoDebitDelay']);
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
     * @param $debitDelay
     */
    public function setDebitDelay($debitDelay)
    {
        $this->DebitDelay = $debitDelay;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getOrderDesc2()
    {
        return $this->orderDesc2;
    }


}
