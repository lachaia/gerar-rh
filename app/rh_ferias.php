<?php
//
//- rh_ferias.php | CONTROLE DAS FÉRIAS 
// (C)haia, 29/04/2025

session_start();

$idModulo = 12; // férias

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
}

$nmLogin = $_SESSION['nmLogin'];

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
    <link href="css/rh_ferias.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <style>
        .status {
            display: inline-block;
            width: 64px;
            height: 22px;
            text-align: center;
            font-size: 12px;
        }

        .legenda{
            display: inline-block;
            width: 180px;
            height: 22px;
            text-align: center;
            font-family: 'Calibri';
            font-size: 13px !important
        }

        .tabela-branca th,
        .tabela-branca td,
        .tabela-branca {
            border: 1px solid #fff !important;
        }
    </style>
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
                        <h3 class="m-3">
                            <i class="fa-regular fa-calendar-check"></i> FÉRIAS
                        </h3>
                    </div>
                    <div id='divAlerta' class="text-center"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Controle dos Períodos Aquisitivos
                            </div>
                            <!--<div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a></div> -->
                        </div>
                        <div class="card-body">
                            <table id="example" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>ID</th>
                                        <th><sup>1</sup>Colaborador</th>
                                        <th><sup>2</sup>Período Aquisitivo</th>
                                        <th><sup>3</sup>Período Concessão</th>
                                        <th><sup>4</sup>Dias</th>

                                        <th><sup>5</sup>STATUS</th>

                                        <th><sup>6</sup>Agenda:1</th>
                                        <th><sup>7</sup>Qtd:1</th>
                                        <th><sup>8</sup>Agenda:2</th>
                                        <th><sup>9</sup>Qtd:2</th>
                                        <th><sup>10</sup>Agenda:3</th>
                                        <th><sup>11</sup>Qtd:3</th>

                                        <th><sup>12</sup>Fruída:1</th>
                                        <th><sup>13</sup>Fruída:2</th>
                                        <th><sup>14</sup>Fruída:3</th>

                                        <th><sup>15</sup>Saldo</th>

                                        <th style='width: 110px'><sup></sup>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="text-end">
                                Status:
                                <kbd class="bg-secondary m-1 legenda">Férias Fruídas</kbd>
                                <kbd class="bg-primary m-1 legenda">Aguardando RH</kbd>
                                <kbd class="bg-warning m-1 legenda">Aguardando Gestor</kbd>
                                <kbd class="bg-success m-1 legenda">Agendada(RH)</kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- The Modal VISUALIZAR -->
                <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarPeriodoLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="background-color: gainsboro">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <h5><i class="fa-solid fa-eye"></i> Férias: Visualizar Período Aquisitivo</h5>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label class="col-sm-3 col-form-label">Nome do Cargo</label>
                                        <p id="v_nome" class="form-control-plaintext visCampo text-center h6"></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-form-label">Período Aquisitivo</label>
                                        <p id="v_aquisitivo" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-form-label">Período Concessivo</label>
                                        <p id="v_concessivo" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-2">
                                        <label class="col-form-label">Agenda:1</label>
                                        <p id="v_agenda1" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-2">
                                        <label class="col-form-label">Dias:1</label>
                                        <p id="v_dias1" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-2">
                                        <label class="col-form-label">Agenda:2</label>
                                        <p id="v_agenda2" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-2">
                                        <label class="col-form-label">Dias:2</label>
                                        <p id="v_dias2" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-2">
                                        <label class="col-form-label">Agenda:3</label>
                                        <p id="v_agenda3" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-2">
                                        <label class="col-form-label">Dias:3</label>
                                        <p id="v_dias3" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <label class="col-form-label">Data Fruição 1ª parcela</label>
                                        <p id="v_fruido1" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="col-form-label">Data Fruição 2ª parcela</label>
                                        <p id="v_fruido2" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="col-form-label">Data Fruição 3ª parcela</label>
                                        <p id="v_fruido3" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label class="col-form-label">Observações</label>
                                        <p id="v_obs" class="form-control-plaintext visCampo text-center"></p>
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

                <!-- The Modal EDITAR FERIAS -->
                <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarPeriodoLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content" style="background-color: gainsboro">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fa-solid fa-pen-to-square"></i> Férias: Editar Período Aquisitivo
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <form action="#" id="formEditar" method='POST'>
                                    <input type="hidden" id="e_id" name="e_id" value="0">


                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Colaborador</label>
                                            <p id="e_nome" class="form-control-plaintext evisCampo text-center h5"></p>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <label class="col-form-label">Período Aquisitivo</label>
                                            <p id="e_aquisitivo" class="form-control-plaintext evisCampo text-center"></p>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <label class="col-form-label">Período Concessivo</label>
                                            <p id="e_concessivo" class="form-control-plaintext evisCampo text-center"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <!-- Agenda 1 -->
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Agenda:1</label>
                                            <input type="date" class="form-control text-center obrigatorio" id="e_agenda1" name="e_agenda1">
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Dias:1</label>
                                            <input type="number" class="form-control text-center obrigatorio" id="e_dias1" name="e_dias1" min="0" onchange='dias(this)'>
                                        </div>
                                        <!-- Agenda 2 -->
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Agenda:2</label>
                                            <input type="date" class="form-control text-center" id="e_agenda2" name="e_agenda2">
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Dias:2</label>
                                            <input type="number" class="form-control text-center" id="e_dias2" name="e_dias2" min="0" onchange='dias(this)'>
                                        </div>
                                        <!-- Agenda 3 -->
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Agenda:3</label>
                                            <input type="date" class="form-control text-center" id="e_agenda3" name="e_agenda3">
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Dias:3</label>
                                            <input type="number" class="form-control text-center" id="e_dias3" name="e_dias3" min="0" onchange='dias(this)'>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <label class="col-form-label">Data Fruição 1ª parcela</label>
                                            <input type="date" class="form-control text-center" id="e_fruido1" name="e_fruido1">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="col-form-label">Data Fruição 2ª parcela</label>
                                            <input type="date" class="form-control text-center" id="e_fruido2" name="e_fruido2">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="col-form-label">Data Fruição 3ª parcela</label>
                                            <input type="date" class="form-control text-center" id="e_fruido3" name="e_fruido3">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Observações</label>
                                            <textarea class="form-control text-center" rows="2" id="e_obs" name="e_obs"></textarea>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="btn-group" id='botoes_editar'>
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                                <i class="fa fa-close"></i> Cancelar
                                            </button>
                                            <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_reset()'>
                                                <i class="fa-solid fa-recycle"></i> Resetar
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="f_editar_commit()">
                                                <i class="fa-solid fa-floppy-disk"></i> Salvar
                                            </button>

                                        </div>
                                    </div>
                                </form>
                                <div id='msgEditar' class='h5 text-center'></div>
                            </div>
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
                            <div class="modal-footer p-0 w-100">
                                <div class="row w-100 m-2">
                                    <div class="col-6 p-1 m-0">
                                        <button class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                    </div>
                                    <div class="col-6 p-1 m-0">
                                        <button class="btn btn-outline-primary w-100" onclick="f_aprovar_selecionadas()">
                                            Aprovar Selecionadas
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Modal de Aprovação -->
                <div class="modal fade" id="modalAprovar" tabindex="-1" aria-hidden="false">
                    <input type="hidden" id='vetorParcelas' value="<?= $vetor ?>">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light border-secondary">
                            <div class="modal-header border-secondary">
                                <h5 class="modal-title" id="modalAprovarLabel">Aprovar Férias</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <label for="senhaAprovacao" class="form-label">Estas férias serão aprovadas por:</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary text-center"
                                    id='aprovador' name='aprovador' value='<?= $nmLogin ?>' readonly>

                                <!-- Seção para motivo da não aprovação -->
                                <div id="motivoNaoAprovacaoSection" class="d-none mt-3">
                                    <label for="motivoNaoAprovacao" class="form-label">Motivo da não aprovação das parcelas desmarcadas:</label>
                                    <textarea id="motivoNaoAprovacao" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="Digite o motivo aqui..."></textarea>
                                </div>
                            </div>

                            <div class="modal-footer d-flex w-100 p-0 border-secondary">
                                <button type="button" class="btn btn-outline-light flex-fill m-1" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-success flex-fill m-1" onclick="confirmarAprovacao()">Confirmar</button>
                            </div>

                            <div id='msgAprova' class="h5 text-center mt-2 text-info"></div>
                        </div>
                    </div>
                </div>


            </main>
            <!-- 
                    Aqui Termina o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_ferias.js"></script>
</body>

</html>