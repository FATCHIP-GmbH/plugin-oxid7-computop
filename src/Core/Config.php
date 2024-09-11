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


use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;

/**
 * Class Config
 */
class Config
{
    protected $merchantID = null;

    protected $mac = null;

    protected $blowfishPassword = null;

    protected $prefixOrdernumber = null;

    protected $suffixOrdernumber = null;

    protected $debuglog = null;

    protected $encryption = null;

    protected $creditCardMode = null;

    protected $creditCardTestMode = null;

    protected $creditCardSilentModeBrandsVisa = null;

    protected $creditCardSilentModeBrandsMaster = null;

    protected $creditCardSilentModeBrandsAmex = null;

    protected $creditCardCaption = null;

    protected $creditCardAcquirer = null;


    protected $creditCardTemplate = null;

    protected $idealDirektOderUeberSofort = null;

    protected $lastschriftDienst = null;

    protected $lastschriftCaption = null;

    protected $lastschriftAnon = null;

    protected $paypalCaption = null;

    protected $paypalExpressCaption = null;

    protected $paypalExpressClientID = null;

    protected $paypalExpressFunding = null;

    protected $paypalExpressFundingExcluded = null;

    protected $paypalExpressMerchantID = null;

    protected $amazonpayPrivKey = null;

    protected $amazonpayPubKeyId = null;

    protected $amazonpayMerchantId = null;

    protected $amazonpayStoreId = null;

    protected $amazonLiveMode = null;

    protected $amazonCaptureType = null;

    protected $amazonButtonType = null;

    protected $amazonButtonColor = null;

    protected $amazonButtonSize = null;

    protected $klarnaaccount = null;

    /**
     * @return null
     */
    public function getDebuglog()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('debuglog', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $debuglog
     */
    public function setDebuglog($debuglog): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('debuglog', $debuglog, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getEncryption()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('encryption', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $encryption
     */
    public function setEncryption($encryption): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('encryption', $encryption, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getCreditCardMode()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('creditCardMode', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardMode
     */
    public function setCreditCardMode($creditCardMode): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardMode', $creditCardMode, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getCreditCardTestMode()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('creditCardTestMode', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardTestMode
     */
    public function setCreditCardTestMode($creditCardTestMode): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardTestMode', $creditCardTestMode, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getCreditCardSilentModeBrandsVisa()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('creditCardSilentModeBrandsVisa', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardSilentModeBrandsVisa
     */
    public function setCreditCardSilentModeBrandsVisa($creditCardSilentModeBrandsVisa): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardSilentModeBrandsVisa', $creditCardSilentModeBrandsVisa, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getCreditCardSilentModeBrandsMaster()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('creditCardSilentModeBrandsMaster', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardSilentModeBrandsMaster
     */
    public function setCreditCardSilentModeBrandsMaster($creditCardSilentModeBrandsMaster): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardSilentModeBrandsMaster', $creditCardSilentModeBrandsMaster, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getCreditCardSilentModeBrandsAmex()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('creditCardSilentModeBrandsAmex', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardSilentModeBrandsAmex
     */
    public function setCreditCardSilentModeBrandsAmex($creditCardSilentModeBrandsAmex): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardSilentModeBrandsAmex', $creditCardSilentModeBrandsAmex, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getCreditCardCaption()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('creditCardCaption', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardCaption
     */
    public function setCreditCardCaption($creditCardCaption): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardCaption', $creditCardCaption, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getCreditCardAcquirer()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('merchantID', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardAcquirer
     */
    public function setCreditCardAcquirer($creditCardAcquirer): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardAcquirer', $creditCardAcquirer, FatchipComputopModule::MODULE_ID);
    }


    /**
     * @return null
     */
    public function getCreditCardTemplate()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('creditCardTemplate', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $creditCardTemplate
     */
    public function setCreditCardTemplate($creditCardTemplate): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('creditCardTemplate', $creditCardTemplate, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getIdealDirektOderUeberSofort()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('idealDirektOderUeberSofort', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $idealDirektOderUeberSofort
     */
    public function setIdealDirektOderUeberSofort($idealDirektOderUeberSofort): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('idealDirektOderUeberSofort', $idealDirektOderUeberSofort, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getLastschriftDienst()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('lastschriftDienst', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $lastschriftDienst
     */
    public function setLastschriftDienst($lastschriftDienst): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('lastschriftDienst', $lastschriftDienst, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getLastschriftCaption()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('lastschriftCaption', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $lastschriftCaption
     */
    public function setLastschriftCaption($lastschriftCaption): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('lastschriftCaption', $lastschriftCaption, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getLastschriftAnon()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('lastschriftAnon', FatchipComputopModule::MODULE_ID);
        return $value;
    }


    /**
     * @return null
     */
    public function getPaypalCaption()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('paypalCaption', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $paypalCaption
     */
    public function setPaypalCaption($paypalCaption): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('paypalCaption', $paypalCaption, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getPaypalExpressCaption()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('paypalExpressCaption', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $paypalExpressCaption
     */
    public function setPaypalExpressCaption($paypalExpressCaption): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('paypalExpressCaption', $paypalExpressCaption, FatchipComputopModule::MODULE_ID);
    }

    public function getPaypalExpressClientID()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('paypalExpressClientID', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $paypalExpressClientID
     */
    public function setPaypalExpressClientID($paypalExpressClientID): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('paypalExpressClientID', $paypalExpressClientID, FatchipComputopModule::MODULE_ID);
    }

    public function getPaypalExpressMerchantID()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('paypalExpressMerchantID', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $paypalExpressMerchantID
     */
    public function setPaypalExpressMerchantID($paypalExpressMerchantID): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('paypalExpressMerchantID', $paypalExpressMerchantID, FatchipComputopModule::MODULE_ID);
    }

    public function getPaypalExpressFunding()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('paypalExpressFunding', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $paypalExpressFunding
     */
    public function setPaypalExpressFunding($paypalExpressFunding): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('paypalExpressFunding', $paypalExpressFunding, FatchipComputopModule::MODULE_ID);
    }

    public function getPaypalExpressFundingExcluded()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('paypalExpressFundingExcluded', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $paypalExpressFundingExcluded
     */
    public function setPaypalExpressFundingExcluded($paypalExpressFundingExcluded): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('paypalExpressFundingExcluded', $paypalExpressFundingExcluded, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getAmazonLiveMode()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonLiveMode', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $amazonLiveMode
     */
    public function setAmazonLiveMode($amazonLiveMode): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonLiveMode', $amazonLiveMode, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getAmazonCaptureType()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonCaptureType', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $amazonCaptureType
     */
    public function setAmazonCaptureType($amazonCaptureType): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonCaptureType', $amazonCaptureType, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getAmazonButtonType()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonButtonType', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $amazonButtonType
     */
    public function setAmazonButtonType($amazonButtonType): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonButtonType', $amazonButtonType, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getAmazonButtonColor()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonButtonColor', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $amazonButtonColor
     */
    public function setAmazonButtonColor($amazonButtonColor): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonButtonColor', $amazonButtonColor, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getAmazonButtonSize()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonButtonSize', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $amazonButtonSize
     */
    public function setAmazonButtonSize($amazonButtonSize): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonButtonSize', $amazonButtonSize, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getKlarnaaccount()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('klarnaaccount', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    public function setKlarnaaccount($klarnaaccount): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('klarnaaccount', $klarnaaccount, FatchipComputopModule::MODULE_ID);
    }


    /**
     * @return null
     */
    public function getMac()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('mac', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $mac
     */
    public function setMac($mac): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('mac', $mac, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getBlowfishPassword()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('blowfishPassword', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $blowfishPassword
     */
    public function setBlowfishPassword($blowfishPassword): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('blowfishPassword', $blowfishPassword, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getPrefixOrdernumber()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('prefixOrdernumber', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $prefixOrdernumber
     */
    public function setPrefixOrdernumber($prefixOrdernumber): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('prefixOrdernumber', $prefixOrdernumber, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @return null
     */
    public function getSuffixOrdernumber()
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('suffixOrdernumber', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    /**
     * @param null $suffixOrdernumber
     */
    public function setSuffixOrdernumber($suffixOrdernumber): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('suffixOrdernumber', $suffixOrdernumber, FatchipComputopModule::MODULE_ID);
    }

    public function getMerchantID(): string
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('merchantID', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    public function setMerchantID($value): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('merchantID', $value, FatchipComputopModule::MODULE_ID);
    }

    public function getAmazonpayMerchantId(): string
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonpayMerchantId', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    public function setAmazonpayMerchantId($value): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonpayMerchantId', $value, FatchipComputopModule::MODULE_ID);
    }

    public function getAmazonpayPrivKey(): string
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonpayPrivKey', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    public function setAmazonpayPrivKey($value): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonpayPrivKey', $value, FatchipComputopModule::MODULE_ID);
    }

    public function getAmazonpayPubKeyId(): string
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonpayPubKeyId', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    public function setAmazonpayPubKeyId($value): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonpayPubKeyId', $value, FatchipComputopModule::MODULE_ID);
    }

    public function getAmazonpayStoreId(): string
    {
        $moduleSettingBridge
            = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $value = $moduleSettingBridge->get('amazonpayStoreId', FatchipComputopModule::MODULE_ID);
        return $value;
    }

    public function setAmazonpayStoreId($value): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('amazonpayStoreId', $value, FatchipComputopModule::MODULE_ID);
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
