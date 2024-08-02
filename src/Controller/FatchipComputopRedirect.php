<?php

namespace Fatchip\ComputopPayments\Controller;

use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class FatchipComputopRedirect extends FatchipComputopPayments
{
    protected $_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_redirect_return';

    public function init()
    {
        ini_set('session.cookie_samesite', 'None');
        ini_set('session.cookie_secure', true);
        parent::init();
    }

    public function __construct()
    {
        parent::__construct();

        $config = new Config();
        $this->fatchipComputopConfig = $config->toArray();
        $this->fatchipComputopSession = Registry::getSession();
        $this->fatchipComputopShopConfig = Registry::getConfig();
        $this->fatchipComputopShopUtils = Registry::getUtils();
        $this->fatchipComputopLogger = new Logger();
        $this->fatchipComputopPaymentService =  new CTPaymentService($this->fatchipComputopConfig);
    }

    public function render() {
        return $this->_sThisTemplate;
    }

    public function getFinishUrl() {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        $custom = Registry::getRequest()->getRequestParameter('Custom');

        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
                'Custom' => $custom,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
            $response2 = $this->fatchipComputopPaymentService->getRequest();
        }

        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        if (!empty($response)) {
            $stoken = $response->getRefNr();
        }

        $returnUrl = $sShopUrl . 'index.php?cl=order&fnc=execute&FatchipComputopLen=' . $len . '&FatchipComputopData=' . $data
            . '&stoken=' . $stoken . '&sid=' . $sessionId;

        Registry::getUtils()->redirect($returnUrl, false, 301);

    }
}
