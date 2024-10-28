$(document).ready(function(){
    $('[data-toggle="popover"]').popover({
        html: true // Enable HTML rendering in popover content
    });
    const idealSelect = document.getElementById("idealDirektOderUeberSofort");
    const bankSection = document.getElementById("bankSection");

    function toggleBankSection() {
        if (idealSelect.value === "PPRO") {
            bankSection.style.display = "none";
        } else {
            bankSection.style.display = "block";
        }
    }

    toggleBankSection();

    idealSelect.addEventListener("change", toggleBankSection);
});
