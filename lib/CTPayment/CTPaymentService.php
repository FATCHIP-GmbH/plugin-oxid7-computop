<?php
/** @noinspection PhpUnused */

/**
 * The Computop Shopware Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Shopware Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5.6, 7 , 7.1
 *
 * @category  Payment
 * @package   Computop_Shopware5_Plugin
 * @author    FATCHIP GmbH <support@fatchip.de>
 * @copyright 2018 Computop
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      https://www.computop.com
 */

namespace Fatchip\CTPayment;

use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\CTPayment\CTCrif\CRIF;
use Fatchip\CTPayment\CTEnums\CTEnumStatus;
use OxidEsales\Eshop\Core\Registry;


/**
 * Class CTPaymentService
 * @package Fatchip\CTPayment
 */
class CTPaymentService extends Encryption
{

    protected $fatchipComputopConfig;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    protected $fatchipComputopLogger;
    public $fatchipComputopSilentParams;

    /**
     * CTPaymentService constructor
     * @param $config
     */
    public function __construct(
        $config
    ) {
        $this->merchantID = $config['merchantID'];
        $this->blowfishPassword = $config['blowfishPassword'];
        $this->mac = $config['mac'];
        $this->encryption = $config['encryption'];
        $config = new Config();
        $this->fatchipComputopConfig = $config->toArray();
        $this->fatchipComputopSession = Registry::getSession();
        $this->fatchipComputopShopConfig = Registry::getConfig();
        $this->fatchipComputopShopUtils = Registry::getUtils();
        $this->fatchipComputopLogger = new Logger();
    }

    /**
     * @param $className
     * @param $config
     * @param null $ctOrder
     * @param null $urlSuccess
     * @param null $urlFailure
     * @param null $urlNotify
     * @param null $orderDesc
     * @param null $userData
     * @param null $eventToken
     * @param null $isFirm
     * @param null $klarnainvoice
     * @return CTPaymentMethodIframe
     */
    public function getIframePaymentClass(
        $className,
        $config,
        $ctOrder = null,
        $urlSuccess = null,
        $urlFailure = null,
        $urlNotify = null,
        $orderDesc = null,
        $userData = null,
        $eventToken = null,
        $isFirm = null,
        $klarnainvoice = null,
        $urlBack = null
    ) {
        //Lastschrift is an abstract class and cannot be instantiated directly
        if ($className == 'Lastschrift') {
            if ($config['lastschriftDienst'] == 'EVO') {
                $className = 'LastschriftEVO';
            } else {
                if ($config['lastschriftDienst'] == 'DIREKT') {
                    $className = 'LastschriftDirekt';
                } else {
                    if ($config['lastschriftDienst'] == 'INTERCARD') {
                        $className = 'LastschriftInterCard';
                    } else {
                        $className = 'LastschriftDirekt';
                    }
                }
            }
        }

        $class = 'Fatchip\\CTPayment\\CTPaymentMethodsIframe\\' . $className;
        return new $class(
            $config,
            $ctOrder,
            $urlSuccess,
            $urlFailure,
            $urlNotify,
            $orderDesc,
            $userData,
            $eventToken,
            $isFirm,
            $klarnainvoice,
            $urlBack
        );
    }

    /**
     * @param $className
     * @return CTPaymentMethod
     */
    public function getPaymentClass($className)
    {
        $class = 'Fatchip\\CTPayment\\CTPaymentMethods\\' . $className;
        return new $class();
    }

    /**
     * @param $config
     * @param $order
     * @param $orderDesc
     * @param $userData
     * @return CRIF
     */
    public function getCRIFClass($config, $order, $orderDesc, $userData)
    {
        return new CRIF($config, $order, $orderDesc, $userData);
    }

    /**
     * @return CTPaymentConfigForms
     */
    public function getPaymentConfigForms()
    {
        return new CTPaymentConfigForms();
    }

    /**
     * @param array $rawRequest
     * @return CTResponse
     */
    public function getDecryptedResponse(array $rawRequest)
    {
        $decryptedRequest = $this->ctDecrypt($rawRequest['Data'], $rawRequest['Len'], $this->blowfishPassword);
        $requestArray = $this->ctSplit(explode('&', $decryptedRequest), '=');
        // uncomment below to inject schemeReferenceID into Computop Responses for testing
        // $requestArray['schemeReferenceID'] = 'schemeReferenceID_' . date('Y-m-d H-i-s');
        // Set special Custom Params (Oxid Session id and TransId)
        $response = new CTResponse($requestArray);
        $response->setShopTransId($rawRequest['TransId']);
        $response->setSessionId(($rawRequest['SessionId']));
        return $response;
    }

    /**
     * returns an array of paymentMethods
     * @return array
     */
    public function getPaymentMethods()
    {
        return CTPaymentMethods::paymentMethods;
    }

    public function getRequest()
    {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        $custom = Registry::getRequest()->getRequestParameter('Custom');
        $customParams = explode('&', base64_decode($custom));
        $session = explode('=', $customParams[0])[1];
        $transId = explode('=', $customParams[1])[1];

        $PostRequestParams = [
            'Len' => $len,
            'Data' => $data,
            'SessionId' => $session,
            'TransId' => $transId,
        ];

        if (!empty($len) && !empty($data)) {
            return $this->getDecryptedResponse($PostRequestParams);
        }
        return false;
    }

    public function handleDirectPaymentResponse($response)
    {
        $this->fatchipComputopSession->setVariable(Constants::CONTROLLER_PREFIX . 'DirectResponse', $response);
        $directRequest = $this->fatchipComputopSession->getVariable(Constants::CONTROLLER_PREFIX . 'DirectRequest');
        $this->fatchipComputopLogger->logRequestResponse(
            $directRequest,
            $this->fatchipComputopPaymentClass,
            'AUTH',
            $response
        );
        switch ($response->getStatus()) {
            case CTEnumStatus::OK:
            case CTEnumStatus::AUTHORIZED:
            case CTEnumStatus::AUTHORIZE_REQUEST:
                return CTEnumStatus::OK;
            case CTEnumStatus::FAILED:
                $this->fatchipComputopSession->setVariable('FatchipComputopErrorCode', $response->getCode());
                $this->fatchipComputopSession->setVariable('FatchipComputopErrorMessage', $response->getDescription());
                $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
                $returnUrl = $sShopUrl . 'index.php?cl=payment';
                Registry::getUtils()->redirect($returnUrl, false, 301);
                break;
        }
    }

    public function handleRedirectResponse($response)
    {
        $this->fatchipComputopSession->setVariable('FatchipComputopRedirectResponse', $response);
        $redirectRequest = $this->fatchipComputopSession->getVariable(
            Constants::CONTROLLER_PREFIX . 'RedirectRequestParams'
        );
        $this->fatchipComputopLogger->logRequestResponse(
            $redirectRequest,
            $this->fatchipComputopPaymentClass,
            'REDIRECT',
            $response
        );
        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();

        switch ($response->getStatus()) {
            case CTEnumStatus::OK:
            case CTEnumStatus::AUTHORIZED:
            case CTEnumStatus::AUTHORIZE_REQUEST:
            $returnUrl = Registry::getConfig()->getCurrentShopUrl(false)
                . 'index.php?cl=order&fnc=execute&action=result&stoken='
                . Registry::getSession()->getSessionChallengeToken();
                // $returnUrl = $sShopUrl . 'index.php?cl=order&fnc=execute';
                break;
            case CTEnumStatus::FAILED:
                $this->fatchipComputopSession->setVariable('FatchipComputopErrorCode', $response->getCode());
                $this->fatchipComputopSession->setVariable('FatchipComputopErrorMessage', $response->getDescription());
                $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
                $returnUrl = $sShopUrl . 'index.php?cl=payment';
                break;
        }
        Registry::getUtils()->redirect($returnUrl, false, 301);
    }


}
