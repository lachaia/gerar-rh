<?php
// GERAR 2025 - SISTEMA DE RH
// index.php
// (C)haia, 24/01/2025.

session_start();

$idModulo = 1; // index.php

$base = $_SESSION['BD'];

if (! isset($_SESSION['SISTEMA'])) {
    header('Location: logout.php');
    exit();
} else {
    if ($_SESSION['SISTEMA'] != "RH" && $_SESSION['SISTEMA'] != "RHPSICO") {
        header('Location: logout.php');
        exit();
    }
}

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
    f_log("CON", "Visualiza Dashboard Gerar Principal ", "contratados", $idModulo, 0);
}

include "includes/conexao_gerar.php";
global $conn;

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Programa Principal" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-image: url('imagens/abstrato_tzuru_fundo.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        #dash {
            background-image: url('imagens/wall1.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .sb-sidenav {
            border-right: 1px solid rgba(0, 0, 0, 0.3);
            box-shadow: 2px 0 4px rgba(111, 111, 111, 0.4);
        }

        .cartao_dash {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s;
            min-height: 180px;
            /* altura mínima para todos */
        }

        .cartao_dash:hover {
            transform: translateY(-3px);
        }

        .display-7 {
            font-size: 1.4rem;
        }
    </style>

</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">

            <!-- Aqui COMEÇA o conteúdo da página -->

            <main class="text-light min-vh-100 p-4" style='background-color: rgba(0, 0, 0, 0.71);'>
                <div class="container-fluid">
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-8">
                            <h1 class="fw-bold text-white">
                                <i class="fa-solid fa-gauge-high me-2"></i> Dashboard de RH
                            </h1>
                            <p class="text-muted mb-0">Visão geral dos colaboradores e indicadores de gestão de pessoas</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-outline-light btn-sm">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Atualizar Dados
                            </button>
                        </div>
                    </div>

                    <!-- Cards principais -->
                    <div class="row g-4">
                        <div class="col-md-2 col-sm-6">
                            <div class="card bg-success bg-gradient text-white shadow-lg border-0 rounded-3">
                                <div class="card-body text-center">
                                    <i class="fa-solid fa-users fa-2x mb-2"></i>
                                    <h6 class="fw-bold">Ativos</h6>
                                    <div id="divQtdAtivos" class="fs-4">carregando...</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="card bg-secondary bg-gradient text-white shadow-lg border-0 rounded-3">
                                <div class="card-body text-center">
                                    <i class="fa-solid fa-user-slash fa-2x mb-2"></i>
                                    <h6 class="fw-bold">Desligados</h6>
                                    <div id="divQtdDesligados" class="fs-4">carregando...</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="card bg-warning bg-gradient text-dark shadow-lg border-0 rounded-3">
                                <div class="card-body text-center">
                                    <i class="fa-solid fa-file-lines fa-2x mb-2"></i>
                                    <h6 class="fw-bold">Currículos</h6>
                                    <div id="divQtdCurriculos" class="fs-4">carregando...</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="card bg-danger bg-gradient text-white shadow-lg border-0 rounded-3">
                                <div class="card-body text-center">
                                    <i class="fa-solid fa-user-injured fa-2x mb-2"></i>
                                    <h6 class="fw-bold">Afastados</h6>
                                    <div id="divQtdAfastados" class="fs-4">carregando...</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="card bg-primary bg-gradient text-white shadow-lg border-0 rounded-3">
                                <div class="card-body text-center">
                                    <i class="fa-solid fa-umbrella-beach fa-2x mb-2"></i>
                                    <h6 class="fw-bold">Em Férias</h6>
                                    <div id="divQtdFerias" class="fs-4">carregando...</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="card bg-info bg-gradient text-dark shadow-lg border-0 rounded-3">
                                <div class="card-body text-center">
                                    <i class="fa-solid fa-arrows-rotate fa-2x mb-2"></i>
                                    <h6 class="fw-bold">Turnover (%)</h6>
                                    <div id="divTurnover" class="fs-4">carregando...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cards secundários -->
                    <div class="row g-4 mt-3">
                        <div class="col-md-4">
                            <div class="card bg-dark text-light border border-secondary shadow-sm cartao_dash">
                                <div class="card-header border-secondary">
                                    <i class="fa-solid fa-venus-mars me-2"></i> Por gênero (ativos)
                                </div>
                                <div class="card-body text-center" id="divGenero">carregando...</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-dark text-light border border-secondary shadow-sm cartao_dash">
                                <div class="card-header border-secondary">
                                    <i class="fa-solid fa-chart-line me-2"></i> Média de Idade (ativos) em anos
                                </div>
                                <div class="card-body text-center" id="divMediaIdade">carregando...</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-dark text-light border border-secondary shadow-sm cartao_dash">
                                <div class="card-header border-secondary">
                                    <i class="fa-solid fa-chart-line  me-2"></i> Idade de Casa (ativos) em anos
                                </div>
                                <div class="card-body text-center" id="divIdadeCasa">carregando...</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-dark text-light border border-secondary shadow-sm cartao_dash">
                                <div class="card-header border-secondary">
                                    <i class="fa-solid fa-chart-line  me-2"></i> Salário Médio (ativos) em R$
                                </div>
                                <div class="card-body text-center" id="divSalMedio">carregando...</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-dark text-light border border-secondary shadow-sm cartao_dash">
                                <div class="card-header border-secondary">
                                    <i class="fa-solid fa-chart-line  me-2"></i> Agrupado por Etnia (ativos)
                                </div>
                                <div class="card-body text-center" id="divEtnias">carregando...</div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>

            <!-- 
                    Aqui TERMINA o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>

    <script data-cfasync="false" src="js/scripts.js"></script>
    <script>
        $(document).ready(function() {
            // Carregar dados via AJAX
            $.ajax({
                url: 'index_aj.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#divQtdAtivos').text(data.qtdAtivos);
                    $('#divQtdDesligados').text(data.qtdDesligados);
                    $('#divQtdCurriculos').text(data.qtdCurriculos);
                    $('#divQtdAfastados').text(data.qtdAfastados);
                    $('#divQtdFerias').text(data.qtdFerias);
                    $('#divGenero').html(data.divGenero);
                    $('#divTurnover').html(data.turnover_percentual);
                    $('#divMediaIdade').html(data.divMediaIdade);
                    $('#divIdadeCasa').html(data.divIdadeCasa);
                    $('#divSalMedio').html(data.divSalMedio);
                    $('#divEtnias').html(data.divEtnias);
                },
                error: function() {
                    alert('Erro ao carregar os dados.');
                }
            });
        });
    </script>
</body>

</html>