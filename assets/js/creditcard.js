document.addEventListener('DOMContentLoaded', (event) => {
    var javaScriptEnabled = 'true';
    var javaEnabled=navigator.javaEnabled();
    var screenHeight = screen.height;
    var screenWidth = screen.width;
    var colorDepth = screen.colorDepth;
    var date = new Date();
    var timeZoneOffset = date.getTimezoneOffset();

    console.log('JavaScriptEnabled:');
    console.log(javaScriptEnabled);
    console.log('JavaEnabled:');
    console.log(javaEnabled);
    console.log('screenHeight:');
    console.log(screenHeight);
    console.log('screenWidth:');
    console.log(screenWidth);
    console.log('ColorDepth:');
    console.log(colorDepth);
    console.log('timeZoneOffset:');
    console.log(timeZoneOffset);
/*

    var javascriptEnabledInput = document.getElementById('fatchip_computop_order_javascriptEnabled');
    javascriptEnabledInput.value = javaScriptEnabled;

    var javaEnabledInput = document.getElementById('fatchip_computop_order_javaEnabled');
    javaEnabledInput.value = javaEnabled;

    var screenHeightInput = document.getElementById('fatchip_computop_order_screenHeight');
    screenHeightInput.value = screenHeight;

    var screenWidthInput = document.getElementById('fatchip_computop_order_screenWidth');
    screenWidthInput.value = screenWidth;

    var colorDepthInput = document.getElementById('fatchip_computop_order_colorDepth');
    colorDepthInput.value = colorDepth;

    var timeZoneOffsetInput = document.getElementById('fatchip_computop_order_timeZoneOffset');
    timeZoneOffsetInput.value = timeZoneOffset;*/
    const cardNumberInput = document.getElementById('card-number');
    const cardTypeIcon = document.querySelector('.card-type-icon');
    const cardTypeInput = document.getElementById('card-type'); // Get the hidden input element
    const cardTypes = {
        VISA: /^4[0-9]{12}(?:[0-9]{3})?$/,
        MASTERCARD: /^5[1-5][0-9]{14}$/,
    };

    cardNumberInput.addEventListener('input', () => {
        let cardType = '';
        for (const type in cardTypes) {
            if (cardTypes[type].test(cardNumberInput.value)) {
                cardType = type;
                break;
            }
        }

        cardTypeIcon.innerHTML = cardType ? `<i class="fa fa-cc-${cardType}"></i>` : '';
    });

    const computopForm = document.getElementById('computopSilentPostForm');

    if (computopForm) {
        computopForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const expiryDateInput = document.getElementById('expiry-date');
            const expiryDateValue = expiryDateInput.value;
            const stokenInput = computopForm.querySelector('input[name="stoken"]');
            const lang = computopForm.querySelector('input[name="lang"]');
            const sDeliveryAddressMD5 = computopForm.querySelector('input[name="sDeliveryAddressMD5"]');
            const stokenValue = stokenInput ? stokenInput.value : ''; //
            const sDeliveryAddressMD5Value = sDeliveryAddressMD5 ? sDeliveryAddressMD5.value : ''; //
            // Convert expiry date from MM/YYYY to YYYYMM
            const [month, year] = expiryDateValue.split('/');
            // Set the formatted expiry date back to the input field
            const urlParams = new URLSearchParams({
                'cl': 'order',
                'fnc': 'creditCardSilent',
                'stoken': stokenValue,
                'sDeliveryAddressMD5': sDeliveryAddressMD5Value,
            });

            expiryDateInput.value = year + month;
            fetch('https://www.dannyddev.ngrok.pizza/index.php?' + urlParams.toString())
                .then(response => response.json())
                .then(data => {
                    if (data && data.Data && data.Len) {
                        // Fill hidden fields with values
                        computopForm.querySelector('input[name="Data"]').value = data.Data;
                        computopForm.querySelector('input[name="Len"]').value = data.Len;
                        computopForm.querySelector('input[name="MerchantID"]').value = data.MerchantID;
                        if (stokenInput) {
                            stokenInput.remove();
                        }
                        if (lang) {
                            lang.remove();
                        }
                        if (sDeliveryAddressMD5) {
                            sDeliveryAddressMD5.remove();
                        }
                        // Submit form automatically or trigger any other action
                        computopForm.submit();
                    } else {
                        console.error('Invalid response format or missing data.');
                        // Handle the error appropriately (e.g., show a message to the user)
                    }
                })
                .catch(error => {
                    console.error('Error fetching captured amount:', error);
                    // Handle the error appropriately
                });
        });
    } else {
        console.error('Computop form or fields container not found!');
    }
});
