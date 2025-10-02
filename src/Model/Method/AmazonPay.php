<?php

namespace Fatchip\ComputopPayments\Model\Method;

use Fatchip\ComputopPayments\Helper\Checkout;
use Fatchip\ComputopPayments\Helper\Config;
use Fatchip\ComputopPayments\Model\Api\Request\Authorization;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order;

class AmazonPay extends RedirectPayment
{
    const ID = "fatchip_computop_amazonpay";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'AmazonPay';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "amazonAPA.aspx";

    /**
     * @var bool
     */
    protected $isIframeLibMethod = false;

    /**
     * Determines if auth requests adds shipping address parameters to the request
     *
     * @var bool
     */
    protected $addShippingAddressData = true;

    /**
     * @var bool
     */
    protected $refNrUpdateNeeded = true;

    /**
     * @var bool
     */
    protected $isRealAutoCaptureMethod = true;

    /**
     * @var string|false
     */
    protected $customFrontendTemplate = 'fatchip_computop_amazonpay.html.twig';

    protected function getAmazonAuthResponse()
    {
        $authResponse = Registry::getSession()->getVariable("ctAmazonAuthResponse");
        if (!empty($authResponse)) {
            return $authResponse;
        }

        $authRequest = new Authorization();
        $response = $authRequest->sendRequestFromBasket();

        Registry::getSession()->setVariable("ctAmazonAuthResponse", $response);

        return $response;
    }

    protected function getAmazonPayload()
    {
        $response = $this->getAmazonAuthResponse();
        if (!empty($response['buttonpayload'])) {
            return $response['buttonpayload'];
        }
        return null;
    }

    protected function getAmazonSignature()
    {
        $response = $this->getAmazonAuthResponse();
        if (!empty($response['buttonsignature'])) {
            return $response['buttonsignature'];
        }
        return null;
    }

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @return array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        $shippingObject = Checkout::getInstance()->getShippingAddressFromSession();
        if (empty($shippingObject)) { // is not a digital/virtual order? -> add shipping address
            $shippingObject = Registry::getSession()->getUser();
        }

        return [
            'checkoutMode' => 'ProcessOrder',
            'TxType' => Config::getInstance()->getConfigParam('amazonCaptureType') == 'AUTO' ? 'AuthorizeWithCapture' : 'Authorize',
            'CountryCode' => Config::getInstance()->getConfigParam('amazonMarketplace'), // Country code of used marketplace. Options EU, UK, US and JP.
            'Name' => $shippingObject->getFieldData("oxfname")." ".$shippingObject->getFieldData("oxlname"),
            'SDZipcode' => $shippingObject->getFieldData("oxzip"),
            'sdPhone' => $this->getTelephoneNumber($dynValue),
            #'ShopUrl' => '',
        ];
    }

    public function getAmazonPayFrontendConfig()
    {
        return [
            'currency' => Registry::getSession()->getBasket()->getBasketCurrency()->name,
            'amazonpayMerchantId' => Config::getInstance()->getConfigParam('amazonpayMerchantId'),
            'amazonpayPubKeyId' => Config::getInstance()->getConfigParam('amazonpayPubKeyId'),
            'amazonButtonColor' => Config::getInstance()->getConfigParam('amazonButtonColor'),
            'amazonPayload' => $this->getAmazonPayload(),
            'amazonSignature' => $this->getAmazonSignature(),
        ];
    }
}