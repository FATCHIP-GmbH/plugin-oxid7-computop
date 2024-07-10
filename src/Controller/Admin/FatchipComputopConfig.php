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

namespace Fatchip\ComputopPayments\Controller\Admin;

use Fatchip\CTPayment\CTPaymentConfigForms;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\CTPayment\CTAPITestService;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleSettingNotFountException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Controller for Computop configuration
 */
class FatchipComputopConfig extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->_sThisTemplate = '@fatchip_computop_payments/admin/fatchip_computop_payments_config';
    }

    /**
     * @return string
     */
    public function render()
    {
        $config = new Config();
        $configArr = $config->toArray(true);

        // Create separate form field arrays for each payment method
        $generalFormFields = array_merge(CTPaymentConfigForms::formGeneralTextElements, CTPaymentConfigForms::formGeneralSelectElements);
        $creditCardFormFields = array_merge(CTPaymentConfigForms::formCreditCardSelectElements, CTPaymentConfigForms::formCreditCardTextElements);
        $idealFormFields = CTPaymentConfigForms::formIdealSelectElements;
        $lastschriftFormFields = CTPaymentConfigForms::formLastschriftSelectElements;
        $payPalFormFields = CTPaymentConfigForms::formPayPalSelectElements;
        $amazonFormFields = array_merge(CTPaymentConfigForms::formAmazonTextElements, CTPaymentConfigForms::formAmazonSelectElements);
        $bonitaetFormFields = array_merge(CTPaymentConfigForms::formBonitaetElements, CTPaymentConfigForms::formBonitaetSelectElements);
        $klarnaFormFields = CTPaymentConfigForms::formKlarnaTextElements;

        $mergedFormFields = array_replace_recursive(
            $generalFormFields,
            $creditCardFormFields,
            $idealFormFields,
            $lastschriftFormFields,
            $payPalFormFields,
            $amazonFormFields,
            $bonitaetFormFields,
            $klarnaFormFields,
            $configArr
        );

        // Split the $merged array into two halves
        $middleIndex = ceil(count($mergedFormFields) / 2);
        $formFields = [
            array_slice($mergedFormFields, 0, $middleIndex),
            array_slice($mergedFormFields, $middleIndex)
        ];

        $this->addTplParam('mergedFormFields', $mergedFormFields);
        $this->addTplParam('formFields', $formFields);
        $this->addTplParam('generalFormFields', $generalFormFields);
        $this->addTplParam('creditCardFormFields', $creditCardFormFields);
        $this->addTplParam('idealFormFields', $idealFormFields);
        $this->addTplParam('lastschriftFormFields', $lastschriftFormFields);
        $this->addTplParam('payPalFormFields', $payPalFormFields);
        $this->addTplParam('amazonFormFields', $amazonFormFields);
        $this->addTplParam('bonitaetFormFields', $bonitaetFormFields);
        $this->addTplParam('klarnaFormFields', $klarnaFormFields);
        $this->addTplParam('config', $mergedFormFields);
        $thisTemplate = parent::render();

        try {
            $this->checkHealth($config);
        } catch (StandardException $e) {
            Registry::getUtilsView()->addErrorToDisplay(
                $e,
                false,
                true,
                'fatchip_computop_error'
            );
        }


        return $thisTemplate;
    }

    /**
     * Saves configuration values
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ModuleSettingNotFountException
     * @throws NotFoundExceptionInterface
     */
    public function save()
    {
        $confArr = (array)Registry::getRequest()->getRequestEscapedParameter('conf');
        $shopId = (string)Registry::getConfig()->getShopId();

        if (isset($_POST['idealButton'])) {
            $this->updateIdeal($confArr);
            return;
        }

        $this->saveConfig($confArr, $shopId);

        parent::save();
    }

    /**
     * Saves configuration values
     *
     * @param array $conf
     * @param string $shopId
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function saveConfig(array $conf, string $shopId)
    {
        $oModuleConfiguration = null;
        $oModuleConfigurationDaoBridge = null;
        $oModuleActivationBridge = null;
        if ($this->useDaoBridge()) {
            /** @var ModuleActivationBridgeInterface $oModuleActivationBridge */
            $oModuleActivationBridge = ContainerFactory::getInstance()->getContainer()->get(
                ModuleActivationBridgeInterface::class
            );
            $oModuleActivationBridge->deactivate(Constants::MODULE_ID, $shopId);

            /** @var ModuleConfigurationDaoBridgeInterface $oModuleConfigurationDaoBridge */
            $oModuleConfigurationDaoBridge = ContainerFactory::getInstance()->getContainer()->get(
                ModuleConfigurationDaoBridgeInterface::class
            );
            $oModuleConfiguration = $oModuleConfigurationDaoBridge->get(Constants::MODULE_ID);
        }

        foreach ($conf as $confName => $value) {
            $value = trim($value);
            if ($this->useDaoBridge()) {
                $oModuleSetting = $oModuleConfiguration->getModuleSetting($confName);
                $value = $oModuleSetting->getType() === 'bool' ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $value;
                $oModuleSetting->setValue($value);
            }
            if (!$this->useDaoBridge()) {
                Registry::getConfig()->saveShopConfVar(
                    strpos($confName, 'bl') ? 'bool' : 'str',
                    $confName,
                    $value,
                    $shopId,
                    'module:' . Constants::MODULE_ID
                );
            }
        }
        if ($this->useDaoBridge()) {
            $oModuleConfigurationDaoBridge->save($oModuleConfiguration);
            $oModuleActivationBridge->activate(Constants::MODULE_ID, $shopId);
        }
    }

    /**
     * check if using DaoBridge is possible
     *
     * @return boolean
     */
    protected function useDaoBridge(): bool
    {
        return class_exists(
            '\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory'
        );
    }

    /**
     * Checks if module configuration is valid
     * @throws StandardException
     */
    public function checkHealth($config)
    {
        $test = $this->apiTest($config->toArray());
        if (
            !$config->getMerchantID() ||
            !$config->getBlowfishPassword() ||
            !$config->getMac()
        ) {
            throw new StandardException('FATCHIP_COMPUTOP_ERR_CONF_INVALID');
        }
    }

    /**
     * assigns error and count of updated items to view
     *
     * @return void
     */
    public function apiTest($config)
    {
        $service = new CTAPITestService($config);
        try {
            $success = $service->doAPITest();
        }
        catch (Exception $e) {
                Registry::getUtilsView()->addErrorToDisplay(
                    $e,
                    true,
                    true,
                    'fatchip_computop_error'
                );
        }
    }

    /**
     * assigns error and count of updated items to view
     *
     * @return void
     */
    public function updateIdeal($config)
    {
        $service = new CTAPITestService($config);
        try {
            $success = $service->getIdealIssuers();
        }
        catch (Exception $e) {
            Registry::getUtilsView()->addErrorToDisplay(
                $e,
                true,
                true,
                'fatchip_computop_error'
            );
        }
    }
}
