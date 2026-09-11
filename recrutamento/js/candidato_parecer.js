//
// candidato_parecer.js | Rotinas da tela de Parecer de Screening | candidato_parecer.php
// (C)haia, 2026-08-28
//

//- Abre o Meet numa janela separada (não aba) - com tamanho/posição próprios, dá
//- pra arrastar pro lado e deixar visível enquanto preenche o parecer ao mesmo tempo.
function abrirJanelaMeet(url) {
    const largura = 900;
    const altura = 720;
    const esquerda = Math.max(0, window.screen.availWidth - largura - 20);
    const topo = 40;
    const feats = `width=${largura},height=${altura},left=${esquerda},top=${topo},resizable=yes,scrollbars=yes,noopener,noreferrer`;
    const janela = window.open(url, 'janelaMeetScreening', feats);
    if (janela) {
        janela.opener = null;
        janela.focus();
    }
}

$(document).ready(function () {
    $('.summernote-edit').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol']],
        ],
    });
    $('.summernote-edit-grande').summernote({
        height: 320,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol']],
        ],
    });
});

function salvarParecer(enviar) {
    const analise = $('#parecer_analise_entrevista').summernote('code');
    const conclusao = $('#parecer_conclusao').val();

    if ($.trim($(analise).text()) === '' || !conclusao) {
        $('#msgAlertaParecer').html("<div class='alert alert-danger py-2'>Preencha ao menos a análise da entrevista e a conclusão.</div>");
        window.scrollTo(0, 0);
        return;
    }

    $('#msgAlertaParecer').html("<div class='alert alert-info py-2'><i class='fa-solid fa-spinner fa-spin me-2'></i>" + (enviar ? 'Salvando e enviando...' : 'Salvando...') + "</div>");
    window.scrollTo(0, 0);

    $.ajax({
        url: 'inc/candidato_parecer_salvar_aj.php',
        type: 'POST',
        dataType: 'json',
        data: {
            candidatura_id: $('#parecer_candidatura_id').val(),
            enviar: enviar ? 1 : 0,
            data_nascimento: $('#parecer_data_nascimento').val(),
            endereco: $('#parecer_endereco').val(),
            gestor_nome: $('#parecer_gestor_nome').val(),
            gestor_email: $('#parecer_gestor_email').val(),
            chamou_atencao: $('#parecer_chamou_atencao').summernote('code'),
            tem_cnh: $('#parecer_tem_cnh').val(),
            nivel_office: $('#parecer_nivel_office').val(),
            pretensao_salarial: $('#parecer_pretensao_salarial').val(),
            tem_experiencia: $('#parecer_tem_experiencia').summernote('code'),
            analise_entrevista: analise,
            conclusao: conclusao,
        },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaParecer').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                if (enviar) {
                    setTimeout(function () {
                        window.location.href = 'vaga_candidatos.php?id=' + VAGA_ID_PARECER;
                    }, 1200);
                }
            } else {
                $('#msgAlertaParecer').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaParecer').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}
