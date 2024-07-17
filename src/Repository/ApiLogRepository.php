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

use Fatchip\ComputopPayments\Model\ApiLog;

class ApiLogRepository
{
    /**
     * @param ApiLog $apiLogEntry
     * @return bool|string|null
     * @throws \Exception
     */
    public function saveApiLog(ApiLog $apiLogEntry): bool|string|null
    {
       // $oApiLog  = oxNew(ApiLog::class);
        $apiLogEntry->assign([
            'request' => $apiLogEntry->getRequest(),
            'response' =>       $apiLogEntry->getResponse(),
            'creation_date' =>    $apiLogEntry->getCreationDate(),
            'payment_name' => $apiLogEntry->getPaymentName(),
            'request_details' => $apiLogEntry->getRequestDetails(),
            'response_details' =>  $apiLogEntry->getResponseDetails(),
            'trans_id' =>  $apiLogEntry->getTransId(),
            'pay_id' =>$apiLogEntry->getPayId(),
            'x_id' =>   $apiLogEntry->getXId(),
        ]);
        return $apiLogEntry->save();
    }
}
