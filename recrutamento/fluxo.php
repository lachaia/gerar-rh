<?php
//
//- fluxo.php | R&S | Pipeline (Kanban) do Fluxo de Recrutamento
//- (C)haia, 26/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
  exit();
}

//- Inclui o arquivo de conexão com o banco de dados
include "../app/includes/conexao_gerar.php";

//
//- rs_vagas.status_id é só o portão de aprovação (1=Solicitada, 2=Aprovada, 3=Reprovada).
//- Este board mostra apenas vagas Aprovadas (2); a etapa dentro do processo seletivo
//- fica em rs_vagas.fluxo_id -> rs_vagas_fluxo. fluxo_id NULL = aprovada, aguardando
//- recrutador (ainda não entrou no funil - ver inc/vaga_edit_salvar_aj.php).
//
const STATUS_APROVADA = 2;
const FLUXO_ALINHAMENTO = 1;
const FLUXO_CANCELADA = 6;

//- Coluna virtual "aguardando recrutador" (fluxo_id IS NULL) usa a cor do status APROVADA
$stmt = $conn->prepare("SELECT id, status, descricao, cor_frente, cor_fundo FROM rs_vagas_status WHERE id = :id");
$stmt->bindValue(':id', STATUS_APROVADA, PDO::PARAM_INT);
$stmt->execute();
$coluna_aguardando = $stmt->fetch(PDO::FETCH_ASSOC);

//- Colunas do board = etapas ativas do fluxo
$sql = "SELECT id, status, descricao, cor_frente, cor_fundo
        FROM rs_vagas_fluxo
        WHERE ativo = 1
        ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$todas_colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$colunas = [];
$coluna_cancelada = null;
foreach ($todas_colunas as $col) {
    if ((int) $col['id'] === FLUXO_CANCELADA) {
        $coluna_cancelada = $col;
    } else {
        $colunas[] = $col;
    }
}

//- Vagas visíveis no board = todas as vagas já aprovadas
$sql = "SELECT V.id, V.identificador, V.qtd, V.fluxo_id, V.recrutador_id, V.criado_em,
               DATEDIFF(CURRENT_DATE(), DATE(V.criado_em)) AS dias,
               DATEDIFF(CURRENT_DATE(), DATE(COALESCE(V.fluxo_alterado_em, V.criado_em))) AS dias_na_etapa,
               S.identificador AS subsede_ds,
               P.identificador AS polo_ds,
               C.nome AS cargo_ds,
               R.nome AS recrutador_ds,
               (SELECT COUNT(*) FROM rs_vagas_candidaturas CA WHERE CA.vaga_id = V.id) AS qtd_candidatos
        FROM rs_vagas V
        LEFT JOIN rh_subsedes S ON S.subsede_id = V.subsede_id
        LEFT JOIN rh_polos P ON P.polo_id = V.polo_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        LEFT JOIN rh_usuarios U ON U.idUsuario = V.recrutador_id
        LEFT JOIN rh_pessoas R ON R.idPessoa = U.idPessoa
        WHERE V.status_id = :status_aprovada
        ORDER BY V.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':status_aprovada', STATUS_APROVADA, PDO::PARAM_INT);
$stmt->execute();
$vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$vagas_por_fluxo = [];
foreach ($vagas as $v) {
    $chave = $v['fluxo_id'] === null ? 0 : (int) $v['fluxo_id'];
    $vagas_por_fluxo[$chave][] = $v;
}

//- Recrutadores ativos (grupo R&S) para o modal rápido de atribuição na coluna "Aguardando Recrutador"
$stmt = $conn->prepare("SELECT U.idUsuario, P.nome
                         FROM rh_usuarios U
                         INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa
                         WHERE U.idUsuarioGrupo = 6 AND U.ativo = 1
                         ORDER BY P.nome");
$stmt->execute();
$recrutadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

//- Reduz um nome completo a primeiro + último nome (ex.: "Luana Felix de Oliveira Cabral" -> "Luana Cabral")
function nome_reduzido(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome));
    if (count($partes) <= 1) {
        return $nome;
    }
    return $partes[0] . ' ' . end($partes);
}

function fluxo_card_html(array $v): string
{
    $id = (int) $v['id'];
    //- Sem identificador, não repete o cargo aqui - ele já aparece na linha logo abaixo.
    $titulo = ($v['identificador'] ?? '') !== '' ? $v['identificador'] : null;
    $cargo_ds = $v['cargo_ds'] ?? '';
    //- Se o identificador já contém o nome do cargo (ex.: "Analista Adm. II - Curitiba"), não repete embaixo.
    $mostrar_cargo = !($titulo !== null && $cargo_ds !== '' && stripos($titulo, $cargo_ds) !== false);
    $dias = (int) $v['dias'];
    $dias_classe = $dias > 30 ? 'bg-danger' : 'bg-success';
    $local = trim(($v['subsede_ds'] ?? '') . ' / ' . ($v['polo_ds'] ?? ''), ' /');
    if ($local === '') $local = '-';
    $tem_recrutador = !empty($v['recrutador_ds']);
    $recrutador_id_atual = $v['recrutador_id'] !== null ? (int) $v['recrutador_id'] : 0;

    //- A edição da vaga (vaga_edit.php) fica restrita à coluna ALINHAMENTO. Na coluna 1
    //- (Aguardando Recrutador) o gestor ainda não alinhou os detalhes com o recrutador;
    //- nas colunas seguintes (Divulgação em diante) só cabe consulta (vaga_view.php).
    $fluxo_id = $v['fluxo_id'] === null ? null : (int) $v['fluxo_id'];
    if ($fluxo_id === null) {
        $estado = 'aguardando';
    } elseif ($fluxo_id === FLUXO_ALINHAMENTO) {
        $estado = 'alinhamento';
    } else {
        $estado = 'outro';
    }

    $qtd_candidatos = (int) ($v['qtd_candidatos'] ?? 0);
    $html = '<div class="fluxo-card" data-vaga-id="' . $id . '" data-recrutador-id="' . $recrutador_id_atual . '" data-qtd-candidatos="' . $qtd_candidatos . '">';
    if ($titulo !== null) {
        if ($estado === 'aguardando') {
            $html .= '<div class="fluxo-card-titulo-wrap fw-bold small mb-1">' . htmlspecialchars($titulo) . '</div>';
        } elseif ($estado === 'alinhamento') {
            $html .= '<a href="vaga_edit.php?id=' . $id . '" class="fluxo-card-titulo-wrap"><div class="fw-bold small mb-1">' . htmlspecialchars($titulo) . '</div></a>';
        } else {
            $html .= '<a href="vaga_view.php?id=' . $id . '" class="fluxo-card-titulo-wrap"><div class="fw-bold small mb-1">' . htmlspecialchars($titulo) . '</div></a>';
        }
    }
    if ($mostrar_cargo) {
        $html .= '<div class="small text-white-50">' . htmlspecialchars($cargo_ds !== '' ? $cargo_ds : '-') . '</div>';
    }
    $html .= '<div class="d-flex justify-content-between align-items-center mt-2">';
    $html .= '<small>' . htmlspecialchars($local) . '</small>';
    $html .= '<span class="badge bg-secondary">' . (int) $v['qtd'] . 'x</span>';
    $html .= '</div>';
    //- Linha do recrutador sempre presente (mesmo vazia) para os cards ficarem
    //- com a mesma altura em todas as colunas, independente de já ter recrutador ou não.
    if ($tem_recrutador) {
        $html .= '<div class="small text-info mt-1"><i class="fa-solid fa-user"></i> ' . htmlspecialchars(nome_reduzido($v['recrutador_ds'])) . '</div>';
    } else {
        $html .= '<div class="small text-white-50 fst-italic mt-1"><i class="fa-solid fa-user-clock"></i> Aguardando recrutador</div>';
    }

    $dias_na_etapa = (int) $v['dias_na_etapa'];
    $dias_etapa_classe = $dias_na_etapa > 7 ? 'bg-warning text-dark' : 'bg-secondary';

    $html .= '<div class="d-flex align-items-center justify-content-between mt-2">';
    $html .= '<div class="d-flex align-items-center gap-1">';
    $html .= '<kbd class="' . $dias_classe . ' fw-bold" title="Dias desde a criação da vaga">' . $dias . 'd</kbd>';
    $html .= '<kbd class="fluxo-kbd-etapa ' . $dias_etapa_classe . ' fw-bold" title="Dias parada nesta etapa">' . $dias_na_etapa . 'd</kbd>';
    $html .= '</div>';
    $html .= '<div class="fluxo-card-acao d-flex gap-1">';
    if ($estado === 'aguardando') {
        $html .= '<button type="button" class="btn btn-warning btn-sm fluxo-btn-icone" onclick="abrirModalRecrutador(' . $id . ', ' . $recrutador_id_atual . ')" title="Selecionar Recrutador">';
        $html .= '<i class="fa-solid fa-user-plus"></i>';
        $html .= '</button>';
    } elseif ($estado === 'alinhamento') {
        $html .= '<a href="vaga_edit.php?id=' . $id . '" class="btn btn-outline-info btn-sm fluxo-btn-icone" title="Editar Vaga">';
        $html .= '<i class="fa-solid fa-pen"></i>';
        $html .= '</a>';
    } else {
        $html .= '<a href="vaga_candidatos.php?id=' . $id . '" class="btn btn-outline-info btn-sm fluxo-btn-icone position-relative" title="Ver Candidatos">';
        $html .= '<i class="fa-solid fa-people-arrows"></i>';
        if ($qtd_candidatos > 0) {
            $html .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">' . $qtd_candidatos . '</span>';
        }
        $html .= '</a>';
        $html .= '<a href="vaga_view.php?id=' . $id . '" class="btn btn-outline-secondary btn-sm fluxo-btn-icone" title="Ver Vaga">';
        $html .= '<i class="fa-solid fa-eye"></i>';
        $html .= '</a>';
    }
    $html .= '</div>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

$titulo_pagina = "FLUXO DE RECRUTAMENTO";
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

  .fluxo-board {
    display: flex;
    gap: 1rem;
    align-items: stretch;
    overflow-x: auto;
    padding-bottom: 1rem;
  }

  .fluxo-coluna {
    flex: 0 0 300px;
    width: 300px;
    display: flex;
    flex-direction: column;
  }

  .fluxo-coluna-cancelada {
    border-left: 2px dashed #495057;
    padding-left: 1rem;
    margin-left: .5rem;
  }

  .fluxo-coluna-header {
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

  .fluxo-coluna-contador {
    background: rgba(255, 255, 255, .2);
    border-radius: 1rem;
    padding: .1rem .55rem;
    font-size: .72rem;
  }

  .fluxo-coluna-body {
    background: #1a1a1a;
    border: 1px solid #333;
    border-top: 0;
    border-radius: 0 0 .4rem .4rem;
    min-height: 120px;
    padding: .5rem;
    flex: 1;
  }

  .fluxo-card {
    background: #212529;
    border: 1px solid #333;
    border-radius: .4rem;
    padding: .6rem .7rem;
    margin-bottom: .6rem;
    cursor: grab;
  }

  .fluxo-card:active {
    cursor: grabbing;
  }

  .fluxo-card a {
    color: #e0e0e0;
    text-decoration: none;
  }

  .fluxo-card a:hover {
    color: #0dcaf0;
  }

  .fluxo-card small {
    color: #999;
  }

  .fluxo-btn-icone {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .fluxo-card.sortable-ghost {
    opacity: .35;
  }

  .fluxo-card.sortable-drag {
    cursor: grabbing;
  }
</style>

<main class="container-fluid py-4 px-4">

  <!-- BARRA SUPERIOR E CABEÇALHO DA PÁGINA -->
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-info bg-opacity-10 text-info border border-info border-opacity-25">
        <i class="fa-brands fa-trello fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold">Fluxo de Recrutamento</h4>
        <p class="text-white-50 small mb-0">Vagas aprovadas em andamento no processo seletivo — arraste os cards entre as etapas</p>
      </div>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
      <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel R&S
    </a>
  </div>

  <div class="fluxo-board">
    <?php if ($coluna_aguardando):
        $lista = $vagas_por_fluxo[0] ?? [];
    ?>
    <div class="fluxo-coluna">
      <div class="fluxo-coluna-header <?= htmlspecialchars($coluna_aguardando['cor_frente']) ?>" style="background-color: <?= htmlspecialchars($coluna_aguardando['cor_fundo']) ?>;">
        <span>Início</span>
        <span class="fluxo-coluna-contador"><?= count($lista) ?></span>
      </div>
      <div class="fluxo-coluna-body fluxo-lista" data-fluxo-id="0">
        <?php foreach ($lista as $v) echo fluxo_card_html($v); ?>
      </div>
    </div>
    <?php endif; ?>

    <?php foreach ($colunas as $col):
        $lista = $vagas_por_fluxo[(int) $col['id']] ?? [];
    ?>
    <div class="fluxo-coluna">
      <div class="fluxo-coluna-header <?= htmlspecialchars($col['cor_frente']) ?>" style="background-color: <?= htmlspecialchars($col['cor_fundo']) ?>;">
        <span><?= htmlspecialchars($col['status']) ?></span>
        <span class="fluxo-coluna-contador"><?= count($lista) ?></span>
      </div>
      <div class="fluxo-coluna-body fluxo-lista" data-fluxo-id="<?= (int) $col['id'] ?>">
        <?php foreach ($lista as $v) echo fluxo_card_html($v); ?>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if ($coluna_cancelada):
        $lista = $vagas_por_fluxo[(int) $coluna_cancelada['id']] ?? [];
    ?>
    <div class="fluxo-coluna fluxo-coluna-cancelada">
      <div class="fluxo-coluna-header <?= htmlspecialchars($coluna_cancelada['cor_frente']) ?>" style="background-color: <?= htmlspecialchars($coluna_cancelada['cor_fundo']) ?>;">
        <span><?= htmlspecialchars($coluna_cancelada['status']) ?></span>
        <span class="fluxo-coluna-contador"><?= count($lista) ?></span>
      </div>
      <div class="fluxo-coluna-body fluxo-lista" data-fluxo-id="<?= (int) $coluna_cancelada['id'] ?>">
        <?php foreach ($lista as $v) echo fluxo_card_html($v); ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</main>

<!-- MODAL SELECIONAR RECRUTADOR (coluna "Aguardando Recrutador") -->
<div class="modal fade" id="modalRecrutador" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title">Selecionar Recrutador</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaRecrutador"></div>
        <p class="text-white-50 small">
          A vaga é movida para <strong>ALINHAMENTO</strong> assim que o recrutador é definido — a edição dos
          dados de publicação fica disponível a partir dessa etapa.
        </p>
        <input type="hidden" id="recrutador_vaga_id">
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">Recrutador Responsável *</label>
          <select id="recrutador_select" class="form-select">
            <option value="0">Selecione...</option>
            <?php foreach ($recrutadores as $r): ?>
              <option value="<?= (int) $r['idUsuario'] ?>"><?= htmlspecialchars($r['nome']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($recrutadores)): ?>
            <small class="text-warning">Nenhum usuário ativo cadastrado no grupo R&S ainda.</small>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="salvarRecrutador()">
          <i class="fa-solid fa-floppy-disk me-2"></i>Salvar e mover para Alinhamento
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL PUBLICAR VAGA (mover para DIVULGAÇÃO) -->
<div class="modal fade" id="modalPublicar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title">Publicar Vaga</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaPublicar"></div>
        <p class="text-white-50 small">
          A vaga passa a aparecer no portal público de vagas. Se quiser, defina até quando o anúncio deve
          ficar no ar — depois disso ela some da listagem automaticamente.
        </p>
        <input type="hidden" id="publicar_vaga_id">
        <div class="mb-2">
          <label class="form-label text-white-50 small fw-bold">Expira em (opcional)</label>
          <input type="date" id="publicar_expira_em" class="form-control">
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="confirmarPublicar()">
          <i class="fa-solid fa-bullhorn me-2"></i>Publicar Vaga
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="js/fluxo.js"></script>
<?php include 'inc/footer.php'; ?>
