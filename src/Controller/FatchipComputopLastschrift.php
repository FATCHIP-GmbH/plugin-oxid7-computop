<?php

namespace Fatchip\ComputopPayments\Controller;

use Exception;
use Fatchip\ComputopPayments\Core\Config;
use Fatchip\CTPayment\CTPaymentService;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class DispatchController
 *
 */
class FatchipComputopLastschrift extends FatchipComputopPayments
{
    protected $fatchipComputopConfig;
    protected $fatchipComputopShopConfig;
    protected $fatchipComputopShopUtils;
    protected $paymentService;
    protected $paymentClass = 'Lastschrift';

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function init()
    {
        parent::init();
    }
}


