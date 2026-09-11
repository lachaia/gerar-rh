//
//-- rh_docs.js | Rotinas JavaScript 
//-- (C)haia, 06/03/24
//-- Modulo = 14; // RH-GED 

var dataTable = "";
const docModalMostra = new bootstrap.Modal(document.getElementById("modalMostraDocumento"));
const docModalIncluir = new bootstrap.Modal(document.getElementById("modalIncluirDocumento"));
const docModalEditar = new bootstrap.Modal(document.getElementById("modalEditarDocumento"));
const docModalEmail = new bootstrap.Modal(document.getElementById("modalEnviarEmail"));

$(document).ready(function () {
    //$("#sidebarToggle").click();
    constroiTabela();
    //
    $('textarea#corpoEmail').summernote({
        height: 200,
        lang: 'pt-br',
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
                $('.note-editable').css('color', 'black');
            }
        }
    });

    $("#formAnexo #nmPessoa, #formEdit #nmPessoa").autocomplete({
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
            $("#formAnexo #idPessoa, #formEdit #idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
            //atualiza_enderecos( ui.item.idPessoa );
        }
    });

});

function reconstroi_tabela() {
    // Destrua a tabela DataTable existente se ela já estiver criada
    if (dataTable) {
        dataTable.clear();
        dataTable.destroy();
    }
    constroiTabela();
}

function constroiTabela() {
    //
    const idTipo = $("#idTipo").val();
    const busca = $("#buscaInterna").val();
    dataTable = new DataTable('#example', {
        dom: 'lrtip',
        "processing": true,
        "serverSide": false,
        "scrollX": false,
        "pageLength": 10,
        "order": [
            [0, "desc"]
        ],
        "ajax": {
            "url": "rh_docs_aj.php",
            "type": "POST",
            data: function (d) {
                d.busca = busca,
                    d.idTipo = idTipo,
                    d.idPessoa = $("#x_idPessoa").val()
            }
        },
        "columnDefs": [
            { "visible": false, "targets": [] },
            {
                "targets": [0,1,6], // Centraliza as colunas 0 e 5 (coluna 6)
                "className": "text-center"
            },
            {
                "targets": 7, // Alinha a coluna 6 (índice 6) à direita
                "className": "text-end" // ou use "dt-body-right"
            }
        ],
        language: {
            url: 'includes/pt-br.json',
        },
    });
    $("#buscaInterna").val("");
}

function f_ver(id) {
    //
    //- Mostra Modal com dados do documento
    //
    docModalMostra.show();
    $("#divVisOCR").hide();
    $("#divModalVisDoc").show();

    limpa_tudo();
    $.ajax({
        url: 'includes/rh_docs_vis_aj.php',
        type: 'post',
        data: {
            idDoc: id
        }, // Parâmetros para a consulta
        dataType: 'json',
        success: function (retorno) {
            let arquivo = retorno.arquivo;
            //alert(arquivo)
            // Preenche as divs com os dados recebidos
            $("#_idPessoa").val(retorno.idPessoa);
            $("#_tipoArquivo").val(retorno.extensao);
            $("#_nomeArquivo").val(arquivo);
            $("#_origem").val(retorno.origem);
            $("#vis_data").html(retorno.data);
            $("#divVisTipoDoc").html(retorno.dsTipo);
            $("#divVisID").html(retorno.idDoc);
            $("#divVisNomeOri").html(retorno.nome_original);
            $("#divVisNomeFis").html(retorno.arquivo);
            $("#vis_doc_descricao").html(retorno.descricao);
            $("#vis_tags").html(retorno.tags);
            $("#vis_ocr").html(retorno.ocr);
            $("#vis_nome").html(retorno.nome);
            //
            //document.getElementById("_nomeArquivo").value = retorno.nome_arquivo;
            //document.getElementById("_tipoArquivo").value = retorno.tipo_arquivo;
            document.getElementById("divInseridoPor").innerHTML = retorno.inseridoPor;
        },
        error: function () {
            document.getElementById("vis_status").innerHTML = "<spam class='text-danger'>Não conseguiu trazer os dados</spam>";
            //console.error(  );
        }
    });
}

function limpa_tudo() {
    $("#vis_data").html("");
    $("#divVisTipoDoc").html("");
    $("#divVisNomeOri").html("");
    $("#divVisNomeFis").html("");
    $("#vis_doc_descricao").html("");
    $("#vis_tags").html("");
    $("#vis_ocr").html("");
    //
    document.getElementById("_nomeArquivo").value = "";
    document.getElementById("_tipoArquivo").value = "";
    document.getElementById("divInseridoPor").innerHTML = "";
}

function mostrar_div_ocr() {
    $("#divModalVisDoc").hide();
    $("#divVisOCR").show();
}

function mostrar_div_documento() {
    $("#divVisOCR").hide();
    $("#divModalVisDoc").show();
}

function visualizar_documento() {
    //
    //-- VISUALIZA O DOCUMENTO ANEXADO
    //

    let idPessoa = $("#_idPessoa").val();
    let origem = $("#_origem").val();
    const documento = $("#_nomeArquivo").val();
    const tipo = $("#_tipoArquivo").val();

    //- IDENTIFICA A ORIGEM DO DOCUMENTO X PATH DE ACESSO
    // GED - GED            | docs/pessoa_?/
    // CIP - CIPA           | docs/CIPA/        | Reuniões da CIPA
    // GIP - CIPA           | docs/CIPA/        | Ged da CIPA    
    // GRI - Brigada        | docs/brigada/     | Ged da Brigada
    // BRI - Brigada        | docs/brigada/     | Reuniões da Brigada
    // TRM - Termos         | docs/pessoa_?/    | Termos de Responsabilidades
    // AFA - Afastamentos   | docs/pessoa_?/    | Atestados Médicos
    // CTR - Contrato Exp.  | docs/pessoa_?/    | Contrato de Trabalho de Experiência
    //

    let diretorio = 'docs/pessoa_' + idPessoa + '/';
    if (origem == 'CIP' || origem == 'GIP') {
        diretorio = 'docs/CIPA/';
    }
    if (origem == 'GRI' || origem == 'BRI') {
        diretorio = 'docs/brigada/';
    }

    if (tipo == 'docx' || tipo == 'xlsx' || tipo == 'pptx') {
        //var caminhoDocumento = 'https://rh.gerar.org.br/docs/pessoa_' + idPessoa + "/" + documento;
        var caminhoDocumento = 'https://rh.gerar.org.br/'+ diretorio + documento;
        var urlGoogleDocsViewer = 'https://docs.google.com/viewer?url=' + caminhoDocumento;
        window.open(urlGoogleDocsViewer, '_blank', 'width=600,height=600');
        return true;
    }

    const tiposPermitidos = ['pdf', 'gif', 'tiff', 'tif', 'jpeg', 'jpg', 'png', 'bmp', 'webp'];
    if (tiposPermitidos.includes(tipo)) {
        //const caminho = "docs/pessoa_" + idPessoa + "/" + documento;
        const caminho = diretorio + documento;
        window.open(caminho, '_blank', 'width=1024,height=800');
        return true;
    }
    alert("Tipo de Arquivo inelegível para leitura!");
}

function incluir() {
    docModalIncluir.show();
}

function incluir_commit() {
    //
    //- VALIDAÇÃO DOS CAMPOS
    //
    const formulario = document.getElementById('formAnexo');
    var inputFile = document.getElementById('doc_arquivo');
    const dataDoc = document.getElementById('dataDoc');
    const campo_ocr = document.getElementById('inc_ocr').value;
    const paginas = $("#inc_paginas");

    const idTipoDoc = $('#idTipoDoc');
    const dsTipoDoc = idTipoDoc.find(":selected").text();

    if (dataDoc.value == 0) {
        alert("Favor informar a Data do Documento...");
        dataDoc.focus();
        return false;
    }
    if (idTipoDoc.val() == 0) {
        alert("Favor selecionar o tipo do documento...");
        idTipoDoc.focus();
        return false;
    }
    if (inputFile.files.length == 0) {
        alert("Favor selecionar um arquivo para upload...");
        inputFile.focus();
        return false;
    }
    //
    $("#divBotoesIncluiDoc").hide();
    $("#msgIncluiDoc").html("Aguarde! Processando " + paginas.val() + " páginas ");
    $("#msgIncluiDoc").show();
    //
    var dados = new FormData(formulario);
    dados.append('dsTipoDoc', dsTipoDoc);
    dados.append('inc_ocr', campo_ocr);
    // 
    $.ajax({
        method: "POST",
        url: "includes/rh_docs_inc_aj.php",
        data: dados,
        contentType: false,
        processData: false,
        success: function (retorno) {
            console.log(retorno);
            const dados = JSON.parse(retorno);
            $("#msgIncluiDoc").html(dados.msg);
            $("#msgIncluiDoc").show();
            const myTimeout = setTimeout(function () {
                if (dados.status == false) {
                    $("#divBotoesIncluiDoc").show();
                    $("#msgIncluiDoc").hide();
                    $("#msgIncluiDoc").html("");
                } else {
                    location.href = "rh_docs.php";
                }
            }, 3000);
            return false;
        }
    });
}

function adicionarNovoTipo( formulario ) {
    let texto = prompt("Digite o nome do novo tipo de documento:");

    if (!texto || texto.trim() === "") {
        return; // usuário cancelou ou não digitou nada
    }

    $.post("includes/rh_docs_aj1.php", { nome: texto.trim() }, function (res) {
        let dados = JSON.parse( res );
        let url = "#" + formulario + " #idTipo";
        let o = $(url); 
        if (dados.status == 1) {
            let option = new Option(dados.nome + " (0)", dados.id, true, true);
            o.append(option).val(dados.id);
        } else {
            alert("Erro: " + dados.mensagem);
        }
    });
}

function f_exclui(id, arquivo) {
    if (confirm("Confirma a Exclusão do Arquivo  " + arquivo + " ?") == true) {
        $.post("includes/rh_docs_del_aj.php", { id: id, arquivo: arquivo },
            function (retorno, status) {
                const dados = JSON.parse(retorno);
                document.getElementById("msgAlerta").innerHTML = dados.msg;
                const myTimeout = setTimeout(function () {
                    if (dados.status == true) {
                        reconstroi_tabela()
                    }
                    document.getElementById("msgAlerta").innerHTML = "";
                }, 3000);
            });
    }
}

function f_edita(id) {
    //
    const dataDoc   = $("#ed_dataDoc");
    const tipoDoc   = $("#formEdit #idTipo");
    const tags      = $("#formEdit #tags");
    const descricao = $("#formEdit #doc_descricao");
    const idDoc     = $("#formEdit #idDoc");
    const campo_ocr = $('#alt_ocr');
    const arquivo   = $("#alt_dsArquivo");
    const idPessoa  = $("#formEdit #idPessoa");
    const nmPessoa  = $("#formEdit #nmPessoa");
    //
    idDoc.val(id);
    //
    $.post("includes/rh_docs_aj5.php", { idDoc: id }, function (retorno) {
        dataDoc.val(retorno.data);
        tipoDoc.val(retorno.idTipoDoc);
        tags.val(retorno.tags);
        descricao.val(retorno.descricao);
        campo_ocr.val(retorno.ocr);
        arquivo.html("Atual: " + retorno.nome_original);
        idPessoa.val(retorno.idPessoa);
        nmPessoa.val(retorno.nmPessoa);
    });
    //
    docModalEditar.show();
}

function edit_commit() {
    const formulario = document.getElementById('formEdit');
    const mensagem = $("#msgaAltDoc");
    const botoes = $("#divBotoesEditaDoc");
    const idTipoDoc = $('#formEdit #idTipoDoc');
    const dsTipoDoc = idTipoDoc.find(":selected").text();
    const campo_ocr = document.getElementById('alt_ocr').value;
    //
    mensagem.html("Aguarde ... ");
    botoes.hide();
    mensagem.show();
    //
    var dados = new FormData(formulario);
    dados.append('dsTipoDoc', dsTipoDoc);
    dados.append("alt_ocr", campo_ocr);
    // 
    $.ajax({
        method: "POST",
        url: "includes/rh_docs_edt_aj.php",
        data: dados,
        contentType: false,
        processData: false,
        success: function (retorno) {
            //console.log(retorno);
            const dados = JSON.parse(retorno);
            mensagem.html(dados.msg);
            //
            const myTimeout = setTimeout(function () {
                if (dados.status == false) {
                    botoes.show();
                    mensagem.hide();
                } else {
                    location.href = "rh_docs.php";
                }
            }, 3000);
            return false;
        }
    });
}

function troca_inc_ocr() {
    const principal = $("#incPrincipal");
    const divOCR = $("#incOCR");
    //
    principal.hide();
    divOCR.show();
}

function troca_inc_principal() {
    const principal = $("#incPrincipal");
    const divOCR = $("#incOCR");
    //
    principal.show();
    divOCR.hide();
}

function troca_alt_ocr() {
    const principal = $("#altPrincipal");
    const divOCR = $("#altOCR");
    //
    principal.hide();
    divOCR.show();
}

function troca_alt_principal() {
    const principal = $("#altPrincipal");
    const divOCR = $("#altOCR");
    //
    principal.show();
    divOCR.hide();
}

function alt_mudou_arquivo(o) {
    const ocr = $("#alt_ocr");
    ocr.val("");
}

async function inc_mudou_arquivo(o) {
    const botoes = $("#divBotoesIncluiDoc");
    const mensagem = $("#msgIncluiDoc");
    const paginas = $("#inc_paginas");
    //
    var file = o.files[0];
    //
    const originalFile = file; // Salva o arquivo original

    if (file.type === "application/pdf") {
        //
        botoes.hide();
        mensagem.html("Aguarde!").show();
        //
        var fileReader = new FileReader();
        fileReader.onload = async function () {
            try {
                var typedarray = new Uint8Array(this.result);
                const pdfDoc = await PDFLib.PDFDocument.load(typedarray);
                const numPages = pdfDoc.getPageCount();
                mensagem.html("Aguarde ... Processando " + numPages + " páginas ");
                paginas.val(numPages);

                if (numPages > 15) {
                    const pagesToExtract = 15;
                    const newPdf = await PDFLib.PDFDocument.create();
                    for (let i = 0; i < pagesToExtract; i++) {
                        mensagem.html("Aguarde ... Extraindo página " + (i + 1));
                        const [copiedPage] = await newPdf.copyPages(pdfDoc, [i]);
                        newPdf.addPage(copiedPage);
                    }

                    const pdfBytes = await newPdf.save();
                    const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                    const newFile = new File([blob], 'fatias.pdf', { type: 'application/pdf' });

                    // Chama a função OCR com o novo arquivo gerado
                    mensagem.html('Realizando OCR das primeiras 15 páginas...');
                    await ocr(newFile);
                } else {
                    // Chama a função OCR com o arquivo original
                    mensagem.html("Aguarde ... Realizando o OCR");
                    await ocr(file);
                }

                // Após o OCR, redefine o campo de arquivo para o arquivo original
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(originalFile);
                document.getElementById('doc_arquivo').files = dataTransfer.files;

            } catch (error) {
                console.error('Erro durante o processamento do PDF:', error);
                mensagem.html("Ocorreu um erro durante o processamento do PDF.");
            } finally {
                mensagem.hide();
                botoes.show();
            }
        };
        fileReader.readAsArrayBuffer(file); // Iniciar a leitura do arquivo
    } else {
        botoes.show();
    }
}

//
//-- Rotinas dos e-Mails
//

function f_email(id) {
    docModalEmail.show();
    $.post("includes/rh_docs_aj4.php", { id: id }, function (result) {
        var dados = JSON.parse(result);
        $('#formEmail #arquivo_original').val( dados['nome_original'] );
        $('#formEmail #arquivo'         ).val( dados['nome_arquivo'] );
        $('#formEmail #_idDoc'          ).val( id );
        $('#formEmail #idPessoa'        ).val( dados['idPessoa'] );
    });
}

function envia_email() {
    var formulario = document.getElementById('formEmail');
    var para = document.getElementById('para');
    var titulo = document.getElementById('titulo');
    var corpoEmail = $('#corpoEmail').summernote('code');

    //
    if (para.value.length == 0) {
        alert("Favor informar o destino do e-mail!");
        para.focus();
        return false;
    }
    if (titulo.value.length == 0) {
        alert("Favor informar o título do e-mail!");
        titulo.focus();
        return false;
    }
    if (corpoEmail.length == 0) {
        alert("Favor informar a Mensagem do e-Mail");
        $('#corpoEmail').summernote('focus');
        return false;
    }
    //
    $("#divBotoesEmail").hide();
    $("#divMensagemEml").show();
    //
    var dados = new FormData(formulario);
    dados.append('mensagem', corpoEmail);
    //
    //
    $.ajax({
        method: "POST",
        url: "includes/rh_docs_eml_aj.php",
        data: dados,
        contentType: false,
        processData: false,
        success: function (retorno) {
            const dados = JSON.parse(retorno);
            $("#divMensagemEml").html(dados.msg);
            const myTimeout = setTimeout(function () {
                $("#divBotoesEmail").show();
                $("#divMensagemEml").hide();
                if (dados.status == true) {
                    location.reload();
                }
            }, 3000);
            return false;

        }
    });
}

async function carregar_emails(valor) {
    if (valor.length >= 3) {
        const dados = await fetch('./includes/pesquisa_emails.php?texto=' + valor);
        const resposta = await dados.json();
        var resultado = "<ul class='list-group position-fixed'>";

        if (resposta['status']) {
            for (i = 0; i < resposta['dados'].length; i++) {
                var nome = resposta['dados'][i].nome;
                var tipo = resposta['dados'][i].tipo;
                var email = resposta['dados'][i].email;
                //
                link = 'atualiza_email("' + nome + '", "' + tipo + '", "' + email + '")';
                //
                resultado += "<li class='list-group-item list-group-item-action'>" +
                    "<a class='aLinkBusca' href='#' onClick='" + link + "'>" + tipo + " | " + nome + " (" + email + ")</a></li>";
            }
        } else {
            resultado += "<li class='list-group-item disabled'>" + resposta['msg'] + "</li>";
        }

        resultado += "</ul>";
        document.getElementById('resultado_pesquisa_emails').innerHTML = resultado;
        let _texto = "<ul class='list-group position-fixed'><li class='list-group-item disabled'>Erro: nada encontrado!</li></ul>";
        if (resultado == _texto) {
            setTimeout(function () {
                document.getElementById('resultado_pesquisa_emails').innerHTML = '';
            }, 3000); // 3000 milissegundos = 3 segundos
        }
    }
}

function atualiza_email(nome, tipo, email) {
    //
    var editor = $('#corpoEmail');
    //
    document.getElementById('para').value = email;
    document.getElementById('resultado_pesquisa_emails').innerHTML = '';
    document.getElementById('nome').value = nome;
    //
    editor.summernote('code'); // Obtém o conteúdo
    //
    var mensagem = "Prezado Senhor <strong>" + nome + "</strong><br><br>Segue anexo o documento.<br><br>Atenciosamente";

    if (editor.length) {
        editor.summernote('code', mensagem);
    }

}