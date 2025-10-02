<?php

namespace Fatchip\ComputopPayments\Model\Method;

use Fatchip\ComputopPayments\Helper\Config;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

class PayPalExpress extends RedirectPayment
{
    const ID = "fatchip_computop_paypal_express";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'PayPalExpress';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "paypalComplete.aspx";

    /**
     * @var bool
     */
    protected $isIframeLibMethod = false;

    public function isActive(): bool
    {
        $oBasket = Registry::getSession()->getBasket();
        $oPayment = oxNew(Payment::class);

        try {
            if (($oPayment->load('fatchip_computop_paypal_express') == false) || ($oPayment->oxpayments__oxactive && $oPayment->oxpayments__oxactive->value === 0)) {
                return false;
            }

            $bIsPaymentValid = $oPayment->isValidPayment(
                null,
                Registry::getConfig()->getShopId(),
                $oBasket->getUser(),
                $oBasket->getPriceForPayment(),
                $oBasket->getShippingId()
            );

            return $bIsPaymentValid;
        } catch (\Exception $exception) {
            Registry::getLogger()->error('PaypalExpress: isActive: ' . $exception->getMessage());
        }

        return false;
    }

    public function getPayPalExpressControllerUrl($fnc = null)
    {
        $url = Registry::getConfig()->getShopUrl() . 'index.php?cl=fatchip_computop_paypal_express';
        if (!empty($fnc)) {
            $url .= '&fnc=' . $fnc;
        }
        return $url;
    }

    protected function getPartnerAttributionId()
    {
        if ($this->isTestModeActive() === false) {
            return Config::getInstance()->getConfigParam('paypalExpressPartnerAttributionID');
        }
        return 'Computop_PSP_PCP_Test';
    }

    public function isTestModeActive(): bool
    {
        return (bool)Config::getInstance()->getConfigParam('paypalExpressTestMode');
    }

    public function getPaypalClientId(): ?string
    {
        if ($this->isTestModeActive() === false) {
            return Config::getInstance()->getConfigParam('paypalExpressClientID');
        }
        return 'AUeU8a0ihEF4KCezWdehyi7IbSSrVjr7cis1dKM2jeoX2MZ-bTDwnTQv75_n8ZAbnOJHpFd1Rc6PGO4H';
    }

    public function getPaypalMerchantId(): ?string
    {
        if ($this->isTestModeActive() === false) {
            return Config::getInstance()->getConfigParam('paypalExpressMerchantID');
        }
        return 'KP89GMC7465RA';
    }

    public function getPayPalExpressConfig(): array
    {
        return [
            'computop' => [
                'merchantId' => Config::getInstance()->getConfigParam('merchantID'),
                'partnerAttributionId' => $this->getPartnerAttributionId(),
                'actions' => [
                    'urls' => [
                        'createOrder' => $this->getPayPalExpressControllerUrl('createOrder'),
                        'onApprove' => $this->getPayPalExpressControllerUrl('onApprove'),
                        'onCancel' => $this->getPayPalExpressControllerUrl('onCancel'),
                    ]
                ]
            ],
            'paypal' => [
                'active' => $this->isActive(),
                'intent' => 'authorize', // This module does not work with "real" AUTO-mode, only "fake" AUTO-mode. So leave this at "authorize" and don't go to "capture" mode!
                'clientId' => $this->getPaypalClientId(),
                'merchantId' => $this->getPaypalMerchantId()
            ]
        ];
    }
}