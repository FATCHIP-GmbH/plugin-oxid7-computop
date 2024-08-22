<?php

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * The Computop Oxid Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 8.1, 8.2
 *
 * @category   Payment
 * @package    fatchip-gmbh/computop_payments
 * @subpackage Admin_FatchipComputopConfig
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop UpdateIdealIssuers
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Controller;

use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

class FatchipComputopPayPalExpress extends FrontendController
{

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypalexpress';

    protected $fatchipComputopConfig;
    protected $fatchipComputopSession;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopPaymentId;
    protected $fatchipComputopPaymentClass;
    protected $fatchipComputopShopUtils;
    protected $fatchipComputopLogger;
    public $fatchipComputopSilentParams;
    protected $fatchipComputopPaymentService;

    public function init()
    {
        ini_set('session.cookie_samesite', 'None');
        ini_set('session.cookie_secure', true);
        parent::init();
    }

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
        $this->fatchipComputopPaymentService = new CTPaymentService($this->fatchipComputopConfig);
    }

    /**
     * The controller renderer
     *
     * @return string
     */
    public function render()
    {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        $custom = Registry::getRequest()->getRequestParameter('Custom');
        $response = null;

        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
                'Custom' => $custom,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }

        if ($response !== null && $response->getStatus() === 'AUTHORIZED') {
            $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypal_express_return';
        } else {
            $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypal_express';
        }

        return parent::render();
    }

    /**
     * Returns PayPal Express URL or redirects directly to it
     *
     * @return mixed
     */
    public function getPayPalExpressUrl()
    {
        $redirectUrl = $this->fatchipComputopSession->getVariable('FatchipComputopPayPalExpressURL');
        if ($redirectUrl) {
            return $redirectUrl;
        }
    }

    public function success()
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

        $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypal_express_return';
    }

    public function getFinishUrl()
    {
        $len = Registry::getRequest()->getRequestParameter('Len');
        $data = Registry::getRequest()->getRequestParameter('Data');
        $response = null;

        if (!empty($len) && !empty($data)) {
            $PostRequestParams = [
                'Len' => $len,
                'Data' => $data,
            ];
            $response = $this->fatchipComputopPaymentService->getDecryptedResponse($PostRequestParams);
        }

        $sShopUrl = $this->fatchipComputopShopConfig->getShopUrl();
        $stoken = $response->getRefNr();
        $returnUrl = $sShopUrl . 'index.php?cl=order&fnc=execute&FatchipComputopLen=' . $len . '&FatchipComputopData=' . $data
            . '&stoken=' . $stoken;
        return json_encode($returnUrl);
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

    /**
     * Display PayPal button
     */
    public function displayPayPalButton()
    {
        $mid = "YOUR_MERCHANTID";
        $len = "LEN_OF_UNENCRYPTED_BLOWFISH_STRING";
        $data = "BLOWFISH_ENCRYPTED_STRING";

        $params = [
            'MerchantID' => $mid,
            'Len' => $len,
            'Data' => $data,
        ];

        Registry::getUtils()->assignSmartyVariable('paypalParams', $params);

        $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_paypalexpress_button';
    }

    /**
     * Handle PayPal onApprove action
     *
     * @param array $data
     * @return void
     */
    public function onApprove($data)
    {
        $mid = "YOUR_MERCHANTID";
        $payid = $data['orderID'];
        $rd = "MerchantId=$mid&PayId=$payid&OrderId=" . $data['orderID'];

        $url = 'https://www.computop-paygate.com/cbPayPal.aspx?rd=' . base64_encode($rd);
        header("Location: $url");
        exit;
    }

    /**
     * Handle PayPal onCancel action
     *
     * @param array $data
     * @return void
     */
    public function onCancel($data)
    {
        $mid = "YOUR_MERCHANTID";
        $payid = $data['orderID'];
        $rd = "MerchantId=$mid&PayId=$payid&OrderId=" . $data['orderID'];

        $url = 'https://www.computop-paygate.com/cbPayPal.aspx?rd=' . base64_encode($rd) . "&ua=cancel&token=" . $data['orderID'];
        header("Location: $url");
        exit;
    }
}
