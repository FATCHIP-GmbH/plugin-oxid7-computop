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

namespace Fatchip\ComputopPayments\Core;

use Exception;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Model\BaseModel as EshopBaseModel;
use OxidEsales\Eshop\Core\Registry;
use Fatchip\CTPayment\CTPaymentMethods;

class Events
{

    public static $sQueryAlterOxorderTransid = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_transid VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;";
    public static $sQueryAlterOxorderPayid = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_payid VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '0' NOT NULL;";
    public static $sQueryAlterOxorderXid = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_xid VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;";
    public static $sQueryAlterOxorderMandateid = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_lastschrift_mandateid VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;";
    public static $sQueryAlterOxorderDos = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_lastschrift_dos VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;";
    public static $sQueryAlterOxorderSchemereferenceid = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_creditcard_schemereferenceid VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;";
    public static $sQueryAlterOxorderAmountCaptured = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_amount_captured DOUBLE DEFAULT '0.0' NOT NULL;";
    public static $sQueryAlterOxorderAmountRefunded = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_amount_refunded DOUBLE iiii DEFAULT '0.0' NOT NULL;";
    public static $sQueryAlterOxorderOrderRemark = "ALTER TABLE oxorder ADD COLUMN fatchip_computop_remark VARCHAR(128) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;";

    /**
     * Execute action on activate event
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function onActivate()
    {
        self::addFatchipComputopPaymentMethods();
        self::createFatchipComputopApiLogTable();
        self::createFatchipComputopIdealBankTable();
        self::updateFatchipComputopOrderAttributes();

        $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $dbMetaDataHandler->updateViews();
    }

    /**
     * Add payment methods
     */
    protected static function updateFatchipComputopOrderAttributes()
    {
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_transid', self::$sQueryAlterOxorderTransid);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_payid', self::$sQueryAlterOxorderPayid);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_xid', self::$sQueryAlterOxorderXid);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_lastschrift_mandateid', self::$sQueryAlterOxorderMandateid);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_lastschrift_dos', self::$sQueryAlterOxorderDos);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_creditcard_schemereferenceid', self::$sQueryAlterOxorderSchemereferenceid);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_amount_captured', self::$sQueryAlterOxorderAmountCaptured);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_amount_refunded', self::$sQueryAlterOxorderAmountRefunded);
        self::addColumnIfNotExists('oxorder',
            'fatchip_computop_remark', self::$sQueryAlterOxorderOrderRemark);
    }

    /**
     * Add payment methods
     */
    protected static function addFatchipComputopPaymentMethods()
    {
        foreach (CTPaymentMethods::paymentMethods AS $paymentMethod) {
            $descriptions = [];
            $descriptions['de']['title'] = $paymentMethod['description'];
            $descriptions['de']['desc'] = $paymentMethod['description'];
            $descriptions['en']['title'] = $paymentMethod['description'];
            $descriptions['en']['desc'] = $paymentMethod['description'];
            self::createPaymentMethod($paymentMethod['name'], $descriptions);
        }
    }

    /**
     * @param string[][] $paymentDescription
     *
     * @throws Exception
     */
    protected static function createPaymentMethod(string $paymentId, array $paymentDescription)
    {
        $payment = oxNew(Payment::class);
        $paymentLoaded = $payment->load($paymentId);
        if (!$paymentLoaded) {
            $payment->setId($paymentId);
            $params = [
                'oxpayments__oxactive' => false,
                'oxpayments__oxaddsum' => 0,
                'oxpayments__oxaddsumtype' => 'abs',
                'oxpayments__oxfromboni' => 0,
                'oxpayments__oxfromamount' => 0,
                'oxpayments__oxtoamount' => 10000
            ];
            $payment->assign($params);
            $payment->save();
            self::assignPaymentToActiveDeliverySets($paymentId);

            $languages = Registry::getLang()->getLanguageIds();
            foreach ($paymentDescription as $languageAbbreviation => $values) {
                $languageId = array_search($languageAbbreviation, $languages, true);
                if ($languageId !== false) {
                    $languageId = (int)$languageId;
                    $payment->loadInLang($languageId, $paymentId);
                    $params = [
                        'oxpayments__oxdesc' => $values['title'],
                        'oxpayments__oxlongdesc' => $values['desc']
                    ];
                    $payment->assign($params);
                    $payment->save();
                }
            }
        }
    }

    /**
     * @param string $paymentId
     * @return void
     * @throws Exception
     */
    protected static function assignPaymentToActiveDeliverySets(string $paymentId)
    {
        $deliverySetIds = self::getActiveDeliverySetIds();
        foreach ($deliverySetIds as $deliverySetId) {
            self::assignPaymentToDelivery($paymentId, $deliverySetId);
        }
    }

    /**
     * @param string $paymentId
     * @param string $deliverySetId
     * @return void
     * @throws Exception
     */
    protected static function assignPaymentToDelivery(string $paymentId, string $deliverySetId)
    {
        $object2Payment = oxNew(EshopBaseModel::class);
        $object2Payment->init('oxobject2payment');
        $object2Payment->assign(
            [
                'oxpaymentid' => $paymentId,
                'oxobjectid'  => $deliverySetId,
                'oxtype'      => 'oxdelset'
            ]
        );
        $object2Payment->save();
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function getActiveDeliverySetIds(): array
    {
        $sql = 'SELECT `OXID`
                FROM `oxdeliveryset`
                WHERE `oxactive` = 1';
        $fromDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        $result = [];
        foreach ($fromDb as $row) {
            $result[$row['OXID']] = $row['OXID'];
        }

        return $result;
    }

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function createFatchipComputopApiLogTable()
    {
        $sql = '

        CREATE TABLE IF NOT EXISTS `fatchip_computop_api_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `request` varchar(255) DEFAULT NULL,
            `response` varchar(255) DEFAULT NULL,
            `creation_date` datetime NOT NULL,
            `payment_name` varchar(255) DEFAULT NULL,
            `request_details` longtext DEFAULT NULL,
            `response_details` longtext DEFAULT NULL,
            `trans_id` varchar(255) DEFAULT NULL,
            `pay_id` varchar(255) DEFAULT NULL,
            `x_id` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
        ';

        $fromDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute($sql);
    }

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function createFatchipComputopIdealBankTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `fatchip_computop_ideal_issuers` (
                `oxid` char(32) NOT NULL,
                `issuer_id` varchar(11) NOT NULL,
                `name` varchar(128) NOT NULL,
                `land` varchar(128) NOT NULL,
                PRIMARY KEY (`oxid`)
            ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
        ';

        $fromDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute($sql);

        $sql = 'SELECT *
                FROM `fatchip_computop_ideal_issuers`
                WHERE 1';

        $rows = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);
        if (empty($rows)) {

        $content = '
            INSERT INTO `fatchip_computop_ideal_issuers` (`id`, `issuer_id`, `name`, `land`) VALUES
                (1, "ABNANL2A", "ABN AMRO", "DE"),
                (2, "ASNBNL21", "ASN Bank", "DE"),
                (3, "BUNQNL2A", "Bunq", "DE"),
                (4, "INGBNL2A", "INGING", "DE"),
                (5, "KNABNL2H", "Knab", "DE"),
                (6, "RABONL2U", "Rabo", "DE"),
                (7, "RBRBNL21", "RegioBank", "DE"),
                (8, "SNSBNL2A", "SNS Bank", "DE"),
                (9, "TRIONL2U", "Triodos Bank", "DE"),
                (10, "FVLBNL22", "van Lanschot", "DE");
        ';
        }
    }

    /**
     * Execute action on deactivate event
     *
     * @return void
     */
    public static function onDeactivate()
    {
        $test = 1;
    }

    /**
     * Add a column to a database table.
     *
     * @param string $sTableName  table name
     * @param string $sColumnName column name
     * @param string $sQuery      sql-query to add column to table
     *
     * @return boolean true or false
     */
    public static function addColumnIfNotExists($sTableName, $sColumnName, $sQuery): bool
    {
        $aColumns = DatabaseProvider::getDb()->getAll("SHOW COLUMNS FROM {$sTableName} LIKE '{$sColumnName}'");

        if (!$aColumns || $aColumns === []) {
            try {
                DatabaseProvider::getDb()->Execute($sQuery);
            } catch (Exception) {
            }
            return true;
        }
        return false;
    }
}
