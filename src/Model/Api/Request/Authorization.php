<?php

namespace Fatchip\ComputopPayments\Model\Api\Request;

use Fatchip\ComputopPayments\Helper\Api;
use Fatchip\ComputopPayments\Helper\Checkout;
use Fatchip\ComputopPayments\Helper\Payment;
use Fatchip\ComputopPayments\Model\Method\BaseMethod;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Registry;

class Authorization extends Base
{
    /**
     * @param  BaseMethod $methodInstance
     * @param  double     $amount
     * @param  string     $currency
     * @param  string     $refNr
     * @param  Order|null $order
     * @param  bool       $log
     * @param  bool       $encrypt
     * @return array
     */
    public function generateRequest(BaseMethod $methodInstance, $amount, $currency, $refNr, ?Order $order = null, $encrypt = false, $log = false)
    {
        $this->addParameter('Currency', $currency);
        $this->addParameter('Amount', Api::getInstance()->formatAmount($amount, $currency));

        $this->addParameter('TransID', $this->getTransactionId($order));
        $this->addParameter('ReqId', Api::getInstance()->getRequestId());
        $this->addParameter('EtiID', Api::getInstance()->getIdentString());

        $this->addParameter('RefNr', $refNr);

        $this->addParameter('URLSuccess', $methodInstance->getSuccessUrl());
        $this->addParameter('URLFailure', $methodInstance->getFailureUrl());
        $this->addParameter('URLBack', $methodInstance->getCancelUrl());
        $this->addParameter('URLCancel', $methodInstance->getCancelUrl());
        $this->addParameter('URLNotify', $methodInstance->getNotifyUrl());
        $this->addParameter('Response', 'encrypt');

        $this->addParameter('orderDesc', $this->getParameter('TransID'));

        $dynValue = Registry::getSession()->getVariable('dynvalue');

        $this->addParameters($methodInstance->getPaymentSpecificParameters($order, $dynValue));

        $params = $this->getParameters();
        if ($log === true) {
            Api::getInstance()->addLogEntry($params, [], get_class($methodInstance), $methodInstance->getRequestType());
        }

        if ($encrypt === true) {
            $params = $this->getEncryptedParameters($params);
            $params = array_merge($params, $methodInstance->getUnencryptedParameters($order));
        }
        return $params;
    }

    /**
     * @param  Order   $order
     * @param  double  $amount
     * @param  bool    $log
     * @param  bool    $encrypt
     * @return array
     */
    public function generateRequestFromOrder(Order $order, $amount, $encrypt = false, $log = false)
    {
        /** @var BaseMethod $methodInstance */
        $methodInstance = $order->computopGetPaymentModel();

        $currency = $order->oxorder__oxcurrency->value;
        $refNr = $order->oxorder__oxordernr->value;

        $shippingSelectorPrefix = 'bill';
        if ($order->oxorder__oxdellname->value != '') {
            $shippingSelectorPrefix = 'del';
        }

        if ($methodInstance->isAddressDataNeeded() === true) {
            $this->addParameter('billingAddress', $this->getAddressInfo($order, 'bill'));
            $this->addParameter('shippingAddress', $this->getAddressInfo($order, $shippingSelectorPrefix));
        }

        if ($methodInstance->isBillingAddressDataNeeded() === true) {
            $this->addParameters($this->getAddressParameters($order, 'bill', 'bd', $methodInstance));
        }

        if ($methodInstance->isShippingAddressDataNeeded() === true) {
            $this->addParameters($this->getAddressParameters($order, $shippingSelectorPrefix, 'sd', $methodInstance));
        }

        return $this->generateRequest($methodInstance, $amount, $currency, $refNr, $order, $encrypt, $log);
    }

    /**
     * @param  Basket     $basket
     * @param  BaseMethod $methodInstance
     * @param  bool       $encrypt
     * @param  bool       $log
     * @return array
     */
    public function generateRequestFromQuote(Basket $basket, $encrypt = false, $log = false)
    {
        // Only needed for PayPal Express and Easycredit at the moment. So payment methods where no order is created before a redirect

        $methodInstance = Payment::getInstance()->getComputopPaymentModel($basket->getPaymentId());

        $amount = $basket->getPrice()->getBruttoPrice();
        $currency = $basket->getBasketCurrency()->name;
        $refNr = $methodInstance->getTemporaryRefNr();

        $shippingAddress = Checkout::getInstance()->getShippingAddressFromSession();
        if (empty($shippingAddress)) {
            $shippingAddress = Registry::getSession()->getUser();
        }

        if ($methodInstance->isBillingAddressDataNeeded() === true) {
            $this->addParameters($this->getAddressParameters(Registry::getSession()->getUser(), '', 'bd', $methodInstance));
        }

        if ($methodInstance->isShippingAddressDataNeeded() === true) {
            $this->addParameters($this->getAddressParameters($shippingAddress, '', 'sd', $methodInstance));
        }

        return $this->generateRequest($methodInstance, $amount, $currency, $refNr, null, $encrypt, $log);
    }

    /**
     * Return country iso code for given country id
     *
     * @param  string $countryId
     * @param  bool   $iso3
     * @return string
     */
    protected function getCountryCode($countryId, $iso3 = false)
    {
        $country = oxNew('oxcountry');
        $country->load($countryId);
        if ($iso3 === true) {
            return $country->oxcountry__oxisoalpha3->value;
        }
        return $country->oxcountry__oxisoalpha2->value;
    }

    /**
     * @param Order|User|Address $object
     * @param string                    $prefix
     * @param BaseMethod|null           $methodInstance
     * @return array
     */
    protected function getAddressParameters($object, $addressSelectorPrefix = "bill", $prefix = '', ?BaseMethod $methodInstance = null)
    {
        $params = [
            $prefix.'FirstName' => $object->getFieldData("ox".$addressSelectorPrefix."fname"),
            $prefix.'LastName' => $object->getFieldData("ox".$addressSelectorPrefix."lname"),
            $prefix.'Zip' => $object->getFieldData("ox".$addressSelectorPrefix."zip"),
            $prefix.'City' => $object->getFieldData("ox".$addressSelectorPrefix."city"),
            $prefix.'CountryCode' => $this->getCountryCode($object->getFieldData("ox".$addressSelectorPrefix."countryid")),
            $prefix.'Street' => $object->getFieldData("ox".$addressSelectorPrefix."street"),
            $prefix.'StreetNr' => $object->getFieldData("ox".$addressSelectorPrefix."streetnr"),
        ];

        if (!empty($object->getFieldData("ox".$addressSelectorPrefix."company"))) {
            $params[$prefix.'CompanyName'] = $object->getFieldData("ox".$addressSelectorPrefix."company");
        }

        if ($methodInstance instanceof \Fatchip\ComputopPayments\Model\Method\Ratepay\Base) {
            $params[$prefix.'ZIPCode'] = $params[$prefix.'Zip'];
        }

        return $params;
    }

    /**
     * Returns address string (json and base64 encoded)
     *
     * @param  Order  $order
     * @param  string $addressSelectorPrefix
     * @return string
     */
    protected function getAddressInfo($order, $addressSelectorPrefix = "bill")
    {
        $address = [
            'city' => $order->getFieldData("ox".$addressSelectorPrefix."city"),
            'country' => [
                'countryA3' => $this->getCountryCode($order->getFieldData("ox".$addressSelectorPrefix."countryid"), true),
            ],
            'addressLine1' => [
                'street' => trim($order->getFieldData("ox".$addressSelectorPrefix."street")),
                'streetNumber' => trim($order->getFieldData("ox".$addressSelectorPrefix."city")),
            ],
            'postalCode' => $order->getFieldData("ox".$addressSelectorPrefix."zip"),
        ];
        return base64_encode(json_encode($address));
    }

    /**
     * @param  Order   $order
     * @param  double  $amount
     * @return array|null
     */
    public function sendRequest(Order $order, $amount)
    {
        /** @var BaseMethod $methodInstance */
        $methodInstance = $order->computopGetPaymentModel();

        $params = $this->generateRequestFromOrder($order, $amount);
        $response = $this->handlePaymentCurlRequest($methodInstance, $params, $order);

        return $response;
    }

    /**
     * @param  Order   $order
     * @param  double  $amount
     * @return array|null
     */
    public function sendRequestFromBasket()
    {
        $basket = Registry::getSession()->getBasket();

        /** @var BaseMethod $methodInstance */
        $methodInstance = Payment::getInstance()->getComputopPaymentModel($basket->getPaymentId());

        $params = $this->generateRequestFromQuote($basket);
        $response = $this->handlePaymentCurlRequest($methodInstance, $params);

        return $response;
    }
}
