<?php /** @noinspection PhpUnused */

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
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2018 Computop
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */
namespace Fatchip\CTPayment;
/**
 * Class CTResponse
 * @package Fatchip\CTPayment
 */
class CTResponse
{

    /**
     * OK oder AUTHORIZED (URLSuccess) sowie FAILED (URLFailure)
     *
     * @var string
     */
    protected $status = null;

    /**
     * Nähere Beschreibung bei Ablehnung der Zahlung.
     * Bitte nutzen Sie nicht den Parameter description sondern code für die Auswertung des Transaktionssta-tus!
     *
     * @var string
     */
    protected $Description = null;


    /**
     * Fehlercode gemäß Excel-Datei Paygate Antwort Codes
     *
     * @var int
     */
    protected $Code = null;


    /**
     * Hash Message Authentication code (HMAC) mit SHA-256-Algorithmus
     *
     * @var string
     */
    protected $MAC = null;


    /**
     * Wenn beim Aufruf angegeben, übergibt das Paygate die Parameter mit dem Zahlungsergebnis an den Shop
     *
     * @var string
     */
    protected $UserData;
    /**
     * HändlerID, die von Computop vergeben wird
     *
     * @var string
     */
    protected $MID = null;
    /**
     * Vom Paygate vergebene ID für die Zahlung
     *
     * @var string
     */
    protected $PayID = null;
    /**
     * Vom Paygate vergebene ID für alle einzelnen Transaktionen (Autorisierung, Bu-chung, Gutschrift),
     * die für eine Zahlung durchgeführt werden
     *
     * @var string
     */
    protected $XID = null;
    /**
     * Transaktionsnummer des Händlers
     *
     * @var string
     */
    protected $TransID = null;
    /**
     * Bei 3D Secure nur in der Antwort an URLNotify: Kürzel zur Typisierung der Zah-lung, z.B. SSL
     *
     * @var string
     */
    protected $Type;
    /**
     * Pseudo Card Number: Vom Paygate generierte Zufallszahl, die eine reale Kre-ditkartennummer repräsentiert.
     * Die Pseudokartennummer (PKN) beginnt mit 0, und die letzten 3 Stellen entsprechen denen der realen Kartennummer.
     * Die PKN können Sie wie eine reale Kartennummer für Autorisierung, Buchung und Gutschriften verwenden
     *
     * @var int
     */
    protected $PCNr;
    /**
     * In Verbindung mit pcNr: Ablaufdatum der Kreditkarte im Format YYYYMM
     *
     * @var int
     */
    protected $CCExpiry;
    /**
     * In Verbindung mit pcNr: Bezeichnung der Kreditkartenmarke
     * Bitte beachten Sie die Schreibweise gemäß Tabelle der Kreditkartenmarken!
     *
     * @var string
     */
    protected $CCBrand;
    /**
     * Decision
     * @var string
     */
    protected $Decision;
    /**
     * Easycredit: Enthält allgemeine Vorgansdaten zur vorherigen Initialisie-rung. Diese werden im JSON-Format und Base64-encodiert zurückgegeben
     * @var string
     */
    protected $Process;
    /**
     * Easycredit: Enthält Finanzierungsdaten zur vorherigen Initialisierung. Diese werden im JSON-Format und Base64-encodiert zu-rückgegeben.
     * @var string
     */
    protected $Financing;
    /**
     * Eindeutige Referenznummer (Optional)
     *
     * @var string
     */
    protected $RefNr;

    /**
     * Klarna
     * Reservierungsnummer: wird bei Reservierung (Autorisierung) zurückgegeben
     *
     * @var string
     */
    protected $RNo;
    /**
     * Klarna
     * Rechnungsnummer: wird bei Aktivierung (Capture) zurückgegeben. Längste In-vNo bisher war 17-stellig.
     * Über folgende URL können Sie sich Packing Slips bei Klarna herunterladen:
     * https://online.klarna.com/packslips/{InvNo}.pdf
     *
     * @var string
     */
    protected $InvNo;
    /**
     * for Lastschrift
     * @var
     */
    protected $IBAN;
    /**
     * for Lastschrift
     * @var
     */
    protected $BIC;
    /**
     * for Lastschrift
     * @var
     */
    protected $AccOwner;
    /**
     * for Lastschrift
     * @var
     */
    protected $Mandateid;
    /**
     * for Lastschrift
     * @var
     */
    protected $Dtofsgntr;
    /**
     * for Lastschrift
     * @var
     */
    protected $Mdtseqtype;
    /**
     * Eindeutige ID des Vorgangs
     *
     * @var string
     */
    protected $Reference;
    /**
     * Vorname in der Lieferanschrift
     *
     * @var string
     */
    protected $sdFirstName;
    /**
     * Nachname in der Lieferanschrift
     *
     * @var string
     */
    protected $sdLastName;
    /**
     * Straßenname in der Lieferanschrift
     *
     * @var string
     */
    protected $sdStreet;
    /**
     * Hausnummer in der Lieferanschrift
     *
     * @var string
     */
    protected $sdStreetNr;
    /**
     * Postleitzahl in der Lieferanschrift
     *
     * @var string
     */
    protected $sdZip;
    /**
     * Ort in der Lieferanschrift
     *
     * @var string
     */
    protected $sdCity;
    /**
     * Ländercode in der Lieferanschrift
     *
     * @var string
     */
    protected $sdCountryCode;
    /**
     * E-Mail-Adresse des Empfängers
     *
     * @var string
     */
    protected $sdEmail;
    /**
     * Diese ID ist vorhanden, sobald sich ein Kunde auf der Checkout-Seite eingeloggt hat.
     *
     * @var string
     */
    protected $TID;
    /**
     * Eindeutige Transaktionsnummer bei PayPal
     *
     * @var string
     */
    protected $TransactionID;
    /**
     * Nachricht an den Händler
     *
     * @var string
     */
    protected $InfoText;
    /**
     * Vorname und Nachname zusammengenommen
     *
     * @var string
     */
    protected $name;
    /**
     * Vorname vom Bezahler (PayerInfo, kann vom Account-Namen abweichen)
     *
     * @var string
     */
    protected $FirstName;
    /**
     * Nachname vom Bezahler (PayerInfo, kann vom Account-Namen abweichen)
     *
     * @var string
     */
    protected $LastName;
    /**
     * E-Mail-Adresse des Käufers.
     *
     * @var string
     */
    protected $EMail;
    /**
     * Straßenname der Lieferadresse
     *
     * @var string
     */
    protected $AddrStreet;
    /**
     * 2. Straßenname der Lieferadresse, wenn mit Computop abgestimmt
     *
     * @var string
     */
    protected $AddrStreet2;
    /**
     * Ortsname der Lieferadresse
     *
     * @var string
     */
    protected $AddrCity;
    /**
     * Bundesland (Bundesstaat) der Lieferadresse
     *
     * @var string
     */
    protected $AddrState;
    /**
     * Postleitzahl der Lieferadresse
     *
     * @var string
     */
    protected $AddrZIP;
    /**
     * Ländercode des Lieferlandes
     *
     * @var string
     */
    protected $AddrCountryCode;
    /**
     * Identifikationsnummer der Rechnungsvereinbarung. Wenn der Käufer die Rech-nungsvereinbarung bestätigt,
     * wird sie gültig und bleibt gültig, bis sie vom Käufer widerrufen wird.
     *
     * @var string
     */
    protected $BillingAgreementiD;
    /**
     * Postfinance
     *
     * Zahlungszweck
     *
     * @var string
     */
    protected $PaymentPurpose;
    /**
     * Postfinance
     * Dieser Parameter wird nur zurückgegeben, falls der Status=OK ist.
     *
     * NONE = keine Zahlungsgarantie
     * VALIDATED = Kundenkonto valide, aber keine Zahlungsgarantie
     * FULL = Zahlungsgarantie Hinweis:
     *
     * @var string
     */
    protected $PaymentGuarantee;
    /**
     * Postfinance
     * Name des Kontoinhabers
     *
     * @var string
     */
    protected $AccNr;
    /**
     * IBAN
     * @var string
     */
    protected $AccIBAN;
    /**
     * Bank
     * @var string
     */
    protected $AccBank;
    /**
     * Geldeingang laut Sofort
     * <1> gewährleistet,
     * <0> nicht gewährleistet.
     * Bitte war-ten Sie bei 0 auf den Geldeingang, bevor Sie die Ware verschicken.
     *
     * @var int
     */
    protected $SecCriteria;
    /**
     * nur bei Sofort Ident: Geburtsdatum
     *
     * @var datetime
     */
    protected $Birthday;
    /**
     * nur im Erfolgsfall bei Sofort Ident: Alter
     *
     * @var int
     */
    protected $Age;
    /**
     * Streetnumber
     * @var string
     */
    protected $AddrStreetNr;
    /**
     *
     * Handlungsempfehlung (Ampel): GREEN, YELLOW, RED, NO RESULT
     *
     * @var string
     */
    protected $result;
    /**
     * Einzelne detailliertere Teilprüfungen
     * @var
     */
    protected $partialResults;
    /**
     * Inquire: amount authorized for order
     * @var AmountAuth
     */
    protected $AmountAuth;
    /**
     * Inquire: Amount Captured for order
     * @var AmountCap
     */
    protected $AmountCap;
    /**
     * Inquire: Amount credited for order
     *
     * @var AmountCred
     */
    protected $AmountCred;


    // Used by AmazonPay
    /**
     * Teil der Benutzerprofil-Informationen. Eindeutige von Amazon pro User verge-bene ID.
     * @var string
     */
    protected $userid;

    /**
     * Teil der Benutzerprofil-Informationen. Benutzername
     * @var string
     */
    protected $buyername;

    /**
     * Teil der Benutzerprofil-Informationen. E-Mail-Adresse
     * @var string
     */
    protected $buyermail;

    /**
     * Von Amazon vergebene eindeutige ID für die Bestellung. Entspricht der Order-ReferenceID
     * @var string
     */
    protected $orderid;

    /**
     * Status der Bestellung bei Amazon. Folgende Werte sind möglich: „Draft“, „O-pen“, „Suspended“, „Canceled“, „Closed“.
     * @var string
     */
    protected $amazonstatus;

    /**
     * Vorname und Nachname des Adressaten
     * @var string
     */
    protected $addrname;

    /**
     * Telefonnummer des Adressaten
     * @var string
     */
    protected $phonenumber;

    /**
     * Straßenname mit Hausnummer der Rechnungsadresse
     * @var string
     */
    protected $bdaddrstreet2;

    /**
     * OOrtsname der Rechnungsadresse
     * @var string
     */
    protected $bdaddrcity;

    /**
     * Ländercode des Rechnungslandes gemäß ISO 3166
     * @var string
     */
    protected $bdaddrcountrycode;

    /**
     * Vorname und Nachname des Adressaten der Rechnungsadresse
     * @var string
     */
    protected $bdaddrname;

    /**
     * @var string
     * Postleitzahl der Rechnungsadresse
     */
    protected $bdaddrzip;

    // END AmazonPay

    /**
     * This token is for the widgets
     * @var string
     */
    protected $accesstoken;

    protected $sessionId;

    protected $shopTransId;

    /**
     * @see https://developer.computop.com/display/DE/schemeReferenceID
     * @see https://tickets.fatchip.de/view.php?id=80394
     *
     * Unique transaction ID for credit card payments
     *
     * @var string
     */
    protected $schemeReferenceID;

    protected $card;

    protected $CardHolder;

    protected $orderVars;

    // amazonpay
    protected $buttonsignature;

    protected $buttonpayload;

    protected     $buttonpublickeyid;
    protected     $stoken;
    protected $delAdress;

    /**
     * CTResponse constructor
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $key => $value) {
            $key = ucwords(str_replace('_', ' ', $key));
            $method = 'set' . str_replace(' ', '', str_replace('-', '', $key));

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }

    /**
     * Converts the response object into a json String
     *
     * @return string
     */
    public function __toString()
    {
        $return = json_encode($this->toArray(), 0);
        return ($return !== false) ? $return: '';
    }

    /**
     * Converts the response object into an array
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach ($this as $key => $data) {
            // @ S v.d.B : why check data here ?
            if ($data === null) {
                continue;
            } else {
                $result[$key] = $data;
            }
        }
        return $result;
    }

    /**
     * @ignore <description>
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @ignore <description>
     * @param int $code
     */
    public function setCode($code)
    {
        $this->Code = $code;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @ignore <description>
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->Description = $description;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @ignore <description>
     * @param string $mac
     */
    public function setMAC($mac)
    {
        $this->MAC = $mac;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getMAC()
    {
        return $this->MAC;
    }

    /**
     * @ignore <description>
     * @param string $userData
     */
    public function setUserData($userData)
    {
        $this->UserData = $userData;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getUserData()
    {
        return $this->UserData;
    }

    /**
     * @ignore <description>
     * @param string $mid
     */
    public function setMID($mid) {
        $this->MID = $mid;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getMID() {
        return $this->MID;
    }

    /**
     * @ignore <description>
     * @param string $payID
     */
    public function setPayID($payID) {
        $this->PayID = $payID;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getPayID() {
        return $this->PayID;
    }

    /**
     * @ignore <description>
     * @param string $xid
     */
    public function setXID($xid) {
        $this->XID = $xid;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getXID() {
        return $this->XID;
    }

    /**
     * @ignore <description>
     * @param string $transID
     */
    public function setTransID($transID) {
        $this->TransID = $transID;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getTransID() {
        return $this->TransID;
    }

    /**
     * @ignore <description>
     * @param string $ccBrand
     */
    public function setCCBrand($ccBrand)
    {
        $this->CCBrand = $ccBrand;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCCBrand() {
        return $this->CCBrand;
    }

    /**
     * @ignore <description>
     * @param int $ccExpiry
     */
    public function setCCExpiry($ccExpiry) {
        $this->CCExpiry = $ccExpiry;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getCCExpiry() {
        return $this->CCExpiry;
    }

    /**
     * @ignore <description>
     * @param int $pcNr
     */
    public function setPCNr($pcNr) {
        $this->PCNr = $pcNr;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getPCNr() {
        return $this->PCNr;
    }

    /**
     * @ignore <description>
     * @param string $type
     */
    public function setType($type) {
        $this->Type = $type;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getType() {
        return $this->Type;
    }

    /**
     * @ignore <description>
     * @param string $desicion
     */
    public function setDecision($desicion) {
        $this->Decision = $desicion;
    }

    /**
     * Enthält die Entscheidungsdaten zur vorherigen Initialisie-rung.
     * Diese werden im JSON-Format und Base64-encodiert zurückgegeben     *
     *
     * @return string
     */
    public function getDecision() {
        return base64_decode($this->Decision);
    }

    /**
     * @ignore <description>
     * @param string $financing
     */
    public function setFinancing($financing) {
        $this->Financing = $financing;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getFinancing() {
        return base64_decode($this->Financing);
    }

    /**
     * @ignore <description>
     * @param string $process
     */
    public function setProcess($process) {
        $this->Process = $process;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getProcess() {
        return base64_decode($this->Process);
    }

    /**
     * @ignore <description>
     * @param string $refNr
     */
    public function setRefNr($refNr) {
        $this->RefNr = $refNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getRefNr() {
        return $this->RefNr;
    }

    /**
     * @ignore <description>
     * for Lastschrift
     * @param mixed $accOwner
     */
    public function setAccOwner($accOwner) {
        $this->AccOwner = $accOwner;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAccOwner() {
        return $this->AccOwner;
    }

    /**
     * @ignore <description>
     * @param mixed $bic
     */
    public function setBIC($bic) {
        $this->BIC = $bic;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBIC() {
        return $this->BIC;
    }

    /**
     * @ignore <description>
     * @param mixed $dtofsgntr
     */
    public function setDtofsgntr($dtofsgntr) {
        $this->Dtofsgntr = $dtofsgntr;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getDtofsgntr() {
        return $this->Dtofsgntr;
    }

    /**
     * @ignore <description>
     * @param mixed $iban
     */
    public function setIBAN($iban) {
        $this->IBAN = $iban;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getIBAN() {
        return $this->IBAN;
    }

    /**
     * @ignore <description>
     * @param mixed $mandateId
     */
    public function setMandateid($mandateId) {
        $this->Mandateid = $mandateId;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getMandateid() {
        return $this->Mandateid;
    }

    /**
     * @ignore <description>
     * @param mixed $mdtseqtype
     */
    public function setMdtseqtype($mdtseqtype) {
        $this->Mdtseqtype = $mdtseqtype;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getMdtseqtype() {
        return $this->Mdtseqtype;
    }

    /**
     * @ignore <description>
     * @param string $reference
     */
    public function setReference($reference) {
        $this->Reference = $reference;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getReference() {
        return $this->Reference;
    }

    /**
     * @ignore <description>
     * @param string $sdCity
     */
    public function setSdCity($sdCity) {
        $this->sdCity = $sdCity;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdCity() {
        return $this->sdCity;
    }

    /**
     * @ignore <description>
     * @param string $sdCountryCode
     */
    public function setSdCountryCode($sdCountryCode) {
        $this->sdCountryCode = $sdCountryCode;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdCountryCode() {
        return $this->sdCountryCode;
    }

    /**
     * @ignore <description>
     * @param string $sdEmail
     */
    public function setSdEmail($sdEmail) {
        $this->sdEmail = $sdEmail;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdEmail() {
        return $this->sdEmail;
    }

    /**
     * @ignore <description>
     * @param string $sdFirstName
     */
    public function setSdFirstName($sdFirstName) {
        $this->sdFirstName = $sdFirstName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdFirstName() {
        return $this->sdFirstName;
    }

    /**
     * @ignore <description>
     * @param string $sdLastName
     */
    public function setSdLastName($sdLastName) {
        $this->sdLastName = $sdLastName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdLastName() {
        return $this->sdLastName;
    }

    /**
     * @ignore <description>
     * @param string $sdStreet
     */
    public function setSdStreet($sdStreet) {
        $this->sdStreet = $sdStreet;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdStreet() {
        return $this->sdStreet;
    }

    /**
     * @ignore <description>
     * @param string $sdStreetNr
     */
    public function setSdStreetNr($sdStreetNr) {
        $this->sdStreetNr = $sdStreetNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdStreetNr() {
        return $this->sdStreetNr;
    }

    /**
     * @ignore <description>
     * @param string $sdZip
     */
    public function setSdZip($sdZip) {
        $this->sdZip = $sdZip;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getSdZip() {
        return $this->sdZip;
    }

    /**
     * @ignore <description>
     * @param string $tid
     */
    public function setTID($tid) {
        $this->TID = $tid;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getTID() {
        return $this->TID;
    }

    /**
     * @ignore <description>
     * @param string $addrCity
     */
    public function setAddrCity($addrCity) {
        $this->AddrCity = $addrCity;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrCity() {
        return $this->AddrCity;
    }

    /**
     * @ignore <description>
     * @param string $addrCountryCode
     */
    public function setAddrCountryCode($addrCountryCode) {
        $this->AddrCountryCode = $addrCountryCode;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrCountryCode() {
        return $this->AddrCountryCode;
    }

    /**
     * @ignore <description>
     * @param string $addrState
     */
    public function setAddrState($addrState) {
        $this->AddrState = $addrState;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrState() {
        return $this->AddrState;
    }

    /**
     * @ignore <description>
     * @param string $addrStreet
     */
    public function setAddrStreet($addrStreet) {
        $this->AddrStreet = $addrStreet;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrStreet() {
        return $this->AddrStreet;
    }

    /**
     * @ignore <description>
     * @param string $addrStreet2
     */
    public function setAddrStreet2($addrStreet2) {
        $this->AddrStreet2 = $addrStreet2;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrStreet2() {
        return $this->AddrStreet2;
    }

    /**
     * @ignore <description>
     * @param string $addrZIP
     */
    public function setAddrZIP($addrZIP) {
        $this->AddrZIP = $addrZIP;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAddrZIP() {
        return $this->AddrZIP;
    }

    /**
     * @ignore <description>
     * @param string $billingAgreementiD
     */
    public function setBillingAgreementiD($billingAgreementiD) {
        $this->BillingAgreementiD = $billingAgreementiD;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getBillingAgreementiD() {
        return $this->BillingAgreementiD;
    }

    /**
     * @ignore <description>
     * @param string $eMail
     */
    public function setEMail($eMail) {
        $this->EMail = $eMail;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getEMail() {
        return $this->EMail;
    }

    /**
     * @ignore <description>
     * @param string $firstName
     */
    public function setFirstName($firstName) {
        $this->FirstName = $firstName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getFirstName() {
        return $this->FirstName;
    }

    /**
     * @ignore <description>
     * @param string $infoText
     */
    public function setInfoText($infoText) {
        $this->InfoText = $infoText;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getInfoText() {
        return $this->InfoText;
    }

    /**
     * @ignore <description>
     * @param string $lastName
     */
    public function setLastName($lastName) {
        $this->LastName = $lastName;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getLastName() {
        return $this->LastName;
    }

    /**
     * @ignore <description>
     * @param string $transactionID
     */
    public function setTransactionID($transactionID) {
        $this->TransactionID = $transactionID;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getTransactionID() {
        return $this->TransactionID;
    }

    /**
     * @ignore <description>
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @ignore <description>
     * @param string $accBank
     */
    public function setAccBank($accBank) {
        $this->AccBank = $accBank;
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
     * @param string $accIban
     */
    public function setAccIBAN($accIban) {
        $this->AccIBAN = $accIban;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAccIBAN() {
        return $this->AccIBAN;
    }

    /**
     * @ignore <description>
     * @param string $accNr
     */
    public function setAccNr($accNr) {
        $this->AccNr = $accNr;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getAccNr() {
        return $this->AccNr;
    }

    /**
     * @ignore <description>
     * @param string $paymentGuarantee
     */
    public function setPaymentGuarantee($paymentGuarantee) {
        $this->PaymentGuarantee = $paymentGuarantee;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getPaymentGuarantee() {
        return $this->PaymentGuarantee;
    }

    /**
     * @ignore <description>
     * @param string $paymentPurpose
     */
    public function setPaymentPurpose($paymentPurpose) {
        $this->PaymentPurpose = $paymentPurpose;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getPaymentPurpose() {
        return $this->PaymentPurpose;
    }

    /**
     * @ignore <description>
     * @param string $RNo
     */
    public function setRNo($RNo) {
        $this->RNo = $RNo;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getRNo() {
        return $this->RNo;
    }

    /**
     * @ignore <description>
     * @param int $Age
     */
    public function setAge($Age) {
        $this->Age = $Age;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getAge() {
        return $this->Age;
    }

    /**
     * @ignore <description>
     * @param $Birthday
     */
    public function setBirthday($Birthday) {
        $this->Birthday = $Birthday;
    }

    /**
     * @ignore <description>
     * @return datetime
     */
    public function getBirthday() {
        return $this->Birthday;
    }

    /**
     * @ignore <description>
     * @param string $InvNo
     */
    public function setInvNo($InvNo) {
        $this->InvNo = $InvNo;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getInvNo() {
        return $this->InvNo;
    }

    /**
     * @ignore <description>
     * @param int $SecCriteria
     */
    public function setSecCriteria($SecCriteria) {
        $this->SecCriteria = $SecCriteria;
    }

    /**
     * @ignore <description>
     * @return int
     */
    public function getSecCriteria() {
        return $this->SecCriteria;
    }

    /**
     * @ignore <description>
     * @param mixed $AddrStreetNr
     */
    public function setAddrStreetNr($AddrStreetNr) {
        $this->AddrStreetNr = $AddrStreetNr;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAddrStreetNr() {
        return $this->AddrStreetNr;
    }

    /**
     * @ignore <description>
     * @param string $result
     */
    public function setResult($result) {
        $this->result = $result;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * @ignore <description>
     * @param mixed $partialResults
     */
    public function setPartialResults($partialResults) {
        $this->partialResults = $partialResults;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getPartialResults() {
        return $this->partialResults;
    }

    /**
     * @ignore <description>
     * @param mixed $AmountAuth
     */
    public function setAmountAuth($AmountAuth) {
        $this->AmountAuth = $AmountAuth;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAmountAuth() {
        return $this->AmountAuth;
    }

    /**
     * @ignore <description>
     * @param mixed $AmountCap
     */
    public function setAmountCap($AmountCap) {
        $this->AmountCap = $AmountCap;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAmountCap() {
        return $this->AmountCap;
    }

    /**
     * @ignore <description>
     * @param mixed $AmountCred
     */
    public function setAmountCred($AmountCred) {
        $this->AmountCred = $AmountCred;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAmountCred() {
        return $this->AmountCred;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * @ignore <description>
     * @param mixed $userid
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBuyername()
    {
        return $this->buyername;
    }

    /**
     * @ignore <description>
     * @param mixed $buyername
     */
    public function setBuyername($buyername)
    {
        $this->buyername = $buyername;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBuyermail()
    {
        return $this->buyermail;
    }

    /**
     * @ignore <description>
     * @param mixed $buyermail
     */
    public function setBuyermail($buyermail)
    {
        $this->buyermail = $buyermail;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getOrderid()
    {
        return $this->orderid;
    }

    /**
     * @ignore <description>
     * @param mixed $orderid
     */
    public function setOrderid($orderid)
    {
        $this->orderid = $orderid;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAmazonstatus()
    {
        return $this->amazonstatus;
    }

    /**
     * @ignore <description>
     * @param mixed $amazonstatus
     */
    public function setAmazonstatus($amazonstatus)
    {
        $this->amazonstatus = $amazonstatus;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getAddrname()
    {
        return $this->addrname;
    }

    /**
     * @ignore <description>
     * @param mixed $addrname
     */
    public function setAddrname($addrname)
    {
        $this->addrname = $addrname;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }

    /**
     * @ignore <description>
     * @param mixed $phonenumber
     */
    public function setPhonenumber($phonenumber)
    {
        $this->phonenumber = $phonenumber;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBdaddrstreet2()
    {
        return $this->bdaddrstreet2;
    }

    /**
     * @ignore <description>
     * @param mixed $bdaddrstreet2
     */
    public function setBdaddrstreet2($bdaddrstreet2)
    {
        $this->bdaddrstreet2 = $bdaddrstreet2;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBdaddrcity()
    {
        return $this->bdaddrcity;
    }

    /**
     * @ignore <description>
     * @param mixed $bdaddrcity
     */
    public function setBdaddrcity($bdaddrcity)
    {
        $this->bdaddrcity = $bdaddrcity;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBdaddrcountrycode()
    {
        return $this->bdaddrcountrycode;
    }

    /**
     * @ignore <description>
     * @param mixed $bdaddrcountrycode
     */
    public function setBdaddrcountrycode($bdaddrcountrycode)
    {
        $this->bdaddrcountrycode = $bdaddrcountrycode;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBdaddrname()
    {
        return $this->bdaddrname;
    }

    /**
     * @ignore <description>
     * @param mixed $bdaddrname
     */
    public function setBdaddrname($bdaddrname)
    {
        $this->bdaddrname = $bdaddrname;
    }

    /**
     * @ignore <description>
     * @return mixed
     */
    public function getBdaddrzip()
    {
        return $this->bdaddrzip;
    }

    /**
     * @ignore <description>
     * @param mixed $bdaddrzip
     */
    public function setBdaddrzip($bdaddrzip)
    {
        $this->bdaddrzip = $bdaddrzip;
    }

    /**
     * @return string
     */
    public function getAccesstoken()
    {
        return $this->accesstoken;
    }

    /**
     * @param string $accesstoken
     */
    public function setAccesstoken(string $accesstoken)
    {
        $this->accesstoken = $accesstoken;
    }

    /**
     * @return string
     */
    public function getSchemeReferenceID()
    {
        return $this->schemeReferenceID;
    }

    /**
     * @param string $schemeReferenceID
     */
    public function setSchemeReferenceID(string $schemeReferenceID)
    {
        $this->schemeReferenceID = $schemeReferenceID;
    }

    /**
     * @ignore <description>
     * @return string
     */
    public function getCard() {
        return base64_decode($this->card);
    }

    /**
     * @ignore <description>
     * @param string $card
     */
    public function setCard($card) {
        $this->card = $card;
    }

    /**
     * @return string
     */
    public function getCardHolder()
    {
        return $this->CardHolder;
    }

    /**
     * @param string $cardHolder
     */
    public function setCardHolder(string $cardHolder)
    {
        $this->CardHolder = $cardHolder;
    }

    /**
     *
     */
    public function getOrderVars()
    {
        return $this->orderVars;
    }

    /**
     * @param $orderVars
     */
    public function setOrderVars($orderVars)
    {
        $this->orderVars = $orderVars;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $cardHolder
     */
    public function getShopTransId(string $cardHolder)
    {
        return $this->shopTransId;
    }

    /**
     * @return string
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @param string $cardHolder
     */
    public function setShopTransId($shopTransId)
    {
        $this->shopTransId = $shopTransId;
    }

    /**
     *
     */
    public function getButtonsignature()
    {
        return $this->buttonsignature;
    }

    /**
     * @param $buttonsignature
     */
    public function setButtonsignature($buttonsignature)
    {
        $this->buttonsignature = $buttonsignature;
    }

    public function getButtonpayload()
    {
        return $this->buttonpayload;
    }

    /**
     * @param $buttonpayload
     */
    public function setButtonpayload($buttonpayload)
    {
        $this->buttonpayload = $buttonpayload;
    }

    public function getButtonpublickeyid()
    {
        return $this->buttonpublickeyid;
    }

    /**
     * @param $buttonpayload
     */
    public function setButtonpublickeyid($buttonpublickeyid)
    {
        $this->buttonpublickeyid = $buttonpublickeyid;
    }

    public function setStoken($param)
    {
        $this->stoken = $param;
    }

    /**
     * @return mixed
     */
    public function getStoken()
    {
        return $this->stoken;
    }

    public function setDelAdress($param)
    {
        $this->delAdress = $param;

    }

    /**
     * @return mixed
     */
    public function getDelAdress()
    {
        return $this->delAdress;
    }
}
