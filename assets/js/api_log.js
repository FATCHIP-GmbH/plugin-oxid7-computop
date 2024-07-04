$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
    $('.table').on('all.bs.table', function (e, name, args) {
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover();
    });
});

$('body')
    .on('mousedown', '.popover', function(e) {
        console.log("clicked inside popover")
        e.preventDefault()
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