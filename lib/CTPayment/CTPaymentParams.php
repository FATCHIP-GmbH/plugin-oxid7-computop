<?php

namespace Fatchip\CTPayment;

use Exception;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Helper\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;

/**
 * Class CTPaymentMethods
 * @package Fatchip\CTPayment
 */
class CTPaymentParams
{
    const authParams = [
        'merchantId',
        'blowfishPassword',
        'hmac',
        'encryption'
    ];
    const generalParams =
        [
            'amount',
            'currency',
            'language',
            'userData',
            'urlSuccess',
            'urlSuccess',
            'urlFailure',
            'urlNotify',
            'urlBack',
            'orderDesc',
            'transId',
            'payId',
            'reqId',
            'ipAddr',
            'sdZip',
            'rtf',
            'response',
            'refNr',
            'email',
            'customerId'
        ];

    protected static $aURLBackWhitelist = [
        'fatchip_computop_paypal_standard',
        'fatchip_computop_paypal_express',
        'fatchip_computop_amazonpay',
        'fatchip_computop_easycredit',
    ];

    public static function getUrlParams($paymentId = false)
    {
        $shopUrl = rtrim(Registry::getConfig()->getShopUrl(), '/') . '/';
        $sessionId = Registry::getSession()->getId();

        $successController = Constants::GENERAL_PREFIX . 'redirect';
        $failureController = $successController;
        if ($paymentId === 'fatchip_computop_easycredit') {
            $successController = 'order';
        }

        $params = [
            'UrlSuccess' => self::buildUrl($shopUrl, $successController, $sessionId),
            'UrlFailure' => self::buildUrl($shopUrl, $failureController, $sessionId),
            'UrlNotify'  => self::buildUrl($shopUrl, Constants::GENERAL_PREFIX . 'notify'),
        ];

        if (Config::getInstance()->getConfigParam('creditCardMode') === 'PAYMENTPAGE') { // don't send urlcancel/back in IFRAME mode, since iframe breakout does not work currently and user can use shop navigation to leave iframe
            self::$aURLBackWhitelist[] = 'fatchip_computop_creditcard';
        }

        if (in_array($paymentId, self::$aURLBackWhitelist)) {
            $params['UrlCancel'] = $params['UrlFailure'];
            $params['UrlBack']   = $params['UrlFailure'];
        }

        return $params;
    }

    /**
     * Helper method to build URLs.
     *
     * @param string $shopUrl   The base shop URL.
     * @param string $cl        The controller or class parameter.
     * @param string $sessionId The current session ID.
     * @param array  $params    Additional query parameters.
     *
     * @return string The constructed URL.
     */
    protected static function buildUrl($shopUrl, $cl, $sessionId = null, array $params = [])
    {
        $baseParams = ['cl' => $cl];
        if (!empty($sessionId)) {
            $baseParams['sid'] = $sessionId;
        }

        $queryString = http_build_query(array_merge($baseParams, $params));

        return "{$shopUrl}index.php?{$queryString}";
    }

    public static function getCustomParam($transid, $paymentId = false)
    {
        Registry::getSession()->setVariable(
            Constants::GENERAL_PREFIX . 'TransId',
            $transid
        );

        $orderOxId = Registry::getSession()->getVariable('sess_challenge');
        $deliveryAddressMd5 = Registry::getRequest()->getRequestParameter('sDeliveryAddressMD5');
        $params = [
            'session'   => Registry::getSession()->getId(),
            'transid'   => $transid,
            'orderid'   => $orderOxId,
            'stoken'    => Registry::getSession()->getSessionChallengeToken(),
            'delAdressMd5' => $deliveryAddressMd5
        ];

        if ($paymentId !== false) {
            $params['paymentid'] = $paymentId;
        }
        $customString = http_build_query($params);
        $encodedCustom = base64_encode($customString);

        return ['custom' => 'Custom=' . $encodedCustom];
    }

    public static function getUserDataParam()
    {
        $moduleVersion = '';

        try {
            $shopConfig =  ContainerFactory::getInstance()
                ->getContainer()
                ->get(ShopConfigurationDaoBridgeInterface::class)->get();
            try {
                $moduleConfig = $shopConfig->getModuleConfiguration('fatchip_computop_payments');
                $moduleVersion = 'ModuleVersion: '.$moduleConfig->getVersion();
            } catch (ModuleConfigurationNotFoundException $e) {
                Registry::getLogger()->error('ModuleConfig not found: ' . $e->getMessage());
            }
        } catch (Exception $e) {
            Registry::getLogger()->error('ModuleConfig fetch error: ' . $e->getMessage());
        }

        $activeShop = Registry::getConfig()->getActiveShop();
        $shopName = $activeShop->oxshops__oxname->value;
        $shopVersion = $activeShop->oxshops__oxversion->value;

        return sprintf('%s %s %s', $shopName, $shopVersion, $moduleVersion);
    }
}
