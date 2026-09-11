<?php
//
// rh_ctr_exp.php | GRID de Contratos de Experiência
// by (C)haia, 19/09/2025
//

session_start();

$idModulo = 20; // Contratos de Experiência

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
    <link href="css/rh_ctr_exp.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- jQuery + jQuery UI -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    <style>

        .status{
            font-size: 12px;
            height: 22px;
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
                        <h3 class="m-4">
                            <i class="fa-solid fa-file-signature"></i> CONTRATOS DE EXPERIÊNCIA
                        </h3>
                    </div>
                    <div id='divAlertaPrincipal' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Relação dos Contratos de Experiência
                            </div>
                            <div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a></div>
                        </div>
                        <div class="card-body">
                            <table id="example" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>Colaborador</th>
                                        <th><sup>1</sup>Cargo</th>
                                        <th><sup>2</sup>Orgão</th>
                                        <th><sup>3</sup>Inicio</th>
                                        <th><sup>3</sup>Prorrogação</th>
                                        <th><sup>4</sup>Fim</th>
                                        <th><sup>5</sup>Duração</th>
                                        <th><sup>6</sup>Status</th>
                                        <th style='width: 110px'><sup></sup>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- The Modal INCLUIR CONTRATO DE EXPERIÊNCIA  -->
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incluirContratoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-plus"></i> Incluir Novo Contrato de Experiência</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formIncluir">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="nome" class="col-sm-3 col-form-label">Nome do Contratado</label>
                                        <input type="text" class="form-control" id="nome" name="nome" placeholder="começe digitando o nome do colaborador...">
                                        <input type="hidden" class="form-control" id="idPessoa" name="idPessoa">
                                        <input type="hidden" class="form-control" id="idColab" name="idColab">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="dtInicial">Data de Início</label>
                                        <input type="date" class="form-control text-center" id="dtInicial" name="dtInicial" value='<?= date('Y-m-d') ?>'>
                                    </div>

                                    <div class="col">
                                        <label for="duracao">Duração</label>
                                        <select class="form-control text-center" id="duracao" name="duracao">
                                            <option value="">Selecione...</option>
                                            <option value="45">45 dias (sem prorrogação)</option>
                                            <option value="90">90 dias direto</option>
                                            <option value="45+45">45 dias + 45 dias</option>
                                            <option value="30+60">30 dias + 60 dias</option>
                                            <option value="60+30">60 dias + 30 dias</option>
                                        </select>
                                    </div>

                                    <div class="col">
                                        <label for="dtProrrogacao">Data da Prorrogação</label>
                                        <input type="date" class="form-control text-center" id="dtProrrogacao" name="dtProrrogacao" readonly>
                                    </div>

                                    <div class="col">
                                        <label for="dtFinal">Data Final</label>
                                        <input type="date" class="form-control text-center" id="dtFinal" name="dtFinal" readonly>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col">
                                        <label for="observacao">Observação</label>
                                        <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col">
                                        <label for="arquivo">Arquivo do Contrato</label>
                                        <input type="file" class="form-control" id="arquivo" name="arquivo" accept="application/pdf">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3 mb-3">
                                    <div class="btn-group" id='botoes_incluir'>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Cancelar
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_limpar()'>
                                            <i class="fa-solid fa-recycle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="btnSalvar" onclick='f_incluir_commit()'>
                                            <i class="fa fa-save"></i> Salvar
                                        </button>
                                    </div>
                                    <div id='msgAlertaIncluir' class="text-center"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- The Modal EDITAR CONTRATO DE EXPERIÊNCIA  -->
            <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarContratoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-pen-to-square"></i> Editar Contrato de Experiência</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formEditar">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="e_nome" class="col-sm-3 col-form-label">Nome do Contratado</label>
                                        <input type="text" class="form-control text-center" id="e_nome" name="e_nome" readonly style="background-color: #BEBEBE; color: white; font-weight: 700;">
                                        <input type="hidden" id="e_idPessoa" name="e_idPessoa">
                                        <input type="hidden" id="e_idColab" name="e_idColab">
                                        <input type="hidden" id="e_idContrato" name="e_idContrato">

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="e_dtInicial">Data de Início</label>
                                        <input type="date" class="form-control text-center" id="e_dtInicial" name="e_dtInicial">
                                    </div>

                                    <div class="col">
                                        <label for="e_duracao">Duração</label>
                                        <select class="form-control text-center" id="e_duracao" name="e_duracao">
                                            <option value="">Selecione...</option>
                                            <option value="45">45 dias (sem prorrogação)</option>
                                            <option value="90">90 dias direto</option>
                                            <option value="45+45">45 dias + 45 dias</option>
                                            <option value="30+60">30 dias + 60 dias</option>
                                            <option value="60+30">60 dias + 30 dias</option>
                                        </select>
                                    </div>

                                    <div class="col">
                                        <label for="e_dtProrrogacao">Data da Prorrogação</label>
                                        <input type="date" class="form-control text-center" id="e_dtProrrogacao" name="e_dtProrrogacao" readonly>
                                    </div>

                                    <div class="col">
                                        <label for="e_dtFinal">Data Final</label>
                                        <input type="date" class="form-control text-center" id="e_dtFinal" name="e_dtFinal" readonly>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col">
                                        <label for="e_observacao">Observação</label>
                                        <textarea class="form-control" id="e_observacao" name="e_observacao" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col">
                                        <label for="e_arquivo">Arquivo do Contrato para substituir o anterior</label>
                                        <input type="file" class="form-control" id="e_arquivo" name="e_arquivo" accept="application/pdf">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3 mb-3">
                                    <div class="btn-group" id='botoes_editar'>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Cancelar
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_reset_editar()'>
                                            <i class="fa-solid fa-recycle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="btnSalvarEditar" onclick='f_editar_commit()'>
                                            <i class="fa fa-save"></i> Salvar
                                        </button>
                                    </div>
                                    <div id='msgAlertaEditar' class="text-center"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal: VISUALIZAR Contrato de Experiência -->
            <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarContratoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h5 class="modal-title">
                                <i class="fa-solid fa-magnifying-glass"></i> Visualizar Contrato de Experiência
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row">
                                <!-- COLUNA DOS DADOS GERAIS -->
                                <div class="col-sm-8">
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Nome do Contratado</label>
                                            <p id="v_nome" class="form-control-plaintext visCampo text-center"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col">
                                            <label>Data de Início</label>
                                            <p id="v_dtInicial" class="form-control-plaintext text-center visCampo"></p>
                                        </div>
                                        <div class="col">
                                            <label>Duração</label>
                                            <p id="v_duracao" class="form-control-plaintext text-center visCampo"></p>
                                        </div>
                                        <div class="col">
                                            <label>Data da Prorrogação</label>
                                            <p id="v_dtProrrogacao" class="form-control-plaintext text-center visCampo"></p>
                                        </div>
                                        <div class="col">
                                            <label>Data Final</label>
                                            <p id="v_dtFinal" class="form-control-plaintext text-center visCampo"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col">
                                            <label>Observação</label>
                                            <p id="v_observacao" class="form-control-plaintext visCampo"></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- COLUNA DO PREVIEW -->
                                <div class="col-sm-4">
                                    <div class="row">
                                        <div class="col-12">
                                            <label>Documento do Contrato</label>
                                            <div class="border rounded bg-white d-flex justify-content-center align-items-center"
                                                style="height: 400px; overflow: hidden;">
                                                <!-- Preview PDF -->
                                                <iframe id="v_previewDoc" src="" width="100%" height="100%" style="border: none;"></iframe>
                                            </div>
                                            <div class="text-center mt-2">
                                                <a id="v_btnVisualizarDoc" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="fa fa-eye"></i> Visualizar em Nova Guia
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3 mb-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                        <i class="fa fa-close"></i> Fechar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            >

            <!-- 
                    Aqui Termina o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_ctr_exp.js"></script>
</body>

</html>