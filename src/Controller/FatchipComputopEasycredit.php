<?php

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
 * PHP version 5.6, 7.0 , 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Controller;

use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Model\Order;
use Fatchip\ComputopPayments\Model\PaymentGateway;
use Fatchip\CTPayment\CTPaymentMethodsIframe\EasyCredit;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

class FatchipComputopEasycredit extends FrontendController
{

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe';

    protected $fatchipComputopConfig;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    protected $fatchipComputopLogger;
    public $fatchipComputopSilentParams;
    protected $fatchipComputopPaymentService;

    /**
     * Class constructor, sets all required parameters for requests.
     */
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


    /**
     * The controller renderer
     *
     *
     * @return string
     */
    public function render()
    {
        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        $returnUrl = $sShopUrl . 'index.php?cl=order';
        Registry::getUtils()->redirect($returnUrl, false);


    }

    /**
     * Returns iframe url or redirects directly to it
     *
     *
     * @return mixed
     */
    public function getIframeUrl()
    {

        $redirectUrl = $this->fatchipComputopSession->getVariable('fatchipComputopIFrameURL');
        if ($redirectUrl) {
            return $redirectUrl;
        }
    }
    public function getDecisionParams($payID, $transID, $amount, $currency)
    {
        $params = [
            'payID' => $payID,
            'merchantID' => $this->merchantID,
            'transID' => $transID,
            'Amount' => $amount,
            'currency' => $currency,
            'EventToken' => 'GET',
            'version' => 'v3',
        ];
        return $params;
    }
    public function success() {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }
        if ($this->fatchipComputopConfig['creditCardMode'] === 'IFRAME') {
            $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe_return';
        } else {
        }
    }

    public function getFinishUrl() {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }
        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        $returnUrl = $sShopUrl . 'index.php?cl=order&amp;fnc=execute&amp;FatchipComputopLen=' . $len . '&amp;FatchipComputopData=' . $data;
        return $returnUrl;

    }

    public function getResponse()
    {
        $response = false;
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }
        return $response;
    }

    public function cancelAction() {
        die('cancel');
        $test = 2;
    }

    public function failureAction() {
        die('failure');
        $test = 3;
    }
}
