<?php
namespace Fatchip\ComputopPayments\Core;

use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Core\Registry;

class FatchipComputopSession extends FatchipComputopSession_parent {
    protected $fatchipComputopConfig;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    protected $fatchipComputopLogger;
    public $fatchipComputopSilentParams;
    protected $fatchipComputopPaymentService;


        protected function allowSessionStart()
        {
            $len = Registry::getRequest()->getRequestParameter('Len');
            $data = Registry::getRequest()->getRequestParameter('Data');
            $paymentClass = Registry::getRequest()->getRequestParameter('cl');
            if ($len && $data && $paymentClass === 'fatchip_computop_redirect' && $_SERVER['HTTP_REFERER'] === 'https://www.computop-paygate.com/') {
                $config = new Config();
                $this->fatchipComputopConfig = $config->toArray();
                $this->fatchipComputopPaymentService = new CTPaymentService($this->fatchipComputopConfig);
                $response = $this->fatchipComputopPaymentService->getRequest();
                if ($response && $response->getSessionId()) {
                    return false;
                }
            }
          return  parent::allowSessionStart();

        }
}
