<?php
//
//- competencias_tecnicas.php | R&S | Cadastro de Competências Técnicas
//- (C)haia, 24/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
  exit();
}

//- Inclui o arquivo de conexão com o banco de dados
include "../app/includes/conexao_gerar.php";

$titulo_pagina = "COMPETÊNCIAS TÉCNICAS"; // Define o Título aqui
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
</style>

<main class="container py-4">

  <!-- BARRA SUPERIOR E CABEÇALHO DA PÁGINA -->
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
        <i class="fa-solid fa-screwdriver-wrench fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold">Competências Técnicas</h4>
        <p class="text-white-50 small mb-0">Cadastro das competências técnicas disponíveis na solicitação de vaga</p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" onclick="nova_competencia()">
        <i class="fa-solid fa-plus"></i> Nova Competência
      </button>
      <a href="index.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel R&S
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-1">
      <div class="table-responsive mt-1">
        <table class="table table-dark table-hover mt-2 border-0 w-100" id="grid_competencias">
          <thead class="table-dark text-uppercase fs-7 border-secondary" style="border-bottom: 2px solid #495057;">
            <tr>
              <th scope="col" class="ps-3 text-white-50">Descrição</th>
              <th scope="col" class="text-center text-white-50">Status</th>
              <th scope="col" class="text-end pe-3 text-white-50">Ações</th>
            </tr>
          </thead>
          <tbody class="border-secondary"></tbody>
        </table>
      </div>
    </div>
  </div>

</main>

<!-- MODAL INCLUIR/EDITAR COMPETÊNCIA -->
<div class="modal fade" id="modalCompetencia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title" id="modalCompetenciaTitulo">Nova Competência</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaCompetencia"></div>
        <form id="formCompetencia">
          <input type="hidden" name="id" id="competencia_id">
          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">Descrição *</label>
            <input type="text" name="descricao" id="competencia_descricao" class="form-control" maxlength="150" placeholder="Ex: Uso de sistemas e ferramentas digitais" required>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="ativo" id="competencia_ativo" value="1" checked>
            <label class="form-check-label text-white-50" for="competencia_ativo">Ativo</label>
          </div>
        </form>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="salvar_competencia()">
          <i class="fa-solid fa-floppy-disk me-2"></i>Salvar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="js/competencias_tecnicas.js"></script>

<?php
include 'inc/footer.php';
