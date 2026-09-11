var dataTable;
var linhasPorPagina = 12;

const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));

$(document).ready(function () {
    //$("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário

    $('#relato').summernote({
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
            "url": "rh_ouvidoria_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 1, 3, 4, 5, 6],
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

function ficha(id) {
    let url = "rh_ficha_ouvidoria.php?id=" + id;
    let win = window.open(url, '_blank');
    win.focus();
}

function excluir(id) {
    // pede o motivo
    let motivo = prompt("Informe o motivo da exclusão:");
    let mensagem = $("#divAlertaAcolhimento");

    // se clicou em cancelar ou não digitou nada
    if (motivo === null || motivo.trim() === "") {
        alert("Exclusão cancelada: é necessário informar o motivo.");
        return;
    }

    // confirma se o usuário realmente quer excluir
    if (confirm("Tem certeza que deseja excluir? Esta ação não poderá ser desfeita!")) {
        // aqui você chama o backend para processar a exclusão
        // por exemplo, via fetch/AJAX

        mensagem.html("<div class='alert alert-warning'>Excluindo...</div>");
        fetch("includes/rh_ouvidoria_exc_aj.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + encodeURIComponent(id) + "&motivo=" + encodeURIComponent(motivo)
        })
            .then(response => response.text())
            .then(data => {
                let dados = JSON.parse(data);
                mensagem.html(dados.msg);
                setTimeout(() => {
                    location.reload(); // recarrega a página para atualizar a lista
                }, 3000); // Ajuste o tempo conforme necessário
            })
            .catch(error => console.error("Erro:", error));

    }
}

function fechar(id) {
    // pede o motivo
    let motivo = prompt("Informe o motivo da fechamento da Denúncia/Acolhimento:");
    let mensagem = $("#divAlertaAcolhimento");

    // se clicou em cancelar ou não digitou nada
    if (motivo === null || motivo.trim() === "") {
        alert("Fechamento cancelado: é necessário informar o motivo.");
        return;
    }

    // confirma se o usuário realmente quer excluir
    if (confirm("Tem certeza que deseja fechar? Esta ação não poderá ser desfeita!")) {
        // aqui você chama o backend para processar a exclusão
        // por exemplo, via fetch/AJAX

        mensagem.html("<div class='alert alert-warning'>Fechando...</div>");
        fetch("includes/rh_ouvidoria_fechar.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + encodeURIComponent(id) + "&motivo=" + encodeURIComponent(motivo)
        })
            .then(response => response.text())
            .then(data => {
                let dados = JSON.parse(data);
                mensagem.html(dados.msg);
                setTimeout(() => {
                    location.reload(); // recarrega a página para atualizar a lista
                }, 3000); // Ajuste o tempo conforme necessário
            })
            .catch(error => console.error("Erro:", error));

    }
}

function incluir() {
    incModal.show();
}

function mostrarIdentificacao(show) {
    $('#dadosIdentificacao').toggle(show);
}

function mostrarTestemunhas(val) {
    $('#campoTestemunhas').toggle(val === 'sim');
}

function mostrarComunicado(val) {
    $('#campoComunicado').toggle(val === 'sim');
}

function requerContato(show) {
    $('#dadosContato').toggle(show);
}

function atualizaContato() {
    if ($('#identificado').is(':checked')) {
        $('#nomeContato').val($('input[name="nome"]').val());
        $('#emailContato').val($('input[name="email"]').val());
        $('#telefoneContato').val($('input[name="telefone"]').val());
    }
}

function enviar() {
    // Verifica tipo de assédio
    var tipo = $('select[name="tipoAssedio"]').val();
    if (!tipo) {
        alert("Por favor, selecione o tipo de assédio.");
        $('select[name="tipoAssedio"]').focus();
        return;
    }

    // Verifica o campo relato
    var relato = $('#relato').summernote('isEmpty') ? '' : $('#relato').summernote('code');
    if (relato.trim() === '' || $('<div>').html(relato).text().trim() === '') {
        alert("Por favor, descreva o ocorrido.");
        $('#relato').summernote('focus');
        return;
    }

    // Se for identificado, validar nome
    if ($('#identificado').is(':checked')) {
        var nome = $('input[name="nome"]').val().trim();
        if (!nome) {
            alert("Por favor, preencha seu nome.");
            $('input[name="nome"]').focus();
            return;
        }
    }

    // Se acompanhamento = sim, validar email ou telefone
    if ($('#contatoSim').is(':checked')) {
        var email = $('#emailContato').val().trim();
        var telefone = $('#telefoneContato').val().trim();
        if (!email && !telefone) {
            alert("Para acompanhamento, informe pelo menos um meio de contato (email ou telefone).");
            $('#emailContato').focus();
            return;
        }
    }

    // Envia via AJAX
    $.ajax({
        url: "includes/rh_ouvidoria_inc_aj.php",
        type: "POST",
        data: $("#denunciaForm").serialize(),
        success: function(response) {
            let dados = JSON.parse(response);
            alert(dados.msg);
            setTimeout(() => {
                location.reload(); // recarrega a página para atualizar a lista
            }, 3000); // Ajuste o tempo conforme necessário
        },
        error: function(xhr, status, error) {
            alert("Ocorreu um erro ao enviar. Tente novamente.");
            console.error(error);
        }
    });
}
