//
// motivos.js | Rotinas do CRUD de Motivos de Abertura de Vaga | motivos.php
// (C)haia, 2026-08-24
//

var dataTable;

$(document).ready(function () {
    constroi_grid();
});

function constroi_grid() {
    dataTable = new DataTable('#grid_motivos', {
        "processing": true,
        "serverSide": false,
        "order": [
            [0, "asc"]
        ],
        "ajax": {
            "url": "inc/motivos_aj.php",
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

function nova_motivacao() {
    $('#formMotivo')[0].reset();
    $('#motivo_id').val('');
    $('#motivo_ativo').prop('checked', true);
    $('#msgAlertaMotivo').html('');
    $('#modalMotivoTitulo').text('Nova Motivação');
    $('#modalMotivo').modal('show');
}

function editar_motivo(id) {
    $.ajax({
        url: 'inc/motivo_get_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaMotivo').html('');
                $('#motivo_id').val(res.dados.id);
                $('#motivo_descricao').val(res.dados.descricao);
                $('#motivo_ativo').prop('checked', parseInt(res.dados.ativo) === 1);
                $('#modalMotivoTitulo').text('Editar Motivação');
                $('#modalMotivo').modal('show');
            } else {
                alert(res.msg);
            }
        },
        error: function () {
            alert('Falha ao carregar o registro.');
        }
    });
}

function salvar_motivo() {
    const id = $('#motivo_id').val();
    const url = id ? 'inc/motivo_alt_aj.php' : 'inc/motivo_inc_aj.php';

    if (!$('#motivo_descricao').val().trim()) {
        $('#msgAlertaMotivo').html("<div class='alert alert-danger py-2'>Informe a descrição da motivação.</div>");
        return;
    }

    const formData = new FormData(document.getElementById('formMotivo'));
    formData.set('ativo', $('#motivo_ativo').is(':checked') ? 1 : 0);

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                $('#msgAlertaMotivo').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                atualiza_grid();
                setTimeout(function () {
                    $('#modalMotivo').modal('hide');
                }, 800);
            } else {
                $('#msgAlertaMotivo').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaMotivo').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function excluir_motivo(id) {
    if (!confirm('Deseja realmente excluir esta motivação?')) return;

    $.ajax({
        url: 'inc/motivo_exc_aj.php',
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
