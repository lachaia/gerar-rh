//
// rh_colab_frm_js.js | Rotinas de JavaScript para o formulário de colaboração
// (C)haia, 04/04/2025

const add_Modal_Funcao = new bootstrap.Modal(document.getElementById("addModalFuncao"));
const add_Modal_Endereco = new bootstrap.Modal(document.getElementById("addModalEndereco"));
const add_Modal_Dependente = new bootstrap.Modal(document.getElementById("addModalDependente"));

$(document).ready(function () {
    $("#sidebarToggle").trigger("click");

    $("#nmPessoa").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "includes/buscar_pessoas.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response($.map(data, function(item) {
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
        select: function(event, ui) {
            $("#idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
            atualiza_enderecos( ui.item.idPessoa );
        }
    });

    $('#dsFuncao').summernote({
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
                $('.note-editor').addClass('form-control'); // aplica o estilo visual do form-control
                $('.note-editor').css({
                    'padding': '0',
                    'border-radius': '.375rem', // arredondamento padrão do BS5
                    'border-color': '#ced4da'
                });
                $('.note-editable').css({
                    'background-color': '#ffffff',
                    'color': '#000000',
                    'border-radius': '.375rem' // borda interna também arredondada
                }); 
                $('.note-toolbar').css('border-bottom', '1px solid #dee2e6');              
            }
            
        }
    });
    
    $("#nmDependente").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "includes/buscar_pessoas.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idPessoa: item.idPessoa
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $("#idPessoaDep").val(ui.item.idPessoa); // <- campo hidden para dependente
        }
    });
    
});


function f_voltar(){
    window.document.location.href = "rh_colab.php";
}

function f_incluir_commit(){
    //
    let mensagem = $("#msgCadastro");
    let idColab  = $("#idColab");
    //
    let idPessoa  = $("#idPessoa" );
    let matricula = $("#matricula");
    let idOrgao   = $("#idOrgao"  );
    let idCargo   = $("#idCargo"  ); 
    let idFuncao  = $("#idFuncao" );
    let idContratoTipo = $("#idContratoTipo");
    let admissao = $("#admissao");
    let horario_ini = $("#horario_ini");
    let horario_fim = $("#horario_fim");
    let carga_h = $("#carga_h");
    let salario = $("#salario");
    let pis = $("#pis");
    let ctps = $("#ctps");
    let mae = $("#mae");
    let idBanco = $("#idBanco");
    let bco_agencia = $("#bco_agencia");
    let bco_cc = $("#bco_cc");
    //
    if (idPessoa.val() == "0") {
        alert("Selecione uma pessoa!");
        $("#nmPessoa").val("");
        $("#nmPessoa").focus();
        return false;
    }
    if (parseInt(matricula.val()) <= 0 || isNaN(parseInt(matricula.val()))) {
        alert("Informe uma matrícula válida (maior que zero)!");
        matricula.focus();
        return false;
    }
    if (idCargo.val() == 0) {
        alert("Selecione um Cargo!");
        idCargo.focus();
        return false;
    }
    if (idFuncao.val() == 0) {
        alert("Selecione uma função!");
        idFuncao.focus();
        return false;
    }
    if (idOrgao.val() == 0) {
        alert("Informe um Órgão!");
        idOrgao.focus();
        return false;
    }
    if (idContratoTipo.val() == 0) {
        alert("Informe o Regime de Admissão!");
        idContratoTipo.focus();
        return false;
    }
    if (admissao.val() == "") {
        alert("Informe o Data de Admissão!");
        admissao.focus();
        return false;
    }
    if (horario_ini.val() == "") {
        alert("Informe o Horário inicial do trabalho!");
        horario_ini.focus();
        return false;
    }
    if (horario_fim.val() == "") {
        alert("Informe o Horário final do trabalho!");
        horario_fim.focus();
        return false;
    }
    if (carga_h.val() == "") {
        alert("Informe o Carga Horária Semanal!");
        carga_h.focus();
        return false;
    }
    if (salario.val() == "") {
        alert("Informe o Salário Contratual!");
        salario.focus();
        return false;
    }
    if (pis.val() == "") {
        alert("Informe PIS/PASEP/NIS!");
        pis.focus();
        return false;
    }
    if (ctps.val() == "") {
        alert("Informe número da CTPS!");	
        ctps.focus();
        return false;
    }
    if (mae.val() == "") {
        alert("Informe nome da Mãe do Colaborador!");	
        mae.focus();
        return false;
    }
    if (idBanco.val() == 0) {
        alert("Selecione o Banco para depósito!");
        idBanco.focus();
        return false;
    }
    if (bco_agencia.val() == "") {
        alert("Informe o nº da Agência Bancária!");	
        bco_agencia.focus();
        return false;
    }
    if (bco_cc.val() == "") {
        alert("Informe o nº da Conta Corrente!");	
        bco_cc.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formCadastro"));
    $("#botoes_principal").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "includes/rh_colab_aj1.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            idColab.val( dados.idColab );
            setTimeout(() => {
                $("#btnVoltar").show();
                mensagem.html("");
                //document.location.reload(true);
                //
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
        }
    });
}

function f_add_funcao(){
    add_Modal_Funcao.show();
    setTimeout(() => {
        $("#nmFuncao").focus();
    }, 500);
}

function f_add_funcao_commit(){
    let nmFuncao = $("#nmFuncao");
    let dsFuncao = $("#dsFuncao");
    //
    if (nmFuncao.val() == "") {
        alert("Informe o nome da função!");
        nmFuncao.focus();
        return false;
    }
    if (dsFuncao.val() == "") {
        alert("Informe a descrição da função!");
        dsFuncao.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formFuncao"));
    $("#botoesModalFuncao").hide();
    $("#msgFuncao").html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "includes/rh_colab_aj2.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let d = JSON.parse(response);
            $("#msgFuncao").html(d.msg);
            setTimeout(() => {
                add_Modal_Funcao.hide();
                $("#btnVoltar").show();
                $("#msgFuncao").html("");
                //document.location.reload(true);
                //
            }, 3000); // Ajuste o tempo conforme necessário
            //
            if (d.dados && d.dados.idFuncao) {
                let option = new Option(d.dados.nmFuncao, d.dados.idFuncao, true, true);
                $("#idFuncao").append(option).trigger('change');
            }            
        },
        error: function (xhr) {
            $("#msgFuncao").html("Erro ao enviar o formulário: " + xhr.responseText);
        }
    });
}

function envia_arquivo_ctps(){
    let mensagem = $("#msgAlertaDocs");
    let idPessoa = $("#idPessoa").val();
    let arquivo = $("#arquivo_ctps")[0].files[0];
    //
    if (idPessoa == "0") {
        alert("Selecione um colaborador!");
        return false;
    }
    if (!valida_arquivo("arquivo_ctps")) {
        return false;
    }
    //
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    let formData = new FormData();
    formData.append("idPessoa", idPessoa);
    formData.append("arquivo_ctps", arquivo);
    //
    $.ajax({
        url: "includes/rh_colab_aj3.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                mensagem.html("");
                $("#dsArquivo_ctps").html(dados.dsArquivo);
                $("#dsArquivo_ctps").show();
                //
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            alert("Erro ao enviar o arquivo: " + xhr.responseText);
        }
    });
}

function envia_arquivo_ddir(){
    let mensagem = $("#msgAlertaDocs");
    let idPessoa = $("#idPessoa").val();
    let arquivo = $("#arquivo_ddir")[0].files[0];
    //
    if (idPessoa == "0") {
        alert("Selecione um colaborador!");
        return false;
    }
    if (!valida_arquivo("arquivo_ddir")) {
        return false;
    }
    //
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    let formData = new FormData();
    formData.append("idPessoa", idPessoa);
    formData.append("arquivo_ddir", arquivo);
    //
    $.ajax({
        url: "includes/rh_colab_aj4.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                mensagem.html("");
                $("#dsArquivo_ddir").html(dados.dsArquivo);
                $("#dsArquivo_ddir").show();
                //
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            alert("Erro ao enviar o arquivo: " + xhr.responseText);
        }
    });
}

function valida_arquivo(idInput) {
    let input = document.getElementById(idInput);
    let arquivo = input.files[0];

    if (!arquivo) {
        alert("Selecione um arquivo para enviar.");
        input.focus();
        return false;
    }

    const extensoesValidas = ["pdf", "jpg", "jpeg", "png"];
    const nomeArquivo = arquivo.name;
    const extensao = nomeArquivo.split('.').pop().toLowerCase();

    if (!extensoesValidas.includes(extensao)) {
        alert("Formato de arquivo inválido. Use: PDF, JPG, PNG ou JPEG.");
        input.value = ""; // limpa o input
        input.focus();
        return false;
    }

    return true;
}

function marquei_lider(checkbox) {
    const isChecked = checkbox.checked;
    const idOrgao = $("#idOrgao").val();
    
    // Ajusta o value do checkbox
    checkbox.value = isChecked ? "1" : "0";

    // Se desmarcou, apenas sai
    if (!isChecked) return;

    // Validação: idOrgao deve estar preenchido
    if ( idOrgao == 0) {
        alert("Selecione o órgão antes de marcar como Líder.");
        checkbox.checked = false;
        checkbox.value = "0";
        return;
    }

    // Consulta o nível do órgão
    $.post("includes/rh_colab_aj5.php", { idOrgao }, function (res) {
        try {
            let dados = JSON.parse(res);

            if (dados.nivel == 6) {
                // Ok, órgão tem nível 6 — pode marcar
                checkbox.checked = true;
                checkbox.value = "1";
            } else {
                alert("Somente órgãos de nível 6 podem ter líderes.");
                checkbox.checked = false;
                checkbox.value = "0";
            }
        } catch (e) {
            console.error("Erro no retorno da verificação:", e);
            alert("Erro ao validar o nível do órgão.");
            checkbox.checked = false;
            checkbox.value = "0";
        }
    });
}

function toggle_endereco(o) {
    let idColab = $("#idColab").val();
    if( idColab == 0 || idColab == ''){
        alert( "Preencha e salve o formulário da esquerda antes");
        $("#nmPessoa").focus();
        o.checked = true;
        o.value = "1";
        return false;
    }
    if (o.checked) {
        o.value = "1";
        $("#divSeletorEndereco").hide();
    } else {
        o.value = "0";
        $("#divSeletorEndereco").show();
    }
}

function change_endereco(o){
    let idEndereco = $("#idEndereco").val();
    let idColab = $("#idColab").val();
    //
    $.post("includes/rh_colab_aj10.php",{idColab: idColab, idEndereco: idEndereco},function(resposta){
    });
}

function add_endereco(){
    $.post("includes/rh_colab_aj6.php",{},function( resposta ){
        //alert( resposta );
        $("#seletor_tipo_endereco").html( resposta );
    });
    add_Modal_Endereco.show();
}

function busca_cep(o) {
    var texto = o.value;
    texto = texto.replace(/[^\d]+/g, '');
    o.value = texto;
    //
    if (texto == '') return false;
    if (texto.length == 8) {
        //- busca o cep
        const url = "https://viacep.com.br/ws/" + texto + "/json/";
        $.get(url, function (data) {
            console.log(data);
            document.getElementById('logradouro').value = data.logradouro;
            document.getElementById('bairro').value = data.bairro;
            document.getElementById('cidade').value = data.localidade;
            document.getElementById('uf').value = data.uf;
            document.getElementById('numero').focus();
        });
        //
        o.value = texto.replace(/(\d{5})(\d{3})/, "$1-$2");
        return true;
    }
    alert("Número de dígitos inválido!");
    o.value = '';
    o.focus();
    return false;
}

function add_endereco_commit(){
    let idTipoEndereco = $("#idTipoEndereco");
    let cep = $("#cep");
    let logradouro = $("#logradouro");
    let numero = $("#numero");
    let bairro = $("#bairro");
    let cidade = $("#cidade");
    let uf = $("#uf");

    //
    if (idTipoEndereco.val() == 0) {
        alert("Selecione um tipo de endereço!");
        idTipoEndereco.focus();
        return false;
    }
    if (cep.val() == "") {
        alert("Informe o CEP do Endereço!");
        cep.focus();
        return false;
    }
    if (logradouro.val() == "") {
        alert("Informe o logradouro!");
        logradouro.focus();
        return false;
    }
    if (numero.val() == "") {
        alert("Informe o número!");
        numero.focus();
        return false;
    }
    if (bairro.val() == "") {
        alert("Informe o bairro!");
        bairro.focus();
        return false;
    }
    if (cidade.val() == "") {
        alert("Informe o cidade do Endereço!");
        cidade.focus();
        return false;
    }
    if (uf.val() == "") {
        alert("Informe Estado (UF)!");
        uf.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formEndereco"));
    formData.append("idPessoa", $("#idPessoa").val());
    formData.append("idColab", $("#idColab").val());
    //
    $("#botoesModalEndereco").hide();
    $("#msgEndereco").html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "includes/rh_colab_aj7.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let d = JSON.parse(response);
            $("#msgEndereco").html(d.msg);
            setTimeout(() => {
                add_Modal_Funcao.hide();
                $("#msgEndereco").html("");
                add_Modal_Endereco.hide();
                //
            }, 3000); // Ajuste o tempo conforme necessário
            //
            if (d.dados && d.dados.idEndereco) {
                let option = new Option(d.dados.dsEndereco, d.dados.idEndereco, true, true);
                $("#idEndereco").append(option).trigger('change');
            }            
        },
        error: function (xhr) {
            $("#msgEndereco").html("Erro ao enviar o formulário: " + xhr.responseText);
        }
    });
}

function salvar_cartoes(){
    //
    let mensagem = $("#msgAlertaCartoes");
    let idColab = $("#idColab").val();
    let vale_refeicao = $("#vale_refeicao").val();
    let vale_transporte = $("#vale_transporte").val();
    let plano_saude = $("#idPlanoSaude").val();
    let plano_odonto = $("#idPlanoOdonto").val();
    //
    if( idColab==0 || idColab==''){
        alert( "Preencha e salve o quadro esquerdo com os dados do colaborador");
        $("#nmPessoa").focus();
        return false;
    }
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.post("includes/rh_colab_aj8.php",{
        idColab: idColab,
        vale_transporte: vale_transporte,
        vale_refeicao: vale_refeicao,
        idPlanoSaude: plano_saude,
        idPlanoOdonto: plano_odonto
    },function( resposta ){
        let dados = JSON.parse( resposta )
        mensagem.html( dados.msg );
        setTimeout(() => {
            mensagem.html("");
        }, 3000); // Ajuste o tempo conforme necessário
    });

}

function atualiza_enderecos( idPessoa ){
    let seletor = $("#seletorEndereco");
    $.post("includes/rh_colab_aj9.php",{ idPessoa: idPessoa}, function(resposta){
        seletor.html( resposta );
    });
}

function add_dependente(){
    let idColab = $("#idColab").val();
    if( idColab == 0 || idColab == ''){
        alert( "Preencha e salve o formulário da esquerda antes");
        $("#nmPessoa").focus();
        o.checked = true;
        o.value = "1";
        return false;
    }
    add_Modal_Dependente.show();
}

function f_add_dependente_commit(){
    alert("commitando o dependente");
}

