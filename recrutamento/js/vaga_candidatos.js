//
// vaga_candidatos.js | Rotinas do Board Kanban de Candidatos | vaga_candidatos.php
// (C)haia, 2026-08-27
//

const FLUXO_REJEITADO = 6;
const FLUXO_SCREENING = 3;
let rejeitarContexto = null;
let agendarContexto = null;
let cpfEncontradoTravado = false;

$(document).ready(function () {
    $('#adicionar_cpf').on('blur', consultarCpfExistente);

    document.querySelectorAll('.cand-lista').forEach(function (lista) {
        new Sortable(lista, {
            group: 'cand-fluxo',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: onCardSolto
        });
    });

    //- Se o modal de rejeição/agendamento for fechado sem confirmar, desfaz o drag -
    //- senão o card fica "flutuando" na coluna sem nada gravado.
    $('#modalRejeitar').on('hidden.bs.modal', function () {
        if (rejeitarContexto && !rejeitarContexto.confirmado) {
            desfazerMovimento(rejeitarContexto.listaOrigem, rejeitarContexto.itemMovido, rejeitarContexto.indiceOrigem);
            atualizarContadores();
        }
        rejeitarContexto = null;
    });
    $('#modalAgendarScreening').on('hidden.bs.modal', function () {
        if (agendarContexto && !agendarContexto.confirmado) {
            desfazerMovimento(agendarContexto.listaOrigem, agendarContexto.itemMovido, agendarContexto.indiceOrigem);
            atualizarContadores();
        }
        agendarContexto = null;
    });
});

function onCardSolto(evt) {
    const candidaturaId = evt.item.getAttribute('data-candidatura-id');
    const fluxoOrigem = evt.from.getAttribute('data-fluxo-id');
    const fluxoDestino = evt.to.getAttribute('data-fluxo-id');

    if (fluxoOrigem === fluxoDestino) return;

    const itemMovido = evt.item;
    const listaOrigem = evt.from;
    const indiceOrigem = evt.oldIndex;

    if (fluxoDestino === String(FLUXO_REJEITADO)) {
        abrirModalRejeitar(candidaturaId, itemMovido, listaOrigem, indiceOrigem);
        return;
    }

    if (fluxoDestino === String(FLUXO_SCREENING)) {
        abrirModalAgendarScreening(candidaturaId, itemMovido, listaOrigem, indiceOrigem);
        return;
    }

    moverCard(candidaturaId, fluxoDestino, itemMovido, listaOrigem, indiceOrigem);
}

function abrirModalAgendarScreening(candidaturaId, itemMovido, listaOrigem, indiceOrigem) {
    agendarContexto = { candidaturaId: candidaturaId, itemMovido: itemMovido, listaOrigem: listaOrigem, indiceOrigem: indiceOrigem, confirmado: false };
    $('#msgAlertaAgendar').html('');
    $('#agendar_candidatura_id').val(candidaturaId);
    $('#agendar_data').val('');
    $('#agendar_hora').val('');
    $('#btnConfirmarAgendar').prop('disabled', false).html("<i class='fa-solid fa-calendar-check me-2'></i>Agendar");
    $('#modalAgendarScreening').modal('show');
}

function confirmarAgendarScreening() {
    if (!agendarContexto) return;

    const btn = $('#btnConfirmarAgendar');
    if (btn.prop('disabled')) return; // já enviando - ignora clique duplicado

    const candidaturaId = $('#agendar_candidatura_id').val();
    const data = $('#agendar_data').val();
    const hora = $('#agendar_hora').val();

    if (!data || !hora) {
        $('#msgAlertaAgendar').html("<div class='alert alert-danger py-2'>Informe data e horário.</div>");
        return;
    }

    btn.prop('disabled', true).html("<i class='fa-solid fa-spinner fa-spin me-2'></i>Agendando...");
    $('#msgAlertaAgendar').html("<div class='alert alert-info py-2'><i class='fa-solid fa-spinner fa-spin me-2'></i>Criando evento no Google Calendar...</div>");

    $.ajax({
        url: 'inc/candidato_fluxo_agendar_screening_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { candidatura_id: candidaturaId, data: data, hora: hora },
        success: function (res) {
            if (res.status) {
                agendarContexto.confirmado = true;
                atualizarContadores();
                location.reload();
            } else {
                btn.prop('disabled', false).html("<i class='fa-solid fa-calendar-check me-2'></i>Agendar");
                $('#msgAlertaAgendar').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            btn.prop('disabled', false).html("<i class='fa-solid fa-calendar-check me-2'></i>Agendar");
            $('#msgAlertaAgendar').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function moverCard(candidaturaId, fluxoDestino, itemMovido, listaOrigem, indiceOrigem) {
    $.ajax({
        url: 'inc/candidato_fluxo_mover_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { candidatura_id: candidaturaId, fluxo_id: fluxoDestino },
        success: function (res) {
            if (!res.status) {
                alert(res.msg);
                desfazerMovimento(listaOrigem, itemMovido, indiceOrigem);
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

function abrirModalRejeitar(candidaturaId, itemMovido, listaOrigem, indiceOrigem) {
    rejeitarContexto = { candidaturaId: candidaturaId, itemMovido: itemMovido, listaOrigem: listaOrigem, indiceOrigem: indiceOrigem, confirmado: false };
    $('#msgAlertaRejeitar').html('');
    $('#rejeitar_candidatura_id').val(candidaturaId);
    $('#rejeitar_motivo').val('');
    $('#modalRejeitar').modal('show');
}

function confirmarRejeitar() {
    if (!rejeitarContexto) return;

    const candidaturaId = $('#rejeitar_candidatura_id').val();
    const motivo = $('#rejeitar_motivo').val();

    $.ajax({
        url: 'inc/candidato_fluxo_rejeitar_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { candidatura_id: candidaturaId, motivo: motivo },
        success: function (res) {
            if (res.status) {
                rejeitarContexto.confirmado = true;
                if (motivo) {
                    const motivoEl = document.createElement('div');
                    motivoEl.className = 'small text-danger mt-1 fst-italic';
                    motivoEl.textContent = motivo;
                    rejeitarContexto.itemMovido.appendChild(motivoEl);
                }
                atualizarContadores();
                $('#modalRejeitar').modal('hide');
            } else {
                $('#msgAlertaRejeitar').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            $('#msgAlertaRejeitar').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function desfazerMovimento(listaOrigem, item, indiceOrigem) {
    const referencia = listaOrigem.children[indiceOrigem] || null;
    listaOrigem.insertBefore(item, referencia);
}

function selecionarOrigemPorNome(descricao) {
    $('#adicionar_origem option').each(function () {
        if ($(this).text().trim() === descricao) {
            $('#adicionar_origem').val($(this).val());
        }
    });
}

function abrirModalAdicionar() {
    $('#msgAlertaAdicionar').html('');
    $('#msgCpfExistente').html('');
    $('#adicionar_cpf').val('');
    $('#adicionar_nome').val('').prop('readonly', false);
    $('#adicionar_telefone').val('').prop('readonly', false);
    $('#adicionar_email').val('').prop('readonly', false);
    $('#adicionar_linkedin').val('');
    $('#adicionar_arquivo').val('');
    $('#adicionar_origem').val('');
    $('#btnConfirmarAdicionar').prop('disabled', false).html("<i class='fa-solid fa-floppy-disk me-2'></i>Adicionar ao Funil");
    cpfEncontradoTravado = false;
    $('#modalAdicionar').modal('show');
}

//- Ao sair do campo CPF, verifica se a pessoa já existe na base (mesmo dedupe do
//- salvamento) - se existir, preenche e trava os campos de contato, já que o
//- back-end reaproveita o cadastro existente e ignora o que for digitado ali.
function consultarCpfExistente() {
    const cpf = $('#adicionar_cpf').val().trim();

    if (cpfEncontradoTravado) {
        $('#adicionar_nome, #adicionar_telefone, #adicionar_email, #adicionar_linkedin').val('').prop('readonly', false);
        $('#msgCpfExistente').html('');
        cpfEncontradoTravado = false;
    }

    if (!cpf) return;

    $.ajax({
        url: 'inc/candidato_cpf_lookup_aj.php',
        type: 'POST',
        dataType: 'json',
        data: { cpf: cpf },
        success: function (res) {
            if (res.status && res.encontrado) {
                $('#adicionar_nome').val(res.nome || '').prop('readonly', true);
                $('#adicionar_telefone').val(res.telefone || '').prop('readonly', true);
                $('#adicionar_email').val(res.email || '').prop('readonly', true);
                if (res.linkedin) {
                    $('#adicionar_linkedin').val(res.linkedin).prop('readonly', true);
                }
                cpfEncontradoTravado = true;

                if (res.colaborador) {
                    selecionarOrigemPorNome('Recrutamento Interno');
                    $('#msgCpfExistente').html("<i class='fa-solid fa-circle-info me-1'></i>Este CPF pertence a um colaborador ativo - origem ajustada para Recrutamento Interno.");
                } else {
                    $('#msgCpfExistente').html("<i class='fa-solid fa-circle-info me-1'></i>Candidato já cadastrado na base - dados de contato reaproveitados.");
                }
            }
        }
    });
}

function confirmarAdicionar() {
    const cpf = $('#adicionar_cpf').val().trim();
    const nome = $('#adicionar_nome').val().trim();
    const origemId = $('#adicionar_origem').val();

    const faltando = [];
    if (!cpf) faltando.push('CPF');
    if (!nome) faltando.push('nome');
    if (!origemId) faltando.push('origem');

    if (faltando.length > 0) {
        $('#msgAlertaAdicionar').html("<div class='alert alert-danger py-2'>Preencha: " + faltando.join(', ') + ".</div>");
        return;
    }

    const btn = $('#btnConfirmarAdicionar');
    if (btn.prop('disabled')) return; // já enviando - ignora clique duplicado

    const formData = new FormData();
    formData.append('vaga_id', VAGA_ID);
    formData.append('cpf', cpf);
    formData.append('nome', nome);
    formData.append('origem_id', origemId);
    formData.append('telefone', $('#adicionar_telefone').val().trim());
    formData.append('email', $('#adicionar_email').val().trim());
    formData.append('linkedin', $('#adicionar_linkedin').val().trim());
    const arquivo = $('#adicionar_arquivo')[0].files[0];
    if (arquivo) formData.append('arquivo', arquivo);

    btn.prop('disabled', true).html("<i class='fa-solid fa-spinner fa-spin me-2'></i>Processando...");
    //- Com currículo em PDF anexado, esse processamento passa pela extração via IA
    //- (candidato_manual_inc_aj.php) - pode demorar alguns segundos, daí o aviso.
    $('#msgAlertaAdicionar').html("<div class='alert alert-info py-2'><i class='fa-solid fa-spinner fa-spin me-2'></i>Aguarde, processando" + (arquivo ? " (lendo o currículo anexado)" : "") + "...</div>");

    $.ajax({
        url: 'inc/candidato_manual_inc_aj.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.status) {
                $('#msgAlertaAdicionar').html("<div class='alert alert-success py-2'>" + res.msg + "</div>");
                setTimeout(function () {
                    location.reload();
                }, 900);
            } else {
                btn.prop('disabled', false).html("<i class='fa-solid fa-floppy-disk me-2'></i>Adicionar ao Funil");
                $('#msgAlertaAdicionar').html("<div class='alert alert-danger py-2'>" + res.msg + "</div>");
            }
        },
        error: function () {
            btn.prop('disabled', false).html("<i class='fa-solid fa-floppy-disk me-2'></i>Adicionar ao Funil");
            $('#msgAlertaAdicionar').html("<div class='alert alert-danger py-2'>Falha de comunicação com o servidor.</div>");
        }
    });
}

function verCurriculo(candidaturaId) {
    const corpo = $('#corpoCurriculo');
    corpo.html('<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fa-2x text-info"></i></div>');
    $('#modalCurriculo').modal('show');

    $.get('inc/candidato_cv_aj.php', { candidatura_id: candidaturaId }, function (data) {
        if (!data.status) {
            corpo.html('<div class="alert alert-danger">' + (data.msg || 'Erro ao carregar currículo.') + '</div>');
            return;
        }
        corpo.html(montarHtmlCurriculo(data));
    }, 'json').fail(function () {
        corpo.html('<div class="alert alert-danger">Falha de comunicação com o servidor.</div>');
    });
}

function escapeHtml(txt) {
    return $('<div>').text(txt == null ? '' : txt).html();
}

//- descricao da experiência já chega como texto simples (sem tags) do servidor -
//- só reaplica as quebras de linha visuais depois de escapar.
function textoComQuebras(txt) {
    return escapeHtml(txt).replace(/\n/g, '<br>');
}

function montarHtmlCurriculo(data) {
    const p = data.dadosPessoais || {};
    const d = data.diversidade || {};
    let html = '';

    html += '<div class="cv-secao-titulo">Dados Pessoais</div>';
    html += '<div class="row small mb-3">';
    html += '<div class="col-md-6 mb-2"><span class="text-white-50">Nome:</span> ' + escapeHtml(p.nome) + '</div>';
    if (data.origem) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Origem:</span> ' + escapeHtml(data.origem) + '</div>';
    if (p.nomeSocial) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Nome Social:</span> ' + escapeHtml(p.nomeSocial) + '</div>';
    if (p.telefone) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Telefone:</span> ' + escapeHtml(p.telefone) + '</div>';
    if (p.email) html += '<div class="col-md-6 mb-2"><span class="text-white-50">E-mail:</span> ' + escapeHtml(p.email) + '</div>';
    if (p.sexo) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Sexo:</span> ' + escapeHtml(p.sexo) + '</div>';
    if (p.nacionalidade) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Nacionalidade:</span> ' + escapeHtml(p.nacionalidade) + '</div>';
    if (p.escolaridade) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Escolaridade:</span> ' + escapeHtml(p.escolaridade) + '</div>';
    if (d.linkedin) html += '<div class="col-md-6 mb-2"><span class="text-white-50">LinkedIn:</span> <a href="' + escapeHtml(d.linkedin) + '" target="_blank" class="text-info">' + escapeHtml(d.linkedin) + '</a></div>';
    html += '</div>';

    if (data.arquivoUrl) {
        html += '<a href="' + escapeHtml(data.arquivoUrl) + '" target="_blank" class="btn btn-outline-info btn-sm mb-3"><i class="fa-solid fa-download me-2"></i>Baixar Currículo Anexado</a>';
    }

    if (d.deficiente == 1 || d.nmCidade || d.cor || d.pronome || d.orientacao || d.idGenero) {
        html += '<div class="cv-secao-titulo">Diversidade</div>';
        html += '<div class="row small mb-3">';
        if (d.nmCidade) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Cidade:</span> ' + escapeHtml(d.nmCidade) + (d.uf ? '/' + escapeHtml(d.uf) : '') + '</div>';
        if (d.cor) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Cor/Raça:</span> ' + escapeHtml(d.cor) + '</div>';
        if (d.pronome) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Pronome:</span> ' + escapeHtml(d.pronome) + '</div>';
        if (d.orientacao) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Orientação:</span> ' + escapeHtml(d.orientacao) + '</div>';
        if (d.idGenero) html += '<div class="col-md-6 mb-2"><span class="text-white-50">Identidade de Gênero:</span> ' + escapeHtml(d.idGenero) + '</div>';
        if (d.deficiente == 1) {
            const tipos = ['fisica', 'visual', 'auditiva', 'mental', 'intelectual', 'autista']
                .filter(function (t) { return d['def_' + t] == 1; })
                .map(function (t) { return t.charAt(0).toUpperCase() + t.slice(1); });
            html += '<div class="col-12 mb-2"><span class="text-white-50">PCD:</span> ' + (tipos.join(', ') || 'Sim') + (d.cid && d.cid !== '0' ? ' — CID ' + escapeHtml(d.cid) : '') + '</div>';
        }
        html += '</div>';
    }

    html += '<div class="cv-secao-titulo">Experiências Profissionais</div>';
    if ((data.experiencias || []).length === 0) {
        html += '<p class="small text-white-50 mb-3">Nenhuma experiência cadastrada.</p>';
    } else {
        html += '<div class="mb-3">';
        data.experiencias.forEach(function (e) {
            const periodo = e.ano_ini + ' — ' + (e.ativo == 1 ? 'atual' : (e.ano_fim || '?'));
            html += '<div class="mb-2 pb-2 border-bottom border-secondary">';
            html += '<div class="d-flex justify-content-between"><span class="fw-bold small">' + escapeHtml(e.cargo) + '</span><span class="small text-white-50">' + periodo + '</span></div>';
            html += '<div class="small text-white-50">' + escapeHtml(e.empresa) + '</div>';
            if (e.descricao) html += '<div class="small mt-1">' + textoComQuebras(e.descricao) + '</div>';
            html += '</div>';
        });
        html += '</div>';
    }

    html += '<div class="cv-secao-titulo">Formação Acadêmica</div>';
    if ((data.formacoes || []).length === 0) {
        html += '<p class="small text-white-50 mb-3">Nenhuma formação cadastrada.</p>';
    } else {
        html += '<div class="mb-3">';
        data.formacoes.forEach(function (f) {
            html += '<div class="mb-2 small"><span class="fw-bold">' + escapeHtml(f.nivel) + '</span> — ' + escapeHtml(f.curso) + ' <span class="text-white-50">(' + escapeHtml(f.sigla) + ', ' + escapeHtml(f.ano_conclusao) + ')</span></div>';
        });
        html += '</div>';
    }

    html += '<div class="cv-secao-titulo">Idiomas</div>';
    if ((data.idiomas || []).length === 0) {
        html += '<p class="small text-white-50 mb-3">Nenhum idioma cadastrado.</p>';
    } else {
        html += '<div class="mb-3 small">';
        html += data.idiomas.map(function (i) { return escapeHtml(i.idioma) + ' (' + escapeHtml(i.fluencia) + ')'; }).join(' &middot; ');
        html += '</div>';
    }

    html += '<div class="cv-secao-titulo">Conquistas e Certificados</div>';
    if ((data.conquistas || []).length === 0) {
        html += '<p class="small text-white-50 mb-3">Nenhuma conquista cadastrada.</p>';
    } else {
        html += '<div class="mb-3">';
        data.conquistas.forEach(function (c) {
            html += '<div class="mb-1 small"><span class="fw-bold">' + escapeHtml(c.titulo) + '</span> — ' + escapeHtml(c.dsTipo) + ' <span class="text-white-50">(' + escapeHtml(c.ano) + ')</span></div>';
        });
        html += '</div>';
    }

    html += '<div class="cv-secao-titulo">Habilidades</div>';
    if ((data.habilidades || []).length === 0) {
        html += '<p class="small text-white-50">Nenhuma habilidade cadastrada.</p>';
    } else {
        html += '<div>';
        data.habilidades.forEach(function (h) {
            html += '<span class="badge bg-secondary me-1 mb-1">' + escapeHtml(h) + '</span>';
        });
        html += '</div>';
    }

    return html;
}

function atualizarContadores() {
    document.querySelectorAll('.cand-lista').forEach(function (lista) {
        const coluna = lista.closest('.cand-coluna');
        const contador = coluna ? coluna.querySelector('.cand-coluna-contador') : null;
        if (contador) contador.textContent = lista.children.length;
    });
}
