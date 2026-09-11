//
// superintendentes.js | Rotinas do CRUD de Superintendentes | superintendentes.php
// (C)haia, 2026-08-24
//

var dataTable;

$(document).ready(function () {
    constroi_grid();
});

function constroi_grid() {
    dataTable = new DataTable('#grid_superintendentes', {
        "processing": true,
        "serverSide": false,
        "order": [
            [0, "asc"]
        ],
        "ajax": {
            "url": "inc/superintendentes_aj.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [2],
                "className": "text-center"
            },
            {
                "targets": [3],
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

function novo_superintendente() {
    $('#formSuperintendente')[0].reset();
    $('#superintendente_id').val('');
    $('#superintendente_ativo').prop('checked', true);
    $('#msgAlertaSuperintendente').html('');
    $('#modalSuperintendenteTitulo').text('Novo Superintendente');
    $('#modalSuperintendente').modal('show');
}

function editar_superintendente(id) {
    $.ajax({
        url: 'inc/superintendente_get_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaSuperintendente').html('');
                $('#superintendente_id').val(res.dados.id);
                $('#superintendente_identificador').val(res.dados.identificador);
                $('#superintendente_email').val(res.dados.email);
                $('#superintendente_ativo').prop('checked', parseInt(res.dados.ativo) === 1);
                $('#modalSuperintendenteTitulo').text('Editar Superintendente');
                $('#modalSuperintendente').modal('show');
            } else {
                alert(res.msg);
            }
        },
        error: function () {
            alert('Falha ao carregar o registro.');
        }
    });
}

function salvar_superintendente() {
    const id = $('#superintendente_id').val();
    const url = id ? 'inc/superintendente_alt_aj.php' : 'inc/superintendente_inc_aj.php';

    if (!$('#superintendente_identificador').val().trim()) {
        $('#msgAlertaSuperintendente').html("<div class='alert alert-danger py-2'>Informe o nome do superintendente.</div>");
        return;
    }
    if (!$('#superintendente_email').val().trim()) {
        $('#msgAlertaSuperintendente').html("<div class='alert alert-danger py-2'>Informe um e-mail válido.</div>");
        return;
    }

    const formData = new FormData(document.getElementById('formSuperintendente'));
    formData.set('ativo', $('#superintendente_ativo').is(':checked') ? 1 : 0);

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                $('#msgAlertaSuperintendente').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                atualiza_grid();
                setTimeout(function () {
                    $('#modalSuperintendente').modal('hide');
                }, 800);
            } else {
                $('#msgAlertaSuperintendente').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaSuperintendente').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function excluir_superintendente(id) {
    if (!confirm('Deseja realmente excluir este superintendente?')) return;

    $.ajax({
        url: 'inc/superintendente_exc_aj.php',
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
