//
// ferias.js | Módulo: GESTOR
// (C)haia, 18/09/2025. | 21/10/2025
//

$(document).ready(function () {
    constroiTabela();
});

let dataTable = "";

document.getElementById('toggleSenha').addEventListener('click', function () {
    const input = document.getElementById('senhaAprovacao');
    const icon = this.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        $("#toggleSenhaIcon").html('<i class="fa-solid fa-eye-slash"></i>');
    } else {
        input.type = 'password';
        $("#toggleSenhaIcon").html('<i class="fa-solid fa-eye"></i>');
    }
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
    linhasPorPagina = 8;
    dataTable = new DataTable('#tblFerias', {
        "processing": true,
        "serverSide": false,
        "stateSave": false, // impede lembrar filtro antigo
         destroy: true, // se recria várias vezes
        "order": [
            [1, "asc"],
            [2, "asc"]
        ],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "includes/ferias_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            "className": "text-center"
        }],
        language: {
            url: '../includes/pt-BR.json',
        },
    });

  dataTable.on('init', function () {
    dataTable.search('').draw();
    var $filtro = $('.dataTables_filter input');
    $filtro.val('').attr('autocomplete','off');
    setTimeout(() => $filtro.val(''), 200);
  });
}

function f_visualizar(idColab) {
    location.href = "ficha_colab.php?id=" + idColab;
}

function fer_aprovar_grid(idFerias) {
    let modalFerias = new bootstrap.Modal(document.getElementById('modalFerias'));
    // Mostra loading enquanto carrega
    $("#conteudoFerias").html("<i class='fa fa-spinner fa-spin'></i> Carregando...");

    // Faz requisição AJAX
    $.ajax({
        url: "includes/ferias_aj1.php",
        type: "POST",
        data: {
            idFerias: idFerias
        },
        success: function (response) {
            $("#conteudoFerias").html(response);
        },
        error: function () {
            $("#conteudoFerias").html("<div class='alert alert-danger'>Erro ao carregar dados.</div>");
        }
    });
    modalFerias.show(); // Mostra a modal de aprovação
}

function toggleParcela(checkbox) {
    //alert("toggleParcela: " + checkbox.value + " | " + checkbox.checked);
    let hidden = document.getElementById("parcelas_desmarcadas");
    let valores = hidden.value ? hidden.value.split(",") : [];

    if (!checkbox.checked) {
        // adiciona se não existir
        if (!valores.includes(checkbox.value)) {
            valores.push(checkbox.value);
        }
    } else {
        // remove se estava desmarcado e voltou a marcar
        valores = valores.filter(v => v !== checkbox.value);
    }

    hidden.value = valores.join(",");
}

function f_aprovar_selecionadas() {
    //
    let modalAprova = new bootstrap.Modal(document.getElementById('modalAprovar'));
    //

    let idColabSupervisor = document.getElementById('idColabSupervisor').value;
    let parcelasDesmarcadas = document.getElementById('parcelas_desmarcadas').value;
    document.getElementById('idColabSupervisorConfirm').value = idColabSupervisor;
    document.getElementById('senhaAprovacao').value = ""; // Limpa o campo de senha 
    //
    if (parcelasDesmarcadas.length > 0) {
        document.getElementById('motivoNaoAprovacaoSection').classList.remove('d-none');
    } else {
        document.getElementById('motivoNaoAprovacaoSection').classList.add('d-none');
    }
    //           
    modalAprova.show();
}

function confirmarAprovacao() {
    let mensagem = $("#msgAprova");
    //
    const idFerias = document.getElementById('idFerias').value;
    const senha = document.getElementById('senhaAprovacao').value;
    const idColabSupervisor = document.getElementById('idColabSupervisorConfirm').value;
    const vetorParcelas = document.getElementById('vetorParcelas').value;

    if (!senha) {
        alert("Por favor, digite a senha.");
        document.getElementById('senhaAprovacao').focus();
        return;
    }
    //
    let desmarcadas = document.getElementById('parcelas_desmarcadas').value;
    if (desmarcadas.length > 0 && !document.getElementById('motivoNaoAprovacao').value.trim()) {
        alert("Por favor, informe o motivo da não aprovação das parcelas desmarcadas.");
        document.getElementById('motivoNaoAprovacao').focus();
        return;
    }
    //
    const parcelasTotais = document.getElementById('vetorParcelas').value
        .split(',')
        .map(p => parseInt(p));

    const qtdParcelas = parcelasTotais.length; // número total de parcelas possíveis

    //
    // Pegar os valores dos checkboxes marcados
    let parcelasMarcadas = [];
    document.querySelectorAll('input[name="parcelas[]"]:checked').forEach(function (checkbox) {
        parcelasMarcadas.push(checkbox.value);
    });

    let qtdSelecionadas = parcelasMarcadas.length; // ✅ quantidade de parcelas marcadas (já é array)

    // Verifica se há pelo menos uma parcela aprovada
    if (qtdSelecionadas === 0) {
        alert("Selecione ao menos uma parcela para aprovar.");
        return;
    }
    //
    // Pegar as parcelas desmarcadas do hidden
    const parcelasDesmarcadas = document.getElementById('parcelas_desmarcadas').value;

    const body = `idFerias=${encodeURIComponent(idFerias)}` +
        `&idGestor=${encodeURIComponent(idColabSupervisor)}` +
        `&senha=${encodeURIComponent(senha)}` +
        `&parcelas=${encodeURIComponent(parcelasMarcadas.join(','))}` +
        `&parcelas_desmarcadas=${encodeURIComponent(parcelasDesmarcadas)}` +
        `&motivoNaoAprovacao=${encodeURIComponent(document.getElementById('motivoNaoAprovacao').value)}`;

    // Aqui você envia para o backend
    fetch('includes/ferias_aj2.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body
    })
        .then(response => response.json())
        .then(retorno => {
            if (retorno.status) {
                mensagem.html(retorno.msg);
                // Fechar modal
                document.activeElement.blur(); // ou: document.body.focus();
                setTimeout(() => {
                    /*
                    mensagem.html('');
                    document.activeElement.blur(); // ou: document.body.focus();
                    modalAprova.hide();
                    modalFerias.hide();
                    // Recarrega a tabela
                    dataTable.ajax.reload(null, false); // false para manter a página atual
                    */
                   window.location.reload(); // Recarrega a página toda
                }, 3000);
                //
            } else {

                mensagem.html(retorno.msg);
                setTimeout(() => {
                    mensagem.html('');
                    document.activeElement.blur(); // ou: document.body.focus();
                    modalAprova.hide();
                }, 3000);
            }
        });
}