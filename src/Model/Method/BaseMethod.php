<?php

namespace Fatchip\ComputopPayments\Model\Method;

use Fatchip\Computop\Model\ComputopConfig;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

abstract class BaseMethod
{
    /**
     * @var string
     */
    protected $oxidPaymentId;

    /**
     * @var string
     */
    protected $libClassName;

    /**
     * @var string
     */
    protected $apiEndpoint;

    /**
     * @var string
     */
    protected $requestType = "REQUEST";

    /**
     * @var string|false
     */
    protected $customFrontendTemplate = false;

    /**
     * @var bool
     */
    protected $isIframeLibMethod = true;

    /**
     * Determines if auth requests adds billing address parameters to the request
     *
     * @var bool
     */
    protected $addBillingAddressData = false;

    /**
     * Determines if auth requests adds shipping address parameters to the request
     *
     * @var bool
     */
    protected $addShippingAddressData = false;

    /**
     * Determines if auth requests adds address parameters to the request
     *
     * @var bool
     */
    protected $sendAddressData = false;

    /**
     * @var Config|null
     */
    protected $config = null;

    /**
     * @var bool
     */
    protected $addLanguageToUrl = false;

    /**
     * @var bool
     */
    protected $refNrUpdateNeeded = false;

    /**
     * @var bool
     */
    protected $isRealAutoCaptureMethod = false;

    /**
     * Get oxid payment id of this payment method
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->oxidPaymentId;
    }

    /**
     * Get name of the matching class in the Computop lib
     *
     * @return string
     */
    public function getLibClassName()
    {
        return $this->libClassName;
    }

    /**
     * Returns the API endpoint
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @return bool
     */
    public function isIframeLibMethod()
    {
        return $this->isIframeLibMethod;
    }


    /**
     * Returns if address parameters have to be added in auth request
     *
     * @return bool
     */
    public function isBillingAddressDataNeeded()
    {
        return $this->addBillingAddressData;
    }

    /**
     * Returns if address parameters have to be added in auth request
     *
     * @return bool
     */
    public function isShippingAddressDataNeeded()
    {
        return $this->addShippingAddressData;
    }

    /**
     * Returns if refnr has to be updated after order is finished
     *
     * @return bool
     */
    public function isRefNrUpdateNeeded()
    {
        return $this->refNrUpdateNeeded;
    }

    /**
     * Returns true if capture is done directly by Computop
     *
     * @return bool
     */
    public function isRealAutoCaptureMethod()
    {
        return $this->isRealAutoCaptureMethod;
    }

    /**
     * @param array $dynValue
     * @param string $fieldName
     * @return string|false
     */
    protected function getDynValue($dynValue, $fieldName)
    {
        if (!empty($dynValue[$this->getPaymentId()."_".$fieldName])) {
            return $dynValue[$this->getPaymentId()."_".$fieldName];
        }
        return false;
    }

    /**
     * @param array $dynValue
     * @return string
     */
    protected function getTelephoneNumber($dynValue)
    {
        $dynValueTelephone = $this->getDynValue($dynValue, "telephone");
        if (!empty($dynValueTelephone)) {
            return $dynValueTelephone;
        }

        $user = Registry::getSession()->getUser();
        if (!empty($user->oxuser__oxmobfon->value)) {
            return $user->oxuser__oxmobfon->value;
        }
        if (!empty($user->oxuser__oxprivfon->value)) {
            return $user->oxuser__oxprivfon->value;
        }
        if (!empty($user->oxuser__oxfon->value)) {
            return $user->oxuser__oxfon->value;
        }
        return false;
    }

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @return array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        return []; // filled in child classes
    }

    /**
     * Return parameters specific to this payment type that have to be added to the unencrypted URL
     *
     * @param  Order|null $order
     * @return array
     */
    public function getUnencryptedParameters(?Order $order = null)
    {
        $params = [];
        if ($this->addLanguageToUrl === true) {
            $params['language'] = strtolower(Registry::getLang()->translateString('FATCHIP_COMPUTOP_LANGUAGE'));
        }
        return $params;
    }

    /**
     * Return parameters specific to this payment subtype
     *
     * @param  Order $order
     * @param  array $dynValue
     * @return array
     */
    public function getSubTypeSpecificParameters(Order $order, $dynValue)
    {
        return []; // filled in child classes
    }

    /**
     * @param Order $order
     * @param CTResponse $notify
     * @return void
     */
    public function handleNotifySpecific(Order $order, CTResponse $notify)
    {
        // hook for extention by child methods
    }

    /**
     * @return string|false
     */
    public function getCustomFrontendTemplate()
    {
        if (!empty($this->customFrontendTemplate)) {
            return "@fatchip_computop_payments/payments/".$this->customFrontendTemplate;
        }
        return false;
    }

    /**
     * Returns if address parameters have to be added in auth request
     *
     * @return bool
     */
    public function isAddressDataNeeded()
    {
        return $this->sendAddressData;
    }

    /**
     * @return string
     */
    protected function getShopUrl()
    {
        return rtrim(Registry::getConfig()->getShopUrl(), '/') . '/';
    }

    /**
     * @param $url
     * @return mixed
     */
    protected function buildReturnUrl($url)
    {
        return $url."&sid=".Registry::getSession()->getId();
    }

    /**
     * @return string
     */
    public function getTemporaryRefNr()
    {
        return 'tmp_'.rand(10000000, 99999999).date('his');
    }

    /**
     * Returns redirect url for success case
     *
     * @return string|null
     */
    public function getSuccessUrl()
    {
        return $this->buildReturnUrl($this->getShopUrl().'index.php?cl=fatchip_computop_redirect');
    }

    /**
     * Returns redirect url for failure case
     *
     * @return string|null
     */
    public function getFailureUrl()
    {
        return $this->buildReturnUrl($this->getShopUrl().'index.php?cl=fatchip_computop_redirect');
    }

    /**
     * Returns redirect url for cancel case
     *
     * @return string|null
     */
    public function getCancelUrl()
    {
        return $this->buildReturnUrl($this->getShopUrl().'index.php?cl=fatchip_computop_redirect');
    }

    /**
     * Returns URL for notify controller
     *
     * @return string|null
     */
    public function getNotifyUrl()
    {
        return $this->getShopUrl().'index.php?cl=fatchip_computop_notify';
    }
}