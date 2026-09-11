//
//- rh_cipa.js | CIPA
// (C)haia, 10/07/2025

// idModulo = 11;

var dataTableMembros;
var dataTableAtend;
var dataTableReunioes;
var dataTableAcoes;

var linhasPorPagina = 12;

const incModalMembro = new bootstrap.Modal(document.getElementById("modalIncluirMembro"));
const altModalMembro = new bootstrap.Modal(document.getElementById("modalEditarMembro"));
const incModalPessoa = new bootstrap.Modal(document.getElementById("modalIncluirPessoa"));
const verModalMembro = new bootstrap.Modal(document.getElementById("modalVerMembro"));

const incModalAtend = new bootstrap.Modal(document.getElementById("modalIncAtendimento"));
const altModalAtend = new bootstrap.Modal(document.getElementById("modalAltAtendimento"));
const incModalTipo = new bootstrap.Modal(document.getElementById("modalNovoTipoOcorrencia"));
const verModalAtend = new bootstrap.Modal(document.getElementById("modalVerAtendimento"));

const incModalReuniao = new bootstrap.Modal(document.getElementById("modalIncReuniao"));
const verModalReuniao = new bootstrap.Modal(document.getElementById("modalVerReuniao"));
const altModalReuniao = new bootstrap.Modal(document.getElementById("modalAltReuniao"));

const incModalAcao = new bootstrap.Modal(document.getElementById("modalIncAcao"));
const verModalAcao = new bootstrap.Modal(document.getElementById("modalVerAcao"));
const altModalAcao = new bootstrap.Modal(document.getElementById("modalAltAcao"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabelaMembros();
        constroiTabelaAtendimentos();
        constroiTabelaReunioes();
        constroiTabelaAcoes();
    }, 300); // Ajuste o tempo conforme necessário

    $("#nmPessoa").autocomplete({
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

    function initSummernote(selector) {
        if ($(selector).next(".note-editor").length) {
            $(selector).summernote("destroy");
        }

        $(selector).summernote({
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
                    $('.note-editable').css('color', 'black'); // ajuste conforme necessário
                }
            }
        });
    }


    // Aplica quando cada modal for exibido
    $('#modalIncReuniao').on('shown.bs.modal', function () {
        initSummernote('#formIncReuniao #observacoes');
    });

    $('#modalAltReuniao').on('shown.bs.modal', function () {
        initSummernote('#formAltReuniao #observacoes');
    });

    $('#modalIncAcao').on('shown.bs.modal', function () {
        initSummernote('#formIncAcao #observacoes');
    });

    $('#modalAltAcao').on('shown.bs.modal', function () {
        initSummernote('#formAltAcao #observacoes');
    });
});

window.onresize = selecionou; // Atualiza quando a tela for redimensionada

$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href"); // ex: #tabAtendimentos

    if (target === "#menuAtendimentos") {
        setTimeout(function () {
            dataTableAtend.columns.adjust().draw();
        }, 10);
    }
    if (target === "#menuReunioes") {
        setTimeout(function () {
            dataTableReunioes.columns.adjust().draw();
        }, 10);
    }
    if (target === "#menuAcoes") {
        setTimeout(function () {
            dataTableAcoes.columns.adjust().draw();
        }, 10);
    }
});


function selecionou() {
    dataTableMembros.clear().draw();
    dataTableMembros.destroy();
    constroiTabelaMembros();
    //
    dataTableAtend.clear().draw();
    dataTableAtend.destroy();
    constroiTabelaAtendimentos();
    //
    dataTableReunioes.clear().draw();
    dataTableReunioes.destroy();
    constroiTabelaReunioes();
    //
    dataTableAcoes.clear().draw();
    dataTableAcoes.destroy();
    constroiTabelaAcoes();
}

//-------------------------------------------------------------------------------------------
// - MEMBROS da CIPA
//

function constroiTabelaMembros() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTableMembros = new DataTable('#tabMembros', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "cipa/rh_cipa_aj1.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 3, 4, 5],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

function btn_incluir_membro() {
    incModalMembro.show();
}

function btnIncSalvarMembro() {
    //
    let mensagem = $("#msgAlertMembro");
    var idPessoa = $("#formIncMembro #idPessoa").val();
    var nmPessoa = $("#formIncMembro #nmPessoa").val();
    var idSubSede = $("#formIncMembro #idSubSede").val();
    var idCargo = $("#formIncMembro #idCargo").val();
    var dtInicio = $("#formIncMembro #dtInicio").val();
    var dtFinal = $("#formIncMembro #dtFinal").val();

    // Validação do campo Pessoa
    if (!idPessoa || isNaN(idPessoa) || Number(idPessoa) <= 0) {
        alert("Por favor, selecione uma Pessoa...");
        $("#formIncMembro #nmPessoa").focus();
        return false;
    }

    // Validação do campo Subsede
    if (!idSubSede || idSubSede == "0") {
        alert("Por favor, selecione uma SubSede...");
        $("#formIncMembro #idSubSede").focus();
        return false;
    }

    // Validação do campo Cargo
    if (!idCargo || idCargo == "0") {
        alert("Por favor, selecione um Cargo...");
        $("#formIncMembro #idCargo").focus();
        return false;
    }

    // Validação da Data de Início
    if (!dtInicio) {
        alert("Por favor, informe a Data de Início...");
        $("#formIncMembro #dtInicio").focus();
        return false;
    }

    // Validação da Data Final (opcional, mas se preenchida deve ser >= dtInicio)
    if (dtFinal) {
        var dInicio = new Date(dtInicio);
        var dFinal = new Date(dtFinal);

        if (dFinal < dInicio) {
            alert("A Data Final não pode ser anterior à Data de Início...");
            $("#formIncMembro #dtFinal").focus();
            return false;
        }
    }

    var formData = new FormData();

    formData.append("nmPessoa", nmPessoa);
    formData.append("idPessoa", idPessoa);
    formData.append("dtInicio", dtInicio);
    formData.append("dtFinal", dtFinal);
    formData.append("idCargo", idCargo);
    formData.append("idSubSede", idSubSede);

    $.ajax({
        type: 'POST',
        url: 'cipa/rh_cipa_aj3.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (dados) {
            //
            var response = JSON.parse(dados);
            if (response.status) {
                var msg = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var msg = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            mensagem.html(msg);
            //
            setTimeout(function () {
                mensagem.html("");
                var dataTable = $('#tabMembros').DataTable();
                dataTable.ajax.reload();
                $("#btnIncReset").click();
                incModalMembro.hide();
            }, 3000);
            return true;
        },
        error: function () {
            document.activeElement.blur(); // Remove o foco atual
            $("#incMembroModalLabel").focus(); // Força o foco no <body>
            incModalMembro.hide();
        }
    });
}

function btn_inclui_pessoa() {
    incModalPessoa.show();
}

function btnIncSalvarPessoa() {
    //
    // Salvar dados da Modal Inclusão de Pessoas
    //
    const _cpf = $("#_cpf").val().replace(/\D/g, ''); // Remove tudo que não for número
    //
    if ($("#_nomePessoa").val() === "") {
        alert("Por favor, informe o nome da pessoa.");
        $("#_nomePessoa").focus();
        return false;
    }
    if ($("#_nomeSocial").val() === "") {
        alert("Por favor, informe o nome social da pessoa.");
        $("#_nomeSocial").focus();
        return false;
    }
    if ($("#_cpf").val() === "") {
        alert("Por favor, informe o CPF da pessoa.");
        $("#_cpf").focus();
        return false;
    }
    if (!validarCPF(_cpf)) {
        alert("CPF INVÁLIDO, tente outro!");
        $("#_cpf").focus();
        return false;
    }
    //- Envia o formulário para salvar
    //
    $("#msgAlertIncPessoa").show();
    $.post("cipa/rh_cipa_aj2.php",
        {
            nome: $("#_nomePessoa").val(),
            nomeSocial: $("#_nomeSocial").val(),
            cpf: $("#_cpf").val(),
            sexo: $("#_sexo").val(),
            email: $("#_email").val(),
            telefone: $("#_celular").val()
        },
        function (dados, status) {
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertIncPessoa").html(mensagem);
            //
            document.activeElement.blur(); // Remove o foco atual
            document.body.focus();         // Força o foco no <body>
            //            
            setTimeout(function () {
                $("#msgAlertIncPessoa").html("");
                incModalPessoa.hide();
                $("#nmPessoa").val(response.nome);
                $("#idPessoa").val(response.idPessoa);
            }, 3000);
        });
}

function f_excluir(idMembro) {
    if (confirm("Confirma a Exclusão do Membro " + idMembro + " ?") == true) {
        $.post("cipa/rh_cipa_aj4.php", { id: idMembro },
            function (retorno, status) {
                const dados = JSON.parse(retorno);
                document.getElementById("msgAlertaMembros").innerHTML = dados.msg;
                const myTimeout = setTimeout(function () {
                    if (dados.status == true) {
                        selecionou()
                    }
                    document.getElementById("msgAlertaMembros").innerHTML = "";
                }, 3000);
            });
    }
}

function f_ver(id) {
    $.post("cipa/rh_cipa_aj5.php", { id: id }, function (retorno) {
        // alert(retorno);
        const x = JSON.parse(retorno);
        //
        $("#v_nome").html(x.dados.nome);
        $("#v_cargo").html(x.dados.dsCargo);
        $("#v_subsede").html(x.dados.dsSubSede);
        $("#v_dataini").html(formatarDataBR(x.dados.data_inicio));
        $("#v_datafim").html(x.dados.data_final ? formatarDataBR(x.dados.data_final) : "Ativo");

        $("#criadoMembro").html('<i class="fa-regular fa-calendar-check"></i> ' + x.dados.criado_em + " por " + x.dados.criado_por);
        // 
        verModalMembro.show();
    });
}

function formatarDataBR(dataISO) {
    if (!dataISO) return '';
    const partes = dataISO.split('-'); // [YYYY, MM, DD]
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function f_editar($idMembro) {
    $("#modalIncluirMembro").html("");
    //
    $.post("cipa/rh_cipa_aj5.php", { id: $idMembro }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#idMembro").val(x.dados.idMembro);
        $("#idPessoa").val(x.dados.idPessoa);
        $("#nmPessoa").val(x.dados.nome);
        $("#idSubSede").val(x.dados.idSubSede);
        $("#idCargo").val(x.dados.idCargo);
        $("#dtInicio").val(x.dados.data_inicio);
        $("#dtFinal").val(x.dados.data_final);
    });
    //
    altModalMembro.show();
}

function btnAltSalvarMembro() {
    //
    let mensagem = $("#msgAlertMembroAlt");

    var idMembro = $("#formAltMembro #idMembro").val();
    var idPessoa = $("#formAltMembro #idPessoa").val();
    var nmPessoa = $("#formAltMembro #nmPessoa").val();
    var idSubSede = $("#formAltMembro #idSubSede").val();
    var idCargo = $("#formAltMembro #idCargo").val();
    var dtInicio = $("#formAltMembro #dtInicio").val();
    var dtFinal = $("#formAltMembro #dtFinal").val();

    // Validação do campo Pessoa
    if (!idPessoa || isNaN(idPessoa) || Number(idPessoa) <= 0) {
        alert("Por favor, selecione uma Pessoa...");
        $("#formAltMembro #nmPessoa").focus();
        return false;
    }

    // Validação do campo Subsede
    if (!idSubSede || idSubSede == "0") {
        alert("Por favor, selecione uma SubSede...");
        $("#formAltMembro #idSubSede").focus();
        return false;
    }

    // Validação do campo Cargo
    if (!idCargo || idCargo == "0") {
        alert("Por favor, selecione um Cargo...");
        $("#formAltMembro #idCargo").focus();
        return false;
    }

    // Validação da Data de Início
    if (!dtInicio) {
        alert("Por favor, informe a Data de Início...");
        $("#formAltMembro #dtInicio").focus();
        return false;
    }

    // Validação da Data Final (opcional, mas se preenchida deve ser >= dtInicio)
    if (dtFinal) {
        var dInicio = new Date(dtInicio);
        var dFinal = new Date(dtFinal);

        if (dFinal < dInicio) {
            alert("A Data Final não pode ser anterior à Data de Início...");
            $("#formAltMembro #dtFinal").focus();
            return false;
        }
    }

    var formData = new FormData();

    formData.append("idMembro", idMembro);
    formData.append("nmPessoa", nmPessoa);
    formData.append("idPessoa", idPessoa);
    formData.append("dtInicio", dtInicio);
    formData.append("dtFinal", dtFinal);
    formData.append("idCargo", idCargo);
    formData.append("idSubSede", idSubSede);

    $.ajax({
        type: 'POST',
        url: 'cipa/rh_cipa_aj6.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (dados) {
            //
            var response = JSON.parse(dados);
            if (response.status) {
                var msg = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var msg = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            mensagem.html(msg);
            //
            setTimeout(function () {
                //mensagem.html("");
                //var dataTable = $('#tabMembros').DataTable();
                //dataTable.ajax.reload();
                //$("#btnIncReset").click();
                //incModalMembro.hide();
                location.reload();

            }, 3000);
            return true;
        },
        error: function () {
            document.activeElement.blur(); // Remove o foco atual
            $("#incMembroModalLabel").focus(); // Força o foco no <body>
            altModalMembro.hide();
        }
    });
}

//-------------------------------------------------------------------------------------------
//-- ATENDIMENTOS da CIPA
//

function constroiTabelaAtendimentos() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTableAtend = new DataTable('#tabAtendimentos', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "cipa/rh_cipa_aj8.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [1, 5],
                "className": "text-center"
            },
            {
                targets: -1, // última coluna
                className: 'dt-nowrap'
            }
        ],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

function btnIncAtendimento() {
    $("#formIncAtendimento")[0].reset();
    incModalAtend.show();
}

function btnSalvarTipo() {
    let descricao = $("#novoTipoOcorrencia").val().trim();
    if (descricao === "") {
        alert("Informe a descrição do tipo de ocorrência.");
        return;
    }

    $.post("cipa/rh_cipa_aj7.php", { descricao: descricao }, function (res) {
        alert(res.msg);
        if (res.status) {
            // Cria novo <option> e insere no select
            let option = $("<option>")
                .val(res.idTipo)
                .text(res.dsTipo)
                .prop("selected", true); // já seleciona

            $("#idTipoOco").append(option);

            // Limpa o campo e fecha a modal
            $("#novoTipoOcorrencia").val("");
            incModalTipo.hide();
        } else {
            alert("Erro: " + res.msg);
        }
    }, "json").fail(function () {
        alert("Erro ao comunicar com o servidor.");
        incModalTipo.hide();
    });
}

function btnSalvarAtendimento() {
    //
    // Salvar dados da Modal Inclusão de Pessoas
    //
    let idMembro = $("#formIncAtendimento #idMembro");
    let mensagem = $("#msgIncAtendimento");
    //
    if ($("#data_ocorrencia").val() === "") {
        alert("Por favor, informe a data da ocorrência");
        $("#data_ocorrencia").focus();
        return false;
    }
    if (idMembro.val() === "" || idMembro.val() === "0") {
        alert("Por favor, informe o nome do Brigadista.");
        $("#idMembro").focus();
        return false;
    }
    if ($("#nmPessoaAtendida").val() === "") {
        alert("Por favor, informe quem foi atendido.");
        $("#nmPessoaAtendida").focus();
        return false;
    }
    if ($("#idTipoOco").val() === "" || $("#idTipoOco").val() === "0") {
        alert("Por favor, informe o tipo da ocorrência.");
        $("#idTipoOco").focus();
        return false;
    }
    if ($("#local_ocorrencia").val() === "") {
        alert("Por favor, informe o local da ocorrência.");
        $("#local_ocorrencia").focus();
        return false;
    }
    if ($("#descricao").val() === "") {
        alert("Por favor, descreva a ocorrência.");
        $("#descricao").focus();
        return false;
    }
    if ($("#acao_realizada").val() === "") {
        alert("Por favor, descreva ação realizada.");
        $("#acao_realizada").focus();
        return false;
    }

    //- Envia o formulário para salvar
    //
    mensagem.html('Aguarde, salvando os dados...');
    $.post("cipa/rh_cipa_aj9.php",
        {
            idMembro: idMembro.val(),
            data_ocorrencia: $("#formIncAtendimento #data_ocorrencia").val(),
            nmPessoaAtendida: $("#formIncAtendimento #nmPessoaAtendida").val(),
            idTipoOco: $("#formIncAtendimento #idTipoOco").val(),
            local_ocorrencia: $("#formIncAtendimento #local_ocorrencia").val(),
            descricao: $("#formIncAtendimento #descricao").val(),
            acao_realizada: $("#formIncAtendimento #acao_realizada").val(),
            encaminhamento: $("#formIncAtendimento #encaminhamento").val(),
        },
        function (dados, status) {
            var response = JSON.parse(dados);
            mensagem.html(response.msg);
            //
            document.activeElement.blur(); // Remove o foco atual
            document.body.focus();         // Força o foco no <body>
            //            
            setTimeout(function () {
                mensagem.html("");
                incModalAtend.hide();
                dataTableAtend.clear().draw();
                dataTableAtend.destroy();
                constroiTabelaAtendimentos();
            }, 3000);
        });
}

function f_ver_atende(id) {
    $.post("cipa/rh_cipa_aj10.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#v_data_ocorrencia").html(x.dados.data_ocorrencia);
        $("#v_nome_brigadista").html(x.dados.nmBrigadista);
        $("#v_paciente").html(x.dados.nome_paciente);
        $("#v_tipo_ocorrencia").html(x.dados.dsOcorrencia);
        $("#v_local_ocorrencia").html(x.dados.local_ocorrencia);
        $("#v_descricao").html(x.dados.descricao);
        $("#v_acao_realizada").html(x.dados.acao_realizada);
        $("#v_encaminhamento").html(x.dados.encaminhamento);
        $("#criado_em").html('<i class="fa-regular fa-calendar-check"></i> ' + x.dados.criado_em + " por " + x.dados.criado_por);
    });
    verModalAtend.show();
}

function f_editar_atende(id) {
    //formAltAtendimento
    $.post("cipa/rh_cipa_aj10.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#formAltAtendimento #idAtendimento").val(id);
        $("#formAltAtendimento #data_ocorrencia").val(x.dados.data_ocorrencia);
        $("#formAltAtendimento #idMembro").val(x.dados.idBrigadista);
        $("#formAltAtendimento #nmPessoaAtendida").val(x.dados.nome_paciente);
        $("#formAltAtendimento #idTipoOco").val(x.dados.tipo_ocorrencia);
        $("#formAltAtendimento #local_ocorrencia").val(x.dados.local_ocorrencia);
        $("#formAltAtendimento #descricao").val(x.dados.descricao);
        $("#formAltAtendimento #acao_realizada").val(x.dados.acao_realizada);
        $("#formAltAtendimento #encaminhamento").val(x.dados.encaminhamento);
    });
    altModalAtend.show();
}

function btnSalvarAltAtendimento() {
    //
    // Salvar dados da Modal Inclusão de Pessoas
    //
    let idMembro = $("#formAltAtendimento #idMembro");
    let mensagem = $("#msgAltAtendimento");
    //
    if ($("#formAltAtendimento #data_ocorrencia").val() === "") {
        alert("Por favor, informe a data da ocorrência");
        $("#formAltAtendimento #data_ocorrencia").focus();
        return false;
    }
    if (idMembro.val() === "" || idMembro.val() === "0") {
        alert("Por favor, informe o nome do Brigadista.");
        $("#idMembro").focus();
        return false;
    }
    if ($("#formAltAtendimento #nmPessoaAtendida").val() === "") {
        alert("Por favor, informe quem foi atendido.");
        $("#formAltAtendimento #nmPessoaAtendida").focus();
        return false;
    }
    if ($("#formAltAtendimento #idTipoOco").val() === "" || $("#formAltAtendimento #idTipoOco").val() === "0") {
        alert("Por favor, informe o tipo da ocorrência.");
        $("#formAltAtendimento #idTipoOco").focus();
        return false;
    }
    if ($("#formAltAtendimento #local_ocorrencia").val() === "") {
        alert("Por favor, informe o local da ocorrência.");
        $("#formAltAtendimento #local_ocorrencia").focus();
        return false;
    }
    if ($("#formAltAtendimento #descricao").val() === "") {
        alert("Por favor, descreva a ocorrência.");
        $("#formAltAtendimento #descricao").focus();
        return false;
    }
    if ($("#formAltAtendimento #acao_realizada").val() === "") {
        alert("Por favor, descreva ação realizada.");
        $("#acao_realizada").focus();
        return false;
    }

    //- Envia o formulário para salvar
    //
    mensagem.html('Aguarde, salvando os dados...');
    $.post("cipa/rh_cipa_aj11.php",
        {
            idMembro: idMembro.val(),
            data_ocorrencia: $("#formAltAtendimento #data_ocorrencia").val(),
            nmPessoaAtendida: $("#formAltAtendimento #nmPessoaAtendida").val(),
            idTipoOco: $("#formAltAtendimento #idTipoOco").val(),
            local_ocorrencia: $("#formAltAtendimento #local_ocorrencia").val(),
            descricao: $("#formAltAtendimento #descricao").val(),
            acao_realizada: $("#formAltAtendimento #acao_realizada").val(),
            encaminhamento: $("#formAltAtendimento #encaminhamento").val(),
            idAtendimento: $("#formAltAtendimento #idAtendimento").val(),
        },
        function (dados, status) {
            var response = JSON.parse(dados);
            mensagem.html(response.msg);
            //
            document.activeElement.blur(); // Remove o foco atual
            document.body.focus();         // Força o foco no <body>
            //            
            setTimeout(function () {
                mensagem.html("");
                altModalAtend.hide();
                dataTableAtend.clear().draw();
                dataTableAtend.destroy();
                constroiTabelaAtendimentos();
            }, 3000);
        });
}

function f_excluir_atende(id) {
    if (confirm("Confirma a Exclusão do Atendimento: " + id + " ?") == true) {
        $.post("cipa/rh_cipa_aj12.php", { id: id },
            function (retorno, status) {
                const dados = JSON.parse(retorno);
                document.getElementById("msgAlertaAtende").innerHTML = dados.msg;
                const myTimeout = setTimeout(function () {
                    if (dados.status == true) {
                        selecionou()
                    }
                    document.getElementById("msgAlertaAtende").innerHTML = "";
                }, 3000);
            });
    }
}

//-------------------------------------------------------------------------------------------
//-- REUNIÕES da CIPA
//

function constroiTabelaReunioes() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTableReunioes = new DataTable('#tabReunioes', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "cipa/rh_cipa_aj13.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [0, 2, 4, 5],
                "className": "text-center"
            },
            {
                targets: -1, // última coluna
                className: 'dt-nowrap'
            }
        ],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

function btnIncReuniao() {
    $("#formIncReuniao")[0].reset();
    incModalReuniao.show();
}

let brigadistasSelecionados = [];
let altBrigadistasSelecionados = [];
let acaoBrigadistasSelecionados = [];

function addBrigadista(formName = "#formIncReuniao") {
    let select = $(`${formName} #brigadista`);
    let id = select.val();
    let nome = select.find("option:selected:not([disabled])").text().trim();

    if (!id || id === "0" || id === "") {
        alert("Por favor, selecione um Brigadista válido.");
        return;
    }

    let arrSelecionados = formName === "#formAltReuniao" ? altBrigadistasSelecionados : brigadistasSelecionados;
    if (arrSelecionados.includes(id)) return;

    arrSelecionados.push(id);

    let span = document.createElement("span");
    span.className = "badge bg-primary p-2";
    span.innerHTML = `
        ${nome}
        <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeBrigadista("${id}", "${formName}")'></i>
        <input type='hidden' name='idBrigadista[]' value='${id}'>
    `;

    $(`${formName} #brigadistasContainer`).append(span);

    // Restaura o select para a primeira opção ("Selecione...")
    select.prop("selectedIndex", 0);
}

function addTodosBrigadista(formName = "#formIncReuniao") {
    $.ajax({
        url: "cipa/rh_cipa_aj14.php",
        method: "POST",
        dataType: "json",
        success: function (response) {
            if (!response.status || !Array.isArray(response.dados)) {
                alert("Erro ao carregar brigadistas!");
                return;
            }

            response.dados.forEach(b => {
                let id = b.idBrigadista.toString();
                let nome = b.nome;

                // Seleciona o array correto
                let listaSelecionados = formName === "#formAltReuniao" ? altBrigadistasSelecionados : brigadistasSelecionados;

                if (!id || listaSelecionados.includes(id)) return;

                listaSelecionados.push(id);

                let span = document.createElement("span");
                span.className = "badge bg-primary p-2";
                span.innerHTML = `
                    ${nome}
                    <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeBrigadista("${id}", "${formName}")'></i>
                    <input type='hidden' name='idBrigadista[]' value='${id}'>
                `;

                $(`${formName} #brigadistasContainer`).append(span);
            });

            $(`${formName} #btnSalvarReuniao`).show();
        },
        error: function (xhr) {
            alert("Erro na comunicação com o servidor.");
            console.log(xhr.responseText);
        }
    });
}

/*
function altAddTodosBrigadista() {
    $.ajax({
        url: "cipa/rh_cipa_aj14.php",
        method: "POST",
        dataType: "json",
        success: function (response) {
            if (!response.status || !Array.isArray(response.dados)) {
                alert("Erro ao carregar brigadistas!");
                return;
            }

            response.dados.forEach(b => {
                let id = b.idBrigadista.toString();
                let nome = b.nome;

                if (!id || altBrigadistasSelecionados.includes(id)) return;

                altBrigadistasSelecionados.push(id);

                let span = document.createElement("span");
                span.className = "badge bg-primary p-2";
                span.innerHTML = `
                    ${nome}
                    <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeBrigadista("${id}", "${formName}")'></i>
                    <input type='hidden' name='idBrigadista[]' value='${id}'>
                `;
                //document.getElementById("altBrigadistasContainer").appendChild(span);
                $("#formAltReuniao #brigadistasContainer").append(span);
            });

            $("#formAltReuniao #btnSalvarReuniao").show();
        },
        error: function (xhr) {
            alert("Erro na comunicação com o servidor.");
            console.log(xhr.responseText);
        }
    });
}
*/

function removeBrigadista(id, formName = "#formIncReuniao") {
    //
    if (formName === "#formAltReuniao") {
        altBrigadistasSelecionados = altBrigadistasSelecionados.filter(b => b !== id);
    } else {
        brigadistasSelecionados = brigadistasSelecionados.filter(b => b !== id);
    }

    $(`${formName} #brigadistasContainer input[value='${id}']`).closest("span").remove();
}

function btnSalvarReuniao(e) {
    if (e) e.preventDefault(); // bloqueia se vier de evento
    //
    let mensagem = $("#msgAlertaIncReuniao");
    //
    let dataReuniao = $("#data_reuniao").val();
    let assunto = $("#assunto").val().trim();
    let observacoes = $("#observacoes").summernote("isEmpty") ? "" : $("#observacoes").summernote("code");
    let brigadistas = [];

    $("input[name='idBrigadista[]']").each(function () {
        brigadistas.push($(this).val());
    });

    // Validações
    if (!dataReuniao) {
        alert("Por favor, selecione a Data da Reunião.");
        $("#data_reuniao").focus();
        return;
    }

    if (assunto === "") {
        alert("Por favor, preencha o Assunto da Reunião.");
        $("#assunto").focus();
        return;
    }

    if (observacoes === "" || observacoes === "<p><br></p>") {
        alert("Por favor, escreva alguma observação da reunião.");
        $("#observacoes").summernote("focus");
        return;
    }

    if (brigadistas.length === 0) {
        alert("Por favor, selecione pelo menos um Brigadista presente.");
        return;
    }
    let form = document.getElementById("formIncReuniao");
    let formData = new FormData(form);
    //
    $.ajax({
        url: "cipa/rh_cipa_aj15.php",
        type: "POST",
        data: formData,
        dataType: "json",
        processData: false, // necessário para envio de arquivos
        contentType: false, // necessário para envio de arquivos
        success: function (response) {
            mensagem.html(response.msg);
            //
            if (response.status) {
                // Atualizar grid ou outro conteúdo se necessário
                setTimeout(function () {
                    mensagem.html("");
                    $("#formIncReuniao")[0].reset();
                    $("#observacoes").summernote("reset");
                    $("#brigadistasContainer").empty();
                    incModalReuniao.hide();
                    dataTableReunioes.clear().draw();
                    dataTableReunioes.destroy();
                    constroiTabelaReunioes();
                }, 3000);
            } else {
                alert("Erro: " + response.msg);
            }
        },
        error: function (xhr, status, error) {
            alert("Falha na comunicação com o servidor.\n" + error);
        }
    });

}

$("#formIncReuniao, #formAltReuniao, #formIncAcao, #formAltAcao").on("submit", function (e) {
    e.preventDefault();
    return false;
});

function f_excluir_reuniao(id) {
    if (confirm("Confirma a Exclusão da Reunião: " + id + " ?") == true) {
        $.post("cipa/rh_cipa_aj16.php", { id: id },
            function (retorno, status) {
                const dados = JSON.parse(retorno);
                document.getElementById("msgAlertaReunioes").innerHTML = dados.msg;
                const myTimeout = setTimeout(function () {
                    if (dados.status == true) {
                        selecionou()
                    }
                    document.getElementById("msgAlertaReunioes").innerHTML = "";
                }, 3000);
            });
    }
}

function f_ver_ata(arquivo) {
    if (!arquivo) {
        alert("Arquivo não informado.");
        return;
    }

    let url = 'docs/cipa/' + encodeURIComponent(arquivo);
    window.open(url, '_blank');
}

function f_ver_reuniao(id) {
    $.post("cipa/rh_cipa_aj17.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#formVerReuniao #v_data_reuniao").html(formatarDataBRComDiaSemana(x.dados.data_reuniao));
        $("#formVerReuniao #v_assunto").html(x.dados.assunto);
        $("#formVerReuniao #v_observacoes").html(x.dados.observacoes);
        $("#formVerReuniao #v_ata_arquivo").html(x.dados.ata_arquivo ? `<a href="#" onclick="f_ver_ata('${x.dados.ata_arquivo}')"><i class="fa-solid fa-file-pdf"></i> Ver Ata</a>` : 'Nenhum arquivo anexado');
        $("#formVerReuniao #v_brigadistas").html(x.dados.nomesParticipantes);
        $("#formVerReuniao #v_criado_em").html('<i class="fa-regular fa-calendar-check"></i> ' + x.dados.criado_em + " por " + x.dados.criado_por);
        $("#formVerReuniao #idReuniaoVisual").html("ID: " + x.dados.id);
    });
    verModalReuniao.show();
}

function formatarDataBRComDiaSemana(dataISO) {
    if (!dataISO) return "";

    const diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

    const partes = dataISO.split("-");
    const ano = parseInt(partes[0], 10);
    const mes = parseInt(partes[1], 10) - 1; // JavaScript usa 0-11 para meses
    const dia = parseInt(partes[2], 10);

    const dataObj = new Date(ano, mes, dia);
    const diaSemana = diasSemana[dataObj.getDay()];

    return `${String(dia).padStart(2, '0')}/${String(mes + 1).padStart(2, '0')}/${ano}, ${diaSemana}`;
}

function f_editar_reuniao(id) {
    // Zera os brigadistas selecionados
    altBrigadistasSelecionados = [];

    $.post("cipa/rh_cipa_aj17.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);

        // Preenche os campos do formulário
        $("#formAltReuniao #idReuniaoAlt").val(id);
        $("#formAltReuniao #data_reuniao").val(x.dados.data_reuniao);
        $("#formAltReuniao #assunto").val(x.dados.assunto);
        $("#formAltReuniao #observacoes").summernote("code", x.dados.observacoes);
        $("#formAltReuniao #idReuniaoVisual").html("ID: " + x.dados.id);

        // Limpa o container visual dos brigadistas
        $("#formAltReuniao #brigadistasContainer").empty();

        // Monta visualmente os brigadistas
        if (x.dados.idParticipantes && x.dados.nomesParticipantes) {
            const ids = x.dados.idParticipantes.split(',');
            const nomes = x.dados.nomesParticipantes.split(', '); // cuidado com o espaço após a vírgula

            let formName = '#formAltReuniao';

            ids.forEach((id, index) => {
                const nome = nomes[index] || "Desconhecido";
                altBrigadistasSelecionados.push(id);

                const span = `
                    <span class="badge bg-primary p-2">
                        ${nome}
                        <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeBrigadista("${id}", "${formName}")'></i>
                        <input type='hidden' name='idBrigadista[]' value='${id}'>
                    </span>
                `;
                $("#formAltReuniao #brigadistasContainer").append(span);
            });
        }
    });

    altModalReuniao.show();
}

function btnSalvarAltReuniao(e) {
    if (e) e.preventDefault(); // Bloqueia se vier de evento

    let mensagem = $("#msgAlertaAltReuniao");

    let dataReuniao = $("#formAltReuniao #data_reuniao").val();
    let assunto = $("#formAltReuniao #assunto").val().trim();
    let observacoes = $("#formAltReuniao #observacoes").summernote("isEmpty") ? "" : $("#formAltReuniao #observacoes").summernote("code");
    let brigadistas = [];

    $("#formAltReuniao input[name='idBrigadista[]']").each(function () {
        brigadistas.push($(this).val());
    });

    // Validações
    if (!dataReuniao) {
        alert("Por favor, selecione a Data da Reunião.");
        $("#formAltReuniao #data_reuniao").focus();
        return;
    }

    if (assunto === "") {
        alert("Por favor, preencha o Assunto da Reunião.");
        $("#formAltReuniao #assunto").focus();
        return;
    }

    if (observacoes === "" || observacoes === "<p><br></p>") {
        alert("Por favor, escreva alguma observação da reunião.");
        $("#formAltReuniao #observacoes").summernote("focus");
        return;
    }

    if (brigadistas.length === 0) {
        alert("Por favor, selecione pelo menos um Brigadista presente.");
        return;
    }

    let form = document.getElementById("formAltReuniao");
    let formData = new FormData(form);

    $.ajax({
        url: "cipa/rh_cipa_aj18.php",
        type: "POST",
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (response) {
            mensagem.html(response.msg);

            if (response.status) {
                setTimeout(function () {
                    mensagem.html("");
                    $("#formAltReuniao")[0].reset();
                    $("#formAltReuniao #observacoes").summernote("reset");
                    $("#formAltReuniao #brigadistasContainer").empty();
                    altModalReuniao.hide();
                    dataTableReunioes.clear().draw();
                    dataTableReunioes.destroy();
                    constroiTabelaReunioes();
                }, 3000);
            } else {
                alert("Erro: " + response.msg);
            }
        },
        error: function (xhr, status, error) {
            alert("Falha na comunicação com o servidor.\n" + error);
        }
    });
}

//-------------------------------------------------------------------------------------------
//- AÇÕES/EVENTOS DA CIPA
//

function constroiTabelaAcoes() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTableAcoes = new DataTable('#tabAcoes', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "cipa/rh_cipa_aj19.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [0, 2, 4, 5],
                "className": "text-center"
            },
            {
                targets: -1, // última coluna
                className: 'dt-nowrap'
            }
        ],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

function btnIncAcao() {
    let container = $("#formIncAcao #brigadistasContainer");
    container.html('');
    incModalAcao.show();
}

function btnSalvarAcao(){
    //
    let mensagem = $("#msgAlertaIncAcao");
    //
    let dataAcao    = $("#formIncAcao #data_acao").val();
    let assunto     = $("#formIncAcao #assunto").val().trim();
    let observacoes = $("#formIncAcao #observacoes").summernote("isEmpty") ? "" : $("#formIncAcao #observacoes").summernote("code");
    let brigadistas = [];

    $("input[name='idBrigadista[]']").each(function () {
        brigadistas.push($(this).val());
    });

    // Validações
    if (!dataAcao) {
        alert("Por favor, selecione a Data da Ação.");
        $("#data_acao").focus();
        return;
    }

    if (assunto === "") {
        alert("Por favor, preencha o Assunto da Ação.");
        $("#assunto").focus();
        return;
    }

    if (observacoes === "" || observacoes === "<p><br></p>") {
        alert("Por favor, escreva alguma observação da Ação.");
        $("#observacoes").summernote("focus");
        return;
    }

    if (brigadistas.length === 0) {
        alert("Por favor, selecione pelo menos um Brigadista presente.");
        return;
    }
    let form = document.getElementById("formIncAcao");
    let formData = new FormData(form);
    //
    $.ajax({
        url: "cipa/rh_cipa_aj20.php",
        type: "POST",
        data: formData,
        dataType: "json",
        processData: false, // necessário para envio de arquivos
        contentType: false, // necessário para envio de arquivos
        success: function (response) {
            mensagem.html(response.msg);
            //
            if (response.status) {
                // Atualizar grid ou outro conteúdo se necessário
                setTimeout(function () {
                    mensagem.html("");
                    $("#formIncAcao")[0].reset();
                    $("#observacoes").summernote("reset");
                    $("#brigadistasContainer").empty();
                    incModalAcao.hide();
                    dataTableAcoes.clear().draw();
                    dataTableAcoes.destroy();
                    constroiTabelaAcoes();
                }, 3000);
            } else {
                alert("Erro: " + response.msg);
            }
        },
        error: function (xhr, status, error) {
            alert("Falha na comunicação com o servidor.\n" + error);
        }
    });

}

function f_ver_acao(id){
    $.post("cipa/rh_cipa_aj21.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#formVerAcao #v_data_acao").html(formatarDataBRComDiaSemana(x.dados.data_acao));
        $("#formVerAcao #v_assunto").html(x.dados.assunto);
        $("#formVerAcao #v_observacoes").html(x.dados.observacoes);
        $("#formVerAcao #v_acao_arquivo").html(x.dados.acao_arquivo ? `<a href="#" onclick="f_ver_ata('${x.dados.acao_arquivo}')"><i class="fa-solid fa-file-pdf"></i> Ver Ata</a>` : 'Nenhum arquivo anexado');
        $("#formVerAcao #v_brigadistas").html(x.dados.nomesParticipantes);
        $("#formVerAcao #v_criado_em").html('<i class="fa-regular fa-calendar-check"></i> ' + x.dados.criado_em + " por " + x.dados.criado_por);
        $("#formVerAcao #v_idAcaoVisual").html("ID: " + x.dados.id);
    });
    verModalAcao.show();
}

function f_editar_acao(id) {
    // Zera os brigadistas selecionados
    brigadistasSelecionados = [];

    $.post("cipa/rh_cipa_aj21.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);

        // Preenche os campos do formulário
        $("#formAltAcao #idAcaoAlt").val(id);
        $("#formAltAcao #data_acao").val(x.dados.data_acao);
        $("#formAltAcao #assunto").val(x.dados.assunto);
        $("#formAltAcao #observacoes").summernote("code", x.dados.observacoes);
        $("#formAltAcao #idAcaoVisual").html("ID: " + x.dados.id);
        $("#formAltAcao #brigadistasContainer").empty();

        // Monta visualmente os brigadistas
        if (x.dados.idParticipantes && x.dados.nomesParticipantes) {
            const ids = x.dados.idParticipantes.split(',');
            const nomes = x.dados.nomesParticipantes.split(', '); // cuidado com o espaço após a vírgula

            let formName = '#formAltAcao';

            ids.forEach((id, index) => {
                const nome = nomes[index] || "Desconhecido";
                altBrigadistasSelecionados.push(id);

                const span = `
                    <span class="badge bg-primary p-2">
                        ${nome}
                        <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeBrigadista("${id}", "${formName}")'></i>
                        <input type='hidden' name='idBrigadista[]' value='${id}'>
                    </span>
                `;
                $("#formAltAcao #brigadistasContainer").append(span);
            });
        }
    });
    altModalAcao.show();
}

function btnSalvarAltAcao() {

    let mensagem = $("#msgAlertaAltAcao");

    let dataAcao = $("#formAltAcao #data_acao").val();
    let assunto = $("#formAltAcao #assunto").val().trim();
    let observacoes = $("#formAltAcao #observacoes").summernote("isEmpty") ? "" : $("#formAltAcao #observacoes").summernote("code");
    let brigadistas = [];

    $("#formAltAcao input[name='idBrigadista[]']").each(function () {
        brigadistas.push($(this).val());
    });

    // Validações
    if (!dataAcao) {
        alert("Por favor, selecione a Data da Ação.");
        $("#formAltAcao #data_acao").focus();
        return;
    }

    if (assunto === "") {
        alert("Por favor, preencha o Assunto da Ação.");
        $("#formAltAcao #assunto").focus();
        return;
    }

    if (observacoes === "" || observacoes === "<p><br></p>") {
        alert("Por favor, escreva alguma observação da Ação.");
        $("#formAltAcao #observacoes").summernote("focus");
        return;
    }

    if (brigadistas.length === 0) {
        alert("Por favor, selecione pelo menos um Brigadista presente.");
        return;
    }

    let form = document.getElementById("formAltAcao");
    let formData = new FormData(form);

    $.ajax({
        url: "cipa/rh_cipa_aj22.php",
        type: "POST",
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (response) {

            mensagem.html(response.msg);

            if (response.status) {
                setTimeout(function () {
                    mensagem.html("");
                    $("#formAltAcao")[0].reset();
                    $("#formAltAcao #observacoes").summernote("reset");
                    $("#formAltAcao #brigadistasContainer").empty();
                    altModalAcao.hide();
                    dataTableAcoes.clear().draw();
                    dataTableAcoes.destroy();
                    constroiTabelaAcoes();
                }, 3000);
            } else {
                alert("Erro: " + response.msg);
            }
        },
        error: function (xhr, status, error) {
            alert("Eita ...Falha na comunicação com o servidor.\n" + error);
        }
    });
}

function f_excluir_acao(id){
    let mensagem = $("#msgAlertaAcoes");
    if (confirm("Confirma a Exclusão da Ação de CIPA ID: " + id + " ?") == true) {
        $.post("cipa/rh_cipa_aj23.php", { id: id },
            function (retorno, status) {
                const dados = JSON.parse(retorno);
                mensagem.html( dados.msg );
                //
                const myTimeout = setTimeout(function () {
                    mensagem.html( "" );
                    if (dados.status == true) {
                        selecionou();
                    }
                }, 3000);
            });
    }
}