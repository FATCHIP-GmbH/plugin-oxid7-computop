<?php

namespace Fatchip\ComputopPayments\Model\Api\Request;

class Inquire extends Base
{
    /**
     * Defines request type to be seen in API Log
     *
     * @var string
     */
    protected $requestType = "INQUIRE";

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "inquire.aspx";

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpointByTransId = "inquire24.aspx";

    /**
     * Inquire payment status from Computop API
     *
     * @return array
     */
    public function getPaymentStatus($payId, $transId)
    {
        $params = [
            'PayId' => $payId,
            'TransID' => $transId,
        ];
        return $this->handleStandardCurlRequest($params);
    }

    /**
     * Inquire payment status from Computop API by only TransID
     *
     * @return array
     */
    public function getPaymentStatusByTransId($transId)
    {
        $params = [
            'TransID' => $transId,
        ];

        $tmpEndpoint = $this->apiEndpoint;
        $this->apiEndpoint = $this->apiEndpointByTransId;

        $response = $this->handleStandardCurlRequest($params);

        $this->apiEndpoint = $tmpEndpoint;

        return $response;
    }
}
