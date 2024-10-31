<?php

/**
 * The Computop Shopware Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Shopware Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5.6, 7.0 , 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment;

use Exception;
use Fatchip\ComputopPayments\Core\Config;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class CTPaymentMethod
 */
abstract class CTPaymentMethod extends Encryption
{

    const paymentClass = '';

    /**
     * These params should not be send with the computop requests and are filtered out in prepareComputopRequest
     */
    const paramexcludes = [
        'blowfishPassword' => 'blowfishPassword',
        'Language' => 'Language',
        'MAC' => 'MAC',
        'mac' => 'mac',
        'merchantID' => 'merchantID',
        'encryption' => 'encryption',
    ];

    /**
     * Vom Paygate vergebene ID für die Zahlung. Z.B. zur Referenzierung in Batch-Dateien.
     *
     * @var string
     */
    protected $payID;

    /**
     * ID die an CT übergeben wird damit CT reports über herkünft Transactionen machen kann
     * Wird mit gleichen wert gefüllt als userData
     * @var string
     */
    protected $EtiId;

    protected $config;

    protected $utils;

    protected $encryption;

    public function __construct()
    {
        $this->config = new Config();
    }

    /**
     * @param string $PayID
     * @ignore <description>
     */
    public function setPayID($PayID)
    {
        $this->payID = $PayID;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getPayID()
    {
        return $this->payID;
    }

    /**
     * @param string $EtiId
     * @ignore <description>
     */
    public function setEtiId($EtiId)
    {
        $this->EtiId = $EtiId;
    }

    /**
     * @return string
     * @ignore <description>
     */
    public function getEtiId()
    {
        return $this->EtiId;
    }

    /**
     * ctHMAC
     * @param $params
     * @return string
     */
    protected function ctHMAC($params)
    {
        $data = $params['payID'] . '*' . $params['transID'] . '*' . $this->merchantID . '*' . $params['amount'] . '*' . $params['currency'];
        return strtoupper(hash_hmac("sha256", $data, $this->mac));
    }

    /**
     * Prepares CT Request. Takes all params, creates a querystring, determines Length and encrypts the data
     *
     * @param $params
     * @param $url
     * @param string $addTemplate
     */
    public function prepareComputopRequest($params, $url, $addTemplate = '')
    {
        $additionFlag = false;
        $config = new Config();
        $this->config = $config->toArray();

        $this->merchantID = $this->config['merchantID'];
        $this->blowfishPassword = $this->config['blowfishPassword'];
        $this->mac = $this->config['mac'];
        $this->encryption = $this->config['encryption'];
        $requestParams = [];

        // StefTest:
        unset($params['PayPalMethod']);

        foreach ($params as $key => $value) {
            if (!array_key_exists($key, $this::paramexcludes)) {
                $requestParams[] = "$key=" . $value;
            }
        }
        $requestParams[] = "MAC=" . $this->ctHMAC($params);

        $request = join('&', $requestParams);
        $len = mb_strlen($request);  // Length of the plain text string
        $data = $this->ctEncrypt($request, $len, $this->blowfishPassword, $this->encryption);

        $url .=
            '?MerchantID=' . $this->merchantID .
            '&Len=' . $len .
            '&Data=' . $data;

        if ($addTemplate) {
            $url .= '&template=' . $addTemplate;
        }

        return $url;
    }

    /**
     * Prepares CT Request. Takes all params, creates a querystring, determines Length and encrypts the data
     * this is used by creditcard payments in "paynow silent mode"
     * @param $params
     * @return array
     */
    public function prepareSilentRequest($params)
    {
        $requestParams = [];
        foreach ($params as $key => $value) {
            if (!array_key_exists($key, $this::paramexcludes)) {
                $requestParams[] = "$key=" . $value;
            }
        }
        $requestParams[] = "MAC=" . $this->ctHMAC($params);
        $request = join('&', $requestParams);
        $len = mb_strlen($request);  // Length of the plain text string
        $data = $this->ctEncrypt($request, $len, $this->blowfishPassword, $this->encryption);

        return ['MerchantID' => $this->merchantID, 'Len' => $len, 'Data' => $data];
    }

    /**
     * makes a server-to-server-call to the computop api
     *
     * uses curl for api communication
     *
     * @param $ctRequest
     * @param $url
     * @return CTResponse
     * @see prepareComputopRequest()
     *
     */
    public function callComputop($ctRequest, $url)
    {
        $curl = curl_init();
        $curlUrl = $this->prepareComputopRequest($ctRequest, $url);
        curl_setopt_array($curl,
            [CURLOPT_RETURNTRANSFER => 1,
             CURLOPT_URL => $curlUrl
            ]);
        try {
            $resp = curl_exec($curl);

            if (false === $resp) {
                throw new Exception(curl_error($curl), curl_errno($curl));
            }
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        } catch (Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }

        if ($httpcode === 302) {
            // Registry::getUtils()->redirect($resp, false);
        }
        $arr = [];
        // Paypal Special:
        $resp = strstr($resp, 'Len=');
        $resp = str_replace('amp;', '', $resp);
        parse_str($resp, $arr);
        $plaintext = $this->ctDecrypt($arr['Data'], $arr['Len'], $this->blowfishPassword);
        $response = new CTResponse($this->ctSplit(explode('&', $plaintext), '='));
        return $response;
    }

    /**
     * returns refund/debit URL
     * @return string
     */
    public function getCTRefundURL()
    {
        return 'https://www.computop-paygate.com/credit.aspx';
    }

    /**
     * returns Capture URL. Can be overridden to return null if Capture is impossible for a paymentmethod
     * @return string
     */
    public function getCTCaptureURL()
    {
        return 'https://www.computop-paygate.com/capture.aspx';
    }

    /**
     * returns InquireURL
     * @return string
     */
    public function getCTInquireURL()
    {
        return 'https://www.computop-paygate.com/inquire.aspx';
    }

    /**
     * returns RefNrChangeURL, used to set the refNr for a transaction in CT-Analytics
     * @return string
     */
    public function getCTRefNrChangeURL()
    {
        return 'https://www.computop-paygate.com/RefNrChange.aspx';
    }

    /**
     * sets and returns request parameters for refunds
     *
     * @param $PayID
     * @param $Amount
     * @param $Currency
     * @param null $transID
     * @param null $xID
     * @param null $orderDesc
     * @param null $klarnaInvNo
     * @param null $schemeReferenceID
     * @return array
     */
    public function getRefundParams($PayID, $Amount, $Currency, $transID = null, $xID = null, $orderDesc = null, $klarnaInvNo = null, $schemeReferenceID = null, $orderAmount = null)
    {
        $reason = $Amount < $orderAmount ? 'WIDERRUF_TEILWEISE' : 'WIDERRUF_VOLLSTAENDIG';
        $params = [
            'payID' => $PayID,
            'amount' => $Amount,
            'currency' => $Currency,
            // used by easyCredit
            'Date' => date("Y-m-d"),
            // used by amazonpay
            'transID' => $transID,
            'xID' => $xID,
            //used by klarna
            'orderDesc' => $orderDesc,
            'invNo' => $klarnaInvNo,
            // used by creditcard 3DS 2
            'schemeReferenceID' => $schemeReferenceID,
            // used by easyCredit refunds. possible values: WIDERRUF_VOLLSTAENDIG, WIDERRUF_TEILWEISE, RUECKGABE_GARANTIE_GEWAEHRLEISTUNG, MINDERUNG_GARANTIE_GEWAEHRLEISTUNG
            'Reason' => $reason,
            'Custom' => $this->Custom,
        ];

        return $params;
    }

    /**
     * sets and returns request parameters for captures
     *
     * @param $PayID
     * @param $Amount
     * @param $Currency
     * @param null $transID
     * @param null $xID
     * @param null $orderDesc
     * @param null $schemeReferenceID
     * @return array
     */
    public function getCaptureParams($PayID, $Amount, $Currency, $transID = null, $xID = null, $orderDesc = null, $schemeReferenceID = null)
    {
        $params = [
            'payID' => $PayID,
            'amount' => $Amount,
            'currency' => $Currency,
            // used by easyCredit
            'Date' => date("Y-m-d"),
            // used by amazonpay
            'transID' => $transID,
            'xID' => $xID,
            //used by klarna
            'orderDesc' => $orderDesc,
            // used by creditcard 3DS 2
            'schemeReferenceID' => $schemeReferenceID,
        ];

        return $params;
    }

    /**
     * sets and returns request parameters for inquire api calls
     *
     * @param $PayID
     * @return array
     */
    public function getInquireParams($PayID)
    {
        $params = [
            'payID' => $PayID,
        ];

        return $params;
    }

    /**
     * sets and returns request parameters for reference number change api call
     *
     * @param $PayID
     * @param $RefNr
     * @return array
     */
    public function getRefNrChangeParams($PayID, $RefNr)
    {
        $params = [
            'payID' => $PayID,
            'RefNr' => $RefNr,
        ];

        return $params;
    }

    /**
     * Returns parameters for redirectURL
     *
     * @param $params
     *
     * @return array
     */
    public function cleanUrlParams($params)
    {
        $requestParams = [];
        foreach ($params as $key => $value) {
            if (!is_null($value) && !array_key_exists($key, $this::paramexcludes)) {
                $requestParams[$key] = $value;
            }
        }
        return $requestParams;
    }

    /**
     * Format amount
     */
    public function formatAmount($amount)
    {
        return number_format($amount * 100, 0, '.', '');
    }

    /**
     * Generate random transaction id
     */
    public static function generateTransID($digitCount = 12)
    {
        mt_srand((double)microtime() * 1000000);

        $transID = (string)mt_rand();
        // y: 2 digits for year
        // m: 2 digits for month
        // d: 2 digits for day of month
        // H: 2 digits for hour
        // i: 2 digits for minute
        // s: 2 digits for second
        $transID .= date('ymdHis');
        // $transID = md5($transID);
        $transID = substr($transID, 0, $digitCount);

        return $transID;
    }
}
