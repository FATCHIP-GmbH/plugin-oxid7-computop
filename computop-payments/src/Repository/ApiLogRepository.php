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

namespace Fatchip\ComputopPayments\Repository;

use Fatchip\ComputopPayments\Core\Constants;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use Fatchip\ComputopPayments\Model\Apilog;

class ApiLogRepository
{
    /**
     * @param Apilog $apiLogEntry
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     * @psalm-suppress InternalMethod
     */
    public function saveApiLog(Apilog $apiLogEntry)
    {
        $sql = 'INSERT IGNORE INTO ' .  Constants::APILOG_TABLE . ' (
                `request`,
                `response`,
                `creation_date`,
                `payment_name`,
                `request_details`,
                `response_details`,
                `trans_id`,
                `pay_id`,
                `x_id`
                ) VALUES (?,?,?,?,?,?,?,?,?);';

        DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute($sql, [
            $apiLogEntry->getRequest(),
            $apiLogEntry->getResponse(),
            $apiLogEntry->getCreationDate(),
            $apiLogEntry->getPaymentName(),
            $apiLogEntry->getRequestDetails(),
            $apiLogEntry->getResponseDetails(),
            $apiLogEntry->getTransId(),
            $apiLogEntry->getPayId(),
            $apiLogEntry->getXId(),
        ]);
    }
}
