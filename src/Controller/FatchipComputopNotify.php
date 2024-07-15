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
 * @copyright  2024 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Controller;

use Exception;
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\CTPayment\CTPaymentService;
use Fatchip\CTPayment\CTResponse;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use Fatchip\CTPayment\CTEnums\CTEnumStatus;
use Fatchip\ComputopPayments\Model\Order;

/**
 * Class DispatchController
 *
 */
class FatchipComputopNotify extends FrontendController
{
    protected $fatchipComputopConfig;
    protected $fatchipComputopShopConfig;
    protected $paymentClass;
    protected $fatchipComputopShopUtils;
    protected $paymentService;
    protected $fatchipComputopSession;
    protected $fatchipComputopLogger;

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function init()
    {
        $config = new Config();
        $this->fatchipComputopConfig = $config->toArray();
        $this->paymentService = new CTPaymentService($this->fatchipComputopConfig);
        $this->fatchipComputopShopConfig = Registry::getConfig();
        $this->fatchipComputopShopUtils = Registry::getUtils();
        $this->fatchipComputopSession = Registry::getSession();
        $this->fatchipComputopLogger = new Logger();
        parent::init();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function render()
    {
        $this->notifyAction();
    }

    /**
     * Notify action method
     *
     * Called if Computop sends notifications to NotifyURL,
     * used to update payment status info
     *
     * @return void
     * @throws Exception
     */
    public function notifyAction()
    {
        // TODO uncomment for raw requests
        file_put_contents("requestdump.txt", var_export($_POST, true), FILE_APPEND);
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
        $response = $this->paymentService->getDecryptedResponse($PostRequestParams);
        $paymentName = $this->getPaymentName($response, $session);

        $this->fatchipComputopLogger->logRequestResponse([], $paymentName, 'NOTIFY', $response,);

        switch ($response->getStatus()) {
            case CTEnumStatus::OK:
            case CTEnumStatus::AUTHORIZED:
            case CTEnumStatus::AUTHORIZE_REQUEST:
            /** @var string $orderOxId */
            $orderOxId = $response->getSessionId();
            $order = oxNew(Order::class);
            if ($order->load($orderOxId)) {
                /** @var \Fatchip\ComputopPayments\Model\Order $order */
                if (empty($order->getFieldData('oxordernr'))) {
                    $orderNumber = $order->getFieldData('oxordernr');
                } else {
                    $orderNumber = $order->getFieldData('oxordernr');
                }

                $order->updateOrderAttributes($response);
                $order->updateComputopFatchipOrderStatus('FATCHIP_COMPUTOP_PAYMENTSTATUS_RESERVED');
                // $this->updateRefNrWithComputop($order, $this->paymentClass);
            }
                /* $this->inquireAndupdatePaymentStatus(
                        $order,
                        $paymentName,
                        json_decode($response->getOrderVars(), true)
                    );
                }
                */
                break;
            default:
                exit(0);
        }
        exit(0);
    }

    /**
     * @param $response CTResponse
     * @return string
     */
    protected function getPaymentName(CTResponse $response, $session) {
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($session) !== true) {
         exit;
        }
        if ( $paymentName = $oOrder->getFieldData('oxorder__oxpaymenttype')) {
            return $paymentName;
        } else {
            exit;
        }
    }
}


