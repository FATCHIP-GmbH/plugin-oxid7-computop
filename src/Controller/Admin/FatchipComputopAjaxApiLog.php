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

namespace Fatchip\ComputopPayments\Controller\Admin;

use Doctrine\DBAL\Query\QueryBuilder; // only used for IDE autocomplete
use Fatchip\ComputopPayments\Core\Constants;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Controller\Admin\AdminController;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

/**
 * handles amazon checkout ajax calls
 */
class FatchipComputopAjaxApiLog extends AdminController
{
    public function render()
    {
        $offset = Registry::getRequest()->getRequestEscapedParameter('offset');
        $limit = Registry::getRequest()->getRequestEscapedParameter('limit');
        $APILogEntries = $this->getAPILogs($offset, $limit);
        $container = ContainerFactory::getInstance()->getContainer();
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $count =         $queryBuilder
            ->select('*')
            ->from(Constants::APILOG_TABLE)
            ->where('1')->execute()->rowCount();

        // $response = json_encode(['total' => $count, 'rows' => $APILogEntries]);
        $response = json_encode( $APILogEntries);
        $this->addTplParam('jsonResponse', $response);
        return '@fatchip_computop_payments/admin/' . Constants::TEMPLATE_PREFIX . 'json';
    }

    protected function getAPILogs($offset, $limit)
    {
        // $reflection = new \ReflectionClass(CTPaymentConfigForms::class);
        // $config = $reflection->getConstants();
        $container = ContainerFactory::getInstance()->getContainer();
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);

        /** @var  $queryBuilder  QueryBuilder */
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder
            ->select('*')
            ->from(Constants::APILOG_TABLE)
            ->where('1');
//            ->andWhere('oxblockname = :name')
//            ->setParameters([
//                'shopId'    => $shopId,
//                'name'      => $name,
//            ]);

        $APILogEntries = $queryBuilder->execute();
        $apiLogs = $APILogEntries->fetchAll();
        $apiLogs = $this->addArrayRequestResponse($apiLogs);

        return $apiLogs;
    }

    protected function addArrayRequestResponse($result)
    {
        if (!empty($result)) {
            foreach ($result as $key => $entry) {
                $request = '';
                $response = '';
                $dataRequest = json_decode($entry['request_details'], true);
                $dataResponse = json_decode($entry['response_details'], true);
                foreach ($dataRequest AS $dataKey => $dataValue) {
                    $request .= $dataKey . '=' . $dataValue . '<BR>';
                }
                foreach ($dataResponse AS $dataKey => $dataValue) {
                    $response .= $dataKey . '=' . $dataValue . '<BR>';
                }
                $result[$key]['requestArray'] = $request;
                $result[$key]['responseArray'] = $response;
            }
        }
        return $result;
    }


}
