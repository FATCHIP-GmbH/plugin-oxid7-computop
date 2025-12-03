<?php

namespace Fatchip\ComputopPayments\Model\Method\Ratepay;

class Invoice extends Base
{
    const ID = "fatchip_computop_ratepay_invoice";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'RatepayInvoice';

    /**
     * @var string|false
     */
    protected $customFrontendTemplate = 'fatchip_computop_ratepay_invoice.html.twig';

    /**
     * @var string|null
     */
    protected $rpMethod = Base::METHOD_INVOICE;

    /**
     * @var string|null
     */
    protected $rpDebitPayType = Base::DEBIT_PAY_TYPE_BANK_TRANSFER;
}