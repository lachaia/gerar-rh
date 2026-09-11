//
// fluxo.js | Rotinas do Board Kanban do Fluxo de Recrutamento | fluxo.php
// (C)haia, 2026-08-26
//

let publicarContexto = null;

$(document).ready(function () {
    document.querySelectorAll('.fluxo-lista').forEach(function (lista) {
        new Sortable(lista, {
            group: 'fluxo',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: onCardSolto
        });
    });

    //- Se o modal de publicação for fechado sem confirmar (Cancelar, X, Esc, clique fora),
    //- desfaz o drag - senão o card fica "flutuando" na coluna Divulgação sem nada gravado.
    $('#modalPublicar').on('hidden.bs.modal', function () {
        if (publicarContexto && !publicarContexto.confirmado) {
            desfazerMovimento(publicarContexto.listaOrigem, publicarContexto.itemMovido, publicarContexto.indiceOrigem);
            atualizarContadores();
        }
        publicarContexto = null;
    });
});

function onCardSolto(evt) {
    const vagaId = evt.item.getAttribute('data-vaga-id');
    const fluxoOrigem = evt.from.getAttribute('data-fluxo-id');
    const fluxoDestino = evt.to.getAttribute('data-fluxo-id');

    if (fluxoOrigem === fluxoDestino) return;

    const itemMovido = evt.item;
    const listaOrigem = evt.from;
    const indiceOrigem = evt.oldIndex;

    //- Entrar em DIVULGAÇÃO (2) tem regras próprias (dados de publicação completos +
    //- validade do anúncio) e passa pelo modal de publicação, não pelo mover genérico.
    if (fluxoDestino === '2') {
        abrirModalPublicar(vagaId, itemMovido, listaOrigem, indiceOrigem);
        return;
    }

    moverCard(vagaId, fluxoDestino, itemMovido, listaOrigem, indiceOrigem);
}

function moverCard(vagaId, fluxoDestino, itemMovido, listaOrigem, indiceOrigem) {
    $.ajax({
        url: 'inc/vaga_fluxo_mover_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { vaga_id: vagaId, fluxo_id: fluxoDestino },
        success: function (res) {
            if (!res.status) {
                alert(res.msg);
                desfazerMovimento(listaOrigem, itemMovido, indiceOrigem);
            } else {
                atualizarCardParaColuna(itemMovido, fluxoDestino);
            }
            atualizarContadores();
        },
        error: function () {
            alert('Falha de comunicação com o servidor.');
            desfazerMovimento(listaOrigem, itemMovido, indiceOrigem);
            atualizarContadores();
        }
    });
}

function abrirModalPublicar(vagaId, itemMovido, listaOrigem, indiceOrigem) {
    publicarContexto = { vagaId: vagaId, itemMovido: itemMovido, listaOrigem: listaOrigem, indiceOrigem: indiceOrigem, confirmado: false };
    $('#msgAlertaPublicar').html('');
    $('#publicar_vaga_id').val(vagaId);
    $('#publicar_expira_em').val('');
    $('#modalPublicar').modal('show');
}

function confirmarPublicar() {
    if (!publicarContexto) return;

    const vagaId = $('#publicar_vaga_id').val();
    const expiraEm = $('#publicar_expira_em').val();

    $.ajax({
        url: 'inc/vaga_fluxo_publicar_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { vaga_id: vagaId, expira_em: expiraEm },
        success: function (res) {
            if (res.status) {
                publicarContexto.confirmado = true;
                atualizarCardParaColuna(publicarContexto.itemMovido, '2');
                atualizarContadores();
                $('#modalPublicar').modal('hide');
            } else {
                $('#msgAlertaPublicar').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaPublicar').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

//- O conteúdo do card (título clicável, botão de ação) depende da coluna e é montado em PHP;
//- ao mover por drag-and-drop precisa ser reconstruído aqui, já que não há reload de página.
//- Edição (vaga_edit.php) só é oferecida na coluna ALINHAMENTO (fluxo_id 1); nas demais
//- colunas já em andamento (Divulgação em diante) só cabe consulta (vaga_view.php).
function atualizarCardParaColuna(cardEl, fluxoDestino) {
    const vagaId = cardEl.getAttribute('data-vaga-id');
    const recrutadorId = cardEl.getAttribute('data-recrutador-id') || '0';
    const qtdCandidatos = parseInt(cardEl.getAttribute('data-qtd-candidatos') || '0', 10);
    const tituloWrap = cardEl.querySelector('.fluxo-card-titulo-wrap');
    const acaoWrap = cardEl.querySelector('.fluxo-card-acao');
    const titulo = tituloWrap ? tituloWrap.textContent.trim() : '';

    //- fluxo_alterado_em é resetado no servidor a cada movimentação - o indicador de
    //- "dias parada nesta etapa" volta para 0d imediatamente, sem esperar reload.
    const kbdEtapa = cardEl.querySelector('.fluxo-kbd-etapa');
    if (kbdEtapa) {
        kbdEtapa.className = 'fluxo-kbd-etapa bg-secondary fw-bold';
        kbdEtapa.textContent = '0d';
    }

    if (fluxoDestino === '0') {
        if (tituloWrap) {
            tituloWrap.outerHTML = '<div class="fluxo-card-titulo-wrap fw-bold small mb-1">' + titulo + '</div>';
        }
        if (acaoWrap) {
            acaoWrap.innerHTML =
                '<button type="button" class="btn btn-warning btn-sm fluxo-btn-icone" onclick="abrirModalRecrutador(' + vagaId + ', ' + recrutadorId + ')" title="Selecionar Recrutador">' +
                '<i class="fa-solid fa-user-plus"></i></button>';
        }
    } else if (fluxoDestino === '1') {
        if (tituloWrap) {
            tituloWrap.outerHTML = '<a href="vaga_edit.php?id=' + vagaId + '" class="fluxo-card-titulo-wrap"><div class="fw-bold small mb-1">' + titulo + '</div></a>';
        }
        if (acaoWrap) {
            acaoWrap.innerHTML =
                '<a href="vaga_edit.php?id=' + vagaId + '" class="btn btn-outline-info btn-sm fluxo-btn-icone" title="Editar Vaga">' +
                '<i class="fa-solid fa-pen"></i></a>';
        }
    } else {
        if (tituloWrap) {
            tituloWrap.outerHTML = '<a href="vaga_view.php?id=' + vagaId + '" class="fluxo-card-titulo-wrap"><div class="fw-bold small mb-1">' + titulo + '</div></a>';
        }
        if (acaoWrap) {
            const badgeCandidatos = qtdCandidatos > 0
                ? '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">' + qtdCandidatos + '</span>'
                : '';
            acaoWrap.innerHTML =
                '<a href="vaga_candidatos.php?id=' + vagaId + '" class="btn btn-outline-info btn-sm fluxo-btn-icone position-relative" title="Ver Candidatos">' +
                '<i class="fa-solid fa-people-arrows"></i>' + badgeCandidatos + '</a>' +
                '<a href="vaga_view.php?id=' + vagaId + '" class="btn btn-outline-secondary btn-sm fluxo-btn-icone" title="Ver Vaga">' +
                '<i class="fa-solid fa-eye"></i></a>';
        }
    }
}

function desfazerMovimento(listaOrigem, item, indiceOrigem) {
    const referencia = listaOrigem.children[indiceOrigem] || null;
    listaOrigem.insertBefore(item, referencia);
}

function atualizarContadores() {
    document.querySelectorAll('.fluxo-lista').forEach(function (lista) {
        const coluna = lista.closest('.fluxo-coluna');
        const contador = coluna ? coluna.querySelector('.fluxo-coluna-contador') : null;
        if (contador) contador.textContent = lista.children.length;
    });
}

function abrirModalRecrutador(vagaId, recrutadorIdAtual) {
    $('#msgAlertaRecrutador').html('');
    $('#recrutador_vaga_id').val(vagaId);
    $('#recrutador_select').val(recrutadorIdAtual || 0);
    $('#modalRecrutador').modal('show');
}

function salvarRecrutador() {
    const vagaId = $('#recrutador_vaga_id').val();
    const recrutadorId = $('#recrutador_select').val();

    if (!recrutadorId || recrutadorId === '0') {
        $('#msgAlertaRecrutador').html("<div class='alert alert-danger py-2'>Selecione um recrutador.</div>");
        return;
    }

    $.ajax({
        url: 'inc/vaga_fluxo_recrutador_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { vaga_id: vagaId, recrutador_id: recrutadorId },
        success: function (res) {
            if (res.status) {
                $('#msgAlertaRecrutador').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                setTimeout(function () {
                    location.reload();
                }, 700);
            } else {
                $('#msgAlertaRecrutador').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaRecrutador').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}
