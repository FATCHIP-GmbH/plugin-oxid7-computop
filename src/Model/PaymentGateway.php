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
 * along with Computop Oxid Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 8.1, 8.2
 *
 * @category   Payment
 * @package    fatchip-gmbh/computop_payments
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Model;

use Fatchip\ComputopPayments\Core\Config;
use OxidEsales\Eshop\Core\Registry;

class PaymentGateway extends PaymentGateway_parent
{
    /**
     * @var null|int
     */
    public $_iLastErrorNo;

    /**
     * @var null|int
     */
    public $_sLastError;

    /**
     * @var string[]
     */
    protected $aSpecialHandlingMethods = [
        'fatchip_computop_paypal_express',
        'fatchip_computop_lastschrift',
        'fatchip_computop_amazonpay',
    ];

    // -----------------> START OXID CORE MODULE EXTENSIONS <-----------------

    /**
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param object $oOrder  User ordering object
     *
     * @return bool
     */
    public function executePayment($dAmount, &$oOrder)
    {
        if ($oOrder->isFatchipComputopOrder() === false) {
            return parent::executePayment($dAmount, $oOrder);
        }

        if (in_array($oOrder->oxorder__oxpaymenttype->value, $this->aSpecialHandlingMethods) || $this->fcctIsSilentCCRequest()) {
            return true;
        }

        $this->_iLastErrorNo = null;
        $this->_sLastError = null;

        /** @var Order $oOrder */
        if ($oOrder->isFatchipComputopRedirectPayment()) {
            return  $oOrder->handleRedirectPayment($dAmount, $this);
        }

        if ($oOrder->isFatchipComputopDirectPayment() || $oOrder->oxorder__oxpaymenttype->value === 'fatchip_computop_easycredit') {
            return $oOrder->handleAuthorization($dAmount, $this);
        }
        return false;
    }

    // -----------------> END OXID CORE MODULE EXTENSIONS <-----------------

    // -----------------> START CUSTOM MODULE FUNCTIONS <-----------------

    /**
     * @return bool
     */
    protected function fcctIsSilentCCRequest()
    {
        $silentCCRequest = Registry::getSession()->getVariable('FatchipComputopDirectRequest');
        $config = new Config();
        $configArray =  $config->toArray();
        if ($configArray['creditCardMode'] === 'SILENT' && $silentCCRequest) {
            return true;
        }
        return false;
    }
}
