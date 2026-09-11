//
// vagas_status.js | Rotinas do CRUD de Status da Vaga | vagas_status.php
// (C)haia, 2026-08-24
//

var dataTable;

$(document).ready(function () {
    constroi_grid();

    $('#status_status, #status_cor_frente, #status_cor_fundo').on('input change', atualiza_preview);
});

function constroi_grid() {
    dataTable = new DataTable('#grid_status', {
        "processing": true,
        "serverSide": false,
        "order": [
            [0, "asc"]
        ],
        "ajax": {
            "url": "inc/vagas_status_aj.php",
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

function atualiza_preview() {
    const texto = $('#status_status').val().trim().toUpperCase() || 'STATUS';
    const corFrente = $('#status_cor_frente').val();
    const corFundo = $('#status_cor_fundo').val();

    $('#status_preview')
        .text(texto)
        .removeClass('text-light text-dark')
        .addClass(corFrente)
        .css('background-color', corFundo);
}

function novo_status() {
    $('#formStatus')[0].reset();
    $('#status_id').val('');
    $('#status_ativo').prop('checked', true);
    $('#status_cor_frente').val('text-light');
    $('#status_cor_fundo').val('#6c757d');
    $('#msgAlertaStatus').html('');
    $('#modalStatusTitulo').text('Novo Status');
    atualiza_preview();
    $('#modalStatus').modal('show');
}

function editar_status(id) {
    $.ajax({
        url: 'inc/vaga_status_get_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaStatus').html('');
                $('#status_id').val(res.dados.id);
                $('#status_status').val(res.dados.status);
                $('#status_descricao').val(res.dados.descricao);
                $('#status_cor_frente').val(res.dados.cor_frente);
                $('#status_cor_fundo').val(res.dados.cor_fundo);
                $('#status_ativo').prop('checked', parseInt(res.dados.ativo) === 1);
                $('#modalStatusTitulo').text('Editar Status');
                atualiza_preview();
                $('#modalStatus').modal('show');
            } else {
                alert(res.msg);
            }
        },
        error: function () {
            alert('Falha ao carregar o registro.');
        }
    });
}

function salvar_status() {
    const id = $('#status_id').val();
    const url = id ? 'inc/vaga_status_alt_aj.php' : 'inc/vaga_status_inc_aj.php';

    if (!$('#status_status').val().trim()) {
        $('#msgAlertaStatus').html("<div class='alert alert-danger py-2'>Informe o nome do status.</div>");
        return;
    }
    if (!$('#status_descricao').val().trim()) {
        $('#msgAlertaStatus').html("<div class='alert alert-danger py-2'>Informe a descrição do status.</div>");
        return;
    }

    const formData = new FormData(document.getElementById('formStatus'));
    formData.set('ativo', $('#status_ativo').is(':checked') ? 1 : 0);

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                $('#msgAlertaStatus').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                atualiza_grid();
                setTimeout(function () {
                    $('#modalStatus').modal('hide');
                }, 800);
            } else {
                $('#msgAlertaStatus').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaStatus').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function excluir_status(id) {
    if (!confirm('Deseja realmente excluir este status?')) return;

    $.ajax({
        url: 'inc/vaga_status_exc_aj.php',
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
