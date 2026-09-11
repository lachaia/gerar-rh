//
// - g_pessoas.js | Rotinas JavaScript do rh_pessoas.php
// - (C) Chaia - 18/08/2023 | 19/02/2025
//

//const visModal = new bootstrap.Modal(document.getElementById("modalVisualisar"));
//const incModal = new bootstrap.Modal(document.getElementById("modalIncluirPessoa"));
//const altModal = new bootstrap.Modal(document.getElementById("modalEditarPessoa"));
const emlModal = new bootstrap.Modal(document.getElementById("modalEmail"));

var dataTable = "";

$(document).ready(function () {

    $("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela(); // Aguarda um tempo mínimo antes de chamar a função
    }, 100); // Ajuste o tempo conforme necessário
    //
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    $('#mensagem').summernote({
        height: 200,
        lang: 'pt-BR',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

});

function constroiTabela() {
    // Definir quantidade de linhas por página dinamicamente
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    //
    var linhasPorPagina = 10;
    if( alturaViewport > 800) linhasPorPagina = 13
    if( alturaViewport > 900) linhasPorPagina = 14
    if( alturaViewport > 1000) linhasPorPagina = 18
    //
    dataTable = new DataTable('#example', {
        "processing": false,
        "serverSide": false,
        "order": [
            [1, "asc"]
        ],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas        
        "ajax": {
            "url": "rh_pessoas_aj.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [0, 3, 4, 6, 7, 8], // Índices das colunas a serem centralizadas (3 e 4 neste caso)
                "className": "text-center" // Classe CSS para centralizar o conteúdo
            },
            {
                "targets": [],
                "className": "dt-body-right"
            },
            { 'orderable': false, 'targets': 8 },
        ],
        language: {
            url: 'includes/pt-BR.json',
        },
    });

}

async function f_excluir(id) {
    //
    const dados = await fetch('includes/g_pessoas_con_aj.php?id=' + id);
    const resposta = await dados.json();
    var botoes = '<button type="button" class="btn btn-warning btn-sm" onClick="f_excluir_commit(' + id + ')">Excluir</button>';
    botoes += '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>';
    //console.log(resposta);            
    if (resposta['status']) {
        visModal.show();
        //
        document.getElementById("idPessoa").innerHTML = resposta["dados"].idPessoa;
        document.getElementById("nome").innerHTML = resposta["dados"].nome;
        document.getElementById("nomeSocial").innerHTML = resposta["dados"].nomeSocial;
        document.getElementById("cpf").innerHTML = resposta["dados"].cpf;
        document.getElementById("telefone").innerHTML = resposta["dados"].telefone;
        document.getElementById("email").innerHTML = resposta["dados"].email;
        //
        if (resposta["dados"].ativo) {
            document.getElementById("pAtivo").innerHTML = "Sim";
        } else {
            document.getElementById("pAtivo").innerHTML = "Inativo";
        }
        document.getElementById("msgAlert").innerHTML = "";
        document.getElementById("divBotaoVisualizar").innerHTML = botoes;
    } else {
        document.getElementById("msgAlert").innerHTML = resposta['msg'];
    }
}

async function f_excluir_commit(id) {
    if (confirm("Você tem certeza que deseja continuar?")) {
        //
        const dados = await fetch('includes/g_pessoas_exc_aj.php?id=' + id);
        const response = await dados.json();
        //
        if (response.status) {
            var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
        } else {
            var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
        }
        $("#divMsgVisualizar").html(mensagem);
        //
        setTimeout(function () {
            $("#divMsgVisualizar").html("");
            var dataTable = $('#example').DataTable();
            dataTable.ajax.reload();
            visModal.hide();
        }, 3000);

    } else {
        return false;
    }
}

function f_ged(id) {
    alert(" mostrando os arquivos de " + id);
}

function f_incluir_commit() {
    //
    // Salvar dados da Modal Inclusão de Pessoas
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
    if (!validarCPF($("#_cpf").val())) {
        alert("CPF INVÁLIDO, tente outro!");
        $("#_cpf").focus();
        return false;
    }
    //- Envia o formulário para salvar
    //
    $.post("includes/g_pessoas_inc_aj.php",
        {
            nome: $("#_nomePessoa").val(),
            nomeSocial: $("#_nomeSocial").val(),
            cpf: $("#_cpf").val(),
            telefone: $("#_telefone").val(),
            email: $("#_email").val()
        },
        function (dados, status) {
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertErroCadPessoa").html(mensagem);
            //
            setTimeout(function () {
                $("#msgAlertErroCadPessoa").html("");
                incModal.hide();
                var dataTable = $('#example').DataTable();
                dataTable.ajax.reload();
            }, 3000);
        });
}

async function f_editar(id) {
    const dados = await fetch('includes/g_pessoas_con_aj.php?id=' + id);
    const resposta = await dados.json();
    //console.log(resposta);            
    if (resposta['status']) {
        altModal.show();
        //
        document.getElementById("altid").value = resposta["dados"].idPessoa;
        document.getElementById("altNome").value = resposta["dados"].nome;
        document.getElementById("altNomeSocial").value = resposta["dados"].nomeSocial;
        document.getElementById("altcpf").value = resposta["dados"].cpf;
        document.getElementById("altTelefone").value = resposta["dados"].telefone;
        document.getElementById("altEmail").value = resposta["dados"].email;
        document.getElementById("msgAlertAltPessoa").innerHTML = "";
        //
        if (resposta['dados'].ativo) {
            $('#altAtivo').prop('checked', true);
            $("#textoAltAtivo").html("Ativa!");
            $("#altAtivo").val("SIM");
        } else {
            $('#altAtivo').prop('checked', false);
            $("#textoAltAtivo").html("Inativa!");
            $("#altAtivo").val("NÃO");
        }
    } else {
        document.getElementById("msgAlertAltPessoa").innerHTML = resposta['msg'];
    }
}

function f_editar_commit() {
    $.post("includes/g_pessoa_alt_aj.php",
        {
            idModulo: 7,
            idPessoa: $("#altid").val(),
            nome: $("#altNome").val(),
            nomeSocial: $("#altNomeSocial").val(),
            cpf: $("#altcpf").val(),
            telefone: $("#altTelefone").val(),
            email: $("#altEmail").val(),
            ativo: $("#altAtivo").val()
        },
        function (dados, status) {
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertAltPessoa").html(mensagem);
            setTimeout(function () {
                $("#msgAlertAltPessoa").html("");
                var dataTable = $('#example').DataTable();
                dataTable.ajax.reload();
                altModal.hide();
            }, 3000);
        });
}

function testeInputAtivo() {
    if (document.getElementById("altAtivo").checked) {
        $("#textoAltAtivo").html("Ativa!");
        $("#altAtivo").val("SIM");
    } else {
        $("#textoAltAtivo").html("Está Inativa!");
        $("#altAtivo").val("NÃO");
    }

}

function f_email(idPessoa, email) {
    emlModal.show();
    $("#para").html(email);
    $("#destino").val(email);
    $("#formEmail #idPessoa").val(idPessoa);
}

function f_email_commit() {
    //
    var formulario = document.getElementById("formEmail");
    var titulo = document.getElementById("titulo");
    var mensagem = document.getElementById("mensagem");
    //
    if (titulo.value.length == 0) {
        alert("Favor informar o Título do e-Mail");
        $("#titulo").focus();
        return false;
    }
    if (mensagem.value.length == 0) {
        alert("Favor informar a Mensagem do e-Mail");
        $("#mensagem").focus();
        return false;
    }
    $("#divBotoesEmail").hide();
    $("#divMensagemEml").show();
    //
    $.ajax({
        method: "POST",
        url: "includes/rh_pessoa_eml_aj.php",
        data: new FormData(formulario),
        contentType: false,
        processData: false,
        success: function (retorno) {
            console.log(retorno);
            const dados = JSON.parse(retorno);
            $("#divMensagemEml").html(dados.msg);
            const myTimeout = setTimeout(function () {
                if (dados.status == false) {
                    $("#divBotoesEmail").show();
                    $("#divMensagemEml").hide();
                } else {
                    location.href = "rh_pessoas.php";
                }
            }, 3000);
            return false;

        }
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

async function f_ver(id) {
    //
    location.href = "rh_ficha_pessoa.php?id=" + id;
    //
}

async function f_editar(id) {
    //
    location.href = "rh_pessoa_frm.php?id=" + id + "&edit=1";
    //
}

function f_excluir( id ) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#msgAlert").show();
        $.post("includes/rh_pessoa_aj17.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            $("#msgAlert").html( dados.msg );
            //
            setTimeout(function() {
                document.location.reload(true);
            }, 3000);            
            
        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function cv( id ){
    //
    location.href = "rh_cv.php?id=" + id;
    //
}