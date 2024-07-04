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
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 8.1, 8.2
 *
 * @category   Payment
 * @package    fatchip-gmbh/computop_payments
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop UpdateIdealIssuers
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use Fatchip\CTPayment\CTAPITestService;

/**
 * controller for validation API credentials
 */
class FatchipComputopUpdateIdealIssuers extends AdminController
{
    private $config;

    public function __construct()
    {
        $config = new \Fatchip\ComputopPayments\Core\Config();
        $this->config = $config->toArray();
        parent::__construct();
    }

    /**
     * assigns error and count of updated items to view
     *
     * @return void
     */
    public function getIssuerListAction()
    {
        $service = new CTAPITestService($this->config);
        try {
            $success = $service->getIdealIssuers();
        } catch (Exception $e) {
            $success = false;
        }

        if ($success) {
            $this->View()->assign(['success' => true]);
        } else {
            $this->View()->assign(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
