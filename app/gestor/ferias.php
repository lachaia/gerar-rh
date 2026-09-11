<?php
//
// colaboradores.php | Módulo de Colaboradores do Portal do GESTOR
// (C)haia, 20/10/2025

session_start();

$idModulo = 16; // Portal do Gestor

$modulo = "Colaboradores";
include 'includes/header.php';

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include "../includes/conexao_gerar.php";

$idGestor = $_SESSION['idColab'];

?>
<link rel="stylesheet" href="css/ferias.css">
<main class="main">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="fa-solid fa-plane"></i>
                Férias dos Colaboradores
            </h2>
            <small style="color:var(--muted)">Relação dos períodos aquisisitivos/concessivos dos colaboradores</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Férias (skeleton) -->
    <div class="big-card">

        <div class="row m-4">

            <div class="col-md-12 mx-auto">
                    <table class="table table-dark table-striped table-hover table-bordered align-middle w-100 nowrap" id='tblFerias'>
                        <thead class="table-dark text-center">
                            <tr>
                                <th><sup>0</sup>ID</th>
                                <th><sup>1</sup>Colaborador</th>
                                <th><sup>2</sup>Aquisitivo</th>
                                <th><sup>3</sup>Concessivo</th>
                                <th><sup>4</sup>Dias</th>
                                <th><sup>5</sup>STATUS</th>
                                <th><sup>6</sup>Agenda:1</th>
                                <th><sup>7</sup>Qtd:1</th>
                                <th><sup>8</sup>Agenda:2</th>
                                <th><sup>9</sup>Qtd:2</th>
                                <th><sup>10</sup>Agenda:3</th>
                                <th><sup>11</sup>Qtd:3</th>
                                <th><sup>12</sup>Saldo</th>
                            </tr>
                        </thead>
                        <tbody id='corpoTabela'>
                            <!-- Conteúdo será carregado via AJAX -->
                        </tbody>
                    </table>
            </div>
        </div>

    </div>

    <!-- Modal Aprovar Férias -->
    <div class="modal fade" id="modalFerias" tabindex="-1" aria-labelledby="modalFeriasLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-light">
                    <h5 class="modal-title" id="modalFeriasLabel">Aprovação de Férias</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Aqui entra o conteúdo carregado via AJAX -->
                    <div id="conteudoFerias" class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Carregando...
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary m-2" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-outline-primary m-2" onclick="f_aprovar_selecionadas()">Aprovar Selecionadas</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Aprovação -->
    <div class="modal fade" id="modalAprovar" tabindex="-1" aria-hidden="false">
        <input type="hidden" id='vetorParcelas' value="<?= $vetor ?>">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAprovarLabel">Aprovar Férias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <label for="senhaAprovacao" class="form-label">Digite sua senha para aprovar:</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="senhaAprovacao" placeholder="Senha" />
                        <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                            <span id="toggleSenhaIcon"><i class="fa-solid fa-eye"></i></span>
                        </button>
                    </div>
                    <input type="hidden" id="idColabSupervisorConfirm" />

                    <!-- Seção para motivo da não aprovação -->
                    <div id="motivoNaoAprovacaoSection" class="d-none mt-3">
                        <label for="motivoNaoAprovacao" class="form-label">Motivo da não aprovação das parcelas desmarcadas:</label>
                        <textarea id="motivoNaoAprovacao" class="form-control" rows="3" placeholder="Digite o motivo aqui..."></textarea>
                    </div>
                </div>

                <div class="modal-footer d-flex w-100 p-0">
                    <button type="button" class="btn btn-outline-secondary flex-fill m-1" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-success flex-fill m-1" onclick="confirmarAprovacao()">Confirmar</button>
                </div>

                <div id='msgAprova' class="h5 text-center mt-2"></div>
            </div>
        </div>
    </div>

</main>
<script src="js/ferias.js"></script>
<?php

include 'includes/footer.php';
