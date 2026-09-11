//
//- Unidades.js | Rotinas auxiliares JavaScript - UnidadeS
// (C)haia, 2026-06-16
//

var dataTable;

$(document).ready(function () {
    //
    constroiTabela();

    // Configura o Autocomplete da Cidade
    $("#form-inc #busca_cidade").autocomplete({
        appendTo: "#modalIncUnidade",
        source: function (request, response) {
            $.ajax({
                url: "includes/busca_cidades.php",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    if (!data.length) {
                        // Dica: alert aqui pode travar a digitação do usuário. 
                        // Se preferir tirar depois, o autocomplete apenas não exibe nada.
                        alert("nada encontrado!");
                    }
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            // Evita que o jQuery UI preencha o input com o label inteiro caso haja divergência
            event.preventDefault();

            // Preenche os campos usando as propriedades limpas do banco
            $("#form-inc #busca_cidade").val(ui.item.nome); // Coloca apenas "Curitiba" no input
            $("#form-inc #cidade_id").val(ui.item.id);
            $("#form-inc #uf").val(ui.item.uf);
            $("#form-inc #pais").val(ui.item.pais);

            console.log(ui.item);
            return false;
        },
        focus: function (event, ui) {
            // Evita que ao passar o mouse/seta pelas opções mude o texto do input para o label longo
            event.preventDefault();
            $("#form-inc #busca_cidade").val(ui.item.nome);
        }
    });

    // Configura o Autocomplete da Cidade
    $("#form-alt #busca_cidade").autocomplete({
        appendTo: "#modalAltUnidade", // Faz a lista "nascer" dentro da modal
        source: function (request, response) {
            $.ajax({
                url: "includes/busca_cidades.php",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    if (!data.length) {
                        // Opcional: avisar que não achou nada
                        alert("nada encontrado!")
                    }
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            $("#form-alt #cidade_busca").val(ui.item.nome);
            $("#form-alt #cidade_id").val(ui.item.id);
            $("#form-alt #uf").val(ui.item.uf);
            $("#form-alt #pais").val(ui.item.pais);
            console.log(ui.item);
            return false;
        }
    });
});

// Função para abrir a modal (chame no seu botão de Incluir)
function f_incluir() {
    // $('#form-inc-Unidade')[0].reset();
    $('#modalIncUnidade').modal('show');
}

function constroiTabela() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 10;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = alturaTotal > 900 ? 12 : 10;
    }
    //
    dataTable = new DataTable('#example', {
        "processing": true,
        "serverSide": false,
        "order": [
            [0, "asc"], [1, "asc"]
        ],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "includes/rh_polos_aj.php",
            "type": "POST",
        },
        "columnDefs": [{
            "targets": [ 2, 6, 8, 9, 10],
            "className": "text-center"
        }, {
            "targets": [],
            "visible": false
        }],
        language: {
            url: 'includes/pt-br.json',
        },
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

function f_visualizar(id) {
    $.post("includes/rh_polo_get_aj.php", { id: id }, function (response) {
        try {
            let res = JSON.parse(response);
            if (res.status) {
                // Preenchendo os campos de texto
                $("#v_responsavel").text(res.dados.responsavel);
                $("#v_identificador").text(res.dados.identificador);
                $("#v_subsede").text(res.dados.subsede_id + " - " + res.dados.subsede_ds);
                $("#v_id").text(res.dados.id);
                $("#v_email").text(res.dados.email || '---');
                $("#v_telefone").text(res.dados.telefone || '---');

                // Montando a localização
                // Montando a localização
                let loc = res.dados.endereco + ", " + 
                        res.dados.numero + 
                        ( res.dados.complemento ? ", " + res.dados.complemento : "") + ", " + 
                        ( res.dados.bairro ? ", " + res.dados.bairro : "") + 
                        ( res.dados.cidade ? ", " + res.dados.cidade : "") + 
                        ( res.dados.uf ? " - " + res.dados.uf : "") + 
                        ( res.dados.pais ? " - " + res.dados.pais : "");
                $("#v_localizacao").text(loc);

                // Tratando o conteúdo do Summernote (usamos .html() porque vem com tags)
                // Tratando o conteúdo do Summernote para manter o padrão Dark
                if (res.dados.obs && res.dados.obs.trim() !== "") {
                    // Adicionamos classes dark e garantimos que o HTML interno respeite o contraste
                    $("#v_obs").html(res.dados.obs);
                } else {
                    $("#v_obs").html('<em class="text-secondary">Nenhuma observação registrada.</em>');
                }

                // Abre a modal
                $("#modalVisUnidade").modal("show");
            } else {
                alert("Erro: " + res.msg);
            }
        } catch (e) {
            console.error("Erro ao processar dados", e);
        }
    });
}

function f_excluir(id) {
    let r = confirm("Confirma a exclusão desta Unidade? ID: " + id);
    if (r == true) {
        $.ajax({
            url: 'includes/rh_polo_exc_aj.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (response) {
                alert(response.msg);
                selecionou();
            },
            error: function (xhr, status, error) {
                console.error("Erro na requisição AJAX: " + error);
            }
        });
    }
    return true;
}

function f_incluir_cidade() {
    $('#form-rapido-cidade')[0].reset();
    $('#modalNovaCidade').modal('show');
}

function f_salvar_cidade_rapido() {
    const nome = $('#n_cidade_nome').val();
    const uf = $('#n_cidade_uf').val();
    const pais = $('#n_cidade_pais').val() || 'Brasil'; // Default Brasil se vazio

    if (!nome) {
        alert("O nome da cidade é obrigatório!");
        return;
    }

    $.post("includes/cidades_insere_rapido.php", {
        nome: nome,
        uf: uf,
        pais: pais
    }, function (res) {
        try {
            const ret = JSON.parse(res);
            if (ret.status) {
                $('#modalNovaCidade').modal('hide');

                // Preenche a modal de Unidade com os dados novos
                // Formato: Cidade (UF) - País
                let exibicao = nome + (uf ? " (" + uf + ")" : "") + " - " + pais;

                $('#form-inc #busca_cidade').val(exibicao);
                $('#form-inc #cidade_id').val(ret.id_inserido);
                $('#form-inc #uf').val(uf);
                $('#form-inc #pais').val(pais);
                $('#form-alt #busca_cidade').val(exibicao);
                $('#form-alt #cidade_id').val(ret.id_inserido);
                $('#form-alt #uf').val(uf);
                $('#form-alt #pais').val(pais);

                $('#form-inc #email').focus();
            } else {
                alert("Erro: " + ret.msg);
            }
        } catch (e) {
            console.error("Erro no retorno do servidor:", res);
        }
    });
}

function f_salvar_unidade() {
    // Validação básica de campos obrigatórios
    if ($("#form-inc #identificador").val() === "") {
        alert("Por favor, preencha o nome da Unidade.");
        $("#identificador").focus();
        return;
    }
    if ($("#form-inc #subsede_id").val() === "0") {
        alert("Por favor, Selecione uma subsede.");
        $("#form-inc #subsede_id").focus();
        return;
    }
    if ($("#form-inc #polo_id").val() === "0") {
        alert("Por favor, Informe o ID do Polo (zum)");
        $("#form-inc #polo_id").focus();
        return;
    }    

    // Coleta os dados do formulário
    // O serialize() funciona bem, mas precisamos garantir o Summernote
    var formData = $("#form-inc").serialize();

    // Feedback visual de carregamento
    const btnSalvar = $("button[onclick='f_salvar_unidade()']");
    const btnTextoOriginal = btnSalvar.html();
    btnSalvar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

    $.ajax({
        url: "includes/rh_polo_inc_aj.php",
        type: "POST",
        data: formData,
        success: function (response) {
            try {
                let res = JSON.parse(response);
                if (res.status) {
                    alert("Unidade inserido com sucesso!");
                    $('#modalIncUnidade').modal('hide');
                    $("#form-inc")[0].reset();
                    $('#c_obs_summernote').summernote('code', ''); // Limpa o editor

                    // Se você tiver uma DataTable na tela principal, recarregue-a:
                    if (typeof dataTable !== 'undefined') {
                        dataTable.ajax.reload();
                    }
                } else {
                    alert("Erro: " + res.msg);
                }
            } catch (e) {
                console.error("Erro no JSON:", response);
                alert("Erro interno no servidor.");
            }
        },
        error: function () {
            alert("Erro na requisição. Verifique sua conexão.");
        },
        complete: function () {
            btnSalvar.prop('disabled', false).html(btnTextoOriginal);
        }
    });
}

// Inicializa o summernote da alteração
$('#inc_obs_summernote').summernote({ height: 100, lang: 'pt-BR' });
$('#alt_obs_summernote').summernote({ height: 100, lang: 'pt-BR' });

// Função chamada pelo botão "Editar" da sua tabela
function f_editar(id) {
    $.post("includes/rh_polo_get_aj.php", { id: id }, function ( res ) {
        if (res.status) {
            // Preenche os campos
            $("#form-alt #id"           ).val(res.dados.id);
            $("#form-alt #identificador").val(res.dados.identificador);
            $("#form-alt #subsede_id"   ).val(res.dados.subsede_id);
            $("#form-alt #responsavel"  ).val(res.dados.responsavel);
            $("#form-alt #cep"          ).val(res.dados.cep);
            $("#form-alt #endereco"     ).val(res.dados.endereco);
            $("#form-alt #numero"       ).val(res.dados.numero);
            $("#form-alt #complemento"  ).val(res.dados.complemento);
            $("#form-alt #bairro"       ).val(res.dados.bairro);
            $("#form-alt #busca_cidade" ).val(res.dados.cidade_ds);
            $("#form-alt #cidade_id"    ).val(res.dados.cidade_id);
            $("#form-alt #uf"           ).val(res.dados.uf);
            $("#form-alt #email"        ).val(res.dados.email);
            $("#form-alt #telefone"     ).val(res.dados.telefone);
            $("#form-alt #pais"         ).val(res.dados.pais);
            $("#form-alt #polo_id"      ).val(res.dados.polo_id);

            // Alimenta o Summernote
            $('#alt_obs_summernote').summernote('code', res.dados.obs);

            $("#modalAltUnidade").modal("show");
        } else {
            alert("Erro ao carregar dados: " + res.msg);
        }
    }, "json");
}

function f_salvar_alteracao() {
    var formData = $("#form-alt").serialize();

    $.ajax({
        url: "includes/rh_polo_alt_aj.php",
        type: "POST",
        data: formData,
        success: function (response) {
            let res = JSON.parse(response);
            if (res.status) {
                alert("Alterado com sucesso!");
                $('#modalAltUnidade').modal('hide');
                dataTable.ajax.reload();
            } else {
                alert("Erro: " + res.msg);
            }
        }
    });
}

function busca_cep(o, formulario = 'form-inc') {
    var texto = o.value;
    texto = texto.replace(/[^\d]+/g, '');
    if (texto == '') return false;
    if (texto.length == 8) {
        //- busca o cep
        const url = "https://viacep.com.br/ws/" + texto + "/json/";
        $.get(url, function (data) {
            console.log(data);
            //
            document.querySelector(`#${formulario} #endereco`).value = data.logradouro;
            document.querySelector(`#${formulario} #bairro`).value = data.bairro;
            document.querySelector(`#${formulario} #busca_cidade`).value = data.localidade;
            document.querySelector(`#${formulario} #uf`).value = data.uf;
            
            document.getElementById('numero').focus();
            //
            $.post("includes/subsede_busca_cidade_aj.php", { cidade: data.localidade }, function (res) {
                try {
                    console.log(res);
                    let dados = JSON.parse(res);
                    if (dados.status) {
                        document.querySelector(`#${formulario} #cidade_id`).value = dados[0].idCidade;
                        document.querySelector(`#${formulario} #pais`).value = dados[0].pais;
                    } else {
                        alert("Erro: " + dados.msg);
                    }
                } catch (e) {
                    console.error("Erro no JSON:", res);
                    alert("Erro interno no servidor.");
                }
            });
            //
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

function minusculas(o) {
    o.value = o.value.toLowerCase();
}

function maiusculas(o) {
    o.value = o.value.toUpperCase();
}