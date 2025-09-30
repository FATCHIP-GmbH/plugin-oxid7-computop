<?php

namespace Fatchip\ComputopPayments\Controller;

use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Helper\Config;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Core\Registry;

class FatchipComputopRedirect extends FatchipComputopPayments
{
    protected $_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_redirect_return';

    public function init()
    {
        // deactivated - throws warnings - not sure if needed
        #ini_set('session.cookie_samesite', 'None');
        #ini_set('session.cookie_secure', true);
        parent::init();
    }

    public function __construct()
    {
        parent::__construct();

        $this->fatchipComputopPaymentService =  new CTPaymentService(Config::getInstance()->getConnectionConfig());
    }

    public function render()
    {
        $request = Registry::getRequest();
        $len     = $request->getRequestParameter('Len');
        $data    = $request->getRequestParameter('Data');
        $custom  = $request->getRequestParameter('Custom');
        $response = null;
        if (!empty($len) && !empty($data)) {
            $postParams = [
                'Len'    => $len,
                'Data'   => $data,
                'Custom' => $custom,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($postParams);
        }
        if (is_object($response)) {
            if ($response->getInfoText() === 'fatchip_computop_creditcard') {
                $ccmode = Config::getInstance()->getConfigParam('creditCardMode') ?? '';
                if ($ccmode === 'IFRAME') {
                    $this->_sThisTemplate = "@fatchip_computop_payments/payments/fatchip_computop_iframe.html.twig";
                    if ($response !== null) {
                        $this->_sThisTemplate = "@fatchip_computop_payments/payments/fatchip_computop_iframe_return.html.twig";
                    }
                } elseif ($ccmode === 'SILENT') {
                    return $this->_sThisTemplate;
                }
            }
        }
        return  $this->_sThisTemplate;
    }

    public function getFinishUrl()
    {
        $req         = Registry::getRequest();
        $len         = $req->getRequestParameter('Len');
        $data        = $req->getRequestParameter('Data');
        $customParam = $req->getRequestParameter('Custom');
        $response    = null;
        $custom      = null;

        if (!empty($len) && !empty($data)) {
            $params   = ['Len' => $len, 'Data' => $data, 'Custom' => $customParam];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($params);
            $custom   = $this->fatchipComputopPaymentService->getRequest();
        }

        if (Config::getInstance()->getConfigParam('creditCardMode') === 'SILENT') {
            Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX."DirectResponse", $response);
            Registry::getSession()->setVariable(Constants::CONTROLLER_PREFIX."RedirectResponse", $response);
        }

        $shopUrl  = Registry::getConfig()->getShopUrl();
        $stoken   = ($response && $response->getStoken()) ? $response->getStoken() : ($custom ? $custom->getStoken() : '');
        $sid      = ($custom && $custom->getSessionId()) ? $custom->getSessionId() : '';
        $delAddr  = ($custom && $custom->getDelAdress()) ? $custom->getDelAdress() : '';
        $stoken   = $stoken ?: Registry::getSession()->getVariable('sess_stoken');

        if (!is_object($response) || $response->getStatus() === 'FAILED') {
            $queryParams = [
                'cl'                 => 'payment',
                'FatchipComputopLen' => $len,
                'FatchipComputopData'=> $data,
                'stoken'             => $stoken,
                'sid'                => $sid,
            ];
        } else {
            $queryParams = [
                'cl'                  => 'order',
                'fnc'                 => 'execute',
                'FatchipComputopLen'  => $len,
                'FatchipComputopData' => $data,
                'stoken'              => $stoken,
                'ord_agb'             => '1',
                'sid'                 => $sid,
                'sDeliveryAddressMD5' => $delAddr,
            ];
        }

        $returnUrl = $shopUrl . 'index.php?' . http_build_query($queryParams);
        Registry::getUtils()->redirect($returnUrl, false, 301);
    }

    public function getFinishUrlIframe()
    {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }
        $sShopUrl = Registry::getConfig()->getShopUrl();
        $stoken = $response->getRefNr();
        if (!is_object($response) || $response->getStatus() === 'FAILED') {
            $queryParams = [
                'cl'                 => 'payment',
                'FatchipComputopLen' => $len,
                'FatchipComputopData'=> $data,
                'stoken'             => $stoken,
            ];
        } else {
            $queryParams = [
                'cl'                  => 'order',
                'fnc'                 => 'execute',
                'FatchipComputopLen'  => $len,
                'FatchipComputopData' => $data,
                'stoken'              => $stoken,
                'ord_agb'             => '1',
            ];
        }

        $returnUrl = $sShopUrl . 'index.php?' . http_build_query($queryParams);
        $returnurl = json_encode($returnUrl);
        return  $returnurl;
    }
}
