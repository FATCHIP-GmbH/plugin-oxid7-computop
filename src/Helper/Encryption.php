<?php

namespace Fatchip\ComputopPayments\Helper;

use Fatchip\ComputopPayments\Model\Api\Encryption\AES;
use Fatchip\ComputopPayments\Model\Api\Encryption\Blowfish;
use OxidEsales\Eshop\Core\Registry;


class Encryption
{
    /**
     * @var Encryption
     */
    protected static $instance = null;

    /**
     * Create singleton instance of this encryption helper
     *
     * @return Encryption
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = oxNew(self::class);
        }
        return self::$instance;
    }

    /**
     * @return AES|Blowfish
     * @throws \Exception
     */
    public function getEncryptionObject()
    {
        if (Config::getInstance()->getConfigParam('encryption') === 'aes') {
            return new AES(Config::getInstance()->getConfigParam('blowfishPassword'));
        }

        if (Config::getInstance()->getConfigParam('encryption') === 'blowfish') {
            return new Blowfish(Config::getInstance()->getConfigParam('blowfishPassword'));
        }

        throw new \Exception('Invalid encryption method');
    }

    public function encrypt($plaintext, $len)
    {
        return $this->getEncryptionObject()->ctEncrypt($plaintext, $len);
    }

    public function decrypt($data, $len)
    {
        return $this->getEncryptionObject()->ctDecrypt($data, $len);
    }
}
