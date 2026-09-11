//
// dados.js | 08/12/2025 | vr. 1.0 | (C)haia.
//

const modalIncEnd = new bootstrap.Modal(document.getElementById("modalIncEnd"));
const modalIncCpt = new bootstrap.Modal(document.getElementById("modalIncComprovante"));
const modalIncFot = new bootstrap.Modal(document.getElementById("modalIncFoto"));
const modalIncEmg = new bootstrap.Modal(document.getElementById("modalIncEmergencia"));

$(document).ready(function () {

    let idPessoa = $("#idPessoa").val();
    if (idPessoa > 0) {
        atualiza_pessoa(idPessoa);
    }

});

function salvar_dados(){
    //
    let mensagem = $("#msgAlertaPessoa");
    let botoes = $("#dados_botoes");
    // Captura os dados do formulário como array
    var dados = $("#formPessoa").serializeArray();
    //
    mensagem.html("Processando...");
    botoes.hide();
    $.post("dados_aj4.php", dados, function (resposta) {
        const dados = JSON.parse(resposta)
        mensagem.html(dados.msg);
        setTimeout(function () {
            document.location.reload(true);
        }, 3000);
    });
}
function atualiza_pessoa(idPessoa) {
    $.post("../includes/rh_pessoa_aj16.php", { idPessoa: idPessoa }, function (resposta) {
        let _dados = JSON.parse(resposta);

        if (_dados.status) {
            // $("#msgAlertaPessoa").html(_dados.msg); // Exibir mensagem na página
            //
            $("#idTipoFormulario").html('PESSOAS - ALTERAÇÃO')
            //
            $("#idPessoa").val(_dados.dados.idPessoa);
            $("#nome").val(_dados.dados.nome);
            $("#nomeSocial").val(_dados.dados.nomeSocial);
            $("#dataNascimento").val(_dados.dados.dtNascimento);
            $("#sexo").val(_dados.dados.sexo);
            $("#idEstadoCivil").val(_dados.dados.idEstadoCivil);
            $("#rg").val(_dados.dados.rg);
            $("#tituloEleitor").val(_dados.dados.titulo_eleitor);
            $("#telefone").val(_dados.dados.telefone);
            $("#email").val(_dados.dados.email);
            $("#tamanhoCamiseta").val(_dados.dados.camiseta);
            $("#idEtnia").val(_dados.dados.idEtnia);
            $("#nacionalidade").val(_dados.dados.nacionalidade);
            $("#cpf").val(_dados.dados.cpf);
            //
            f_lista_enderecos(_dados.dados.idPessoa);
            f_lista_comprovantes(_dados.dados.idPessoa);
            f_lista_fotos(_dados.dados.idPessoa);
            f_lista_contatos(_dados.dados.idPessoa);
            //
            //f_lista_antecedentes(_dados.dados.idPessoa);
            //
        }
    });
}

//- GERA a LISTA dos ENDEREÇOS da Pessoa
//
function f_lista_enderecos(idPessoa) {
    //
    $.post("../includes/rh_pessoa_aj6.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);
        if (resultado.status) {
            $("#listaEnderecos").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.enderecos.forEach(function (endereco) {
                let enderecoFormatado = `
                        <div class="card-footer mt-2 p-3 rounded cartao" id="endereco_${endereco.idEndereco}">
                            <strong class="titulo">${endereco.dsTipoEndereco}</strong><br>
                            ${endereco.logradouro}, ${endereco.numero}${endereco.complemento ? ", " + endereco.complemento : ""}, ${endereco.bairro}<br>
                            ${endereco.cep} - ${endereco.cidade}/${endereco.uf}
                            <div class="btn-group float-end">
                                <button class="btn btn-sm btn-primary editar-endereco" data-id="${endereco.idEndereco}" onclick="f_editar_endereco(${endereco.idEndereco})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger remover-endereco" data-id="${endereco.idEndereco}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>`;

                $("#listaEnderecos").append(enderecoFormatado);
            });
        }
    });
}

// Evento para remover um endereço da lista
$(document).on("click", ".remover-endereco", function () {
    let id = $(this).data("id");
    $("#endereco_" + id).remove();
    //
    $.post("../includes/rh_pessoa_aj3.php", { idEndereco: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaEndereco").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaEndereco").html("");
        }, 3000);
    });
});

function f_incluir_endereco() {
    let titulo = '<i class="fa-regular fa-pen-to-square"></i> Incluir novo Endereço';
    $("#modalEnderecoLabel").html( titulo );    
    $("#btnResetNovoEndereco").click();    
    modalIncEnd.show();
}

//- Saiu do campo CEP no tela de Inclusão
//
function busca_cep(o) {
    var texto = o.value;
    texto = texto.replace(/[^\d]+/g, '');
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

//- SALVA ENDEREÇO
//
function f_endereco_commit(tipo) {
    //
    const formulario = $("#formNovoEndereco");
    var dados = formulario.serialize();
    dados += "&idPessoa=" + encodeURIComponent($("#idPessoa").val());
    //
    $.post("dados_aj2.php", { dados }, function (resposta) {
        //
        let _dados = JSON.parse(resposta);
        $("#msgAlertaNovoEndereco").html(_dados.msg);
        //
        $("#idPessoa").val(_dados.idPessoa);
        //
        // Formatando o endereço
        let enderecoFormatado = `
            <div class="card-footer cartao mt-2 p-3 rounded" id="endereco_${_dados.idEndereco}" style='position: relative;'>
                <strong>${_dados.dados.idTipoEndereco == 1 ? "Residencial" : "Comercial"}</strong><br>
                ${_dados.dados.logradouro}, ${_dados.dados.numero}${_dados.dados.complemento ? ", " + _dados.dados.complemento : ""}, ${_dados.dados.bairro}<br>
                ${_dados.dados.cep} - ${_dados.dados.cidade}/${_dados.dados.uf}
                <div class="btn-group float-end">
                    <button class="btn btn-sm btn-primary editar-endereco" data-id="${_dados.idEndereco}" onclick="f_editar_endereco(${_dados.idEndereco})">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-sm btn-danger remover-endereco" data-id="${_dados.idEndereco}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>`;

        // Adiciona o novo endereço à lista sem remover os anteriores
        $("#listaEnderecos").append(enderecoFormatado);
        //
        $("#msgAlertaNovoEndereco").html(resposta.msg);
        setTimeout(function () {
            //document.location.reload(true);
            $("#msgAlertaNovoEndereco").html("");
            $("#btnResetNovoEndereco").click();
            modalIncEnd.hide();
        }, 3000);

    });

}

function f_editar_endereco(idEndereco) {
    modalIncEnd.show();
    let titulo = '<i class="fa-regular fa-pen-to-square"></i> Alterar Endereço';
    $("#modalEnderecoLabel").html( titulo );
    //
    $.post("dados_aj1.php", { idEndereco: idEndereco }, function (resposta) {
        let x = JSON.parse(resposta);
        //
        $("#idEndereco").val(x.dados.idEndereco);
        $("#idTipoEndereco").val(x.dados.idTipoEndereco);
        $("#logradouro").val(x.dados.logradouro);
        $("#numero").val(x.dados.numero);
        $("#complemento").val(x.dados.complemento);
        $("#bairro").val(x.dados.bairro);
        $("#cep").val(x.dados.cep);
        $("#cidade").val(x.dados.cidade);
        $("#uf").val(x.dados.uf);
        //
        $("#btnSalvarEndereco").attr("onclick", "f_salvar_endereco()");
    });
    //
}

function f_salvar_endereco() {
    //
    const formulario = $("#formNovoEndereco");
    var dados = formulario.serialize();
    dados += "&idPessoa=" + encodeURIComponent($("#idPessoa").val());
    //
    $.post("index_aj4.php", { dados }, function (resposta) {
        //
        let _dados = JSON.parse(resposta);
        $("#msgAlertaNovoEndereco").html(_dados.msg);
        $("#idPessoa").val(_dados.idPessoa);
        //
        // Remove o endereço anterior
        $("#endereco_" + _dados.idEndereco).remove();

        // Formatando o endereço
        let enderecoFormatado = `
            <div class="card-footer cartao mt-2 p-3 rounded" id="endereco_${_dados.idEndereco}" style='position: relative;'>
                <strong>${_dados.dados.idTipoEndereco == 1 ? "Residencial" : "Comercial"}</strong><br>
                ${_dados.dados.logradouro}, ${_dados.dados.numero}${_dados.dados.complemento ? ", " + _dados.dados.complemento : ""}, ${_dados.dados.bairro}<br>
                ${_dados.dados.cep} - ${_dados.dados.cidade}/${_dados.dados.uf}
                <div class="btn-group float-end">
                    <button class="btn btn-sm btn-primary editar-endereco" data-id="${_dados.idEndereco}" onclick="f_editar_endereco(${_dados.idEndereco})">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-sm btn-danger remover-endereco" data-id="${_dados.idEndereco}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>`;

        // Adiciona o novo endereço à lista sem remover os anteriores
        $("#listaEnderecos").append(enderecoFormatado);
        //
        $("#msgAlertaNovoEndereco").html(resposta.msg);
        setTimeout(function () {
            //document.location.reload(true);
            $("#msgAlertaNovoEndereco").html("");
            $("#btnResetNovoEndereco").click();
            modalIncEnd.hide();
        }, 3000);

    });
}

//- GERA a LISTA dos Comprovantes de Endereço
//
function f_lista_comprovantes(idPessoa) {
    //
    $.post("../includes/rh_pessoa_aj10.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaComprovantes").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.comprovantes.forEach(function (comprovante) {
                let arquivoFormatado = `
                <div class="card-footer cartao mt-2 p-3 rounded" id="ce_${comprovante.idDoc}">
                    <strong>Comprovante de Endereço ID: ${comprovante.idDoc}</strong><br>
                    ${comprovante.nome_original} <a href='#' onclick='mostra_arquivo(${idPessoa},"${comprovante.arquivo}")'><i class="fa-solid fa-magnifying-glass"></i></a>
                    <button class="btn btn-sm btn-danger float-end remover-ce" data-id="${comprovante.idDoc}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

                $("#listaComprovantes").append(arquivoFormatado);
            });
        }
    });
}

function mostra_arquivo(idPessoa, nomeArquivo) {
    let url = "../docs_view.php?pessoa=" + idPessoa + "&arquivo=" + encodeURIComponent(nomeArquivo);
    let win = window.open(url, '_blank');
    if (win) {
        // Browser has allowed it to be opened
        win.focus();
    } else {
        // Browser has blocked it
        alert('Por favor, permita os pop-ups para visualizar o arquivo.');
    }
}

function f_incluir_comprovante() {
    modalIncCpt.show();
}

function f_envia_comprovante() {
    //
    let formData = new FormData();

    // Campos de texto
    formData.append("idColab", document.getElementById("idColab").value);
    formData.append("idPessoa", document.getElementById("idPessoa").value);
    formData.append("idEndereco", document.getElementById("idEndereco").value);
    formData.append("ce_data", document.getElementById("ce_data").value);

    // Campo de arquivo
    const arquivoInput = document.getElementById("ce_arquivo");
    if (arquivoInput.files.length > 0) {
        formData.append("ce_arquivo", arquivoInput.files[0]);
    }
    //
    $.ajax({
        url: "dados_aj3.php",
        type: "POST",
        data: formData,
        processData: false, // Não processa os dados para permitir envio de arquivos
        contentType: false, // Mantém o Content-Type correto
        success: function (resposta) {
            //
            let _dados = JSON.parse(resposta);
            $("#msgAlertaCE").html(_dados.msg);
            //
            $("#idPessoa").val(_dados.idPessoa);
            //
            //
            // Formatando o endereço
            let arquivoFormatado = `
                <div class="card-footer cartao mt-2 p-3 rounded" id="ce_${_dados.idDoc}">
                    <strong>Comprovante de Endereço ID: ${_dados.idDoc}</strong><br>
                    ${_dados.original} <a href='#' onclick='mostra_arquivo(${idPessoa},"${_dados.arquivo}")'><i class="fa-solid fa-magnifying-glass"></i></a>
                    <button class="btn btn-sm btn-danger float-end remover-ce" data-id="${_dados.idDoc}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

            // Adiciona o novo endereço à lista sem remover os anteriores
            $("#listaComprovantes").append(arquivoFormatado);
            //
            $("#msgAlertaCE").html(resposta.msg);
            setTimeout(function () {
                //document.location.reload(true);
                $("#msgAlertaCE").html("");
                modalIncCpt.hide();
            }, 3000);
        }
    });
}

// Evento para remover um Comprovante e Foto de Endereço da lista
$(document).on("click", ".remover-ce", function () {
    let id = $(this).data("id");
    $("#ce_" + id).remove();
    //
    $.post("../includes/rh_pessoa_aj5.php", { idDoc: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaCE").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaCE").html("");
        }, 3000);
    });
});

function f_incluir_foto() {
    modalIncFot.show();
}

//- GERA a LISTA das FOTOS
//
function f_lista_fotos(idPessoa) {
    $.post("../includes/rh_pessoa_aj15.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaFotos").html(""); // Limpa a lista antes de adicionar as fotos

            resultado.fotos.forEach(function (foto) {
                let caminhoImagem = `../docs_view.php?pessoa=${idPessoa}&arquivo=${encodeURIComponent(foto.arquivo)}`; // caminho real do arquivo

                let arquivoFormatado = `
                    <div class="card-footer cartao mt-2 p-3 rounded d-flex justify-content-between align-items-center" id="foto_${foto.idDoc}">
                        <div class="d-flex align-items-center">
                            <img src="${caminhoImagem}" alt="foto" style="height: 40px; width: 40px; object-fit: cover; margin-right: 10px; border-radius: 4px;" />
                            <div>
                                <strong>Foto ID: ${foto.idDoc}</strong><br>
                                ${foto.nome_original}
                            </div>
                        </div>
                        <button class="btn btn-sm btn-danger remover-foto" data-id="${foto.idDoc}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;

                $("#listaFotos").append(arquivoFormatado);
            });
        }
    });
}

//- INCLUI nova FOTO
//
function f_form_fotos() {
    const formulario_1 = document.getElementById("form_fotos");
    const formulario_2 = document.getElementById("formPessoa");

    // Criando um único FormData para armazenar os dois formulários
    var formData = new FormData();

    // Adicionando os dados do primeiro formulário (incluindo arquivos)
    new FormData(formulario_1).forEach((value, key) => {
        formData.append(key, value);
    });

    // Adicionando os dados do segundo formulário (incluindo arquivos)
    new FormData(formulario_2).forEach((value, key) => {
        formData.append(key, value);
    });

    $.ajax({
        url: "../includes/rh_pessoa_aj13.php",
        type: "POST",
        data: formData,
        processData: false, // Não processa os dados para permitir envio de arquivos
        contentType: false, // Mantém o Content-Type correto
        success: function (resposta) {
            //
            let _dados = JSON.parse(resposta);
            $("#msgAlertaFotos").html(_dados.msg);
            //
            $("#idPessoa").val(_dados.idPessoa);
            //
            // Formatando o endereço
            let caminhoImagem = `../docs_view.php?pessoa=${_dados.idPessoa}&arquivo=${encodeURIComponent(_dados.arquivo)}`; // caminho real do arquivo
            let arquivoFormatado = `
                    <div class="card-footer mt-2 p-3 rounded d-flex justify-content-between align-items-center cartao" id="foto_${_dados.idDoc}">
                        <div class="d-flex align-items-center">
                            <img src="${caminhoImagem}" alt="foto" style="height: 40px; width: 40px; object-fit: cover; margin-right: 10px; border-radius: 4px;" />
                            <div>
                                <strong>Foto ID: ${_dados.idDoc}</strong><br>
                                ${_dados.original}
                            </div>
                        </div>
                        <button class="btn btn-sm btn-danger remover-foto" data-id="${_dados.idDoc}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;

            // Adiciona o novo endereço à lista sem remover os anteriores
            if (_dados.status == true) $("#listaFotos").append(arquivoFormatado);
            //
            $("#msgAlertaFotos").html(resposta.msg);
            setTimeout(function () {
                //document.location.reload(true);
                $("#msgAlertaFotos").html("");
                document.getElementById("form_fotos").reset();
                $("#idSetaFotos").click();
            }, 3000);
        }
    });
}

// Evento para remover uma FOTO
$(document).on("click", ".remover-foto", function () {
    let id = $(this).data("id");
    $("#foto_" + id).remove();
    //
    $.post("../includes/rh_pessoa_aj14.php", { idDoc: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaFotos").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaFotos").html("");
        }, 3000);
    });
});

//- GERA a LISTA dos Contatos de Emergência
//
function f_lista_contatos(idPessoa) {
    $.post("../includes/rh_pessoa_aj12.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaEmergencia").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.contatos.forEach(function (contato) {
                let contatoFormatado = `
                <div class="card-footer cartao mt-2 p-3 rounded" id="emg_${contato.idContato}">
                    <strong class='titulo'>Contato de Emergência: ${contato.grau}</strong><br>
                    ${contato.nome}, Fones: ${contato.telefone} e ${contato.celular}<br>
                    ${contato.endereco}
                    <button class="btn btn-sm btn-danger float-end remover-contato" data-id="${contato.idContato}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>`;

                $("#listaEmergencia").append(contatoFormatado);
            });
        }
    });
}

function f_incluir_contato() {
    modalIncEmg.show();
}

//- Pressionou botão Cancelar no Forms de Inclusão Contato Emergência
//
function f_emergencia_cancel() {
    $("#btnResetEmergencia").click();
    modalIncEmg.hide();
}

// Evento para remover um Contato de Emergência
$(document).on("click", ".remover-contato", function () {
    let id = $(this).data("id");
    $("#emg_" + id).remove();
    //
    $.post("../includes/rh_pessoa_aj9.php", { idContato: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaEmergencia").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaEmergencia").html("");
        }, 3000);
    });
});

//- SALVA CONTATO DE EMERGÊNCIA
//
function f_contato_commit(tipo) {
    //
    const formulario_1 = $("#formEmergencia");
    const formulario_2 = $("#formPessoa");
    //
    var dados_1 = formulario_1.serialize();
    var dados_2 = formulario_2.serialize();
    //

    $.post("../includes/rh_pessoa_aj8.php", { dados_1, dados_2 }, function (resposta) {
        //
        let _dados = JSON.parse(resposta);
        $("#msgAlertaEmergencia").html(_dados.msg);
        //
        $("#idPessoa").val(_dados.idPessoa);
        // Formatando o endereço
        let contatoFormatado = `
           <div class="card-footer cartao mt-2 p-3 rounded" id="emg_${_dados.idContato}">
               <strong>Contato de Emergência: ${_dados.dados.emg_grau}</strong><br>
               ${_dados.dados.emg_nome}, Fones: ${_dados.dados.emg_telefone} e ${_dados.dados.emg_celular}<br>
               ${_dados.dados.emg_endereco}
               <button class="btn btn-sm btn-danger float-end remover-contato" data-id="${_dados.idContato}">
                   <i class="fa-solid fa-trash"></i>
               </button>
           </div>`;

        // Adiciona o novo endereço à lista sem remover os anteriores
        $("#listaEmergencia").append(contatoFormatado);
        //
        $("#msgAlertaEmergencia").html(resposta.msg);
        setTimeout(function () {
            $("#msgAlertaEmergencia").html("");
            document.getElementById("formEmergencia").reset();
            $("#idSetaEmergencia").click();
        }, 3000);
    });

}
