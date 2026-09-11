$(document).ready(function () {
    // O menu só expande ou encolhe ao clicar no botão
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
});

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})