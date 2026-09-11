//
//- rh_termos.js | Rotinas de rh_termos.php
//

var dataTable;
var linhasPorPagina = 12;

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar"));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));

const tipModal = new bootstrap.Modal(document.getElementById("modalIncluirTipo"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário


    $("#_nome").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "includes/buscar_colab.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idColab: item.idColab,  // <- chave personalizada
                            idPessoa: item.idPessoa  // <- chave personalizada
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            $("#_idColab").val(ui.item.idColab); // <- usa a mesma chave
            $("#_idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
            //atualiza_enderecos( ui.item.idPessoa );
        }
    });


});

function constroiTabela() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTable = new DataTable('#example', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_termos_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0,3,4],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

async function selecionou() {
    // Destroy the existing DataTable
    //
    dataTable.clear().draw();
    dataTable.destroy();
    constroiTabela();
    //
}

function selecionou_tipo(formulario) {
    // Pega o texto da opção selecionada no select
    let texto = $(formulario + " #idTipo option:selected").text();

    // Atribui ao campo hidden
    $(formulario + " #dsTipo").val(texto);
}

window.onresize = selecionou; // Atualiza quando a tela for redimensionada

function f_incluir() {
    incModal.show();
    setTimeout(() => {
        $("#_nome").focus();
    }, 1000); // Ajuste o tempo conforme necessário
    return true;
}

function f_incluir_commit() {
    // Validação dos campos
    let mensagem = $("#msgAlertaIncluir");
    //
    if ($("#_idColab").val() === "") {
        alert("Por favor, informe o Colaborador...");
        $("#_nome").focus();
        return false;
    }

    if ($("#_data").val() === "") {
        alert("Por favor, informe o data do atestado.");
        $("#_data").focus();
        return false;
    }

    if ($("#formIncluir #idTipo").val() === "" || parseInt($("#formIncluir #idTipo").val()) <= 0) {
        alert("Por favor, informe o tipo do Termo.");
        $("#formIncluir #idTipo").focus();
        return false;
    }

    if ($("#_arquivo").get(0).files.length === 0) {
        alert("Por favor, selecione um arquivo para upload.");
        $("#_arquivo").focus();
        return false;
    }

    //
    $("#botoes_incluir").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formIncluir"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_termo_aj1.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                window.location.reload();
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
        },
        complete: function () {
            $(".btn-enviar").html('Enviar <i class="fa-solid fa-paper-plane"></i>');
            $(".btn-enviar").prop("disabled", false);
        }
    });
}

function f_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#divAlertaTermo").show();
        $.post("includes/rh_termo_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#divAlertaTermo").html(dados.msg);
            //
            setTimeout(function () {
                $("#divAlertaTermo").html("");
                selecionou();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function f_editar(id) {
    //
    $.post("includes/rh_termo_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #e_id").val(dados.id);
        $("#formEditar #e_nome").val(dados.nome);
        $("#formEditar #e_idColab").val(dados.idColab);
        $("#formEditar #e_idPessoa").val(dados.idPessoa);
        $("#formEditar #e_data").val(dados.data);
        $("#formEditar #idTipo").val(dados.idTipoTermo);
        $("#formEditar #dsTipo").val(dados.descricao);
        //
    });
}

function f_editar_commit() {
    //
    let nome     = $("#formEditar #e_nome");
    let idColab  = $("#formEditar #e_idColab");
    let idPessoa = $("#formEditar #e_idPessoa");
    let data     = $("#formEditar #e_data");
    let idTipo   = $("#formEditar #idTipo");

    let mensagem = $("#msgAlertaEditar");

    if ( idColab.val().trim() === "") {
        alert("Por favor, informe o Colaborador...");
        nome.focus();
        return false;
    }

    if ( data.val().trim() === "") {
        alert("Por favor, informe o data do atestado.");
        data.focus();
        return false;
    }

    if ( idTipo.val().trim() === "" || parseInt(idTipo.val()) <= 0) {
        alert("Por favor, informe o tipo do Termo.");
        idTipo.focus();
        return false;
    }
    //
    $("#botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //

    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditar"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_termo_aj3.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#botoes_editar").show();
                mensagem.html("");
                altModal.hide();
                selecionou();
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
        },
        complete: function () {
            $(".btn-enviar").html('Enviar <i class="fa-solid fa-paper-plane"></i>');
            $(".btn-enviar").prop("disabled", false);
        }
    });
}

function f_visualizar(id) {

    $.post("includes/rh_termo_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_nome").html(dados.nome);
        $("#v_data").html(dados.data);
        $("#v_tipo_trm").html(dados.descricao);
        $("#v_quando").html(dados.criado_em + " por " + dados.criado_por);
        $("#v_arquivo_trm").html(dados.arquivo_link);
        // Abrir o modal de visualização
        visModal.show();
    });

}

function verifica_data(o) {
    let dataDigitada = o.value;
    if (!dataDigitada) return;

    // Converte a string para objeto Date
    let partes = dataDigitada.split("-");
    let data = new Date(partes[0], partes[1] - 1, partes[2]);

    // Pega a data de hoje (sem hora)
    let hoje = new Date();
    hoje.setHours(0, 0, 0, 0); // zera o horário

    if (data > hoje) {
        alert("A data não pode ser maior que hoje.");
        o.value = ""; // limpa o campo
        o.focus();
    }
}

function incluir_novo_tipo(){
    tipModal.show();
}

function f_incluir_tipo_commit() {
    var nome = $("#formIncluirTipo #_nome");

    if (nome.val().trim() === "") {
        alert("Informe o novo tipo de termo.");
        nome.focus();
        return;
    }

    $.post("includes/rh_termo_aj5.php", { descricao: nome.val().trim() }, function (retorno) {
        // retorno esperado: { id: 123, descricao: "texto" }
        try {
            var data = JSON.parse(retorno);
            if (data.id) {
                // Adiciona no select
                $("#formIncluir #idTipo").append(
                    $("<option>", { value: data.id, text: data.descricao, selected: true })
                );

                // Fecha modal
                $("#modalIncluirTipo").modal("hide");

                // Limpa campo para próxima inclusão
                $("#_nome").val("");
            } else {
                alert("Erro ao incluir tipo: " + (data.erro || "desconhecido"));
            }
        } catch (e) {
            alert("Erro no retorno do servidor.");
            console.error("Retorno inválido:", retorno);
        }
    });
}
