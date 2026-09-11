<?php
//- g_phpinfo.php - Módulo para mostrar a configuração da plataforma de instalação
//- (C) Chaia, 01/08/2023
//

$idModulo = 12;

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
}

include_once __DIR__ . "/includes/conexao_gerar.php";
include_once __DIR__ . "/includes/f_logs.php";
f_log("CON", "Consulta Configurações", "*", $idModulo, 0);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Módulo de Reajuste Salarial" />
    <meta name="author" content="LAChaia" />
    <title>Gerar</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />

    <!-- Inclua os arquivos do JSQuery -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>

        <!-- jQuery UI (CSS e JS) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        footer {
            background-color: #8b98a5ff;
        }

        /* Estilo customizado para o card (opcional, para uma aparência mais refinada) */
        .elegant-card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, .05);
            border: 1px solid rgba(0, 0, 0, .125);
            border-radius: .5rem;
            background-color: #aeb6c0ff;
        }

        /* Garantindo que os botões do footer tenham a mesma largura */
        .card-footer .btn {
            width: 48%;
            /* Ajuste a porcentagem se houver margem entre eles */
        }
    </style>
</head>

<body class="sb-nav-fixed" style="background-color: #494d52ff; border-left: 2px solid #000;">

    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <main class="container mt-2">
                <!-- Aqui COMEÇA o conteúdo da página -->


                <div class="container my-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">

                            <div class="card elegant-card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Reajuste Salarial</h5>
                                </div>

                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-md-6 border-end pe-4">

                                            <div class="mb-3">
                                                <label for="percentualReajuste" class="form-label">Percentual do Reajuste (%)</label>
                                                <input type="number" class="form-control text-center" id="percentualReajuste" value="0.00"
                                                    step="0.01" min="0" style="font-weight: bold; font-size: 24px;">
                                            </div>

                                            <div class="d-grid mb-3">
                                                <button class="btn btn-outline-success" type="button" id="incluirTodosBtn">
                                                    <i class="bi bi-people-fill me-2"></i>Incluir Todos os Colaboradores
                                                </button>
                                            </div>

                                            <div class="mb-3">
                                                <label for="selectOrgao" class="form-label">Órgão a ser Reajustado</label>
                                                <div class="input-group">
                                                    <select class="form-select" id="selectOrgao">
                                                        <span id='opcaoSelectOrgao'><?= opcaoSelectOrgao() ?></span>
                                                    </select>
                                                    <button class="btn btn-primary" type="button" id="incluirOrgaoBtn">Incluir</button>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="selectOrgao" class="form-label">Órgão a ser Reajustado com suborgãos</label>
                                                <div class="input-group">
                                                    <select class="form-select" id="selectOrgaoSub">
                                                        <span id='opcaoSelectOrgao'><?= opcaoSelectOrgao() ?></span>
                                                    </select>
                                                    <button class="btn btn-primary" type="button" id="incluirOrgaoBtnSub">Incluir</button>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="nomeColaborador" class="form-label">Incluir Colaborador por Nome</label>
                                                <input type="text" class="form-control" id="nomeColaborador" name='nomeColaborador' placeholder="Digite o nome do colaborador">
                                            </div>

                                            <div class="mb-3">
                                                <label for="motivoReajuste" class="form-label">Motivo do Reajuste</label>
                                                <textarea class="form-control" id="motivoReajuste" rows="3"></textarea>
                                            </div>

                                        </div>

                                        <div class="col-md-6 ps-4">
                                            <input type="hidden" id='listaID'>
                                            <label class="form-label">Colaboradores a Serem Reajustados</label>
                                            <div id="listaColaboradores" class="border p-2" style="min-height: 400px; max-height: 400px; overflow-y: auto;">
                                                <p class="text-muted text-center" id="msgVazio">Nenhum colaborador incluído.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-danger m-1" onClick='goBack()'>Cancelar</button>
                                    <button type="button" class="btn btn-outline-secondary m-1" onClick='ressetReajuste()'>Limpar tudo</button>
                                    <button type="button" class="btn btn-primary m-1" id="enviarBtn" onClick='enviarReajuste()'>Enviar Reajuste</button>
                                </div>
                                <div id='alertaReajuste' class="h5 text-center mt-2 text-info"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Aqui Termina o conteúdo da página -->
            </main>
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_reajuste.js"></script>
    <style>
        .sb-sidenav {
            border-right: 1px solid rgba(0, 0, 0, 0.3);
            box-shadow: 2px 0 4px rgba(111, 111, 111, 0.4);
        }
    </style>
</body>
</html>
<?PHP 
//opcaoSelectOrgao

function opcaoSelectOrgao(){
    global $conn;
    $sql = "SELECT idOrgao, descricao 
            FROM RH.rh_organograma 
            WHERE ativo = 1
            ORDER BY nivel_1, nivel_2, nivel_3, nivel_4, nivel_5, nivel_6, nivel_7";
    $result = $conn->query($sql);
    echo "<option value='0'>Selecione o Órgão...</option>";
    if ($result->rowCount() > 0) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . $row['idOrgao'] . "'>" . $row['descricao'] . "</option>";
        }
    }
}