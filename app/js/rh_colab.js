//
// - g_pessoas.js | Rotinas JavaScript do rh_colab.php
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
            "url": "rh_colab_aj.php",
            "type": "POST"
        },
        "columnDefs": [
            {
                "targets": [0,2,5,6,7,8,9], // Índices das colunas a serem centralizadas (3 e 4 neste caso)
                "className": "text-center" // Classe CSS para centralizar o conteúdo
            },
            {
                "targets": [],
                "className": "dt-body-right"
            },
            { 'orderable': false, 'targets': [] },
        ],
        language: {
            url: 'includes/pt-BR.json',
        },
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
    location.href = "rh_ficha_colab.php?id=" + id;
    //
}

async function f_editar(id) {
    //
    location.href = "rh_colab_edt.php?id=" + id;
    //
}

function f_excluir( id ) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#msgAlert").show();
        $.post("includes/rh_colab_aj12.php", { id: id }, function (response) {
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