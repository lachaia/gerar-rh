$(document).ready(function () {

    $(document).ready(function () {
        $("footer").removeClass("bg-light").addClass("bg-dark");
    });
});

function goBack() {
    window.location.href = "index.php";
}

function ressetReajuste() {
    /*
    let vazio = '<p class="text-muted text-center" id="msgVazio">Nenhum colaborador incluído.</p>';
    $("#percentualReajuste").val(0);
    $("#selectOrgao").val(0);
    $("#selectOrgaoSub").val(0);
    $("#motivoReajuste").html("");
    $("#listaColaboradores").html(vazio);
    */
    window.location.reload();
}

document.addEventListener('DOMContentLoaded', () => {
    const listaColaboradoresDiv = document.getElementById('listaColaboradores');
    const msgVazio = document.getElementById('msgVazio');
    const listaID = document.getElementById('listaID'); // campo hidden para enviar os IDs
    let colaboradoresReajuste = [];

    const renderizarLista = () => {
        listaColaboradoresDiv.innerHTML = '';
        if (colaboradoresReajuste.length === 0) {
            msgVazio.classList.remove('d-none');
            listaColaboradoresDiv.appendChild(msgVazio);
            if (listaID) listaID.value = ''; // limpa o campo hidden
            return;
        }

        msgVazio.classList.add('d-none');

        const ul = document.createElement('ul');
        ul.classList.add('list-group', 'list-group-flush');

        colaboradoresReajuste.forEach(item => {
            const id = (item && typeof item === 'object') ? (item.id ?? item.idColab ?? item.id_colab ?? null) : null;
            const nome = (typeof item === 'string') ? item : (item.nome ?? item.Nome ?? item.name ?? 'Sem nome');

            const li = document.createElement('li');
            li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
            li.innerHTML = `
                <span class="flex-grow-1">${nome}</span>
                <button class="btn btn-sm btn-outline-danger btn-remover" data-id="${id ?? ''}">
                    &times;
                </button>
            `;
            ul.appendChild(li);
        });

        listaColaboradoresDiv.appendChild(ul);

        // Atualiza o campo hidden com a lista de IDs válidos
        if (listaID) {
            const ids = colaboradoresReajuste
                .map(c => (typeof c === 'object' ? (c.id ?? c.idColab ?? c.id_colab) : null))
                .filter(id => id !== null && id !== undefined);
            listaID.value = ids.join(',');
        }

        // Eventos de remoção
        document.querySelectorAll('.btn-remover').forEach(button => {
            button.addEventListener('click', e => {
                const idAttr = e.currentTarget.getAttribute('data-id');
                colaboradoresReajuste = colaboradoresReajuste.filter(c => {
                    const cid = (typeof c === 'object') ? (String(c.id ?? c.idColab ?? c.id_colab ?? '')) : '';
                    return cid !== String(idAttr);
                });
                renderizarLista();
            });
        });
    };

    // Exemplo da função de incluir todos via AJAX
    document.getElementById('incluirTodosBtn').addEventListener('click', () => {
        fetch('includes/rh_reajuste_aj1.php?tipo=todos')
            .then(resp => resp.json())
            .then(data => {
                data.forEach(item => {
                    let id = item.idColab ?? item.id ?? item.id_colab ?? null;
                    let nome = item.nome ?? item.Nome ?? item.name ?? '';
                    if (!colaboradoresReajuste.some(c => c.id === id)) {
                        colaboradoresReajuste.push({ id, nome });
                    }
                });
                renderizarLista();
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao buscar colaboradores');
            });
    });

    document.getElementById('incluirOrgaoBtn').addEventListener('click', () => {
        let idOrgao = document.getElementById('selectOrgao').value;
        fetch('includes/rh_reajuste_aj1.php?tipo=orgao&idOrgao=' + idOrgao)
            .then(resp => resp.json())
            .then(data => {
                data.forEach(item => {
                    let id = item.idColab ?? item.id ?? item.id_colab ?? null;
                    let nome = item.nome ?? item.Nome ?? item.name ?? '';
                    if (id && !colaboradoresReajuste.some(c => c.id === id)) {
                        colaboradoresReajuste.push({ id, nome });
                    }
                });

                renderizarLista();

                // 🔹 Atualiza o campo oculto com os IDs
                let ids = colaboradoresReajuste.map(c => c.id).join(',');
                $("#listaID").val(ids);

                console.log("IDs atualizados:", ids); // debug
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao buscar colaboradores');
            });
    });

    document.getElementById('incluirOrgaoBtnSub').addEventListener('click', () => {
        let idOrgao = document.getElementById('selectOrgaoSub').value;
        fetch('includes/rh_reajuste_aj1.php?tipo=suborgao&idOrgao=' + idOrgao)
            .then(resp => resp.json())
            .then(data => {
                data.forEach(item => {
                    let id = item.idColab ?? item.id ?? item.id_colab ?? null;
                    let nome = item.nome ?? item.Nome ?? item.name ?? '';
                    if (id && !colaboradoresReajuste.some(c => c.id === id)) {
                        colaboradoresReajuste.push({ id, nome });
                    }
                });

                renderizarLista();

                // 🔹 Atualiza o campo oculto com os IDs
                let ids = colaboradoresReajuste.map(c => c.id).join(',');
                $("#listaID").val(ids);

                console.log("IDs atualizados:", ids); // debug
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao buscar colaboradores');
            });
    });


    $("#nomeColaborador").autocomplete({
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
                            idColab: item.idColab,
                            idPessoa: item.idPessoa
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            // Monta o objeto com id e nome
            const id = ui.item.idColab;
            const nome = ui.item.value;

            // Verifica se já existe no vetor
            if (!colaboradoresReajuste.some(c => c.id === id)) {
                colaboradoresReajuste.push({ id, nome });
            } else {
                alert("Este colaborador já está na lista.");
                return;
            }

            // Atualiza a lista visual
            renderizarLista();

            // Atualiza o campo oculto de IDs
            const ids = colaboradoresReajuste.map(c => c.id).join(',');
            $("#listaID").val(ids);

            // Limpa o campo de busca
            $("#nomeColaborador").val('');

            console.log("Colaborador adicionado:", { id, nome });
            return false; // evita sobrescrever o campo de texto
        }
    });

    renderizarLista();
});


function enviarReajuste() {
    let mensagem = $("#alertaReajuste");
    let listaID = $("#listaID").val().trim();
    let percentual = parseFloat($("#percentualReajuste").val());
    let motivo = $("#motivoReajuste").val().trim();
    let orgao = $("#selectOrgao").val();
    let orgaoSub = $("#selectOrgaoSub").val();

    // converte a lista de IDs em vetor
    let idColaboradores = listaID
        ? listaID.split(',').map(id => id.trim()).filter(id => id !== '')
        : [];

    // === VALIDAÇÕES ===
    if (isNaN(percentual) || percentual <= 0) {
        alert("Informe um percentual de reajuste maior que zero.");
        $("#percentualReajuste").focus();
        return;
    }

    if (!motivo) {
        alert("Informe o motivo do reajuste.");
        $("#motivoReajuste").focus();
        return;
    }

    if (idColaboradores.length === 0) {
        alert("Adicione pelo menos um colaborador para aplicar o reajuste.");
        return;
    }

    // === SE TUDO OK, ENVIA ===
    let data = {
        percentual: percentual,
        motivo: motivo,
        idColaboradores: idColaboradores
    };

    //console.log("Dados enviados:", data); // debug

    mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");

    $.post("includes/rh_reajuste_aj2.php", data, function (result) {
        //alert(result);
        let dados = JSON.parse(result);
        mensagem.html( dados.msg );
        setTimeout(function () {
            // document.location.reload(true);
            window.location.reload();
        }, 3000);
    }).fail(function () {
        alert("Erro ao enviar os dados. Verifique a conexão ou o backend.");
    });
}


