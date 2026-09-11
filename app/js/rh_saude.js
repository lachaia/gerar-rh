var dataTable;
var linhasPorPagina = 12;

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar"));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));
const addModal = new bootstrap.Modal(document.getElementById("modalAddTipoDoc"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário

    $('#formIncluir #obs, #formEditar #obs').summernote({
        height: 150,
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

    $(function () {
        
        $("#formEditar #nmPessoa").autocomplete({
            source: function (request, response) {
                //console.log("Termo digitado:", request.term); // << debug aqui
                $.ajax({
                    url: "includes/buscar_colab.php",
                    type: "GET",
                    dataType: "json",
                    data: { term: request.term },
                    success: function (data) {
                        //console.log("Dados recebidos:", data); // << debug aqui
                        response($.map(data, function (item) {
                            return {
                                label: item.nome,
                                value: item.nome,
                                idPessoa: item.idPessoa,
                                idColab: item.idColab
                            };
                        }));
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro na requisição:", status, error);
                    }
                });
            },
            minLength: 2,
            appendTo: "#modalEditar", // garante que fique dentro da modal
            select: function (event, ui) {
                $("#formEditar #nmPessoa").val(ui.item.nome);
                $("#formEditar #idPessoa").val(ui.item.idPessoa);
                $("#formEditar #idColab").val(ui.item.idColab);
            }
        });

        $("#formIncluir #nmPessoa").autocomplete({
            source: function (request, response) {
                //console.log("Termo digitado:", request.term); // << debug aqui
                $.ajax({
                    url: "includes/buscar_colab.php",
                    type: "GET",
                    dataType: "json",
                    data: { term: request.term },
                    success: function (data) {
                        //console.log("Dados recebidos:", data); // << debug aqui
                        response($.map(data, function (item) {
                            return {
                                label: item.nome,
                                value: item.nome,
                                idPessoa: item.idPessoa,
                                idColab: item.idColab
                            };
                        }));
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro na requisição:", status, error);
                    }
                });
            },
            minLength: 2,
            appendTo: "#modalIncluir", // garante que fique dentro da modal
            select: function (event, ui) {
                $("#formIncluir #nmPessoa").val(ui.item.nome);
                $("#formIncluir #idPessoa").val(ui.item.idPessoa);
                $("#formIncluir #idColab").val(ui.item.idColab);
            }
        });
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
            "url": "rh_saude_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 2, 5, 6, 8],
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
        $("#nmPessoa").focus();
    }, 1000); // Ajuste o tempo conforme necessário
    return true;
}

function f_incluir_commit() {
    // Validação dos campos
    let mensagem = $("#msgAlertaIncluir");
    //
    let arquivo     = $("#formIncluir #arquivo")[0].files[0];
    let nmPessoa    = $("#formIncluir #nmPessoa");
    let idExameTipo = $("#formIncluir #idExameTipo");
    let dsExame     = $("#formIncluir #dsExame");
    let nmClinica   = $("#formIncluir #nmClinica");
    let nmMedico    = $("#formIncluir #nmMedico");
    //
    if (nmPessoa.val() === "") {
        alert("Por favor, informe o nome do Colaborador.");
        nmPessoa.focus();
        return false;
    }
    if (idExameTipo.val() === "" || idExameTipo.val() == 0) {
        alert("Por favor, informe o Tipo do Exame");
        idExameTipo.focus();
        return false;
    }
    if (dsExame.val() === "") {
        alert("Por favor, informe o título do Exame");
        dsExame.focus();
        return false;
    }
    if (nmClinica.val() === "") {
        alert("Por favor, informe o nome da Clínica");
        nmClinica.focus();
        return false;
    }
    if (nmMedico.val() === "") {
        alert("Por favor, informe o nome do Médico");
        nmMedico.focus();
        return false;
    }
    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#obs").val($("#obs").summernote('code'));
    //
    $("#botoes_incluir").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formIncluir"));
    formData.append("arquivo", arquivo);
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_saude_aj2.php",
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
    let mensagem = $("#msgAlertaExame");
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    // 
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
        //
        $.post("includes/rh_saude_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            mensagem.html(dados.msg);
            //
            setTimeout(function () {
                selecionou();
                mensagem.html("");
                mensagem.hide();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function f_editar(id) {
    //
    $.post("includes/rh_saude_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #idExame").val(id);
        $("#formEditar #nmPessoa").val(dados.nome);
        $("#formEditar #idPessoa").val(dados.idPessoa);
        $("#formEditar #idColab").val(dados.idColab);
        $("#formEditar #data").val(dados.data);
        $("#formEditar #dtVencimento").val(dados.dtValidade);
        $("#formEditar #idExameTipo").val(dados.idExameTipo);
        $("#formEditar #dsExame").val(dados.nmExame);
        $("#formEditar #nmClinica").val(dados.nmClinica);
        $("#formEditar #nmMedico").val(dados.nmMedico);
        $("#formEditar #status").val(dados.status);
        $("#formEditar #idExameTipo").val(dados.idExameTipo);
        $("#formEditar #obs").summernote('code', dados.observacoes);
        //
    });
}

function f_editar_commit() {
    // Validação dos campos
    let mensagem = $("#msgAlertaEditar");
    //
    let arquivo     = $("#formEditar #arquivo")[0].files[0];
    let nmPessoa    = $("#formEditar #nmPessoa");
    let idExameTipo = $("#formEditar #idExameTipo");
    let dsExame     = $("#formEditar #dsExame");
    let nmClinica   = $("#formEditar #nmClinica");
    let nmMedico    = $("#formEditar #nmMedico");
    //
    if (nmPessoa.val() === "") {
        alert("Por favor, informe o nome do Colaborador.");
        nmPessoa.focus();
        return false;
    }
    if (idExameTipo.val() === "" || idExameTipo.val() == 0) {
        alert("Por favor, informe o Tipo do Exame");
        idExameTipo.focus();
        return false;
    }
    if (dsExame.val() === "") {
        alert("Por favor, informe o título do Exame");
        dsExame.focus();
        return false;
    }
    if (nmClinica.val() === "") {
        alert("Por favor, informe o nome da Clínica");
        nmClinica.focus();
        return false;
    }
    if (nmMedico.val() === "") {
        alert("Por favor, informe o nome do Médico");
        nmMedico.focus();
        return false;
    }
    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#obs").val($("#obs").summernote('code'));
    //
    $("#botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditar"));
    formData.append("arquivo", arquivo);
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_saude_aj3.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#botoes_editar").show();
                altModal.hide();
                mensagem.html("");
                f_reset();
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

    $.post("includes/rh_saude_aj1.php", { id: id, origem: 'visualizar' }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_nome").html(dados.nome);
        $("#v_data").text(dados.data);
        $("#v_tipo").html(dados.dsExame);
        $("#v_vencimento").html(dados.dtValidade);
        $("#v_dsExame").html(dados.nmExame);
        $("#v_nmClinica").html(dados.nmClinica);
        $("#v_nmMedico").html(dados.nmMedico);
        $("#v_status").html(dados.status);
        $("#v_obs").html(dados.observacoes);
        $("#v_nomeArquivo").html(dados.arquivo);
        // Abrir o modal de visualização
        visModal.show();
        //
        if (dados.arquivo) {
            $("#v_nomeArquivo").text(dados.arquivo);
            $("#v_linkArquivo")
                .attr("href", "docs/pessoa_" + dados.idPessoa + "/" + encodeURIComponent(dados.arquivo))
                .attr("download", dados.arquivo)
                .show();
        } else {
            $("#v_nomeArquivo").text("Nenhum arquivo disponível");
            $("#v_linkArquivo").hide();
        }

        //        
    });

}

function f_limpar() {
    // Limpa os campos do formulário
    $("#obs").summernote('code', ""); // Limpa o conteúdo do Summernote

    // Exibe uma mensagem (opcional)
    //alert("Campos resetados com sucesso!");
}

function f_reset() {
    let id = $("#formEditar #idExame").val();
    $.post("includes/rh_saude_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #idExame" ).val(id);
        $("#formEditar #nmPessoa").val(dados.nome    );
        $("#formEditar #idPessoa").val(dados.idPessoa);
        $("#formEditar #idColab" ).val(dados.idColab );
        $("#formEditar #data" ).val(dados.data );
        $("#formEditar #idExameTipo" ).val(dados.idExameTipo );
        $("#formEditar #dtVencimento" ).val(dados.dtValidade );
        $("#formEditar #dsExame" ).val(dados.nmExame );
        $("#formEditar #nmClinica" ).val(dados.nmClinica );
        $("#formEditar #nmMedico" ).val(dados.nmMedico );
        $("#formEditar #status" ).val(dados.status );
        $("#formEditar #obs").summernote('code', dados.observacoes);
        //
    });
}

function verificaArquivo() {
    const input = document.getElementById("arquivo");
    const arquivo = input.files[0];

    if (!arquivo) return; // Nenhum arquivo selecionado

    const tamanhoMax = 5 * 1024 * 1024; // 5MB

    // Verifica tipo
    if (arquivo.type !== "application/pdf") {
        alert("Por favor, selecione um arquivo PDF.");
        input.value = ""; // Limpa o campo
        return;
    }

    // Verifica tamanho
    if (arquivo.size > tamanhoMax) {
        alert("Arquivo muito grande! O tamanho máximo permitido é 5MB.");
        input.value = "";
        return;
    }

    // Exibe nome do arquivo (opcional)
    console.log("Arquivo selecionado:", arquivo.name);
}

function f_ver_doc(idPessoa, arquivo) {
    let url = "docs/pessoa_" + idPessoa + "/" + arquivo;
    let win = window.open(url, '_blank');
}

function add_tipo_doc(){
    addModal.show();
    setTimeout(() => {
        $("#nmTipoDoc").focus();
    }, 1000); // Ajuste o tempo conforme necessário
}

function tipo_doc_commit(){
    let nmTipoDoc = $("#nmTipoDoc");
    let validade = $("#validade");
    //
    if (nmTipoDoc.val() === "") {
        alert("Por favor, informe o nome do Tipo de Documento.");
        nmTipoDoc.focus();
        return false;
    }
    if (validade.val() === "") {
        alert("Por favor, informe a Validade do Tipo de Documento.");
        validade.focus();
        return false;
    }
    //
    $("#btnSalvarTipoDoc").hide();
    $.post("includes/rh_saude_aj5.php", { nmTipoDoc: nmTipoDoc.val(), validade: validade.val() }, function (retorno) {
        const dados = JSON.parse(retorno);
        $("#msgAlertaTipoDoc").html(dados.msg);
        //
        if (dados.id && dados.nome) {
            // Adiciona o novo tipo ao select e já seleciona
            $("#idTipoDoc").append(
                `<option value="${dados.id}" selected>${dados.nome}</option>`
            );
            $("#idTipoDoc").val(dados.id).trigger('change'); // caso use select2
        }        
        //
        setTimeout(() => {
            $("#btnSalvarTipoDoc").show();
            addModal.hide();
            $("#msgAlertaTipoDoc").html("");
            //selecionou();
        }, 3000); // Ajuste o tempo conforme necessário
    });
}