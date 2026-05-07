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
 * PHP version 5.6, 7.0, 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @subpackage CTPaymentMethods
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment\CTPaymentMethods;

use Fatchip\ComputopPayments\Core\Blowfish;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Helper\Api;
use Fatchip\ComputopPayments\Helper\Encryption;
use Fatchip\CTPayment\CTPaymentMethod;
use Fatchip\CTPayment\CTAddress\CTAddress;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class PaypalExpress
 * @package Fatchip\CTPayment\CTPaymentMethods
 */
class PayPalExpress extends CTPaymentMethod
{
    const paymentClass = 'PayPalExpress';

    public function isActive(): bool
    {
        $oBasket = Registry::getSession()->getBasket();
        $oPayment = oxNew(Payment::class);

        try {
            if (($oPayment->load('fatchip_computop_paypal_express') == false) || ($oPayment->oxpayments__oxactive && $oPayment->oxpayments__oxactive->value === 0)) {
                return false;
            }

            $bIsPaymentValid = $oPayment->isValidPayment(
                null,
                \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId(),
                $oBasket->getUser(),
                $oBasket->getPriceForPayment(),
                $oBasket->getShippingId()
            );

            return $bIsPaymentValid;

        } catch (\Exception $exception) {
            Registry::getLogger()->error('PaypalExpress: isActive: ' . $exception->getMessage());
        }

        return false;
    }
}
