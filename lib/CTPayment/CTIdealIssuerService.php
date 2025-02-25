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

use Exception;

/**
 * Class CTIdealIssuerService.
 *
 *  gets supported ideal financial institutes from the computop api
 *  as a fallback an array is returned
 */
class CTIdealIssuerService extends Encryption
{

    /**
     * CTIdealIssuerService constructor.
     *
     * @param $config array plugin configuration
     */
    public function __construct(
        $config
    )
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
     * @see ctHMAC()
     * @see ctEncrypt()
     *
     * @return string url
     */
    public function getIssuerListURL()
    {
        $queryarray = [
            'MerchantID=' . $this->merchantID,
            'MAC=' . $this->ctHMAC([]),
        ];

        $query = join("&", $queryarray);

        $len = strlen($query);  // Length of the plain text string
        $data = $this->ctEncrypt($query, $len, $this->blowfishPassword, $this->encryption);

        return 'https://www.computop-paygate.com/idealIssuerList.aspx' .
            '?MerchantID=' . $this->merchantID .
            '&Len=' . $len .
            '&Data=' . $data;
    }

    /**
     * calls computop api to get ideal financial institutes list
     *
     * @return array
     */
    public function getIssuerList()
    {
        $issuerList = [];
        // does not work, for testing just update with static array)
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->getIssuerListURL(),
        ));

        try {
            $resp = curl_exec($curl);

            if (FALSE === $resp) {
                throw new Exception(curl_error($curl), curl_errno($curl));
            }

            $respArray = [];
            parse_str($resp, $respArray);
            $decryptedRequest = $this->ctDecrypt($respArray['Data'], $respArray['Len'], $this->blowfishPassword);
            $decryptedArray = $this->ctSplit(explode('&', $decryptedRequest), '=');
            $issuerList = $decryptedArray['IdealIssuerList'];

        } catch (Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }

        if (empty($issuerList)){
            $issuerList = [
                [
                    'issuerId' => 'ABNANL2A',
                    'name' => 'ABN AMRO',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'ASNBNL21',
                    'name' => 'ASN Bank',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'BUNQNL2A',
                    'name' => 'Bunq',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'INGBNL2A',
                    'name' => 'INGING',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'KNABNL2H',
                    'name' => 'Knab',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'RABONL2U',
                    'name' => 'Rabo',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'RBRBNL21',
                    'name' => 'RegioBank',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'SNSBNL2A',
                    'name' => 'SNS Bank',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'TRIONL2U',
                    'name' => 'Triodos Bank',
                    'land' => 'DE',
                ],
                [
                    'issuerId' => 'FVLBNL22',
                    'name' => 'van Lanschot',
                    'land' => 'DE',
                ],
            ];
        }
        return $issuerList;
    }
}
