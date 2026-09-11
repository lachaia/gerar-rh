<?php
//
//- vaga_view.php | R&S | Visualização (somente leitura) de uma Vaga
//- (C)haia, 26/08/2026
//
//- A edição da vaga (recrutador + dados de publicação) fica restrita ao fluxo.php/vaga_edit.php.
//- Esta tela é só consulta - acessada pela lupa da grade em index.php.
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
  exit();
}

//- Inclui o arquivo de conexão com o banco de dados
include "../app/includes/conexao_gerar.php";

$vaga_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$vaga_id) {
  header("location: index.php");
  exit();
}

$sql = "SELECT V.*,
               S.identificador AS subsede_ds,
               P.identificador AS polo_ds,
               O.descricao AS orgao_ds,
               C.nome AS cargo_ds,
               M.descricao AS motivo_ds,
               ST.status AS status_ds, ST.cor_frente AS status_cor_frente, ST.cor_fundo AS status_cor_fundo,
               FL.status AS fluxo_ds, FL.cor_frente AS fluxo_cor_frente, FL.cor_fundo AS fluxo_cor_fundo,
               R.nome AS recrutador_ds
        FROM rs_vagas V
        LEFT JOIN rh_subsedes S ON S.subsede_id = V.subsede_id
        LEFT JOIN rh_polos P ON P.polo_id = V.polo_id
        LEFT JOIN rh_organograma O ON O.idOrgao = V.orgao_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        LEFT JOIN rs_vagas_mot M ON M.id = V.motivo_id
        LEFT JOIN rs_vagas_status ST ON ST.id = V.status_id
        LEFT JOIN rs_vagas_fluxo FL ON FL.id = V.fluxo_id
        LEFT JOIN rh_usuarios U ON U.idUsuario = V.recrutador_id
        LEFT JOIN rh_pessoas R ON R.idPessoa = U.idPessoa
        WHERE V.id = :vaga_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->execute();
$vaga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vaga) {
  header("location: index.php");
  exit();
}

$sql = "SELECT A.*, S.identificador AS super_nome
        FROM rs_vagas_aprova A
        LEFT JOIN rs_superintendentes S ON S.id = A.super_id
        WHERE A.vaga_id = :vaga_id
        ORDER BY A.id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->execute();
$aprovacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT quando, oque, quem FROM rs_vagas_timeline WHERE vaga_id = :vaga_id ORDER BY quando DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->execute();
$timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "VAGA #{$vaga_id}"; // Define o Título aqui
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

  .status {
    height: 24px !important;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .campo-info {
    border-left: 3px solid #375a7f;
  }

  .campo-info .card-body {
    color: #e0e0e0;
  }

  .campo-label {
    color: #a2e3a9;
    font-size: .7rem;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: .03em;
    margin-bottom: .25rem;
  }

  .campo-valor {
    font-size: .9rem;
  }

  .campo-valor p:last-child {
    margin-bottom: 0;
  }

  /*- Alguns campos (descrição, diferenciais, benefícios) vêm do editor rico (Summernote)
    - como HTML; neutraliza cor/fundo inline que ele possa ter gravado (ex.: texto colado
    - do Word), senão quebra o tema escuro. */
  .campo-valor,
  .campo-valor * {
    color: inherit !important;
    background-color: transparent !important;
  }

  .campo-info-warning {
    border-left-color: #ffc107;
  }

  .campo-info-warning .campo-label,
  .campo-info-warning .campo-valor {
    color: #ffc107;
  }

  .campo-info-publicacao {
    border-left-color: #20c997;
  }

  .campo-info-reprovado {
    border-left-color: #dc3545;
  }
</style>

<main class="container-fluid px-4 px-xl-5 py-4">

  <!-- BARRA SUPERIOR E CABEÇALHO DA PÁGINA -->
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
        <i class="fa-solid fa-eye fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-1 text-white fw-bold">Vaga #<?= $vaga_id ?></h4>
        <p class="text-white-50 small mb-2"><?= htmlspecialchars($vaga['cargo_ds'] ?? '') ?></p>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge <?= htmlspecialchars($vaga['status_cor_frente'] ?? 'text-light') ?> status" style="background-color: <?= htmlspecialchars($vaga['status_cor_fundo'] ?? '#6c757d') ?>;"><?= htmlspecialchars($vaga['status_ds'] ?? '-') ?></span>
          <?php if (!empty($vaga['fluxo_ds'])): ?>
          <span class="badge <?= htmlspecialchars($vaga['fluxo_cor_frente']) ?> status" style="background-color: <?= htmlspecialchars($vaga['fluxo_cor_fundo']) ?>;"><?= htmlspecialchars($vaga['fluxo_ds']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
      <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel R&S
    </a>
  </div>

  <div class="row g-4">

    <!-- COLUNA ESQUERDA: DADOS DA SOLICITAÇÃO -->
    <div class="col-lg-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header border-secondary bg-transparent py-3">
          <h6 class="mb-0 text-primary fw-bold">
            <i class="fa-solid fa-file-lines me-2"></i>Dados Informados pelo Solicitante
          </h6>
        </div>
        <div class="card-body">
          <?php
          campo_info('Cargo', htmlspecialchars($vaga['cargo_ds'] ?? '-'));

          campo_info('Lotação',
              'SubSede ' . htmlspecialchars($vaga['subsede_ds'] ?? '-') . '<br>' .
              htmlspecialchars($vaga['polo_ds'] ?? '-') . '<br>' .
              htmlspecialchars($vaga['orgao_ds'] ?? '-')
          );

          campo_info('Motivo da Solicitação', htmlspecialchars($vaga['motivo_ds'] ?? '-'));

          campo_info('Formato / Tipo de Contrato',
              htmlspecialchars($vaga['forma_recrutamento'] ?? '-') . ' — ' . htmlspecialchars($vaga['tipo_contrato'] ?? '-')
          );

          campo_info('Regime de Trabalho', htmlspecialchars($vaga['modalidade'] ?? '-'));

          campo_info('Horário', htmlspecialchars($vaga['horario'] ?? '-'));

          campo_info('Quantidade / PCD',
              (int) $vaga['qtd'] . ' vaga(s) ' . ($vaga['pcd'] ? "<span class='badge bg-info ms-1'>Aceita PCD</span>" : '')
          );

          if (!empty($vaga['salario'])) {
              campo_info('Salário Informado', 'R$ ' . number_format((float) $vaga['salario'], 2, ',', '.'));
          }

          $formacao_html = htmlspecialchars($vaga['formacao'] ?? '-');
          if (!empty($vaga['curso_superior'])) $formacao_html .= "<br><small class='text-white-50'>Curso: " . htmlspecialchars($vaga['curso_superior']) . "</small>";
          if (!empty($vaga['curso_estagio_1'])) $formacao_html .= "<br><small class='text-white-50'>Estágio 1: " . htmlspecialchars($vaga['curso_estagio_1']) . "</small>";
          if (!empty($vaga['curso_estagio_2'])) $formacao_html .= "<br><small class='text-white-50'>Estágio 2: " . htmlspecialchars($vaga['curso_estagio_2']) . "</small>";
          campo_info('Formação Necessária', $formacao_html);

          campo_info('Experiência Desejada', conteudo_rico($vaga['experiencia']) ?: '-');
          campo_info('Atividades da Vaga', conteudo_rico($vaga['atividades']) ?: '-');
          campo_info('Competências Técnicas', $vaga['c_tecnicas'] ?: '-');
          campo_info('Competências Comportamentais', $vaga['c_comportamentais'] ?: '-');
          campo_info('Equipamentos Solicitados', $vaga['equipamentos'] ?: '-');

          if (!empty($vaga['obs'])) {
              campo_info('Observações', conteudo_rico($vaga['obs']));
          }

          campo_info('Preferências (uso interno — não publicar)',
              'Gênero: ' . htmlspecialchars($vaga['genero'] ?? '-') . ' | ' .
              'Faixa etária: ' . htmlspecialchars($vaga['etaria'] ?? '-') . ' | ' .
              'CNH: ' . htmlspecialchars($vaga['cnh'] ?? '-'),
              'warning'
          );

          campo_info('Solicitado por', htmlspecialchars($vaga['criado_por'] ?? '-') . ' em ' . date('d/m/Y', strtotime($vaga['criado_em'])));

          if (!empty($vaga['arquivo_aut_gestor'])):
              $url_arquivo = 'docs/' . rawurlencode($vaga['arquivo_aut_gestor']);
          ?>
          <a href="<?= htmlspecialchars($url_arquivo) ?>" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm mt-2">
            <i class="fa-solid fa-file-lines me-1"></i>Ver Autorização do Gestor
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- COLUNA DIREITA: DADOS DE PUBLICAÇÃO (SOMENTE LEITURA) -->
    <div class="col-lg-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header border-secondary bg-transparent py-3">
          <h6 class="mb-0 text-primary fw-bold">
            <i class="fa-solid fa-bullhorn me-2"></i>Dados para Publicação da Vaga
          </h6>
        </div>
        <div class="card-body">
          <?php if (empty($vaga['recrutador_ds'])): ?>
          <p class="text-white-50 mb-0">
            <i class="fa-solid fa-circle-info me-1"></i>
            Esta vaga ainda não teve um recrutador atribuído — os dados de publicação são preenchidos a partir da etapa
            de Alinhamento, no fluxo de recrutamento.
          </p>
          <?php else: ?>
          <?php
          campo_info('Recrutador Responsável', htmlspecialchars($vaga['recrutador_ds']));
          campo_info('Identificador da Vaga', htmlspecialchars($vaga['identificador'] ?: '-'));
          campo_info('Código da Vaga', htmlspecialchars($vaga['codigo_vaga'] ?: '-'));
          campo_info('Setor / Área', htmlspecialchars($vaga['setor_ds'] ?: '-') . ' / ' . htmlspecialchars($vaga['area'] ?: '-'));
          campo_info('Nível de Experiência', htmlspecialchars($vaga['nivel_experiencia'] ?: '-'));
          campo_info('Descrição da Vaga', conteudo_rico($vaga['descricao']) ?: '-');
          campo_info('Resumo', nl2br(htmlspecialchars($vaga['resumo'] ?: '-')));
          if (!empty($vaga['diferenciais'])) campo_info('Diferenciais', conteudo_rico($vaga['diferenciais']));
          if (!empty($vaga['beneficios'])) campo_info('Benefícios', conteudo_rico($vaga['beneficios']));
          ?>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($aprovacoes)): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header border-secondary bg-transparent py-3">
          <h6 class="mb-0 text-primary fw-bold">
            <i class="fa-solid fa-user-check me-2"></i>Aprovação dos Superintendentes
          </h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <?php foreach ($aprovacoes as $a):
                $classe_extra = !empty($a['reprovado_em']) ? ' campo-info-reprovado' : '';
            ?>
            <div class="col-md-4">
              <div class="card campo-info<?= $classe_extra ?> h-100">
                <div class="card-body py-2 px-3">
                  <div class="campo-label"><?= htmlspecialchars($a['super_nome'] ?? 'Superintendente') ?></div>
                  <div class="campo-valor">
                    <?php if (!empty($a['aprovado_em'])): ?>
                      <span class="badge bg-success mb-1">Aprovado</span>
                      <div class="small text-white-50">em <?= date('d/m/Y \à\s H:i', strtotime($a['aprovado_em'])) ?></div>
                    <?php elseif (!empty($a['reprovado_em'])): ?>
                      <span class="badge bg-danger mb-1">Reprovado</span>
                      <div class="small text-white-50">em <?= date('d/m/Y \à\s H:i', strtotime($a['reprovado_em'])) ?></div>
                      <?php if (!empty($a['obs'])): ?>
                      <div class="mt-2"><strong class="text-danger">Motivo:</strong> <?= nl2br(htmlspecialchars($a['obs'])) ?></div>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="badge bg-secondary">Aguardando decisão</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($timeline)): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header border-secondary bg-transparent py-3">
          <h6 class="mb-0 text-primary fw-bold">
            <i class="fa-solid fa-timeline me-2"></i>Linha do Tempo
          </h6>
        </div>
        <div class="card-body">
          <?php foreach ($timeline as $t): ?>
          <div class="card campo-info mb-2">
            <div class="card-body py-2 px-3">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="campo-valor"><?= htmlspecialchars($t['oque']) ?></div>
                <span class="badge bg-secondary flex-shrink-0"><?= date('d/m/Y H:i', strtotime($t['quando'])) ?></span>
              </div>
              <div class="small text-white-50 mt-1"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($t['quem']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>

</main>

<?php
include 'inc/footer.php';

//
//- descricao/experiencia/atividades/diferenciais/beneficios/obs podem vir do editor rico
//- (Summernote, já em HTML) ou como texto simples com quebras de linha normais (ex.:
//- preenchido por IA). Só escapa e quebra linha quando não há tag nenhuma.
//
function conteudo_rico(?string $valor): string
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        return '';
    }
    return strip_tags($valor) === $valor ? nl2br(htmlspecialchars($valor)) : $valor;
}

//
//- Exibe um campo de dados dentro de um mini card Bootstrap (label em destaque + conteúdo).
//- $conteudo_html já deve vir pronto (htmlspecialchars() aplicado pelo chamador quando necessário).
//
function campo_info(string $label, string $conteudo_html, string $variante = 'padrao'): void
{
  $classe_extra = $variante === 'warning' ? ' campo-info-warning' : '';
  echo "<div class='card campo-info{$classe_extra} mb-2'>
          <div class='card-body py-2 px-3'>
            <div class='campo-label'>{$label}</div>
            <div class='campo-valor'>{$conteudo_html}</div>
          </div>
        </div>";
}
