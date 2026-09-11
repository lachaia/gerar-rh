<?php
//
//- vaga_candidatos.php | R&S | Espaço de trabalho da vaga - Kanban de candidatos
//- (C)haia, 27/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
  exit();
}

include "../app/includes/conexao_gerar.php";

const FLUXO_REJEITADO = 6;
const FLUXO_SCREENING = 3;
const ORIGEM_RECRUTAMENTO_INTERNO = 8;
const ORIGEM_INDICACAO = 2;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("location: fluxo.php");
    exit();
}

$sql = "SELECT V.id, V.identificador, C.nome AS cargo_ds
        FROM rs_vagas V
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        WHERE V.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$vaga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vaga) {
    header("location: fluxo.php");
    exit();
}

$titulo_vaga = $vaga['identificador'] ?: ($vaga['cargo_ds'] ?? 'Vaga');

//- Colunas do board = estágios ativos do funil de candidato
$stmt = $conn->prepare("SELECT id, status, descricao, cor_frente, cor_fundo
                         FROM rs_candidatos_fluxo
                         WHERE ativo = 1
                         ORDER BY id");
$stmt->execute();
$todas_colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$colunas = [];
$coluna_rejeitado = null;
foreach ($todas_colunas as $col) {
    if ((int) $col['id'] === FLUXO_REJEITADO) {
        $coluna_rejeitado = $col;
    } else {
        $colunas[] = $col;
    }
}

//- Candidatos (candidaturas) desta vaga
$sql = "SELECT CA.id, CA.fluxo_id, CA.motivo_rejeicao, CA.criado_em, CA.origem_id,
               CA.screening_data, CA.screening_meet_link,
               P.nome, P.telefone, P.email,
               O.descricao AS origem_ds
        FROM rs_vagas_candidaturas CA
        INNER JOIN rh_pessoas P ON P.idPessoa = CA.pessoa_id
        LEFT JOIN rs_candidatos_origem O ON O.id = CA.origem_id
        WHERE CA.vaga_id = :vaga_id
        ORDER BY CA.criado_em ASC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':vaga_id', $id, PDO::PARAM_INT);
$stmt->execute();
$candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

//- Origens ativas para o combo do modal "Adicionar Candidato"
$stmt = $conn->prepare("SELECT id, descricao FROM rs_candidatos_origem WHERE ativo = 1 ORDER BY descricao");
$stmt->execute();
$origens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$candidatos_por_fluxo = [];
foreach ($candidatos as $c) {
    $chave = (int) $c['fluxo_id'];
    $candidatos_por_fluxo[$chave][] = $c;
}

function candidato_card_html(array $c): string
{
    $id = (int) $c['id'];
    $fluxo_id_atual = (int) $c['fluxo_id'];
    $origem_id_atual = (int) ($c['origem_id'] ?? 0);
    $eh_interno = $origem_id_atual === ORIGEM_RECRUTAMENTO_INTERNO;
    $eh_indicacao = $origem_id_atual === ORIGEM_INDICACAO;
    $classe_origem = $eh_interno ? ' cand-card-interno' : ($eh_indicacao ? ' cand-card-indicacao' : '');
    $titulo_origem = $eh_interno ? 'Recrutamento Interno' : ($eh_indicacao ? 'Indicação' : '');
    $html = '<div class="cand-card' . $classe_origem . '" data-candidatura-id="' . $id . '" title="' . $titulo_origem . '">';
    $html .= '<div class="fw-bold small mb-1">' . htmlspecialchars($c['nome']) . '</div>';
    if (!empty($c['telefone'])) {
        $html .= '<div class="small text-white-50"><i class="fa-solid fa-phone me-1"></i>' . htmlspecialchars($c['telefone']) . '</div>';
    }
    if (!empty($c['email'])) {
        $html .= '<div class="small text-white-50 text-truncate"><i class="fa-solid fa-envelope me-1"></i>' . htmlspecialchars($c['email']) . '</div>';
    }
    if (!empty($c['origem_ds'])) {
        $html .= '<div class="small text-white-50"><i class="fa-solid fa-signs-post me-1"></i>' . htmlspecialchars($c['origem_ds']) . '</div>';
    }
    $html .= '<div class="d-flex justify-content-between align-items-center mt-1">';
    $html .= '<span class="small text-info"><i class="fa-solid fa-calendar-plus me-1"></i>' . date('d/m/Y', strtotime($c['criado_em'])) . '</span>';
    $html .= '<div class="d-flex gap-1">';
    if ($fluxo_id_atual >= FLUXO_SCREENING && $fluxo_id_atual !== FLUXO_REJEITADO) {
        $html .= '<a href="candidato_parecer.php?candidatura_id=' . $id . '" class="btn btn-outline-warning btn-sm cand-btn-icone" title="Parecer de Screening">';
        $html .= '<i class="fa-solid fa-file-signature"></i>';
        $html .= '</a>';
    }
    $html .= '<button type="button" class="btn btn-outline-info btn-sm cand-btn-icone" title="Ver Currículo" onclick="verCurriculo(' . $id . ')">';
    $html .= '<i class="fa-solid fa-file-lines"></i>';
    $html .= '</button>';
    $html .= '</div>';
    $html .= '</div>';
    if (!empty($c['screening_data']) && $fluxo_id_atual !== FLUXO_REJEITADO) {
        $html .= '<div class="small text-warning mt-1">';
        $html .= '<i class="fa-solid fa-video me-1"></i>Screening: ' . date('d/m/Y H:i', strtotime($c['screening_data']));
        if (!empty($c['screening_meet_link'])) {
            $html .= ' <a href="' . htmlspecialchars($c['screening_meet_link']) . '" target="_blank" class="text-warning" title="Entrar na reunião"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
        }
        $html .= '</div>';
    }
    if (!empty($c['motivo_rejeicao'])) {
        $html .= '<div class="small text-danger mt-1 fst-italic">' . htmlspecialchars($c['motivo_rejeicao']) . '</div>';
    }
    $html .= '</div>';
    return $html;
}

$titulo_pagina = "CANDIDATOS - " . $titulo_vaga;
include 'inc/header.php';
?>
<style>
  .icon-box {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }

  .cand-board {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    overflow-x: auto;
    padding-bottom: 1rem;
  }

  .cand-coluna {
    flex: 0 0 280px;
    width: 280px;
  }

  .cand-coluna-rejeitado {
    border-left: 2px dashed #495057;
    padding-left: 1rem;
    margin-left: .5rem;
  }

  .cand-coluna-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .6rem .8rem;
    border-radius: .4rem .4rem 0 0;
    font-weight: 700;
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .4px;
  }

  .cand-coluna-contador {
    background: rgba(255, 255, 255, .2);
    border-radius: 1rem;
    padding: .1rem .55rem;
    font-size: .72rem;
  }

  .cand-coluna-body {
    background: #1a1a1a;
    border: 1px solid #333;
    border-top: 0;
    border-radius: 0 0 .4rem .4rem;
    min-height: 120px;
    padding: .5rem;
  }

  .cand-card {
    background: #212529;
    border: 1px solid #333;
    border-radius: .4rem;
    padding: .6rem .7rem;
    margin-bottom: .6rem;
    cursor: grab;
  }

  /* Diferencia candidatos de Recrutamento Interno no board */
  .cand-card-interno {
    border-right: 4px solid #ffc107;
  }

  .cand-card-indicacao {
    border-right: 4px solid #6f42c1;
  }

  .cand-card:active {
    cursor: grabbing;
  }

  .cand-card.sortable-ghost {
    opacity: .35;
  }

  .cand-card.sortable-drag {
    cursor: grabbing;
  }

  .cand-btn-icone {
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .cv-secao-titulo {
    color: #0dcaf0;
    font-weight: 700;
    font-size: .85rem;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-bottom: 1px solid #333;
    padding-bottom: .3rem;
    margin-bottom: .6rem;
  }
</style>

<main class="container-fluid py-4 px-4">

  <!-- BARRA SUPERIOR E CABEÇALHO DA PÁGINA -->
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-info bg-opacity-10 text-info border border-info border-opacity-25">
        <i class="fa-solid fa-people-arrows fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold"><?= htmlspecialchars($titulo_vaga) ?></h4>
        <p class="text-white-50 small mb-0">Candidatos desta vaga — arraste os cards entre as etapas</p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-outline-info btn-sm d-flex align-items-center gap-2" onclick="abrirModalAdicionar()">
        <i class="fa-solid fa-user-plus"></i> Adicionar Candidato
      </button>
      <a href="fluxo.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Fluxo
      </a>
    </div>
  </div>

  <?php if (empty($candidatos)): ?>
    <div class="alert alert-secondary text-center">Nenhum candidato se candidatou a esta vaga ainda.</div>
  <?php endif; ?>

  <div class="cand-board">
    <?php foreach ($colunas as $col):
        $lista = $candidatos_por_fluxo[(int) $col['id']] ?? [];
    ?>
    <div class="cand-coluna">
      <div class="cand-coluna-header <?= htmlspecialchars($col['cor_frente']) ?>" style="background-color: <?= htmlspecialchars($col['cor_fundo']) ?>;">
        <span><?= htmlspecialchars($col['status']) ?></span>
        <span class="cand-coluna-contador"><?= count($lista) ?></span>
      </div>
      <div class="cand-coluna-body cand-lista" data-fluxo-id="<?= (int) $col['id'] ?>">
        <?php foreach ($lista as $c) echo candidato_card_html($c); ?>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if ($coluna_rejeitado):
        $lista = $candidatos_por_fluxo[(int) $coluna_rejeitado['id']] ?? [];
    ?>
    <div class="cand-coluna cand-coluna-rejeitado">
      <div class="cand-coluna-header <?= htmlspecialchars($coluna_rejeitado['cor_frente']) ?>" style="background-color: <?= htmlspecialchars($coluna_rejeitado['cor_fundo']) ?>;">
        <span><?= htmlspecialchars($coluna_rejeitado['status']) ?></span>
        <span class="cand-coluna-contador"><?= count($lista) ?></span>
      </div>
      <div class="cand-coluna-body cand-lista" data-fluxo-id="<?= (int) $coluna_rejeitado['id'] ?>">
        <?php foreach ($lista as $c) echo candidato_card_html($c); ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</main>

<!-- MODAL AGENDAR SCREENING (Google Calendar + Meet) -->
<div class="modal fade" id="modalAgendarScreening" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="fa-solid fa-video me-2"></i>Agendar Screening</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaAgendar"></div>
        <p class="text-white-50 small">
          Cria um evento no Google Calendar da recrutadora responsável, com Google Meet automático,
          convidando o candidato por e-mail.
        </p>
        <input type="hidden" id="agendar_candidatura_id">
        <div class="row g-2">
          <div class="col-7">
            <label class="form-label text-white-50 small fw-bold">Data *</label>
            <input type="date" id="agendar_data" class="form-control">
          </div>
          <div class="col-5">
            <label class="form-label text-white-50 small fw-bold">Horário *</label>
            <input type="time" id="agendar_hora" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="btnConfirmarAgendar" onclick="confirmarAgendarScreening()">
          <i class="fa-solid fa-calendar-check me-2"></i>Agendar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ADICIONAR CANDIDATO MANUALMENTE (indicação, e-mail, LinkedIn etc.) -->
<div class="modal fade" id="modalAdicionar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Adicionar Candidato</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaAdicionar"></div>
        <p class="text-white-50 small">
          Para candidatos que chegaram por indicação, e-mail ou LinkedIn — fora do portal público.
          Se o CPF já existir na base, o cadastro é reaproveitado.
        </p>
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">Origem *</label>
          <select id="adicionar_origem" class="form-select">
            <option value="">Selecione...</option>
            <?php foreach ($origens as $o): ?>
              <option value="<?= (int) $o['id'] ?>"><?= htmlspecialchars($o['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">CPF *</label>
          <input type="text" id="adicionar_cpf" class="form-control" placeholder="000.000.000-00">
          <div id="msgCpfExistente" class="small mt-1"></div>
        </div>
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">Nome *</label>
          <input type="text" id="adicionar_nome" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">Telefone</label>
          <input type="text" id="adicionar_telefone" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">E-mail</label>
          <input type="email" id="adicionar_email" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">LinkedIn</label>
          <input type="text" id="adicionar_linkedin" class="form-control" placeholder="https://www.linkedin.com/in/...">
        </div>
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">Currículo (PDF, DOC ou DOCX)</label>
          <input type="file" id="adicionar_arquivo" class="form-control" accept=".pdf,.doc,.docx">
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-info" id="btnConfirmarAdicionar" onclick="confirmarAdicionar()">
          <i class="fa-solid fa-floppy-disk me-2"></i>Adicionar ao Funil
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL MOTIVO DA REJEIÇÃO -->
<div class="modal fade" id="modalRejeitar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title">Rejeitar Candidato</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaRejeitar"></div>
        <input type="hidden" id="rejeitar_candidatura_id">
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">Motivo (opcional)</label>
          <textarea id="rejeitar_motivo" class="form-control" rows="3" placeholder="Ex.: Perfil não aderente, pretensão salarial, desistência..."></textarea>
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="confirmarRejeitar()">
          <i class="fa-solid fa-user-xmark me-2"></i>Rejeitar Candidato
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL CURRÍCULO (somente leitura) -->
<div class="modal fade" id="modalCurriculo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="fa-solid fa-file-lines me-2"></i>Currículo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body" id="corpoCurriculo">
        <div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fa-2x text-info"></i></div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>var VAGA_ID = <?= (int) $id ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="js/vaga_candidatos.js"></script>
<?php include 'inc/footer.php'; ?>
