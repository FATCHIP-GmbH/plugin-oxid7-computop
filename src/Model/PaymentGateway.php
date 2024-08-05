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
     * Overrides standard oxid finalizeOrder method if the used payment method belongs to Computop.
     * Return parent's return if payment method is no Computop method
     *
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param object &$oOrder User ordering object
     *
     * @extend executePayment
     * @return bool
     */
    public function executePayment($dAmount, &$oOrder)
    {
        if (!$oOrder->isFatchipComputopOrder()) {
            return null;
        }
        $config = new Config();
        $configArray =  $config->toArray();
        $this->_iLastErrorNo = null;
        $this->_sLastError = null;
        $silentCCResponse = Registry::getSession()->getVariable('FatchipComputopRedirectResponse');
        $silentCCRequest = Registry::getSession()->getVariable('FatchipComputopDirectRequest');
        if ($configArray['creditCardMode'] === 'SILENT' && $silentCCRequest) {
            return true;
        }
/*        if ($silentCCRequest === null && $configArray['creditCardMode'] === 'SILENT') {
            return false;
        }*/
        /** @var Order $oOrder */
        if ($oOrder->isFatchipComputopRedirectPayment()) {
            return  $oOrder->handleRedirectPayment($dAmount, $this);
        }

        if ($oOrder->isFatchipComputopDirectPayment()) {
            return $oOrder->handleAuthorization($dAmount, $this);
        }
    }


    /**
     * Setter for last error number
     *
     * @param int $iLastErrorNr
     */
    public function setLastErrorNr($iLastErrorNr): void
    {
        $this->_iLastErrorNo = $iLastErrorNr;
    }

    /**
     * Setter for last error text
     *
     * @param int $sLastError
     */
    public function setLastError($sLastError): void
    {
        $this->_sLastError = $sLastError;
    }
}
