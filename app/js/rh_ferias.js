var dataTable;
var linhasPorPagina = 12;

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar"));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar"));

$(document).ready(function () {
    $("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário

    $('#e_obs').summernote({
        height: 150,
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
        "order": [1, "asc"],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_ferias_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0,2,3,4,5,6,7,8,9,10,11,12,13,14,15],
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

function f_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o Período de ID " + id + "?")) {
        $("#divAlerta").show();
        $.post("includes/rh_ferias_aj3.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            //alert(response);
            $("#divAlerta").html(dados.msg);
            //
            setTimeout(function () {
                $("#divAlerta").html("");
                selecionou();
            }, 3000);

        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
    }
}

function f_editar(id) {
    //
    $.post("includes/rh_ferias_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#e_id").val(id);
        $("#e_nome").html(dados.nome);
        $("#e_aquisitivo").html(formatarDataBR(dados.inicio_aquisitivo) + " a " + formatarDataBR(dados.fim_aquisitivo) );
        $("#e_concessivo").html(formatarDataBR(dados.inicio_concessivo) + " a " + formatarDataBR(dados.fim_concessivo) );
        $("#e_agenda1").val(dados.agenda_parte1);
        $("#e_dias1"  ).val(dados.dias_parte1  );
        $("#e_agenda2").val(dados.agenda_parte2);
        $("#e_dias2"  ).val(dados.dias_parte2  );
        $("#e_agenda3").val(dados.agenda_parte3);
        $("#e_dias3"  ).val(dados.dias_parte3  );
        $("#e_fruido1").val(dados.data_parte1  );
        $("#e_fruido2").val(dados.data_parte2  );
        $("#e_fruido3").val(dados.data_parte3  );

        $("#e_obs").summernote('code', dados.obs);
        //
    });
}

function f_editar_commit() {
    //
    const id = $("#e_id");
    const agenda_1 = $("#e_agenda1");
    const e_dias1   = $("#e_dias1");
    let mensagem = $("#msgEditar");
    //
    if (agenda_1.val() == "") {
        agenda_1.focus();
        alert("Informe o data da agenda para a 1º parcela");
        return;
    }
    //
    if (e_dias1.val() == "") {
        e_dias1.focus();
        alert("Informe o nro de dias a fruir na primeira parcela");
        return;
    }
    //
    $("#botoes_editar").hide();
    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");    
    //
    // Atualiza o valor do campo #_descricao com o conteúdo do Summernote
    $("#e_obs").val($("#e_obs").summernote('code'));

    // 🔥 2. CRIANDO O FORM DATA
    let formData = new FormData(document.getElementById("formEditar"));
    //
    // 🔥 3. ENVIANDO O FORMULÁRIO
    $.ajax({
        url: "includes/rh_ferias_aj2.php",
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
            //
        }
    });
}

function f_visualizar(id) {

    $.post("includes/rh_ferias_aj1.php", { id: id, origem: 'visualizar' }, function (retorno) {
        const dados = JSON.parse(retorno);
        //
        $("#v_nome").html(dados.nome);
        $("#v_aquisitivo").html(formatarDataBR(dados.inicio_aquisitivo) + " a " + formatarDataBR(dados.fim_aquisitivo) );
        $("#v_concessivo").html(formatarDataBR(dados.inicio_concessivo) + " a " + formatarDataBR(dados.fim_concessivo) );
        $("#v_agenda1").html(formatarDataBR(dados.agenda_parte1));
        $("#v_dias1").html(dados.dias_parte1 || "Não");
        $("#v_agenda2").html(formatarDataBR(dados.agenda_parte2));
        $("#v_dias2").html(dados.dias_parte2 || "Não");
        $("#v_agenda3").html(formatarDataBR(dados.agenda_parte3));
        $("#v_dias3").html(dados.dias_parte3 || "Não");
        $("#v_fruido1").html(formatarDataBR(dados.data_parte1));
        $("#v_fruido2").html(formatarDataBR(dados.data_parte2));
        $("#v_fruido3").html(formatarDataBR(dados.data_parte3));
        $("#v_obs").html( dados.alerta + "<br class='mt-3'>" + dados.obs );
                
        // Abrir o modal de visualização
        visModal.show();
    });

}

function formatarDataBR(dataStr) {
    if (!dataStr) return "Não";
    const data = new Date(dataStr);
    if (isNaN(data)) return "Não";
    return data.toLocaleDateString('pt-BR');
}

function f_reset() {
    let id = $("#e_id").val();
    $.post("includes/rh_ferias_aj1.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        $("#e_id").val(id);
        $("#e_nome").html(dados.nome);
        $("#e_aquisitivo").html(formatarDataBR(dados.inicio_aquisitivo) + " a " + formatarDataBR(dados.fim_aquisitivo) );
        $("#e_concessivo").html(formatarDataBR(dados.inicio_concessivo) + " a " + formatarDataBR(dados.fim_concessivo) );
        $("#e_agenda1").val(dados.agenda_parte1);
        $("#e_dias1"  ).val(dados.dias_parte1  );
        $("#e_agenda2").val(dados.agenda_parte2);
        $("#e_dias2"  ).val(dados.dias_parte2  );
        $("#e_agenda3").val(dados.agenda_parte3);
        $("#e_dias3"  ).val(dados.dias_parte3  );
        $("#e_fruido1").val(dados.data_parte1  );
        $("#e_fruido2").val(dados.data_parte2  );
        $("#e_fruido3").val(dados.data_parte3  );
        $("#e_obs").summernote('code', dados.observacao);
    });
}

function dias(o) {
    let d1 = parseInt($("#e_dias1").val()) || 0;
    let d2 = parseInt($("#e_dias2").val()) || 0;
    let d3 = parseInt($("#e_dias3").val()) || 0;

    let total = d1 + d2 + d3;

    if (total > 30) {
        alert("ERRO: excede 30 dias");
        o.value = 0;
        o.focus();
    }
}

function fer_aprovar_grid(idFerias) {
    let modalFerias = new bootstrap.Modal(document.getElementById('modalFerias'));
    // Mostra loading enquanto carrega
    $("#conteudoFerias").html("<i class='fa fa-spinner fa-spin'></i> Carregando...");

    // Faz requisição AJAX
    $.ajax({
        url: "includes/rh_ferias_aj4.php",
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
    let parcelasDesmarcadas = document.getElementById('parcelas_desmarcadas').value;
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
    const vetorParcelas = document.getElementById('vetorParcelas').value;
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
        `&parcelas=${encodeURIComponent(parcelasMarcadas.join(','))}` +
        `&parcelas_desmarcadas=${encodeURIComponent(parcelasDesmarcadas)}` +
        `&motivoNaoAprovacao=${encodeURIComponent(document.getElementById('motivoNaoAprovacao').value)}`;

    // Aqui você envia para o backend
    fetch('includes/rh_ferias_aj5.php', {
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