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
 * @subpackage Admin_FatchipComputopConfig
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop UpdateIdealIssuers
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Core;

use Exception;
use Fatchip\ComputopPayments\Model\ApiLog;
use Fatchip\ComputopPayments\Repository\ApiLogRepository;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogLogger;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger
{
    /**
     * @var ApiLogRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $logFileName;

    public function __construct(string $logFileName = 'fatchip_computop_payments.log')
    {
        $this->logFileName = $logFileName;
        $this->repository = oxNew(ApiLogRepository::class);
    }

    public function logRequestResponse($requestParams, $paymentName, $requestType, $response)
    {
        if ($paymentName === '' || $paymentName === null) {
           $paymentName = Registry::getSession()->getVariable('paymentid');
           $paymentName = Constants::getPaymentClassfromId($paymentName);
         }
        $logMessage = new ApiLog();
        $logMessage->setPaymentName($paymentName);
        $logMessage->setCreationDate(date('Y-m-d H-i-s'));
        $logMessage->setRequest($requestType);
        $logMessage->setRequestDetails(json_encode($requestParams));
        $logMessage->setTransId($response->getTransID());
        $logMessage->setPayId($response->getPayID());
        $logMessage->setXId($response->getXID());
        $logMessage->setResponse($response->getStatus());
        $logMessage->setResponseDetails(json_encode($response->toArray()));

        $this->repository->saveApiLog($logMessage);
    }

    /**
     * @return ApiLogRepository
     */
    public function getRepository(): ApiLogRepository
    {
        return $this->repository;
    }

    /**
     * @param int $log_level
     * @return MonoLogLogger
     * @throws Exception
     */
    private function getLogger(int $log_level): LoggerInterface
    {
        $logger = new MonoLogLogger($this->logFileName);
        $logger->pushHandler(
            new StreamHandler(
                Registry::getConfig()->getLogsDir() . $this->logFileName,
                $log_level
            )
        );

        return $logger;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function log($level, $message, array $context = [])
    {
        $levelName = MonoLogLogger::getLevels()[strtoupper($level)];
        $this->getLogger($levelName)->addRecord($levelName, $message, $context);
    }
}
