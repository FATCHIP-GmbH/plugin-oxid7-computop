<?php

namespace Fatchip\ComputopPayments\Model\Method\Ratepay;

use OxidEsales\Eshop\Application\Model\Order;

class DirectDebit extends Base
{
    const ID = "fatchip_computop_ratepay_debit";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'RatepayDirectDebit';

    /**
     * @var string|false
     */
    protected $customFrontendTemplate = 'fatchip_computop_ratepay_directdebit.html.twig';

    /**
     * @var string|null
     */
    protected $rpMethod = Base::METHOD_DIRECT_DEBIT;

    /**
     * @var string|null
     */
    protected $rpDebitPayType = Base::DEBIT_PAY_TYPE_DIRECT_DEBIT;

    /**
     * Return parameters specific to this payment subtype
     *
     * @param  Order $order
     * @param  array $dynValue
     * @return array
     */
    public function getSubTypeSpecificParameters(Order $order, $dynValue)
    {
        $return = parent::getSubTypeSpecificParameters($order, $dynValue);

        $return['AccOwner'] = $this->getDynValue($dynValue, 'accountholder');
        $return['IBAN'] = $this->getDynValue($dynValue, 'iban');

        if (!empty($this->getDynValue($dynValue, 'bic'))) {
            $return['BIC'] = $this->getDynValue($dynValue, 'bic');
        }
        return $return;
    }
}