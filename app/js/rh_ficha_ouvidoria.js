const modalLDT = new bootstrap.Modal(document.getElementById("modalLinhaTempo"));
const modalTip = new bootstrap.Modal(document.getElementById("modalNovoTipoAcao"));
const modalVer = new bootstrap.Modal(document.getElementById("modalLinhaTempoView"));
const modalAlt = new bootstrap.Modal(document.getElementById("modalLinhaTempoEdit"));

$(document).ready(function () {

    //$("#sidebarToggle").trigger("click"); // Dispara o clique

    //<!-- Inicialização do Summernote -->
    $('#descricao, #xdescricao').summernote({
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

function inclui_ldt() {
    modalLDT.show();
}

function inclui_tipo_acao() {
    modalTip.show();
}

function salvarNovoTipoAcao() {
    let nome = $('#nomeNovoTipo').val().trim();
    let icone = $('#iconeNovoTipo').val().trim();
    let cor = $('#corNovoTipo').val().trim();

    if (!nome) {
        alert("O nome do tipo de ação é obrigatório.");
        $('#nomeNovoTipo').focus();
        return;
    }

    $.post("includes/rh_ficha_ouvir_aj1.php", {
        acao: "novo_tipo_acao",
        nome: nome,
        icone: icone,
        cor: cor
    }, function (res) {
        if (res.status) {
            // Adiciona ao seletor
            let novaOpcao = new Option(res.nome, res.id, true, true);
            $("#idTipoAcao").append(novaOpcao).trigger('change');
            modalTip.hide();
        } else {
            alert("Erro ao adicionar tipo: " + res.mensagem);
        }
    }, "json");
}

function f_voltar() {
    window.location.href = "rh_ouvidoria.php";
}

function trocar() {
    let id = $('#id').val();
    //
    $.post("rh_ficha_ouvidoria_aj.php", {
        id: id
    }, function (data) {
        let dados = JSON.parse(data);
        if ($('#esconder').val() == 1) {
            $('#linkOlho').html("<i class='fa-regular fa-eye'></i>");
            $("#esconder").val(0);
            //
            //- Mostrar dados decriptografados
            //
            $('#nome').html(dados.nome);
            $('#telefone').html(dados.telefone);
            $('#email').html(dados.email);
            $('#relato').html(dados.relato);
            $('#envolvidos').html(dados.envolvidos);
            $('#nomes_testemunhas').html(dados.nomes_testemunhas);
            $('#resposta_comunicado').html(dados.resposta_comunicado);
            $('#nome_contato').html(dados.nome_contato);
            $('#email_contato').html(dados.email_contato);
            $('#telefone_contato').html(dados.telefone_contato);
            $('#tipo_outro').html(dados.tipo_outro);
            //
        } else {
            $('#linkOlho').html("<i class='fa-regular fa-eye-slash'></i>");
            $("#esconder").val(1);
            //
            function mascarar(texto) {
                return "•".repeat(Math.max(5, texto.length)); // mínimo de 5 bolinhas
            }

            $('#nome').html(mascarar(dados.nome));
            $('#telefone').html(mascarar(dados.telefone));
            $('#email').html(mascarar(dados.email));
            $('#relato').html(mascarar(dados.relato));
            $('#envolvidos').html(mascarar(dados.envolvidos));
            $('#nomes_testemunhas').html(mascarar(dados.nomes_testemunhas));
            $('#resposta_comunicado').html(mascarar(dados.resposta_comunicado));
            $('#nome_contato').html(mascarar(dados.nome_contato));
            $('#email_contato').html(mascarar(dados.email_contato));
            $('#telefone_contato').html(mascarar(dados.telefone_contato));
            $('#tipo_outro').html(mascarar(dados.tipo_outro));

        }
    });
    //
}

function salvarLinhaTempo() {
    //
    let idTipoAcao = $('#idTipoAcao');
    let data = $('#data');
    let descricao = $('#descricao');
    let conteudoDescricao = descricao.summernote('code').replace(/<\/?[^>]+(>|$)/g, "").trim(); // remove tags HTML e espaços
    let mensagem = $("#msgLinhaDoTempo");
    let botoes = $("#botoesLDT");
    //
    if (idTipoAcao.val() == 0) {
        alert("O tipo da ação é obrigatório.");
        idTipoAcao.focus();
        return;
    }
    if (!data.val().trim()) {
        alert("A data da ação é obrigatória.");
        data.focus();
        return;
    }
    if (!conteudoDescricao) {
        alert("A Descrição da ação é obrigatória.");
        descricao.summernote('focus');
        return;
    }
    botoes.hide();
    mensagem.html("<i class='fa-solid fa-spinner fa-spin'></i> Salvando...");
    $.post("includes/rh_ficha_ouvir_aj2.php", {
        idTipoAcao: idTipoAcao.val(),
        data: data.val(),
        descricao: descricao.summernote('code'),
        idDenuncia: $('#idDenuncia').val()
    }, function (res) {
        let dados = JSON.parse( res );
        mensagem.html(dados.msg);
        setTimeout(() => {
            if( dados.status ) {
                window.location.reload();
            }else{
                mensagem.html("");
                botoes.show();
            }            
        }, 3000); // Ajuste o tempo conforme necessário
    });
}

function f_edita_cartao( idAcao ){
    //
    $.post("includes/rh_ficha_ouvir_aj3.php",{ idAcao: idAcao}, function(retorno){
        x = JSON.parse(retorno);
        $("#xformLinhaTempo #idAcao"    ).val( x.dados.idAcao );
        $("#xformLinhaTempo #idTipoAcao").val( x.dados.idAcaoTipo );
        $("#xformLinhaTempo #xdata"     ).val( x.dados.data );
        $("#xformLinhaTempo #xdescricao").summernote('code', x.dados.descricao);
        //
        modalAlt.show();
        if( x.soleitura == true) $("#xbtnSalvar").prop("disabled", true );
    });
}

function f_ver_cartao( idAcao ){
    //
    alert( idAcao );
    $.post("includes/rh_ficha_ouvir_aj3.php",{ idAcao: idAcao}, function(retorno){
        x = JSON.parse(retorno);
        $("#xformLinhaTempoView #viewAcaoTipo").val( x.dados.dsTipoAcao );
        $("#xformLinhaTempoView #viewData"     ).val( x.dados.data );
        $("#xformLinhaTempoView #viewDescricao").html(x.dados.descricao);
        $("#viewCriado").html(x.dados.criado_em + " por " + x.dados.login);
        //
        modalVer.show();
        if( x.soleitura == true) $("#xbtnSalvar").prop("disabled", true );
    });
}

function xsalvarLinhaTempo(){
    let mensagem   = $('#xmsgLinhaDoTempo');
    let idTipoAcao = $("#xformLinhaTempo #idTipoAcao");
    let data       = $("#xformLinhaTempo #xdata");
    let descricao  = $("#xformLinhaTempo #xdescricao");
    let idAcao     = $("#xformLinhaTempo #idAcao").val();
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
    formData.append("idAcao", idAcao); // Adiciona idPessoa ao FormData
    //
    mensagem.html('<i class="fa-solid fa-spinner fa-spin"></i> Salvando...');
    $("#xbotoesLDT").hide();
    //
    $.ajax({
        url: "includes/rh_ficha_ouvir_aj5.php",
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
        $.post("includes/rh_ficha_ouvir_aj4.php",{ idAcao: idAcao}, function(retorno){
            x = JSON.parse(retorno);
            //alert( x.msg );
            window.location.reload();
        });
    }
}