var dataTable;
var linhasPorPagina = 12;

//const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar"));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));
const verModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário

    $('#observacao, #e_observacao').summernote({
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

    function configurarAutocomplete(seletor, modal) {
        $(seletor).autocomplete({
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
                                idPessoa: item.idPessoa,
                                idColab: item.idColab,
                                salario: item.salario
                            };
                        }));
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro na requisição:", status, error);
                    }
                });
            },
            minLength: 2,
            appendTo: modal, // garante que o menu fique dentro da modal correta
            select: function (event, ui) {
                $(seletor).val(ui.item.nome);

                // seta os hidden fields correspondentes ao form atual
                if (modal === "#modalIncluir") {
                    $("#formIncluir #idPessoa").val(ui.item.idPessoa);
                    $("#formIncluir #idColab").val(ui.item.idColab);
                } else if (modal === "#modalEditar") {
                    $("#formEditar #e_idPessoa").val(ui.item.idPessoa);
                    $("#formEditar #e_idColab").val(ui.item.idColab);
                }
            }
        });
    }

    // aplica nos dois forms
    configurarAutocomplete("#formIncluir #nome", "#modalIncluir");
    configurarAutocomplete("#formEditar #e_nome", "#modalEditar");

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
            "url": "rh_ctr_exp_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 3, 4, 5, 6, 7, 8],
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
    f_limpar();
    setTimeout(() => {
        $("#nome").focus();
    }, 1000); // Ajuste o tempo conforme necessário
    return true;
}

function f_incluir_commit() {
    // Validação dos campos
    let mensagem = $("#msgAlertaIncluir");
    if ($("#nome").val() === "") {
        alert("Por favor, informe o nome do Colaborador...");
        $("#nome").focus();
        return false;
    }

    if ($("#data_inicio").val() === "") {
        alert("Por favor, informe o Data de Início do Contrato");
        $("#data_inicio").focus();
        return false;
    }

    if ($("#duracao").val() === "") {
        alert("Por favor, selecione a Duração do Contrato");
        $("#duracao").focus();
        return false;
    }

    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#observacao").val($("#observacao").summernote('code'));

    //
    $("#botoes_incluir").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formIncluir"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_ctr_exp_aj2.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#botoes_incluir").show();
                incModal.hide();
                mensagem.html("");
                f_limpar();
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

function f_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#divAlertaPrincipal").show();
        $.post("includes/rh_ctr_exp_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#divAlertaPrincipal").html(dados.msg);
            //
            setTimeout(function () {
                $("#divAlertaPrincipal").html("");
                selecionou();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function f_editar(id) {
    //
    $.post("includes/rh_ctr_exp_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #e_idContrato").val(id);
        $("#formEditar #e_nome").val(dados.nome);
        $("#formEditar #e_idColab").val(dados.idColab);
        $("#formEditar #e_idPessoa").val(dados.idPessoa);
        $("#formEditar #e_dtInicial").val(dados.data_inicio);
        $("#formEditar #e_duracao").val(dados.duracao);
        $("#formEditar #e_dtProrrogacao").val(dados.data_prorrogacao);
        $("#formEditar #e_dtFinal").val(dados.data_fim);
        $("#formEditar #e_observacao").summernote('code', dados.observacoes);
        //
    });
}

function f_editar_commit() {
    //
    let mensagem = $("#msgAlertaEditar");
    //
    // Validação dos campos
    if ($("#e_idColab").val() === "") {
        alert("Por favor, informe o nome do Colaborador...");
        $("#nome").focus();
        return false;
    }

    if ($("#e_dtInicial").val() === "") {
        alert("Por favor, informe o Data de Início do Contrato");
        $("#e_dtInicial").focus();
        return false;
    }

    if ($("#e_duracao").val() === "") {
        alert("Por favor, selecione a Duração do Contrato");
        $("#e_duracao").focus();
        return false;
    }
    //
    $("#botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#e_observacao").val($("#e_observacao").summernote('code'));

    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditar"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_ctr_exp_aj3.php",
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
    $.post("includes/rh_ctr_exp_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);

        // Preenche campos de texto
        $("#v_nome").text(dados.nome || "N/D");
        $("#v_dtInicial").text(
            dados.data_inicio
                ? new Date(dados.data_inicio).toLocaleDateString("pt-BR", { timeZone: "UTC" })
                : "N/D"
        );
        $("#v_dtProrrogacao").text(
            dados.data_prorrogacao
                ? new Date(dados.data_prorrogacao).toLocaleDateString("pt-BR", { timeZone: "UTC" })
                : "N/D"
        );
        $("#v_dtFinal").text(
            dados.data_fim
                ? new Date(dados.data_fim).toLocaleDateString("pt-BR", { timeZone: "UTC" })
                : "N/D"
        );
        $("#v_duracao").text(dados.duracao || "N/D");
        $("#v_observacao").html(dados.observacoes || "N/D");

        // --- Documento PDF ---
        if (dados.arquivo && dados.arquivo !== "") {
            // Caminho do arquivo (ajuste o diretório conforme sua estrutura)
            let caminho = "docs/pessoa_" + dados.idPessoa + "/" + dados.arquivo;

            // Seta o preview no iframe
            $("#v_previewDoc").attr("src", caminho);

            // Ativa o botão para abrir em nova guia
            $("#v_btnVisualizarDoc").attr("href", caminho).show();
        } else {
            // Se não tem arquivo, limpa e desabilita
            $("#v_previewDoc").attr("src", "");
            $("#v_btnVisualizarDoc").hide();
        }

        // Mostra a modal
        $("#modalVisualizar").modal("show");
    });
}

function f_limpar() {
    // Limpa os campos do formulário
    $("#nome").val("");
    $("#duracao").val("");
    $("#dtProrrogacao").val("");
    $("#dtFinal").val("");
    $("#observacao").summernote('code', ""); // Limpa o conteúdo do Summernote

    // Exibe uma mensagem (opcional)
    //alert("Campos resetados com sucesso!");
}

function f_reset_editar() {
    let id = $("#formEditar #e_idColab").val(); // id do contrato ou colaborador

    $.post("includes/rh_ctr_exp_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);

        // repopula os campos com os valores originais
        $("#formEditar #e_idColab").val(idColab);
        $("#formEditar #e_nome").val(dados.nome);
        $("#formEditar #e_idPessoa").val(dados.idPessoa);
        $("#formEditar #e_dtInicial").val(dados.data_inicio);
        $("#formEditar #e_duracao").val(dados.duracao);
        $("#formEditar #e_dtProrrogacao").val(dados.data_prorrogacao);
        $("#formEditar #e_dtFinal").val(dados.data_fim);
        $("#formEditar #e_observacao").val(dados.observacoes);
    });
}


//
//-- A PARTIR DE HOJE
//

document.addEventListener("DOMContentLoaded", function () {
    function setupCalculoDatas(idInicial, idDuracao, idFinal, idProrrogacao) {
        const dtInicial = document.getElementById(idInicial);
        const duracao = document.getElementById(idDuracao);
        const dtFinal = document.getElementById(idFinal);
        const dtProrrogacao = document.getElementById(idProrrogacao);

        if (!dtInicial || !duracao || !dtFinal || !dtProrrogacao) return;

        function calcularDatas() {
            if (!dtInicial.value || !duracao.value) {
                dtFinal.value = "";
                dtProrrogacao.value = "";
                return;
            }

            let inicio = new Date(dtInicial.value);
            let diasTotal = 0;
            let dataProrrogacao = null;

            if (duracao.value.includes("+")) {
                let partes = duracao.value.split("+");
                let p1 = parseInt(partes[0]);
                let p2 = parseInt(partes[1]);

                diasTotal = p1 + p2;

                // Calcula data da prorrogação
                let prorrogacao = new Date(inicio);
                prorrogacao.setDate(prorrogacao.getDate() + p1);
                dataProrrogacao = prorrogacao;

            } else {
                diasTotal = parseInt(duracao.value);
            }

            // Calcula data final
            let fim = new Date(inicio);
            fim.setDate(fim.getDate() + diasTotal);

            function formatarData(data) {
                let ano = data.getFullYear();
                let mes = String(data.getMonth() + 1).padStart(2, "0");
                let dia = String(data.getDate()).padStart(2, "0");
                return `${ano}-${mes}-${dia}`;
            }

            dtFinal.value = formatarData(fim);
            dtProrrogacao.value = dataProrrogacao ? formatarData(dataProrrogacao) : "";
        }

        dtInicial.addEventListener("change", calcularDatas);
        duracao.addEventListener("change", calcularDatas);
    }

    // Ativa para o formulário Incluir
    setupCalculoDatas("dtInicial", "duracao", "dtFinal", "dtProrrogacao");

    // Ativa para o formulário Editar
    setupCalculoDatas("e_dtInicial", "e_duracao", "e_dtFinal", "e_dtProrrogacao");
});
