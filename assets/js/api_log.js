$(function () {
    // Function to initialize popovers (only defined once)
    function initPopovers() {
        // Initialize tooltips first (if any)
        $('[data-toggle="tooltip"]').tooltip();

        // Then initialize popovers
        $('[data-toggle="popover"]').popover({
            html: true,
            container: 'body',
            trigger: 'click' // Set the trigger to 'click' explicitly
        });
    }

    // Initial initialization on page load
    initPopovers();

    // Event listener for Bootstrap table events
    $('#table').on('load-success.bs.table', function () { // Listen to load-success
        initPopovers(); // Re-initialize popovers after table data loads
    });

    // Click handler for the entire body
    $('body').on('click', function (e) {
        // Check if the click is on a popover trigger
        let triggerElement = $(e.target).closest('[data-toggle="popover"]');

        // If it is a trigger, hide all other popovers and show the clicked one
        if (triggerElement.length) {
            $('[data-toggle="popover"]').not(triggerElement).popover('hide');
            triggerElement.popover('show');
        } else if (!$(e.target).closest('.popover').length) {
            // If the click is not on a trigger or inside a popover, hide all popovers
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    // Mousedown handler for popovers
    $('body').on('mouseup', '.popover', function(e) {
        e.preventDefault()
    });
});
function requestDetailsFormatter(value) {
    return '<a data-placement="left" data-toggle="popover" data-trigger="click" href="#" data-content="' + value + '" title="Details" data-html="true" class="">Details</a>';
}

function responseDetailsFormatter(value) {
    return '<a data-placement="left" data-toggle="popover" data-trigger="click" href="#" data-content="' + value + '" title="Details" data-html="true" class="">Details</a>';
}

function DateFormatter(value) {
    return value.substr(0, 16);
}

function modeDetailsFormatter(value) {
    if (value === false) {
        return 'Test';
    } else {
        return 'Live';
    }
}
