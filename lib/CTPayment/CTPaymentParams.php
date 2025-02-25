<?php

namespace Fatchip\CTPayment;

use Exception;
use Fatchip\ComputopPayments\Core\Constants;
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
    public static function getUrlParams($paymentId = false, $config = false)
    {
        $shopUrl = rtrim(Registry::getConfig()->getShopUrl(), '/') . '/';
        $sessionId = Registry::getSession()->getId();

        if ($paymentId === 'fatchip_computop_easycredit') {
            $paymentClass = $paymentId;
            $urlSuccess   = self::buildUrl($shopUrl, 'order', $sessionId);
        } else {
            $paymentClass = Constants::GENERAL_PREFIX . 'redirect';
            $urlSuccess   = self::buildUrl($shopUrl, $paymentClass, $sessionId);
        }

        $urlCancel  = self::buildUrl($shopUrl, $paymentClass, $sessionId);
        $urlNotify  = self::buildUrl($shopUrl, Constants::GENERAL_PREFIX . 'notify', $sessionId);
        $urlFailure = $urlCancel;

        $params = [
            'UrlSuccess' => $urlSuccess,
            'UrlFailure' => $urlFailure,
            'UrlNotify'  => $urlNotify,
            'UrlCancel'  => $urlCancel,
            'UrlBack'    => $urlCancel,
        ];

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
    protected static function buildUrl($shopUrl, $cl, $sessionId, array $params = [])
    {
        $baseParams = [
            'cl'  => $cl,
            'sid' => $sessionId,
        ];

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
            'session'   => $orderOxId,
            'transid'   => $transid,
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
