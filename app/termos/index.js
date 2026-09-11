
let dataTableSolic;
let dataTableTermos;
let dataTableModelos;
let dataTableUsuarios;

const verModalSolic = new bootstrap.Modal(document.getElementById("modalVerSolic"));

const incModalTermos = new bootstrap.Modal(document.getElementById("modalIncTermos"));
const verModalTermo = new bootstrap.Modal(document.getElementById("modalVerTermo"));
const altModalTermos = new bootstrap.Modal(document.getElementById("modalAltTermos"));
const bxaModalTermo = new bootstrap.Modal(document.getElementById("modalBxaTermo"));
const assModalTermo = new bootstrap.Modal(document.getElementById("modalAssTermo"));

const incModalModelo = new bootstrap.Modal(document.getElementById("modalIncModelo"));
const verModalModelo = new bootstrap.Modal(document.getElementById("modalVerModelo"));
const altModalModelo = new bootstrap.Modal(document.getElementById("modalAltModelo"));

$(document).ready(function () {
    constroiTabela_solicitacoes();
    constroiTabela_termos();
    constroiTabela_modelos();

    $('#inc_html, #edit_html, #termo_observacoes, #termo_observacoes_dev, #bxa_termo_observacoes_dev').summernote({
        placeholder: 'Informe o texto aqui...',
        tabsize: 2,
        height: 300, // altura inicial
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ]
    });

    $("#termo_inc_nome").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "../includes/buscar_pessoas.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    response($.map(data, function (item) {
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
        select: function (event, ui) {
            $("#termo_idPessoa").val(ui.item.idPessoa); // <- usa a mesma chave
            f_termo_atualiza_endereco(ui.item.idPessoa); //-- atualiza endereço
        }
    });

});

$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    const target = $(e.target).attr("href"); // retorna tipo "#tabTermos"

    if (target === "#home" || target === "#termos" || target === "#modelos") {
        $.post("index_aj18.php", { tab: target });
    }

    if (target === "#termos" && dataTableTermos) {
        dataTableTermos.columns.adjust().draw();
    }
    else if (target === "#modelos" && dataTableModelos) {
        dataTableModelos.columns.adjust().draw();
    }
    else if (target === "#usuarios" && dataTableUsuarios) {
        dataTableUsuarios.columns.adjust().draw();
    }
});

function abrirPagina(pagina) {
    //window.location.href = pagina;
    if (pagina == 'solicitacoes') {
        let div = document.getElementById('divSolicitacoes');
        div.style.display = 'block';
        //- Iniciar DataTable
    }
}

function constroiTabela_solicitacoes() {
    dataTableSolic = new DataTable('#tabelaSolicitacoes', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": 10, // Define a quantidade de linhas
        "ajax": {
            "url": "index_aj1.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 4, 5],
            "className": "text-center"
        }],
        language: {
            url: '../includes/pt-BR.json',
        },
    });
}

function constroiTabela_termos() {
    dataTableTermos = new DataTable('#tabelaTermos', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": 10, // Define a quantidade de linhas
        "ajax": {
            "url": "index_aj2.php",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [0, 3, 4, 5, 6], "className": "text-center" },
            { "targets": [], "visible": false } // Oculta a coluna 5
        ],
        language: {
            url: '../includes/pt-BR.json',
        },
    });
}

function constroiTabela_modelos() {
    dataTableModelos = new DataTable('#tabelaModelos', {
        "processing": false,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": 10, // Define a quantidade de linhas
        "ajax": {
            "url": "index_aj3.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 1, 2, 3, 4],
            "className": "text-center"
        }],
        language: {
            url: '../includes/pt-BR.json',
        },
    });
}

function f_ver_solicitacao(id) {
    $.post("../includes/rh_equipamentos_aj3.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);

        $("#view_responsavel").html(resposta.nome);
        $("#view_usuario").html(resposta.usuario_final);
        $("#view_equipamentos").html(resposta.equipamentos);
        $("#view_observacoes").html(resposta.observacao);
        $("#view_criado_em").html(resposta.criado_em);
        $("#view_criado_por").html(resposta.criado_por);
        $("#view_glpi").html(resposta.glpi_id);
        // 
    });
    verModalSolic.show();
}

function f_incluir_termo() {
    incModalTermos.show();
}

function f_incluir_modelo() {
    incModalModelo.show();
}

function f_incluir_modelo_salvar() {
    let mensagem = $("#msgAlertaModelo");
    let botoes = $("#divBotoesModelo");
    let nome = $("#inc_nome").val();
    let html = $("#inc_html").val();

    if (nome == "" || html == "") {
        alert("Preencha todos os campos obrigatórios.");
        return;
    }
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    botoes.hide();
    $.post("index_aj4.php", { nome: nome, html: html }, function (data) {
        dados = JSON.parse(data);
        mensagem.html(dados.msg);
        if (dados.status == true) {
            setTimeout(() => {
                window.location.reload();
            }, 3000); // Ajuste o tempo conforme necessário
        } else {
            alert("Erro ao incluir modelo: " + data);
            mensagem.html("");
            botoes.show();
        }
    });
}

function f_ver_modelo(id) {
    $.post("index_aj5.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        $('#view_nome').text(resposta.dados.nome);
        $('#view_html').html(resposta.dados.texto);
        verModalModelo.show();
    });
}

function f_editar_modelo(id) {
    $.post("index_aj5.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        $('#edit_id').val(resposta.dados.id);
        $('#edit_nome').val(resposta.dados.nome);
        $('#edit_html').summernote('code', resposta.dados.texto);
        altModalModelo.show();
    });
}

function f_editar_modelo_salvar() {
    let mensagem = $("#msgAlertaAltModelo");
    let botoes = $("#divBotoesAltModelo");
    let id = $("#edit_id").val();
    let nome = $("#edit_nome").val();
    let html = $("#edit_html").val();

    if (nome == "" || html == "") {
        alert("Preencha todos os campos obrigatórios.");
        return;
    }
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    botoes.hide();
    $.post("index_aj6.php", { id: id, nome: nome, html: html }, function (data) {
        dados = JSON.parse(data);
        mensagem.html(dados.msg);
        if (dados.status == true) {
            setTimeout(() => {
                window.location.reload();
            }, 3000); // Ajuste o tempo conforme necessário
        } else {
            alert("Erro ao incluir modelo: " + data);
            mensagem.html("");
            botoes.show();
        }
    });
}

function buscarReservaEquip() {
    let id = $("#inc_termo_reserva").val();
    if (id == "") {
        alert("Informe o número da reserva.");
        return;
    }
    $.post("index_aj7.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        if (resposta.status == true) {
            $("#termo_inc_nome").val(resposta.dados.nome);
            $("#termo_idPessoa").val(resposta.dados.idPessoa);
            $("#termo_cpf").val(resposta.dados.cpf || "");
            $("#termo_glpi").html(resposta.dados.glpi_id);
            $("#termo_lista").html(resposta.dados.equipamentos);
            $("#termo_endereco").val(resposta.dados.logradouro);
            $("#termo_end_nro").val(resposta.dados.numero);
            $("#termo_end_cpl").val(resposta.dados.complemento);
            $("#termo_bairro").val(resposta.dados.bairro);
            $("#termo_cidade").val(resposta.dados.cidade);
            $("#termo_uf").val(resposta.dados.uf);
            $("#termo_cep").val(resposta.dados.cep);
            $("#termo_email").val(resposta.dados.email);
            $("#termo_telefone").val(resposta.dados.telefone);
        } else {
            alert(resposta.msg);
        }
    });
}

function f_termo_atualiza_endereco(id) {
    $.post("index_aj8.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        if (resposta.status == true) {
            //$("#termo_inc_nome").val(resposta.dados.nome);
            //$("#termo_idPessoa").val(resposta.dados.idPessoa);
            //$("#termo_glpi").html(resposta.dados.glpi_id);
            //$("#termo_lista").html(resposta.dados.equipamentos);
            $("#termo_cpf").val(resposta.dados.cpf);
            $("#termo_endereco").val(resposta.dados.logradouro);
            $("#termo_end_nro").val(resposta.dados.numero);
            $("#termo_end_cpl").val(resposta.dados.complemento);
            $("#termo_bairro").val(resposta.dados.bairro);
            $("#termo_cidade").val(resposta.dados.cidade);
            $("#termo_uf").val(resposta.dados.uf);
            $("#termo_cep").val(resposta.dados.cep);
            $("#termo_email").val(resposta.dados.email);
            $("#termo_telefone").val(resposta.dados.telefone);
        } else {
            alert(resposta.msg);
        }
    });
}

function f_add_item_termo() {
    fetch('index_aj9.php')
        .then(response => response.text())
        .then(html => {
            let novoItem = `
            <div class="row g-2 mt-2 item-termo">
                <div class="col-sm-4">
                    ${html} <!-- Aqui virá o <select> vindo do PHP -->
                </div>
                <div class="col-sm-4">
                    <input type="text" class='form-control' name='modelo[]' placeholder="Modelo...">
                </div>
                <div class="col-sm-3">
                    <input type="text" class='form-control' name='patrimonio[]' placeholder="Patrimônio...">
                </div>
                <div class="col-sm-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="remover_item(this)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>`;

            document.getElementById('itens_termo').insertAdjacentHTML('beforeend', novoItem);
        })
        .catch(err => console.error('Erro ao carregar select:', err));
}

function remover_item(botao) {
    botao.closest('.item-termo').remove();
}

function f_incluir_termo_salvar() {
    let botoes = $("#divBotoesTermo");
    let erros = [];

    // Campos obrigatórios principais
    let idPessoa = document.getElementById('termo_idPessoa').value.trim();
    let idModelo = document.getElementById('termo_idModelo').value.trim();
    let nome = document.getElementById('termo_inc_nome').value.trim();
    let endereco = document.getElementById('termo_endereco').value.trim();
    let cidade = document.getElementById('termo_cidade').value.trim();
    let uf = document.getElementById('termo_uf').value.trim();
    let email = document.getElementById('termo_email').value.trim();
    let telefone = document.getElementById('termo_telefone').value.trim();

    //if (!idPessoa) erros.push("Selecione uma pessoa responsável.");
    if (!idModelo) erros.push("O Modelo do termo é obrigatério.");
    if (!nome) erros.push("O nome do responsável é obrigatério.");
    if (!endereco) erros.push("O endereço é obrigatério.");
    if (!cidade) erros.push("A cidade é obrigatéria.");
    if (!uf) erros.push("A UF é obrigatéria.");
    if (!email) erros.push("O e-mail é obrigatério.");
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) erros.push("E-mail inválido.");
    if (!telefone) erros.push("O telefone é obrigatério.");

    // Validação dos itens do termo
    let itens = document.querySelectorAll('#itens_termo .item-termo');
    if (itens.length === 0) {
        erros.push("Adicione ao menos um item ao termo.");
    } else {
        itens.forEach((item, index) => {
            let tipo = item.querySelector('select[name="idTipoEquip[]"]').value.trim();
            let modelo = item.querySelector('input[name="modelo[]"]').value.trim();
            let patrimonio = item.querySelector('input[name="patrimonio[]"]').value.trim();

            if (!tipo) erros.push(`Item ${index + 1}: Tipo obrigatério.`);
            if (!modelo) erros.push(`Item ${index + 1}: Modelo obrigatério.`);
            if (!patrimonio) erros.push(`Item ${index + 1}: Patrimônio/Serial obrigatério.`);
        });
    }

    // Se houver erros, exibe na tela e interrompe
    if (erros.length > 0) {
        document.getElementById('msgAlertaTermo').innerHTML =
            `<div class="alert alert-danger text-start">${erros.join("<br>")}</div>`;
        setTimeout(function () {
            document.getElementById('msgAlertaTermo').innerHTML = '';
        }, 3000); // 5000 ms = 5 segundos
        return;
    }

    botoes.addClass("d-none").hide();
    document.getElementById('msgAlertaTermo').innerHTML =
        "<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>";

    // Monta dados para envio via AJAX
    let formData = new FormData(document.getElementById('formIncTermo'));

    fetch('index_aj10.php', { // Troque para seu arquivo PHP que irá salvar
        method: 'POST',
        body: formData
    })
        .then(resp => resp.json())
        .then(resposta => {
            document.getElementById('msgAlertaTermo').innerHTML = resposta.msg;
            if (resposta.status) {
                // Sucesso: fechar modal ou limpar formulário
                setTimeout(() => {
                    document.getElementById('msgAlertaTermo').innerHTML = '';
                    //bootstrap.Modal.getInstance(document.getElementById('modalIncTermos')).hide();
                    window.location.reload();
                }, 3000);
            }
        })
        .catch(() => {
            document.getElementById('msgAlertaTermo').innerHTML =
                `<div class="alert alert-danger">Erro ao salvar o termo!</div>`;
        });
}

function f_adicionar_vistoria() {
    $("#divEndereco, #divItensTermo, #botaoAdicionarVistoria, #divBaixa").hide();
    $("#botaoVoltarTermo, #divObservacoes").show();
}

function f_mostrar_endereco() {
    $("#divEndereco, #divItensTermo, #botaoAdicionarVistoria, #divUpload").show();
    $("#botaoVoltarTermo, #divObservacoes, #divBaixa").hide();
}

function pesquisarCEP() {
    let cep = document.getElementById('termo_cep').value;

    // Remove tudo que não for número
    cep = cep.replace(/\D/g, "");

    // Garante que tenha exatamente 8 dígitos
    if (!/^\d{8}$/.test(cep)) {
        alert("CEP inválido. Digite um CEP com 8 números.");
        return;
    }

    let url = `https://viacep.com.br/ws/${cep}/json/`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                alert("CEP não encontrado.");
                return;
            }
            document.getElementById('termo_endereco').value = data.logradouro || "";
            document.getElementById('termo_bairro').value = data.bairro || "";
            document.getElementById('termo_cidade').value = data.localidade || "";
            document.getElementById('termo_uf').value = data.uf || "";
        })
        .catch(error => console.error("Erro ao buscar CEP:", error));
}

function testar_cpf(o) {
    if (validarCPF(o.value)) {
        o.style.border = '1px solid green';
    } else {
        o.style.border = '1px solid red';
        alert('CPF inválido!');
        o.value = '';
        o.focus();
    }
}

function f_select_status_termo(o) {
    let botaoDadosBaixa = document.getElementById("botaoDadosBaixa");
    if (o.value === "Baixado") {
        botaoDadosBaixa.style.display = "block";
    } else {
        botaoDadosBaixa.style.display = "none";
    }
}

function f_dados_baixa() {
    $("#divEndereco, #divItensTermo, #botaoAdicionarVistoria, #divObservacoes, #divUpload").hide();
    $("#botaoVoltarTermo, #divBaixa").show();
}

function f_ver_termo(id) {
    $.post("../includes/rh_equipamentos_aj5.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        
        // Preenchimento dos campos...
        $("#vw_termo_responsavel").html(resposta.nome);
        $("#vw_termo_endereco").html(resposta.endereco);
        $("#vw_termo_email").html(resposta.termo_email);
        $("#vw_termo_celular").html(resposta.termo_celular);
        $("#vw_termo_equipamentos").html(resposta.tabela);
        $("#vw_termo_criado_em").html(resposta.criado_em);
        $("#vw_termo_criado_por").html(resposta.criado_por);
        $("#vw_termo_status").html(resposta.dsStatus);
        //
        $("#vw_termo_recebido_em").html(resposta.data_devolucao);
        $("#vw_termo_recebido_por").html(resposta.user_devolucao);
        $("#vw_termo_arquivo").html(resposta.arquivo);
        $("#vw_termo_vistoria").html(resposta.vistoria_devolucao);
        //
        const arquivo = String(resposta.arquivo || "").trim();
        if (arquivo) {
            let filename = "../docs_view.php?pessoa=" + resposta.idPessoa + "&arquivo=" + encodeURIComponent(arquivo);
            $("#vw_termo_documento").val(filename);
            $("#view_documento").html('<embed src="' + filename + '" type="application/pdf" width="100%" height="400px" />');
        } else {
            $("#vw_termo_documento").val("");
            $("#view_documento").html('<div class="alert alert-warning mb-0">Documento não encontrado para este termo.</div>');
        }
        
        // Atribui o ID aos botões de ação para uso posterior
        $("#btnExcluirTermo").off('click').on('click', function() { f_excluir_termo(id); });
        $("#btnEditarTermo").off('click').on('click', function() { f_editar_termo(id, resposta); });
        
        verModalTermo.show();
    });
}

// Função para Excluir
function f_excluir_termo(id) {
    if (confirm("Deseja realmente excluir este termo? Esta ação não pode ser desfeita.")) {
        $.post("index_aj17.php", { id: id }, function(data) {
            let res;
            try {
                res = (typeof data === "string") ? JSON.parse(data) : data;
            } catch (e) {
                alert("Erro inesperado ao excluir termo. Resposta inválida do servidor.");
                return;
            }
            
            if (!res || typeof res !== "object") {
                alert("Erro inesperado ao excluir termo. Resposta vazia.");
                return;
            }
            alert(res.msg);
            if (res.status) {
                verModalTermo.hide();
                location.reload(); // Recarrega a grid
            }
        }).fail(function(xhr) {
            alert("Falha na requisição de exclusão (" + xhr.status + ").");
        });
    }
}

function f_editar_termo(id) {
    // 1. Fecha a modal de visualização
    verModalTermo.hide();

    // 2. Busca dados atualizados do termo
    $.post("../includes/rh_equipamentos_aj5.php", { id: id }, function (data) {
        let res = (typeof data === "string") ? JSON.parse(data) : data;

        // 3. Popula campos básicos
        $("#alt_id_termo").val(id);
        $("#alt_termo_reserva").val(res.nro_reserva || "");
        $("#alt_termo_nome").val(res.nome);
        $("#alt_termo_idPessoa").val(res.idPessoa);
        $("#alt_termo_glpi").val(res.glpi_id);
        $("#alt_termo_idModelo").val(res.idModelo);
        $("#alt_termo_cpf").val(res.cpf);
        $("#alt_termo_email").val(res.termo_email);
        $("#alt_termo_cep").val(res.termo_cep);
        $("#alt_termo_endereco").val(res.termo_endereco);
        $("#alt_termo_end_nro").val(res.termo_end_numero);
        $("#alt_termo_bairro").val(res.termo_bairro);
        $("#alt_termo_status").val(res.status);

        // 4. Popula os Itens
        $("#alt_itens_termo").html("");
        const itens = (res.itens && res.itens.length > 0) ? res.itens : extrair_itens_da_tabela(res.tabela);
        itens.forEach(function(item) {
            f_add_item_alteracao(item.tipo, item.modelo, item.patrimonio);
        });

        // 5. Abre a modal de alteração
        altModalTermos.show();
    });
}

function extrair_itens_da_tabela(tabelaHtml) {
    let itens = [];
    if (!tabelaHtml) return itens;

    const tabela = $("<div>").html(tabelaHtml);
    tabela.find("tr").each(function () {
        const colunas = $(this).find("td");
        if (colunas.length === 3) {
            itens.push({
                tipo: $(colunas[0]).text().trim(),
                modelo: $(colunas[1]).text().trim(),
                patrimonio: $(colunas[2]).text().trim()
            });
        }
    });

    return itens;
}

// Função auxiliar para adicionar linhas de itens na alteração
function f_add_item_alteracao(tipo = "", modelo = "", patrimonio = "") {
    const esc = (v) => String(v || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");

    let opcoes = $("#itens_termo select[name=\"idTipoEquip[]\"]").first().html() ||
        '<option value="" selected>-- selecione um tipo --</option>';

    let html = `
        <div class="row g-2 mt-2 item-termo-alt">
            <div class="col-sm-4">
                <select class="form-select" name="alt_idTipoEquip[]">${opcoes}</select>
            </div>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="alt_modelo[]" value="${esc(modelo)}">
            </div>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="alt_patrimonio[]" value="${esc(patrimonio)}">
            </div>
            <div class="col-sm-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="remover_item_alteracao(this)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;

    const $row = $(html);
    const $select = $row.find("select[name=\"alt_idTipoEquip[]\"]");
    const tipoNorm = String(tipo || "").trim().toLowerCase();
    let selecionado = false;

    $select.find("option").each(function () {
        const txt = $(this).text().trim().toLowerCase();
        const val = String($(this).val() || "").trim().toLowerCase();
        if (tipoNorm && (txt === tipoNorm || val === tipoNorm)) {
            $(this).prop("selected", true);
            selecionado = true;
            return false;
        }
    });

    if (!selecionado && tipoNorm) {
        $select.append(`<option value="" selected>${esc(tipo)}</option>`);
    }

    $("#alt_itens_termo").append($row);
}

function remover_item_alteracao(botao) {
    $(botao).closest(".item-termo-alt").remove();
}

function f_salvar_alteracao_termo() {
    let id = $("#alt_id_termo").val();
    let idPessoa = $("#alt_termo_idPessoa").val();
    let idModelo = $("#alt_termo_idModelo").val();
    let email = $("#alt_termo_email").val();
    let endereco = $("#alt_termo_endereco").val();
    let numero = $("#alt_termo_end_nro").val();
    let bairro = $("#alt_termo_bairro").val();
    let cep = $("#alt_termo_cep").val();
    let status = $("#alt_termo_status").val();
    let erros = [];

    if (!id) erros.push("ID do termo não informado.");
    if (!idPessoa) erros.push("Pessoa responsável não informada.");
    if (!idModelo) erros.push("Modelo do termo é obrigatório.");
    if (!email) erros.push("E-mail é obrigatório.");
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) erros.push("E-mail inválido.");
    if (!endereco) erros.push("Endereço é obrigatório.");
    if (!numero) erros.push("Número do endereço é obrigatório.");
    if (!bairro) erros.push("Bairro é obrigatório.");
    if (!cep) erros.push("CEP é obrigatório.");
    if (!status) erros.push("Status é obrigatório.");

    let itens = $("#alt_itens_termo .item-termo-alt");
    if (itens.length === 0) {
        erros.push("Adicione ao menos um item no termo.");
    } else {
        itens.each(function (idx) {
            let tipo = $(this).find("select[name=\"alt_idTipoEquip[]\"]").val();
            let modelo = $(this).find("input[name=\"alt_modelo[]\"]").val();
            let patrimonio = $(this).find("input[name=\"alt_patrimonio[]\"]").val();

            if (!tipo) erros.push(`Item ${idx + 1}: tipo obrigatório.`);
            if (!modelo || !String(modelo).trim()) erros.push(`Item ${idx + 1}: modelo obrigatório.`);
            if (!patrimonio || !String(patrimonio).trim()) erros.push(`Item ${idx + 1}: patrimônio obrigatório.`);
        });
    }

    if (erros.length > 0) {
        alert(erros.join("\n"));
        return;
    }

    const botaoSalvar = $("#modalAltTermos .btn-success");
    botaoSalvar.prop("disabled", true).html("Salvando...");

    let formData = new FormData(document.getElementById("formAltTermo"));

    fetch("index_aj16.php", {
        method: "POST",
        body: formData
    })
        .then(resp => resp.json())
        .then(res => {
            alert(res.msg || "Operação concluída.");
            if (res.status) {
                window.location.reload();
            } else {
                botaoSalvar.prop("disabled", false).html("Salvar Alterações");
            }
        })
        .catch(() => {
            alert("Erro ao salvar alteração do termo.");
            botaoSalvar.prop("disabled", false).html("Salvar Alterações");
        });
}

function f_preview_documento() {
    let filename = $("#vw_termo_documento").val();
    if (filename) {
        window.open(filename, '_blank'); // abre em nova guia
    } else {
        alert("Nenhum documento disponível para visualização.");
    }
}

function f_baixar_termo(id) {
    let bxa_termo_responsavel = $("#bxa_termo_responsavel");
    $.post("../includes/rh_equipamentos_aj5.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        bxa_termo_responsavel.html(resposta.nome);
        $("#bxa_termo_id").val(id);
        $("#bxa_termo_origem").val(resposta.origem);
        //
        if (resposta.origem === 'e') {
            $("#divUploadBxaTermo").addClass("d-none").hide();
        }
        bxaModalTermo.show();
    });

}

function f_incluir_bxa_termo_salvar() {
    let fileInput = document.getElementById("termo_arquivo_bxa");
    let bxa_termo_data_dev = $("#bxa_termo_data_dev").val();
    let bxa_termo_vistoriador = $("#bxa_termo_vistoriador").val();
    let bxa_termo_observacoes_dev = $('#bxa_termo_observacoes_dev').summernote('code');
    let bxa_termo_id = $("#bxa_termo_id").val();
    let bxa_termo_origem = $("#bxa_termo_origem").val();

    let botoes = $("#botoes_bxa_termo");
    let mensagem = $("#msgAlertaBxaTermo");

    //
    // validações básicas
    if (!bxa_termo_data_dev) {
        alert("Informe a data de devolução");
        $("#bxa_termo_data_dev").focus();
        return;
    }

    if (!bxa_termo_vistoriador) {
        alert("Informe o vistoriador");
        $("#bxa_termo_vistoriador").focus();
        return;
    }

    if (!bxa_termo_observacoes_dev || bxa_termo_observacoes_dev === "<p><br></p>") {
        alert("Informe as observações");
        $('#bxa_termo_observacoes_dev').summernote('focus');
        return;
    }

    // regra especial: se origem = 'd', arquivo é obrigatério
    if (bxa_termo_origem === 'd') {
        if (!fileInput.files || fileInput.files.length === 0) {
            alert("É necessário anexar o termo de devolução");
            fileInput.focus();
            return;
        }
    }
    //
    botoes.addClass("d-none").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");

    if (fileInput.files.length > 0) {
        // --- Tem arquivo, envia para o PHP ---
        let formData = new FormData();
        formData.append("termo_arquivo_bxa", fileInput.files[0]);
        formData.append("bxa_termo_data_dev", bxa_termo_data_dev);
        formData.append("bxa_termo_vistoriador", bxa_termo_vistoriador);
        formData.append("bxa_termo_observacoes_dev", bxa_termo_observacoes_dev);
        formData.append("bxa_termo_id", bxa_termo_id);

        fetch("index_aj13.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                //console.log(data);
                //alert("Arquivo enviado com sucesso!");
                // você pode também atualizar mensagem na tela
                $("#msgAlertaBxaTermo").html(data.msg);
                setTimeout(() => {
                    //window.location.reload();
                }, 3000); // Ajuste o tempo conforme necessário
            })
            .catch(error => {
                console.error("Erro no envio:", error);
                alert("Erro ao enviar o arquivo!");
            });

    } else {
        // --- Não tem arquivo, abre modal ---
        botoes.hide();
        mensagem.html("Confirme a assinatura eletrônica para concluir a baixa do termo");
        $("#idTermo").val(bxa_termo_id);
        assModalTermo.show();
    }
}

function fechar_assinatura() {
    let botoes = $("#botoes_bxa_termo");
    let mensagem = $("#msgAlertaBxaTermo");
    //
    botoes.removeClass("d-none").show();
    mensagem.html("");
}

function confirma_assinatura() {
    let mensagem = $("#msgAlerta");
    let usuario = $("#usuario").val();
    let senha = $("#senha").val();
    let idTermo = $("#idTermo").val();
    //
    let bxa_termo_data_dev        = $("#bxa_termo_data_dev").val();
    let bxa_termo_vistoriador     = $("#bxa_termo_vistoriador").val();
    let bxa_termo_observacoes_dev = $('#bxa_termo_observacoes_dev').summernote('code');
    let bxa_termo_origem          = $("#bxa_termo_origem").val();     
    //
    $.post("index_aj14.php", {
        idTermo: idTermo,
        usuario: usuario,
        senha: senha,
        data_dev: bxa_termo_data_dev,
        vistoriador: bxa_termo_vistoriador,
        observacoes_dev: bxa_termo_observacoes_dev,
        origem: bxa_termo_origem

    }, function (res) {
        let dados = JSON.parse(res);
        mensagem.html(dados.msg);
        setTimeout(() => {
            /*
            $("#modalAssinar").modal("hide");
            $("#divPrincipal").modal("hide");
            $("#divObrigado").modal("show");
            */
           window.location.reload();
        }, 3000);
    });

}

document.getElementById("toggleSenha").addEventListener("click", function () {
    const senha = document.getElementById("senha");
    const icon = document.getElementById("iconSenha");

    if (senha.type === "password") {
        senha.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        senha.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
});

function f_alterar_status(id, novoStatus) {
    console.log("Alterando ID " + id + " para " + novoStatus);

    fetch('index_aj15.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&status=${novoStatus}`
    })
    .then(response => response.json()) // Converte a resposta para JSON
    .then(data => {
        if (data.status) {
            // Caso tenha dado certo (status: true)
            console.log("Sucesso:", data.msg);
            alert("Sucesso: Status atualizado para " + novoStatus);
            window.document.location.reload();
        } else {
            // Caso o PHP retorne erro (status: false)
            alert("Erro: " + (data.msg || "Não foi possível alterar."));
        }
    })
    .catch(error => {
        alert("Erro crítico na requisição.");
        console.error("Erro no Fetch:", error);
    });
}




