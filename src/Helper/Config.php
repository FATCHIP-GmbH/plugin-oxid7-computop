<?php

namespace Fatchip\ComputopPayments\Helper;

use Fatchip\ComputopPayments\Core\FatchipComputopModule;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;

class Config
{
    protected $connectionConfigFields = [
        'merchantID',
        'blowfishPassword',
        'mac',
        'encryption',
    ];

    /**
     * @var Payment
     */
    protected static $instance = null;

    /**
     * Create singleton instance of this payment helper
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = oxNew(self::class);
        }
        return self::$instance;
    }

    /**
     * @return mixed
     */
    protected function getSettingBridge()
    {
        return ContainerFactory::getInstance()->getContainer()->get(ModuleSettingBridgeInterface::class);
    }

    /**
     * @param $param
     * @return mixed
     */
    public function getConfigParam($param)
    {
        return $this->getSettingBridge()->get($param, FatchipComputopModule::MODULE_ID);
    }

    /**
     * @param string $param
     * @param string $value
     * @return void
     */
    public function setConfigParam($param, $value)
    {
        $this->getSettingBridge()->save($param, $value, FatchipComputopModule::MODULE_ID);
    }

    public function getConnectionConfig()
    {
        $config = [];
        foreach ($this->connectionConfigFields as $fieldName) {
            $config[$fieldName] = $this->getConfigParam($fieldName);
        }
        return $config;
    }
}
