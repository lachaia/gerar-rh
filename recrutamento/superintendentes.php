<?php
//
//- superintendentes.php | R&S | Cadastro de Superintendentes Aprovadores de Vaga
//- (C)haia, 24/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
  exit();
}

//- Inclui o arquivo de conexão com o banco de dados
include "../app/includes/conexao_gerar.php";

$titulo_pagina = "SUPERINTENDENTES"; // Define o Título aqui
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
        <i class="fa-solid fa-user-tie fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold">Superintendentes</h4>
        <p class="text-white-50 small mb-0">Cadastro dos aprovadores de abertura de vaga</p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" onclick="novo_superintendente()">
        <i class="fa-solid fa-plus"></i> Novo Superintendente
      </button>
      <a href="index.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel R&S
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-1">
      <div class="table-responsive mt-1">
        <table class="table table-dark table-hover mt-2 border-0 w-100" id="grid_superintendentes">
          <thead class="table-dark text-uppercase fs-7 border-secondary" style="border-bottom: 2px solid #495057;">
            <tr>
              <th scope="col" class="ps-3 text-white-50">Superintendente</th>
              <th scope="col" class="text-white-50">E-mail</th>
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

<!-- MODAL INCLUIR/EDITAR SUPERINTENDENTE -->
<div class="modal fade" id="modalSuperintendente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title" id="modalSuperintendenteTitulo">Novo Superintendente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="msgAlertaSuperintendente"></div>
        <form id="formSuperintendente">
          <input type="hidden" name="id" id="superintendente_id">

          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">Nome *</label>
            <input type="text" name="identificador" id="superintendente_identificador" class="form-control" maxlength="45" placeholder="Nome do superintendente" required>
          </div>

          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">E-mail *</label>
            <input type="email" name="email" id="superintendente_email" class="form-control" maxlength="100" placeholder="nome@gerar.org.br" required>
          </div>

          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="ativo" id="superintendente_ativo" value="1" checked>
            <label class="form-check-label text-white-50" for="superintendente_ativo">Ativo</label>
          </div>
        </form>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="salvar_superintendente()">
          <i class="fa-solid fa-floppy-disk me-2"></i>Salvar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="js/superintendentes.js"></script>

<?php
include 'inc/footer.php';
