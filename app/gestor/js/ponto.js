//
// ferias.js | Módulo: GESTOR
// (C)haia, 18/09/2025. | 21/10/2025
//

let janela = new bootstrap.Modal(document.getElementById('modalDecide'));

$(document).ready(function () {
    constroiTabela();
});

let dataTable = "";

function constroiTabela() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }
    let chk_pendentes = document.getElementById('chk_pendentes').checked;
    let supervisor_id = document.getElementById('supervisor_id').value;
    //
    dataTable = new DataTable('#tabela', {
        "processing": true,
        "serverSide": false,
        "stateSave": false, // impede lembrar filtro antigo
         destroy: true, // se recria várias vezes
        "order": [
            [1, "asc"],
            [2, "asc"]
        ],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "includes/ponto_aj.php",
            "type": "POST",
            "data": function (d) {
                d.supervisor_id = supervisor_id,
                d.chk_pendentes = chk_pendentes ? 1 : 0
            }
        },
        "columnDefs": [{
            "targets": [0, 2, 3, 4, 5, 6, 7],
            "className": "text-center"
        }],
        language: {
            url: '../includes/pt-BR.json',
        },
    });

  dataTable.on('init', function () {
    dataTable.search('').draw();
    var $filtro = $('.dataTables_filter input');
    $filtro.val('').attr('autocomplete','off');
    setTimeout(() => $filtro.val(''), 200);
  });
}

function f_decidir( id ) {
    //
    $.post("includes/ponto_aj1.php", { id: id }, function (retorno) {
        $("#nmColaborador").html(retorno.nome);
        $("#ajuste").html(retorno.dsTipo);
        $("#dataSolicitacao").html(retorno.solicitado_em);
        $("#data_hora").html(retorno.data_hora);
        $("#justificativa").html(retorno.motivo);
        $("#batidas").html(retorno.batidas);
        $("#solicitacao_id").val( id );
        janela.show();
    }, "JSON");
}

function f_salvar( decisao ) {
    //
    let mensagem = $("#mensagem");
    let solicitacao_id = $("#solicitacao_id").val();
    let observacao = $("#observacao").val();
    //
    $("#botoes_decisao").hide();
    $("#mensagem").html("<i class='fa fa-spinner fa-spin'></i> Carregando...");
    //
    // Faz requisição AJAX
    $.ajax({
        url: "includes/ponto_aj2.php",
        type: "POST",
        data: {
            solicitacao_id: solicitacao_id,
            observacao: observacao,
            decisao: decisao 
        },
        success: function (response) {
            mensagem.html(response.msg);
            setTimeout(function () {
                document.location.reload();
            }, 3000);
        },
        error: function () {
            mensagem.html("<div class='alert alert-danger'>Erro ao carregar dados.</div>");
            setTimeout(function () {
                document.location.reload();
            }, 5000);
        }
    }, "JSON");
}

function selecionou( o ){
    if( o.checked ){
        o.value = "1";
    } else {
        o.value = "0";
    }
    dataTable.clear().draw();
    dataTable.destroy();
    constroiTabela();
}