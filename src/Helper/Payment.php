<?php

namespace Fatchip\ComputopPayments\Helper;

use Fatchip\ComputopPayments\Model\Method\AmazonPay;
use Fatchip\ComputopPayments\Model\Method\BaseMethod;
use Fatchip\ComputopPayments\Model\Method\Creditcard;
use Fatchip\ComputopPayments\Model\Method\DirectDebit;
use Fatchip\ComputopPayments\Model\Method\Easycredit;
use Fatchip\ComputopPayments\Model\Method\Ideal;
use Fatchip\ComputopPayments\Model\Method\Klarna;
use Fatchip\ComputopPayments\Model\Method\PayPal;
use Fatchip\ComputopPayments\Model\Method\PayPalExpress;
use Fatchip\ComputopPayments\Model\Method\Ratepay\DirectDebit as RPDirectDebit;
use Fatchip\ComputopPayments\Model\Method\Ratepay\Invoice;

class Payment
{
    /**
     * @var Payment
     */
    protected static $instance = null;

    /**
     * List of all implemented Computop methods in this module
     *
     * @var array
     */
    protected $paymentMethods = array(
        Creditcard::ID      => array('title' => 'Kreditkarte',          'model' => \Fatchip\ComputopPayments\Model\Method\Creditcard::class),
        PayPal::ID          => array('title' => 'PayPal',               'model' => \Fatchip\ComputopPayments\Model\Method\PayPal::class),
        PayPalExpress::ID   => array('title' => 'PayPalExpress',        'model' => \Fatchip\ComputopPayments\Model\Method\PayPalExpress::class),
        DirectDebit::ID     => array('title' => 'Lastschrift',          'model' => \Fatchip\ComputopPayments\Model\Method\DirectDebit::class),
        Ideal::ID           => array('title' => 'iDEAL',                'model' => \Fatchip\ComputopPayments\Model\Method\Ideal::class),
        Klarna::ID          => array('title' => 'Klarna',               'model' => \Fatchip\ComputopPayments\Model\Method\Klarna::class),
        Easycredit::ID      => array('title' => 'Easycredit',           'model' => \Fatchip\ComputopPayments\Model\Method\Easycredit::class),
        AmazonPay::ID       => array('title' => 'AmazonPay',            'model' => \Fatchip\ComputopPayments\Model\Method\AmazonPay::class),
    );

    /**
     * Create singleton instance of this payment helper
     *
     * @return Payment
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = oxNew(self::class);
        }
        return self::$instance;
    }


    /**
     * Return all implemented Computop methods
     *
     * @return array
     */
    public function getComputopPaymentMethods()
    {
        $paymentMethods = array();
        foreach ($this->paymentMethods as $paymentId => $paymentMethodInfo) {
            $paymentMethods[$paymentId] = $paymentMethodInfo['title'];
        }
        return $paymentMethods;
    }

    /**
     * Determine if given paymentId is a Computop payment method
     *
     * @param string $paymentId
     * @return bool
     */
    public function isComputopPaymentMethod($paymentId)
    {
        if (isset($this->paymentMethods[$paymentId])) {
            return true;
        }
        return false;
    }

    /**
     * Returns payment model for given paymentId
     *
     * @param string $paymentId
     * @return BaseMethod
     * @throws \Exception
     */
    public function getComputopPaymentModel($paymentId)
    {
        if ($this->isComputopPaymentMethod($paymentId) === false || !isset($this->paymentMethods[$paymentId]['model'])) {
            throw new \Exception('Computop payment method unknown: '.$paymentId);
        }

        return oxNew($this->paymentMethods[$paymentId]['model']);
    }

    /**
     * Generates random transaction id for TransID parameter
     * Taken from library-computop generateTransID() method
     *
     * @param  int $digitCount
     * @return string
     */
    public function getTransactionId($digitCount = 12)
    {
        mt_srand(intval(microtime(true) * 1000000));

        $transID = (string)mt_rand();
        // y: 2 digits for year
        // m: 2 digits for month
        // d: 2 digits for day of month
        // H: 2 digits for hour
        // i: 2 digits for minute
        // s: 2 digits for second
        $transID .= date('ymdHis');
        // $transID = md5($transID);
        return substr($transID, 0, $digitCount);
    }
}
