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

use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Helper\Config;
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

        $this->fatchipComputopPaymentService =  new CTPaymentService(Config::getInstance()->getConnectionConfig());
    }

    /**
     * Returns iframe url or redirects directly to it
     *
     * @return mixed
     */
    public function getIframeUrl()
    {
        $redirectUrl = Registry::getSession()->getVariable(Constants::CONTROLLER_PREFIX.'RedirectUrl');
        if (!empty($redirectUrl)) {
            return $redirectUrl;
        }
        return false;
    }
}
