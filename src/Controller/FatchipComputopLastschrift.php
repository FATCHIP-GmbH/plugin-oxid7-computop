<?php

namespace Fatchip\ComputopPayments\Controller;

use Exception;
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Core\Logger;
use Fatchip\ComputopPayments\Model\Order;
use Fatchip\CTPayment\CTEnums\CTEnumStatus;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class DispatchController
 *
 */
class FatchipComputopLastschrift extends FatchipComputopPayments
{
    protected $fatchipComputopConfig;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopShopUtils;
    protected $paymentService;
    protected $paymentClass = 'Lastschrift';

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
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($session) !== true) {
            exit;
        }
        $paymentName = $this->getPaymentName($oOrder);
        $paymentName = Constants::getPaymentClassfromId($paymentName);
        $this->fatchipComputopLogger->logRequestResponse([], $paymentName, 'NOTIFY', $response,);

        switch ($response->getStatus()) {
            case CTEnumStatus::OK:
            case CTEnumStatus::AUTHORIZED:
            case CTEnumStatus::AUTHORIZE_REQUEST:
                /** @var string $orderOxId */
                $orderOxId = $response->getSessionId();
                /** @var Order $order */
                $order = oxNew(Order::class);
                if ($order->load($orderOxId)) {
                    if (empty($order->getFieldData('oxordernr'))) {
                        $orderNumber = $order->getFieldData('oxordernr');
                    } else {
                        $orderNumber = $order->getFieldData('oxordernr');
                    }
                    $order->updateOrderAttributes($response);
                   // $order->customizeOrdernumber($response);
                    $responseRefNr =  $this->updateRefNrWithComputop($order);
                    //  $order->autoCapture();


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
}


