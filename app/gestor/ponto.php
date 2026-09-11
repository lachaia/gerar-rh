<?php
//
// ponto.php | Módulo de Ponto Eletrônico do Portal do GESTOR
// (C)haia, 13/11/2025

session_start();

$idModulo = 16; // Portal do Gestor

$modulo = "Ponto";
include 'includes/header.php';

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include "../includes/conexao_gerar.php";

$idGestor = $_SESSION['idColab'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar</title>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Bootstrap 5 JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/ponto.css">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
<main class="main">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="fa-solid fa-clock"></i>
                Ponto dos Colaboradores
            </h2>
            <small style="color:var(--muted)">Relação das Solicitações de Ajuste de Ponto dos colaboradores</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Férias (skeleton) -->
    <div class="big-card">

        <div class="row">

            <div class="col-md-12 mx-auto">
                <div class="form-check form-switch">
                    <input 
                        class="form-check-input" 
                        type="checkbox" id="chk_pendentes" name="chk_pendentes" value="1" 
                        checked onchange="selecionou(this)">
                    <label class="form-check-label" for="mySwitch">Mostrar só os pendentes</label>
                    <input type="hidden" id='supervisor_id' name='supervisor_id' value='<?=  $idGestor ?>'>
                </div>
                <table
                    class="table table-dark table-striped table-hover table-bordered align-middle w-100"
                    id='tabela'>
                    <thead class="table-dark text-center">
                        <tr>
                            <th><sup>0</sup>ID</th>
                            <th><sup>1</sup>Colaborador</th>
                            <th><sup>2</sup>Solicitado em</th>
                            <th><sup>3</sup>Tipo Ajuste</th>
                            <th><sup>4</sup>Data Ajuste</th>
                            <th><sup>5</sup>Aprovação</th>
                            <th><sup>6</sup>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id='corpoTabela'>
                        <!-- Conteúdo será carregado via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal decisão sobre o ajuste -->
    <div class="modal fade" id="modalDecide" tabindex="-1" aria-labelledby="modalDecideLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-light">
                    <h5 class="modal-title" id="modalDecideLabel">Análise da Solicitação</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="text-center text-dark">
                            Sr(a) Gestor(a),<br> em <i id='dataSolicitacao'></i><br>o colaborador <i id='nmColaborador'></i><br>solicitou o seguinte ajuste de ponto:
                        </div>
                        <div class="text-center text-dark mt-4">
                            <strong>Pontos do dia:</strong>
                        </div>
                        <div id='batidas' class="text-center text-dark"></div>
                        <div class="text-center text-dark mt-4">
                            <b>Tipo de Ajuste:</b> <i id='ajuste'></i>
                        </div>
                        <div class="text-center text-dark mt-2">
                            <b>Para Data/Hora:</b> <i id='data_hora'></i>
                        </div>
                        <div class="text-center text-dark mt-2">
                            <b>Justificativa:</b> <i id='justificativa'></i>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="text-dark">
                            <label for="observacao" class="text-dark">Observações (opcional)</label>
                            <textarea
                                class="form-control text-dark"
                                name="observacao"
                                id="observacao"
                                placeholder="Observações do gestor"
                                rows="3"
                                style="background-color: #f9f9f9;"></textarea>
                        </div>
                    </div>
                    <input type="hidden" id='solicitacao_id'>
                </div>
                <div class="modal-footer" id='botoes_decisao'>
                    <button class="btn btn-outline-secondary m-2" data-bs-dismiss="modal">Fechar</button>
                    <button class="btn btn-outline-danger m-2" onclick="f_salvar('Rejeitar')">Rejeitar</button>
                    <button class="btn btn-outline-success m-2" onclick="f_salvar('Aceitar')">Aceitar</button>
                </div>
                <div id='mensagem' class="h5 text-center text-dark"></div>
            </div>
        </div>
    </div>

</main>
<script src="js/ponto.js"></script>
<?php

include 'includes/footer.php';
