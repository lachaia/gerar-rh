//- rh_equipamentos.js
// (C)haia, 19/08/2025

const incModalSolic = new bootstrap.Modal(document.getElementById("modalIncSolic"));
const verModalSolic = new bootstrap.Modal(document.getElementById("modalVerSolic"));
const verModalTermo = new bootstrap.Modal(document.getElementById("modalVerTermo"));

let dataTable;

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    /*
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário
    */

    $('#inc_observacoes').summernote({
        height: 200,
        lang: 'pt-BR',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['codeview']]
        ],
        callbacks: {
            onInit: function () {
                // Garante que o texto fique branco ao inicializar
                $('.note-editable').css('color', 'white');
            }
        }
    });

    $("#inc_responsavel").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "includes/buscar_pessoas.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idPessoa: item.idPessoa  // <- chave personalizada
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            $("#idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
            //atualiza_enderecos( ui.item.idPessoa );
        }
    });

    constroiTabela_solicitacoes();
    constroiTabela_termos();

});

function f_incluir_solicitacao() {
    incModalSolic.show();
}

function f_incluir_solicitacao_commit() {
    let mensagem = $("#msgAlertaSolicitacao");

    // valida responsável
    let responsavel = $("#inc_responsavel").val().trim();
    if (!responsavel) {
        mensagem.html("<div class='text-danger'>Por favor, informe o responsável pelo termo.</div>");
        $("#inc_responsavel").focus();
        return;
    }

    // valida usuário
    let usuario = $("#inc_usuario").val().trim();
    if (!usuario) {
        mensagem.html("<div class='text-danger'>Por favor, informe o usuário final dos equipamentos.</div>");
        $("#inc_usuario").focus();
        return;
    }

    // valida hidden idColab e idPessoa
    let idPessoa = $("#idPessoa").val();
    if (!idPessoa) {
        mensagem.html("<div class='text-danger'>Erro: colaborador ou pessoa não identificado(s). Verifique os campos.</div>");
        $("#inc_responsavel").focus();
        return;
    }

    // valida pelo menos um equipamento
    if ($("input[name='equipamentos[]']:checked").length === 0) {
        mensagem.html("<div class='text-danger'>Selecione pelo menos um equipamento necessário.</div>");
        return;
    }

    // se chegou até aqui, está válido
    $("#botoes_incluir").addClass("d-none"); // esconde botões
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");

    // exemplo de envio via AJAX
    $.ajax({
        url: "includes/rh_equipamentos_aj1.php",
        method: "POST",
        data: $("form").serialize(),
        success: function (resposta) {
            let data = JSON.parse(resposta);
            mensagem.html(data.msg);
            setTimeout(() => {
                window.location.reload(); // recarrega a página após 3 segundos
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function () {
            mensagem.html("<div class='text-danger'>Erro ao enviar solicitação.</div>");
            $("#botoes_incluir").removeClass("d-none"); // reexibe botões em caso de falha
            setTimeout(() => {
                mensagem.html(""); // limpa mensagem após 3 segundos
            }, 3000); // Ajuste o tempo conforme necessário
        }
    });
}

function constroiTabela_solicitacoes() {

    dataTable = new DataTable('#tabelaSolicitacoes', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": 10, // Define a quantidade de linhas
        "ajax": {
            "url": "includes/rh_equipamentos_aj2.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0,3],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

function constroiTabela_termos() {

    dataTable = new DataTable('#tabelaEquipamentos', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": 10, // Define a quantidade de linhas
        "ajax": {
            "url": "includes/rh_equipamentos_aj4.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0,2,3],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

function f_ver_solicitacao( id ) {
    $.post("includes/rh_equipamentos_aj3.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        console.log($("#view_responsavel").html());

        $("#view_responsavel").html(resposta.nome);
        $("#view_usuario").html(resposta.usuario_final);
        $("#view_equipamentos").html(resposta.equipamentos);
        $("#view_observacoes").html(resposta.observacao);
        $("#view_criado_em").html(resposta.criado_em);
        $("#view_criado_por").html(resposta.criado_por);
        $("#view_glpi").html(resposta.glpi_id);
    });
    verModalSolic.show();
}

function f_ver_termo(id){
    $.post("includes/rh_equipamentos_aj5.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        $("#vw_termo_responsavel").html(resposta.nome);
        $("#vw_termo_endereco").html(resposta.endereco);
        $("#vw_termo_email").html(resposta.termo_email);
        $("#vw_termo_celular").html(resposta.termo_celular);
        $("#vw_termo_equipamentos").html(resposta.tabela);
        $("#vw_termo_criado_em").html(resposta.criado_em);
        $("#vw_termo_criado_por").html(resposta.criado_por);
        $("#vw_termo_status").html(resposta.dsStatus);
        //
        let filename = "docs/pessoa_" + resposta.idPessoa + "/" + resposta.arquivo;
        let url  = '<embed src="'+filename+'" type="application/pdf" width="100%" height="400px" />';
        $("#view_documento").html( url);
        $("#vw_termo_documento").val( filename );
    });
    verModalTermo.show();
}

function f_preview_documento() {
    let filename = $("#vw_termo_documento").val();
    if (filename) {
        window.open(filename, '_blank'); // abre em nova guia
    } else {
        alert("Nenhum documento disponível para visualização.");
    }
}
