var dataTable;
var linhasPorPagina = 12;

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar"));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));
const aprModal = new bootstrap.Modal(document.getElementById("modalAprovar"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário


    $("#afa_nome").autocomplete({
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
            $("#afa_idColab").val(ui.item.idColab); // <- usa a mesma chave
            $("#afa_idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
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
            "url": "rh_afastamentos_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 3, 4, 5, 6, 7],
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
    if ($("#afa_idColab").val() === "") {
        alert("Por favor, informe o Colaborador...");
        $("#afa_nome").focus();
        return false;
    }

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

    if ($("#formInformIncluirAfastamentocluir #idTipo").val() === "" || parseInt($("#formIncluirAfastamento #idTipo").val()) <= 0) {
        alert("Por favor, informe o tipo de afastamento.");
        $("#formIncluirAfastamento #idTipo").focus();
        return false;
    }

    if ($("#afa_emitidoPor").val() === "") {
        alert("Por favor, informe quem emitiu o atestado.");
        $("#afa_emitidoPor").focus();
        return false;
    }

    //- CID é opcional conforme Resolução CFM nº 1.658/2002

    if ($("#afa_arquivo").get(0).files.length === 0) {
        alert("Por favor, selecione um arquivo para upload.");
        $("#afa_arquivo").focus();
        return false;
    }

    //
    $("#botoes_incluir").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formIncluirAfastamento"));
    formData.append("origem", "RH");
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_afastamento_aj1.php",
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
        $("#divAlertaAfastamento").show();
        $.post("includes/rh_afastamento_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#divAlertaAfastamento").html(dados.msg);
            //
            setTimeout(function () {
                $("#divAlertaAfastamento").html("");
                selecionou();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function f_editar(id) {
    //
    $.post("includes/rh_afastamento_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #e_id").val(dados.id);
        $("#formEditar #e_nome").val(dados.nome);
        $("#formEditar #e_idColab").val(dados.idColab);
        $("#formEditar #e_idPessoa").val(dados.idPessoa);
        $("#formEditar #e_data").val(dados.data_inicio);
        $("#formEditar #e_qtd").val(dados.dias_afastado);
        $("#formEditar #e_dtRetorno").val(dados.data_retorno);
        $("#formEditar #idTipo").val(dados.idTipo);
        $("#formEditar #e_emitidoPor").val(dados.emitido_por);
        $("#formEditar #e_cid").val(dados.cid);
        //
    });
}

function f_editar_commit() {
    //
    let id = $("#formEditar #e_id").val().trim();
    let nome = $("#formEditar #e_nome").val().trim();
    let idColab = $("#formEditar #e_idColab").val().trim();
    let idPessoa = $("#formEditar #e_idPessoa").val().trim();
    let data = $("#formEditar #e_data").val().trim();
    let qtd = $("#formEditar #e_qtd").val().trim();
    let dtRetorno = $("#formEditar #e_dtRetorno").val().trim();
    let idTipo = $("#formEditar #idTipo").val().trim();
    let emitido_por = $("#formEditar #e_emitidoPor").val().trim();
    let cid = $("#formEditar #e_cid").val().trim();

    let mensagem = $("#msgAlertaEditar");

    // Validação dos campos obrigatórios
    if (nome === "") {
        alert("Informe o nome do cargo");
        $("#formEditar #e_nome").focus();
        return;
    }
    if (idColab === "") {
        alert("Informe o colaborador");
        $("#formEditar #e_idColab").focus();
        return;
    }
    if (idPessoa === "") {
        alert("Informe o ID da pessoa");
        $("#formEditar #e_idPessoa").focus();
        return;
    }
    if (data === "") {
        alert("Informe a data de início");
        $("#formEditar #e_data").focus();
        return;
    }
    if (qtd === "" || isNaN(qtd)) {
        alert("Informe a quantidade de dias (número válido)");
        $("#formEditar #e_qtd").focus();
        return;
    }
    if (dtRetorno === "") {
        alert("Informe a data de retorno");
        $("#formEditar #e_dtRetorno").focus();
        return;
    }
    if (idTipo === "") {
        alert("Informe o tipo de afastamento");
        $("#formEditar #idTipo").focus();
        return;
    }
    if (emitido_por === "") {
        alert("Informe quem emitiu o atestado");
        $("#formEditar #e_emitidoPor").focus();
        return;
    }
    if (cid === "") {
        alert("Informe o CID");
        $("#formEditar #e_cid").focus();
        return;
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
        url: "includes/rh_afastamento_aj3.php",
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

    $.post("includes/rh_afastamento_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_nome").html(dados.nome);
        $("#v_data").html(dados.data_inicio);
        $("#v_qtd").html(dados.dias_afastado);
        $("#v_retorno").html(dados.data_retorno);
        $("#v_tipo").html(dados.descricao);
        $("#v_emitido_por").html(dados.emitido_por);
        $("#v_cid").html(dados.cid);
        $("#v_quando").html(dados.criado_em + " por " + dados.login);
        $("#v_arquivo").html(dados.arquivo_link);
        // Abrir o modal de visualização
        visModal.show();
    });

}

function calc_dias() {
    let data_inicial = document.getElementById('afa_data').value;
    let qtd = parseInt(document.getElementById('afa_qtd').value);
    let data_final = document.getElementById('afa_dtRetorno');

    if (!data_inicial || isNaN(qtd) || qtd <= 0) {
        data_final.value = "";
        return;
    }

    let partes = data_inicial.split("-");
    let data = new Date(partes[0], partes[1] - 1, partes[2]);

    // Soma a quantidade de dias - 1 (último dia de afastamento) + 1 (retorno)
    data.setDate(data.getDate() + qtd);

    // Formata para yyyy-mm-dd
    let dia = String(data.getDate()).padStart(2, '0');
    let mes = String(data.getMonth() + 1).padStart(2, '0');
    let ano = data.getFullYear();

    data_final.value = `${ano}-${mes}-${dia}`;
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

function desloca(form) {
    $("#" + form + " #idTipo").focus();
}


function f_aprovar( id ){
    $.post("includes/rh_afastamento_aj2.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_apr_id").val( id );
        $("#v_apr_nome").html(dados.nome);
        $("#v_apr_data").html(dados.data_inicio);
        $("#v_apr_qtd").html(dados.dias_afastado);
        $("#v_apr_retorno").html(dados.data_retorno);
        $("#v_apr_tipo").html(dados.descricao);
        $("#v_apr_emitido_por").html(dados.emitido_por);
        $("#v_apr_cid").html(dados.cid);
        $("#v_apr_quando").html(dados.criado_em + " por " + dados.login);
        $("#v_apr_arquivo").html(dados.arquivo_link);
        // Abrir o modal de visualização
        aprModal.show();
    });    

}

function f_aprovar_commit( status ){
    let mensagem = $("#msgAlertaAprovar");
    let id = $("#v_apr_id").val();
    //
    $("#botoes_aprovar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.post("includes/rh_afastamento_aj5.php",{ id: id, status: status }, function(res){
        let retorno = JSON.parse( res );
        mensagem.html( retorno.msg );
        //
        setTimeout(() => {
            $("#botoes_aprovar").show();
            mensagem.html("");
            aprModal.hide();
            selecionou();
        }, 3000); // Ajuste o tempo conforme necessário        
    });
}