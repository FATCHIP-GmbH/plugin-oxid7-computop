<?php

namespace Fatchip\ComputopPayments\Controller\Admin;

use Fatchip\ComputopPayments\Model\ApiLog;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;

class FatchipComputopApiLogMain extends AdminDetailsController
{
    /**
     * Current class template name
     *
     * @var string
     */
    protected $_sThisTemplate = '@fatchip_computop_payments/admin/fatchip_computop_apilog_main';

    /**
     * @var RequestLog
     */
    protected $_oRequestLog = null;

    /**
     * Parameter for $this->getObjectData($sData) for EditObject
     */
    protected const EDIT = 'edit';

    /**
     * Parameter for $this->getObjectData($sData) for ResponseData
     */
    protected const RESPONSE = 'response';

    /**
     * Parameter for $this->getObjectData($sData) for RequestData
     */
    protected const REQUEST = 'request';
    protected const REQUESTDETAILS = 'request_details';
    protected const RESPONSEDETAILS = 'response_details';

    /**
     * @return string
     */
    public function render()
    {
        $sOxid = $this->getEditObjectId();
        if ($sOxid != '-1') {
            $this->_oRequestLog = oxNew(ApiLog::class);
            $this->_oRequestLog->load($sOxid);
        }
        $return =  parent::render();
        $this->addTplParam('sHelpURL','https://developer.computop.com/');
        return $return;

    }

    /**
     * Getter for TPL
     *
     * @return false
     */
    public function getRequest()
    {
        return $this->getObjectData(self::REQUEST);
    }

    /**
     * Getter for TPL
     *
     * @return false
     */
    public function getRequestDetails()
    {
        return $this->getObjectData(self::REQUESTDETAILS);
    }

    /**
     * Getter for TPL
     *
     * @return false
     */
    public function getResponseDetails()
    {
        return $this->getObjectData(self::RESPONSEDETAILS);
    }

    /**
     * Getter for TPL
     *
     * @return false
     */
    public function getResponse()
    {
        return $this->getObjectData(self::RESPONSE);
    }

    /**
     * Getter for TPL
     *
     * @return RequestLog $this->_oRequestLog
     * @return false
     */
    public function getEdit()
    {
        return $this->getObjectData(self::EDIT);
    }

    /**
     * Function for the TPL-Getter
     *
     * @param $sData
     * @return false
     */
    private function getObjectData($sData)
    {
        if ($this->_oRequestLog) {
            if ($sData == 'edit') {
                return $this->_oRequestLog;
            }
            $rawValue = $this->_oRequestLog->{"fatchip_computop_api_log__$sData"}->rawValue;
            if (!empty($rawValue && $rawValue !== '[]')) {
                return $rawValue;
            }
        }
        return false;
    }
}
