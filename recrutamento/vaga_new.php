<?php
//
//- vaga_new.php | R&S | Inclusão de Vaga pelo Recrutador
//- (C)haia, 24/07/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
}

//- Inclui o arquivo de conexão com o banco de dados
include "../app/includes/conexao_gerar.php";

$titulo_pagina = "INCLUSÃO DE VAGA"; // Define o Título aqui
include 'inc/header.php';

?>
<link href="css/vaga_new.css" rel="stylesheet" />

<main class="container py-4">

  <!-- BARRA SUPERIOR E CABEÇALHO DA PÁGINA -->
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
        <i class="fa-solid fa-file-signature fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold">Solicitação de Abertura de Vaga</h4>
        <p class="text-white-50 small mb-0">Portal Intranet — Módulo de Recrutamento & Seleção</p>
      </div>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
      <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel R&S
    </a>
  </div>

  <form id="formSolicitacaoVaga" action="vaga_salvar.php" method="POST" enctype="multipart/form-data">

    <!-- 1. IDENTIFICAÇÃO E ESTRUTURA DA VAGA -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header border-secondary bg-transparent py-3">
        <h6 class="mb-0 text-primary fw-bold">
          <i class="fa-solid fa-sitemap me-2"></i>1. Identificação e Estrutura Orgânica
        </h6>
      </div>
      <div class="card-body">
        <div class="row g-3">

          <!-- seletor_motivação -->
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Motivação da Solicitação *</label>
            <!-- motivacao_id -->
            <?= seletor_motivacao($conn); ?>
          </div>

          <!-- Anexo da Autorização do Gestor -->
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Anexo: E-mail de Autorização do Gestor *</label>
            <input type="file" name="email_gestor_file" class="form-control" accept=".pdf,.msg,.eml,.jpg,.png" required>
            <span class="fs-7 text-white-50">Upload de cópia do e-mail do gestor autorizando a vaga.</span>
          </div>

          <!-- vaga sigilosa -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Vaga Sigilosa? *</label>
            <select name="sigilosa" class="form-select" required>
              <option value="NÃO">Não</option>
              <option value="SIM">Sim</option>
            </select>
          </div>

          <!-- forma de recrutamento -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Formato do Recrutamento *</label>
            <select name="formato" class="form-select" required>
              <option value="Externo">Externo (Exclusivamente mercado)</option>
              <option value="Misto">Misto (Inclui recrutamento interno)</option>
            </select>
          </div>

          <!-- seletor_subsedes -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Subsede *</label>
            <?= seletor_subsede($conn); ?>
          </div>

          <!-- seletor_polos -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Polo *</label>
            <div id='seletor_polos'>
              <select class="form-select" required>
                <option value="" class="text-warning">Selecione...</option>
              </select>
            </div>

          </div>

          <!-- seletor_organograma -->
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Orgão de lotação *</label>
            <?= seletor_organograma($conn); ?>
          </div>

          <!-- Salário -->
          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Salário *</label>
            <input type='text' id='salario' name='salario' class="form-control text-end" placeholder="R$ 0,00" required>
          </div>

          <!-- Tipo da Vaga: CLT, Estagiário, etc.-->
          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Tipo de Vaga *</label>
            <select name="tipo_vaga" id="tipo_vaga" class="form-select" required>
              <option value="Nenhum">Selecione...</option>
              <option value="CLT">CLT</option>
              <option value="Instrutor">Instrutor</option>
              <option value="Iniciativas Sociais">Iniciativas Sociais</option>
              <option value="Estágio">Estagiário</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Quantidade *</label>
            <input type="number" name="qtd" class="form-control text-center" placeholder="Qtd" value='1' min=1 required>
          </div>

          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Cargo *</label>
            <?= seletor_cargo($conn); ?>
          </div>

          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Horário de Trabalho *</label>
            <input type="text" name="horario_trabalho" class="form-control text-center" placeholder="Ex: 08:20 às 18:05 (Seg a Sex)" required>
          </div>

        </div>
      </div>
    </div>

    <!-- 3. PERFIL DO CANDIDATO E REQUISITOS -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header border-secondary bg-transparent py-3">
        <h6 class="mb-0 text-primary fw-bold">
          <i class="fa-solid fa-id-card me-2"></i>2. Perfil do Candidato e Requisitos
        </h6>
      </div>
      <div class="card-body">
        <div class="row g-3">

          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Preferência por Gênero</label>
            <select name="pref_genero" class="form-select">
              <option value="Não">Não</option>
              <option value="Masculino">Sim - Masculino</option>
              <option value="Feminino">Sim - Feminino</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Preferência por Faixa Etária</label>
            <select name="pref_faixa_etaria" class="form-select">
              <option value="Sem preferência">Não</option>
              <option value="Sim - entre 18 e 25">Sim - entre 18 e 25</option>
              <option value="Sim - entre 26 e 35">Sim - entre 26 e 35</option>
              <option value="Sim - entre 35 e 50">Sim - entre 35 e 50</option>
              <option value="Sim - mais de 50">Sim - mais de 50</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Regime de Trabalho *</label>
            <select name="regime_trabalho" id="regime_trabalho" class="form-select" required>
              <option value="100% Home Office">100% Home Office</option>
              <option value="100% Presencial" selected>100% Presencial</option>
              <option value="Híbrido">Híbrido</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">CNH (b) *</label>
            <select name="cnh" class="form-select" required>
              <option value="Não">Não</option>
              <option value="Sim">Sim</option>
              <option value="Sim e será um diferencial">Sim - Será um diferencial</option>
            </select>
          </div>

          <!-- BLOCO CONDICIONAL - ENDEREÇO DE TRABALHO -->
          <div class="col-md-12 d-none" id="bloco_endereco_trabalho">

            <div class="p-3 border border-secondary rounded bg-dark">

              <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-location-dot text-primary"></i>
                <span class="text-white fw-bold">Local de Trabalho</span>
                <small class="text-white-50">
                  Informe o endereço onde a atividade será realizada.
                </small>
              </div>

              <div class="row g-3">

                <!-- CEP -->
                <div class="col-md-3">
                  <label class="form-label text-white-50 small fw-bold">CEP *</label>
                  <input
                    type="text"
                    name="cep"
                    id="cep"
                    class="form-control"
                    placeholder="00000-000"
                    maxlength="9" onchange='busca_cep(this)'>
                </div>

                <!-- ENDEREÇO -->
                <div class="col-md-6">
                  <label class="form-label text-white-50 small fw-bold">Endereço *</label>
                  <input
                    type="text"
                    name="endereco"
                    id="endereco"
                    class="form-control"
                    placeholder="Rua, Avenida, Rodovia...">
                </div>

                <!-- NÚMERO -->
                <div class="col-md-3">
                  <label class="form-label text-white-50 small fw-bold">Número *</label>
                  <input
                    type="text"
                    name="numero"
                    id="numero"
                    class="form-control"
                    placeholder="Número">
                </div>

                <!-- BAIRRO -->
                <div class="col-md-4">
                  <label class="form-label text-white-50 small fw-bold">Bairro *</label>
                  <input
                    type="text"
                    name="bairro"
                    id="bairro"
                    class="form-control"
                    placeholder="Bairro">
                </div>

                <!-- COMPLEMENTO -->
                <div class="col-md-4">
                  <label class="form-label text-white-50 small fw-bold">Complemento</label>
                  <input
                    type="text"
                    name="complemento"
                    id="complemento"
                    class="form-control"
                    placeholder="Sala, bloco, andar...">
                </div>

                <!-- CIDADE -->
                <div class="col-md-3">
                  <label class="form-label text-white-50 small fw-bold">Cidade *</label>
                  <input
                    type="text"
                    name="cidade"
                    id="cidade"
                    class="form-control"
                    placeholder="Cidade">
                </div>

                <!-- UF -->
                <div class="col-md-1">
                  <label class="form-label text-white-50 small fw-bold">UF *</label>
                  <input
                    type="text"
                    name="uf"
                    id="uf"
                    class="form-control text-uppercase"
                    placeholder="UF"
                    maxlength="2">
                </div>

              </div>

            </div>

          </div>

          <!-- Formação Necessária -->
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Formação Necessária *</label>
            <select name="formacao_necessaria" id="formacao_necessaria" class="form-select" required>
              <option value="">Selecione...</option>
              <option value="Ensino Médio Completo">Ensino Médio Completo</option>
              <option value="Ensino Superior Cursando">Ensino Superior Cursando</option>
              <option value="Ensino Superior Completo">Ensino Superior Completo</option>
              <option value="Pós-Graduação em Andamento">Pós-Graduação em Andamento</option>
              <option value="Pós-Graduação Completa">Pós-Graduação Completa</option>
            </select>
          </div>

          <!-- BLOCO CONDICIONAL - Curso Superior/Pós -->
          <div class="col-md-6 d-none" id="box_curso_superior">
            <label class="form-label text-white-50 small fw-bold">Informe o Curso *</label>
            <input type="text" name="curso_superior_nome" class="form-control" placeholder="Nome do curso superior/pós">
          </div>

          <!-- BLOCO CONDICIONAL DE ESTÁGIO -->
          <div class="col-12 d-none" id="bloco_estagio">
            <div class="row g-3 p-3 border border-secondary rounded">
              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">Curso Estágio 1 *</label>
                <input type="text" name="estagio_curso_1" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white-50 small fw-bold">Curso Estágio 2</label>
                <input type="text" name="estagio_curso_2" class="form-control">
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label text-white-50 small fw-bold">Incluir Pergunta Chave? *</label>
            <select name="incluir_pergunta" id="incluir_pergunta" class="form-select" required>
              <option value="1">Não</option>
              <option value="2">Sim</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Aceita PCD ? *</label>
            <select name="pcd" id="pcd" class="form-select" required>
              <option value="0">Não</option>
              <option value="1">Sim</option>
            </select>
          </div>

          <div class="col-md-12 d-none" id="box_pergunta_chave">
            <label class="form-label text-white-50 small fw-bold">Digite a Pergunta Chave *</label>
            <input type="text" name="pergunta_chave_texto" class="form-control" placeholder="Pergunta eliminatória/classificatória para o candidato">
          </div>

          <div class="col-md-12">
            <label class="form-label text-white-50 small fw-bold">Experiência Profissional Desejada *</label>
            <textarea name="experiencia" id="experiencia" class="form-control" rows="2" placeholder="Descreva os anos de experiência ou conhecimentos prévios mínimos..." required></textarea>
          </div>

          <div class="col-md-12">
            <label class="form-label text-white-50 small fw-bold">Atividades da Vaga (Descreva pelo menos 5) *</label>
            <textarea name="atividades_vaga" id="atividades_vaga" class="form-control" rows="4" placeholder="1. &#10;2. &#10;3. &#10;4. &#10;5. " required></textarea>
          </div>

        </div>
      </div>
    </div>

    <!-- 3. COMPETÊNCIAS E EQUIPAMENTOS -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header border-secondary bg-transparent py-3">
        <h6 class="mb-0 text-primary fw-bold">
          <i class="fa-solid fa-list-check me-2"></i>3. Competências e Recursos Técnicos
        </h6>
      </div>
      <div class="card-body">
        <div class="row g-4">

          <!-- Competências Comportamentais (Até 3) -->
          <div class="col-md-6">
            <label class="form-label text-white fw-bold d-block">
              Competências Comportamentais Essenciais
              <span class="badge bg-primary ms-1">Selecione até 3</span>
            </label>
            <div class="p-3 border border-secondary rounded overflow-auto" style="max-height: 250px;">
              <?php listar_competencias_comportamentais($conn); ?>
            </div>
          </div>

          <!-- Competências Técnicas (Até 3) -->
          <div class="col-md-6">
            <label class="form-label text-white fw-bold d-block">
              Competências Técnicas Essenciais
              <span class="badge bg-primary ms-1">Selecione até 3</span>
            </label>
            <div class="p-3 border border-secondary rounded overflow-auto" style="max-height: 250px;">
              <?php listar_competencias_tecnicas($conn); ?>
            </div>
          </div>

          <!-- Equipamentos para TI -->
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Equipamentos a Solicitar ao TI</label>
            <div class="p-3 border border-secondary rounded">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="equipamentos[]" value="1" id="eq_1">
                <label class="form-check-label text-white-50 small" for="eq_1">Nenhum</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="equipamentos[]" value="2" id="eq_2">
                <label class="form-check-label text-white-50 small" for="eq_2">Notebook (c/ carregador)</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="equipamentos[]" value="3" id="eq_3">
                <label class="form-check-label text-white-50 small" for="eq_3">Smartphone Corporativo (Carregador, chip)</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="equipamentos[]" value="4" id="eq_4">
                <label class="form-check-label text-white-50 small" for="eq_4">Monitor para Computador</label>
              </div>
              <div class="mt-2">
                <input type="text" name="equipamentos_outro" class="form-control form-control-sm" placeholder="Outro equipamento...">
              </div>
            </div>
          </div>

          <!-- Indicação e Observações -->
          <div class="col-md-6">
            <div>
              <label class="form-label text-white-50 small fw-bold">Observações Adicionais</label>
              <textarea name="observacoes" id="observacoes" class="form-control" rows="3" placeholder="Informações relevantes para o analista de R&S..."></textarea>
            </div>
          </div>
          <div class='col-md-12'>
              <div class="mb-3">
                <label class="form-label text-warning small fw-bold">Indicação de Candidato (Upload de Currículo)</label>
                <input type="file" name="indicacao_cv_file" class="form-control" accept=".pdf,.doc,.docx">
              </div>
          </div>

        </div>
      </div>
    </div>

    <!-- BOTÕES DE AÇÃO -->
    <div class="d-flex justify-content-end gap-2 mb-5">
      <button type="reset" class="btn btn-outline-secondary">Limpar Formulário</button>
      <button type="button" class="btn btn-primary px-4 fw-bold" onclick='enviar_solicitacao()'>
        <i class="fa-solid fa-paper-plane me-2"></i>Enviar Solicitação
      </button>
    </div>

  </form>

</main>

<script src="js/vaga_new.js"></script>

<?php
include 'inc/footer.php';

function seletor_motivacao($conn)
{
  $sql = "SELECT * FROM rs_vagas_mot WHERE ativo = 1";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  echo "
    <select name='motivacao_id' class='form-select' required>
      <option value='0' class='text-warning'>Selecione...</option>";
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='{$row['id']}'>{$row['descricao']}</option>";
  }
  echo "</select>";
}

function seletor_subsede($conn)
{
  $sql = "SELECT subsede_id, identificador FROM rh_subsedes WHERE ativo = 1 ORDER BY identificador";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  echo "
    <select name='subsede_id' class='form-select' required onchange='selecionou_subsede(this)'>
      <option value='0' class='text-warning'>Selecione...</option>";
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='{$row['subsede_id']}'>{$row['identificador']}</option>";
  }
  echo "</select>";
}

function seletor_organograma($conn)
{
  $sql = "SELECT *
                FROM rh_organograma
                WHERE ativo = 1
                ORDER BY nivel_1, nivel_2, nivel_3, nivel_4, nivel_5, nivel_6 ";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $select = "<select class='form-select fs-13' id='idOrgao' name='idOrgao' required>";
  $select .= "<option value='0' selected class='text-warning'>Escolha</option>";

  while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $margem = $nivel * 3;
    $cor = $selected = "";
    if ($nivel == 3) $cor = "text-danger";
    if ($nivel == 4) $cor = "text-primary";
    if ($nivel == 7) $cor = "text-success";
    $select .= "<option value='$idOrgao' class='$cor'>" . str_repeat("&nbsp;", $margem) . "$idOrgao-$descricao</option>";
  }
  $select .= "</select>";
  echo $select;
}

function seletor_cargo($conn)
{
  $sql = "SELECT *
                FROM rh_cargos
                WHERE ativo = 1
                ORDER BY nome";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $select = "<select class='form-select fs-13 obrigatorio' id='idCargo' name='idCargo' required>";
  $select .= "<option value='0' selected>Selecione um cargo</option>";

  while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $select .= "<option value='$idCargo'>$nome</option>";
  }
  $select .= "</select>";
  echo $select;
}

function listar_competencias_tecnicas($conn)
{
  $sql = "SELECT id, descricao FROM rs_competencias_tecnicas WHERE ativo = 1 ORDER BY descricao";
  $stmt = $conn->prepare($sql);
  $stmt->execute();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "
        <div class='form-check mb-2'>
          <input class='form-check-input comp-tecnica-check' type='checkbox' name='comp_tecnicas[]' value='{$row['id']}' id='comp_t_{$row['id']}'>
          <label class='form-check-label text-white-50 small' for='comp_t_{$row['id']}'>" . htmlspecialchars($row['descricao']) . "</label>
        </div>";
  }
}

function listar_competencias_comportamentais($conn)
{
  $sql = "SELECT id, descricao FROM rs_competencias_comportamentais WHERE ativo = 1 ORDER BY descricao";
  $stmt = $conn->prepare($sql);
  $stmt->execute();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "
        <div class='form-check mb-2'>
          <input class='form-check-input comp-comportamental-check' type='checkbox' name='comp_comportamentais[]' value='{$row['id']}' id='comp_c_{$row['id']}'>
          <label class='form-check-label text-white-50 small' for='comp_c_{$row['id']}'>" . htmlspecialchars($row['descricao']) . "</label>
        </div>";
  }
}
