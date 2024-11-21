<?php /** @noinspection PhpUnused */

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
 * PHP version 5.6, 7.0 , 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @subpackage CTPayment
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment;
/**
 * Class Blowfish
 * @package Fatchip\CTPayment
 */
class Encryption
{

    const blowfishCipher = 'bf-ecb';

    const aes128Cipher = 'aes-128-cbc';

    const aes192Cipher = 'aes-192-cbc';

    const aes256Cipher = 'aes-256-cbc';

    /**
     * HändlerID, die von First Cash Solution vergeben wird. Dieser Parameter ist unverschlüsselt zu übergeben.
     *
     * @var string
     */
    protected $merchantID = '';
    /**
     * Blowfish password
     */
    protected $blowfishPassword = '';
    /**
     * HMAC Password
     *
     * @var string
     */
    protected $mac = '';

    /**
     * Encryption
     * blowfish | aes
     *
     * @var string
     */
    protected $encryption;

    /**
     * expand
     * @param $text
     * @return string
     */
    protected function expand8($text)
    {
        while (strlen($text) % 8 != 0) {
            $text .= chr(0);
        }
        return $text;
    }

    /**
     * expand
     * @param $text
     * @return string
     */
    protected function expand16($text)
    {
        while (strlen($text) % 16 != 0) {
            $text .= chr(0);
        }
        return $text;
    }

    /**
     * decrypt
     * @param $text
     * @return string
     */
    protected function openssl_decrypt($text, $iv = false)
    {
        /* @see https://stackoverflow.com/questions/54180458/why-are-mcrypt-and-openssl-encrypt-not-giving-the-same-results-for-blowfish-with/54190706#54190706
         * make sure decrypt works with all supported PHP Versions
         */

        if ($this->encryption !== 'blowfish') {
            $pwLength = strlen($this->blowfishPassword);
            if ($pwLength <= 16) {
                $keyLength = 16; // aes-128-cbc
                $cipher = self::aes128Cipher;
            } else if ($pwLength <= 24) {
                $keyLength = 24; // aes 192-cbc
                $cipher = self::aes192Cipher;
            } else {
                $keyLength = 32; // aes 256-cbc
                $cipher = self::aes256Cipher;
            }
            $ivRaw = hex2bin($iv);
            $plain = openssl_decrypt($text, $cipher, $this->blowfishPassword, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING | OPENSSL_DONT_ZERO_PAD_KEY, $ivRaw);
        } else {
            $plain = openssl_decrypt($text, self::blowfishCipher, $this->blowfishPassword, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING | OPENSSL_DONT_ZERO_PAD_KEY);
        }

        return $plain;
    }

    /**
     * ctSplit
     * @param $arText
     * @param $sSplit
     * @return array
     */
    protected function ctSplit($arText, $sSplit)
    {
        $arr = [];
        foreach ($arText as $text) {
            $str = explode($sSplit, $text);
            $arr[$str[0]] = $str[1];
        }
        return $arr;
    }

    /**
     * encrypt
     * @param $text
     * @return string
     */
    protected function openssl_encrypt($text)
    {
        $cipher = self::blowfishCipher;
        if ($this->encryption !== 'blowfish') {
            $pwLength = strlen($this->blowfishPassword);
            if ($pwLength <= 16) {
                $keyLength = 16; // aes-128-cbc
                $cipher = self::aes128Cipher;
            } else if ($pwLength <= 24) {
                $keyLength = 24; // aes 192-cbc
                $cipher = self::aes192Cipher;
            } else {
                $keyLength = 32; // aes 256-cbc
                $cipher = self::aes256Cipher;
            }

            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = random_bytes($ivlen);
            $aes = openssl_encrypt($text, $cipher, $this->getBlowfishPassword(), OPENSSL_RAW_DATA, $iv);
            return bin2Hex($iv) . '-' . bin2hex($aes);
        } else {
            return bin2hex(openssl_encrypt($text, self::blowfishCipher, $this->getBlowfishPassword(), OPENSSL_RAW_DATA));
        }
    }

    /**
     * Encrypt the passed text (any encoding) with Blowfish.
     *
     * @param string $plaintext
     * @param integer $len
     * @param string $password
     * @param string $encryption
     * @return bool|string
     */
    public function ctEncrypt($plaintext, $len, $password, $encryption)
    {
        if (mb_strlen($password) <= 0) {
            $password = ' ';
        }
        if (mb_strlen($plaintext) != $len) {
            return false;
        }

        if ($encryption === 'blowfish') {
            $plaintext = $this->expand8($plaintext);
        } else {
            $plaintext = $this->expand16($plaintext);
        }

        return $this->openssl_encrypt($plaintext);
    }

    /**
     * Decrypt the passed HEX string with Blowfish.
     *
     * @param string $cipher
     * @param integer $len
     * @param string $password
     * @return bool|string
     */
    public function ctDecrypt($cipher, $len, $password)
    {
        $iv = false;
        if (mb_strlen($password) <= 0) {
            $password = ' ';
        }
        // remove IV from Data when AES Encryption is used
        if (strpos($cipher, '-') !== false) {
            $cipherParts = explode('-', $cipher );
            $cipher = $cipherParts[1];
            $iv = $cipherParts[0];
        }
        $cipherRaw = hex2bin($cipher);
        if ($len > strlen($cipherRaw)) {
            return false;
        }

        return mb_substr($this->openssl_decrypt($cipherRaw, $iv), 0, $len);
    }

    /**
     * @param string $merchantId
     * @ignore <description>
     */
    public function setMerchantID($merchantId)
    {
        $this->merchantID = $merchantId;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getMerchantID()
    {
        return $this->merchantID;
    }

    /**
     * @param mixed $blowfishPassword
     * @ignore <description>
     */
    public function setBlowfishPassword($blowfishPassword)
    {
        $this->blowfishPassword = $blowfishPassword;
    }

    /**
     * @return mixed
     * @ignore <description>
     */
    public function getBlowfishPassword()
    {
        return $this->blowfishPassword;
    }

    /**
     * @param string $mac
     * @ignore <description>
     */
    public function setMac($mac)
    {
        $this->mac = $mac;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getMac()
    {
        return $this->mac;
    }

    /**
     * @param string $encryption
     * @ignore <description>
     */
    public function setEncryption($encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getEncryption()
    {
        return $this->encryption;
    }
}
