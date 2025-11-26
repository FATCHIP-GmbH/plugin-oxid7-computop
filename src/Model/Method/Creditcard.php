<?php

namespace Fatchip\ComputopPayments\Model\Method;

use Fatchip\ComputopPayments\Helper\Config;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class Creditcard extends RedirectPayment
{
    const ID = "fatchip_computop_creditcard";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'CreditCard';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "payssl.aspx"; // endpoint for iframe and payment page mode

    /**
     * @var bool
     */
    protected $addLanguageToUrl = true;

    /**
     * Determines if auth requests adds address parameters to the request
     *
     * @var bool
     */
    protected $sendAddressData = true;

    /**
     * @var bool
     */
    protected $refNrUpdateNeeded = true;

    /**
     * @var string|false
     */
    #protected $customFrontendTemplate = 'fatchip_computop_creditcard_iframe.html.twig';

    /**
     * @return string
     */
    public function getRequestType()
    {
        return parent::getRequestType()."-".Config::getInstance()->getConfigParam('creditCardMode');
    }

    /**
     * Returns the API endpoint
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        if (Config::getInstance()->getConfigParam('creditCardMode') == 'SILENT') {
            $this->apiEndpoint = "paynow.aspx"; // endpoint for silent mode
        }
        return parent::getApiEndpoint();
    }

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @param array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        $params = [
            'RefNr' => Registry::getSession()->getSessionChallengeToken(), // FCRM_TODO: RefNr is misused here to secure session for reentry from iframe payment... Repair it to use correct refNr
        ];
        if ((bool)Config::getInstance()->getConfigParam('creditCardMode') === true) {
            $params['orderDesc'] = 'Test:0000';
        }
        return $params;
    }

    /**
     * @param Order $order
     * @param CTResponse $notify
     * @return void
     */
    public function handleNotifySpecific(Order $order, CTResponse $notify)
    {
        $changed = false;

        if (!empty($notify->getPCNr())) {
            $order->assign(['fatchip_computop_pcnr' => $notify->getPCNr()]);
            $changed = true;
        }

        if (!empty($notify->getCCExpiry())) {
            $order->assign(['fatchip_computop_ccexpiry' => $notify->getCCExpiry()]);
            $changed = true;
        }

        if (!empty($notify->getCCBrand())) {
            $order->assign(['fatchip_computop_ccbrand' => $notify->getCCBrand()]);
            $changed = true;
        }

        if (!empty($notify->getCardHolder())) {
            $order->assign(['fatchip_computop_cardholder' => $notify->getCardHolder()]);
            $changed = true;
        }

        if ($changed === true) {
            $order->save();
        }
    }
}