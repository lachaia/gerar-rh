<?php
//
//- rh_cargos.php | CARGOS do RH
// (C)haia, 20/03/2025

use phpseclib3\Math\BigInteger\Engines\PHP;

session_start();

$idModulo = 5; // Cargos

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tabela de ti_logins no Sistema" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_avaliacoes.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- JS: jQuery sempre antes do jQuery UI -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <!-- 
                    Aqui COMEÇA o conteúdo da página 
                -->
            <main>
                <div class="container-fluid px-4">
                    <div class="card mt-2 bg-dark text-white">
                        <h3 class="m-4">
                            <i class="fa-solid fa-person-circle-check"></i> AVALIAÇÕES DE DESEMPENHO
                        </h3>
                    </div>
                    <div id='divAlertaCargo' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Avaliações
                            </div>

                            <div class="float-end d-flex">
                                <input type="date" class="form-control form-control-sm m-1" id="data_ini" value='<?= date("Y-01-01") ?>'>
                                <div class="input-group input-group-sm m-1">
                                    <input type="date" class="form-control" id="data_fim" value='<?= date("Y-m-d") ?>'>
                                    <button class="btn btn-outline-secondary" type="button" onclick="selecionou()">
                                        <i class="fa-solid fa-repeat"></i>
                                    </button>
                                </div>
                                <a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success m-1'>Incluir</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-8">
                                    <table id="tabela" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                        <thead class="gb-gray">
                                            <tr>
                                                <th><sup>0</sup>ID</th>
                                                <th><sup>1</sup>Data/Status</th>
                                                <th><sup>2</sup>Tipo</th>
                                                <th><sup>3</sup>Avaliador</th>
                                                <th><sup>4</sup>Colaborador</th>
                                                <th><sup>5</sup>Score</th>
                                                <th style='width: 110px'><sup></sup>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-4">
                                    <table id="tabela_media" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                        <thead class="gb-gray">
                                            <tr>
                                                <th><sup>0</sup>Colab ID</th>
                                                <th><sup>1</sup>Colaborador</th>
                                                <th><sup>2</sup>Média</th>
                                                <th style='width: 110px'><sup></sup>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- The Modal Gerar Avaliações -->
            <div class="modal fade" id="modalGerarAvaliacoes" tabindex="-1" aria-labelledby="modalGerarLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa-regular fa-comments"></i> Gerar Avaliações para os Colaboradores
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <form id="formGerarAvaliacoes">
                                <div class="mb-3">
                                    <label for="_nome" class="form-label">Tipo de Avaliação</label>
                                    <?= seletor_tipo_avaliacao() ?>
                                </div>
                                <div class="mb-3">
                                    <label for="_nome" class="form-label">Colaborador</label>
                                    <input type="text" class="form-control" id="_nome" name="_nome" placeholder="Digite o nome do colaborador...">
                                </div>

                                <!-- Container onde aparecerão os colaboradores selecionados -->
                                <div id="listaColabs" class="p-2 border rounded bg-white" style="min-height:200px; max-height:400px; overflow-y:auto;">
                                    <small class="text-muted">Nenhum colaborador adicionado...</small>
                                </div>
                            </form>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-dark" onclick='incluir_todos()'>Inserir todos Colaboradores</button>
                            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="buttom" class="btn btn-outline-primary" onclick='gerar_avaliacoes()'>Gerar Avaliações</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- The Modal VISUALIZAR CARGO -->
            <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarCargoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-eye"></i> Visualizar Cargo</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-sm-10">
                                    <label class="col-sm-3 col-form-label">Nome do Cargo</label>
                                    <p id="v_nome" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2">
                                    <label class="col-form-label">Nível</label>
                                    <p id="v_nivel" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Descrição</label>
                                    <p id="v_descricao" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                        <i class="fa fa-close"></i> Fechar
                                    </button>
                                </div>
                                <span id='divQuando' class="mt-2 ms-2" style='font-size: 12px; font-weight: 200'></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 
                    Aqui Termina o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_avaliacoes.js"></script>
</body>

</html>
<?PHP

include "includes/conexao_gerar.php";

function seletor_tipo_avaliacao()
{
    global $conn;
    $sql = "SELECT * FROM rh_avaliacao_tipos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $html = "<select id='idTipo' name='idTipo' class='form-select'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $html .= "<option value='$id'>$descricao</option>";
    }
    $html .= "</select>";
    return $html;
}
