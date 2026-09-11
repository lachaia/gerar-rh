//***********************************************************************************************************//
//***********************************************************************************************************//
//*****************************                                         *************************************//
//*****************************        C U R R Í C U L O                *************************************//
//*****************************                                         *************************************//
//***********************************************************************************************************//
//***********************************************************************************************************//

const fa_Modal_Ver = new bootstrap.Modal(document.getElementById("faModalVer"));
const fa_Modal_Inc = new bootstrap.Modal(document.getElementById("faModalInc"));
const fa_Modal_Alt = new bootstrap.Modal(document.getElementById("faModalAlt"));
const fa_Modal_IncIE = new bootstrap.Modal(document.getElementById("faModalIncIE"));

const exp_Modal_Ver = new bootstrap.Modal(document.getElementById("expModalVer"));
const exp_Modal_Inc = new bootstrap.Modal(document.getElementById("expModalInc"));
const exp_Modal_Alt = new bootstrap.Modal(document.getElementById("expModalAlt"));

const idi_Modal_Inc = new bootstrap.Modal(document.getElementById("idiModalInc"));
const idi_Modal_Alt = new bootstrap.Modal(document.getElementById("idiModalAlt"));

const con_Modal_Ver = new bootstrap.Modal(document.getElementById("conModalVer"));
const con_Modal_Inc = new bootstrap.Modal(document.getElementById("conModalInc"));
const con_Modal_Alt = new bootstrap.Modal(document.getElementById("conModalAlt"));


$(document).ready(function () {

    $('#formIncExp #descricao, #formAltExp #descricao, #formIncCon #descricao, #formAltCon #descricao').summernote({
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
                $('.note-editable').css({
                    'color': 'black',
                    'background-color': 'white'
                });
            }
        }
    });

    if ($("#deficiente").val() == 1) $("#deficiencia-bloco").show();

    $("#d_cidade").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "../includes/buscar_cidades.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.nome, // O que aparece para o usuário
                            value: item.nome, // O que preenche o input
                            id: item.id       // ID da cidade
                        };
                    }));
                }
            });
        },
        minLength: 2, // Busca após 2 caracteres
        select: function (event, ui) {
            $("#d_cidade_id").val(ui.item.id); // Salva o ID no campo oculto
            troggle_on_btn_diversidade();
        }
    });

    btnResetDiversidade();

    loadSkills();

    $("#_nome, #afa_nome").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "../includes/buscar_colab.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idColab: item.idColab,  // <- chave personalizada
                            idPessoa: item.idPessoa  // <- chave personalizada
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            $("#_idColab").val(ui.item.idColab); // <- usa a mesma chave
            $("#_idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
            //
            $("#afa_idColab").val(ui.item.idColab); // <- usa a mesma chave
            $("#afa_idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
            //
            //atualiza_enderecos( ui.item.idPessoa );
        }
    });

});

function mostra_cv(idPessoa, arquivo) {
    let url = "../docs_view.php?pessoa=" + idPessoa + "&arquivo=" + encodeURIComponent(arquivo);
    window.open(url, "_blank");
}

function mostrar_certificado(url) {
    window.open(url, "_blank");
}

$('.modal').on('hide.bs.modal', function () {
    document.activeElement.blur();
});


//-----------------------------------------------------
// BLOCO: FORMAÇÃO ACADÊMICA
//

function fa_ver(id) {
    fa_Modal_Ver.show();
    $.post("../includes/rh_cv_fa_aj1.php", { id: id }, function (retorno) {
        //
        let dados = JSON.parse(retorno);
        //
        let arquivo = "não informado";
        if (dados.arquivo.length > 0) {
            let url = dados.url.replace(/'/g, "\\'"); // evita quebra no JS
            let link = ` <a href="#" onclick="mostrar_certificado('${url}')"><i class="fa-solid fa-magnifying-glass"></i></a>`;
            arquivo = dados.arquivo + link;
        }
        //
        $("#v_curso").html(dados.curso)
        $("#v_instituicao").html(dados.nmInstituicao)
        $("#v_sigla").html(dados.sigla)
        $("#v_nivel").html(dados.nivel)
        $("#v_ano_conclusao").html(dados.ano_conclusao)
        $("#divQuando").html(dados.dtLogin + " - " + dados.login)
        $("#fa_arquivo").html(arquivo);
    });
}

function fa_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#msgAlertaPessoa").show();
        $.post("../includes/rh_cv_fa_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#msgAlertaPessoa").html(dados.msg);
            //
            setTimeout(function () {
                $("#msgAlertaPessoa").html("");
                document.location.reload(true);
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function fa_editar(id) {
    fa_Modal_Alt.show();
    $.post("../includes/rh_cv_fa_aj1.php", { id: id }, function (retorno) {
        let dados = JSON.parse(retorno);
        $("#formAltFA #idCurso").val(dados.id)
        $("#formAltFA #curso").val(dados.curso)
        $("#formAltFA #idInstituicao").val(dados.idInstituicao)
        $("#formAltFA #idNivel").val(dados.idNivel)
        $("#formAltFA #ano").val(dados.ano_conclusao)
    });
}

function fa_editar_salva() {
    let mensagem = $("#msgformAltFA");
    //
    let curso = $("#formAltFA #curso");
    let idInstituicao = $("#formAltFA #idInstituicao");
    let idNivel = $("#formAltFA #idNivel");
    let ano = $("#formAltFA #ano");
    //
    if (curso.val() == '') {
        alert("Informe o nome do curso");
        curso.focus();
        return false;
    }
    //
    if (idInstituicao.val() == 0) {
        alert("Informe a IE do curso");
        idInstituicao.focus();
        return false;
    }
    //
    if (idNivel.val() == 0) {
        alert("Informe o Nível do curso");
        idNivel.focus();
        return false;
    }
    //
    if (ano.val() == 0) {
        alert("Informe o ano de conclusão curso");
        ano.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formAltFA"));
    $("#divBotoesFAAlt").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_fa_aj5.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesFAAlt").show();
                mensagem.html("");
                fa_Modal_Alt.hide();
                document.location.reload(true);
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

function fa_incluir() {
    fa_Modal_Inc.show();
}

function fa_incluir_salva() {
    let mensagem = $("#msgformIncFA");
    //
    let curso = $("#formIncFA #curso");
    let idInstituicao = $("#formIncFA #idInstituicao");
    let idNivel = $("#formIncFA #idNivel");
    let ano = $("#formIncFA #ano");
    //
    if (curso.val() == '') {
        alert("Informe o nome do curso");
        curso.focus();
        return false;
    }
    //
    if (idInstituicao.val() == 0) {
        alert("Informe a IE do curso");
        idInstituicao.focus();
        return false;
    }
    //
    if (idNivel.val() == 0) {
        alert("Informe o Nível do curso");
        idNivel.focus();
        return false;
    }
    //
    if (ano.val() == 0) {
        alert("Informe o ano de conclusão curso");
        ano.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formIncFA"));
    $("#divBotoesFA").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_fa_aj3.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesFA").show();
                mensagem.html("");
                fa_Modal_Inc.hide();
                btnResetFA.click();
                document.location.reload(true);
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

//----------------------------------------------------
// INSTITUIÇÃO DE ENSINO
//
function f_inclui_ie() {
    // Exibe a modal
    fa_Modal_IncIE.show();

    // Configura o foco após 500ms (quando a modal estiver totalmente visível)
    $('#fa_Modal_IncIE').on('shown.bs.modal', function () {
        setTimeout(function () {
            $("#_nomeInstituicao").focus();
        }, 3000);
    });
}

function f_inclui_ie_salva() {
    //
    let mensagem = $("#msgformIncFAIE");
    //
    let nome = $("#formIncFAIE #_nomeInstituicao");
    let sigla = $("#formIncFAIE #_sigla");
    let idNivel = $("#formIncFAIE #idNivel");
    let cidade = $("#formIncFAIE #_cidade");
    let uf = $("#formIncFAIE #uf");
    let pais = $("#formIncFAIE #_pais");
    //
    if (nome.val() == '') {
        alert("Informe o nome da Instituição");
        nome.focus();
        return false;
    }
    //
    if (sigla.val() == '') {
        alert("Informe a SIGLA da Instituição");
        sigla.focus();
        return false;
    }
    //
    if (idNivel.val() == 0) {
        alert("Informe o nível de ensino");
        idNivel.focus();
        return false;
    }
    //
    if (cidade.val() == '') {
        alert("Informe a Cidade onde está a Instituição");
        cidade.focus();
        return false;
    }
    //
    if (uf.val() == 0) {
        alert("Informe o Estado onde está a Instituição");
        uf.focus();
        return false;
    }
    //
    if (pais.val() == '') {
        alert("Informe o País onde está a Instituição");
        pais.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formIncFAIE"));
    $("#botoes_fa_id").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_fa_aj2.php",
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

//----------------------------------------------------
// EXPERIÊNCIA PROFISSIONAL
//

function exp_ver(id) {
    exp_Modal_Ver.show();
    $.post("../includes/rh_cv_exp_aj1.php", { id: id }, function (retorno) {
        //
        //alert( retorno );
        let dados = JSON.parse(retorno);
        $("#v_empresa").html(dados.empresa)
        $("#v_cargo").html(dados.cargo)
        $("#v_ano_ini").html(dados.ano_ini)
        $("#v_ano_fim").html(dados.ano_fim)
        $("#v_descricao").html(dados.descricao)
        $("#v_atual").html(dados.ativo)
        $("#divQuandoExp").html(dados.dtLogin + " - " + dados.login)
    });
}

function exp_incluir() {
    exp_Modal_Inc.show();
}

function exp_atual(o) {
    if (o.checked) {
        o.value = "1";
    } else {
        o.value = "0";
    }
}

function exp_incluir_salva() {
    let mensagem = $("#msgformIncExp");
    //
    let empresa = $("#formIncExp #empresa");
    let cargo = $("#formIncExp #cargo");
    let ano_ini = $("#formIncExp #ano_ini");
    let ano_fim = $("#formIncExp #ano_fim");
    let descricao = $("#formIncExp #descricao");
    //
    if (empresa.val() == '') {
        alert("Informe o nome do empresa");
        empresa.focus();
        return false;
    }
    //
    if (cargo.val() == '') {
        alert("Informe o cargo");
        cargo.focus();
        return false;
    }
    //
    if (ano_ini.val() == 0) {
        alert("Informe o ano inicial da experiência");
        ano_ini.focus();
        return false;
    }
    if (ano_fim.val() == 0) {
        alert("Informe o ano final da experiência");
        ano_fim.focus();
        return false;
    }
    if (descricao.val() == '') {
        alert("Informe a experiência no cargo");
        descricao.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formIncExp"));
    $("#divBotoesExp").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_exp_aj3.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesExp").show();
                mensagem.html("");
                exp_Modal_Inc.hide();
                btnResetExp.click();
                document.location.reload(true);
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

function exp_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#msgAlertaPessoa").show();
        $.post("../includes/rh_cv_exp_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#msgAlertaPessoa").html(dados.msg);
            //
            setTimeout(function () {
                $("#msgAlertaPessoa").html("");
                document.location.reload(true);
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function exp_editar(id) {
    exp_Modal_Alt.show();
    $.post("../includes/rh_cv_exp_aj1.php", { id: id }, function (retorno) {
        //
        //alert( retorno );
        let dados = JSON.parse(retorno);
        $("#formAltExp #idExp").val(dados.id)
        $("#formAltExp #empresa").val(dados.empresa)
        $("#formAltExp #cargo").val(dados.cargo)
        $("#formAltExp #ano_ini").val(dados.ano_ini)
        $("#formAltExp #ano_fim").val(dados.ano_fim)
        $("#formAltExp #ativo").val(dados.ativo)
        $("#formAltExp #ativo").prop("checked", dados.ativo == "1");
        $("#formAltExp #descricao").summernote('code', dados.descricao);
    });
}

function exp_editar_salva() {
    let mensagem = $("#msgformAltExp");
    //
    let empresa = $("#formAltExp #empresa");
    let cargo = $("#formAltExp #cargo");
    let ano_ini = $("#formAltExp #ano_ini");
    let ano_fim = $("#formAltExp #ano_fim");
    let descricao = $("#formAltExp #descricao");
    //
    if (empresa.val() == '') {
        alert("Informe o nome do empresa");
        empresa.focus();
        return false;
    }
    //
    if (cargo.val() == '') {
        alert("Informe o cargo");
        cargo.focus();
        return false;
    }
    //
    if (ano_ini.val() == 0) {
        alert("Informe o ano inicial da experiência");
        ano_ini.focus();
        return false;
    }
    if (ano_fim.val() == 0) {
        alert("Informe o ano final da experiência");
        ano_fim.focus();
        return false;
    }
    if (descricao.val() == '') {
        alert("Informe a experiência no cargo");
        descricao.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formAltExp"));
    $("#divBotoesExpAlt").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_exp_aj5.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesExpAlt").show();
                mensagem.html("");
                fa_Modal_Alt.hide();
                document.location.reload(true);
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

//----------------------------------------------------
// IDIOMAS
//

function idioma_incluir() {
    idi_Modal_Inc.show();
}

function idioma_incluir_salva() {
    let mensagem = $("#msgformIncIdi");
    //
    let idIdioma = $("#formIncIdi #idIdioma");
    let idFluencia = $("#formIncIdi #idFluencia");
    //
    if (idIdioma.val() == 0) {
        alert("Informe a Lingua");
        idIdioma.focus();
        return false;
    }
    //
    if (idFluencia.val() == 0) {
        alert("Informe o grau de fluência nessa lingua");
        idFluencia.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formIncIdi"));
    $("#divBotoesExp").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_idi_aj3.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesIdi").show();
                mensagem.html("");
                idi_Modal_Inc.hide();
                btnResetIdi.click();
                document.location.reload(true);
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

function idioma_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#msgAlertaPessoa").show();
        $.post("../includes/rh_cv_idi_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#msgAlertaPessoa").html(dados.msg);
            //
            setTimeout(function () {
                $("#msgAlertaPessoa").html("");
                document.location.reload(true);
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function idioma_editar(id) {
    idi_Modal_Alt.show();
    $.post("../includes/rh_cv_idi_aj1.php", { id: id }, function (retorno) {
        //
        //alert( retorno );
        let dados = JSON.parse(retorno);
        $("#formAltIdi #idIdi").val(dados.id)
        $("#formAltIdi #idIdioma").val(dados.idIdioma)
        $("#formAltIdi #idFluencia").val(dados.idFluencia)
    });
}

function idioma_editar_salva() {
    let mensagem = $("#msgformAltIdi");
    //
    let idIdioma = $("#formAltIdi #idIdioma");
    let idFluencia = $("#formAltIdi #idFluencia");
    //
    if (idIdioma.val() == 0) {
        alert("Informe a Lingua");
        idIdioma.focus();
        return false;
    }
    //
    if (idFluencia.val() == 0) {
        alert("Informe o grau de fluência nessa lingua");
        idFluencia.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formAltIdi"));
    $("#divBotoesExpAlt").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_idi_aj5.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesIdiAlt").show();
                mensagem.html("");
                fa_Modal_Alt.hide();
                document.location.reload(true);
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

//----------------------------------------------------
// CONQUISTAS & CERTIFICADOS
//

function con_incluir() {
    con_Modal_Inc.show();
}

function btn_reset_con() {
    $("#formIncCon #descricao").summernote('code', '');
}

function con_incluir_salva() {
    let mensagem = $("#msgformIncCon");
    //
    let idConqTipo = $("#formIncCon #idConqTipo");
    let titulo = $("#formIncCon #titulo");
    let ano = $("#formIncCon #ano");
    let descricao = $("#formIncCon #descricao");
    //
    if (idConqTipo.val() == 0) {
        alert("Informe o tipo de conquista/certificado");
        idConqTipo.focus();
        return false;
    }
    //
    if (titulo.val() == '') {
        alert("Informe o título do evento");
        titulo.focus();
        return false;
    }
    //
    if (ano.val() == 0) {
        alert("Informe o ano do evento");
        ano.focus();
        return false;
    }
    if (descricao.val() == '') {
        alert("Descreva o evento");
        descricao.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formIncCon"));
    $("#divBotoesCon").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_con_aj3.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesCon").show();
                mensagem.html("");
                //exp_Modal_Inc.hide();
                //btnResetCon.click();
                document.location.reload(true);
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

function con_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#msgAlertaPessoa").show();
        $.post("../includes/rh_cv_con_aj4.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#msgAlertaPessoa").html(dados.msg);
            //
            setTimeout(function () {
                $("#msgAlertaPessoa").html("");
                document.location.reload(true);
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function con_ver(id) {
    con_Modal_Ver.show();
    $.post("../includes/rh_cv_con_aj1.php", { id: id }, function (retorno) {
        //
        //alert( retorno );
        let dados = JSON.parse(retorno);
        //
        let arquivo = "não informado";

        if (dados.arquivo.length > 0) {
            let url = dados.url.replace(/'/g, "\\'"); // evita quebra no JS
            let link = ` <a href="#" onclick="mostrar_certificado('${url}')"><i class="fa-solid fa-magnifying-glass"></i></a>`;
            arquivo = dados.arquivo + link;
        }
        //
        $("#v_tipo").html(dados.dsTipo)
        $("#conModalVer #v_ano").html(dados.ano)
        $("#v_titulo").html(dados.titulo)
        $("#conModalVer #v_descricao").html(dados.descricao)
        $("#divQuandoCon").html(dados.dtLogin + " - " + dados.login)
        $("#v_arquivo").html(arquivo);
    });
}

function con_editar(id) {
    con_Modal_Alt.show();
    $.post("../includes/rh_cv_con_aj1.php", { id: id }, function (retorno) {
        //
        //alert( retorno );
        let dados = JSON.parse(retorno);
        $("#formAltCon #idConq").val(dados.id)
        $("#formAltCon #idConqTipo").val(dados.idConqTipo)
        $("#formAltCon #ano").val(dados.ano)
        $("#formAltCon #titulo").val(dados.titulo)
        $("#formAltCon #descricao").summernote('code', dados.descricao);
    });
}

function btn_reset_con_alt() {
    let id = $("#formAltCon #idConq").val();
    $.post("../includes/rh_cv_con_aj1.php", { id: id }, function (retorno) {
        //
        let dados = JSON.parse(retorno);
        $("#formAltCon #idConq").val(dados.id)
        $("#formAltCon #idConqTipo").val(dados.idConqTipo)
        $("#formAltCon #ano").val(dados.ano)
        $("#formAltCon #titulo").val(dados.titulo)
        $("#formAltCon #descricao").summernote('code', dados.descricao);
    });
}

function con_editar_salva() {
    let mensagem = $("#msgformAltCon");
    //
    let idConqTipo = $("#formAltCon #idConqTipo");
    let titulo = $("#formAltCon #titulo");
    let ano = $("#formAltCon #ano");
    let descricao = $("#formAltCon #descricao");
    //
    if (idConqTipo.val() == 0) {
        alert("Informe o tipo de conquista/certificado");
        idConqTipo.focus();
        return false;
    }
    //
    if (titulo.val() == '') {
        alert("Informe o título do evento");
        titulo.focus();
        return false;
    }
    //
    if (ano.val() == 0) {
        alert("Informe o ano do evento");
        ano.focus();
        return false;
    }
    if (descricao.val() == '') {
        alert("Descreva o evento");
        descricao.focus();
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formAltCon"));
    $("#divBotoesExpAlt").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_con_aj5.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#divBotoesConAlt").show();
                mensagem.html("");
                //con_Modal_Alt.hide();
                document.location.reload(true);
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

//-----------------------------------------------------
// BLOCO: DADOS PESSOAIS
//

function toggleDeficiencia(o) {
    var bloco = document.getElementById("deficiencia-bloco");
    var seletor = document.getElementById("selectDeficiencia");

    if (o.checked) {
        bloco.classList.add("show"); // Mostra o bloco
        o.setAttribute("value", "1"); // Define o valor para 1
        seletor.classList.add("collapse");
    } else {
        bloco.classList.remove("show"); // Oculta o bloco
        o.setAttribute("value", "0"); // Define o valor para 0
    }

    showBtnCV();
}

function toggleNaoDeficiencia(o) {
    var bloco = document.getElementById("deficiencia-bloco");
    var seletor = document.getElementById("selectDeficiencia");

    var fisica = document.getElementById("fisica");
    var visual = document.getElementById("visual");
    var auditiva = document.getElementById("auditiva");
    var mental = document.getElementById("mental");
    var intelectual = document.getElementById("intelectual");
    var autista = document.getElementById("autista");
    var naoTenho = document.getElementById("naoTenhoDeficiencia");
    var deficiente = document.getElementById("deficiente");

    if (o.checked) {
        // Oculta o bloco
        bloco.classList.remove("show");
        seletor.classList.remove("collapse");

        // Desmarca todas as deficiências e zera o valor
        [deficiente, fisica, visual, auditiva, mental, intelectual, autista, naoTenho ].forEach(el => {
            if (el) {
                el.checked = false;
                el.value = 0;
            }
        });
    }
}

function toggleDefValue(o) {
    if (o.value == 0) {
        o.value = 1;
    } else {
        o.value = 0
    }
}

function showBtnCV() {
    let botoes = $("#btnSalvaCV");
    botoes.show();
}

const radios = document.querySelectorAll('input[name="genero"]');

// Adiciona o evento 'change' a cada input de rádio
radios.forEach(radio => {
    radio.addEventListener('change', function () {
        // Chama a função togleBtnSalvarCV quando qualquer radio for alterado
        showBtnCV();
    });
});

function btnResetBloco1() {
    let idPessoa = $("#formIncFA #idPessoa").val();
    $.post("../includes/rh_cv_aj0.php", { idPessoa: idPessoa }, function (retorno) {
        //alert( retorno )
        let d = JSON.parse(retorno);
        //
        $("input[name='genero'][value='" + d.dados.genero + "']").prop("checked", true);
        //
        if (d.dados.deficiente == 1) $("#deficiente").prop("checked", true).val(1);
        if (d.dados.def_fisica == 1) $("#fisica").prop("checked", true).val(1);
        if (d.dados.def_visual == 1) $("#visual").prop("checked", true).val(1);
        if (d.dados.def_auditiva == 1) $("#auditiva").prop("checked", true).val(1);
        if (d.dados.def_mental == 1) $("#mental").prop("checked", true).val(1);
        if (d.dados.def_intelectual == 1) $("#intelectual").prop("checked", true).val(1);
        if (d.dados.def_autista == 1) $("#autista").prop("checked", true).val(1);
        if (d.dados.def_autista == 1) $("#linkedin").prop("checked", true).val(1);
        //
        $("#cid").val(d.dados.cid);
        $("#linkedin").val(d.dados.linkedin);
        //
        $("#btnSalvaCV").hide();
    });
}

function salvar_cv() {
    let mensagem = $("#msgformCV");
    //
    if (validarGenero() == false) {
        return false;
    }
    if (validarDeficiencia() == false) {
        return false;
    }
    //
    let formData = new FormData(document.getElementById("formCV"));
    $("#btnSalvaCV").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_aj1.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#btnSalvaCV").show();
                mensagem.html("");
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

function validarGenero() {
    // Verifica se algum radio button com name="genero" está marcado
    let selecionado = $("input[name='genero']:checked").length > 0;

    if (!selecionado) {
        alert("Por favor, selecione uma opção de gênero.");
        return false; // Impede a continuação da função de salvamento
    }
    return true; // Permite o salvamento
}

function validarDeficiencia() {
    let deficienteMarcado = $("#deficiente").prop("checked");

    if (deficienteMarcado) {
        // Verifica se pelo menos um dos checkboxes de deficiência está marcado
        let algumSelecionado = $("input[name='fisica']:checked, input[name='visual']:checked, input[name='auditiva']:checked, input[name='mental']:checked, input[name='intelectual']:checked, input[name='autista']:checked").length > 0;

        // Verifica se o campo CID está preenchido
        let cidPreenchido = $("#cid").val().trim() !== "";

        if (!algumSelecionado) {
            alert("Selecione pelo menos um tipo de deficiência.");
            return false;
        }

        if (!cidPreenchido) {
            alert("Preencha o campo CID.");
            return false;
        }
    }

    return true; // Permite continuar se tudo estiver correto
}

//---------------------------------------------------------------
// BLOCO 2 - DIVERSIDADE
//

function salvar_cv2() {
    let mensagem = $("#msgformDiversidade");
    //
    let formData = new FormData(document.getElementById("formDiversidade"));
    $("#btnDiversidade").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    $.ajax({
        url: "../includes/rh_cv_aj2.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                mensagem.html("");
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

function troggle_on_btn_diversidade() {
    $("#btnDiversidade").show()
}

function btnResetDiversidade() {
    let idPessoa = $("#formDiversidade #idPessoa").val();
    $.post("../includes/rh_cv_aj0.php", { idPessoa: idPessoa }, function (retorno) {
        //alert( retorno )
        let d = JSON.parse(retorno);
        //
        if (d.dados.idCidade > 0) {
            $("#d_cidade_id").val(d.dados.idCidade);
            $("#d_cidade").val(d.dados.nmCidade + "/" + d.dados.uf);
        }
        //        
        $("#cor").val(d.dados.cor);
        $("#pronome").val(d.dados.pronome);
        $("#orientacao").val(d.dados.orientacao);
        $("#identgenero").val(d.dados.idGenero);
        //
        $("#btnDiversidade").hide();
    });
}

//---------------------------------------------------
// BLOCO: HABILIDADES (SKILLS)
//

let skills = [];

function addSkill() {
    let skillInput = $("#inputSkill");
    let skill = skillInput.val().trim();

    if (skill === "") return; // Evita adicionar valores vazios

    // Verifica se a habilidade já existe
    let exists = false;
    $("#skillsContainer span").each(function () {
        if ($(this).text().trim() === skill) {
            exists = true;
        }
    });

    if (!exists) {
        addSkillToContainer(skill);
        $("#btnHabilidades").show();
    }

    skillInput.val(""); // Limpa o campo após adicionar
}

function removeSkill(skill) {
    $("#skillsContainer span").each(function () {
        let textoSkill = $(this).text().trim();
        if (textoSkill.startsWith(skill)) { // Verifica se o texto começa com a skill
            $(this).remove(); // Remove o elemento corretamente
        }
    });
    $("#btnHabilidades").show();
}

function updateSkills() {
    let container = document.getElementById("skillsContainer");
    container.innerHTML = "";
    $("#btnHabilidades").show();
    skills.forEach(skill => {
        let span = document.createElement("span");
        span.className = "badge bg-secondary p-2";
        span.innerHTML = `${skill} <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeSkill("${skill}")'></i>`;
        container.appendChild(span);
    });
}

function saveSkills() {
    let mensagem = $('#msgHabilidades');
    //
    let idPessoa = $("#formCV #idPessoa").val(); // ID do currículo
    let skills = [];

    // Verificar se o container de skills existe
    if ($("#skillsContainer").length === 0) {
        alert("Erro: O container de skills NÃO foi encontrado! Verifique o ID no HTML.");
        return;
    }

    // Coletar habilidades dentro do container
    $("#skillsContainer span").each(function () {
        let skillText = $(this).text().trim().replace(" x", "");
        if (skillText !== "") {
            skills.push(skillText);
        }
    });

    if (skills.length === 0) {
        mensagem.html("Nenhuma habilidade foi encontrada!");
        return;
    }

    // Enviar via AJAX
    $.post("../includes/rh_cv_aj3.php", { idPessoa: idPessoa, habilidades: skills }, function (response) {
        mensagem.html(response.msg);
        setTimeout(() => {
            mensagem.html("");
            $("#btnHabilidades").hide();
            //
        }, 3000); // Ajuste o tempo conforme necessário
    });
}

function loadSkills() {
    let mensagem = $('#msgHabilidades');
    let idPessoa = $("#formCV #idPessoa").val();
    if (!idPessoa) return;

    $.ajax({
        url: "../includes/rh_cv_aj4.php",
        type: "POST",
        data: { idPessoa: idPessoa },
        dataType: "json",
        success: function (response) {
            if (response.status) {
                $("#skillsContainer").empty(); // Limpa o container antes de adicionar
                response.habilidades.forEach(function (skill) {
                    addSkillToContainer(skill);
                });
            } else {
                mensagem.html("Nenhuma habilidade encontrada.");
            }
        },
        error: function (xhr) {
            mensagem.html("Erro ao carregar habilidades:", xhr.responseText);
        }
    });
}

// Adiciona a skill ao container com botão de remoção
function addSkillToContainer(skill) {
    let span = document.createElement("span");
    span.className = "badge bg-primary p-2";
    span.innerHTML = `${skill} <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeSkill("${skill}")'></i>`;
    document.querySelector("#skillsContainer").appendChild(span);
    //
    //$("#btnHabilidades").show();
}

//- ENVIO DE CV (ARQUIVO)
//
function envia_arquivoCV() {
    //
    let arquivo = $("#cv_arquivo")[0].files[0]; // Obtém o arquivo selecionado
    if (!arquivo) {
        $("#mensagemUpload").html("<div class='alert alert-warning'>Por favor, selecione um arquivo.</div>");
        return;
    }

    let formData = new FormData();
    formData.append("arquivo", arquivo); // Adiciona o arquivo ao FormData
    formData.append("idPessoa", $("#formCV #idPessoa").val()); // ID da pessoa no formulário

    $.ajax({
        url: "../includes/rh_cv_aj5.php",
        type: "POST",
        data: formData,
        processData: false,  // Necessário para envio de arquivos
        contentType: false,  // Necessário para envio de arquivos
        beforeSend: function () {
            $("#mensagemUpload").html("<div class='alert alert-info'>Enviando arquivo...</div>");
        },
        success: function (response) {
            let dados = JSON.parse(response);
            let msg = "";
            try {
                if (dados.status) {
                    msg = "<div class='alert alert-success'>Arquivo enviado com sucesso!</div>";
                } else {
                    msg = "<div class='alert alert-danger'>Erro ao enviar o arquivo: " + dados.msg + "</div>";
                }
            } catch (e) {
                console.error("Erro ao processar JSON:", e, response); // Exibe detalhes do erro no console
                msg = "<div class='alert alert-danger'>Erro inesperado no envio: " + e.message + "</div>";
            }
            $("#mensagemUpload").html(msg);
            setTimeout(() => {
                let url = '<i class="fa-regular fa-folder-open"></i> <b>Arquivo de CV: </b>' + dados.arquivo +
                    ' <a href="#" onclick="mostra_cv(' + dados.idPessoa + ', \'' + dados.arquivo + '\')">' +
                    '<i class="fa-solid fa-magnifying-glass"></i></a>';
                $("#mensagemUpload").html(url);
            }, 3000);
        },
        error: function () {
            $("#mensagemUpload").html("<div class='alert alert-danger'>Erro na requisição AJAX.</div>");
        }
    });
}
//---------------------------------------------------

function mostra_cv(idPessoa, arquivo) {
    let url = "../docs_view.php?pessoa=" + idPessoa + "&arquivo=" + encodeURIComponent(arquivo);
    window.open(url, "_blank");
}

function mostrar_certificado(url) {
    window.open(url, "_blank");
}

$('.modal').on('hide.bs.modal', function () {
    document.activeElement.blur();
});

function validarAgenda(idCampo, nomeParcela, dtLimite) {
    let valor = document.getElementById(idCampo).value;
    if (!valor) return true; // campo vazio, nada a validar

    let dataAgenda = new Date(valor);
    let dataLimite = new Date(dtLimite);

    // hoje + 30 dias
    let hoje = new Date();
    let dataMinima = new Date();
    dataMinima.setDate(hoje.getDate() + 30);

    // Sexta-feira = 5
    if (dataAgenda.getDay() === 5) {
        alert(`A data da ${nomeParcela} não pode cair em uma sexta-feira!`);
        document.getElementById(idCampo).value = '';
        document.getElementById(idCampo).focus();
        return false;
    }

    if (dataAgenda < dataMinima) {
        alert(`A data da ${nomeParcela} deve ser ao menos 30 dias após hoje!`);
        document.getElementById(idCampo).value = '';
        document.getElementById(idCampo).focus();
        return false;
    }

    if (dataAgenda > dataLimite) {
        alert(`A data da ${nomeParcela} ultrapassa o limite permitido!\nFavor dirigir-se ao RH para mais informações.`);
        document.getElementById(idCampo).value = '';
        document.getElementById(idCampo).focus();
        return false;
    }

    return true;
}

function dias(o) {
    let d1 = parseInt($("#e_dias1").val()) || 0;
    let d2 = parseInt($("#e_dias2").val()) || 0;
    let d3 = parseInt($("#e_dias3").val()) || 0;

    let total = d1 + d2 + d3;

    if (total > 30) {
        alert("ERRO: excede 30 dias");
        o.value = 0;
        o.focus();
        return;
    }

    if (d1 === 30) {
        $("#e_dias2, #e_dias3, #e_agenda2, #e_agenda3")
            .val("")
            .prop("disabled", true)
            .css("background-color", "#e9ecef"); // cinza claro (similar ao Bootstrap disabled)
    } else {
        $("#e_dias2, #e_dias3, #e_agenda2, #e_agenda3")
            .prop("disabled", false)
            .css("background-color", ""); // volta ao padrão
    }
}