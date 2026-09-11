/*
    ***********************************************************************************************************
    ***********************************************************************************************************
    *****************************                                         *************************************
    *****************************              T E R M O S                *************************************
    *****************************                                         *************************************
    ***********************************************************************************************************
    ***********************************************************************************************************
*/

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar"));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));
const tipModal = new bootstrap.Modal(document.getElementById("modalIncluirTipo"));

let dataTableTermos = "";

$(document).ready(function () {

    f_trm_constroi_tabela();

});

function f_trm_incluir() {
    incModal.show();
    setTimeout(() => {
        $("#_data").focus();
    }, 1000); // Ajuste o tempo conforme necessário
    return true;
}

function f_trm_incluir_commit() {
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
        url: "../includes/rh_termo_aj1.php",
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

function f_trm_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#divAlertaTermo").show();
        $.post("../includes/rh_termo_aj4.php", { id: id }, function (response) {
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

function f_trm_editar(id) {
    //
    $.post("../includes/rh_termo_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditarTRM #e_id").val(dados.id);
        $("#formEditarTRM #e_nome").val(dados.nome);
        $("#formEditarTRM #e_idColab").val(dados.idColab);
        $("#formEditarTRM #e_idPessoa").val(dados.idPessoa);
        $("#formEditarTRM #e_data").val(dados.data);
        $("#formEditarTRM #idTipo").val(dados.idTipoTermo);
        $("#formEditarTRM #dsTipo").val(dados.descricao);
        //
    });
}

function f_trm_editar_commit() {
    //
    let nome = $("#formEditarTRM #e_nome");
    let idColab = $("#formEditarTRM #e_idColab");
    let idPessoa = $("#formEditarTRM #e_idPessoa");
    let data = $("#formEditarTRM #e_data");
    let idTipo = $("#formEditarTRM #idTipo");

    let mensagem = $("#msgAlertaEditar");

    if (idColab.val().trim() === "") {
        alert("Por favor, informe o Colaborador...");
        nome.focus();
        return false;
    }

    if (data.val().trim() === "") {
        alert("Por favor, informe o data do atestado.");
        data.focus();
        return false;
    }

    if (idTipo.val().trim() === "" || parseInt(idTipo.val()) <= 0) {
        alert("Por favor, informe o tipo do Termo.");
        idTipo.focus();
        return false;
    }
    //
    $("#botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //

    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditarTRM"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "../includes/rh_termo_aj3.php",
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

function f_trm_visualizar(id) {

    $.post("../includes/rh_termo_aj2.php", { id: id, origem: 'colaborador' }, function (retorno) {
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

function f_trm_incluir_novo_tipo() {
    tipModal.show();
}

function f_trm_incluir_tipo_commit() {
    var nome = $("#formIncluirTipo #_nome_tipo");

    if (nome.val().trim() === "") {
        alert("Informe o novo tipo de termo.");
        nome.focus();
        return;
    }

    $.post("../includes/rh_termo_aj5.php", { descricao: nome.val().trim() }, function (retorno) {
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
                $("#_nome_tipo").val("");
            } else {
                alert("Erro ao incluir tipo: " + (data.erro || "desconhecido"));
            }
        } catch (e) {
            alert("Erro no retorno do servidor.");
            console.error("Retorno inválido:", retorno);
        }
    });
}

async function selecionou() {
    // Destroy the existing DataTable
    /*
    dataTableTermos.clear().draw();
    dataTableTermos.destroy();
    f_trm_constroi_tabela();
    */
    document.location.reload(true);
}

function f_trm_constroi_tabela() {
    dataTableTermos = new DataTable('#tabTermos', {
        "processing": true,
        "serverSide": false,
        "responsive": true,
        "info": false,
        "order": [0, "asc"],
        "pageLength": 10, // Define a quantidade de linhas
        "columnDefs": [{
            "targets": [0, 2, 3],
            "className": "text-center"
        }],
        language: {
            url: '../includes/pt-BR.json',
        },
    });
}