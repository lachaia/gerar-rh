$(document).ready(function () {
    $("#sidebarToggle").trigger("click"); // Dispara o clique

    let idPessoa = $("#idPessoa").val();
    if (idPessoa > 0) {
        atualiza_pessoa(idPessoa);
    }    
    //
});

function f_voltar() {
    window.location.href = "rh_pessoas.php";
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

//-- Grava Inclusão no BD da Pessoa
//
function f_pessoa_commit() {
    //
    if (f_valida_form_pessoa() == false) {
        return false;
    }
    //
    // Captura os dados do formulário como array
    var dados = $("#formPessoa").serializeArray();

    // Adiciona manualmente o campo "ativo"
    let ativo = $("#dcAtivo").is(":checked") ? 1 : 0;
    dados.push({ name: "ativo", value: ativo });
    //
    $.post("includes/rh_pessoa_aj1.php", dados, function (resposta) {
        const dados = JSON.parse(resposta)
        $("#msgAlertaPessoa").html(dados.msg);
        //alert( dados._idPessoa );
        setTimeout(function () {
            // document.location.reload(true);
            f_voltar();
        }, 3000);
    });
}

//- VERIFICA SE TODOS OS CAMPOS OBRIGATÓRIOS - PESSOA
//
function f_valida_form_pessoa() {
    const nome = $("#formPessoa #nome");
    const cpf = $("#formPessoa #cpf");
    //
    if (nome.val() == "") {
        nome.focus();
        alert("Informe o nome da pessoa");
        return false;
    }
    if (cpf.val() == "") {
        cpf.focus();
        alert("Informe o CPF da pessoa");
        return false;
    }
    //
    return true;
}

//- VALIDA O CPF
//
function testar_cpf(o) {
    let tipo = $("#acao_tipo").val();
    if (o.value == "") return;
    var texto = o.value;
    if (validarCPF(texto) == false) {
        alert("CPF inválido!");
        o.value = "";
        return false;
    }
    o.value = texto.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
    if( tipo == 'incluir' ) verifica_existencia(o.value);
}

//-- Carrega dados no Form Pessoa se CPF já na Base
//
function verifica_existencia(cpf) {
    $.post("includes/rh_pessoa_aj0.php", { cpf: cpf }, function (resposta) {
        let _dados = JSON.parse(resposta);
        if (_dados.status) {
            $("#msgAlertaPessoa").html(_dados.msg); // Exibir mensagem na página
            //
            $("#idTipoFormulario").html('PESSOAS - ALTERAÇÃO')
            $("#acao_tipo").val('editar');
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
            //
            $("#cnh").val(_dados.dados.cnh);
            $("#cnh_categoria").val(_dados.dados.cnh_categoria);
            $("#cnh_vencimento").val(_dados.dados.cnh_vencimento);
            $("#nome_mae").val(_dados.dados.nome_mae);            
            $("#idGrauInstrucao").val(_dados.dados.idGrauEscola);            
            $("#pis").val(_dados.dados.pis);            
            $("#ctps").val(_dados.dados.ctps);
            //
            f_lista_enderecos(_dados.dados.idPessoa);
            f_lista_comprovantes(_dados.dados.idPessoa);
            f_lista_antecedentes(_dados.dados.idPessoa);
            f_lista_contatos(_dados.dados.idPessoa);
            f_lista_fotos(_dados.dados.idPessoa);
            //
            setTimeout(function () {
                $("#msgAlertaPessoa").html("");
            }, 3000);
        }
    });
}

//-- Carrega dados no Form Pessoa se CPF já na Base
//
function atualiza_pessoa( idPessoa ) {
    $.post("includes/rh_pessoa_aj16.php", { idPessoa: idPessoa }, function (resposta) {
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
            $("#cnh").val(_dados.dados.cnh);
            $("#cnh_categoria").val(_dados.dados.cnh_categoria);
            $("#cnh_vencimento").val(_dados.dados.cnh_vencimento);
            $("#nome_mae").val(_dados.dados.nome_mae);            
            $("#idGrauInstrucao").val(_dados.dados.idGrauEscola);            
            $("#pis").val(_dados.dados.pis);            
            $("#ctps").val(_dados.dados.ctps);            
            //
            f_lista_enderecos(_dados.dados.idPessoa);
            f_lista_comprovantes(_dados.dados.idPessoa);
            f_lista_antecedentes(_dados.dados.idPessoa);
            f_lista_contatos(_dados.dados.idPessoa);
            f_lista_fotos(_dados.dados.idPessoa);
            //
            setAtivoStatus( _dados.dados.ativo );
        }
    });
}

//- RESET do Form Pessoas
//
$("#formPessoa").on("reset", function () {
    $("#idTipoFormulario").html("PESSOAS - INCLUSÃO");
});

/*
=============================================================== ENDEREÇOS
*/

//- ABRE CARD - ENDEREÇOS
//
$("#toggleEnd").click(function () {
    if (f_valida_form_pessoa() == false) {
        return false;
    }
    //
    $("#bodyEnd").collapse("toggle"); // Alterna a expansão

    let icon = $("#idSetaEnd");
    if (icon.length) {
        icon.toggleClass("fa-chevron-down fa-chevron-up");
    } else {
        console.log("Ícone ainda não encontrado!");
    }
});

//- Pressionou botão Cancelar no Forms de Inclusão de Endereços
//
function f_endereco_cancel() {
    $("#btnResetEndereco").click();
    $("#idSetaEnd").click();
}

//- SALVA ENDEREÇO
//
function f_endereco_commit(tipo) {
    //
    const formulario_1 = $("#formEnderecos");
    const formulario_2 = $("#formPessoa");
    var dados_1 = formulario_1.serialize();
    var dados_2 = formulario_2.serialize();
    //

    $.post("includes/rh_pessoa_aj2.php", { dados_1, dados_2 }, function (resposta) {
        //
        let _dados = JSON.parse(resposta);
        $("#msgAlertaEndereco").html(_dados.msg);
        //
        $("#idPessoa").val(_dados.idPessoa);
        //
        // Formatando o endereço
        let enderecoFormatado = `
           <div class="card-footer text-dark mt-2 p-3 rounded" id="endereco_${_dados.idEndereco}" style='background-color: gainsboro'>
               <strong>${_dados.dados.idTipoEndereco == 1 ? "Residencial" : "Comercial"}</strong><br>
               ${_dados.dados.logradouro}, ${_dados.dados.numero}${_dados.dados.complemento ? ", " + _dados.dados.complemento : ""}, ${_dados.dados.bairro}<br>
               ${_dados.dados.cep} - ${_dados.dados.cidade}/${_dados.dados.uf}
               <button class="btn btn-sm btn-outline-danger float-end remover-endereco" data-id="${_dados.idEndereco}">
                   <i class="fa-solid fa-trash"></i>
               </button>
           </div>`;

        // Adiciona o novo endereço à lista sem remover os anteriores
        $("#listaEnderecos").append(enderecoFormatado);
        //
        $("#msgAlertaEndereco").html(resposta.msg);
        setTimeout(function () {
            //document.location.reload(true);
            $("#msgAlertaEndereco").html("");
            f_endereco_cancel();
        }, 3000);

    });

}

// Evento para remover um endereço da lista
$(document).on("click", ".remover-endereco", function () {
    let id = $(this).data("id");
    $("#endereco_" + id).remove();
    //
    $.post("includes/rh_pessoa_aj3.php", { idEndereco: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaEndereco").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaEndereco").html("");
        }, 3000);
    });
});

//- GERA a LISTA dos ENDEREÇOS da Pessoa
//
function f_lista_enderecos(idPessoa) {
    $.post("includes/rh_pessoa_aj6.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaEnderecos").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.enderecos.forEach(function (endereco) {
                let enderecoFormatado = `
                    <div class="card-footer text-dark mt-2 p-3 rounded" id="endereco_${endereco.idEndereco}" style='background-color: gainsboro'>
                        <strong>${endereco.idTipoEndereco == 1 ? "Residencial" : "Comercial"}</strong><br>
                        ${endereco.logradouro}, ${endereco.numero}${endereco.complemento ? ", " + endereco.complemento : ""}, ${endereco.bairro}<br>
                        ${endereco.cep} - ${endereco.cidade}/${endereco.uf}
                        <button class="btn btn-sm btn-outline-danger float-end remover-endereco" data-id="${endereco.idEndereco}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>`;

                $("#listaEnderecos").append(enderecoFormatado);
            });
        }
    });
}

/*
=============================================================== COMPROVANTE DE ENDEREÇOS
*/

//- ABRE CARD - DOCUMENTOS - Comprovante de Endereços
//
$("#toggleCE").click(function () {
    if (f_valida_form_pessoa() == false) {
        return false;
    }
    //    
    $("#bodyCE").collapse("toggle"); // Alterna a expansão

    let icon = $("#idSetaCE");
    if (icon.length) {
        icon.toggleClass("fa-chevron-down fa-chevron-up");
    } else {
        console.log("Ícone ainda não encontrado!");
    }
});

//- INCLUI novo COMPROVANTE DE ENDEREÇO
//
function f_form_ce() {
    const formulario_1 = document.getElementById("form_ce");
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
        url: "includes/rh_pessoa_aj4.php",
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
                <div class="card-footer text-dark mt-2 p-3 rounded" id="ce_${_dados.idDoc}" style='background-color: gainsboro'>
                    <strong>Comprovante de Endereço ID: ${_dados.idDoc}</strong><br>
                    ${_dados.arquivo}
                    <button class="btn btn-sm btn-outline-danger float-end remover-ce" data-id="${_dados.idDoc}">
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
                document.getElementById("form_ce").reset();
                $("#idSetaCE").click();
            }, 3000);
        }
    });
}

// Evento para remover um Comprovante de Endereço da lista
$(document).on("click", ".remover-ce", function () {
    let id = $(this).data("id");
    $("#ce_" + id).remove();
    //
    $.post("includes/rh_pessoa_aj5.php", { idDoc: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaCE").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaCE").html("");
        }, 3000);
    });
});

//- GERA a LISTA dos Comprovantes de Endereço
//
function f_lista_comprovantes(idPessoa) {
    $.post("includes/rh_pessoa_aj10.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaComprovantes").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.comprovantes.forEach(function (comprovante) {
                let arquivoFormatado = `
                <div class="card-footer text-dark mt-2 p-3 rounded" id="ce_${comprovante.idDoc}" style='background-color: gainsboro'>
                    <strong>Comprovante de Endereço ID: ${comprovante.idDoc}</strong><br>
                    ${comprovante.nome_original}
                    <button class="btn btn-sm btn-outline-danger float-end remover-ce" data-id="${comprovante.idDoc}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

                $("#listaComprovantes").append(arquivoFormatado);
            });
        }
    });
}

/*
=============================================================== ANTECEDENTES CRIMINAIS
*/

//- ABRE CARD - DOCUMENTOS - Atestado de Antecedentes
//
$("#toggleAA").click(function () {
    if (f_valida_form_pessoa() == false) {
        return false;
    }
    // 
    //    
    $("#bodyAA").collapse("toggle"); // Alterna a expansão

    let icon = $("#idSetaAA");
    if (icon.length) {
        icon.toggleClass("fa-chevron-down fa-chevron-up");
    } else {
        console.log("Ícone ainda não encontrado!");
    }
});

//- INCLUI novo ATESTADO de ANTECEDENTES CRIMINAIS
//
function f_form_aa() {
    const formulario_1 = document.getElementById("form_aa");
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
        url: "includes/rh_pessoa_aj7.php",
        type: "POST",
        data: formData,
        processData: false, // Não processa os dados para permitir envio de arquivos
        contentType: false, // Mantém o Content-Type correto
        success: function (resposta) {
            //
            let _dados = JSON.parse(resposta);
            $("#msgAlertaAA").html(_dados.msg);
            //
            $("#idPessoa").val(_dados.idPessoa);
            //
            //
            // Formatando o endereço
            let arquivoFormatado = `
                <div class="card-footer text-dark mt-2 p-3 rounded" id="aa_${_dados.idDoc}"  style='background-color: gainsboro'>
                    <strong>Antecedentes Criminais ID: ${_dados.idDoc}</strong><br>
                    ${_dados.arquivo}
                    <button class="btn btn-sm btn-outline-danger float-end remover-aa" data-id="${_dados.idDoc}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

            // Adiciona o novo endereço à lista sem remover os anteriores
            $("#listaAntecedentes").append(arquivoFormatado);
            //
            $("#msgAlertaAA").html(resposta.msg);
            setTimeout(function () {
                //document.location.reload(true);
                $("#msgAlertaAA").html("");
                document.getElementById("form_aa").reset();
                $("#idSetaAA").click();
            }, 3000);
        }
    });
}

// Evento para remover um Atestado de Antecedentes
$(document).on("click", ".remover-aa", function () {
    let id = $(this).data("id");
    $("#aa_" + id).remove();
    //
    $.post("includes/rh_pessoa_aj5.php", { idDoc: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaAA").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaAA").html("");
        }, 3000);
    });
});

//- GERA a LISTA dos Antecedentes Criminais
//
function f_lista_antecedentes(idPessoa) {
    $.post("includes/rh_pessoa_aj11.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaAntecedentes").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.atestados.forEach(function (atestado) {
                let arquivoFormatado = `
                <div class="card-footer text-dark mt-2 p-3 rounded" id="ce_${atestado.idDoc}" style='background-color: gainsboro'>
                    <strong>Antecedentes Criminais ID: ${atestado.idDoc}</strong><br>
                    ${atestado.nome_original}
                    <button class="btn btn-sm btn-outline-danger float-end remover-ce" data-id="${atestado.idDoc}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

                $("#listaAntecedentes").append(arquivoFormatado);
            });
        }
    });
}

/*
=============================================================== CONTATOS DE EMERGÊNCIA
*/

//- ABRE CARD - CONTATOS DE EMERGÊNCIA
//
$(document).on("click", "#toggleEmergencia", function (event) {
    if (f_valida_form_pessoa() == false) {
        return false;
    }
    // 
    event.preventDefault();

    $("#bodyEmergencia").collapse("toggle");

    let icon = $("#idSetaEmergencia");
    if (icon.length) {
        icon.toggleClass("fa-chevron-down fa-chevron-up");
    } else {
        console.log("Ícone ainda não encontrado!");
    }
});

//- Pressionou botão Cancelar no Forms de Inclusão Contato Emergência
//
function f_emergencia_cancel() {
    $("#btnResetEmergencia").click();
    $("#idSetaEmergencia").click();
}

// Evento para remover um Contato de Emergência
$(document).on("click", ".remover-contato", function () {
    let id = $(this).data("id");
    $("#emg_" + id).remove();
    //
    $.post("includes/rh_pessoa_aj9.php", { idContato: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaEmergencia").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaEmergencia").html("");
        }, 3000);
    });
});

//- SALVA CONTATO DE EMERGÊNCIA
//
function f_emergencia_commit(tipo) {
    //
    const formulario_1 = $("#formEmergencia");
    const formulario_2 = $("#formPessoa");
    //
    var dados_1 = formulario_1.serialize();
    var dados_2 = formulario_2.serialize();
    //

    $.post("includes/rh_pessoa_aj8.php", { dados_1, dados_2 }, function (resposta) {
        //
        let _dados = JSON.parse(resposta);
        $("#msgAlertaEmergencia").html(_dados.msg);
        //
        $("#idPessoa").val(_dados.idPessoa);
        /*
        "emg_nome": "ANNA CAROLINE CHAIA",
        "emg_grau": "FILHA",
        "emg_telefone": "42 3622 6697",
        "emg_celular": "42 9 9101 8668",
        "emg_endereco":
        */
        // Formatando o endereço
        let contatoFormatado = `
           <div class="card-footer text-dark mt-2 p-3 rounded" id="emg_${_dados.idContato}" style='background-color: gainsboro'>
               <strong>Contato de Emergência: ${_dados.dados.emg_grau}</strong><br>
               ${_dados.dados.emg_nome}, Fones: ${_dados.dados.emg_telefone} e ${_dados.dados.emg_celular}<br>
               ${_dados.dados.emg_endereco}
               <button class="btn btn-sm btn-outline-danger float-end remover-contato" data-id="${_dados.idContato}">
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

//- GERA a LISTA dos Contatos de Emergência
//
function f_lista_contatos(idPessoa) {
    $.post("includes/rh_pessoa_aj12.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaEmergencia").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.contatos.forEach(function (contato) {
                let contatoFormatado = `
                <div class="card-footer text-dark mt-2 p-3 rounded" id="emg_${contato.idContato}" style='background-color: gainsboro'>
                    <strong>Contato de Emergência: ${contato.grau}</strong><br>
                    ${contato.nome}, Fones: ${contato.telefone} e ${contato.celular}<br>
                    ${contato.endereco}
                    <button class="btn btn-sm btn-outline-danger float-end remover-contato" data-id="${contato.idContato}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>`;

                $("#listaEmergencia").append(contatoFormatado);
            });
        }
    });
}

/*
=============================================================== FOTOS
*/

//- ABRE CARD - FOTOS
//
$("#toggleFotos").click(function () {
    if (f_valida_form_pessoa() == false) {
        return false;
    }
    // 
    //    
    $("#bodyFotos").collapse("toggle"); // Alterna a expansão

    let icon = $("#idSetaFotos");
    if (icon.length) {
        icon.toggleClass("fa-chevron-down fa-chevron-up");
    } else {
        console.log("Ícone ainda não encontrado!");
    }
});

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
        url: "includes/rh_pessoa_aj13.php",
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
            //
            // Formatando o endereço
            let arquivoFormatado = `
                <div class="card-footer text-dark mt-2 p-3 rounded" id="foto_${_dados.idDoc}">
                    <strong>Foto ID: ${_dados.idDoc}</strong><br>
                    ${_dados.arquivo}
                    <button class="btn btn-sm btn-outline-danger float-end remover-foto" data-id="${_dados.idDoc}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

            // Adiciona o novo endereço à lista sem remover os anteriores
            if( _dados.status == true) $("#listaFotos").append(arquivoFormatado);
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
    $.post("includes/rh_pessoa_aj14.php", { idDoc: id }, function (retorno) {
        dados = JSON.parse(retorno);
        $("#msgAlertaFotos").html(dados.msg);
        setTimeout(function () {
            $("#msgAlertaFotos").html("");
        }, 3000);
    });
});

//- GERA a LISTA das FOTOS
//
function f_lista_fotos(idPessoa) {
    $.post("includes/rh_pessoa_aj15.php", { idPessoa: idPessoa }, function (resposta) {
        let resultado = JSON.parse(resposta);

        if (resultado.status) {
            $("#listaFotos").html(""); // Limpa a lista antes de adicionar os endereços

            resultado.fotos.forEach(function (foto) {
                let caminhoFoto = `docs_view.php?pessoa=${idPessoa}&arquivo=${encodeURIComponent(foto.arquivo)}`;

                let arquivoFormatado = `
                <div class="card-footer text-dark mt-2 p-3 rounded d-flex align-items-center" id="ce_${foto.idDoc}" style="background-color: gainsboro">
                    
                    <!-- Miniatura -->
                    <img src="${caminhoFoto}" alt="Foto" 
                         class="me-3 rounded" 
                         style="width: 60px; height: 60px; object-fit: cover;">

                    <!-- Texto e botão -->
                    <div class="flex-grow-1">
                        <strong>Foto ID: ${foto.idDoc}</strong><br>
                        ${foto.nome_original}
                    </div>
                    
                    <button class="btn btn-sm btn-outline-danger ms-2 remover-ce" data-id="${foto.idDoc}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
                `;

                $("#listaFotos").append(arquivoFormatado);
            });
        }
    });
}


function toggleAtivo() {
    let ativo = document.getElementById("dcAtivo").checked ? 1 : 0;
    $("#dcAtivo").val(ativo);
    $("#labelAtivo").text(ativo ? "Ativo" : "Inativo");
}

function setAtivoStatus(ativo) {
    if (ativo == 1) {
        $("#dcAtivo").prop("checked", true).val(1);
        $("#labelAtivo").text("Ativo");
    } else {
        $("#dcAtivo").prop("checked", false).val(0);
        $("#labelAtivo").text("Inativo");
    }
}
