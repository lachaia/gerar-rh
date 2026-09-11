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
<link rel="stylesheet" href="css/equipe.css">
<main class="main">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="fa-solid fa-users"></i>
                Colaboradores do Gestor
            </h2>
            <small style="color:var(--muted)">Relação dos Colaboradores ativos no Órgão gerenciado</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Férias (skeleton) -->
    <div class="big-card">

        <div class="container align-items-center justify-content-center text-white mt-4">
            <div class="d-flex justify-content-center position-relative">
                <h3 class="text-center w-100">
                    Minha Equipe
                </h3>
            </div>
            <!-- Aqui COMEÇA o conteúdo da página -->
            <div class="row mt-2">
                <div class="col-md-12">
                        <table class="table table-dark table-bordered table-striped align-middle text-nowrap tabela-transparente w-100" id='tblEquipe'>
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Colaborador</th>
                                    <th>E-mail</th>
                                    <th>Setor</th>
                                    <th>Cargo</th>
                                    <th>Data Admissão</th>
                                    <th>Tipo Contrato</th>
                                    <th>Situação</th>
                                    <th><i class="fa-solid fa-magnifying-glass"></i></th>
                                </tr>
                            </thead>
                            <tbody id='corpoTabela'>
                                <!-- Conteúdo será carregado via AJAX -->
                            </tbody>
                        </table>
                </div>
            </div>

        </div>
    </div>

</main>
<script src="js/equipe.js"></script>
<?php

include 'includes/footer.php';
