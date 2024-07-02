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
                'description' => '{s name="backend/FatchipCTPayment/merchantid_desc"}Ihre Merchant Id (Benutzername){/s}',
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
                'description' => 'Präfix für Bestellnummern. Sie können folgende Platzhalter verwenden: %transid% , %payid%, %xid%',
            ],
            'suffixOrdernumber' => [
                'name' => 'suffixOrdernumber',
                'type' => 'text',
                'value' => '',
                'label' => 'Bestellnummer Suffix',
                'required' => false,
                'description' => 'Suffix für Bestellnummern. Sie können folgende Platzhalter verwenden: %transid% , %payid%, %xid%',
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
                            'fr_FR' => 'désactiver la journalisation',
                        ]],
                        ['active', [
                            'de_DE' => 'Protokollierung',
                            'en_GB' => 'enable logging',
                            'fr_FR' => 'activer la journalisation',
                        ]],
                        ['extended', [
                            'de_DE' => 'erweiterte Protokollierung',
                            'en_GB' => 'enable extra logging',
                            'fr_FR' => 'activer la journalisation supplémentaire',
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
                            'fr_FR' => 'Blowfish chiffrement (défaut)',
                        ]],
                        ['aes', [
                            'de_DE' => 'AES Verschlüsselung',
                            'en_GB' => 'AES encyption',
                            'fr_FR' => 'AES chiffrement',
                        ]],
                    ],
                'description' => 'Art der verwendeten Verschlüsselung.<BR>
                                  Blowfish Verschlüsselung wird vom Computop Support als Standard eingerichtet.<BR>
                                  Sollte die Blowfish Verschlüsselung (bf-cbc) bei Ihrem Hoster nicht verfügbar sein, wenden Sie sich bitte an den Computop Support und lassen Sie AES aufschalten.<BR>
                                  Wenn seitens Computop AES aktiviert wurde, stellen Sie auf AES um und führen den API Test durch.',
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
                'description' => '<b>IFrame</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in einem IFrame eingegeben<BR>
                                  <b>Silent Mode</b>: Kreditkartendaten werden auf der Seite "Prüfen und Bestellen" eingegeben.<BR>
                                  <b>Payment Page</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in einem blanken Fenster eingegeben<BR>'
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
                        'fr_FR' => 'désactivé',
                    ]],
                    [1, [
                        'de_DE' => 'aktiv',
                        'en_GB' => 'enabled',
                        'fr_FR' => 'activé',
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
                            'fr_FR' => 'désactivé',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                            'fr_FR' => 'activé',
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
                            'fr_FR' => 'désactivé',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                            'fr_FR' => 'activé',
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
                            'fr_FR' => 'désactivé',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                            'fr_FR' => 'activé',
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
                            'fr_FR' => 'automatique',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                            'fr_FR' => 'manuel',
                        ]],
                    ],
                'description' => '<b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR>
                                  <b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
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
                'description' => '<b>GICC</b>: Concardis, B+S Card Service, EVO Payments, American Express, Elavon, SIX Payment Service<BR>
                                  <b>CAPN</b>: American Express<BR>
                                  <b>Omnipay</b>: EMS payment solutions, Global Payments, Paysquare',
            ],
            'creditCardAccVerify' => [
                'name' => 'creditCardAccVerify',
                'type' => 'select',
                'value' => 0,
                'label' => 'Kreditkarte - Kontoverifizierung anfordern ',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        [0, [
                            'de_DE' => 'inaktiv',
                            'en_GB' => 'disabled',
                            'fr_FR' => 'désactivé',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                            'fr_FR' => 'activé',
                        ]],
                    ],
                'description' => 'Indikator für Anforderung einer Kontoverifizierung (alias Nullwert-Authorisierung). <BR>
                                  Bei einer angeforderten Kontoverifizierung ist der übermittelte Betrag optional und <BR>
                                  wird für die tatsächliche Zahlungstransaktion ignoriert (z.B. Autorisierung).',
            ],
            'creditCardSilentModeBrandDetection' => [
                'name' => 'creditCardSilentModeBrandDetection',
                'type' => 'select',
                'value' => 0,
                'label' => 'Kreditkarte - Silent Mode Kartentyp Autoerkennung ',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        [0, [
                            'de_DE' => 'inaktiv',
                            'en_GB' => 'disabled',
                            'fr_FR' => 'désactivé',
                        ]],
                        [1, [
                            'de_DE' => 'aktiv',
                            'en_GB' => 'enabled',
                            'fr_FR' => 'activé',
                        ]],
                    ],
                'description' => 'Wählt anhand der eingebenen Kartennummer automatisch den Karten Typ und zeigt das Kartenlogo an.<BR>',
            ],
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
                'description' => 'Ideal Zahlungen können direkt über Ideal oder über PPRO abgewickelt werden',
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
                            'fr_FR' => 'direct',
                        ]],
                        ['EVO', [
                            'de_DE' => 'EVO Payments',
                            'en_GB' => 'EVO Payments',
                            'fr_FR' => 'EVO Payments',
                        ]],
                        ['INTERCARD', [
                            'de_DE' => 'Intercard',
                            'en_GB' => 'Intercard',
                            'fr_FR' => 'Intercard',
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
                            'fr_FR' => 'automatique',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                            'fr_FR' => 'manuel',
                        ]],
                    ],
                'description' => '<b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR>
                            <b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
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
                            'fr_FR' => 'off',
                        ]],
                        ['An', [
                            'de_DE' => 'An',
                            'en_GB' => 'on',
                            'fr_FR' => 'on',
                        ]],
                    ],
                'description' => 'Stellt im Checkout und im Mein Konto Bereich die Iban anonymisiert dar',
            ],
        ];

    const formLastschriftNumberElements =
        [
        ];

    const formPayDirektTextElements =
        [
            'payDirektShopApiKey' => [
                'name' => 'payDirektShopApiKey',
                'type' => 'text',
                'value' => '',
                'label' => 'Paydirekt - Shop Api Key',
                'required' => false,
                'description' => 'Ihr Paydirekt Api Schlüssel',
            ],
        ];

    const formPayDirektSelectElements =
        [
            'payDirektCaption' => [
                'name' => 'payDirektCaption',
                'type' => 'select',
                'value' => 'AUTO',
                'label' => 'Paydirekt - Capture Modus',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['AUTO', [
                            'de_DE' => 'Automatisch',
                            'en_GB' => 'automatic',
                            'fr_FR' => 'automatique',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                            'fr_FR' => 'manuel',
                        ]],
                    ],
                'description' => '<b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR>
                                  <b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
            ],
        ];

    const formPayDirektNumberElements =
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
                            'fr_FR' => 'automatique',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                            'fr_FR' => 'manuel',
                        ]],
                    ],
                'description' => 'bestimmt, ob der angefragte Betrag sofort oder erst später eingezogen wird. <br>
                                  <b>Wichtig:<br>Bitte kontaktieren Sie den Computop Support für Manual, um die unterschiedlichen Einsatzmöglichkeiten abzuklären.</b>',
            ],
            'paypalSetOrderStatus' => [
                'name' => 'paypalSetOrderStatus',
                'type' => 'select',
                'value' => 'Aus',
                'label' => 'Paypal - Bestellstatus bei Capture',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['Aus', [
                            'de_DE' => 'Aus',
                            'en_GB' => 'off',
                            'fr_FR' => 'off',
                        ]],
                        ['An', [
                            'de_DE' => 'An',
                            'en_GB' => 'on',
                            'fr_FR' => 'on',
                        ]],
                    ],
                'description' => 'Wenn diese Option An ist, dann wird der Bestellstatus bei Paypal Bestellungen,<br>bei denen der Capture fehlschlägt, auf "Klärung notwendig" gesetzt"  <br>',
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
                            'fr_FR' => 'automatique',
                        ]],
                        ['MANUAL', [
                            'de_DE' => 'Manuell',
                            'en_GB' => 'manual',
                            'fr_FR' => 'manuel',
                        ]],
                    ],
                'description' => '<b>Automatisch</b>: Reservierte Beträge werden automatisch eingezogen.<BR>
                                  <b>Manuell</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
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
                'description' => 'Typ des Amazon Buttons<BR>
                                  Das Aussehen der verschiedenen Buttons.<BR>
                                  Klicken Sie links auf den Link "AmazonPay - Button Typ"',
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
                'description' => 'Farbe des Amazon Buttons<BR>
                                  Das Aussehen der verschiedenen Buttons.<BR>
                                  Klicken Sie links auf den Link "AmazonPay - Button Farbe"',
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
                            'fr_FR' => 'petit',
                        ]],
                        ['medium', [
                            'de_DE' => 'mittel',
                            'en_GB' => 'medium',
                            'fr_FR' => 'moyen',
                        ]],
                    ],
                'description' => 'Größe des Amazon Buttons<BR>
                                  Das Aussehen der verschiedenen Buttons.<BR>
                                  Klicken Sie links auf den Link "AmazonPay - Button Größe"',
            ],
        ];

    const formBonitaetElements =
        [
            'bonitaetusereturnaddress' => [
                'name' => 'bonitaetusereturnaddress',
                'type' => 'boolean',
                'value' => false,
                'label' => 'Bonitätsprüfung - Zurückgelieferte Adressdaten verwenden',
                'required' => false,
                'description' => 'Ersetzt die Rechnungsaddresse mit u.U. korrigierten Adressen aus der Bonitätsprüfung',
            ],
            'bonitaetinvalidateafterdays' => [
                'name' => 'bonitaetinvalidateafterdays',
                'type' => 'number',
                'value' => '30',
                'label' => 'Bonitätsprüfung - Gültigkeit der Bonitätsprüfung in Tagen',
                'required' => false,
                'description' => 'Stellen Sie hier ein, wie lange ein bereits durchgeführte Bontitätsprüfung gültig bleibt',
            ],
        ];

    const formBonitaetSelectElements =
        [
            'crifmethod' => [
                'name' => 'crifmethod',
                'type' => 'select',
                'value' => 'inactive',
                'label' => 'CRIF Bonitätsprüfung',
                'required' => false,
                'editable' => false,
                'store' =>
                    [
                        ['inactive', [
                            'de_DE' => 'Inaktiv',
                            'en_GB' => 'inactive',
                            'fr_FR' => 'inactif',
                        ]],
                        ['QuickCheck', [
                            'de_DE' => 'QuickCheck',
                            'en_GB' => 'quick check',
                            'fr_FR' => 'vérification rapide',
                        ]],
                        ['CreditCheck', [
                            'de_DE' => 'Kredit Check',
                            'en_GB' => 'Creditcard check',
                            'fr_FR' => 'Vérification CreditCheck',
                        ]],
                    ],
                'description' => 'führt eine Bonitätsprüfung aus, bevor ein Benutzer Zahlarten auswählen kann.<BR>
                                  Erstellen Sie unter "Einstellungen->Riskmanagement" Regeln mit den Bedingungen<BR>
                                  "Computop Risikoampel IST <Farbe>"<BR>und<BR>
                                  "Computop Risikoampel IST NICHT <Farbe>"<BR>',
            ],
        ];

    const formKlarnaTextElements =
        [
            'klarnaaction' => [
                'name' => 'klarnaaction',
                'type' => 'text',
                'value' => '',
                'label' => 'Klarna Aktionscode',
                'required' => false,
                'description' => 'Der Wert ist von Laufzeiten und Monatsraten abhängig, die Sie mit Klarna vereinbart haben. Dieser Wert kann per Subshop unterschiedlich sein.',
            ],
            'klarnaaccount' => [
                'name' => 'klarnaaccount',
                'type' => 'text',
                'value' => '',
                'label' => 'Klarna Konto',
                'required' => false,
                'description' => 'Das zu benutzende Klarna Konto. <br/><br/>
    
            <b>Die verfügbaren Klarna-Zahlungsarten in Abhängigkeit von der Konfiguration bei Klarna </b><br/>
            Wenn <u>Klarna PayNow</u> aktiviert ist, dann können <u>Klarna Lastschrift</u> und <u>Klarna Sofort</u> nicht aktiviert werden<br/>
    
            Wenn <u>Klarna Lastschrift</u> und/oder <u>Klarna Sofort</u> aktiv ist, dann kann <u>Klarna PayNow</u> nicht aktiviert werden',
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
                'fatchip_computop_apitest_button' => [
                    'label' => '<strong>API Zugang Testen (Erst Speichern und Caches leeren)<strong>',
                ],
                'fatchip_computop_ideal_button' => [
                    'label' => '<strong>iDeal Banken aktualisieren <strong>',
                ],
                'prefixOrdernumber' => [
                    'label' => 'Bestellnummer Präfix',
                    'description' => 'Präfix für Bestellnummern. Sie können folgende Platzhalter verwenden: %transid% , %payid%, %xid%',
                ],
                'suffixOrdernumber' => [
                    'label' => 'Bestellnummer Suffix',
                    'description' => 'Suffix für Bestellnummern. Sie können folgende Platzhalter verwenden: %transid% , %payid%, %xid%',
                ],
                'debuglog' => [
                    'label' => 'Debug Protokoll',
                    'description' => 'Erzeugt eine Log Datei <FatchipCTPayment_.log> mit Debug Ausgaben im Shopware Protokollverzeichnis',
                ],
                'encryption' => [
                    'label' => 'Verschlüsselung',
                    'description' => 'Art der verwendeten Verschlüsselung.<BR>
                                  Blowfish Verschlüsselung wird vom Computop Support als Standard eingerichtet.<BR>
                                  Sollte die Blowfish Verschlüsselung (bf-cbc) bei Ihrem Hoster nicht verfügbar sein, wenden Sie sich bitte an den Computop Support und lassen Sie AES aufschalten.<BR>
                                  Wenn seitens Computop AES aktiviert wurde, stellen Sie auf AES um und führen den API Test durch.',
                ],
                'creditCardMode' => [
                    'label' => 'Kreditkarte - Modus',
                    'description' => '<b>IFrame</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in ein IFrame eingegeben<BR>
                                  <b>Silent Mode</b>: Kreditkartendaten werden auf der Seite "Prüfen und Bestellen" eingegeben.<BR>
								  <b>Payment Page</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in einem blanken Fenster eingegeben<BR>',
                ],
                'creditCardTestMode' => [
                    'label' => 'Kreditkarte - Test-Modus',
                    'description' => '<b>IFrame</b>: Kreditkartendaten werden nach klick auf "Zahlungsplichtig bestellen" in ein IFrame eingegeben<BR>
                                  <b>Silent Mode</b>: Kreditkartendaten werden auf der Seite "Prüfen und Bestellen" eingegeben.<BR>',
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
                    'description' => '<b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR>
                                  <b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
                ],
                'creditCardAcquirer' => [
                    'label' => 'Kreditkarte - Acquirer',
                    'description' => '<b>GICC</b>: Concardis, B+S Card Service, EVO Payments, American Express, Elavon, SIX Payment Service<BR>
                                  <b>CAPN</b>: American Express<BR>
                                  <b>Omnipay</b>: EMS payment solutions, Global Payments, Paysquare',
                ],
                'creditCardAccVerify' => [
                    'label' => 'Kreditkarte - Kontoverifizierung anfordern',
                    'description' => 'Indikator für Anforderung einer Kontoverifizierung (alias Nullwert-Authorisierung). <BR>
                                  Bei einer angeforderten Kontoverifizierung ist der übermittelte Betrag optional und <BR>
                                  wird für die tatsächliche Zahlungstransaktion ignoriert (z.B. Autorisierung).',
                ],
                'creditCardSilentModeBrandDetection' => [
                    'label' => 'Kreditkarte - Silent Mode Kartentyp Autoerkennung',
                    'description' => 'Wählt anhand der eingebenen Kartennummer automatisch den Karten Typ und zeigt das Kartenlogo an.<BR>',
                ],
                'creditCardTemplate' => [
                    'label' => 'Kreditkarte - Template Name',
                    'description' => 'Name der XSLT-Datei mit Ihrem individuellen Layout für das Bezahlformular. Wenn Sie das Responsive Computop-Template für mobile Endgeräte nutzen möchten, übergeben Sie den Templatenamen „ct_responsive“.',
                ],
                'idealDirektOderUeberSofort' => [
                    'label' => 'iDEAL - Dienst',
                    'description' => 'Ideal Zahlungen können direkt über Ideal oder über PPRO abgewickelt werden',
                ],
                'lastschriftDienst' => [
                    'label' => 'Lastschrift - Dienst',
                    'description' => 'Lastschrift Zahlungen können direkt, über EVO oder über INTERCARD abgewickelt werden.',
                ],
                'lastschriftCaption' => [
                    'label' => 'Lastschrift - Capture Modus',
                    'description' => '<b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR>
                            <b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
                ],
                'lastschriftAnon' => [
                    'label' => 'Iban anonymisieren',
                    'description' => 'Stellt im Checkout und im Mein Konto Bereich die Iban anonymisiert dar',
                ],
                'payDirektShopApiKey' => [
                    'label' => 'Paydirekt - Shop Api Key',
                    'description' => 'Ihr Paydirekt Api Schlüssel',
                ],
                'payDirektCaption' => [
                    'label' => 'Paydirekt - Capture Modus',
                    'description' => '<b>AUTO</b>: Reservierte Beträge werden sofort automatisch eingezogen.<BR>
                                  <b>MANUAL</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
                ],
                'paypalCaption' => [
                    'label' => 'Paypal - Capture Modus',
                    'description' => 'bestimmt, ob der angefragte Betrag sofort oder erst später eingezogen wird. <br>
                                  <b>Wichtig:<br>Bitte kontaktieren Sie den Computop Support für Manual, um die unterschiedlichen Einsatzmöglichkeiten abzuklären.</b>',
                ],
                'paypalSetOrderStatus' => [
                    'label' => 'Paypal - Bestellstatus bei Capture',
                    'description' => 'Wenn diese Option An ist, dann wird der Bestellstatus bei Paypal Bestellungen,<br>bei denen der Capture fehlschlägt, auf "Klärung notwendig" gesetzt"  <br>',
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
                    'description' => '<b>Automatisch</b>: Reservierte Beträge werden automatisch eingezogen.<BR>
                                  <b>Manuell</b>: Geldeinzüge werden von Ihnen manuell im Shopbackend durchgeführt.',
                ],
                'amazonButtonType' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_TYPE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Typ</a>',
                    'description' => 'Typ des Amazon Buttons<BR>
                                  Das Aussehen der verschiedenen Buttons.<BR>
                                  Klicken Sie links auf den Link "AmazonPay - Button Typ"',
                ],
                'amazonButtonColor' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_COLOR_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Farbe</a>',
                    'description' => 'Farbe des Amazon Buttons<BR>
                                  Das Aussehen der verschiedenen Buttons.<BR>
                                  Klicken Sie links auf den Link "AmazonPay - Button Farbe"',
                ],
                'amazonButtonSize' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_SIZE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Button Größe</a>',
                    'description' => 'Größe des Amazon Buttons<BR>
                                  Das Aussehen der verschiedenen Buttons.<BR>
                                  Klicken Sie links auf den Link "AmazonPay - Button Größe"',
                ],
                'bonitaetusereturnaddress' => [
                    'label' => 'Bonitätsprüfung - Zurückgelieferte Adressdaten verwenden',
                    'description' => 'Ersetzt die Rechnungsaddresse mit u.U. korrigierten Adressen aus der Bonitätsprüfung',
                ],
                'bonitaetinvalidateafterdays' => [
                    'label' => 'Bonitätsprüfung - Gültigkeit der Bonitätsprüfung in Tagen',
                    'description' => 'Stellen Sie hier ein, wie lange ein bereits durchgeführte Bontitätsprüfung gültig bleibt',
                ],
                'crifmethod' => [
                    'label' => 'CRIF Bonitätsprüfung',
                    'description' => 'führt eine Bonitätsprüfung aus, bevor ein Benutzer Zahlarten auswählen kann.<BR>
                                  Erstellen Sie unter "Einstellungen->Riskmanagement" Regeln mit den Bedingungen<BR>
                                  "Computop Risikoampel IST <Farbe>"<BR>und<BR>
                                  "Computop Risikoampel IST NICHT <Farbe>"<BR>',
                ],
                'klarnaaction' => [
                    'label' => 'Klarna Aktionscode',
                    'description' => 'Der Wert ist von Laufzeiten und Monatsraten abhängig, die Sie mit Klarna vereinbart haben. Dieser Wert kann per Subshop unterschiedlich sein.',
                ],
                'klarnaaccount' => [
                    'label' => 'Klarna Konto',
                    'description' => 'Das zu benutzende Klarna Konto. <br/><br/>
                                    <b>Die verfügbaren Klarna-Zahlungsarten in Abhängigkeit von der Konfiguration bei Klarna </b><BR>
                                    Wenn <u>Klarna PayNow</u> aktiviert ist, dann können <u>Klarna Lastschrift</u> und <u>Klarna Sofort</u> nicht aktiviert werden<BR>
                                    Wenn <u>Klarna Lastschrift</u> und/oder <u>Klarna Sofort</u> aktiv ist, dann kann <u>Klarna PayNow</u> nicht aktiviert werden',
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
                'fatchip_computop_apitest_button' => [
                    'label' => '<strong>test API credentials (Save and clear cache first)<strong>',
                ],
                'fatchip_computop_ideal_button' => [
                    'label' => '<strong>update iDeal banks<strong>',
                ],
                'prefixOrdernumber' => [
                    'label' => 'Ordernumber prefix',
                    'description' => 'Prefix for ordernumbers. You can use the following placeholders: %transid% , %payid%, %xid%',
                ],
                'suffixOrdernumber' => [
                    'label' => 'Ordernumber suffix',
                    'description' => 'Suffix for ordernumbers. You can use the following placeholders: %transid% , %payid%, %xid%',
                ],
                'debuglog' => [
                    'label' => 'Debug protocol',
                    'description' => 'Creates a log file <FatchipCTPayment_.log> with debugging output on the shopware log folder',
                ],
                'encryption' => [
                    'label' => 'Encyption',
                    'description' => 'Type of encryption used.<br>
                                  Blowfish encryption is set up by Computop Support as a standard. If Blowfish encryption (bf-cbc) is not available from your hoster, please contact Computop Support and have AES activated.<BR>
                                  If Computop has activated AES, switch to AES and run the API test.',
                ],
                'creditCardMode' => [
                    'label' => 'Creditcard - Mode',
                    'description' => '<b>IFrame</b>: The creditcard form will be displayed after clicking "confirm payment" in an iframe<BR>
                                  <b>Silent Mode</b>: The creditcard form will be displayed on the "complete order" page.<BR>
                                  <b>Payment Page</b>: Credit card details are entered in a blank page after clicking on "Order payment".',
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
                    'description' => '<b>AUTO</b>: Reserved amounts will be captured automatically.<BR>
                                  <b>MANUAL</b>: Reserverd amounts have to be captured manuelly in the shop backend.',
                ],
                'creditCardAcquirer' => [
                    'label' => 'Creditcard - Acquirer',
                    'description' => '<b>GICC</b>: Concardis, B+S Card Service, EVO Payments, American Express, Elavon, SIX Payment Service<BR>
                                  <b>CAPN</b>: American Express<BR>
                                  <b>Omnipay</b>: EMS payment solutions, Global Payments, Paysquare',
                ],
                'creditCardAccVerify' => [
                    'label' => 'Creditcard - Account verification',
                    'description' => 'Indicator for an account verification request (alias Null-Auth).<BR>
                                  By using an account verification request the amount is optional and<BR>
                                  will be ignored for the real payment (E.g. authorization).',
                ],
                'creditCardSilentModeBrandDetection' => [
                    'label' => 'Creditcard - Silent Mode card type autodetection',
                    'description' => 'Chooses automatically the card type when entering the creditcard number and shows the creditcard logo.<BR>',
                ],
                'creditCardTemplate' => [
                    'label' => 'Creditcard - Template name',
                    'description' => 'Name of the XSLT-file with your individual payment form layout. If you want to use the responsive computop template for mobile devices, please use the template name „ct_responsive“.',
                ],
                'idealDirektOderUeberSofort' => [
                    'label' => 'iDEAL - Service',
                    'description' => 'iDEAL payments can be handled by using direct (Sofort) or PPRO.',
                ],
                'lastschriftDienst' => [
                    'label' => 'Direct debit - Service',
                    'description' => 'Direct debit payments can be handled by using direct, EVO or INTERCARD',
                ],
                'lastschriftCaption' => [
                    'label' => 'Direct debit - Capture Mode',
                    'description' => '<b>AUTO</b>: Reserved amounts will be captured automatically.<BR>
                                  <b>MANUAL</b>: Reserverd amounts have to be captured manuelly in the shop backend.',
                ],
                'lastschriftAnon' => [
                    'label' => 'Anonymize IBAN',
                    'description' => 'The customers IBAN will be displayed anonymized in checkout and on the my accoutn page',
                ],
                'payDirektShopApiKey' => [
                    'label' => 'Paydirekt - Shop Api Key',
                    'description' => 'Your Paydirekt Api Key',
                ],
                'payDirektCaption' => [
                    'label' => 'Paydirekt - Capture Modus',
                    'description' => '<b>AUTO</b>: Reserved amounts will be captured automatically.<BR>
                                  <b>MANUAL</b>: Reserverd amounts have to be captured manuelly in the shop backend.',
                ],
                'paypalCaption' => [
                    'label' => 'Paypal - Capture Modus',
                    'description' => 'capture reserverd amounts now or manually later. <br>
                                  <b>Important:<br>Please contact the computop customer support before using the manual setting to clarify possible use cases .</b>',
                ],
                'paypalSetOrderStatus' => [
                    'label' => 'Paypal - Order status on captures',
                    'description' => 'If this option is enabled, the orderstatus of failed paypal captures will be set to "review necessary"<br>',
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
                    'description' => '<b>AUTO</b>: Reserved amounts will be captured automatically.<BR>
                                  <b>MANUAL</b>: Reserverd amounts have to be captured manuelly in the shop backend.',
                ],
                'amazonButtonType' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_TYPE_PARAMETER" target="_blank" rel="noopener">AmazonPay - Button Type</a>',
                    'description' => 'Type of the Amazon button<BR>
                                  The look of the different buttons.<BR>
                                  Please click on the left link "AmazonPay - Button Type"',
                ],
                'amazonButtonColor' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_COLOR_PARAMETER" target="_blank" rel="noopener">AmazonPay - Button Color</a>',
                    'description' => 'Color of the Amazon button<BR>
                                  The look of the different button.<BR>
                                  Please click on the left link "AmazonPay - Button Color"',
                ],
                'amazonButtonSize' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_SIZE_PARAMETER" target="_blank" rel="noopener">AmazonPay - Button Size</a>',
                    'description' => 'Size of the amazon button<BR>
                                  The look of the different button.<BR>
                                  Please click on the left link "AmazonPay - Button Size"',
                ],
                'bonitaetusereturnaddress' => [
                    'label' => 'Credit check  - Use customer addresses from credit check response',
                    'description' => 'Replaces the customers addresses with the addresses from the credit check response',
                ],
                'bonitaetinvalidateafterdays' => [
                    'label' => 'Credit check - Validity of the credit check in days',
                    'description' => 'Set how many days a credit check stays valid for a customer befor re-cehecking',
                ],
                'crifmethod' => [
                    'label' => 'CRIF Credit check',
                    'description' => 'checks credit worthiness before displaying possible payments.<BR>
                                  Create your own rules in "Riskmanagement" with the conditions <BR>
                                  "Computop Traffic light IS <Color>"<BR>and<BR>
                                  "Computop Traffic light IST NOT <Color>"<BR>',
                ],
                'klarnaaction' => [
                    'label' => 'Klarna action code',
                    'description' => 'This value depends on durations and monthly rates term you arranged with Klarna. This value can be different for each subshop.',
                ],
                'klarnaaccount' => [
                    'label' => 'Klarna Account',
                    'description' => 'Your Klarna account. <br/><br/>
                                    <b>Available Klarna Payments depending on your Klarna account configuration</b><BR>
                                    If <u>Klarna PayNow</u> is active, you cant activate <u>Klarna Direct debit</u> und <u>Klarna Sofort</u><BR>
                                    if <u>Klarna Direct debit</u> and/or <u>Klarna Sofort</u> are active, then <u>Klarna PayNow</u> can not be activated',
                ],
            ],
            'fr_FR' => [
                'merchantID' => [
                    'label' => 'MerchantID',
                    'description' => 'Votre MerchantID',
                ],
                'mac' => [
                    'label' => 'MAC',
                    'description' => 'Votre clé HMAC',
                ],
                'blowfishPassword' => [
                    'label' => 'Mot de passe Blowfish',
                    'description' => 'Votre mot de passe de cryptage',
                ],
                'fatchip_computop_apitest_button' => [
                    'label' => '<strong>Tester les données d`accès API (Enregistrer d`abord et vider le cache)<strong>',
                ],
                'fatchip_computop_ideal_button' => [
                    'label' => '<strong>Actualiser les iDeal banques<strong>',
                ],
                'prefixOrdernumber' => [
                    'label' => 'Préfixe de numéro de commande',
                    'description' => 'Préfixe pour les numéros de commande. Vous pouvez utiliser les paramètres fictifs suivants: %transid% , %payid%, %xid%',
                ],
                'suffixOrdernumber' => [
                    'label' => 'Suffixe de numéro de commande',
                    'description' => 'Suffixe pour les numéros de commande. Vous pouvez utiliser les paramètres fictifs suivants: %transid% , %payid%, %xid%',
                ],
                'debuglog' => [
                    'label' => 'Journalisation de déboggage',
                    'description' => 'Créer un journal <FatchipCTPayment_.log> avec les traces de déboggage dans le dossier de journaux de shopware',
                ],
                'encryption' => [
                    'label' => 'Chiffrement',
                    'description' => 'Type de chiffrement utilisé.<br>
                                  Le chiffrement Blowfish est défini comme standard par le support Computop. Si le chiffrement Blowfish (bf-cbc) n\'est pas disponible chez votre hébergeur, veuillez contacter le support Computop and activer AES.<BR>
                                  Si Computop a activé AES, basculez vers AES et executez le test de l\'API.',
                ],
                'creditCardMode' => [
                    'label' => 'Carte de crédit - Mode',
                    'description' => '<b>IFrame</b>: Le formulaire sera affiché après avoir cliqué sur "Confirmer le paiement" dans un iframe<BR>
                                  <b>Mode silencieux</b>: Le formulaire sera affiché sur la page "Vérifier et Commander".<BR>
                                  <b>Payment Page</b>: Les coordonnées de la carte de crédit sont saisies dans une fenêtre vierge après avoir cliqué sur "Commande avec obligation de paiement".',
                ],
                'creditCardTestMode' => [
                    'label' => 'Carte de crédit - Mode test',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsVisa' => [
                    'label' => 'Carte de crédit - Visa (Mode silencieux)',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsMaster' => [
                    'label' => 'Carte de crédit - MasterCard (Mode silencieux)',
                    // 'description' => '',
                ],
                'creditCardSilentModeBrandsAmex' => [
                    'label' => 'Carte de crédit - American Express (Mode silencieux)',
                    // 'description' => '',
                ],
                'creditCardCaption' => [
                    'label' => 'Carte de crédit - Mode de débit',
                    'description' => '<b>AUTO</b>: Les montants réservés seront débités automatiquement.<BR>
                                  <b>MANUEL</b>: Les montants réservés doivent être débités manuellement via l\'administration de la boutique.',
                ],
                'creditCardAcquirer' => [
                    'label' => 'Carte de crédit - Acquéreur',
                    'description' => '<b>GICC</b>: Concardis, B+S Card Service, EVO Payments, American Express, Elavon, SIX Payment Service<BR>
                                  <b>CAPN</b>: American Express<BR>
                                  <b>Omnipay</b>: EMS payment solutions, Global Payments, Paysquare',
                ],
                'creditCardAccVerify' => [
                    'label' => 'Carte de crédit - Vérification de compte',
                    'description' => 'Indicateur pour une requête de vérification de compte (alias Null-Auth).<BR>
                                  En utilisant une demande de vérification de compte, le montant est<BR>
                                  facultatif et sera ignoré pour le paiement réel (Exemple, autorisation).',
                ],
                'creditCardSilentModeBrandDetection' => [
                    'label' => 'Carte de crédit - Mode silencieux détection automatique du type de carte',
                    'description' => 'Choisit automatiquement le type de carte lors de la saisie du numéro, et affiche le logo de la carte de crédit.<BR>',
                ],
                'creditCardTemplate' => [
                    'label' => 'Carte de crédit - Nom du modèle',
                    'description' => 'Nom du fichier XSLT avec votre disposition de formulaire de paiment individuel. Si vous souhaitez utiliser le modèle computop adaptable pour les appareils mobiles, veuillez utiliser le nom de modèle „ct_responsive“.',
                ],
                'idealDirektOderUeberSofort' => [
                    'label' => 'iDEAL - Service',
                    'description' => 'iDEAL payments can be handled by using direct (Sofort) or PPRO.',
                    'description' => 'Les paiements iDEAL peuvent être gérés en utilisant direct (Sofort) ou PPRO.',
                ],
                'lastschriftDienst' => [
                    'label' => 'Direct debit - Service',
                    'description' => 'Les paiements par prélèvement peuvent être gérés en utilisant direct, EVO ou INTERCARD.',
                ],
                'lastschriftCaption' => [
                    'label' => 'Direct debit - Mode de débit',
                    'description' => '<b>AUTO</b>: Les montants réservés seront débités automatiquement.<BR>
                                  <b>MANUEL</b>: Les montants réservés doivent être débités manuellement via l\'administration de la boutique.',
                ],
                'lastschriftAnon' => [
                    'label' => 'Anonymiser l\'IBAN',
                    'description' => 'L\'IBAN du client sera affiché de manière anonyme durant la transaction et sur la page \'Mon compte\'.',
                ],
                'payDirektShopApiKey' => [
                    'label' => 'Paydirekt - Clé API',
                    'description' => 'Votre clé API Paydirekt',
                ],
                'payDirektCaption' => [
                    'label' => 'Paydirekt - Mode de débit',
                    'description' => '<b>AUTO</b>: Les montants réservés seront débités automatiquement.<BR>
                                  <b>MANUEL</b>: Les montants réservés doivent être débités manuellement via l\'administration de la boutique.',
                ],
                'paypalCaption' => [
                    'label' => 'Paypal - Mode de débit',
                    'description' => 'Les montants réservés seront débités maintenant ou manuellement plus tard. <br>
                                  <b>Important:<br>Veuillez contacter le service client computop avant d\'utiliser le paramètre manuel, pour clarifier les possibles cas d\'usage.</b>',
                ],
                'paypalSetOrderStatus' => [
                    'label' => 'Paypal - Statut de la commande sur les débits',
                    'description' => 'Si cette option est activée, le statut de commande d\'un débit Paypal échoué sera converti en "révision nécessaire"<br>',
                ],
                'amazonSellerId' => [
                    'label' => 'AmazonPay - SellerId',
                    'description' => 'Voire SellerId Amazonpay',
                ],
                'amazonClientId' => [
                    'label' => 'AmazonPay - ClientId',
                    'description' => 'Votre ClientId Amazonpay',
                ],
                'amazonLiveMode' => [
                    'label' => 'Mode Amazon',
                    'description' => 'Utiliser AmazonPay en mode live ou test',
                ],
                'amazonCaptureType' => [
                    'label' => 'Amazon Mode de débit',
                    'description' => '<b>AUTO</b>: Les montants réservés seront débités automatiquement.<BR>
                                  <b>MANUEL</b>: Les montants réservés doivent être débités manuellement via l\'administration de la boutique.',
                ],
                'amazonButtonType' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_TYPE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Type de bouton</a>',
                    'description' => 'Type du bouton Amazon<BR>
                                  L\'aspect des différents boutons.<BR>
                                  Veuillez cliquer sur le lien à gauche "AmazonPay - Type de bouton"',
                ],
                'amazonButtonColor' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_COLOR_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Couleur de bouton</a>',
                    'description' => 'Couleur du bouton Amazon<BR>
                                  L\'aspect des différents boutons.<BR>
                                  Veuillez cliquer sur le lien à gauche "AmazonPay - Couleur de bouton"',
                ],
                'amazonButtonSize' => [
                    'label' => '<a href="https://pay.amazon.com/de/developer/documentation/lpwa/201952050#ENTER_SIZE_PARAMETER" target="_blank" rel="noopener" >AmazonPay - Taille de bouton</a>',
                    'description' => 'Taille du bouton Amazon<BR>
                                  L\'aspect des différents boutons.<BR>
                                  Veuillez cliquer sur le lien à gauche "AmazonPay - Taille de bouton"',
                ],
                'bonitaetusereturnaddress' => [
                    'label' => 'Vérification de crédit  - Utiliser l\'adresse du client de la vérification de crédit',
                    'description' => 'Remplace l\'adresse du client par celle reçue dans la réponse de vérification de crédit',
                ],
                'bonitaetinvalidateafterdays' => [
                    'label' => 'Vérification de crédit - Validité de la vérification de crédit en jours',
                    'description' => 'Définir combien de jours une vérification de crédit reste valide pour un client avant re-vérification',
                ],
                'crifmethod' => [
                    'label' => 'Vérification de crédit CRIF',
                    'description' => 'Vérifie la validité du crédit avant d\'afficher les paiements disponibles.<BR>
                                  Créez vos propre règles dans "Gestion de risque" avec les conditions <BR>
                                  "Computop Traffic light IS <Color>"<BR>et<BR>
                                  "Computop Traffic light IST NOT <Color>"<BR>',
                ],
                'klarnaaction' => [
                    'label' => 'Code action Klarna',
                    'description' => 'Cette valeur dépend de la durée et des termes de taux mensuels que vous avez convenus avec Klarna. Cette valeur peut différer entre chaque sous-boutique.',
                ],
                'klarnaaccount' => [
                    'label' => 'Compte Klarna',
                    'description' => 'Votre compte Klarna. <br/><br/>
                                    <b>Paiement Klarna disponibles selon la configuration de votre compte Klarna</b><BR>
                                    Si <u>Klarna PayNow</u> est actif, vous ne pouvez pas activer <u>Klarna Direct debit</u> et <u>Klarna Sofort</u><BR>
                                    si <u>Klarna Direct debit</u> et/ou <u>Klarna Sofort</u> sont actifs, alors <u>Klarna PayNow</u> ne peut pas être activé',
                ],
            ],
        ];
}
