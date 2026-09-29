<?php

namespace Fatchip\ComputopPayments\Core\Cronjob;

use Fatchip\ComputopPayments\Helper\Config;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;

class FinalizeOrder extends Base
{
    /**
     * Id of current cronjob
     *
     * @var string
     */
    protected $sCronjobId = 'computop_finalize_orders';

    /**
     * Default cronjob interval in minutes
     *
     * @var int
     */
    protected $iDefaultMinuteInterval = 10;

    /**
     * Static property to make finalize order status readable from everywhere in Oxid framework
     *
     * @var bool
     */
    protected static $blIsFinalizingOrder = false;

    /**
     * @param  bool $blIsFinalizingOrder
     * @return void
     */
    public static function setIsFinalizingOrder($blIsFinalizingOrder)
    {
        self::$blIsFinalizingOrder = $blIsFinalizingOrder;
    }

    /**
     * @return bool
     */
    public static function isIsFinalizingOrder()
    {
        return self::$blIsFinalizingOrder;
    }

    /**
     * Collects all expired order ids
     *
     * @return array
     */
    protected function getPaidUnfinishedOrders()
    {

        $aOrders = [];

        $oHelperOrder = oxNew(Order::class);

        $sDefaultFolder = $oHelperOrder->computopGetDefaultFolder();
        $iCronFinishOrdersDays = (int)Config::getInstance()->getConfigParam('cronFinalizeOrdersDays');
        if (empty($iCronFinishOrdersDays)) {
            $iCronFinishOrdersDays = 14;
        }

        $sTriggerDate = date('Y-m-d H:i:s', time() - (60 * 60 * 24 * $iCronFinishOrdersDays));
        $sMinTriggerDate = date('Y-m-d H:i:s', time() - (60 * 2)); // This will prevent finishing legit orders before the customer does
        $sQuery = " SELECT 
                        OXID 
                    FROM 
                        oxorder 
                    WHERE 
                        oxstorno = 0 AND 
                        oxpaymenttype LIKE '%computop%' AND 
                        oxpaymenttype != 'fatchip_computop_paypal_express' AND 
                        fatchip_computop_transid != '' AND
                        fatchip_computop_payid != '' AND
                        oxorderdate > ? AND 
                        oxorderdate < ? AND 
                        oxtransstatus = 'NOT_FINISHED' AND
                        oxpaid = '0000-00-00 00:00:00' AND
                        oxfolder = ?";
        $aParams = [$sTriggerDate, $sMinTriggerDate, $sDefaultFolder];
        if ($this->getShopId() !== false) {
            $sQuery .= " AND oxshopid = ? ";
            $aParams[] = $this->getShopId();
        }
        $aResult = DatabaseProvider::getDb()->getAll($sQuery, $aParams);
        foreach ($aResult as $aRow) {
            $aOrders[] = $aRow[0];
        }

        return $aOrders;
    }

    /**
     * Collects exired order ids and cancels these orders
     *
     * @return bool
     */
    protected function handleCronjob()
    {
        $aUnfinishedOrders = $this->getPaidUnfinishedOrders();
        foreach ($aUnfinishedOrders as $sUnfinishedOrderId) {
            $oOrder = oxNew(Order::class);

            if ($oOrder->load($sUnfinishedOrderId) && $oOrder->computopIsOrderUnfinalized()) {
                self::setIsFinalizingOrder(true);
                $oOrder->computopFinalizeOrder();
                self::setIsFinalizingOrder(false);
                self::outputInfo($oOrder->getId()." - Successfully finalized order");
            }
        }
        return true;
    }
}