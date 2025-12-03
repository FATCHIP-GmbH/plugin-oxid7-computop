function ctGetPaymentForm() {
    if (document.order) {
        if (document.order[0].nodeName != 'FORM' && document.order.paymentid) {
            return document.order;
        } else {
            for (var i = 0; i < document.order.length; i++) {
                if (document.order[i].paymentid) {
                    return document.order[i];
                }
            }
        }
    }
    return false;
}

function ctGetSelectedPaymentMethod() {
    let form = ctGetPaymentForm();
    if(form && form.paymentid) {
        if(form.paymentid.length) {
            for(var i = 0;i < form.paymentid.length; i++) {
                if(form.paymentid[i].checked == true) {
                    return form.paymentid[i].value;
                }
            }
        } else {
            return form.paymentid.value;
        }
    }
    return false;
}

function ctAddPaymentFormSubmitEvent(callback, paymentId) {
    let paymentForm = ctGetPaymentForm();
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(event) {
            if (ctGetSelectedPaymentMethod() === paymentId) {
                callback(event);
            }
        });
    }
}

function ctValidateRatepayDirectDebitForm(event) {
    let element = document.getElementById('computop_ratepay_direct_debug_accept_sepa_mandate');
    if (element && element.checked == false) {
        ctToggleElementVisibility('ctRatepayDirectDebitAcceptMandateError');
        event.preventDefault();
    }
}

function ctToggleMandateText() {
    ctToggleElementVisibility('ctRatepayMandateText');
}

function ctToggleElementVisibility(elementId) {
    let element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

function ctHideElement(elementId) {
    let element = document.getElementById(elementId);
    if (element) {
        element.style.display = 'none';
    }
}