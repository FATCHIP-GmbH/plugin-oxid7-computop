<?php

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
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 8.1, 8.2
 *
 * @category   Payment
 * @package    fatchip-gmbh/computop_payments
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop UpdateIdealIssuers
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Core;

/**
 * Module COnstants
 */
class Constants
{
    const MODULE_ID = 'fatchip_computop_payments';
    const CONTROLLER_PREFIX = 'FatchipComputop';
    const GENERAL_PREFIX = 'fatchip_computop_';
    const TEMPLATE_PREFIX = 'fatchip_computop_';
    const APILOG_TABLE = self::GENERAL_PREFIX . 'api_log';

    const directPayments = [
        'fatchip_computop_lastschrift',

    ];

    const redirectPayments = [
        'fatchip_computop_klarna',
        'fatchip_computop_paypal_standard',
        'fatchip_computop_lastschrift',
        'fatchip_computop_easycredit',
        'fatchip_computop_creditcard',
        'fatchip_computop_twint',
        'fatchip_computop_ideal'

    ];

    const amazonpayPaymentId = 'fatchip_computop_amazonpay';

    const PAYMENTSTATUSPARTIALLYPAID = 'FATCHIP_COMPUTOP_PAYMENTSTATUS_PARTIALLY_PAID';
    const PAYMENTSTATUSPAID = 'FATCHIP_COMPUTOP_PAYMENTSTATUS_PAID';
    const PAYMENTSTATUSOPEN = 'FATCHIP_COMPUTOP_PAYMENTSTATUS_OPEN';
    const PAYMENTSTATUSRESERVED = 'FATCHIP_COMPUTOP_PAYMENTSTATUS_RESERVED';
    const PAYMENTSTATUSREVIEWNECESSARY = 'FATCHIP_COMPUTOP_PAYMENTSTATUS_REVIEW_NECESSARY';
    const PAYMENTSTATUSREFUNDED = 'FATCHIP_COMPUTOP_PAYMENTSTATUS_REFUNDED';


    public static function isFatchipComputopPayment(string $paymentId): bool
    {
        return strpos($paymentId, self::GENERAL_PREFIX) !== false;
    }

    public static function isFatchipComputopDirectPayment(string $paymentId): bool
    {
        return in_array($paymentId, self::directPayments);
    }

    public static function isFatchipComputopRedirectPayment(string $paymentId): bool
    {
        return in_array($paymentId, self::redirectPayments);
    }

    public static function getPaymentClassfromId(string $paymentId): string
    {
        switch ($paymentId) {
            case "fatchip_computop_lastschrift":
                return 'LastschriftDirekt';

            case "fatchip_computop_paypal":
                return 'Paypal';

            case "fatchip_computop_paypal_standard":
                return 'PaypalStandard';

            case "fatchip_computop_amazonpay":
                return 'Amazonpay';

            case "fatchip_computop_creditcard":
                return 'CreditCard';

            case "fatchip_computop_easycredit":
                return 'EasyCredit';

            case "fatchip_computop_paypal_express":
                return 'PayPalExpress';

            case "fatchip_computop_ideal":
                return 'Ideal';

            case "fatchip_computop_twint":
                return 'Twint';

            case "fatchip_computop_klarna":
                return 'KlarnaPayments';
        }
    }
}
