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

use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethodIframe;

/**
 * Class PostFinance
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
class PostFinance extends CTPaymentMethodIframe
{
    const paymentClass = 'PostFinance';

    /**
     * Name des Kontoinhabers
     *
     * @var string
     */
    protected $accOwner;

    /**
     * PostFinance constructor
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
        $this->setAccOwner($order->getBillingAddress()->getFirstName . ' ' . $order->getBillingAddress()->getLastName());
    }

    /**
     * @ignore <description>
     * @param string $accOwner
     */
    public function setAccOwner($accOwner)
    {
        $this->accOwner = $accOwner;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAccOwner()
    {
        return $this->accOwner;
    }


    /**
     * returns paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/postfinance.aspx';
    }

    /**
     * returns null for CaptureURL because no capture can be made for PostFinance
     * @return null|string
     */
    public function getCTCaptureURL()
    {
        return null;
    }

}
