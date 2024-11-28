<?php /** @noinspection PhpUnused */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
use Fatchip\CTPayment\CTPaymentMethodIframe;

/**
 * Class Sofort
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class Sofort extends CTPaymentMethodIframe
{
    const paymentClass = 'Sofort';
    /**
     * „Ident“ für Sofort Ident oder „ideal“ für Sofort iDEAL
     * Ident müssen wir nicht implementieren, macht nur identification
     *
     * @var string
     */
    protected $sofortAction = '';

    /**
     * ID der Bank, über die iDEAL-Überweisungen erfolgen sollen;
     * Pflicht bei So-fortaction=ideal
     * Folgende IssuerIDs sind verfügbar:
     * RABONL2U - Rabobank
     * INGBNL2A - ING
     * ABNANL2A - ABN Amro
     * ASNBNL21 - ASN Bank
     * SNSBNL2A - SNS Bank
     * RBRBNL21 - Regiobank
     * TRIONL2U - Triodos Bank
     * FVLBNL22 - Van Lanschot Bankiers
     * KNABNL2H - Knab
     * BUNQNL2A - bunq
     *
     * @var string
     */
    protected $issuerID; //Bic der ausgewählten Bank

    /**
     * Ländercode zweistellig gemäß ISO 3166.
     * Derzeit DE, AT, BE, NL, ES, CH, PL, IT zulässig.
     *
     * @var string
     */
    protected $addrCountryCode;

    /**
     * Sofort constructor
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
    ) {
        parent::__construct($config, $order, $orderDesc, $userData);
        $this->setUrlSuccess($urlSuccess);
        $this->setUrlFailure($urlFailure);
        $this->setUrlNotify($urlNotify);
        $this->setAddrCountryCode($order->getBillingAddress()->getCountryCode());
    }


    /**
     * @ignore <description>
     * @param string $sofortAction
     */
    public function setSofortAction($sofortAction)
    {
        $this->sofortAction = $sofortAction;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSofortAction()
    {
        return $this->sofortAction;
    }


    /**
     * @ignore <description>
     * @param string $issuerID
     */
    public function setIssuerID($issuerID)
    {
        $this->issuerID = $issuerID;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getIssuerID()
    {
        return $this->issuerID;
    }

    /**
     * @ignore <description>
     * @param string $addrCountryCode
     */
    public function setAddrCountryCode($addrCountryCode)
    {
        $this->addrCountryCode = $addrCountryCode;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrCountryCode()
    {
        return $this->addrCountryCode;
    }

    /**
     * returns paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/sofort.aspx';
    }

    /**
     * returns CaptureURL als null because no captures are possible for Sofort
     * @return null|string
     */
    public function getCTCaptureURL()
    {
        return null;
    }

}
