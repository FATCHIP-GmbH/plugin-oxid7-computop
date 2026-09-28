<?php

namespace Fatchip\ComputopPayments\Model;

use Fatchip\ComputopPayments\Core\Cronjob\FinalizeOrder;

class BasketItem extends BasketItem_parent
{
    /**
     * @return void
     */
    public function computopUnsetArticle()
    {
        $this->_oArticle = null;
    }

    /**
     * @return \OxidEsales\Eshop\Application\Model\Article
     */
    public function getArticle($blCheckProduct = false, $sProductId = null, $blDisableLazyLoading = false)
    {
        if (FinalizeOrder::isIsFinalizingOrder() === true) {
            $blCheckProduct = false;
        }
        return parent::getArticle($blCheckProduct, $sProductId, $blDisableLazyLoading);
    }
}
