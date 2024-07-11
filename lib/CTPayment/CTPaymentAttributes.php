<?php
/**
 * The Computop Shopware Plugin is free software: you can redistribute it and/or modify
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
 * PHP version 5.6, 7 , 7.1
 *
 * @category  Payment
 * @package   Computop_Shopware5_Plugin
 * @author    FATCHIP GmbH <support@fatchip.de>
 * @copyright 2018 Computop
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      https://www.computop.com
 */

namespace Fatchip\CTPayment;

/**
 * Class CTPaymentAttributes
 * @package Fatchip\CTPayment
 */
class CTPaymentAttributes
{
    /**
     * @var array
     */
    const orderAttributes = [

        'status' => [
            'type' => 'VARCHAR(255)',
        ],
        'transid' => [
            'type' => 'VARCHAR(255)',
        ],
        'payid' => [
            'type' => 'VARCHAR(255)',
        ],
        'xid' => [
            'type' => 'VARCHAR(255)',
        ],
        'klarnainvno' => [
            'type' => 'VARCHAR(30)',
        ],
        'shipcaptured' => [
            'type' => 'float',
            'additionalInfo' =>
                ['label' => 'Versandkosten bisher eingezogen:',
                    'helpText' => '',
                    'displayInBackend' => true,
                ]
        ],
        'shipdebit' => [
            'type' => 'float',
            'additionalInfo' =>
                ['label' => 'Versandkosten bisher gutgeschrieben:',
                    'helpText' => '',
                    'displayInBackend' => true,
                ]
        ],
        'kreditkartepseudonummer' => [
            'type' => 'VARCHAR(20)',
        ],
        'kreditkartebrand' => [
            'type' => 'VARCHAR(25)',
        ],
        'kreditkarteexpiry' => [
            'type' => 'VARCHAR(6)',
        ],
        'paypalbillingagreementid' => [
            'type' => 'VARCHAR(19)',
        ],
        'lastschriftmandateid' => [
            'type' => 'VARCHAR(40)',
        ],
        'lastschriftdos' => [
            'type' => 'VARCHAR(10)',
        ],
        'kreditkarteschemereferenceid' => [
            'type' => 'VARCHAR(255)',
        ],
        'kreditkartecardholdername' => [
            'type' => 'VARCHAR(255)',
        ],
        'sessionId' => [
            'type' => 'VARCHAR(255)',
        ],
    ];

    /**
     * @var array
     */
    const orderDetailsAttributes = [

        'paymentstatus' => [
            'type' => 'VARCHAR(255)',
        ],
        'shipmentdate' => [
            'type' => 'DATE',
        ],
        'captured' => [
            'type' => 'float',
        ],
        'debit' => [
            'type' => 'float',
        ]
    ];

    const userAttributes = [
        'SocialSecurityNumber' => [
            'type' => 'VARCHAR(12)',
        ],
        'AnnualSalary' => [
            'type' => 'float',
        ],
        'lastschriftbank' => [
            'type' => 'VARCHAR(20)'
        ],
        'lastschriftaccowner' => [
            'type' => 'VARCHAR(50)'
        ],
        'lastschriftiban' => [
            'type' => 'VARCHAR(50)'
        ],
        'afterpayinstallmentiban' => [
            'type' => 'VARCHAR(50)'
        ],
        'creditcardinitialpaymentsuccess' => [
            'type' => 'TINYINT(1)'
        ],

    ];
}
