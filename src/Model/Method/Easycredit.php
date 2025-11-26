<?php

namespace Fatchip\ComputopPayments\Model\Method;

use Fatchip\CTPayment\CTEnums\CTEnumEasyCredit;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class Easycredit extends ServerToServerPayment
{
    const ID = "fatchip_computop_easycredit";

    /**
     * @var string
     */
    protected $oxidPaymentId = self::ID;

    /**
     * @var string
     */
    protected $libClassName = 'EasyCredit';

    /**
     * Defines where API requests are sent to at the Comutop API
     *
     * @var string
     */
    protected $apiEndpoint = "easyCredit.aspx";

    /**
     * Determines if auth requests adds billing address parameters to the request
     *
     * @var bool
     */
    protected $addBillingAddressData = true;

    /**
     * Determines if auth requests adds shipping address parameters to the request
     *
     * @var bool
     */
    protected $addShippingAddressData = true;

    /**
     * @var string|false
     */
    protected $customFrontendTemplate = 'fatchip_computop_easycredit.html.twig';

    /**
     * Return parameters specific to this payment type
     *
     * @param  Order|null $order
     * @return array
     */
    public function getPaymentSpecificParameters(?Order $order, $dynValue, $ctOrder = false)
    {
        $params = [];

        $session = Registry::getSession();
        if (!empty($session->getVariable('fatchip_computop_TransId'))) {
            $decisionPayId = $session->getVariable('fatchipComputopEasyCreditPayId');

            $params['EventToken'] = CTEnumEasyCredit::EVENTTOKEN_CON;
            $params['payID'] = $decisionPayId->getPayID();
        } else {
            $params['EventToken'] = CTEnumEasyCredit::EVENTTOKEN_INIT;
            $params['DateOfBirth'] = $dynValue['fatchip_computop_easycredit_birthdate_year']. '-' . $dynValue['fatchip_computop_easycredit_birthdate_month'] . '-' . $dynValue['fatchip_computop_easycredit_birthdate_day'];
        }
        return $params;
    }
}