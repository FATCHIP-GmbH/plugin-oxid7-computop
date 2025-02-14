<?php
namespace Fatchip\ComputopPayments\Core;

use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Model\Order;
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
    protected $errorCode;
    protected $errorMessage;

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
    public function unsetSessionVars()
    {

        $sessionVars = [
            'FatchipComputopErrorCode',
            'FatchipComputopErrorMessage',
            'paymentid',
            'sess_challenge',
            Constants::CONTROLLER_PREFIX . 'DirectResponse',
            Constants::CONTROLLER_PREFIX . 'RedirectResponse',
            Constants::CONTROLLER_PREFIX . 'DirectRequest',
            Constants::CONTROLLER_PREFIX . 'RedirectUrl',
            Constants::CONTROLLER_PREFIX . 'PpeFinished',
        ];

        foreach ($sessionVars as $var) {
            $this->deleteVariable($var);
        }
    }

    public function cleanUpPPEOrder()
    {
        $orderId = $this->getVariable('sess_challenge');

        if ($orderId) {
            $oOrder = oxNew(Order::class);
            $oOrder->delete($orderId);
        }

        $this->deleteVariable(Constants::CONTROLLER_PREFIX . 'PpeOngoing');
    }

    /**
     * @return void
     */
    protected function logRedirectToPayment()
    {
        $request = Registry::getRequest();
        $len = $request->getRequestParameter('FatchipComputopLen') ? $request->getRequestParameter(
            'FatchipComputopLen'
        ) : $request->getRequestParameter('Len');

        $data = $request->getRequestParameter('FatchipComputopLen') ? $request->getRequestParameter(
            'FatchipComputopData'
        ) : $request->getRequestParameter('Data');

        if (empty($len) || empty($data)) {
            return;
        }

        $config = new Config();
        $this->fatchipComputopConfig        = $config->toArray();
        $this->fatchipComputopPaymentService = new CTPaymentService($this->fatchipComputopConfig);

        $postRequestParams = [
            'Len'    => $len,
            'Data'   => $data,
        ];

        $response      = $this->fatchipComputopPaymentService->getDecryptedResponse($postRequestParams);
        if ($response->getStatus() ==='FAILED') {
            $this->errorCode = $response->getCode();
            $this->errorMessage = $response->getDescription();
        }
        $logger = oxNew(Logger::class);
        $logger->logRequestResponse($postRequestParams, 'RedirectToPaymentPage', 'REDIRECT-BACK', $response);
    }

    /**
     * @return void
     */
    public function handlePaymentSession()
    {
        $this->logRedirectToPayment();
        if ($errorCode = $this->errorCode) {
            $errorMessage = $this->errorMessage;
            $errorText = $errorCode === 22890703 || $errorCode === 22060200 ? 'FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_CANCEL' : "$errorCode-$errorMessage";
            Registry::getUtilsView()->addErrorToDisplay($errorText);
            return;
        }
        $ppeFinished =  $this->getVariable(Constants::CONTROLLER_PREFIX . 'PpeFinished');
        $ppeOnGoing  =  $this->getVariable(Constants::CONTROLLER_PREFIX . 'PpeOngoing');
        $redirected  = Registry::getRequest()->getRequestParameter('redirected');
        if ($this->getVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse')) {
            $this->unsetSessionVars();
        }

        if ($ppeFinished === 1 && $ppeOnGoing) {
            $this->unsetSessionVars();
        }

        if ($ppeFinished === 0 && !empty($ppeOnGoing) && $redirected === "0") {
            $this->cleanUpPPEOrder();
            $this->unsetSessionVars();
            Registry::getUtilsView()->addErrorToDisplay('FATCHIP_COMPUTOP_PAYMENTS_PAYMENT_CANCEL');
        }
        if ($this->getVariable(Constants::CONTROLLER_PREFIX . 'RedirectUrl')  ) {
            $this->cleanUpPPEOrder();
            $this->unsetSessionVars();
        }

        $this->deleteVariable(Constants::CONTROLLER_PREFIX . 'RedirectResponse');
        $this->deleteVariable(Constants::CONTROLLER_PREFIX . 'DirectRequest');

    }
}
