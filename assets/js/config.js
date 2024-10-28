$(document).ready(function(){
    $('[data-toggle="popover"]').popover({
        html: true // Enable HTML rendering in popover content
    });
    const idealSelect = document.getElementById("idealDirektOderUeberSofort");
    const bankSection = document.getElementById("bankSection");

    // Funktion zum Anzeigen/Ausblenden des Bankbereichs
    function toggleBankSection() {
        if (idealSelect.value === "PPRO") {
            bankSection.style.display = "none";
        } else {
            bankSection.style.display = "block";
        }
    }

    // Initiale Ausführung, um den aktuellen Status zu setzen
    toggleBankSection();

    // Event Listener für Änderungen in der Selectbox
    idealSelect.addEventListener("change", toggleBankSection);
});
