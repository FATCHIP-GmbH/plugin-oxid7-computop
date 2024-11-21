<?php
/** @noinspection PhpUnused */

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Shopware Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Shopware Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5.6, 7.0 , 7.1
 *
 * @category   Payment
 * @package    FatchipCTPayment
 * @subpackage CTPaymentMethodsIframe
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */
namespace Fatchip\CTPayment\CTPaymentMethodsIframe;

use Fatchip\CTPayment\CTOrder\CTOrder;
use Fatchip\CTPayment\CTPaymentMethodIframe;

/**
 * Class Lastschrift
 * @package Fatchip\CTPayment\CTPaymentMethodsIframe
 */
abstract class Lastschrift extends CTPaymentMethodIframe
{

  /**
   * Name der XSLT-Datei mit Ihrem individuellen Layout für das Bezahlformular.
   * Wenn Sie das neugestaltete und abwärtskompatible Computop-template nut-zen möchten,
   * übergeben Sie den Templatenamen „ct_compatible“.
   * Wenn Sie das Responsive Computop-template für mobile Endgeräte nutzen möchten,
   * übergeben Sie den Templatenamen „ct_responsive“.
   *
   * @var string
   */
    protected $Template;

    /**
     * Bestimmt Art und Zeitpunkt der Buchung (engl. Capture).
     * AUTO: Buchung so-fort nach Autorisierung (Standardwert).
     * MANUAL: Buchung erfolgt durch den Händler.
     * <Zahl>: Verzögerung in Stunden bis zur Buchung (ganze Zahl; 1 bis 696).
     *
     * @var string
     */
    protected $Capture; //AUTO, MANUAL, ZAHL

    /**
     * Über welchen Dienst wird Lastschrift angebunden`:
     * Direktanbindung
     * EVO Payments
     * Intercard
     *
     * @var
     */
    protected $dienst;

    /**
     * für SEPA: SEPA-Mandatsnummer (Pflicht bei SEPA) sollte eindeutig sein, ist nicht case-sensitive
     *
     * @var string
     */
    protected $MandateID;

    /**
     * für SEPA: Gibt an, ob es sich um eine Erst-, Folge-, Einmal- oder letztmalige
     * Lastschrift handelt.
     * Zulässige Werte: FRST, RCUR, OOFF, FNAL
     * Standard: OOFF (Einmal-Lastschrift)
     *
     * @var string
     */
    protected $MdtSeqType;

    /**
     * für SEPA: Datum der Mandatserteilung im Format DD.MM.YYYY
     * Pflicht bei Übergabe von MandateID
     *
     * @var string
     */
    protected $DtOfSgntr;

    /**
     * Bezeichnung Bank
     * @var string
     */
    protected $AccBank;

    /**
     * Kontoinhaber
     * @var string
     */
    protected $AccOwner;

    /**
     * International Bank Account Number
     *
     * @var string
     */
    protected $IBAN;



    /**
     * Lastschrift constructor
     *
     * @param array $config
     * @param CTOrder|null $order
     * @param null|string $urlSuccess
     * @param null|string $urlFailure
     * @param $urlNotify
     * @param $orderDesc
     * @param $userData
     * @param $capture
     */

    public function __construct(
        $config,
        $order,
        $urlSuccess,
        $urlFailure,
        $urlNotify,
        $orderDesc,
        $userData,
        /** @noinspection PhpUnusedParameterInspection */
        $capture = 'MANUAL'
    ) {
        parent::__construct($config, $order, $orderDesc, $userData);

        $this->setUrlSuccess($urlSuccess);
        $this->setUrlFailure($urlFailure);
        $this->setUrlNotify($urlNotify);
        $this->setMandateID($this->createMandateID($order->getAmount()));

        $this->setCapture('MANUAL');
    }

    /**
     * @ignore <description>
     * @param string $capture
     */
    public function setCapture($capture)
    {
        $this->Capture = $capture;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCapture()
    {
        return $this->Capture;
    }

    /**
     * @ignore <description>
     * @param mixed $dienst
     */
    public function setDienst($dienst)
    {
        $this->dienst = $dienst;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getDienst()
    {
        return $this->dienst;
    }

    /**
     * @ignore <description>
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->Template = $template;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getTemplate()
    {
        return $this->Template;
    }

    /**
     * @ignore <description>
     * @param string $mandateID
     */
    public function setMandateID($mandateID)
    {
        $this->MandateID = $mandateID;
        //if we set MandateID, also dtOfSgntr is obligatory
        $this->setDtOfSgntr(date('d-m-Y'));
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getMandateID()
    {
        return $this->MandateID;
    }

    /**
     * @param $MdtSeqType
     * @ignore <description>
     */
    public function setMdtSeqType($MdtSeqType)
    {
        $this->MdtSeqType = $MdtSeqType;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getMdtSeqType()
    {
        return $this->MdtSeqType;
    }

    /**
     * @ignore <description>
     * @param string $dtOfSgntr
     */
    public function setDtOfSgntr($dtOfSgntr)
    {
        $this->DtOfSgntr = $dtOfSgntr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getDtOfSgntr()
    {
        return $this->DtOfSgntr;
    }

    /**
     * @ignore <description>
     * @param string $AccBank
     */
    public function setAccBank($AccBank) {
        $this->AccBank = $AccBank;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAccBank() {
        return $this->AccBank;
    }

    /**
     * @ignore <description>
     * @param string $AccOwner
     */
    public function setAccOwner($AccOwner) {
        $this->AccOwner = $AccOwner;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAccOwner() {
        return $this->AccOwner;
    }

    /**
     * @ignore <description>
     * @param string $IBAN
     */
    public function setIBAN($IBAN) {
        $this->IBAN = $IBAN;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getIBAN() {
        return $this->IBAN;
    }

    /**
     * Each ELV payment needs a unique mandateID.
     * For now, it is the ordernumber plus date
     * @param $orderID
     * @return string
     */
    public function createMandateID($orderID)
    {
        return $orderID . date('yzGis');
    }

    /**
     * returns the paymentURL
     * @return string
     */
    public function getCTPaymentURL()
    {
        return 'https://www.computop-paygate.com/edddirect.aspx';
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
