//
// competencias_comportamentais.js | Rotinas do CRUD de Competências Comportamentais | competencias_comportamentais.php
// (C)haia, 2026-08-24
//

var dataTable;

$(document).ready(function () {
    constroi_grid();
});

function constroi_grid() {
    dataTable = new DataTable('#grid_competencias', {
        "processing": true,
        "serverSide": false,
        "order": [
            [0, "asc"]
        ],
        "ajax": {
            "url": "inc/competencias_comportamentais_aj.php",
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

function nova_competencia() {
    $('#formCompetencia')[0].reset();
    $('#competencia_id').val('');
    $('#competencia_ativo').prop('checked', true);
    $('#msgAlertaCompetencia').html('');
    $('#modalCompetenciaTitulo').text('Nova Competência');
    $('#modalCompetencia').modal('show');
}

function editar_competencia(id) {
    $.ajax({
        url: 'inc/competencia_comportamental_get_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaCompetencia').html('');
                $('#competencia_id').val(res.dados.id);
                $('#competencia_descricao').val(res.dados.descricao);
                $('#competencia_ativo').prop('checked', parseInt(res.dados.ativo) === 1);
                $('#modalCompetenciaTitulo').text('Editar Competência');
                $('#modalCompetencia').modal('show');
            } else {
                alert(res.msg);
            }
        },
        error: function () {
            alert('Falha ao carregar o registro.');
        }
    });
}

function salvar_competencia() {
    const id = $('#competencia_id').val();
    const url = id ? 'inc/competencia_comportamental_alt_aj.php' : 'inc/competencia_comportamental_inc_aj.php';

    if (!$('#competencia_descricao').val().trim()) {
        $('#msgAlertaCompetencia').html("<div class='alert alert-danger py-2'>Informe a descrição da competência.</div>");
        return;
    }

    const formData = new FormData(document.getElementById('formCompetencia'));
    formData.set('ativo', $('#competencia_ativo').is(':checked') ? 1 : 0);

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                $('#msgAlertaCompetencia').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                atualiza_grid();
                setTimeout(function () {
                    $('#modalCompetencia').modal('hide');
                }, 800);
            } else {
                $('#msgAlertaCompetencia').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaCompetencia').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function excluir_competencia(id) {
    if (!confirm('Deseja realmente excluir esta competência?')) return;

    $.ajax({
        url: 'inc/competencia_comportamental_exc_aj.php',
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
