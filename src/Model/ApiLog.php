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
 * along with Computop Oxid Plugin. If not, see <http://www.gnu.org/licenses/>.
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

namespace Fatchip\ComputopPayments\Model;

use Fatchip\ComputopPayments\Core\Constants;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class ApiLog extends BaseModel
{
    public static $sTableName = "fatchip_computop_api_log";

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->init(self::$sTableName);
    }

  /**
   * Id of the Log entry
   *
   * @var integer $oxid
   *
   * @ORM\Column(name="id", type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
    private $oxid;

  /**
   * will be used to save the type of the request to computop
   * so we can easily filter
   * "CreditCard" "EasyCredit"
   *
   * @ORM\Column(name="request", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
   */
    private $request;

  /**
   *  response status field "OK" or "Error"
   * @ORM\Column(name="response", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
   */
    private $response;

   /**
    * Date the entry was created
   * @ORM\Column(name="creation_date", type="datetime", precision=0, scale=0, nullable=false, unique=false)
   */
    private $creationDate;

    /**
     * Paymentname
     * @ORM\Column(name="payment_name", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $paymentName;


    /**
     * Contains all details of the request
   * @ORM\Column(name="request_details", type="array", precision=0, scale=0, nullable=true, unique=false)
   */
    private $requestDetails;

  /**
   * Contains all details of the response
   * @ORM\Column(name="response_details", type="array", precision=0, scale=0, nullable=true, unique=false)
   */
    private $responseDetails;

    /**
     * TransactionsID
     * @var string $transId
     * @ORM\Column(name="trans_id", length=255, type="string", nullable=true)
     */
    private $transId;

    /**
     * PayID
     * @var string $PayId
     * @ORM\Column(name="pay_id", length=255, type="string", nullable=true)
     */
    private $payId;

    /**
     * XID
     * @var string $x
     * @ORM\Column(name="x_id", length=255, type="string", nullable=true)
     */
    private $xId;


    /**
     * automatically insert timestamp
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->creationDate = new \DateTime();
    }


    /**
     * @ignore <description>
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @ignore <description>
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @ignore <description>
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * @ignore <description>
     * @param mixed $paymentName
     */
    public function setPaymentName($paymentName)
    {
        $this->paymentName = $paymentName;
    }


    /**
     * @ignore <description>
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @ignore <description>
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getRequestDetails()
    {
        return $this->requestDetails;
    }

    /**
     * @ignore <description>
     * @param mixed $requestDetails
     */
    public function setRequestDetails($requestDetails)
    {
        $this->requestDetails = $requestDetails;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getResponseDetails()
    {
        return $this->responseDetails;
    }

    /**
     * @ignore <description>
     * @param mixed $responseDetails
     */
    public function setResponseDetails($responseDetails)
    {
        $this->responseDetails = $responseDetails;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getTransId()
    {
        return $this->transId;
    }

    /**
     * @ignore <description>
     * @param string $transId
     */
    public function setTransId($transId)
    {
        $this->transId = $transId;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getPayId()
    {
        return $this->payId;
    }

    /**
     * @ignore <description>
     * @param string $payId
     */
    public function setPayId($payId)
    {
        $this->payId = $payId;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getXId()
    {
        return $this->xId;
    }

    /**
     * @ignore <description>
     * @param string $xId
     */
    public function setXId($xId)
    {
        $this->xId = $xId;
    }

    public function loadByTransId($transId)
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $builder = $queryBuilder
            ->select('oxid')
            ->from(Constants::APILOG_TABLE)
            ->where('trans_id = :transid')->setParameter('transid', $transId);
        $result = $builder->execute()->fetchOne();
        if ($result !== false) {
            return $this->load($result);
        }
        return false;
    }
}
