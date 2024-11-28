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
 * PHP version 5.6, 7.0 , 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @subpackage CTPaymentMethods
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\CTPayment\CTHelper;

use Exception;
use Fatchip\CTPayment\CTOrder\CTOrder;
use OxidEsales\Eshop\Core\Registry;


/**
 * @package Fatchip\CTPayment\CTPaymentMethods
 * @property Util $utils
 */
trait KlarnaPayments
{

    protected $billToCustomer;

    public function needNewKlarnaSession()
    {
        /** @var CTOrder $ctOrder */
        $ctOrder = $this->utils->createCTOrder();
        /** @var \Fatchip\CTPayment\CTPaymentMethods\KlarnaPayments $payment */
        $session = Registry::getSession();

        $sessionAmount = $session->get('FatchipCTKlarnaPaymentAmount', '');
        $currentAmount = $ctOrder->getAmount();
        $amountChanged = $currentAmount !== $sessionAmount;

        $sessionArticleListBase64 = $session->get('FatchipCTKlarnaPaymentArticleListBase64', '');
        $currentArticleListBase64 = $this->createArticleListBase64();
        $articleListChanged = $sessionArticleListBase64 !== $currentArticleListBase64;

        $sessionAddressHash = $session->get('FatchipCTKlarnaPaymentAddressHash', '');
        $currentAddressHash = $this->createAddressHash();
        $addressChanged = $sessionAddressHash !== $currentAddressHash;

        $sessionDispatch = $session->get('FatchipCTKlarnaPaymentDispatchID', '');
        $currentDispatch = $session->offsetGet('sDispatch');
        $dispatchChanged = $sessionDispatch != $currentDispatch;

        return !$session->offsetExists('FatchipCTKlarnaAccessToken')
            || $amountChanged
            || $articleListChanged
            || $addressChanged
            || $dispatchChanged;
    }

    /**
     * Calculates the Klarna tax amount by adding the tax amounts of each position in the article list.
     *
     * @param $articleList
     *
     * @return float
     */
    public static function calculateTaxAmount($articleList)
    {
        $taxAmount = 0;
        $articleList = json_decode(base64_decode($articleList), true);
        foreach ($articleList['order_lines'] as $article) {
            $itemTaxAmount = $article['total_tax_amount'];
            $taxAmount += $itemTaxAmount;
        }

        return $taxAmount;
    }



    /**
     * @param int $digitCount Optional parameter for the length of resulting
     *                        transID. The default value is 12.
     *
     * @return string The transID with a length of $digitCount.
     */
    public static function generateTransID($digitCount = 12)
    {
        mt_srand((double)microtime() * 1000000);

        $transID = (string)mt_rand();
        // y: 2 digits for year
        // m: 2 digits for month
        // d: 2 digits for day of month
        // H: 2 digits for hour
        // i: 2 digits for minute
        // s: 2 digits for second
        $transID .= date('ymdHis');
        // $transID = md5($transID);
        $transID = substr($transID, 0, $digitCount);

        return $transID;
    }



    public function getBillToCustomer()
    {
        return $this->billToCustomer;
    }

    public function setBillToCustomer($ctOrder)
    {
        #$customer['consumer']['salutation'] = $ctOrder->getBillingAddress()->getSalutation();
        $customer['consumer']['firstName'] = $ctOrder->getBillingAddress()->getFirstName();
        $customer['consumer']['lastName'] = $ctOrder->getBillingAddress()->getLastName();
        $customer['email'] = $ctOrder->getEmail();
        $this->billToCustomer = base64_encode(json_encode($customer));
    }
}
