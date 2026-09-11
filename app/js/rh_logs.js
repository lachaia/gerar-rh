//
//- rh_logs.js | Rotinas do rh_logs
// (C)haia, 12/02/2025

var dataTable;

$(document).ready(function() {
    //
    constroiTabela();
    //
    $.post("includes/rh_logs_aj1.php", {}, function(retorno) {
        $("#seletores").html(retorno);
    });

});

function constroiTabela() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 17 : 10;
    //
    // Definir quantidade de linhas por página dinamicamente
    if( alturaTotal == alturaViewport ) {
        linhasPorPagina = alturaTotal > 900 ? 24 : 16;
    }     
    //
    const idUsuario = $("#idUsuario").val(); // Captura o ID do usuário
    const dtFrom = $("#dtFrom").val(); // Captura o ID do usuário
    const dtTo = $("#dtTo").val(); // Captura o ID do usuário

    dataTable = new DataTable('#example', {
        "processing": true,
        "serverSide": true,
        "order": [
            [0, "desc"]
        ],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_logs_aj.php",
            "type": "POST",
            "data": function(d) { // Adiciona o idUsuario à requisição
                d.dtFrom = dtFrom,
                d.dtTo = dtTo,
                d.idUsuario = idUsuario;
            }
        },
        "columnDefs": [{
            "targets": [0, 2, 3, 4, 7, 8],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}


async function selecionou() {
    // Destroy the existing DataTable
    //
    dataTable.clear().draw();
    dataTable.destroy();
    constroiTabela();
    //
}

window.onresize = selecionou; // Atualiza quando a tela for redimensionada