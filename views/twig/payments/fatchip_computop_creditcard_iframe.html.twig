<div class="payment-option">
    {% set dynvalue = oView.getDynValue() %}
    <div class="payment-option-form">
        <input class="form-check-input" id="payment_{{ sPaymentID }}" type="radio" name="paymentid"
               value="{{ sPaymentID }}"{% if oView.getCheckedPaymentId() == paymentmethod.oxpayments__oxid.value %} checked{% endif %}>
        <label
                for="payment_{{ sPaymentID }}">{{ paymentmethod.oxpayments__oxdesc.value }}</label>

        <div class="payment-option-info{% if oView.getCheckedPaymentId() == paymentmethod.oxpayments__oxid.value %} activePayment{% endif %}">
            <div class="hidden">
                <input id="payment_{{ sPaymentID }}_javascriptEnabled" type="hidden" maxlength="64"
                       name="dynvalue[{{ sPaymentID }}_javascriptEnabled]" value="">
                <input id="payment_{{ sPaymentID }}_javaEnabled" type="hidden" maxlength="64"
                       name="dynvalue[{{ sPaymentID }}_javaEnabled]" value="">
                <input id="payment_{{ sPaymentID }}_screenHeight" type="hidden" maxlength="64"
                       name="dynvalue[{{ sPaymentID }}_screenHeight]" value="">
                <input id="payment_{{ sPaymentID }}_screenWidth" type="hidden" maxlength="64"
                       name="dynvalue[{{ sPaymentID }}_screenWidth]" value="">
                <input id="payment_{{ sPaymentID }}_colorDepth" type="hidden" maxlength="64"
                       name="dynvalue[{{ sPaymentID }}_colorDepth]" value="">
                <input id="payment_{{ sPaymentID }}_timeZoneOffset" type="hidden" maxlength="64"
                       name="dynvalue[{{ sPaymentID }}_timeZoneOffset]" value="">
            </div>
        </div>
    </div>
    {% if paymentmethod.getPrice() %}
        <div class="payment-option-price">
            {% set oPaymentPrice = paymentmethod.getPrice() %}
            {% if oViewConf.isFunctionalityEnabled('blShowVATForPayCharge') %}
                {{ format_price(oPaymentPrice.getNettoPrice(), { currency: currency }) }}
                {% if oPaymentPrice.getVatValue() > 0 %}
                    {{ translate({ ident: "PLUS_VAT" }) }} {{ format_price(oPaymentPrice.getVatValue(), { currency: currency }) }}
                {% endif %}
            {% else %}
                {{ format_price(oPaymentPrice.getBruttoPrice(), { currency: currency }) }}
            {% endif %}
        </div>
    {% endif %}
</div>
<script>
    var javaScriptEnabled = true;
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

    var javascriptEnabledInput = document.getElementById('payment_{{ sPaymentID }}_javascriptEnabled');
    javascriptEnabledInput.setAttribute('value', javaScriptEnabled);

    var javaEnabledInput = document.getElementById('payment_{{ sPaymentID }}_javaEnabled');
    javaEnabledInput.setAttribute('value', javaEnabled);

    var screenHeightInput = document.getElementById('payment_{{ sPaymentID }}_screenHeight');
    screenHeightInput.setAttribute('value', screenHeight);

    var screenWidthInput = document.getElementById('payment_{{ sPaymentID }}_screenWidth');
    screenWidthInput.setAttribute('value', screenWidth);

    var colorDepthInput = document.getElementById('payment_{{ sPaymentID }}_colorDepth');
    colorDepthInput.setAttribute('value', colorDepth);

    var timeZoneOffsetInput = document.getElementById('payment_{{ sPaymentID }}_timeZoneOffset');
    timeZoneOffsetInput.setAttribute('value', timeZoneOffset);
</script>
