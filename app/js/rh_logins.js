var dataTable;

$(document).ready(function() {
    //
    constroiTabela();
    //
    $.post("includes/rh_logins_aj1.php", {}, function(retorno) {
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
            [2, "desc"]
        ],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_logins_aj.php",
            "type": "POST",
            "data": function(d) {
                d.dtFrom = dtFrom,
                d.dtTo = dtTo,
                d.idUsuario = idUsuario;
            }
        },
        "columnDefs": [{
            "targets": [1, 2, 3, 4, 5, 8, 9],
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