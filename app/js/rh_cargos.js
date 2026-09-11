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

    dataTable = new DataTable('#example', {
        "processing": true,
        "serverSide": false,
        "order": [0, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_cargos_aj.php",
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
    let mensagem = $("#msgAlertaIncluir");
    if ($("#_nome").val() === "") {
        alert("Por favor, informe o nome do Cargo.");
        $("#_nome").focus();
        return false;
    }

    if ($("#_nivel").val() === "") {
        alert("Por favor, informe o Nível do Cargo.");
        $("#_nivel").focus();
        return false;
    }

    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#_descricao").val($("#_descricao").summernote('code'));

    if ($("#_descricao").val() === "") {
        alert("Por favor, informe a descrição do Cargo.");
        $("#_descricao").focus();
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
        url: "includes/rh_cargo_aj2.php",
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
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#divAlertaCargo").show();
        $.post("includes/rh_cargo_aj3.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#divAlertaCargo").html(dados.msg);
            //
            setTimeout(function () {
                $("#divAlertaCargo").html("");
                selecionou();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function f_editar(id) {
    //
    $.post("includes/rh_cargo_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #e_idCargo").val(id);
        $("#formEditar #e_nome").val(dados.nome);
        $("#formEditar #e_nivel").val(dados.nivel);
        //$("#formEditar #e_descricao").val(dados.descricao);
        $("#formEditar #e_descricao").summernote('code', dados.descricao);
        //
        setAtivoStatus(dados.ativo);
    });
}

function f_editar_commit() {
    //
    const nome = $("#formEditar #e_nome");
    const nivel = $("#formEditar #e_nivel");
    const descricao = $("#formEditar #e_descricao");
    let mensagem = $("#msgAlertaEditar");
    //
    if (nome.val() == "") {
        nome.focus();
        alert("Informe o nome do cargo");
        return;
    }
    //
    if (nivel.val() == "") {
        nivel.focus();
        alert("Informe o nível na estrutura");
        return;
    }
    //
    if (descricao.val() == "0") {
        descricao.focus();
        alert("Descreva o cargo");
        return;
    }
    //
    $("#botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");    
    //
    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#e_descricao").val($("#e_descricao").summernote('code'));

    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditar"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_cargo_aj4.php",
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

    $.post("includes/rh_cargo_aj1.php", { id: id, origem: 'visualizar' }, function (retorno) {
        const dados = JSON.parse(retorno);

        $("#v_nome").html(dados.nome);
        $("#v_nivel").text(dados.nivel);
        $("#v_descricao").html(dados.descricao);
        // Abrir o modal de visualização
        visModal.show();
    });

}

function f_limpar() {
    // Limpa os campos do formulário
    $("#_nome").val(""); // Substitua #_nome pelo ID do campo de nome
    $("#_nivel").val(""); // Substitua #_nivel pelo ID do campo de nível
    $("#_descricao").summernote('code', ""); // Limpa o conteúdo do Summernote

    // Exibe uma mensagem (opcional)
    //alert("Campos resetados com sucesso!");
}

function f_reset() {
    //
    let id = $("#formEditar #e_idCargo").val();
    //
    $.post("includes/rh_cargo_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #e_idCargo").val(id);
        $("#formEditar #e_nome").val(dados.nome);
        $("#formEditar #e_nivel").val(dados.nivel);
        $("#formEditar #e_descricao").summernote('code', dados.descricao);
        //
    });
}

function toggleAtivo() {
    let ativo = document.getElementById("e_ativo").checked ? 1 : 0;
    $("#e_ativo").val(ativo);
    $("#labelAtivo").text(ativo ? "Ativo" : "Inativo");
}

function setAtivoStatus(ativo) {
    if (ativo == 1) {
        $("#e_ativo").prop("checked", true).val(1);
        $("#labelAtivo").text("Ativo");
    } else {
        $("#e_ativo").prop("checked", false).val(0);
        $("#labelAtivo").text("Inativo");
    }
}