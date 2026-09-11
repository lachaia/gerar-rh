//
// vagas_fluxo.js | Rotinas do CRUD de Etapas do Fluxo | vagas_fluxo.php
// (C)haia, 2026-08-26
//

var dataTable;

$(document).ready(function () {
    constroi_grid();

    $('#fluxo_status, #fluxo_cor_frente, #fluxo_cor_fundo').on('input change', atualiza_preview);
});

function constroi_grid() {
    dataTable = new DataTable('#grid_fluxo', {
        "processing": true,
        "serverSide": false,
        "order": [
            [0, "asc"]
        ],
        "ajax": {
            "url": "inc/vagas_fluxo_aj.php",
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
    const texto = $('#fluxo_status').val().trim().toUpperCase() || 'ETAPA';
    const corFrente = $('#fluxo_cor_frente').val();
    const corFundo = $('#fluxo_cor_fundo').val();

    $('#fluxo_preview')
        .text(texto)
        .removeClass('text-light text-dark')
        .addClass(corFrente)
        .css('background-color', corFundo);
}

function novo_fluxo() {
    $('#formFluxo')[0].reset();
    $('#fluxo_id').val('');
    $('#fluxo_ativo').prop('checked', true);
    $('#fluxo_cor_frente').val('text-light');
    $('#fluxo_cor_fundo').val('#6c757d');
    $('#msgAlertaFluxo').html('');
    $('#modalFluxoTitulo').text('Nova Etapa');
    atualiza_preview();
    $('#modalFluxo').modal('show');
}

function editar_fluxo(id) {
    $.ajax({
        url: 'inc/vaga_fluxo_get_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaFluxo').html('');
                $('#fluxo_id').val(res.dados.id);
                $('#fluxo_status').val(res.dados.status);
                $('#fluxo_descricao').val(res.dados.descricao);
                $('#fluxo_cor_frente').val(res.dados.cor_frente);
                $('#fluxo_cor_fundo').val(res.dados.cor_fundo);
                $('#fluxo_ativo').prop('checked', parseInt(res.dados.ativo) === 1);
                $('#modalFluxoTitulo').text('Editar Etapa');
                atualiza_preview();
                $('#modalFluxo').modal('show');
            } else {
                alert(res.msg);
            }
        },
        error: function () {
            alert('Falha ao carregar o registro.');
        }
    });
}

function salvar_fluxo() {
    const id = $('#fluxo_id').val();
    const url = id ? 'inc/vaga_fluxo_alt_aj.php' : 'inc/vaga_fluxo_inc_aj.php';

    if (!$('#fluxo_status').val().trim()) {
        $('#msgAlertaFluxo').html("<div class='alert alert-danger py-2'>Informe o nome da etapa.</div>");
        return;
    }
    if (!$('#fluxo_descricao').val().trim()) {
        $('#msgAlertaFluxo').html("<div class='alert alert-danger py-2'>Informe a descrição da etapa.</div>");
        return;
    }

    const formData = new FormData(document.getElementById('formFluxo'));
    formData.set('ativo', $('#fluxo_ativo').is(':checked') ? 1 : 0);

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                $('#msgAlertaFluxo').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                atualiza_grid();
                setTimeout(function () {
                    $('#modalFluxo').modal('hide');
                }, 800);
            } else {
                $('#msgAlertaFluxo').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaFluxo').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function excluir_fluxo(id) {
    if (!confirm('Deseja realmente excluir esta etapa?')) return;

    $.ajax({
        url: 'inc/vaga_fluxo_exc_aj.php',
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
