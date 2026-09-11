//
//- ponto.js | (C)haia, 08/12/2025
//

const modal_ponto = new bootstrap.Modal(document.getElementById("modalPonto"));
const modal_solic = new bootstrap.Modal(document.getElementById("modalSolicitacao"));

function f_ver_ponto( id ){
    //
    $.post("includes/ponto_aj1.php", { id: id }, function (data) {
        $("#pontoConteudo").html(data);
        modal_ponto.show();
    });    
}

function abrirModalNovo( dia ){
    const principal = document.getElementById("divPrincipal");
    const formulario = document.getElementById("divFormulario");
    //
    principal.classList.add("d-none");
    formulario.classList.remove("d-none");
    //
    $.post("includes/ponto_aj2.php", { dia: dia }, function (data) {
        formulario.innerHTML = data;
    })
}

function abrirModalEditar( id ){
    const principal = document.getElementById("divPrincipal");
    const formulario = document.getElementById("divFormulario");
    //
    principal.classList.add("d-none");
    formulario.classList.remove("d-none");
    //
    $.post("includes/ponto_aj4.php", { id: id }, function (data) {
        formulario.innerHTML = data;
    })  
}

function abrirModalExcluir( id ){
    const principal = document.getElementById("divPrincipal");
    const formulario = document.getElementById("divFormulario");
    //
    principal.classList.add("d-none");
    formulario.classList.remove("d-none");
    //
    $.post("includes/ponto_aj6.php", { id: id }, function (data) {
        formulario.innerHTML = data;
    }) 
}

function f_salvarBatida(){
    const botoes = document.getElementById("divBotoesFormulario");
    const mensagem = document.getElementById("msgAlertaFormulario");
    const motivo = document.getElementById("motivo");
    //
    if( motivo.value == "" ){
        alert("Selecione um motivo.");
        motivo.focus();
        return false;
    }
    //
    botoes.classList.add("d-none");
    mensagem.innerHTML = "<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>";
    //
    const dados = $("#formBatida").serialize();
    $.post("includes/ponto_aj3.php", dados, function (data) {
        mensagem.innerHTML = data.msg;
        setTimeout(function() {
            //mensagem.classList.add("d-none");
            //botoes.classList.remove("d-none");
            document.location.reload();
        }, 3000);
    },'json');
}

function f_salvar_alteracao(){
    const botoes = document.getElementById("divBotoesFormulario");
    const mensagem = document.getElementById("msgAlertaFormulario");
    const motivo = document.getElementById("motivo");
    //
    if( motivo.value == "" ){
        alert("Selecione um motivo.");
        motivo.focus();
        return false;
    }    
    //
    botoes.classList.add("d-none");
    mensagem.innerHTML = "<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>";
    //
    const dados = $("#formBatida").serialize();
    $.post("includes/ponto_aj5.php", dados, function (data) {
        mensagem.innerHTML = data.msg;
        setTimeout(function() {
            //mensagem.classList.add("d-none");
            //botoes.classList.remove("d-none");
            document.location.reload();
        }, 3000);
    },'json');
}

function f_salvar_exclusao(){
    const botoes = document.getElementById("divBotoesFormulario");
    const mensagem = document.getElementById("msgAlertaFormulario");
    const motivo = document.getElementById("motivo");
    //
    if( motivo.value == "" ){
        alert("Selecione um motivo.");
        motivo.focus();
        return false;
    }    
    //
    botoes.classList.add("d-none");
    mensagem.innerHTML = "<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>";
    //
    const dados = $("#formBatida").serialize();
    $.post("includes/ponto_aj7.php", dados, function (data) {
        mensagem.innerHTML = data.msg;
        setTimeout(function() {
            document.location.reload();
        }, 3000);
    },'json');    
}

//
//- SOLICITAÇÕES
//

function f_ver_solicitacao( id ){
    //
    $.post("includes/ponto_sol_aj1.php", { id: id }, function (data) {
        $("#pontoConteudo").html(data);
        modal_ponto.show();
    }); 
}

function f_excluir_solicitacao( id ){
    if (confirm("Tem certeza que deseja excluir esta solicitação?")) {
        $.post('../ponto/api/edita_cartao_aj3.php', {
            id: id
        }, function(resposta) {
            console.log(resposta);

            // A resposta já é um objeto JS
            if (resposta.status === true) {
                alert(resposta.msg);
                location.reload();
            } else {
                alert('Erro ao excluir: ' + (resposta.mensagem || 'tente novamente'));
            }
        }, 'json'); // <-- importante: informa que o retorno é JSON
    }    
}

//
//- ESPELHOS DO PONTO
//

function f_espelhos_do_ponto(){
    const area_ponto = document.getElementById("area_ponto");
    const area_espelho = document.getElementById("area_espelho");
    const botao_ponto = document.getElementById("botao_ponto");
    const botao_espelho = document.getElementById("botao_espelho");
    //
    area_ponto.classList.add("d-none");
    area_espelho.classList.remove("d-none");
    botao_ponto.classList.remove("d-none");
    botao_espelho.classList.add("d-none");
}

function f_ver_meu_ponto(){
    const area_ponto    = document.getElementById("area_ponto");
    const area_espelho  = document.getElementById("area_espelho");
    const botao_ponto   = document.getElementById("botao_ponto");
    const botao_espelho = document.getElementById("botao_espelho");
    //
    area_espelho.classList.add("d-none");
    area_ponto.classList.remove("d-none"); 
    botao_ponto.classList.add("d-none");
    botao_espelho.classList.remove("d-none");       
}

function f_ver_espelho( espelho_id, status ){
    const area_espelho  = document.getElementById("area_espelho");
    const area_arquivo  = document.getElementById("area_arquivo");
    const botao_espelho = document.getElementById("botao_espelho");
    const botao_voltar  = document.getElementById("botao_voltar");
    const botao_ponto   = document.getElementById("botao_ponto");
    //
    area_espelho.classList.add("d-none");
    area_arquivo.classList.remove("d-none");
    botao_espelho.classList.add("d-none");
    botao_voltar.classList.remove("d-none");
    botao_ponto.classList.add("d-none");
    //
    $.post("includes/ponto_espelho_aj1.php", { espelho_id: espelho_id, status: status }, function (dados) {
        $("#area_arquivo").html(dados);
        //alert( "ID: " + espelho_id + " - Status: " + status );
    });    
}

function f_voltar_ao_espelho(){
    const botao_voltar  = document.getElementById("botao_voltar");
    const botao_ponto   = document.getElementById("botao_ponto");
    //
    const area_espelho  = document.getElementById("area_espelho");
    const area_arquivo  = document.getElementById("area_arquivo");
    //
    area_arquivo.classList.add("d-none");
    area_espelho.classList.remove("d-none");
    botao_voltar.classList.add("d-none");
    botao_ponto.classList.remove("d-none");
}

function f_assinar( id ){
    const mensagem = $("#msgAssinatura");
    if (confirm("Tem certeza que deseja ASSINAR este Ponto?")) {
        $.post('../ponto/api/espelho_assina.php', {
            id: id,
            origem: 'colaborador'
        }, function(resposta) {
            console.log(resposta);

            // A resposta já é um objeto JS
            if (resposta.status === true) {
                mensagem.html(resposta.msg);
                setTimeout(function() {
                    location.reload();
                }, 3000);
                
            } else {
                alert('Erro ao assinar: ' + (resposta.mensagem || 'tente novamente'));
            }
        }, 'json'); // <-- importante: informa que o retorno é JSON
    } 
}