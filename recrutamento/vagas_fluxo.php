<?php
//
//- vagas_fluxo.php | R&S | Cadastro das Etapas do Fluxo de Recrutamento
//- (C)haia, 26/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
  exit();
}

//- Inclui o arquivo de conexão com o banco de dados
include "../app/includes/conexao_gerar.php";

$titulo_pagina = "ETAPAS DO FLUXO"; // Define o Título aqui
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

  .badge-preview {
    display: inline-flex;
    padding: .35em .65em;
    font-size: .75em;
    font-weight: 700;
    border-radius: .375rem;
  }

  .form-control-color {
    width: 100%;
    height: 38px;
  }
</style>

<main class="container py-4">

  <!-- BARRA SUPERIOR E CABEÇALHO DA PÁGINA -->
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-info bg-opacity-10 text-info border border-info border-opacity-25">
        <i class="fa-brands fa-trello fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold">Etapas do Fluxo de Recrutamento</h4>
        <p class="text-white-50 small mb-0">Colunas do board de fluxo.php — pipeline pós-aprovação da vaga</p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" onclick="novo_fluxo()">
        <i class="fa-solid fa-plus"></i> Nova Etapa
      </button>
      <a href="index.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel R&S
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-1">
      <div class="table-responsive mt-1">
        <table class="table table-dark table-hover mt-2 border-0 w-100" id="grid_fluxo">
          <thead class="table-dark text-uppercase fs-7 border-secondary" style="border-bottom: 2px solid #495057;">
            <tr>
              <th scope="col" class="ps-3 text-white-50">Etapa</th>
              <th scope="col" class="text-white-50">Descrição</th>
              <th scope="col" class="text-center text-white-50">Situação</th>
              <th scope="col" class="text-end pe-3 text-white-50">Ações</th>
            </tr>
          </thead>
          <tbody class="border-secondary"></tbody>
        </table>
      </div>
    </div>
  </div>

</main>

<!-- MODAL INCLUIR/EDITAR ETAPA -->
<div class="modal fade" id="modalFluxo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title" id="modalFluxoTitulo">Nova Etapa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaFluxo"></div>
        <form id="formFluxo">
          <input type="hidden" name="id" id="fluxo_id">

          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">Etapa *</label>
            <input type="text" name="status" id="fluxo_status" class="form-control text-uppercase" maxlength="45" placeholder="Ex: TESTES TÉCNICOS" required>
          </div>

          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">Descrição *</label>
            <textarea name="descricao" id="fluxo_descricao" class="form-control" rows="2" maxlength="255" placeholder="Explique o que essa etapa representa no fluxo" required></textarea>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">Cor do Texto *</label>
              <select name="cor_frente" id="fluxo_cor_frente" class="form-select" required>
                <option value="text-light">Claro (fundo escuro)</option>
                <option value="text-dark">Escuro (fundo claro)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">Cor de Fundo *</label>
              <input type="color" name="cor_fundo" id="fluxo_cor_fundo" class="form-control form-control-color" value="#6c757d" required>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label text-white-50 small fw-bold d-block">Prévia</label>
            <span id="fluxo_preview" class="badge-preview text-light" style="background-color:#6c757d;">ETAPA</span>
          </div>

          <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" name="ativo" id="fluxo_ativo" value="1" checked>
            <label class="form-check-label text-white-50" for="fluxo_ativo">Ativo</label>
          </div>
        </form>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="salvar_fluxo()">
          <i class="fa-solid fa-floppy-disk me-2"></i>Salvar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="js/vagas_fluxo.js"></script>

<?php
include 'inc/footer.php';
