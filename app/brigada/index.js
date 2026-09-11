//
//- rh_brigada.js | Brigada de Incêncio 
// (C)haia, 16/06/2025

// idModulo = 10; // Brigada de Emergência

var dataTableMembros;
var dataTableAtend;
var dataTableReunioes;
var dataTableAcoes;
var dataTableDocs;
var dataTableUsuarios;

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

const incModalDoc = new bootstrap.Modal(document.getElementById("modalIncDoc"));
const altModalDoc = new bootstrap.Modal(document.getElementById("modalEditDoc"));

const visModalUsu = new bootstrap.Modal(document.getElementById("modalVisUsuario"));
const incModalUsu = new bootstrap.Modal(document.getElementById("modalIncUsuario"));
const altModalUsu = new bootstrap.Modal(document.getElementById("modalAltUsuario"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabelaMembros();
        constroiTabelaAtendimentos();
        constroiTabelaReunioes();
        constroiTabelaAcoes();
        constroiTabelaDocs();
        constroiTabelaUsuarios();
        //
    }, 300); // Ajuste o tempo conforme necessário

    $("#nmPessoa").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "../includes/buscar_pessoas.php",
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
    //
    //- Reativar o Menu Tab depois que foi d-none pelo: "Usuários"
    //
    $('.menu .nav-link').on('click', function() {
        $("#cartoes").removeClass("d-none");    // Mostra as abas
        $("#cartaoUsuarios").addClass("d-none"); // Esconde a tela de usuários
    });    
    //
    
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
    if (target === "#menuDocs") {
        setTimeout(function () {
            dataTableDocs.columns.adjust().draw();
        }, 10);
    }
    if (target === "#menuUsuarios") {
        setTimeout(function () {
            dataTableDocs.columns.adjust().draw();
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
    //
    dataTableDocs.clear().draw();
    dataTableDocs.destroy();
    constroiTabelaDocs();
    //
    dataTableUsuarios.clear().draw();
    dataTableUsuarios.destroy();
    constroiTabelaUsuarios();
    //
}

//-------------------------------------------------------------------------------------------
// - MEMBROS da Brigada
//

function constroiTabelaMembros() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 8 : 6;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 10;
    }

    dataTableMembros = new DataTable('#tabMembros', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_brigada_aj1.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 2, 3, 4, 5, 6],
            "className": "text-center"
        }],
        language: {
            url: '../includes/pt-BR.json',
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

    // Adiciona a foto (campo tipo file)
    var fotoInput = document.getElementById("foto");
    if (fotoInput.files.length > 0) {
        formData.append("foto", fotoInput.files[0]);
    }

    $.ajax({
        type: 'POST',
        url: 'rh_brigada_aj3.php',
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
    $.post("rh_brigada_aj2.php",
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
        $.post("rh_brigada_aj4.php", { id: idMembro },
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
    $.post("rh_brigada_aj5.php", { id: id }, function (retorno) {
        // alert(retorno);
        const x = JSON.parse(retorno);
        //
        $("#v_nome").html(x.dados.nome);
        $("#v_cargo").html(x.dados.dsCargo);
        $("#v_subsede").html(x.dados.dsSubSede);
        $("#v_dataini").html(formatarDataBR(x.dados.data_inicio));
        $("#v_datafim").html(x.dados.data_final ? formatarDataBR(x.dados.data_final) : "Ativo");
        $("#v_email").html(x.dados.email ? x.dados.email : "ND");
        $("#v_telefone").html(x.dados.telefone ? x.dados.telefone : "ND");

        $("#criadoMembro").html('<i class="fa-regular fa-calendar-check"></i> ' + x.dados.criado_em + " por " + x.dados.criado_por);
        //
        //
        let foto = x.dados.foto || 'perfil.png'; // se não vier, usa avatar padrão
        document.getElementById("v_foto").src = "../docs/brigada/" + foto;
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
    $.post("rh_brigada_aj5.php", { id: $idMembro }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#idMembro").val(x.dados.idMembro);
        $("#idPessoa").val(x.dados.idPessoa);
        $("#nmPessoa").val(x.dados.nome);
        $("#idSubSede").val(x.dados.idSubSede);
        $("#idCargo").val(x.dados.idCargoBrigada);
        $("#dtInicio").val(x.dados.data_inicio);
        $("#dtFinal").val(x.dados.data_final);
        //
        $("#fotoAtual").val(x.dados.foto);
        //
        // Atualiza preview da foto
        const preview = document.getElementById("previewImagemAlt");
        if (x.dados.foto && x.dados.foto.trim() !== "") {
            preview.src = "../docs/brigada/" + x.dados.foto;
        } else {
            preview.src = "../fotos/perfil.png";
        }
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

    var fotoInput = document.getElementById("fotoAlt");
    if (fotoInput.files.length > 0) {
        formData.append("fotoAlt", fotoInput.files[0]);
    }

    $.ajax({
        type: 'POST',
        url: 'rh_brigada_aj6.php',
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
//-- ATENDIMENTOS da Brigada
//

function constroiTabelaAtendimentos() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 10 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 10 : 8;
    }

    dataTableAtend = new DataTable('#tabAtendimentos', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_brigada_aj8.php",
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
            url: '../includes/pt-BR.json',
        },
    });
}

function btnRelAtendimento(rota) {
    let tabela = $("#divTabAtendimentos");
    let grafico = $("#divGraficoAtendimento");
    //
    if (rota == 'ir') {
        tabela.addClass("d-none");      // esconde a tabela
        grafico.removeClass("d-none");  // mostra o gráfico
    } else {
        tabela.removeClass("d-none");   // mostra a tabela
        grafico.addClass("d-none");     // esconde o gráfico
    }
}


function btnIncAtendimento() {
    let container = $("#formIncAtendimento #brigadistasContainer");
    container.html('');
    $("#formIncAtendimento")[0].reset();
    incModalAtend.show();
}

function btnSalvarTipo() {
    let botoes = $("#botoesNovoTipoOcorrencia");
    let mensagem = $("#msgIncTipo");

    let descricao = $("#novoTipoOcorrencia").val().trim();
    if (descricao === "") {
        alert("Informe a descrição do tipo de ocorrência.");
        return;
    }

    botoes.hide();
    mensagem.html('Aguarde, salvando o novo tipo...');

    $.post("rh_brigada_aj7.php", { descricao: descricao }, function (res) {
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
            mensagem.html("");
            incModalTipo.hide();
        } else {
            alert("Erro: " + res.msg);
        }
    }, "json").fail(function () {
        alert("Erro ao comunicar com o servidor.");
        incModalTipo.hide();
    });
}

function btnSalvarAtendimento(e) {
    if (e) e.preventDefault(); // Bloqueia se vier de evento
    //
    // Salvar dados da Modal Inclusão de Pessoas
    //
    let mensagem = $("#msgIncAtendimento");
    //
    if ($("#data_ocorrencia").val() === "") {
        alert("Por favor, informe a data da ocorrência");
        $("#data_ocorrencia").focus();
        return false;
    }

    let brigadistas = [];

    $("input[name='idBrigadista[]']").each(function () {
        brigadistas.push($(this).val());
    });

    if (brigadistas.length === 0) {
        alert("Por favor, selecione pelo menos um Brigadista presente.");
        return;
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
    $.post("rh_brigada_aj9.php",
        {
            brigadistas: brigadistas,
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
    $.post("rh_brigada_aj10.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#v_data_ocorrencia").html(x.dados.data_ocorrencia);
        $("#v_nome_brigadista").html(x.dados.membros);
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
    $.post("rh_brigada_aj10.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);
        //
        $("#formAltAtendimento #idAtendimento").val(id);
        $("#formAltAtendimento #data_ocorrencia").val(x.dados.data_ocorrencia);
        //$("#formAltAtendimento #idMembro").val(x.dados.idBrigadista);
        $("#formAltAtendimento #nmPessoaAtendida").val(x.dados.nome_paciente);
        $("#formAltAtendimento #idTipoOco").val(x.dados.tipo_ocorrencia);
        $("#formAltAtendimento #local_ocorrencia").val(x.dados.local_ocorrencia);
        $("#formAltAtendimento #descricao").val(x.dados.descricao);
        $("#formAltAtendimento #acao_realizada").val(x.dados.acao_realizada);
        $("#formAltAtendimento #encaminhamento").val(x.dados.encaminhamento);
        //
        $("#formAltAtendimento #brigadistasContainer").empty();
        //
        // Monta visualmente os brigadistas
        if (x.dados.idMembros && x.dados.membros) {
            const ids = x.dados.idMembros.split(',');
            const nomes = x.dados.membros.split(', '); // cuidado com o espaço após a vírgula

            let formName = '#formAltAtendimento';

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
                $("#formAltAtendimento #brigadistasContainer").append(span);
            });
        }

    });
    altModalAtend.show();
}

function btnSalvarAltAtendimento(e) {
    if (e) e.preventDefault(); // Bloqueia se vier de evento
    //
    // Salvar dados da Modal Inclusão de Pessoas
    //
    //let idMembro = $("#formAltAtendimento #idMembro");
    let mensagem = $("#msgAltAtendimento");
    //
    if ($("#formAltAtendimento #data_ocorrencia").val() === "") {
        alert("Por favor, informe a data da ocorrência");
        $("#formAltAtendimento #data_ocorrencia").focus();
        return false;
    }

    let brigadistas = [];

    $("input[name='idBrigadista[]']").each(function () {
        brigadistas.push($(this).val());
    });

    if (brigadistas.length === 0) {
        alert("Por favor, selecione pelo menos um Brigadista presente.");
        return;
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
    $.post("rh_brigada_aj11.php",
        {
            brigadistas: brigadistas,
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
        $.post("rh_brigada_aj12.php", { id: id },
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
//-- REUNIÕES da Brigada
//

function constroiTabelaReunioes() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 10 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 10 : 8;
    }

    dataTableReunioes = new DataTable('#tabReunioes', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_brigada_aj13.php",
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
            url: '../includes/pt-BR.json',
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
        url: "rh_brigada_aj14.php",
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
        url: "rh_brigada_aj15.php",
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
        $.post("rh_brigada_aj16.php", { id: id },
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

    let url = '../docs/brigada/' + encodeURIComponent(arquivo);
    window.open(url, '_blank');
}

function f_ver_reuniao(id) {
    $.post("rh_brigada_aj17.php", { id: id }, function (retorno) {
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

    $.post("rh_brigada_aj17.php", { id: id }, function (retorno) {
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
        url: "rh_brigada_aj18.php",
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
//- AÇÕES/EVENTOS DA BRIGADA
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
            "url": "rh_brigada_aj19.php",
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
            url: '../includes/pt-BR.json',
        },
    });
}

function btnIncAcao() {
    let container = $("#formIncAcao #brigadistasContainer");
    container.html('');
    incModalAcao.show();
}

function btnSalvarAcao(e) {
    if (e) e.preventDefault(); // Bloqueia se vier de evento
    //
    let mensagem = $("#msgAlertaIncAcao");
    //
    let dataAcao = $("#formIncAcao #data_acao").val();
    let assunto = $("#formIncAcao #assunto").val().trim();
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
        url: "rh_brigada_aj20.php",
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

function f_ver_acao(id) {
    $.post("rh_brigada_aj21.php", { id: id }, function (retorno) {
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

    $.post("rh_brigada_aj21.php", { id: id }, function (retorno) {
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

function btnSalvarAltAcao(e) {
    if (e) e.preventDefault(); // Bloqueia se vier de evento
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
        url: "rh_brigada_aj22.php",
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

function f_excluir_acao(id) {
    let mensagem = $("#msgAlertaAcoes");
    if (confirm("Confirma a Exclusão da Ação de Brigada ID: " + id + " ?") == true) {
        $.post("rh_brigada_aj23.php", { id: id },
            function (retorno, status) {
                const dados = JSON.parse(retorno);
                mensagem.html(dados.msg);
                //
                const myTimeout = setTimeout(function () {
                    mensagem.html("");
                    if (dados.status == true) {
                        selecionou();
                    }
                }, 3000);
            });
    }
}

function previewFoto(event, imgId = 'preview') {
    const reader = new FileReader();
    reader.onload = function () {
        const img = document.getElementById(imgId);
        img.src = reader.result;
        img.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}

//-------------------------------------------------------------------------------------------
//- DOCUMENTOS DA CIPA
//

function constroiTabelaDocs() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTableDocs = new DataTable('#tabDocs', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_brigada_aj25.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [0, 3],
                "className": "text-center"
            },
            {
                targets: -1, // última coluna
                className: 'dt-nowrap text-end'
            }
        ],
        language: {
            url: '../includes/pt-BR.json',
        },
    });
}

function f_ver_doc(idDoc) {
    //
    $.post("rh_brigada_aj26.php", { id: idDoc }, function (retorno) {
        const x = JSON.parse(retorno);
        document.getElementById("v_arquivo").textContent = x.dados.arquivo;
        document.getElementById("v_titulo").textContent = x.dados.descricao;
        document.getElementById("v_data").textContent = x.dados.data;
        document.getElementById("v_original").textContent = x.dados.nome_original;
        //
        const extensao = x.dados.arquivo.split('.').pop().toLowerCase();
        const caminho = "../docs/brigada/" + x.dados.arquivo;
        const previewDiv = document.getElementById("previewDoc");
        let htmlPreview = "";
        //
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extensao)) {
            htmlPreview = `<img src="${caminho}" class="img-thumbnail" style="max-height: 200px;">`;
        } else if (extensao === 'pdf') {
            htmlPreview = `<embed src="${caminho}" type="application/pdf" width="100%" height="300px" />`;
        } else {
            htmlPreview = `<span class="text-muted">Visualização não disponível.</span>`;
        }

        previewDiv.innerHTML = htmlPreview;
        document.getElementById("btnDownloadDoc").href = caminho;
        document.getElementById("btnVisualizarDoc").href = caminho;

        const modal = new bootstrap.Modal(document.getElementById('modalVerDoc'));
        modal.show();
    });

}

function btnIncDoc() {
    $("#formIncDoc")[0].reset();
    $.post("rh_brigada_aj28.php", { id: 0 }, function (retorno) {
        $("#formIncDoc #seletor_tipos_doc").html(retorno);
    });
    incModalDoc.show();
}

function btnSalvarDoc() {
    //
    let mensagem = $("#msgAlertaIncDoc");
    //    
    let data = $("#formIncDoc #data").val();
    let arquivo = $("#formIncDoc #documento")[0].files[0];
    let assunto = $("#formIncDoc #assunto").val().trim();
    let idTipoDoc = $("#formIncDoc #idTipoDoc").val();

    // Validações
    if (assunto === "") {
        alert("Por favor, preencha o assunto/descrição do documento.");
        assunto.focus();
        return;
    }

    if (!data) {
        alert("Por favor, informe a data do documento.");
        data.focus();
        return;
    }

    if (!arquivo) {
        alert("Por favor, selecione um arquivo para upload.");
        arquivo.focus();
        return;
    }

    let formData = new FormData(document.getElementById("formIncDoc"));
    mensagem.html('Aguarde, salvando os dados...');
    $("#divBtnSalvarDoc").hide();

    $.ajax({
        url: "rh_brigada_aj27.php",
        type: "POST",
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (response) {
            mensagem.html(response.msg);
            //
            if (response.status) {
                setTimeout(function () {
                    mensagem.html("");
                    $("#formIncDoc")[0].reset();
                    incModalDoc.hide();
                    dataTableDocs.clear().draw();
                    dataTableDocs.destroy();
                    constroiTabelaDocs();
                    $("#divBtnSalvarDoc").show();
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

function f_excluir_doc(id) {
    let mensagem = $("#msgAlertaDocs");
    if (confirm("Confirma a Exclusão do Documento de Brigada ID: " + id + " ?") == true) {
        $.post("rh_brigada_aj29.php", { id: id },
            function (retorno, status) {
                const dados = JSON.parse(retorno);
                mensagem.html(dados.msg);
                //
                const myTimeout = setTimeout(function () {
                    mensagem.html("");
                    if (dados.status == true) {
                        location.reload();
                    }
                }, 3000);
            });
    }
}

function f_editar_doc(id) {
    //
    //
    $.post("rh_brigada_aj26.php", { id: id }, function (retorno) {
        const x = JSON.parse(retorno);

        // Preenche os campos do formulário
        $("#formAltDoc #idDoc").val(id);
        $("#formAltDoc #dataEdit").val(x.dados.data);
        $("#formAltDoc #assuntoEdit").val(x.dados.descricao);
        $("#formAltDoc #tagsEdit").val(x.dados.tags);
        //
        $.post("rh_brigada_aj28.php", { id: x.dados.idTipoDoc }, function (retorno) {
            $("#formAltDoc #seletor_tipos_doc_edit").html(retorno);
        });

    });
    altModalDoc.show();
}

function previewDocumento(event, containerId = 'previewContainer') {
    const file = event.target.files[0];
    const container = document.getElementById(containerId);

    if (file && file.type === 'application/pdf') {
        const fileURL = URL.createObjectURL("../docs/brigada/"+file);
        container.innerHTML = `<iframe src="${fileURL}" frameborder="0" width="100%" height="100%"></iframe>`;
        container.style.background = 'none'; // remove placeholder
    } else {
        container.innerHTML = '';
        container.style.background = "url('../fotos/pdf_placeholder.png') center center no-repeat";
        container.style.backgroundSize = 'contain';
    }
}

function btnSalvarEdicaoDoc() {
    //
    let mensagem = $("#msgAlertaAltDoc");
    //
    let data = $("#formAltDoc #dataEdit").val();
    let assunto = $("#formAltDoc #assuntoEdit").val().trim();
    let tags = $("#formAltDoc #tagsEdit").val().trim();
    let idTipoDoc = $("#formAltDoc #idTipoDoc").val();
    let idDoc = $("#formAltDoc #idDoc").val();

    // Validações
    if (assunto === "") {
        alert("Por favor, preencha o assunto/descrição do documento.");
        assunto.focus();
        return;
    }

    if (!data) {
        alert("Por favor, informe a data do documento.");
        data.focus();
        return;
    }

    if (!idTipoDoc) {
        alert("Por favor, selecione o tipo de documento.");
        return;
    }

    let formData = new FormData(document.getElementById("formAltDoc"));
    mensagem.html('Aguarde, salvando os dados...');
    $("#divBtnSalvarAltDoc").hide();

    $.ajax({
        url: "rh_brigada_aj30.php",
        type: "POST",
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (response) {
            mensagem.html(response.msg);
            //
            if (response.status) {
                setTimeout(function () {
                    mensagem.html("");
                    $("#formAltDoc")[0].reset();
                    altModalDoc.hide();
                    dataTableDocs.clear().draw();
                    dataTableDocs.destroy();
                    constroiTabelaDocs();
                    $("#divBtnSalvarAltDoc").show();
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

//
//- ROTINA - ALTERAR SENHA
//

document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        // Encontra o input que está no mesmo grupo que o botão
        const input = this.parentElement.querySelector('input');
        const icon = this.querySelector('i');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

function btn_salvar_senha(){
    let modalSenha = $("#modalAltSenha");
    let mensagem = $("#msgAlertaSenha");
    let senha = $("#formAltSenha #nova_senha").val().trim();
    let confirmarSenha = $("#formAltSenha #confirma_senha").val().trim();
    let botoes = $("#botoesAltSenha");
    //
    if (senha === "") {
        alert("Por favor, preencha a nova senha.");
        $("#formAltSenha #nova_senha").focus();
        return;
    }
    if (confirmarSenha === "") {
        alert("Por favor, confirme a nova senha.");
        $("#formAltSenha #confirma_senha").focus();
        return;
    }
    if (senha !== confirmarSenha) {
        alert("As senhas informadas devem ser iguais.");
        $("#formAltSenha #confirma_senha").focus();
        return;
    }

    botoes.addClass("d-none");
    mensagem.html('Aguarde, salvando os dados...');
    $.post("salva_senha.php", { senha: senha }, function (retorno) {
        mensagem.html(retorno.msg);
        if (retorno.status) {
            setTimeout(function () {
                location.reload();
            }, 3000);
        } else {
            alert("Erro: " + retorno.msg);
        }
    }, "json");
}

//
//- ROTINA - CADASTRO DE USUÁRIOS DO SISTEMA
//

function constroiTabelaUsuarios() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 10 : 6;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 10 : 8;
    }

    dataTableUsuarios = new DataTable('#tabUsuarios', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_brigada_aj31.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [0, 5],
                "className": "text-center"
            },
            {
                targets: -1, // última coluna
                className: 'dt-nowrap'
            }
        ],
        language: {
            url: '../includes/pt-BR.json',
        },
    });
}

function abrirTabUsuarios() {
    const cartoes = $("#cartoes");
    const usuarios = $("#cartaoUsuarios");
    //
    cartoes.addClass("d-none");
    usuarios.removeClass("d-none").show();
    //
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
}

async function f_usuario_visualizar(id) {
    //
    $("#rodape").show();
    const dados = await fetch('../includes/rh_usuarios_con_aj.php?id=' + id);
    const resposta = await dados.json();
    //console.log(resposta);            
    if (resposta['status']) {
        visModalUsu.show();
        //
        document.getElementById("idUsuario" ).innerHTML = resposta["dados"].idUsuario;
        document.getElementById("v_usu_idPessoa"  ).innerHTML = resposta["dados"].idPessoa;
        document.getElementById("idGrupo"   ).innerHTML = resposta["dados"].idUsuarioGrupo + " | " + resposta["dados"].dsGrupo;
        document.getElementById("login"     ).innerHTML = resposta["dados"].login;
        document.getElementById("nome"      ).innerHTML = resposta["dados"].nome;
        document.getElementById("nomeSocial").innerHTML = resposta["dados"].nomeSocial;
        document.getElementById("cpf"       ).innerHTML = resposta["dados"].cpf;
        document.getElementById("email"     ).innerHTML = resposta["dados"].email;
        document.getElementById("criado_em" ).innerHTML = resposta["dados"].criado_em + " (" + resposta["dados"].criado_por + ")";
        //
        let foto = "../fotos/" + resposta["dados"].foto;
        $('#fichaFoto').attr('src', foto || 'fotos/perfil.png');

        $("#_chaveApp").html( resposta["dados"].chaveApp );
        //
        if (resposta["dados"].uAtivo) {
            document.getElementById("uAtivo").innerHTML = "<kbd class='bg-success'>Sim</kbd>";
        } else {
            document.getElementById("uAtivo").innerHTML = "<kbd class='bg-danger'>Inativo</kbd>";
        }
        //
        if (resposta["dados"].pAtivo) {
            document.getElementById("pAtivo").innerHTML = "<kbd class='bg-success'>Sim</kbd>";
        } else {
            document.getElementById("pAtivo").innerHTML = "<kbd class='bg-danger'>Inativo</kbd>";
        }
        //
        if (resposta["dados"].dcCIPA) {
            document.getElementById("pCipa").innerHTML = "<kbd class='bg-success'>Sim</kbd>";
        } else {
            document.getElementById("pCipa").innerHTML = "<kbd class='bg-danger'>Não</kbd>";
        }
        //
        if (resposta["dados"].dcBrigada) {
            document.getElementById("pBrigada").innerHTML = "<kbd class='bg-success'>Sim</kbd>";
        } else {
            document.getElementById("pBrigada").innerHTML = "<kbd class='bg-danger'>Não</kbd>";
        }
        //
    } else {
        //document.getElementById("msgAlert").innerHTML = resposta['msg'];
    }
}

function btnIncUsuario() {
    incModalUsu.show();
    //- Preenche o Combo Pessoas
    $.post("../includes/rh_usuario_inc_aj2.php", { "idPessoa": 0 },
        function (codigo, status) {
            $("#form-cad-usuario #idSeletorPessoas").html(codigo);
        });
    //- Preenche o Combo Colaboradores
    $.post("../includes/rh_usuario_inc_aj4.php", { "idColab": 0 },
        function (codigo, status) {
            $("#form-cad-usuario #idSeletorColaborador").html(codigo);
        });
    //- Preenche o Combo Subsedes
    $.post("../includes/rh_selectSubsede.php", { "idSubSede": 0 },
        function (codigo, status) {
            $("#form-cad-usuario #idSeletorSubsedes").html(codigo);
        });
}

function f_incluiPessoa(){
    incModalPessoa.show();
}

$(document).on('show.bs.modal', '.modal', function () {
    const zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(() => {
        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
});

//
//- Salvar Inclusão de Usuário
//
function btnIncSalvarUsuario() {
    //
    var idUsuarioGrupo = 2; //- Grupo Padrão
    var idEmpresa = $("#inputIdEmpresa").val();
    var idPessoa  = $("#inputIDPessoa").val();
    var idColab   = $("#form-cad-usuario #inputIDColab").val();
    var login     = $("#inputLogin").val();
    var senha     = $("#inputSenha").val();
    var foto      = $("#inputFoto")[0].files[0]; // Obtenha o arquivo de imagem selecionado
    var chave     =  "";
    var idSubSede = $("#form-cad-usuario #idSubSede").val();
    //
    var chkCipa    = 0;     //- Não é membro da CIPA por padrão
    var chkBrigada = 1;     //- SIM - é membro da Brigada
    //

    if (!idPessoa || isNaN(idPessoa) || Number(idPessoa) <= 0) {
        alert(idPessoa);
        alert("Por favor, Selecione uma Pessoa...");
        $("#inputIDPessoa").focus();
        return false;
    }
    if (idSubSede == 0) {
        alert("Por favor, Selecione uma Subsede...");
        $("#form-cad-usuario #idSubSede").focus();
        return false;
    }
    if (login == "") {
        alert("Por favor, preencha o campo de login.");
        $("#inputLogin").focus();
        return false;
    }
    if (senha == "") {
        alert("Por favor, preencha a SENHA de login.");
        $("#inputSenha").focus();
        return false;
    }

    var formData = new FormData();
    formData.append("idUsuarioGrupo", idUsuarioGrupo);
    formData.append("idPessoa", idPessoa);
    formData.append("idColab", idColab);
    formData.append("idEmpresa", idEmpresa);
    formData.append("login", login);
    formData.append("senha", senha);
    formData.append("foto", foto);
    formData.append("chaveApp", chave);
    formData.append("idSubSede", idSubSede);
    formData.append("chkCipa", chkCipa);
    formData.append("chkBrigada", chkBrigada);

    $.ajax({
        type: 'POST',
        url: '../includes/rh_usuarios_inc_aj.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (dados) {
            //
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertErroUsuInc").html(mensagem);
            //
            setTimeout(function () {
                $("#msgAlertErroUsuInc").html("");
                var dataTableUsuarios = $('#example').DataTable();
                dataTableUsuarios.ajax.reload();
                $("#btnIncUsuReset").click();
                incModal.hide();
            }, 3000);
        },
        error: function () {
            // Tratar erros de requisição aqui
        }
    });
}

function f_usuario_editar( id ){
    altModalUsu.show();
    $.post("../includes/rh_usuario_alt1_aj.php", { idUsuario: id },
        function (dados, status) {
            var response = JSON.parse(dados);
            var idPessoa = response.dados.idPessoa;
            var idColab = response.dados.idColab;
            var idSubSede = response.dados.idSubSede;
            //
            $("#idAltUsuarioGrupo").val(response.dados.idUsuarioGrupo);
            $("#inputCheckCipa"   ).val(response.dados.dcCIPA   );
            $("#inputAltChave"    ).val(response.dados.chaveApp );
            $("#idAltUsuario"     ).val(response.dados.idUsuario);
            //
            if (response.status) {
                //- preenche os campos para edição
                //
                
                $("#inputAltLogin").val(response.dados.login);

                //- preenche o campo pessoa 
                $.post("../includes/rh_usuario_alt3_aj.php", { idPessoa: idPessoa },
                    function (dados3, status2) {
                        $("#idAltSeletorPessoas").html(dados3);
                    }
                );
                //- Preenche o Combo Colaboradores
                $.post("../includes/rh_usuario_alt_aj4.php", { "idColab": idColab },
                    function (codigo, status) {
                        $("#idAltSeletorColaborador").html(codigo);
                    });
                //- Preenche o Combo Subsedes
                $.post("../includes/rh_selectSubsede.php", { idSubSede: idSubSede },
                    function (codigo, status) {
                        $("#idAltSeletorSubsedes").html(codigo);
                    });

                if (response.dados.ativo) {
                    $('#inputAltAtivo').prop('checked', true);
                    $('#inputAltAtivo').val("SIM");
                } else {
                    $('#inputAltAtivo').prop('checked', false);
                    $('#inputAltAtivo').val("NÃO");
                }
                if (response.dados.dcBrigada) {
                    $('#inputChkBrigada').prop('checked', true);
                    $('#inputChkBrigada').val("1");
                } else {
                    $('#inputChkBrigada').prop('checked', false);
                    $('#inputChkBrigada').val("0");
                }
                testeInputAtivo();
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
                $("#msgAlertErroUsuAlt").html(mensagem);
                return false;
            }

        });
    //
}

function testeInputAtivo() {
    if (document.getElementById("inputAltAtivo").checked) {
        $("#textoAltAtivo").html("Ativo!");
        $("#inputAltAtivo").val("SIM");
    } else {
        $("#textoAltAtivo").html("Está Inativo!");
        $("#inputAltAtivo").val("NÃO");
    }

}

function testeCheck( o ){
    if (o.checked) {
        o.value = 1;
    } else {
        o.value = 0;
    }
}

function btnAltSalvarUsuario(){
    //
    var idUsuarioGrupo = $("#form-alt-usuario #idAltUsuarioGrupo").val();
    var idUsuario      = $("#form-alt-usuario #idAltUsuario"     ).val();
    var idPessoa       = $("#form-alt-usuario #inputAltIDPessoa" ).val();
    var idColab        = $("#form-alt-usuario #inputAltIDColab"  ).val();
    var idSubSede      = $("#form-alt-usuario #idSubSede").val();
    var login          = $("#form-alt-usuario #inputAltLogin"  ).val();
    var senha          = $("#form-alt-usuario #inputAltSenha"  ).val();
    var ativo          = $("#form-alt-usuario #inputAltAtivo"  ).val();
    var cipa           = $("#form-alt-usuario #inputCheckCipa" ).val();
    var brigada        = $("#form-alt-usuario #inputChkBrigada").val();
    var chave          = $("#form-alt-usuario #inputAltChave"  ).val();
    var foto           = $("#form-alt-usuario #inputAltFoto")[0].files[0]; // Obtenha o arquivo de imagem selecionado
    //
    if (idUsuarioGrupo == 0) {
        alert("Por favor, Selecione um grupo...");
        $("#inputAltIdUsuarioGrupo").focus();
        return false;
    }
    if (idPessoa == 0) {
        alert("Por favor, Selecione uma Pessoa...");
        $("#inputAltIDPessoa").focus();
        return false;
    }
    if (login == "") {
        alert("Por favor, preencha o campo de login.");
        $("#inputAltLogin").focus();
        return false;
    }
    if (idSubSede == 0) {
        alert("Por favor, selecione a subsede");
        $("#form-alt-usuario #idSubSede").focus();
        return false;
    }

    var formData = new FormData();
    formData.append("idUsuarioGrupo", idUsuarioGrupo);
    formData.append("idPessoa", idPessoa);
    formData.append("idColab", idColab);
    formData.append("idSubSede", idSubSede);
    formData.append("idUsuario", idUsuario);
    formData.append("login", login);
    formData.append("senha", senha);
    formData.append("foto", foto);
    formData.append("ativo", ativo);
    formData.append("checkCipa", cipa);
    formData.append("checkBrigada", brigada);
    formData.append("chaveApp", chave);

    $.ajax({
        type: 'POST',
        url: '../includes/rh_usuario_alt_aj.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (dados) {
            //
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertErroUsuAlt").html(mensagem);
            setTimeout(function () {
                $("#msgAlertErroUsuAlt").html("");
                var dataTableUsuarios = $('#example').DataTable();
                dataTableUsuarios.ajax.reload();
                $("#btnResetAltUsuario").click();
                altModalUsu.hide();
            }, 3000);
        },
        error: function () {
            // Tratar erros de requisição aqui
        }
    });
}