<?php
//
//- vaga_edit.php | R&S | Atribuição de Recrutador e Preenchimento dos Dados de Publicação da Vaga
//- (C)haia, 24/08/2026
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
               ST.status AS status_ds,
               ST.cor_frente,
               ST.cor_fundo
        FROM rs_vagas V
        LEFT JOIN rh_subsedes S ON S.subsede_id = V.subsede_id
        LEFT JOIN rh_polos P ON P.polo_id = V.polo_id
        LEFT JOIN rh_organograma O ON O.idOrgao = V.orgao_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        LEFT JOIN rs_vagas_mot M ON M.id = V.motivo_id
        LEFT JOIN rs_vagas_status ST ON ST.id = V.status_id
        WHERE V.id = :vaga_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->execute();
$vaga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vaga) {
  header("location: index.php");
  exit();
}

$titulo_pagina = "EDITAR VAGA #{$vaga_id}"; // Define o Título aqui
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

  .campo-sugerido {
    border-color: #ffc107 !important;
  }

  .badge-sugerido {
    font-size: .65rem;
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

  /*- experiencia/atividades vêm do editor rico (Summernote) como HTML; neutraliza cor/fundo
    - inline que ele possa ter gravado (ex.: texto colado do Word), senão quebra o tema escuro. */
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
</style>

<main class="container py-4">

  <!-- BARRA SUPERIOR E CABEÇALHO DA PÁGINA -->
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
        <i class="fa-solid fa-pen-to-square fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold">Editar Vaga #<?= $vaga_id ?></h4>
        <p class="text-white-50 small mb-0">
          <?= htmlspecialchars($vaga['cargo_ds'] ?? '') ?>
          <span class="badge <?= $vaga['cor_frente'] ?> status ms-2" style="background-color: <?= $vaga['cor_fundo'] ?>;"><?= htmlspecialchars($vaga['status_ds'] ?? '') ?></span>
        </p>
      </div>
    </div>
    <a href="fluxo.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
      <i class="fa-solid fa-arrow-left"></i> Voltar ao Fluxo
    </a>
  </div>

  <div class="row g-4">

    <!-- COLUNA ESQUERDA: DADOS DA SOLICITAÇÃO (SOMENTE LEITURA) -->
    <div class="col-lg-5">
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
          ?>
        </div>
      </div>
    </div>

    <!-- COLUNA DIREITA: DADOS PARA PUBLICAÇÃO (EDITÁVEL) -->
    <div class="col-lg-7">
      <div class="card shadow-sm mb-4">
        <div class="card-header border-secondary bg-transparent py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 text-primary fw-bold">
            <i class="fa-solid fa-bullhorn me-2"></i>Dados para Publicação da Vaga
          </h6>
          <button type="button" class="btn btn-outline-warning btn-sm" onclick="sugerir_preenchimento(true)">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Sugerir Preenchimento
          </button>
        </div>
        <div class="card-body">
          <div id="msgAlertaVaga"></div>

          <form id="formVagaEdit">
            <input type="hidden" name="id" value="<?= $vaga_id ?>">

            <div class="mb-3">
              <label class="form-label text-white-50 small fw-bold">Recrutador Responsável *</label>
              <?php seletor_recrutador($conn, $vaga['recrutador_id']); ?>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">Nome do Gestor da Vaga</label>
                <input type="text" name="gestor_nome" id="campo_gestor_nome" class="form-control" maxlength="150" value="<?= htmlspecialchars($vaga['gestor_nome'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">E-mail do Gestor da Vaga</label>
                <input type="email" name="gestor_email" id="campo_gestor_email" class="form-control" maxlength="200" value="<?= htmlspecialchars($vaga['gestor_email'] ?? '') ?>">
                <small class="text-white-50">Usado para enviar o parecer de screening dos candidatos.</small>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label text-white-50 small fw-bold">Identificador da Vaga *</label>
                <input type="text" name="identificador" id="campo_identificador" class="form-control" maxlength="150" value="<?= htmlspecialchars($vaga['identificador'] ?? '') ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label text-white-50 small fw-bold">Código da Vaga *</label>
                <input type="text" name="codigo_vaga" id="campo_codigo_vaga" class="form-control" maxlength="20" value="<?= htmlspecialchars($vaga['codigo_vaga'] ?? '') ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">Setor *</label>
                <input type="text" name="setor_ds" id="campo_setor" class="form-control" maxlength="45" value="<?= htmlspecialchars($vaga['setor_ds'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">Área *</label>
                <input type="text" name="area" id="campo_area" class="form-control" maxlength="45" value="<?= htmlspecialchars($vaga['area'] ?? '') ?>" required>
              </div>

              <div class="col-md-12">
                <label class="form-label text-white-50 small fw-bold">Nível de Experiência *</label>
                <input type="text" name="nivel_experiencia" id="campo_nivel_experiencia" class="form-control" list="lista_niveis" maxlength="45" value="<?= htmlspecialchars($vaga['nivel_experiencia'] ?? '') ?>" required>
                <datalist id="lista_niveis">
                  <option value="Estagiário">
                  <option value="Sem experiência anterior">
                  <option value="Júnior">
                  <option value="Pleno">
                  <option value="Sênior">
                  <option value="Especialista">
                </datalist>
              </div>

              <div class="col-md-12">
                <label class="form-label text-white-50 small fw-bold">Descrição da Vaga *</label>
                <textarea name="descricao" id="campo_descricao" class="form-control" rows="6" required><?= htmlspecialchars($vaga['descricao'] ?? '') ?></textarea>
              </div>

              <div class="col-md-12">
                <label class="form-label text-white-50 small fw-bold">Resumo (aparece na listagem de vagas) *</label>
                <textarea name="resumo" id="campo_resumo" class="form-control" rows="2" maxlength="255" required><?= htmlspecialchars($vaga['resumo'] ?? '') ?></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">Diferenciais</label>
                <textarea name="diferenciais" id="campo_diferenciais" class="form-control" rows="4"><?= htmlspecialchars($vaga['diferenciais'] ?? '') ?></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">Benefícios</label>
                <textarea name="beneficios" id="campo_beneficios" class="form-control" rows="4"><?= htmlspecialchars($vaga['beneficios'] ?? '') ?></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="card-footer border-secondary bg-transparent d-flex justify-content-end gap-2 py-3">
          <button type="button" class="btn btn-primary px-4 fw-bold" onclick="salvar_vaga()">
            <i class="fa-solid fa-floppy-disk me-2"></i>Salvar
          </button>
        </div>
      </div>
    </div>

  </div>

</main>

<script src="js/vaga_edit.js"></script>

<?php
include 'inc/footer.php';

//
//- Monta o <select> de recrutadores: usuários ativos do grupo R&S (idUsuarioGrupo = 6)
//
function seletor_recrutador($conn, $selecionado = null)
{
  $sql = "SELECT U.idUsuario, P.nome
          FROM rh_usuarios U
          INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa
          WHERE U.idUsuarioGrupo = 6 AND U.ativo = 1
          ORDER BY P.nome";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<select name='recrutador_id' id='recrutador_id' class='form-select' required>";
  echo "<option value='0' class='text-warning'" . ($selecionado ? "" : " selected") . ">Selecione...</option>";
  foreach ($linhas as $row) {
    $sel = ($selecionado && $selecionado == $row['idUsuario']) ? "selected" : "";
    echo "<option value='{$row['idUsuario']}' $sel>" . htmlspecialchars($row['nome']) . "</option>";
  }
  echo "</select>";

  if (empty($linhas)) {
    echo "<small class='text-warning'>Nenhum usuário ativo cadastrado no grupo R&S (idUsuarioGrupo = 6) ainda.</small>";
  }
}

//
//- experiencia/atividades/obs podem vir do editor rico (Summernote, já em HTML) ou como
//- texto simples com quebras de linha normais (ex.: preenchido por IA). Só escapa e
//- quebra linha quando não há tag nenhuma.
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
//- Exibe um campo de "Dados Informados pelo Solicitante" dentro de um mini card
//- Bootstrap (label em destaque + conteúdo). $conteudo_html já deve vir pronto
//- (htmlspecialchars() aplicado pelo chamador quando necessário).
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
