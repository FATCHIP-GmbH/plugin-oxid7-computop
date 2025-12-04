;(function() {
    window.FatchipComputopIbanComponent = {

        ibanbicReg: /^[A-Z0-9 ]+$/,
        errorMessageClass: 'alert-danger',
        fatchipComputopIbanWrongCharacterMessage: 'Dieses Feld darf nur Großbuchstaben und Ziffern enthalten',
        fatchipComputopIbanWrongLengthMessage: 'Bitte prüfen Sie die Länge der IBAN',
        fatchipComputopIbanWrongCecksumMessage: 'Die Prüfsumme der IBAN ist falsch',
        fatchipComputopIbanField: null,
        fatchipComputopFieldEmpty: 'Bitte füllen Sie das Pflichtfeld aus',

        init: function () {
            this.registerEvents();
        },

        registerEvents: function () {
            var paymentId =  document.querySelector('input[name=paymentid]:checked').value;
            var elem = document.getElementById('fatchip_computop_lastschrift_iban');

            const buttons = document.getElementsByClassName("btn btn-highlight btn-lg w-100");
            const submitButton = Array.prototype.filter.call(
                buttons,
                (testElement) => testElement.type === "button",
            );

            if (paymentId !== 'fatchip_computop_lastschrift_iban') {
                submitButton[0].disabled = false;
                ibanError = false;
            }

            elem.onkeyup = function () {
                var errorElem = document.getElementById('fatchip_computop_error_message');
                if (errorElem != null) {
                    document.getElementById('fatchip_computop_error_message').remove();
                }
                var check = FatchipComputopIbanComponent.isValidIBANNumber(elem.value);
                if (elem.value && !FatchipComputopIbanComponent.ibanbicReg.test(elem.value)) {
                    elem.insertAdjacentHTML('afterend', '<div id="fatchip_computop_error_message" class="'+ FatchipComputopIbanComponent.errorMessageClass + '" ><p>' + FatchipComputopIbanComponent.fatchipComputopIbanWrongCharacterMessage +'</p></div>');
                    submitButton[0].disabled = true;
                    ibanError = true;
                } else if (elem.value && check === false) {
                    elem.insertAdjacentHTML('afterend', '<div id="fatchip_computop_error_message" class="'+ FatchipComputopIbanComponent.errorMessageClass + '" ><p>' + FatchipComputopIbanComponent.fatchipComputopIbanWrongLengthMessage +'</p></div>');
                    submitButton[0].disabled = true;
                    ibanError = true;
                } else if (elem.value && check !== 1) {
                    elem.insertAdjacentHTML('afterend', '<div id="fatchip_computop_error_message" class="'+ FatchipComputopIbanComponent.errorMessageClass + '" ><p>' + FatchipComputopIbanComponent.fatchipComputopIbanWrongCecksumMessage +'</p></div>');
                    submitButton[0].disabled = true;
                    ibanError = true;
                } else {
                    if (document.getElementById('fatchip_computop_error_message')) {
                        document.getElementById('fatchip_computop_error_message').remove();
                    }
                    submitButton[0].disabled = false;
                }
            };

            // check pre-filled input field
            if (elem !== null && elem.value !== '') {
                elem.onkeyup();
            }
        },

        isValidIBANNumber: function (input) {
            var CODE_LENGTHS = {
                AD: 24, AE: 23, AT: 20, AZ: 28, BA: 20, BE: 16, BG: 22, BH: 22, BR: 29,
                CH: 21, CR: 21, CY: 28, CZ: 24, DE: 22, DK: 18, DO: 28, EE: 20, ES: 24,
                FI: 18, FO: 18, FR: 27, GB: 22, GI: 23, GL: 18, GR: 27, GT: 28, HR: 21,
                HU: 28, IE: 22, IL: 23, IS: 26, IT: 27, JO: 30, KW: 30, KZ: 20, LB: 28,
                LI: 21, LT: 20, LU: 20, LV: 21, MC: 27, MD: 24, ME: 22, MK: 19, MR: 27,
                MT: 31, MU: 30, NL: 18, NO: 15, PK: 24, PL: 28, PS: 29, PT: 25, QA: 29,
                RO: 24, RS: 22, SA: 24, SE: 24, SI: 19, SK: 24, SM: 27, TN: 24, TR: 26,
                AL: 28, BY: 28, EG: 29, GE: 22, IQ: 23, LC: 32, SC: 31, ST: 25,
                SV: 28, TL: 23, UA: 29, VA: 22, VG: 24, XK: 20
            };
            var iban = String(input).toUpperCase().replace(/[^A-Z0-9]/g, ''), // keep only alphanumeric characters
                code = iban.match(/^([A-Z]{2})(\d{2})([A-Z\d]+)$/), // match and capture (1) the country code, (2) the check digits, and (3) the rest
                digits;
            // check syntax and length
            if (!code || iban.length !== CODE_LENGTHS[code[1]]) {
                return false;
            }
            // rearrange country code and check digits, and convert chars to ints
            digits = (code[3] + code[1] + code[2]).replace(/[A-Z]/g, function (letter) {
                return letter.charCodeAt(0) - 55;
            });
            // final check
            return this.mod97(digits);
        },
        mod97: function (string) {
            var checksum = string.slice(0, 2), fragment;
            for (var offset = 2; offset < string.length; offset += 7) {
                fragment = String(checksum) + string.substring(offset, offset + 7);
                checksum = parseInt(fragment, 10) % 97;
            }
            return checksum;
        },
    };
})()
FatchipComputopIbanComponent.init();
var ibanError = false;

window.onload = (event) => {
    const buttons = document.getElementsByClassName("btn btn-highlight btn-lg w-100");
    const submitButton = Array.prototype.filter.call(
        buttons,
        (testElement) => testElement.type === "button",
    );
    submitButton[0].disabled = false;
    submitButton[0].addEventListener("click", function(event){
        if (ibanError) {
            event.preventDefault();
        } else {
            submitButton[0].disabled = false;
        }
    });

    var radioButtons = document.getElementsByName('paymentid');
    for (var i = 0; i < radioButtons.length; i++) {
        var button = radioButtons[i];
        button.onclick = function() {
            if (document.querySelector('input[name=paymentid]:checked').value !== 'fatchip_computop_lastschrift') {
                submitButton[0].disabled = false;
                ibanError = false;
            };
        }
    }
};