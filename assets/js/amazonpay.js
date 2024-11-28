const FatchipComputopAmazonPayButtonComponent = {
    amazonPayButton: null,
    payloadJSON: null,
    signature: null,

    init: function (amazonPayButton, payloadJSON, signature, publicKeyId) {
        const buttonClassesApex = "btn btn-highlight btn-lg w-100";
        const buttonClassesTwig = "btn btn-lg btn-primary pull-right submitButton nextStep largeButton";
        const amazonPayWrapper = document.getElementById("AmazonPayWrapper");

        const apexButton = document.getElementsByClassName(buttonClassesApex)[0];
        const twigButton = document.getElementsByClassName(buttonClassesTwig)[0];

        if (apexButton && amazonPayWrapper) {
            apexButton.parentNode.append(amazonPayWrapper);
            apexButton.style.display = "none";
        }

        if (twigButton && amazonPayWrapper) {
            twigButton.parentNode.prepend(amazonPayWrapper);
            twigButton.style.display = "none";
        }

        this.amazonPayButton = amazonPayButton;
        console.log('payloadJSON: Registering Events',payloadJSON);
        console.log('payloadJSON as String:', JSON.stringify(payloadJSON));

        console.log('publicKeyId (button):',publicKeyId);
        this.payloadJSON = payloadJSON;
        this.signature = signature;
        this.publicKeyId = publicKeyId;

        this.registerEvents();

    },

    registerEvents: function () {
        console.log('FatchipComputopAmazonPayButtonComponent: Registering Events');
        this.amazonPayButton.onClick(() => {
            this.payButtonClickHandler();
        });
    },

    payButtonClickHandler: function () {
        if (this.isConfirmationRequired() && !this.isConfirmationAccepted()) {
            this.showConfirmationError();
        } else {
            this.amazonPayButton.initCheckout({
                createCheckoutSessionConfig: {
                    payloadJSON: this.payloadJSON,
                    signature: this.signature,
                    publicKeyId: this.publicKeyId
                }
            });
        }
    },

    isConfirmationRequired: function () {
        return this.forceConfirmAGB() || this.forceConfirmDPA() || this.forceConfirmSPA();
    },

    isConfirmationAccepted: function () {
        return this.isAgbConfirmed() && this.isDpaConfirmed() && this.isSpaConfirmed();
    },

    showConfirmationError: function () {
        const errorContainer = document.getElementById("confirm-agb-error-container");
        const agbConfirmation = document.getElementsByClassName("agbConfirmation")[0];

        if (errorContainer && agbConfirmation) {
            errorContainer.style.display = "block";
            agbConfirmation.classList.add("alert-danger");
        }
    },

    hideErrorContainer: function () {
        return true;
    },

    // Methods for different types of confirmations
    forceConfirmAGB: function () {
        return true;
    },

    forceConfirmDPA: function () {
        return true;
    },

    forceConfirmSPA: function () {
        return true;
    },

    isAgbConfirmed: function () {
        return true;
    },

    isDpaConfirmed: function () {
        return true;
    },

    isSpaConfirmed: function () {
        return true;
    }
};
