<?php
//
//- index.php | R&S - Módulo CRM de Recrutamento de Colaboradores
//- (C)haia, 24/07/2026
//- DASHBOARD

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../app/logout.php");
}

//- Inclui o arquivo de conexão com o banco de dados
include "../app/includes/conexao_gerar.php";

$baseDir = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$_SESSION['baseDir'] = $baseDir;

//
//- Board de 3 colunas = os 3 status de aprovação (rs_vagas_status). As vagas só mudam de
//- coluna automaticamente pelo sistema (aprova.php / vaga_new_inc_aj.php) - não é drag-and-drop.
//- Para acompanhar o pipeline pós-aprovação (Alinhamento, Divulgação, etc.), ver fluxo.php.
//
const STATUS_APROVADA = 2;

$sql = "SELECT id, status, descricao, cor_frente, cor_fundo FROM rs_vagas_status WHERE ativo = 1 ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$colunas_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT V.id, V.identificador, V.qtd, V.status_id, V.fluxo_id, V.criado_em,
               DATEDIFF(CURRENT_DATE(), DATE(V.criado_em)) AS dias,
               S.identificador AS subsede_ds,
               P.identificador AS polo_ds,
               C.nome AS cargo_ds,
               ST.cor_frente AS status_cor_frente, ST.cor_fundo AS status_cor_fundo,
               FL.status AS fluxo_ds, FL.cor_frente AS fluxo_cor_frente, FL.cor_fundo AS fluxo_cor_fundo
        FROM rs_vagas V
        LEFT JOIN rh_subsedes S ON S.subsede_id = V.subsede_id
        LEFT JOIN rh_polos P ON P.polo_id = V.polo_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        LEFT JOIN rs_vagas_status ST ON ST.id = V.status_id
        LEFT JOIN rs_vagas_fluxo FL ON FL.id = V.fluxo_id
        WHERE V.fechada_em IS NULL
        ORDER BY V.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$vagas_index = $stmt->fetchAll(PDO::FETCH_ASSOC);

$vagas_index_por_status = [];
foreach ($vagas_index as $v) {
    $vagas_index_por_status[(int) $v['status_id']][] = $v;
}

function index_card_html(array $v): string
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
    $busca = mb_strtolower(($titulo ?? '') . ' ' . $cargo_ds . ' ' . $local, 'UTF-8');

    $html = '<div class="kb-card" data-busca="' . htmlspecialchars($busca) . '">';
    $html .= '<div class="d-flex justify-content-between align-items-start gap-2">';
    if ($titulo !== null) {
        $html .= '<div class="fw-bold small mb-1">' . htmlspecialchars($titulo) . '</div>';
    }
    $html .= '<a href="vaga_view.php?id=' . $id . '" class="kb-card-olho ms-auto" title="Visualizar"><i class="fa-solid fa-eye"></i></a>';
    $html .= '</div>';
    if ($mostrar_cargo) {
        $html .= '<div class="small text-white-50">' . htmlspecialchars($cargo_ds !== '' ? $cargo_ds : '-') . '</div>';
    }
    $html .= '<div class="d-flex justify-content-between align-items-center mt-2">';
    $html .= '<small>' . htmlspecialchars($local) . '</small>';
    $html .= '<span class="badge bg-secondary">' . (int) $v['qtd'] . 'x</span>';
    $html .= '</div>';

    //- Etapa do fluxo de recrutamento (rs_vagas_fluxo) - só existe para vagas já aprovadas;
    //- aprovada sem fluxo_id ainda = aguardando recrutador (ver fluxo.php).
    if (!empty($v['fluxo_ds'])) {
        $fluxo_label = $v['fluxo_ds'];
        $fluxo_cor_frente = $v['fluxo_cor_frente'];
        $fluxo_cor_fundo = $v['fluxo_cor_fundo'];
    } elseif ((int) $v['status_id'] === STATUS_APROVADA) {
        $fluxo_label = 'Aguardando Recrutador';
        $fluxo_cor_frente = $v['status_cor_frente'];
        $fluxo_cor_fundo = $v['status_cor_fundo'];
    } else {
        $fluxo_label = null;
    }

    $html .= '<div class="d-flex align-items-center flex-wrap gap-2 mt-2">';
    $html .= '<kbd class="' . $dias_classe . ' fw-bold">' . $dias . 'd</kbd>';
    if ($fluxo_label !== null) {
        $cor_frente_classe = $fluxo_cor_frente ?: 'text-light';
        $cor_fundo_estilo = $fluxo_cor_fundo ?: '#6c757d';
        $html .= '<span class="badge kb-badge-fluxo ' . htmlspecialchars($cor_frente_classe) . '" style="background-color: ' . htmlspecialchars($cor_fundo_estilo) . ';">' . htmlspecialchars($fluxo_label) . '</span>';
    }
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

$titulo_pagina = "DASHBOARD"; // Define o Título aqui
include 'inc/header.php';

?>
<link href="css/index.css" rel="stylesheet" />

<style>
  .icon-box {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }

  .kb-board {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
  }

  @media (max-width: 767px) {
    .kb-board {
      grid-template-columns: 1fr;
    }
  }

  .kb-coluna-header {
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

  .kb-coluna-contador {
    background: rgba(255, 255, 255, .2);
    border-radius: 1rem;
    padding: .1rem .55rem;
    font-size: .72rem;
  }

  .kb-coluna-body {
    background: #1a1a1a;
    border: 1px solid #333;
    border-top: 0;
    border-radius: 0 0 .4rem .4rem;
    padding: .5rem;
    max-height: 60vh;
    overflow-y: auto;
  }

  .kb-card {
    background: #212529;
    border: 1px solid #333;
    border-radius: .4rem;
    padding: .6rem .7rem;
    margin-bottom: .6rem;
  }

  .kb-card small {
    color: #999;
  }

  .kb-badge-fluxo {
    font-size: .68rem;
    white-space: normal;
    text-align: center;
  }

  .kb-card-olho {
    color: #0dcaf0;
    flex-shrink: 0;
  }

  .kb-card-olho:hover {
    color: #6edff6;
  }
</style>

<main class="container-fluid p-3">
  
  <!-- Cabeçalho da Página -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1 text-white fw-bold">Painel de Recrutamento & Seleção</h4>
      <p class="text-white-50 small mb-0">Visão geral do fluxo de vagas e processos seletivos ativos.</p>
    </div>
    <a href="vaga_new.php" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
      <i class="fa-solid fa-plus fw"></i> Nova Vaga
    </a>
  </div>

  <div class="row g-3">
    
    <!-- COLUNA DA ESQUERDA: Cards Indicadores -->
    <div class="col-12 col-xl-4">
      <div class="row g-3">
        
        <!-- Vagas em Processo -->
        <div class="col-12 col-sm-6 col-xl-12">
          <div class="card bg-dark border-secondary text-white h-100 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <span class="text-white-50 small text-uppercase fw-semibold d-block">Em Recrutamento</span>
                <h3 class="mb-0 fw-bold text-primary mt-1" id="vagas_abertas">88</h3>
                <small class="text-secondary fs-7">Vagas ativas no funil</small>
              </div>
              <!-- Ícone Padronizado -->
              <div class="icon-box bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                <i class="fa-solid fa-briefcase fa-lg fw"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Aguardando Aprovação -->
        <div class="col-12 col-sm-6 col-xl-12">
          <div class="card bg-dark border-secondary text-white h-100 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <span class="text-white-50 small text-uppercase fw-semibold d-block">Aguardando Superintendência</span>
                <h3 class="mb-0 fw-bold text-warning mt-1" id="vagas_diretoria">88</h3>
                <small class="text-secondary fs-7">Aprovação pela Superintendência</small>
              </div>
              <!-- Ícone Padronizado -->
              <div class="icon-box bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                <i class="fa-solid fa-hourglass-half fa-lg fw"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Candidatos Contactados -->
        <div class="col-12 col-sm-6 col-xl-12">
          <div class="card bg-dark border-secondary text-white h-100 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <span class="text-white-50 small text-uppercase fw-semibold d-block">Triagem</span>
                <h3 class="mb-0 fw-bold text-info mt-1" id='vagas_triagem'>88</h3>
                <small class="text-secondary fs-7">Em contato ou entrevista</small>
              </div>
              <!-- Ícone Padronizado -->
              <div class="icon-box bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                <i class="fa-solid fa-users fa-lg fw"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Vagas Críticas / Atrasadas -->
        <div class="col-12 col-sm-6 col-xl-12">
          <div class="card bg-dark border-secondary text-white h-100 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <span class="text-white-50 small text-uppercase fw-semibold d-block">Vagas Críticas (>30 dias)</span>
                <h3 class="mb-0 fw-bold text-danger mt-1" id="vagas_criticas">02</h3>
                <small class="text-secondary fs-7">Acima do tempo limite (SLA)</small>
              </div>
              <!-- Ícone Padronizado -->
              <div class="icon-box bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                <i class="fa-solid fa-triangle-exclamation fa-lg fw"></i>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- COLUNA DA DIREITA: Board de Vagas em Aberto (por status de aprovação) -->
    <div class="col-12 col-xl-8">
      <div class="card bg-dark border-secondary text-white shadow-sm h-100">

        <div class="card-header border-secondary bg-transparent d-flex align-items-center justify-content-between py-3">
          <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-list-check me-2 text-primary fw"></i>Vagas em Processo</h6>
        </div>

        <div class="card-body">
          <input type="text" id="busca_vagas" class="form-control bg-dark border-secondary text-white mb-3" placeholder="Pesquisar por identificador, cargo ou local...">

          <div class="kb-board">
            <?php foreach ($colunas_status as $col):
                $lista = $vagas_index_por_status[(int) $col['id']] ?? [];
            ?>
            <div class="kb-coluna">
              <div class="kb-coluna-header <?= htmlspecialchars($col['cor_frente']) ?>" style="background-color: <?= htmlspecialchars($col['cor_fundo']) ?>;">
                <span><?= htmlspecialchars($col['status']) ?></span>
                <span class="kb-coluna-contador"><?= count($lista) ?></span>
              </div>
              <div class="kb-coluna-body">
                <?php if (empty($lista)): ?>
                <p class="text-white-50 small text-center py-3 mb-0">Nenhuma vaga</p>
                <?php else: ?>
                <?php foreach ($lista as $v) echo index_card_html($v); ?>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>

  </div>

</main>

<script src="js/index.js"></script>

<?php
include 'inc/footer.php' ;