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
 * Class CTPaymentConfigForms
 * @package Fatchip\CTPayment
 */
class CTPaymentConfigForms
{
    const formGeneralTextElements =
        [
            'merchantID' => [
                'name' => 'merchantID',
                'type' => 'text',
                'value' => '',
                'label' => 'MerchantID',
                'required' => true,
                'description' => 'Ihre Merchant Id (Benutzername)',
            ],
            'mac' => [
                'name' => 'mac',
                'type' => 'text',
                'value' => '',
                'label' => 'MAC',
                'required' => true,
                'description' => 'Ihr HMAC-Key',
            ],
            'blowfishPassword' => [
                'name' => 'blowfishPassword',
                'type' => 'text',
                'value' => '',
                'label' => 'Passwort',
                'required' => true,
                'description' => 'Ihr Verschlüsselungs-Passwort',
            ],
            'prefixOrdernumber' => [
                'name' => 'prefixOrdernumber',
                'type' => 'text',
                'value' => '',
                'label' => 'Bestellnummer Präfix',
                'required' => false,
                'description' => 'Präfix für Bestellnummern.',
            ],
            'suffixOrdernumber' => [
                'name' => 'suffixOrdernumber',
                'type' => 'text',
                'value' => '',
                'label' => 'Bestellnummer Suffix',
                'required' => false,
                'description' => 'Suffix für Bestellnummern.',
            ],
        ];

    const formGeneralSelectElements =
        [
            'debuglog' => [
                'name' => 'debuglog',
                'type' => 'select',
                'value' => 'inactive',
                'label' => 'Debug Protokoll',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['inactive', [
                            'de_DE' => 'keine Protokollierung',
                            'en_GB' => 'disable logging',
                        ]],
                        ['active', [
                            'de_DE' => 'Protokollierung',
                            'en_GB' => 'enable logging',
                        ]],
                        ['extended', [
                            'de_DE' => 'erweiterte Protokollierung',
                            'en_GB' => 'enable extra logging',
                        ]],
                    ],
                'description' => 'Erzeugt eine Log Datei <FatchipCTPayment_.log> mit Debug Ausgaben im Shopware Protokollverzeichnis.<BR>',
            ],
            'encryption' => [
                'name' => 'encryption',
                'type' => 'select',
                'value' => 'blowfish',
                'label' => 'Verschlüsselung',
                'required' => true,
                'editable' => false,
                'store' =>
                    [
                        ['blowfish', [
                            'de_DE' => 'Blowfish Verschlüsselung (Standard)',
                            'en_GB' => 'Blowfish encyption (default)',
                        ]],
                        ['aes', [
                            'de_DE' => 'AES Verschlüsselung',
                            'en_GB' => 'AES encyption',
                        ]],
                    ],
                'description' => '<p>Art der verwendeten Verschlüsselung.</p><p>Blowfish Verschlüsselung wird vom Computop Support als Standard eingerichtet.</p><p>Sollte die Blowfish Verschlüsselung (bf-cbc) bei Ihrem Hoster nicht verfügbar sein, wenden Sie sich bitte an den Computop Support und lassen Sie AES aufschalten.</p><p>Wenn seitens Computop AES aktiviert wurde, stellen Sie auf AES um.</p>',
            ],
        ];

    const formCreditCardSelectElements =
        [
            'creditCardMode' => [
                'name' => 'creditCardMode',
                'type' => 'select',
                'value' => 'IFRAME',
                'label' => 'Kreditkarte - Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['IFRAME', 'IFrame'],
                        ['SILENT', 'Silent Mode'],
                        ['PAYMENTPAGE', 'Payment Page'],
                    ],
                'description' => '<p><b>IFrame</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in einem IFrame eingegeben<BR><b>Silent Mode</b>: Kreditkartendaten werden auf der Seite "Prüfen und Bestellen" eingegeben.<BR><b>Payment Page</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in einem blanken Fenster eingegeben<BR></p>'
            ],
            'creditCardTestMode' => [
                'name' => 'creditCardTestMode',
                'type' => 'select',
                'value' => 1,
                'label' => 'Kreditkarte - Test-Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        [0, [
                            'de_DE' => 'inaktiv',
                            'en_GB' => 'disabled',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                        ]],
                    ],
            ],
            'creditCardSilentModeBrandsVisa' => [
                'name' => 'creditCardSilentModeBrandsVisa',
                'type' => 'select',
                'value' => 1,
                'label' => 'Kreditkarte - Visa (Silent Mode)',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        [0, [
                            'de_DE' => 'inaktiv',
                            'en_GB' => 'disabled',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                        ]],
                    ],
            ],
            'creditCardSilentModeBrandsMaster' => [
                'name' => 'creditCardSilentModeBrandsMaster',
                'type' => 'select',
                'value' => 1,
                'label' => 'Kreditkarte - MasterCard (Silent Mode)',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        [0, [
                            'de_DE' => 'inaktiv',
                            'en_GB' => 'disabled',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                        ]],
                    ],
            ],
            'creditCardSilentModeBrandsAmex' => [
                'name' => 'creditCardSilentModeBrandsAmex',
                'type' => 'select',
                'value' => 1,
                'label' => 'Kreditkarte - American Express (Silent Mode)',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        [0, [
                            'de_DE' => 'inaktiv',
                            'en_GB' => 'disabled',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                        ]],
                    ],
            ],
            'creditCardCaption' => [
                'name' => 'creditCardCaption',
                'type' => 'select',
                'value' => 'AUTO',
                'label' => 'Kreditkarte - Capture Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['AUTO', [
                            'de_DE' => 'Automatisch',
                            'en_GB' => 'automatic',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                        ]],
                    ],
                'description' => '<p><b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR><b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.</p>',
            ],
            'creditCardAcquirer' => [
                'name' => 'creditCardAcquirer',
                'type' => 'select',
                'value' => 'GICC',
                'label' => 'Kreditkarte - Acquirer',
                'required' => 'true',
                'editable' => false,
                'store' =>
                    [
                        ['GICC', 'GICC'],
                        ['CAPN', 'CAPN'],
                        ['Omnipay', 'Omnipay'],
                    ],
                'description' => '<p><b>GICC</b>: Concardis, B+S Card Service, EVO Payments, American Express, Elavon, SIX Payment Service<BR><b>CAPN</b>: American Express<BR><b>Omnipay</b>: EMS payment solutions, Global Payments, Paysquare</p>',
            ]
        ];

    const formCreditCardNumberElements =
        [
        ];

    const formCreditCardTextElements =
        [
            'creditCardTemplate' => [
                'name' => 'creditCardTemplate',
                'type' => 'text',
                'value' => 'ct_responsive',
                'label' => 'Kreditkarte - Template Name',
                'required' => false,
                'description' => 'Name der XSLT-Datei mit Ihrem individuellen Layout für das Bezahlformular. Wenn Sie das Responsive Computop-Template für mobile Endgeräte nutzen möchten, übergeben Sie den Templatenamen „ct_responsive“.',
            ],
        ];

    const formIdealSelectElements =
        [
            'idealDirektOderUeberSofort' => [
                'name' => 'idealDirektOderUeberSofort',
                'type' => 'select',
                'value' => 'DIREKT',
                'label' => 'iDEAL - Dienst',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['DIREKT', 'iDEAL Direkt'],
                        ['PPRO', 'via PPRO'],
                    ],
                'description' => 'Wählen Sie hier Ihre Anbindung an iDeal aus - direkt oder über PPRO.',
            ],
        ];


    const formLastschriftSelectElements =
        [
            'lastschriftDienst' => [
                'name' => 'lastschriftDienst',
                'type' => 'select',
                'value' => 'DIREKT',
                'label' => 'Lastschrift - Dienst',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['DIREKT', [
                            'de_DE' => 'Direktanbindung',
                            'en_GB' => 'direct',
                        ]],
                        ['EVO', [
                            'de_DE' => 'EVO Payments',
                            'en_GB' => 'EVO Payments',
                        ]],
                        ['INTERCARD', [
                            'de_DE' => 'Intercard',
                            'en_GB' => 'Intercard',
                        ]],
                    ],
                'description' => 'Lastschrift Zahlungen können direkt, über EVO oder über INTERCARD abgewickelt werden.',
            ],
            'lastschriftCaption' => [
                'name' => 'lastschriftCaption',
                'type' => 'select',
                'value' => 'AUTO',
                'label' => 'Lastschrift - Capture Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['AUTO', [
                            'de_DE' => 'Automatisch',
                            'en_GB' => 'automatic',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                        ]],
                    ],
                'description' => '<p></p><b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR><b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.</p>',
            ],
            'lastschriftAnon' => [
                'name' => 'lastschriftAnon',
                'type' => 'select',
                'value' => 'Aus',
                'label' => 'Iban anonymisieren',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['Aus', [
                            'de_DE' => 'Aus',
                            'en_GB' => 'off',
                        ]],
                        ['An', [
                            'de_DE' => 'An',
                            'en_GB' => 'on',
                        ]],
                    ],
                'description' => 'Stellt im Checkout und im Mein Konto Bereich die Iban anonymisiert dar',
            ],
        ];

    const formLastschriftNumberElements =
        [
        ];

    const formPayPalSelectElements =
        [
            'paypalCaption' => [
                'name' => 'paypalCaption',
                'type' => 'select',
                'value' => 'AUTO',
                'label' => 'Paypal - Capture Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['AUTO', [
                            'de_DE' => 'Automatisch',
                            'en_GB' => 'automatic',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                        ]],
                    ],
                'description' => '<p>Bestimmt, ob der angefragte Betrag sofort oder erst später eingezogen wird. <br><b>Wichtig:<br>Bitte kontaktieren Sie den Computop Support für Manual, um die unterschiedlichen Einsatzmöglichkeiten abzuklären.</b></p>',
            ],
        ];

    const formAmazonTextElements =
        [
            'amazonpayMerchantId' => [
                'name' => 'amazonpayMerchantId',
                'type' => 'text',
                'value' => '',
                'label' => 'AmazonPay - MerchantId',
                'required' => false,
                'description' => 'Ihre Amazonpay MerchantId',
            ],
            'amazonpayPrivKey' => [
                'name' => 'amazonpayPrivKey',
                'type' => 'text',
                'value' => '',
                'label' => 'AmazonPay - Private Kay',
                'required' => false,
                'description' => 'Ihr Amazonpay Private Key',
            ],
            'amazonpayPubKeyId' => [
                'name' => 'amazonpayPubKeyId',
                'type' => 'text',
                'value' => '',
                'label' => 'AmazonPay - Public Key Id',
                'required' => false,
                'description' => 'Ihre Amazonpay Public Key Id',
            ],
            'amazonpayStoreId' => [
                'name' => 'amazonpayStoreId',
                'type' => 'text',
                'value' => '',
                'label' => 'AmazonPay - Store Id',
                'required' => false,
                'description' => 'Ihre Amazonpay Store Id',
            ],

        ];

    const formAmazonSelectElements =
        [
            'amazonLiveMode' => [
                'name' => 'amazonLiveMode',
                'type' => 'select',
                'value' => 'Test',
                'label' => 'Amazon Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['Live', 'Live'],
                        ['Test', 'Test'],
                    ],
                'description' => 'AmazonPay im Live oder Testmodus benutzen',
            ],
            'amazonCaptureType' => [
                'name' => 'amazonCaptureType',
                'type' => 'select',
                'value' => 'AUTO',
                'label' => 'Amazon Capture Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['AUTO', [
                            'de_DE' => 'Automatisch',
                            'en_GB' => 'automatic',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                        ]],
                    ],
                'description' => '<p><b>Automatisch</b>: Reservierte Beträge werden automatisch eingezogen.<BR><b>Manuell</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.</p>',
            ],
            'amazonButtonType' => [
                'name' => 'amazonButtonType',
                'type' => 'select',
                'value' => 'PwA',
                'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_TYPE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Typ</a>',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['PwA', 'Amazon Pay (Default)'],
                        ['Pay', 'Pay'],
                        ['A', 'A'],
                        ['LwA', 'LwA'],
                        ['Login', 'Login'],
                    ],
                'description' => '<p>Typ des Amazon Buttons<BR>Das Aussehen der verschiedenen Buttons.<BR>Klicken Sie links auf den Link "AmazonPay - Button Typ"</p>',
            ],
            'amazonButtonColor' => [
                'name' => 'amazonButtonColor',
                'type' => 'select',
                'value' => 'Gold',
                'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_COLOR_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Farbe</a>',
                'required' => 'true',
                'editable' => false,
                'store' =>
                    [
                        ['Gold', 'Gold'],
                        ['LightGray', 'LightGray'],
                        ['DarkGray', 'DarkGray'],
                    ],
                'description' => '<p>Farbe des Amazon Buttons<BR>Das Aussehen der verschiedenen Buttons.<BR>Klicken Sie links auf den Link "AmazonPay - Button Farbe"</p>',
            ],
            'amazonButtonSize' => [
                'name' => 'amazonButtonSize',
                'type' => 'select',
                'value' => 'medium',
                'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_SIZE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Größe</a>',
                'required' => 'true',
                'editable' => false,
                'store' =>
                    [
                        ['small', [
                            'de_DE' => 'klein',
                            'en_GB' => 'small',
                        ]],
                        ['medium', [
                            'de_DE' => 'mittel',
                            'en_GB' => 'medium',
                        ]],
                    ],
                'description' => '<p>Größe des Amazon Buttons<BR>Das Aussehen der verschiedenen Buttons.<BR>Klicken Sie links auf den Link "AmazonPay - Button Größe"</p>',
            ],
        ];

    const formKlarnaTextElements =
        [
            'klarnaaccount' => [
                'name' => 'klarnaaccount',
                'type' => 'text',
                'value' => '',
                'label' => 'Klarna Konto',
                'required' => false,
                'description' => '<p>Das zu benutzende Klarna Konto.</p>',
            ],
        ];

    const formTranslations =
        [
            'de_DE' => [
                'merchantID' => [
                    'label' => 'MerchantID',
                    'description' => 'Ihre MerchantID',
                ],
                'mac' => [
                    'label' => 'MAC',
                    'description' => 'Ihr HMAC-Key',
                ],
                'blowfishPassword' => [
                    'label' => 'Passwort',
                    'description' => 'Ihr Verschlüsselungs-Passwort',
                ],
                'fatchip_computop_ideal_button' => [
                    'label' => '<strong>iDeal Banken aktualisieren <strong>',
                ],
                'prefixOrdernumber' => [
                    'label' => 'Bestellnummer Präfix',
                    'description' => 'Präfix für Bestellnummern.',
                ],
                'suffixOrdernumber' => [
                    'label' => 'Bestellnummer Suffix',
                    'description' => 'Suffix für Bestellnummern.',
                ],
                'debuglog' => [
                    'label' => 'Debug Protokoll',
                    'description' => 'Erzeugt eine Log Datei <FatchipCTPayment_.log> mit Debug Ausgaben im Shopware Protokollverzeichnis',
                ],
                'encryption' => [
                    'label' => 'Verschlüsselung',
                    'description' => '<p>Art der verwendeten Verschlüsselung.</p><p>Blowfish Verschlüsselung wird vom Computop Support als Standard eingerichtet.</p><p>Sollte die Blowfish Verschlüsselung (bf-cbc) bei Ihrem Hoster nicht verfügbar sein, wenden Sie sich bitte an den Computop Support und lassen Sie AES aufschalten.</p><p>Wenn seitens Computop AES aktiviert wurde, stellen Sie auf AES um.</p>',
                ],
                'creditCardMode' => [
                    'label' => 'Kreditkarte - Modus',
                    'description' => '<p><b>IFrame</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in einem IFrame eingegeben<BR><b>Silent Mode</b>: Kreditkartendaten werden auf der Seite "Prüfen und Bestellen" eingegeben.<BR><b>Payment Page</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in einem blanken Fenster eingegeben.</p>',
                ],
                'creditCardTestMode' => [
                    'label' => 'Kreditkarte - Test-Modus',
                    //'description' => '',
                ],
                'creditCardSilentModeBrandsVisa' => [
                    'label' => 'Kreditkarte - Visa (Silent Mode)',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsMaster' => [
                    'label' => 'Kreditkarte - MasterCard (Silent Mode)',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsAmex' => [
                    'label' => 'Kreditkarte - American Express (Silent Mode)',
                    // 'description' => '',
                ],
                'creditCardCaption' => [
                    'label' => 'Kreditkarte - Capture Modus',
                    'description' => '<p><b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR><b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.</p>',
                ],
                'creditCardAcquirer' => [
                    'label' => 'Kreditkarte - Acquirer',
                    'description' => '<p><b>GICC</b>: Concardis, B+S Card Service, EVO Payments, American Express, Elavon, SIX Payment Service<BR><b>CAPN</b>: American Express<BR><b>Omnipay</b>: EMS payment solutions, Global Payments, Paysquare</p>',
                ],

                'creditCardTemplate' => [
                    'label' => 'Kreditkarte - Template Name',
                    'description' => 'Name der XSLT-Datei mit Ihrem individuellen Layout für das Bezahlformular. Wenn Sie das Responsive Computop-Template für mobile Endgeräte nutzen möchten, übergeben Sie den Templatenamen „ct_responsive“.',
                ],
                'idealDirektOderUeberSofort' => [
                    'label' => 'iDEAL - Dienst',
                    'description' => 'Wählen Sie hier Ihre Anbindung an iDeal aus - direkt oder über PPRO.',
                ],
                'lastschriftDienst' => [
                    'label' => 'Lastschrift - Dienst',
                    'description' => 'Lastschrift Zahlungen können direkt, über EVO oder über INTERCARD abgewickelt werden.',
                ],
                'lastschriftCaption' => [
                    'label' => 'Lastschrift - Capture Modus',
                    'description' => '<p></p><b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR><b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.</p>',
                ],
                'lastschriftAnon' => [
                    'label' => 'Iban anonymisieren',
                    'description' => 'Stellt im Checkout und im Mein Konto Bereich die Iban anonymisiert dar',
                ],
                'paypalCaption' => [
                    'label' => 'Paypal - Capture Modus',
                    'description' => '<p>Bestimmt, ob der angefragte Betrag sofort oder erst später eingezogen wird. <br><b>Wichtig:<br>Bitte kontaktieren Sie den Computop Support für Manual, um die unterschiedlichen Einsatzmöglichkeiten abzuklären.</b></p>',
                ],
                'amazonSellerId' => [
                    'label' => 'AmazonPay - SellerId',
                    'description' => 'Ihre Amazonpay SellerId',
                ],
                'amazonClientId' => [
                    'label' => 'AmazonPay - ClientId',
                    'description' => 'Ihre Amazonpay ClientId',
                ],
                'amazonLiveMode' => [
                    'label' => 'Amazon Modus',
                    'description' => 'AmazonPay im Live oder Testmodus benutzen',
                ],
                'amazonCaptureType' => [
                    'label' => 'Amazon Capture Modus',
                    'description' => '<p><b>Automatisch</b>: Reservierte Beträge werden automatisch eingezogen.<BR><b>Manuell</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.</p>',
                ],
                'amazonButtonType' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_TYPE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Typ</a>',
                    'description' => '<p>Typ des Amazon Buttons<BR>Das Aussehen der verschiedenen Buttons.<BR>Klicken Sie links auf den Link "AmazonPay - Button Typ"</p>',
                ],
                'amazonButtonColor' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_COLOR_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Farbe</a>',
                    'description' => '<p>Farbe des Amazon Buttons<BR>Das Aussehen der verschiedenen Buttons.<BR>Klicken Sie links auf den Link "AmazonPay - Button Farbe"</p>',
                ],
                'amazonButtonSize' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_SIZE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Größe</a>',
                    'description' => '<p>Größe des Amazon Buttons<BR>Das Aussehen der verschiedenen Buttons.<BR>Klicken Sie links auf den Link "AmazonPay - Button Größe"</p>',
                ],

                'klarnaaccount' => [
                    'label' => 'Klarna Konto',
                    'description' => '<p>Das zu benutzende Klarna Konto.</p>',
                ],
            ],
            'en_GB' => [
                'merchantID' => [
                    'label' => 'MerchantID',
                    'description' => 'Your MerchantID',
                ],
                'mac' => [
                    'label' => 'MAC',
                    'description' => 'Your HMAC-Key',
                ],
                'blowfishPassword' => [
                    'label' => 'Password',
                    'description' => 'Your encryption password',
                ],
                'fatchip_computop_ideal_button' => [
                    'label' => '<strong>update iDeal banks<strong>',
                ],
                'prefixOrdernumber' => [
                    'label' => 'Ordernumber prefix',
                    'description' => 'Prefix for ordernumbers.',
                ],
                'suffixOrdernumber' => [
                    'label' => 'Ordernumber suffix',
                    'description' => 'Suffix for ordernumbers.',
                ],
                'debuglog' => [
                    'label' => 'Debug protocol',
                    'description' => 'Creates a log file <FatchipCTPayment_.log> with debugging output on the shopware log folder',
                ],
                'encryption' => [
                    'label' => 'Encyption',
                    'description' => '<p>Type of encryption used.<br>Blowfish encryption is set up by Computop Support as a standard. If Blowfish encryption (bf-cbc) is not available from your hoster, please contact Computop Support and have AES activated.<BR>If Computop has activated AES, switch to AES.</p>',
                ],
                'creditCardMode' => [
                    'label' => 'Creditcard - Mode',
                    'description' => '</p><b>IFrame</b>: The creditcard form will be displayed after clicking "confirm payment" in an iframe<BR><b>Silent Mode</b>: The creditcard form will be displayed on the "complete order" page.<BR><b>Payment Page</b>: Credit card details are entered in a blank page after clicking on "Order payment".',
                ],
                'creditCardTestMode' => [
                    'label' => 'Creditcard - Testmode',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsVisa' => [
                    'label' => 'Creditcard - Visa (Silent Mode)',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsMaster' => [
                    'label' => 'Creditcard - MasterCard (Silent Mode)',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsAmex' => [
                    'label' => 'Creditcard - American Express (Silent Mode)',
                    // 'description' => '',
                ],
                'creditCardCaption' => [
                    'label' => 'Creditcard - Capture Mode',
                    'description' => '<p></p><b>AUTO</b>: Reserved amounts will be captured automatically.<BR><b>MANUAL</b>: Reserverd amounts have to be captured manuelly in the shop backend.</p>',
                ],
                'creditCardAcquirer' => [
                    'label' => 'Creditcard - Acquirer',
                    'description' => '<p><b>GICC</b>: Concardis, B+S Card Service, EVO Payments, American Express, Elavon, SIX Payment Service<BR><b>CAPN</b>: American Express<BR><b>Omnipay</b>: EMS payment solutions, Global Payments, Paysquare</p>',
                ],
                'creditCardTemplate' => [
                    'label' => 'Creditcard - Template name',
                    'description' => 'Name of the XSLT-file with your individual payment form layout. If you want to use the responsive computop template for mobile devices, please use the template name „ct_responsive“.',
                ],
                'idealDirektOderUeberSofort' => [
                    'label' => 'iDEAL - Service',
                    'description' => 'Select your integration with iDeal here - directly or via PPRO.',
                ],
                'lastschriftDienst' => [
                    'label' => 'Direct debit - Service',
                    'description' => 'Direct debit payments can be handled by using direct, EVO or INTERCARD',
                ],
                'lastschriftCaption' => [
                    'label' => 'Direct debit - Capture Mode',
                    'description' => '<p><b>AUTO</b>: Reserved amounts will be captured automatically.<BR><b>MANUAL</b>: Reserverd amounts have to be captured manuelly in the shop backend.</p>',
                ],
                'lastschriftAnon' => [
                    'label' => 'Anonymize IBAN',
                    'description' => 'The customers IBAN will be displayed anonymized in checkout and on the my accoutn page',
                ],
                'paypalCaption' => [
                    'label' => 'Paypal - Capture Modus',
                    'description' => '<p>Determines whether the requested amount is collected immediately or at a later date. <br><b>Important:<br>Please contact Computop Support for Manual to clarify the different application options.</b></p>',
                ],
                'amazonSellerId' => [
                    'label' => 'AmazonPay - SellerId',
                    'description' => 'Your Amazonpay SellerId',
                ],
                'amazonClientId' => [
                    'label' => 'AmazonPay - ClientId',
                    'description' => 'Your Amazonpay ClientId',
                ],
                'amazonLiveMode' => [
                    'label' => 'Amazon Modus',
                    'description' => 'Use AmazonPay in live or test mode',
                ],
                'amazonCaptureType' => [
                    'label' => 'Amazon Capture Mode',
                    'description' => '<p><b>AUTO</b>: Reserved amounts will be captured automatically.<BR><b>MANUAL</b>: Reserverd amounts have to be captured manuelly in the shop backend.</p>',
                ],
                'amazonButtonType' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_TYPE_PARAMETER" target="_blank" rel="noopener">AmazonPay - Button Type</a>',
                    'description' => '<p>Type of the Amazon button<BR>The look of the different buttons.<BR>Please click on the left link "AmazonPay - Button Type"</p>',
                ],
                'amazonButtonColor' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_COLOR_PARAMETER" target="_blank" rel="noopener">AmazonPay - Button Color</a>',
                    'description' => '<p>Color of the Amazon button<BR>The look of the different button.<BR>Please click on the left link "AmazonPay - Button Color"</p>',
                ],
                'amazonButtonSize' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_SIZE_PARAMETER" target="_blank" rel="noopener">AmazonPay - Button Size</a>',
                    'description' => '<p>Size of the amazon button<BR>The look of the different button.<BR>Please click on the left link "AmazonPay - Button Size"</p>',
                ],

                'klarnaaccount' => [
                    'label' => 'Klarna Account',
                    'description' => '<p>Your Klarna account.</p>',
                ],
            ],
        ];

    const formPayPalExpressSelectElements =
        [
            'paypalExpressCaption' => [
                'name' => 'paypalExpressCaption',
                'type' => 'select',
                'value' => 'AUTO',
                'label' => 'PaypalExpress - Capture Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['AUTO', [
                            'de_DE' => 'Automatisch',
                            'en_GB' => 'automatic',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                        ]],
                    ],
                'description' => '<p>Bestimmt, ob der angefragte Betrag sofort oder erst später eingezogen wird. <br><b>Wichtig:<br>Bitte kontaktieren Sie den Computop Support für Manual, um die unterschiedlichen Einsatzmöglichkeiten abzuklären.</b></p>',
            ],
        ];

    const formPayPalExpressTextElementClientID = [
        'paypalExpressClientID' => [
            'name' => 'paypalExpressClientID',
            'type' => 'text',
            'value' => '',
            'label' => 'PaypalExpress - Client-ID',
            'required' => true,
            'description' => '<p>PaypalExpress Client-ID.</p>',
        ]
    ];

    const formPayPalExpressTextElementMerchantID = [
        'paypalExpressMerchantID' => [
            'name' => 'paypalExpressMerchantID',
            'type' => 'text',
            'value' => '',
            'label' => 'PaypalExpress - Merchant-ID',
            'required' => true,
            'description' => '<p>PaypalExpress Merchant-ID.</p>',
        ]
    ];
}
