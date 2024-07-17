<?php

namespace Fatchip\ComputopPayments\Controller\Admin;
use Fatchip\ComputopPayments\Model\ApiLog;
use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;



class FatchipComputopApiLogList extends AdminListController
{
    /**
     * Name of chosen object class (default null).
     * @var string
     */
    protected $_sListClass = ApiLog::class;

    /**
     * Enable/disable sorting by DESC (SQL) (default false - disable).
     * @var bool
     */
    protected $_blDesc = true;
    protected $_sDefSortField = 'creation_date';

    /**
     * Default SQL sorting parameter (default null).
     * @var string
     */
    //protected $_sDefSortField = "timestamp";

    /**
     * Current class template name
     * @var string
     */
    protected $_sThisTemplate = '@fatchip_computop_payments/admin/fatchip_computop_apilog_list';
}
