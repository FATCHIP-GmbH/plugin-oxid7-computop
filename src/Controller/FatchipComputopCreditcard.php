<?php

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Oxid Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Oxid Plugin. If not, see <http://www.gnu.org/licenses/>.
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

class FatchipComputopCreditcard extends FrontendController
{
    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe';

    /**
     * Flag if current view is an order view
     *
     * @var bool
     */
    protected $_blIsOrderStep = true;

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
        // deactivated - throws warnings - not sure if needed
        #ini_set('session.cookie_samesite', 'None');
        #ini_set('session.cookie_secure', true);
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
        $response = $this->getResponse();
        if (!empty($response) && $this->fatchipComputopConfig['creditCardMode'] === 'SILENT' ) {
            $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe_return';
        } else {
            $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe';
            if ($this->fatchipComputopConfig['creditCardMode'] === 'IFRAME' && (!empty($response) && $response->getStatus() === 'AUTHORIZED')) {
                $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe_return';
            } elseif ($this->fatchipComputopConfig['creditCardMode'] === 'IFRAME') {
                $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe';
            }
        }

        return parent::render();
    }
    /**
     * Returns iframe url or redirects directly to it
     *
     *
     * @return mixed
     */
    public function getIframeUrl()
    {
        $redirectUrl = $this->fatchipComputopSession->getVariable('FatchipComputopIFrameURL');
        if (!empty($redirectUrl)) {
            return $redirectUrl;
        }
        return false;
    }

    public function success()
    {
        if ($this->fatchipComputopConfig['creditCardMode'] === 'IFRAME') {
            $this->_sThisTemplate = '@fatchip_computop_payments/payments/fatchip_computop_iframe_return';
        }
    }

    public function getFinishUrl()
    {
        $response = $this->getResponse();
        if (!empty($response)) {
            $len = Registry::getRequest()->getRequestParameter('Len');
            $data = Registry::getRequest()->getRequestParameter('Data');
            $returnUrl = $this->fatchipComputopShopConfig->getShopUrl() . 'index.php?cl=order&fnc=execute&FatchipComputopLen=' . $len . '&FatchipComputopData=' . $data . '&stoken='.$response->getRefNr();
            return json_encode($returnUrl);
        }
        return false;
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
}
