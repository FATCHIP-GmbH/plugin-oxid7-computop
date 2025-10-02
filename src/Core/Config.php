<?php

namespace Fatchip\ComputopPayments\Core;

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
 * @copyright  2024 Computop UpdateIdealIssuers
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */


use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;

/**
 * Class Config
 */
class Config
{
    public function getConfigParam($param)
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        return $moduleSettingBridge->get($param, FatchipComputopModule::MODULE_ID);
    }

    public function setConfigParam($param, $value)
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save($param, $value, FatchipComputopModule::MODULE_ID);
    }

    public function toArray($mergable = false)
    {
        $return = [];
        foreach ($this as $key => $value) {
            $getter = 'get' . ucwords($key);
            if ($mergable) {
                $return[$key]['current_value'] = $this->$getter();
            } else {
                $return[$key] = $this->$getter();
            }
        }
        return $return;
    }

    public static function getRemoteAddress()
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'];
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $proxy = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (!empty($proxy)) {
                $proxyIps = explode(',', $proxy);
                $relevantIp = array_shift($proxyIps);
                $relevantIp = trim($relevantIp);
                if (!empty($relevantIp)) {
                    return $relevantIp;
                }
            }
        }
        // Cloudflare sends a special Proxy Header, see:
        // https://support.cloudflare.com/hc/en-us/articles/200170986-How-does-Cloudflare-handle-HTTP-Request-headers-
        // In theory, CF should respect X-Forwarded-For, but in some instances this failed
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        return $remoteAddr;
    }
}
