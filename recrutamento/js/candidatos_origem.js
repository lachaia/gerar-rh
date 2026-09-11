//
// candidatos_origem.js | Rotinas do CRUD de Origens de Candidato | candidatos_origem.php
// (C)haia, 2026-08-27
//

var dataTable;

$(document).ready(function () {
    constroi_grid();
});

function constroi_grid() {
    dataTable = new DataTable('#grid_origens', {
        "processing": true,
        "serverSide": false,
        "order": [
            [0, "asc"]
        ],
        "ajax": {
            "url": "inc/candidatos_origem_aj.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [1],
                "className": "text-center"
            },
            {
                "targets": [2],
                "className": "text-end"
            }
        ],
        language: {
            url: 'inc/pt-br.json',
        },
    });
}

function atualiza_grid() {
    dataTable.ajax.reload(null, false);
}

function nova_origem() {
    $('#formOrigem')[0].reset();
    $('#origem_id').val('');
    $('#origem_ativo').prop('checked', true);
    $('#msgAlertaOrigem').html('');
    $('#modalOrigemTitulo').text('Nova Origem');
    $('#modalOrigem').modal('show');
}

function editar_origem(id) {
    $.ajax({
        url: 'inc/candidato_origem_get_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaOrigem').html('');
                $('#origem_id').val(res.dados.id);
                $('#origem_descricao').val(res.dados.descricao);
                $('#origem_ativo').prop('checked', parseInt(res.dados.ativo) === 1);
                $('#modalOrigemTitulo').text('Editar Origem');
                $('#modalOrigem').modal('show');
            } else {
                alert(res.msg);
            }
        },
        error: function () {
            alert('Falha ao carregar o registro.');
        }
    });
}

function salvar_origem() {
    const id = $('#origem_id').val();
    const url = id ? 'inc/candidato_origem_alt_aj.php' : 'inc/candidato_origem_inc_aj.php';

    if (!$('#origem_descricao').val().trim()) {
        $('#msgAlertaOrigem').html("<div class='alert alert-danger py-2'>Informe a descrição da origem.</div>");
        return;
    }

    const formData = new FormData(document.getElementById('formOrigem'));
    formData.set('ativo', $('#origem_ativo').is(':checked') ? 1 : 0);

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                $('#msgAlertaOrigem').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                atualiza_grid();
                setTimeout(function () {
                    $('#modalOrigem').modal('hide');
                }, 800);
            } else {
                $('#msgAlertaOrigem').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaOrigem').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function excluir_origem(id) {
    if (!confirm('Deseja realmente excluir esta origem?')) return;

    $.ajax({
        url: 'inc/candidato_origem_exc_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            alert(res.msg);
            if (res.status) atualiza_grid();
        },
        error: function () {
            alert('Falha de comunicação com o servidor.');
        }
    });
}
