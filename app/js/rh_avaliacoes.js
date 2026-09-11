var dataTable;
var dataTable_medias;

var linhasPorPagina = 12;

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const incModal = new bootstrap.Modal(document.getElementById("modalGerarAvaliacoes"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
        constroiTabelaMedias();
    }, 300); // Ajuste o tempo conforme necessário

    $('#_descricao, #e_descricao').summernote({
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

    dataTable = new DataTable('#tabela', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_avaliacoes_aj1.php",
            "type": "POST",
            "data": function (d) {
                d.data_ini = $("#data_ini").val();
                d.data_fim = $("#data_fim").val();
            }
        },
        "columnDefs": [{
            "targets": [0, 1, 2, 3, 4, 5, 6],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}

function constroiTabelaMedias() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if (alturaTotal == alturaViewport) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }

    dataTable_medias = new DataTable('#tabela_media', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_avaliacoes_aj2.php",
            "type": "POST",
            "data": function (d) {
                d.data_ini = $("#data_ini").val();
                d.data_fim = $("#data_fim").val();
            }
        },
        "columnDefs": [{
            "targets": [0,1, 2,3],
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
    dataTable_medias.clear().draw();
    dataTable.destroy();
    dataTable_medias.destroy();
    constroiTabela();
    constroiTabelaMedias();
    //
}

window.onresize = selecionou; // Atualiza quando a tela for redimensionada

function f_incluir() {
    incModal.show();
    setTimeout(() => {
        //$("#_nome").focus();
    }, 1000); // Ajuste o tempo conforme necessário
    return true;
}

function f_visualizar(id) {

    $.post("includes/rh_cargo_aj1.php", { id: id, origem: 'visualizar' }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_nome").html(dados.nome);
        $("#v_nivel").text(dados.nivel);
        $("#v_descricao").html(dados.descricao);
        // Abrir o modal de visualização
        visModal.show();
    });

}

//
// AUTOCOMPLETE PARA GERAR A LISTA
//

let colabsSelecionados = [];

$("#_nome").autocomplete({
    source: function (request, response) {
        $.ajax({
            url: "includes/buscar_colab.php",
            type: "GET",
            dataType: "json",
            data: { term: request.term },
            success: function (data) {
                response($.map(data, function (item) {
                    return {
                        label: item.nome,
                        value: item.nome,
                        id: item.idColab   // usa id para evitar undefined
                    };
                }));
            }
        });
    },
    minLength: 2,
    select: function (event, ui) {
        inserirColab(ui.item.id, ui.item.label);

        // limpa o campo e mantém o foco para próxima busca
        $("#_nome").val("").focus();

        // impede o autocomplete de jogar o nome de volta no campo
        return false;
    }
});


// Função para inserir no container
function inserirColab(id, nome) {
    if (colabsSelecionados.includes(id)) return; // evita duplicados
    colabsSelecionados.push(id);

    // remove placeholder se for o primeiro
    $("#listaColabs small").remove();

    let badge = `
        <span class="badge bg-primary p-2 m-1 d-inline-flex align-items-center">
            ${nome}
            <i class="fa-solid fa-xmark ms-2" style="cursor:pointer" onclick="removerColab('${id}')"></i>
            <input type="hidden" name="idColab[]" value="${id}">
        </span>
    `;

    $("#listaColabs").append(badge);
}

function incluir_todos() {
    $.ajax({
        url: "includes/rh_avaliacoes_aj3.php",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (!Array.isArray(data)) {
                alert("Erro ao carregar colaboradores.");
                return;
            }

            data.forEach(colab => {
                inserirColab(colab.idColab, colab.nome);
            });
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Erro na comunicação com o servidor.");
        }
    });
}

function gerar_avaliacoes() {
    let idTipo = $("#idTipo").val();
    // pega todos os IDs dos inputs hidden já inseridos
    let ids = $("input[name='idColab[]']").map(function () {
        return $(this).val();
    }).get();

    if (ids.length === 0) {
        alert("Nenhum colaborador selecionado!");
        return;
    }

    // tipo de avaliação (por enquanto fixo = 1, simples - 90º)
    let tipo_avaliacao = 1;

    $.ajax({
        url: "includes/rh_avaliacoes_aj4.php",
        type: "POST",
        dataType: "json",
        data: {
            idTipo: idTipo,
            ids: ids
        },
        success: function (res) {
            if (res.status) {
                alert( res.msg );
                //$("#modalGerarAvaliacoes").modal("hide");
                setTimeout(() => {
                    location.reload();
                }, 300); // Ajuste o tempo conforme necessário
            } else {
                alert("Erro: " + res.msg);
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Erro na comunicação com o servidor.");
        }
    });
}

// Função para remover colaborador
function removerColab(id) {
    colabsSelecionados = colabsSelecionados.filter(c => c != id);
    $(`#listaColabs input[value='${id}']`).parent().remove();

    // se esvaziar, mostra placeholder
    if (colabsSelecionados.length === 0) {
        $("#listaColabs").html('<small class="text-muted">Nenhum colaborador adicionado...</small>');
    }
}

