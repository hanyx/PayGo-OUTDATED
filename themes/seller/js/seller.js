$(function() {
    $('select').each(function() {
        $(this).select2();
    });

    $('.wysi').each(function() {
        $(this).wysihtml5();
    });

    $('[data-toggle="tooltip"]').tooltip();
});