//***********************************************************************************************************//
//***********************************************************************************************************//
//*****************************                                         *************************************//
//*****************************        A F A S T A M E N T O S          *************************************//
//*****************************                                         *************************************//
//***********************************************************************************************************//
//*

const afa_visModal = new bootstrap.Modal(document.getElementById("modalAfaVisualizar"));
const afa_incModal = new bootstrap.Modal(document.getElementById("modalAfaIncluir"));
const afa_altModal = new bootstrap.Modal(document.getElementById("modalAfaEditar"));

let dataTableAfastamento = "";

$(document).ready(function () {

    f_afa_constroi_tabela();

});

function f_afa_constroi_tabela() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTableAfastamento = new DataTable('#tabAfastamentos', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "columnDefs": [{
            "targets": [0, 3, 4, 5],
            "className": "text-center"
        }],
        language: {
            url: '../includes/pt-BR.json',
        },
    });
}

function f_afa_visualizar(id) {

    $.post("../includes/rh_afastamento_aj2.php", { id: id, origem: 'colaborador' }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_afa_nome").html(dados.nome);
        $("#v_afa_data").html(dados.data_inicio);
        $("#v_afa_qtd").html(dados.dias_afastado);
        $("#v_afa_retorno").html(dados.data_retorno);
        $("#v_afa_tipo").html(dados.descricao);
        $("#v_afa_emitido_por").html(dados.emitido_por);
        $("#v_afa_cid").html(dados.cid);
        $("#v_afa_quando").html(dados.criado_em + " por " + dados.login);
        $("#v_afa_arquivo").html(dados.arquivo_link);
        // Abrir o modal de visualização
        afa_visModal.show();
    });

}

function f_afa_incluir() {
    afa_incModal.show();
    setTimeout(() => {
        $("#_nome").focus();
    }, 1000); // Ajuste o tempo conforme necessário
    return true;
}

function f_afa_incluir_commit() {
    // Validação dos campos
    let mensagem = $("#afa_msgAlertaIncluir");
    //

    if ($("#afa_data").val() === "") {
        alert("Por favor, informe o data do atestado.");
        $("#afa_data").focus();
        return false;
    }

    if ($("#afa_qtd").val() === "" || parseInt($("#afa_qtd").val()) <= 0) {
        alert("Por favor, informe uma quantidade de dias de afastamento válida...");
        $("#afa_qtd").focus();
        return false;
    }

    if ($("#formIncluirAfastamento #idTipo").val() === "" || parseInt($("#formIncluirAfastamento #idTipo").val()) <= 0) {
        alert("Por favor, informe o tipo de afastamento.");
        $("#formIncluirAfastamento #idTipo").focus();
        return false;
    }

    if ($("#afa_emitidoPor").val() === "") {
        alert("Por favor, informe quem emitiu o atestado.");
        $("#afa_emitidoPor").focus();
        return false;
    }

    // CID - Opcional.

    if ($("#afa_arquivo").get(0).files.length === 0) {
        alert("Por favor, selecione um arquivo para upload.");
        $("#afa_arquivo").focus();
        return false;
    }

    //
    $("#afa_botoes_incluir").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formIncluirAfastamento"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "../includes/rh_afastamento_aj1.php",
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

function calc_dias(origem) {
    // pegue os elementos corretos conforme a origem
    let elData, elQtd, elRetorno;

    if (origem === 'incluir') {
        elData = document.getElementById('afa_data');
        elQtd = document.getElementById('afa_qtd');
        elRetorno = document.getElementById('afa_dtRetorno');
    } else {
        elData = document.getElementById('e_afa_data');
        elQtd = document.getElementById('e_afa_qtd');
        elRetorno = document.getElementById('e_afa_dtRetorno');
    }

    // valores
    const data_inicial = elData ? elData.value : '';
    const qtd = parseInt(elQtd ? elQtd.value : '', 10);

    if (!elRetorno) return; // segurança

    // validação
    if (!data_inicial || !Number.isFinite(qtd) || qtd <= 0) {
        elRetorno.value = '';
        return;
    }

    // monta a data sem risco de fuso (YYYY-MM-DD -> numbers)
    const [ano, mes, dia] = data_inicial.split('-').map(Number);
    const d = new Date(ano, mes - 1, dia);

    // soma os dias de afastamento: retorno = início + qtd
    d.setDate(d.getDate() + qtd);

    // formata YYYY-MM-DD
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');

    elRetorno.value = `${y}-${m}-${dd}`;
}


function desloca(url) {
    $(url).focus();
}

function f_afa_editar(id) {
    $.post("../includes/rh_afastamento_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        afa_altModal.show();
        $("#formEditarAfastamento #e_afa_id").val(dados.id);
        $("#formEditarAfastamento #e_afa_nome").val(dados.nome);
        $("#formEditarAfastamento #e_afa_idColab").val(dados.idColab);
        $("#formEditarAfastamento #e_afa_idPessoa").val(dados.idPessoa);
        $("#formEditarAfastamento #e_afa_data").val(dados.data_inicio);
        $("#formEditarAfastamento #e_afa_qtd").val(dados.dias_afastado);
        $("#formEditarAfastamento #e_afa_dtRetorno").val(dados.data_retorno);
        $("#formEditarAfastamento #e_afa_emitidoPor").val(dados.emitido_por);
        $("#formEditarAfastamento #e_afa_cid").val(dados.cid);
        $("#formEditarAfastamento #idTipo").val(dados.idTipo);
        //
    });
}

function f_afa_reset() {
    let id = $("#formEditarAfastamento #e_afa_id").val();
    //
    $.post("../includes/rh_afastamento_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        afa_altModal.show();
        $("#formEditarAfastamento #e_afa_id").val(dados.id);
        $("#formEditarAfastamento #e_afa_nome").val(dados.nome);
        $("#formEditarAfastamento #e_afa_idColab").val(dados.idColab);
        $("#formEditarAfastamento #e_afa_idPessoa").val(dados.idPessoa);
        $("#formEditarAfastamento #e_afa_data").val(dados.data_inicio);
        $("#formEditarAfastamento #e_afa_qtd").val(dados.dias_afastado);
        $("#formEditarAfastamento #e_afa_dtRetorno").val(dados.data_retorno);
        $("#formEditarAfastamento #e_afa_emitidoPor").val(dados.emitido_por);
        $("#formEditarAfastamento #e_afa_cid").val(dados.cid);
        $("#formEditarAfastamento #idTipo").val(dados.idTipo);
        //
    });
}

function f_afa_editar_commit() {
    //
    let data = $("#formEditarAfastamento #e_afa_data").val().trim();
    let qtd = $("#formEditarAfastamento #e_afa_qtd").val().trim();
    let dtRetorno = $("#formEditarAfastamento #e_afa_dtRetorno").val().trim();
    let emitido_por = $("#formEditarAfastamento #e_afa_emitidoPor").val().trim();
    let cid = $("#formEditarAfastamento #e_afa_cid").val().trim();
    let idTipo = $("#formEditarAfastamento #idTipo").val().trim();

    let mensagem = $("#msgAlertaEditarAfastamento");

    // Validação dos campos obrigatórios

    if (data === "") {
        alert("Informe a data de início");
        $("#formEditarAfastamento #e_afa_data").focus();
        return;
    }
    if (qtd === "" || isNaN(qtd)) {
        alert("Informe a quantidade de dias (número válido)");
        $("#formEditarAfastamento #e_afa_qtd").focus();
        return;
    }
    if (dtRetorno === "") {
        alert("Informe a data de retorno");
        $("#formEditarAfastamento #e_afa_dtRetorno").focus();
        return;
    }
    if (idTipo === "") {
        alert("Informe o tipo de afastamento");
        $("#formEditarAfastamento #idTipo").focus();
        return;
    }
    if (emitido_por === "") {
        alert("Informe quem emitiu o atestado");
        $("#formEditarAfastamento #e_afa_emitidoPor").focus();
        return;
    }
    if (cid === "") {
        alert("Informe o CID");
        $("#formEditarAfastamento #e_afa_cid").focus();
        return;
    }

    //
    $("#afa_botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //

    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditarAfastamento"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "../includes/rh_afastamento_aj3.php",
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

function f_afa_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#divAlertaAfastamento").show();
        $.post("../includes/rh_afastamento_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#divAlertaAfastamento").html(dados.msg);
            //
            setTimeout(function () {
                $("#divAlertaAfastamento").html("");
                window.location.reload();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
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
