//
// rh_pessoa_cv.js | Scripts JS de rh_pessoa_cv.php
// (C)haia, 24/03/2025

const fa_Modal_Ver = new bootstrap.Modal(document.getElementById("faModalVer"));
const fa_Modal_Inc = new bootstrap.Modal(document.getElementById("faModalInc"));

const fa_Modal_IncIE = new bootstrap.Modal(document.getElementById("faModalIncIE"));

function fa_ver( id ){
    fa_Modal_Ver.show();
    $.post("includes/rh_pessoa_cv_aj1.php",{ id: id}, function( retorno ){
        //
        //alert( retorno );
        let dados = JSON.parse( retorno );
        $("#v_curso").html( dados.curso )
        $("#v_instituicao").html( dados.nmInstituicao )
        $("#v_sigla").html( dados.sigla )
        $("#v_nivel").html( dados.nivel )
        $("#v_ano_conclusao").html( dados.ano_conclusao )
        $("#divQuando").html( dados.dtLogin + " - " + dados.login )
    });
    
    
}

function f_voltar() {
    window.location.href = "rh_pessoas.php";
}

function fa_excluir( id ){
    alert( "excluindo " + id );
}

function fa_editar( id ){
    alert( "Editando " + id );
}

function fa_incluir(){
    fa_Modal_Inc.show();
}

function fa_incluir_salva(){
    alert( "salvando");
    //
    let mensagem = $("#msgformIncFAIE");
    //
    let nome    = $("#formIncFAIE #_nomeInstituicao");
    let sigla   = $("#formIncFAIE #_sigla");
}

//----------------------------------------------------
// INSTITUIÇÃO DE ENSINO
//
function f_inclui_ie() {
    // Exibe a modal
    fa_Modal_IncIE.show();
    
    // Configura o foco após 500ms (quando a modal estiver totalmente visível)
    $('#fa_Modal_IncIE').on('shown.bs.modal', function() {
        setTimeout(function() {
            $("#_nomeInstituicao").focus();
        }, 3000);
    });
}

function f_inclui_ie_salva(){
    //
    let mensagem = $("#msgformIncFAIE");
    //
    let nome    = $("#formIncFAIE #_nomeInstituicao");
    let sigla   = $("#formIncFAIE #_sigla");
    let idNivel = $("#formIncFAIE #idNivel");
    let cidade  = $("#formIncFAIE #_cidade");
    let uf      = $("#formIncFAIE #uf");
    let pais    = $("#formIncFAIE #_pais");
    //
    if( nome.val() == ''){
        alert( "Informe o nome da Instituição");
        nome.focus();
        return false;
    }
    //
    if( sigla.val() == ''){
        alert( "Informe a SIGLA da Instituição");
        sigla.focus();
        return false;
    }
    //
    if( idNivel.val() == 0){
        alert( "Informe o nível de ensino");
        idNivel.focus();
        return false;
    }
    //
    if( cidade.val() == ''){
        alert( "Informe a Cidade onde está a Instituição");
        cidade.focus();
        return false;
    }
    //
    if( uf.val() == 0){
        alert( "Informe o Estado onde está a Instituição");
        uf.focus();
        return false;
    }
    //
    if( pais.val() == ''){
        alert( "Informe o País onde está a Instituição");
        pais.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formIncFAIE"));
    $("#botoes_fa_id").hide();
    mensagem.html("AGUARDE ...");
    //
    $.ajax({
        url: "includes/rh_pessoa_cv_aj2.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#botoes_fa_id").show();
                mensagem.html("");
                fa_Modal_IncIE.hide();
                //
                let newOption = `<option value="${dados.dados.idInstituicao}" selected>${dados.dados.nome} (${dados.dados.cidade}/${dados.dados.uf}-${dados.dados.pais})</option>`;
                // Adicionar a nova opção ao select
                $("#idInstituicao").append(newOption);

                // Alternativamente, você pode forçar o "Selecione uma IE" a ser desmarcado se estiver presente
                $("#idInstituicao").val(dados.dados.idInstituicao);
                //
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
        },
        complete: function () {
            //
        }
    });    
} 