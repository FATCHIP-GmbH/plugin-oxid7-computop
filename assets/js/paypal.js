let mid = "YOUR MERCHANTID";
let len = "LEN OF UNENCRYPTED BLOWFISH STRING";
let data = "BLOWFISH ENCRYPTED STRING";
let payid;

if (len != '' && data != '') {
    // Set the request parameter MerchantID, Len and Data
    const params = new URLSearchParams({
        MerchantID: mid,
        Len: len,
        Data: data
    });

    // Render the PayPal button into #paypal-button-container
    paypal.Buttons({
        // Call your server to set up the transaction
        createOrder: function (data, actions) {
            return fetch('https://www.computop-paygate.com/ExternalServices/paypalorders.aspx', {
                method: 'POST',
                body: params
            }).then(function (res) {
                return res.text();
            }).then(function (orderData) {
                let qData = new URLSearchParams(orderData)
                payid = qData.get('PayID');
                return qData.get('orderid');
            });
        },
        // Call cbPayPal.aspx for continue sequence
        onApprove: function (data, actions) {
            var rd = "MerchantId=" + mid + "&PayId=" + payid + "&OrderId=" + data.orderID;
            // Build an invisible form and directly submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'getContinueURL?rd=' + window.btoa(rd);
            form.style.display = 'none';
            // Add form to body
            document.body.appendChild(form);
            // Submit form
            form.submit();
        },
        onCancel: function (data, actions) {
            var rd = "MerchantId=" + mid + "&PayId=" + payid + "&OrderId=" + data.orderID;
            // Build an invisible form and directly submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "https://www.computop-paygate.com/cbPayPal.aspx?rd=" + window.btoa(rd) + "&ua=cancel&token=" + data.orderID;
            form.style.display = 'none';
            // Add form to body
            document.body.appendChild(form);
            // Submit form
            form.submit();
        },
        onError: function (data, actions) {
            var rd = "MerchantId=" + mid + "&PayId=" + payid + "&OrderId=" + data.orderID;
            // Build an invisible form and directly submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "https://www.computop-paygate.com/cbPayPal.aspx?rd=" + window.btoa(rd) + "&ua=cancel&token=" + data.orderID;
            form.style.display = 'none';
            // Add form to body
            document.body.appendChild(form);
            // Submit form
            form.submit();
        }
    }).render('#paypal-button-container');
}
