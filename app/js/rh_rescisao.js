var dataTable;
var linhasPorPagina = 12;

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar"));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário

    $('#formIncluir #_motivo, #formEditar #_motivo').summernote({
        height: 200,
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
                $('.note-editable').css('color', 'white');
            }
        }
    });

    $("#formIncluir #nmPessoa").autocomplete({
        source: function (request, response) {
            //console.log("Termo digitado:", request.term); // << debug aqui
            $.ajax({
                url: "includes/buscar_colab.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    //console.log("Dados recebidos:", data); // << debug aqui
                    response($.map(data, function (item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idPessoa: item.idPessoa,
                            idColab: item.idColab,
                            salario: item.salario
                        };
                    }));
                },
                error: function (xhr, status, error) {
                    console.error("Erro na requisição:", status, error);
                }
            });
        },
        minLength: 2,
        appendTo: "#modalIncluir", // garante que fique dentro da modal
        select: function (event, ui) {
            $("#formIncluir #nmPessoa").val(ui.item.nome);
            $("#formIncluir #idPessoa").val(ui.item.idPessoa);
            $("#formIncluir #idColab").val(ui.item.idColab);
            $("#formIncluir #_saldoSalario").val(ui.item.salario);
        }
    });
    //
    $("#formEditar #nmPessoa").autocomplete({
        source: function (request, response) {
            //console.log("Termo digitado:", request.term); // << debug aqui
            $.ajax({
                url: "includes/buscar_colab.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function (data) {
                    //console.log("Dados recebidos:", data); // << debug aqui
                    response($.map(data, function (item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idPessoa: item.idPessoa,
                            idColab: item.idColab
                        };
                    }));
                },
                error: function (xhr, status, error) {
                    console.error("Erro na requisição:", status, error);
                }
            });
        },
        minLength: 2,
        appendTo: "#modalEditar", // garante que fique dentro da modal
        select: function (event, ui) {
            $("#formEditar #nmPessoa").val(ui.item.nome);
            $("#formEditar #idPessoa").val(ui.item.idPessoa);
            $("#formEditar #idColab").val(ui.item.idColab);
        }
    });

});

function constroiTabela() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTable = new DataTable('#example', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_rescisao_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 3, 4, 5, 6],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
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

function f_incluir() {
    incModal.show();
    setTimeout(() => {
        $("#_nome").focus();
    }, 1000); // Ajuste o tempo conforme necessário
    return true;
}

function f_incluir_commit() {
    // Validação dos campos
    if (!validarCamposObrigatorios('formIncluir')) return;

    let mensagem = $("#msgAlertaIncluir");
    
    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#formIncluir #_motivo").val($("#formIncluir #_motivo").summernote('code'));

    if ($("#formIncluir #_motivo").val() === "") {
        alert("Por favor, informe a descrição da Rescisão.");
        $("#formIncluir #_motivo").focus();
        return false;
    }
    //
    $("#botoes_incluir").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
    //
    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formIncluir"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_rescisao_aj2.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#botoes_incluir").show();
                incModal.hide();
                mensagem.html("");
                f_limpar();
                selecionou();
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
        },
        complete: function () {
            $(".btn-enviar").html('Enviar <i class="fa-solid fa-paper-plane"></i>');
            $(".btn-enviar").prop("disabled", false);
        }
    });
}

function f_excluir(id) {
    let mensagem = $("#msgAlertaRescisao");
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {

        $.post("includes/rh_rescisao_aj3.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            mensagem.html(dados.msg);
            //
            setTimeout(function () {
                mensagem.html("");
                selecionou();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function f_editar(id) {
    //
    $.post("includes/rh_rescisao_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #idRescisao").val(id);
        $("#formEditar #nmPessoa").val(dados.nome);
        $("#formEditar #idPessoa").val(dados.idPessoa);
        $("#formEditar #idColab" ).val(dados.idColab);

        $("#formEditar #idTipoRescisao" ).val(dados.idTipoRescisao);
        $("#formEditar #_tipoAviso" ).val(dados.tipoAviso);
        $("#formEditar #_dtAviso" ).val(dados.dtAviso);
        $("#formEditar #_dtDesligamento" ).val(dados.dtDesligamento);
        $("#formEditar #_status" ).val(dados.status);

        $("#formEditar #_saldoSalario" ).val(dados.saldoSalario);
        $("#formEditar #_feriasVencidas" ).val(dados.feriasVencidas);
        $("#formEditar #_feriasProporcionais" ).val(dados.feriasProporcionais);
        $("#formEditar #_decimoTerceiro" ).val(dados.decimoTerceiro);
        $("#formEditar #_multaFgts" ).val(dados.multaFgts);
        $("#formEditar #_descontos" ).val(dados.descontos);
        $("#formEditar #_totalLiquido" ).val(dados.totalLiquido);

        $("#formEditar #_motivo").summernote('code', dados.motivoRescisao);
        //
    });
}

function f_editar_commit() {
    //
    let mensagem = $("#msgAlertaEditar");
    if (!validarCamposObrigatorios('formEditar')) return;
    //
    $("#botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");    
    //
    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#formEditar #_motivo").val($("#formEditar #_motivo").summernote('code'));

    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditar"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_rescisao_aj4.php",
        type: "POST",
        data: formData,
        processData: false,  // Evita que o jQuery tente converter o FormData em string
        contentType: false,  // Permite que arquivos sejam enviados corretamente
        success: function (response) {
            let dados = JSON.parse(response);
            mensagem.html(dados.msg);
            setTimeout(() => {
                $("#botoes_editar").show();
                mensagem.html("");
                altModal.hide();
                selecionou();
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function (xhr) {
            mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
        },
        complete: function () {
            $(".btn-enviar").html('Enviar <i class="fa-solid fa-paper-plane"></i>');
            $(".btn-enviar").prop("disabled", false);
        }
    });
}

function f_visualizar(id) {

    $.post("includes/rh_rescisao_aj1.php", { id: id, origem: 'visualizar' }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_nmPessoa").html(dados.nome);
        $("#v_idRescisaoTipo").text(dados.dsTipoRescisao);
        $("#v_tipoAviso").html(dados.tipoAviso);
        $("#v_dtAviso").html(dados.dtAviso);
        $("#v_dtDesligamento").html(dados.dtDesligamento);
        $("#v_status").html(dados.status);
        $("#v_saldoSalario").html(dados.saldoSalario);
        $("#v_feriasVencidas").html(dados.feriasVencidas);
        $("#v_feriasProporcionais").html(dados.feriasProporcionais);
        $("#v_decimoTerceiro").html(dados.decimoTerceiro); 
        $("#v_multaFgts").html(dados.multaFgts);
        $("#v_descontos").html(dados.descontos);
        $("#v_totalLiquido").html(dados.totalLiquido);
        $("#v_motivo").html(dados.motivoRescisao);
        //
        // Abrir o modal de visualização
        visModal.show();
    });

}

document.getElementById('modalVisualizar').addEventListener('hide.bs.modal', function () {
    // Garante que nenhum botão ou campo dentro da modal permaneça com foco
    if (document.activeElement && document.getElementById('modalVisualizar').contains(document.activeElement)) {
        document.activeElement.blur();
    }
});

function f_reset() {
    //
    let id = $("#formEditar #idRescisao").val();
    f_editar(id);
}

function f_calcularTotal(formulario) {
    // Função auxiliar para obter o valor numérico ou zero
    function getValor(id) {
        let val = parseFloat($(`#${formulario} #${id}`).val().replace(',', '.'));
        return isNaN(val) ? 0 : val;
    }

    // Pega todos os valores usando os IDs
    let saldoSalario = getValor('_saldoSalario');
    let feriasVencidas = getValor('_feriasVencidas');
    let feriasProporcionais = getValor('_feriasProporcionais');
    let decimoTerceiro = getValor('_decimoTerceiro');
    let multaFgts = getValor('_multaFgts');
    let descontos = getValor('_descontos');

    // Calcula total
    let total = (saldoSalario + feriasVencidas + feriasProporcionais + decimoTerceiro + multaFgts) - descontos;

    // Atualiza o campo _totalLiquido com duas casas decimais
    $(`#${formulario} #_totalLiquido`).val(total.toFixed(2));
}

function validarCamposObrigatorios(formularioId) {
    const camposObrigatorios = [
        { id: 'nmPessoa', nome: 'Colaborador' },
        { id: 'idTipoRescisao', nome: 'Tipo de Rescisão' },
        { id: '_tipoAviso', nome: 'Tipo de Aviso' },
        { id: '_dtAviso', nome: 'Data do Aviso Prévio' },
        { id: '_dtDesligamento', nome: 'Data de Desligamento' },
        { id: '_status', nome: 'Status' },
        { id: '_saldoSalario', nome: 'Saldo de Salários' },
        { id: '_feriasVencidas', nome: 'Férias Vencidas' },
        { id: '_decimoTerceiro', nome: '13º Salário' },
        { id: '_multaFgts', nome: 'Multa do FGTS' },
        { id: '_descontos', nome: 'Descontos' },
    ];

    for (let campo of camposObrigatorios) {
        let el = document.querySelector(`#${formularioId} #${campo.id}`);
        if (!el) {
            alert(`Erro interno: campo "${campo.nome}" (id: ${campo.id}) não encontrado no formulário.`);
            console.error(`Elemento com id "${campo.id}" não encontrado no formulário ${formularioId}.`);
            return false;
        }

        if (el.tagName === 'SELECT' && (el.value === '' || el.value === '0')) {
            alert(`Por favor, selecione uma opção para "${campo.nome}".`);
            el.focus();
            return false;
        }

        if (el.tagName !== 'SELECT' && !el.value.trim()) {
            alert(`Por favor, preencha o campo "${campo.nome}".`);
            el.focus();
            return false;
        }
    }

    // Validação extra: data do aviso não pode ser depois da data de desligamento
    const dtAviso = document.querySelector(`#${formularioId} #_dtAviso`).value;
    const dtDesligamento = document.querySelector(`#${formularioId} #_dtDesligamento`).value;
    if (dtAviso && dtDesligamento && dtAviso > dtDesligamento) {
        alert('A Data de Aviso não pode ser posterior à Data de Desligamento.');
        document.querySelector(`#${formularioId} #_dtAviso`).focus();
        return false;
    }

    return true;
}

function f_limpar() {
    $("#formIncluir")[0].reset();
    $("#formEditar")[0].reset();
    $("#formIncluir #nmPessoa").val("");
    $("#formEditar #nmPessoa").val("");
    $("#formIncluir #idColab").val("");
    $("#formEditar #idColab").val("");
    $("#formIncluir #_motivo").summernote('code', '');
    $("#formEditar #_motivo").summernote('code', '');
}