//
// vaga_edit.js | Rotinas de edição/publicação de vaga | vaga_edit.php
// (C)haia, 2026-08-24
//

// mapeia a chave do JSON de sugestão -> id do campo no formulário
const MAPA_CAMPO = {
    identificador: 'campo_identificador',
    codigo_vaga: 'campo_codigo_vaga',
    setor_ds: 'campo_setor',
    area: 'campo_area',
    nivel_experiencia: 'campo_nivel_experiencia',
    descricao: 'campo_descricao',
    resumo: 'campo_resumo',
    diferenciais: 'campo_diferenciais',
    beneficios: 'campo_beneficios'
};

// campos que viram editor rico (Summernote) — mesmo padrão do formulário de
// solicitação (vaga_new.js): descrição/diferenciais/benefícios podem ter
// parágrafos e listas. "resumo" fica de fora de propósito: é texto curto
// (varchar(255), aparece na listagem de vagas) e não deve virar HTML.
const CAMPOS_SUMMERNOTE = ['campo_descricao', 'campo_diferenciais', 'campo_beneficios'];

$(document).ready(function () {
    // Precisa checar quem já está preenchido ANTES de iniciar o Summernote,
    // pois ao inicializar num campo vazio ele já reescreve o valor (com um
    // parágrafo vazio), o que atrapalharia essa checagem depois.
    const algumPreenchido = Object.values(MAPA_CAMPO).some(function (campoId) {
        return $('#' + campoId).val().trim() !== '';
    });

    inicializar_summernote();

    if (!algumPreenchido) {
        sugerir_preenchimento(false);
    }
});

function inicializar_summernote() {
    const base = {
        tabsize: 2,
        lang: 'pt-BR',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['forecolor', 'backcolor']],
            ['para', ['ul', 'ol', 'paragraph']]
        ]
    };

    $('#campo_descricao').summernote({ ...base, height: 220, placeholder: 'Descrição completa da vaga...' });
    $('#campo_diferenciais').summernote({ ...base, height: 150, placeholder: 'Diferenciais desejáveis (não eliminatórios)...' });
    $('#campo_beneficios').summernote({ ...base, height: 150, placeholder: 'Benefícios oferecidos...' });
}

function eh_summernote(campoId) {
    return CAMPOS_SUMMERNOTE.indexOf(campoId) !== -1;
}

function campo_esta_vazio(campoId) {
    if (eh_summernote(campoId)) {
        return $('#' + campoId).summernote('isEmpty');
    }
    return $('#' + campoId).val().trim() === '';
}

function definir_valor_campo(campoId, valor) {
    if (eh_summernote(campoId)) {
        $('#' + campoId).summernote('code', valor);
        $('#' + campoId).next('.note-editor').addClass('campo-sugerido');
    } else {
        $('#' + campoId).val(valor).addClass('campo-sugerido');
    }
}

function id_vaga() {
    return $('input[name="id"]').val();
}

function sugerir_preenchimento(forcar) {
    $.ajax({
        url: 'inc/vaga_edit_sugestao_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id_vaga() },
        success: function (res) {
            if (!res.status) {
                alert(res.msg);
                return;
            }

            $.each(res.dados, function (chave, valor) {
                const campoId = MAPA_CAMPO[chave];
                if (!campoId) return;

                if (forcar || campo_esta_vazio(campoId)) {
                    definir_valor_campo(campoId, valor);
                }
            });

            if (forcar) {
                $('#msgAlertaVaga').html("<div class='alert alert-warning py-2'><i class='fa-solid fa-wand-magic-sparkles me-1'></i> Sugestões aplicadas — revise antes de salvar.</div>");
            }
        },
        error: function () {
            if (forcar) alert('Falha ao gerar sugestões.');
        }
    });
}

function salvar_vaga() {
    // sincroniza os editores Summernote com os <textarea> originais antes de
    // montar o FormData (o Summernote já faz isso sozinho, mas reforçar aqui
    // garante que o valor mais recente vá no submit)
    CAMPOS_SUMMERNOTE.forEach(function (campoId) {
        $('#' + campoId).val($('#' + campoId).summernote('code'));
    });

    const camposObrigatorios = [
        ['recrutador_id', 'Selecione o recrutador responsável.'],
        ['campo_identificador', 'Informe o identificador da vaga.'],
        ['campo_codigo_vaga', 'Informe o código da vaga.'],
        ['campo_setor', 'Informe o setor.'],
        ['campo_area', 'Informe a área.'],
        ['campo_nivel_experiencia', 'Informe o nível de experiência.'],
        ['campo_descricao', 'Informe a descrição da vaga.'],
        ['campo_resumo', 'Informe o resumo da vaga.'],
    ];

    for (const [campoId, mensagem] of camposObrigatorios) {
        const valorVazio = campoId === 'recrutador_id'
            ? ($('#recrutador_id').val() === '0' || !$('#recrutador_id').val())
            : campo_esta_vazio(campoId);

        if (valorVazio) {
            $('#msgAlertaVaga').html("<div class='alert alert-danger py-2'>" + mensagem + "</div>");
            if (!eh_summernote(campoId)) $('#' + campoId).trigger('focus');
            return;
        }
    }

    const formData = new FormData(document.getElementById('formVagaEdit'));
    formData.set('recrutador_id', $('#recrutador_id').val());

    $.ajax({
        url: 'inc/vaga_edit_salvar_aj.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                $('#msgAlertaVaga').html("<div class='alert alert-success py-2'>" + res.msg + " Redirecionando...</div>");
                $('.campo-sugerido').removeClass('campo-sugerido');
                setTimeout(function () {
                    window.location = 'index.php';
                }, 2000);
            } else {
                $('#msgAlertaVaga').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaVaga').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}
