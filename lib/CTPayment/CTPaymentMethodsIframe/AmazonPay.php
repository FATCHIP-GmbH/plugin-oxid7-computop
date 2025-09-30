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
 * PHP version 8.0
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @subpackage CTPaymentMethods
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */
namespace Fatchip\CTPayment\CTPaymentMethodsIframe;

use Fatchip\CTPayment\CTAddress\CTAddress;
use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethod;

use Fatchip\CTPayment\CTPaymentMethodIframe;


/**
 * Class AmazonPay
 * @package Fatchip\CTPayment\CTPaymentMethods
 */
class AmazonPay extends CTPaymentMethodIframe
{
    const paymentClass = 'AmazonPay';

    /**
     * returns PaymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/AmazonAPA.aspx';
    }
    public function getAuthorizeParams($payID, $transID, $amount, $currency, $capture)
    {
        $params = [
            'merchantID' => $this->merchantID,
            'amount' => $amount,
            'currency' => $currency,
            'Capture' => $capture,
        ];

        return $params;
    }
    public function __construct(
        $config,
        $order,
        $urlSuccess,
        $urlFailure,
        $urlNotify,
        $orderDesc,
        $userData,
        $eventToken = null,
        $isFirm = null,
        $klarnainvoice = null,
        $urlBack
    )
    {
        parent::__construct($config, $order, $orderDesc, $userData);

        $this->setUrlSuccess($urlSuccess);
        $this->setUrlFailure($urlFailure);
        $this->setUrlNotify($urlNotify);
        $this->setUrlBack($urlBack);

        $this->setMsgVer('2.0');
        $this->setUserData(base64_encode($userData));

        if($config['creditCardTestMode']) {
            $this->setOrderDesc('Test:0000');
        }
        else {
            $this->setOrderDesc($orderDesc);
        }


        $this->setBillingAddress($order->getBillingAddress());
        $this->setShippingAddress($order->getShippingAddress());
        $this->setCapture('MANUAL');

        $this->setCustom();

    }
    public function setCapture($capture)
    {
        $this->capture = $capture;
    }

    /**
     * @ignore <description>
     * @param string $msgVer
     */
    public function setMsgVer($msgVer)
    {
        $this->msgVer = $msgVer;
    }
    /**
     * sets and returns request parameters for amazon
     * "LOGON" api call
     *
     * @param $transID
     * @param $accessToken
     * @param $tokenType
     * @param $expiry
     * @param $scope
     * @param $countryCode
     * @param $urlNotify
     * @return array
     */
    public function getAmazonInitParams($merchantId, $transID, $countryCode, $amount, $currency, $urlSuccess, $URLFailure, $URLNotify,
                                        $URLCancel, $shopUrl, $txType = 'Authorize', $localCurrency = 'EUR', $scope = '' )
    {
        $params = [
            'transID' => $transID,
            'CountryCode' => $countryCode,
            'amount' => $amount,
            'currency' => $currency,
            'URLSuccess' => $urlSuccess,
            'URLFailure' => $URLFailure,
            'URLNotify' => $URLNotify,
            'UrlCancel' => $URLCancel,
            'ShopUrl' => $shopUrl,
            'txType' => $txType,
            'LocalCurrency' => $localCurrency,
            // 'Scope' => $scope
        ];

        return $params;
    }

    /**
     * sets and returns request parameters for amazon
     * "LOGON" api call
     *
     * @param $transID
     * @param $accessToken
     * @param $tokenType
     * @param $expiry
     * @param $scope
     * @param $countryCode
     * @param $urlNotify
     * @return array
     */
    public function getAmazonLGNParams($transID, $accessToken, $tokenType, $expiry, $scope, $countryCode, $urlNotify)
    {
        $params = [
            'merchantID' => $this->merchantID,
            'transID' => $transID,
            'CountryCode' => $countryCode,
            'URLNotify' => $urlNotify,
            'AccessToken' => $accessToken,
            'TokenType' => $tokenType,
            'Expiry' => $expiry,
            'Scope' => $scope,
            'EventToken' => 'LGN',
        ];

        return $params;
    }

    /**
     * sets and returns request parameters for amazon
     * "Set Order Details" api call
     *
     * @param $payID
     * @param $transID
     * @param $amount
     * @param $currency
     * @param $orderDesc
     * @param $referenceID
     * @return array
     */
    public function getAmazonSODParams($payID, $transID, $amount, $currency, $orderDesc, $referenceID)
    {
        $params = [
            'payID' => $payID,
            'merchantID' => $this->merchantID,
            'transID' => $transID,
            'amount' => $amount,
            'currency' => $currency,
            'OrderDesc' => $orderDesc,
            'OrderReferenceID' => $referenceID,
            'EventToken' => 'SOD',
        ];

        return $params;
    }

    /**
     * sets and returns request parameters for amazon
     * "Get Order Details" api call
     *
     * @param $payID
     * @param $orderDesc
     * @param $referenceID
     * @return array
     */
    public function getAmazonGODParams($payID, $orderDesc, $referenceID)
    {
        $params = [
            'payID' => $payID,
            'merchantID' => $this->merchantID,
            'OrderDesc' => $orderDesc,
            'OrderReferenceID' => $referenceID,
            'EventToken' => 'GOD',
        ];

        return $params;
    }

    /**
     * sets and returns request parameters for amazon
     * "ATH" api call
     *
     * @param $payID
     * @param $transID
     * @param $amount
     * @param $currency
     * @param $referenceID
     * @param $orderDesc
     * @return array
     */
    public function getAmazonATHParams($payID, $transID, $amount, $currency, $referenceID, $orderDesc)
    {
        $params = [
            'payID' => $payID,
            'merchantID' => $this->merchantID,
            'transID' => $transID,
            'amount' => $amount,
            'currency' => $currency,
            'OrderDesc' => $orderDesc,
            'OrderReferenceID' => $referenceID,
            'EventToken' => 'ATH',
            'Capture' => $this->config['amazonCaptureType'],
        ];

        return $params;
    }
    /**
     * @ignore <description>
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @ignore <description>
     * @param CTAddress $CTAddress
     */
    public function setBillingAddress($CTAddress)
    {
        $this->billingAddress = base64_encode(json_encode($this->declareAddress($CTAddress)));
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @ignore <description>
     * @param CTAddress $CTAddress
     */
    public function setShippingAddress($CTAddress)
    {
        $this->shippingAddress = base64_encode(json_encode($this->declareAddress($CTAddress)));
    }

    /**
     * @param CTAddress $CTAddress
     * @return array
     */
    protected function declareAddress($CTAddress)
    {
        $address['city'] = $CTAddress->getCity();
        $address['country']['countryA3'] = $CTAddress->getCountryCodeIso3();
        $address['addressLine1']['street'] = $CTAddress->getStreet();
        $address['addressLine1']['streetNumber'] = $CTAddress->getStreetNr();
        $address['postalCode'] = $CTAddress->getZip();
        return $address;
    }

    /**
     * @return string
     */
    public function getBillToCustomer()
    {
        return $this->billToCustomer;
    }

    /**
     * @param CTOrder $ctOrder
     */
    public function setBillToCustomer($ctOrder)
    {
        #$customer['consumer']['salutation'] = $ctOrder->getBillingAddress()->getSalutation();
        $customer['consumer']['firstName'] = $ctOrder->getBillingAddress()->getFirstName();
        $customer['consumer']['lastName'] = $ctOrder->getBillingAddress()->getLastName();
        $customer['email'] = $ctOrder->getEmail();
        $this->billToCustomer = base64_encode(json_encode($customer));
    }

    /**
     * @return string
     */
    public function getShipToCustomer()
    {
        return $this->shipToCustomer;
    }

    /**
     * @param CTOrder $ctOrder
     */
    public function setShipToCustomer($ctOrder)
    {
        #$customer['consumer']['salutation'] = $ctOrder->getShippingAddress()->getSalutation();
        $customer['consumer']['firstName'] = $ctOrder->getShippingAddress()->getFirstName();
        $customer['consumer']['lastName'] = $ctOrder->getShippingAddress()->getLastName();
        $customer['email'] = $ctOrder->getEmail();
        $customer['customerNumber'] = $ctOrder->getCustomerID();
        $this->shipToCustomer = base64_encode(json_encode($customer));
    }
    public function getCredentialsOnFile()
    {
        //  return $this->credentialOnFile;
    }

    /**
     * return string
     */
    public function setCredentialsOnFile($unscheduled = 'CIT', $initalPayment = true)
    {
        $credentialsOnFile['type']['unscheduled'] = $unscheduled;
        $credentialsOnFile['initialPayment'] = $initalPayment;
        //  $this->credentialOnFile = base64_encode(json_encode($credentialsOnFile));
    }

    public function setCustom()
    {
        $this->Custom = '';
    }
}
