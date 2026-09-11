const modalLDT = new bootstrap.Modal(document.getElementById("modalLinhaTempo"));
//const verModal = new bootstrap.Modal(document.getElementById("modalLinhaTempoVer"));

$(document).ready(function() {

    $("#sidebarToggle").trigger("click"); // Dispara o clique

    //<!-- Inicialização do Summernote -->
    $('#emailCorpo, #descricao, #xdescricao').summernote({
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

function f_mostra(idDoc, idPessoa, arquivo) {
    const caminho = `./docs/pessoa_${idPessoa}/${arquivo}`;
    console.log("Abrindo arquivo: " + caminho);

    // Define o caminho no iframe
    document.getElementById("iframeArquivo").src = caminho;

    // Abre o modal
    let modal = new bootstrap.Modal(document.getElementById('modalArquivo'));
    modal.show();
}

function f_mostra_aba(idPessoa, arquivo) {
    const caminho = `./docs/pessoa_${idPessoa}/${arquivo}`;
    window.open(caminho, '_blank');
}


function f_down(idDoc, idPessoa, arquivo) {
    let caminho = "./docs/pessoa_" + idPessoa + "/" + arquivo;

    // Criar um link temporário e simular o clique
    let link = document.createElement("a");
    link.href = caminho;
    link.target = "_blank"; // Abre em nova aba
    link.download = arquivo; // Sugere o download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link); // Remove o link após o clique
}

function f_email(idDoc, idPessoa, arquivo) {
    // Preenche os campos com informações relevantes
    let sugestaoTitulo = "Envio de Documento - " + arquivo;
    let corpoPadrao = `<p>Prezado(a),</p><p>Segue anexo o documento <strong>${arquivo}</strong>.</p><p>Atenciosamente,</p>`;

    $("#idDoc").val(idDoc);
    $("#arquivo").val(arquivo);
    $("#idPessoa").val(idPessoa);

    $("#emailTitulo").val(sugestaoTitulo);
    $("#emailCorpo").summernote('code', corpoPadrao); // Adiciona corpo padrão formatado

    // Abre a modal
    $("#modalEmail").modal('show');
}


// Função para enviar o e-mail via AJAX
function f_email_commit() {
    //
    // 🔥 1. PEGANDO O CONTEÚDO ATUALIZADO DO SUMMERNOTE
    let emailCorpo = $('#emailCorpo').summernote('code');
    $("#emailCorpo").val(emailCorpo); // Garante que o textarea tenha o conteúdo atualizado
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("emailForm"));
    let destinatario = $("#emailDestinatario").val().trim();    
    //
    let mensagem = $("#msgEmail");

    if (destinatario === "") {
        alert("Informe um destinatário!");
        return;
    }

    // Desabilita o botão e muda o texto para indicar que está enviando
    $(".btn-enviar").html('<i class="fa-solid fa-spinner fa-spin"></i> Enviando...');
    $(".btn-enviar").prop("disabled", true);

    $.ajax({
        url: "includes/rh_ficha_pessoa_aj1.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function(response) {
            let dados = JSON.parse( response );
            mensagem.html( dados.msg );
            setTimeout(() => {
                $("#modalEmail").modal('hide'); // Fecha a modal após o envio
                mensagem.html("");
            }, 5000); // Ajuste o tempo conforme necessário
        },
        error: function(xhr) {
            mensagem.html("Erro ao enviar o e-mail: " + xhr.responseText);
        },
        complete: function() {
            $(".btn-enviar").html('Enviar <i class="fa-solid fa-paper-plane"></i>');
            $(".btn-enviar").prop("disabled", false);
        }
    });
}

function f_mostra_email(idEmail) {
    //
    $.post("includes/rh_ficha_pessoa_aj2.php",{ idEmail: idEmail}, function(retorno){
        x = JSON.parse(retorno);
        //alert( retorno );
        let destinatario = x.dados[0].destinatario; 
        let cc = x.dados[0].cc; 
        let cco = x.dados[0].cco; 
        let titulo = x.dados[0].titulo; 
        let corpo = x.dados[0].mensagem; 
        //
        let arquivo = x.dados[0].arquivo; 
        let idPessoa = x.dados[0].idPessoa; 
        let idDoc = x.dados[0].idDoc; 
        let botao = `<button type='button' class='btn btn-outline-secondary btn-sm ms-2' id='btnVisualizarAnexo' 
                onClick='f_mostra_aba(${idPessoa}, "${arquivo}")'>
                <i class='fa-solid fa-magnifying-glass'></i>
             </button>`;
        //
        $("#v_emailDestinatario").html( destinatario );
        $("#v_emailCC").html( cc );
        $("#v_emailCCO").html( cco );
        $("#v_emailAssunto").html( titulo );
        $("#v_emailCorpo").html( corpo );
        $("#v_emailAnexo").html( arquivo );
        $("#v_botao").html( botao );
        //
        $("#modalVisualizarEmail").modal("show")
    });
    /*
    
    $("#emailAssunto").text(assunto);
    $("#emailCorpo").html(corpo); // Usa .html() para manter formatação HTML do e-mail
    */
}

function inclui_ldt() {
    modalLDT.show();
}

function salvarLinhaTempo(){
    let mensagem = $('#msgLinhaDoTempo');
    let idTipoAcao = $("#formLinhaTempo #idTipoAcao");
    let data       = $("#formLinhaTempo #data");
    let descricao  = $("#formLinhaTempo #descricao");
    let idPessoa   = $("#idPessoa").val();
    //
    if( idTipoAcao.val() == 0 ){
        alert("Selecione um tipo de ação!");
        idTipoAcao.focus();
        return;
    }
    //
    if( data.val() == '' ){
        alert("Informe a data da ação!");
        data.focus();
        return;
    }
    //
    if( descricao.val() == '' ){
        alert("Descreva a acão ou evento!");
        descricao.focus();
        return;
    }
    //
    let xdescricao = $('#descricao').summernote('code');
    $("#descricao").val(xdescricao); // Garante que o textarea tenha o conteúdo atualizado
    //
    let formData = new FormData(document.getElementById("formLinhaTempo"));
    formData.append("idPessoa", idPessoa); // Adiciona idPessoa ao FormData
    //
    mensagem.html('<i class="fa-solid fa-spinner fa-spin"></i> Salvando...');
    $("#botoesLDT").hide();
    //
    $.ajax({
        url: "includes/rh_ficha_pessoa_aj3.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function(response) {
            const dados = JSON.parse( response );
            //alert( dados.msg );
            mensagem.html( dados.msg );
            setTimeout(() => {
                /*
                mensagem.html('');
                $("#botoesLDT").show();
                modalLDT.hide();
                mensagem.html("");
                */
                window.location.reload();
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function(xhr) {
            mensagem.html("Erro ao enviar o e-mail: " + xhr.responseText);
        }
    });
}

function f_mostra_cartao( idAcao ){
    //
    $.post("includes/rh_ficha_pessoa_aj4.php",{ idAcao: idAcao}, function(retorno){
        x = JSON.parse(retorno);
        $("#xformLinhaTempo #idAcao"    ).val( x.dados.idAcao );
        $("#xformLinhaTempo #idTipoAcao").val( x.dados.idAcaoTipo );
        $("#xformLinhaTempo #xdata"     ).val( x.dados.data );
        $("#xformLinhaTempo #xdescricao").summernote('code', x.dados.descricao);
        //
        $("#modalLinhaTempoVer").modal("show");
        if( x.soleitura == true) $("#xbtnSalvar").prop("disabled", true );
    });
}

function xsalvarLinhaTempo(){
    let mensagem   = $('#xmsgLinhaDoTempo');
    let idTipoAcao = $("#xformLinhaTempo #idTipoAcao");
    let data       = $("#xformLinhaTempo #xdata");
    let descricao  = $("#xformLinhaTempo #xdescricao");
    let idPessoa   = $("#idPessoa").val();
    //
    if( idTipoAcao.val() == 0 ){
        alert("Selecione um tipo de ação!");
        idTipoAcao.focus();
        return;
    }
    //
    if( data.val() == '' ){
        alert("Informe a data da ação!");
        data.focus();
        return;
    }
    //
    if( descricao.val() == '' ){
        alert("Descreva a acão ou evento!");
        descricao.focus();
        return;
    }
    //
    let xdescricao = $('#descricao').summernote('code');
    $("#descricao").val(xdescricao); // Garante que o textarea tenha o conteúdo atualizado
    //
    let formData = new FormData(document.getElementById("xformLinhaTempo"));
    formData.append("idPessoa", idPessoa); // Adiciona idPessoa ao FormData
    //
    mensagem.html('<i class="fa-solid fa-spinner fa-spin"></i> Salvando...');
    $("#xbotoesLDT").hide();
    //
    $.ajax({
        url: "includes/rh_ficha_pessoa_aj5.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function(response) {
            const dados = JSON.parse( response );
            //alert( dados.msg );
            mensagem.html( dados.msg );
            setTimeout(() => {
                window.location.reload();
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function(xhr) {
            mensagem.html("Erro ao enviar o e-mail: " + xhr.responseText);
        }
    });    
}

function f_del( idAcao ){
    if( confirm("Confirma a exclusão do registro?") ){
        $.post("includes/rh_ficha_pessoa_aj6.php",{ idAcao: idAcao}, function(retorno){
            x = JSON.parse(retorno);
            //alert( x.msg );
            window.location.reload();
        });
    }
}