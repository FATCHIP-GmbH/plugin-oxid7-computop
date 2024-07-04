window.FatchipComputopAmazonPayButtonComponent = {
    amazonPayButtonfatchipComputopAmazonPayButton: null, payLoad: null, signature: null, init: function (e, n, t) {
        buttonClassesApex = "btn btn-highlight btn-lg w-100", buttonClassesTwig = "btn btn-lg btn-primary pull-right submitButton nextStep largeButton", document.getElementsByClassName(buttonClassesApex)[0] && (document.getElementsByClassName(buttonClassesApex)[0].parentNode.append(document.getElementById("AmazonPayWrapper")), document.getElementsByClassName(buttonClassesApex)[0].style.display = "none"), document.getElementsByClassName(buttonClassesTwig)[0] && (document.getElementsByClassName(buttonClassesTwig)[0].parentNode.prepend(document.getElementById("AmazonPayWrapper")), document.getElementsByClassName(buttonClassesTwig)[0].style.display = "none"), this.fatchipComputopAmazonPayButton = e, this.payloadJSON = n, this.signature = t, this.registerEvents()
    }, registerEvents: function () {
        console.log('FatchipComputopAmazonPayButtonComponent registerEvents');
        fatchipComputopAmazonPayButton.onClick(function () {
            FatchipComputopAmazonPayButtonComponent.payButtonClickHandler()
        })
    }, payButtonClickHandler: function () {
        this.forceConfirmAGB() && !FatchipComputopAmazonPayButtonComponent.isAgbConfirmed() || this.forceConfirmDPA() && !FatchipComputopAmazonPayButtonComponent.isDpaConfirmed() || this.forceConfirmSPA() && !FatchipComputopAmazonPayButtonComponent.isSpaConfirmed() ? (this.forceConfirmAGB() || this.forceConfirmDPA() || this.forceConfirmSPA()) && document.getElementById("confirm-agb-error-container") && (document.getElementById("confirm-agb-error-container").setAttribute("style", "display:block"), document.getElementsByClassName("agbConfirmation")[0].classList.add("alert-danger")) : (alert(this.payloadJSON + "<BR>"), this.fatchipComputopAmazonPayButton.initCheckout({
            createCheckoutSessionConfig: {
                payloadJSON: this.payloadJSON,
                signature: this.signature
            }
        }))
    }, hideErrorContainer: function () {
        return true;
    }, forceConfirmAGB: function () {
        return true;
    }, forceConfirmDPA: function () {
        return true;
    }, forceConfirmSPA: function () {
        return true;
    }, isAgbConfirmed: function () {
        return true;
    }
};