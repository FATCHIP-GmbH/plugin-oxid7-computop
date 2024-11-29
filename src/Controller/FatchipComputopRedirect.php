<?php

namespace Fatchip\ComputopPayments\Controller;

use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
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
            $custom = $this->fatchipComputopPaymentService->getRequest();
        }
        if ($this->fatchipComputopConfig['creditCardMode'] === 'SILENT') {
            $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse', $response);
            $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'RedirectResponse',$response);
        }
        $stoken = '';
        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        if (!empty($response)) {
            $stoken = $response->getStoken();
        }
        $sid = '';
        $delAdress = '';
        if ($custom) {
            $this->fatchipComputopLogger->logRequestResponse([], 'REDIRECT', 'AUTH', $response,);

            if (!empty($custom->getSessionId())) {
                $sid = $custom->getSessionId();
            }
            if (empty($response->getStoken())) {
                $stoken = $custom->getStoken();
            }
            if (!empty($custom->getDelAdress())) {
                $delAdress = $custom->getDelAdress();
            }
        }
        if (empty($stoken)) {
            $stoken = Registry::getSession()->getVariable('sess_stoken');
        }
        $returnUrl = $sShopUrl . 'index.php?cl=order&fnc=execute&FatchipComputopLen=' . $len . '&FatchipComputopData=' . $data
            . '&stoken=' . $stoken.'&sid='.$sid.'&sDeliveryAddressMD5='.$delAdress;

        Registry::getUtils()->redirect($returnUrl, false, 301);

    }
}
