<?php
//
// afastamentos.php | Módulo de Colaboradores Afastados para o Portal do GESTOR
// (C)haia, 21/10/2025

session_start();

$idModulo = 16; // Portal do Gestor

$modulo = "Afastamentos";
include 'includes/header.php';

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include "../includes/conexao_gerar.php";

$idGestor = $_SESSION['idColab'];

?>
<link rel="stylesheet" href="css/afastamentos.css">
<main class="main">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="fa-solid fa-file-medical"></i>
                Colaboradores em Afastamento
            </h2>
            <small style="color:var(--muted)">Relação dos colaboradores afastados para tratamento de saúde</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Férias (skeleton) -->
    <div class="big-card">
        <div class="row mt-2">
            <div class="col-md-12">
                    <table class="table table-dark table-striped table-hover table-bordered align-middle w-100" id='tblAfastados'>
                        <thead class="table-dark text-center">
                            <tr>
                                <th>ID</th>
                                <th>Colaborador</th>
                                <th>Tipo</th>
                                <th>Início</th>
                                <th>Retorno</th>
                                <th>Dias</th>
                                <th>Status</th>
                                <th style='width: 110px'>Ações</th>
                            </tr>
                        </thead>
                        <tbody id='corpoTabela'>
                            <!-- Conteúdo será carregado via AJAX -->
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
</main>

<!-- The Modal VISUALIZAR AFASTAMENTO -->
<div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarAfastamentoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: gainsboro">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">
                    <h5><i class="fa-solid fa-eye"></i> Visualizar Registro do Afastamento</h5>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-sm-12">
                        <label class="col-sm-3 col-form-label">Colaborador</label>
                        <p id="v_nome" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                    <div class="col-sm-4">
                        <label for="v_data" class="col-form-label">Data</label>
                        <p id="v_data" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                    <div class="col-sm-4">
                        <label for="v_qtd" class="col-form-label">Qtd Dias</label>
                        <p id="v_qtd" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                    <div class="col-sm-4">
                        <label for="v_retorno" class="col-form-label">Retorno</label>
                        <p id="v_retorno" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                </div>


                <div class="row mb-3">
                    <div class="col-sm-12">
                        <label class="col-form-label">Tipo de Afastamento</label>
                        <p id="v_tipo" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                    <div class="col-sm-8">
                        <label class="col-form-label">Emitido por:</label>
                        <p id="v_emitido_por" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-form-label">CID</label>
                        <p id="v_cid" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-12">
                        <label class="col-form-label">Arquivo enviado</label>
                        <p id="v_arquivo" class="form-control-plaintext visCampo text-center"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" data-bs-dismiss="modal">
                            <i class="fa fa-close"></i> Fechar
                        </button>
                    </div>
                    <span id='v_quando' class="mt-2 ms-2" style='font-size: 12px; font-weight: 200'></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/afastamentos.js"></script>
<?php

include 'includes/footer.php';
