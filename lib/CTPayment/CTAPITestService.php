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
 * PHP version 5.6, 7.0 , 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @subpackage Bootstrap
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment;

use Fatchip\ComputopPayments\Helper\Api;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\StandardException AS Exception;
use Fatchip\CTPayment\CTPaymentMethodsIframe\CreditCard;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class CTAPITestService.
 *
 *  gets supported ideal financial institutes from the computop api
 *  as a fallback an array is returned
 */
class CTAPITestService extends Encryption
{


    /**
     * CTAPITestService constructor.
     *
     * @param $config array plugin configuration
     */
    public function __construct($config)
    {
        $this->merchantID = $config['merchantID'];
        $this->blowfishPassword = $config['blowfishPassword'];
        $this->mac = $config['mac'];
        $this->encryption = $config['encryption'];
    }

    /**
     * creates uri which will be used to download the issuers
     *
     * data fields are read from class props and encrypted
     *
     * @return string url
     * @see ctEncrypt()
     *
     * @see ctHMAC()
     */
    public function getURL()
    {
        mt_srand((double)microtime() * 1000000);
        $reqId = (string)mt_rand();
        $reqId .= date('yzGis');

        $testParams = [
            'MerchantID' => $this->getMerchantID(),
            'capture' => 'MANUAL',
            'msgVer' => '2.0',
            'billingAddress' => 'eyJjaXR5IjoiQmVybGluIiwiY291bnRyeSI6eyJjb3VudHJ5QTMiOiJERVUifSwiYWRkcmVzc0xpbmUxIjp7InN0cmVldCI6IkhhdXB0c3RyLiIsInN0cmVldE51bWJlciI6IjMifSwicG9zdGFsQ29kZSI6IjEwNzc5In0=',
            'shippingAddress' => 'eyJjaXR5IjoiQmVybGluIiwiY291bnRyeSI6eyJjb3VudHJ5QTMiOiJERVUifSwiYWRkcmVzc0xpbmUxIjp7InN0cmVldCI6IkhhdXB0c3RyLiIsInN0cmVldE51bWJlciI6IjMifSwicG9zdGFsQ29kZSI6IjEwNzc5In0=',
            'credentialOnFile' => 'eyJ0eXBlIjp7InVuc2NoZWR1bGVkIjoiQ0lUIn0sImluaXRpYWxQYXltZW50Ijp0cnVlfQ==',
            'Custom' => '',
            'amount' => 45995,
            'currency' => 'EUR',
            'language' => 'de',
            'userData' => 'U2hvcHdhcmUgVmVyc2lvbjogNS43LjE2IE1vZHVsIFZlcnNpb246ICUlVkVSU0lPTiUl',
            'urlSuccess' => 'https://ct.dev.stefops.de/index.php',
            'urlFailure' => 'https://ct.dev.stefops.de/index.php',
            'urlNotify' => 'https://ct.dev.stefops.de/index.php',
            'orderDesc' => 'Test:0000',
            'transID' => CreditCard::generateTransID(),
            'response' => 'encrypt',
            'reqID' => $reqId,
            'sdZip' => '10779',
            'EtiId' => 'Oxid Test',
        ];

        // Parameters used to check AES encryption via
        // https://computop.com/de/developer/paygate-test
        /* $testParamsAESCheck = [
            'MerchantID' => $this->getMerchantID(),
            'TransID' => 'TID123',
            'Amount' => 123,
            'Currency' => 'EUR',
            'URLNotify' => 'https://www.yourshop.org/notify.php',
            'URLSuccess' => 'https://www.yourshop.org/success.php',
            'URLFailure' => 'https://www.yourshop.org/failure.php',

        ];
        foreach ($testParamsAESCheck as $key => $value) {
            $requestParams[] = "$key=" . $value;
        }
        $requestParams[] = "MAC=" . $this->ctHMAC($testParamsAESCheck);
        */


        $requestParams = [];
        foreach ($testParams as $key => $value) {
            $requestParams[] = "$key=" . $value;
        }
        $requestParams[] = "MAC=" . $this->ctHMAC($testParams);

        $request = join('&', $requestParams);
        $len = mb_strlen($request);  // Length of the plain text string



        $this->checkOpenSSLSupport();

        #$data = $this->ctEncrypt($request, $len, $this->getBlowfishPassword(), $this->encryption);
        $data = \Fatchip\ComputopPayments\Helper\Encryption::getInstance()->encrypt($request, $len);

        if (!$data) {
            throw new Exception('Failed Encrypting Data. ');
            return false;
        }

        $url = 'https://www.computop-paygate.com/payssl.aspx';
        $url .=
            '?MerchantID=' . $this->merchantID .
            '&Len=' . $len .
            '&Data=' . $data;
        return $url;
    }

    /**
     * calls computop api to get ideal financial institutes list
     *
     * @return bool
     */
    public function doAPITest()
    {
        $curl = curl_init();
        $curl = Api::getInstance()->setCurlSecurityOptions($curl);

        curl_setopt($curl, CURLOPT_URL, $this->getUrl());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $resp = curl_exec($curl);

        if (FALSE === $resp) {
            throw new Exception(curl_error($curl), curl_errno($curl));
        }

        if (strpos($resp, 'Unexpected exception') !== false) {
            throw new Exception('Wrong Credentials');
            return false;
        } else {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function checkOpenSSLSupport()
    {
        $ciphers = openssl_get_cipher_methods(false);
        $isBlowfishSupported = in_array(Encryption::blowfishCipher, $ciphers);
        $isAES128Supported = in_array(Encryption::aes128Cipher, $ciphers);
        $isAES192Supported = in_array(Encryption::aes192Cipher, $ciphers);
        $isAES256Supported = in_array(Encryption::aes256Cipher, $ciphers);

        $pwLength = strlen($this->blowfishPassword);
        if ($pwLength <= 16) {
            $keyLength = 16;
        } else if ($pwLength <= 24) {
            $keyLength = 24;
        } else {
            $keyLength = 32;
        }

        if ($this->encryption === 'blowfish' && !$isBlowfishSupported) {
            throw new Exception('Openssl ' . Encryption::blowfishCipher . ' Encryption is not supported on your platform. Please use AES');
            return false;
        }
        if ($keyLength === 16 && $this->encryption === 'aes' && !$isAES128Supported) {
            throw new Exception('Openssl ' . Encryption::aes128Cipher . ' Encryption is not supported on your platform. Please use Blowfish.');
            return false;
        }

        if ($keyLength === 24 && $this->encryption === 'aes' && !$isAES192Supported) {
            throw new Exception('Openssl ' . Encryption::aes192Cipher . ' Encryption is not supported on your platform.  Please use Blowfish.');
            return false;
        }

        if ($keyLength === 32 && $this->encryption === 'aes' && !$isAES256Supported) {
            throw new Exception('Openssl ' .Encryption::aes256Cipher . ' Encryption is not supported on your platform.  Please use Blowfish.');
            return false;
        }
    }
}
