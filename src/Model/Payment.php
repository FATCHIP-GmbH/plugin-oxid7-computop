<?php

namespace Fatchip\ComputopPayments\Model;

use Fatchip\ComputopPayments\Helper\Payment as PaymentHelper;

class Payment extends Payment_parent
{
    /**
     * @return bool
     */
    public function isComputopPaymentMethod()
    {
        if (PaymentHelper::getInstance()->isComputopPaymentMethod($this->getId())) {
            return true;
        }
        return false;
    }

    /**
     * @return Method\BaseMethod|false
     * @throws \Exception
     */
    public function computopGetPaymentModel()
    {
        if ($this->isComputopPaymentMethod() === true) {
            return PaymentHelper::getInstance()->getComputopPaymentModel($this->getId());
        }
        return false;
    }
}