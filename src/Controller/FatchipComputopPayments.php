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
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Controller\FrontendController;

class FatchipComputopPayments extends FrontendController
{

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = '';

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
        $this->fatchipComputopPaymentService = new CTPaymentService($this->fatchipComputopConfig);
    }

    /**
     * The controller renderer
     *
     *
     * @return string
     */
    public function render()
    {
        $response = $this->fatchipComputopPaymentService->getRequest();

        if ($response) {
            $this->fatchipComputopPaymentService->handleRedirectResponse($response);
        }
        return parent::render();
    }
}