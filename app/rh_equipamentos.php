<?php
//
//- rh_cargos.php | CARGOS do RH
// (C)haia, 20/03/2025

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
    <meta name="description" content="Solicitação de Equipoamentos" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_afastamentos.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI (CSS e JS) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>


    <!-- Inclua os arquivos do DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_equipamentos.css" rel="stylesheet" />

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
                            <i class="fa-solid fa-computer"></i> EQUIPAMENTOS
                        </h3>
                    </div>
                    <div id='divAlertaCargo' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i> Equipamentos cedidos ao Colaborador
                            </div>
                            <div class="float-end">
                                <!--<a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a> -->
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="card col-md-6">
                                    <div class="card-header h6 clearfix">
                                        <div class="float-start">
                                            <i class="fas fa-table me-1"></i> Solicitações de Equipamentos
                                        </div>
                                        <div class="float-end">
                                            <a href='#!' onclick='f_incluir_solicitacao()' class='btn btn-sm btn-outline-success'>Incluir</a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table id="tabelaSolicitacoes" class="table table-striped table-bordered w-100">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Responsável</th>
                                                    <th>Equipamentos</th>
                                                    <th><i class="fa-solid fa-magnifying-glass"></i></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <div class="card col-md-6">
                                    <div class="card-header h6">Equipamentos entregues</div>
                                    <div class="card-body">
                                        <table id="tabelaEquipamentos" class="table table-striped table-bordered w-100">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Responsável</th>
                                                    <th>Status</th>
                                                    <th><i class="fa-solid fa-magnifying-glass"></i></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </main>

            <!-- The Modal Incluir Nova Solicitação-->
            <div class="modal fade" id="modalIncSolic" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                aria-labelledby="solicitacaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class=" modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header bg-primary text-white">
                            <h4 class="modal-title"><i class="fa-solid fa-computer"></i> Fazer Solicitação de Equipamentos</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <form action="">
                                <input type="hidden" id='idPessoa' name='idPessoa'>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="inc_responsavel">Responsável pelo termo</label>
                                        <input type="text" class="form-control" id="inc_responsavel" name="inc_responsavel" placeholder="Começe digitando o nome..." required>
                                    </div>
                                    <div class="col-sm-12 mt-3">
                                        <label for="inc_usuario">Usuário final dos equipamentos</label>
                                        <input type="text" class="form-control" id="inc_usuario" name="inc_usuario" placeholder="Informe o usuário final!" required>
                                    </div>
                                    <div class="col-sm-12 mt-3">
                                        <label class="m-2">Equipamentos necessários</label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipamentos[]" id="equip_notebook" value="Notebook">
                                                <label class="form-check-label" for="equip_notebook">Notebook</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipamentos[]" id="equip_smartphone" value="Smartphone">
                                                <label class="form-check-label" for="equip_smartphone">Smartphone</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipamentos[]" id="equip_chip" value="Chip">
                                                <label class="form-check-label" for="equip_chip">Chip</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipamentos[]" id="equip_desktop" value="Desktop">
                                                <label class="form-check-label" for="equip_desktop">Desktop</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipamentos[]" id="equip_monitor" value="Monitor">
                                                <label class="form-check-label" for="equip_monitor">Monitor</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipamentos[]" id="equip_impressora" value="Impressora">
                                                <label class="form-check-label" for="equip_impressora">Impressora</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 mt-3">
                                        <label for="inc_observacoes">Observações</label>
                                        <textarea class="form-control" id="inc_observacoes" name="inc_observacoes" rows="5" placeholder="Descreva aqui as observações necessárias..."></textarea>
                                    </div>
                                </div>
                            </form>


                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer d-flex w-100 gap-2" id='botoes_incluir'>
                            <button type="button" class="btn btn-outline-danger flex-fill" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="button" class="btn btn-outline-primary flex-fill" onclick='f_incluir_solicitacao_commit()'>
                                Confirmar
                            </button>
                        </div>
                        <div class="text-center h6" id='msgAlertaSolicitacao'></div>

                    </div>
                </div>


            </div>

            <!-- The Modal VISUALISAR Solicitação-->

            <div class="modal fade" id="modalVerSolic" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header bg-secondary text-white">
                            <h4 class="modal-title"><i class="fa-solid fa-eye"></i> Detalhes da Solicitação</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label class="fw-bold">Responsável pelo termo</label>
                                    <p id="view_responsavel" class="form-control-plaintext border rounded px-2"></p>
                                </div>

                                <div class="col-sm-12 mt-3">
                                    <label class="fw-bold">Usuário final dos equipamentos</label>
                                    <p id="view_usuario" class="form-control-plaintext border rounded px-2"></p>
                                </div>

                                <div class="col-sm-12 mt-3">
                                    <label class="fw-bold">Equipamentos necessários</label>
                                    <p id="view_equipamentos" class="form-control-plaintext border rounded px-2"></p>
                                </div>

                                <div class="col-sm-12 mt-3">
                                    <label class="fw-bold">Observações</label>
                                    <p id="view_observacoes" class="form-control-plaintext border rounded px-2"></p>
                                </div>
                                <div class="col-sm-4 mt-3">
                                    <label class="fw-bold">Criado em</label>
                                    <p id="view_criado_em" class="form-control-plaintext border rounded px-2 text-center"></p>
                                </div>
                                <div class="col-sm-4 mt-3">
                                    <label class="fw-bold">Criado Por</label>
                                    <p id="view_criado_por" class="form-control-plaintext border rounded px-2 text-center"></p>
                                </div>
                                <div class="col-sm-4 mt-3">
                                    <label class="fw-bold">ID GLPI</label>
                                    <p id="view_glpi" class="form-control-plaintext border rounded px-2 text-center"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Fechar</button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- The Modal VISUALISAR TERMOS-->
            <div class="modal fade" id="modalVerTermo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header bg-secondary text-white">
                            <h4 class="modal-title"><i class="fa-solid fa-eye"></i> Detalhes do  Termo</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row">
                                <!-- Coluna esquerda (campos de visualização) -->
                                <div class="col-sm-8">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label class="fw-bold">Responsável pelo termo</label>
                                            <p id="vw_termo_responsavel" class="form-control-plaintext border rounded px-2"></p>
                                        </div>
                                        <div class="col-sm-12">
                                            <label class="fw-bold">Endereço</label>
                                            <p id="vw_termo_endereco" class="form-control-plaintext border rounded px-2"></p>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="fw-bold">e-Mail</label>
                                            <p id="vw_termo_email" class="form-control-plaintext border rounded px-2"></p>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="fw-bold">Celular</label>
                                            <p id="vw_termo_celular" class="form-control-plaintext border rounded px-2"></p>
                                        </div>

                                        <div class="col-sm-12 mt-3">
                                            <label class="fw-bold">Equipamentos entregues</label>
                                            <p id="vw_termo_equipamentos" class="form-control-plaintext border rounded px-2"></p>
                                        </div>

                                        <div class="col-sm-4 mt-3">
                                            <label class="fw-bold">Criado em</label>
                                            <p id="vw_termo_criado_em" class="form-control-plaintext border rounded px-2 text-center"></p>
                                        </div>
                                        <div class="col-sm-4 mt-3">
                                            <label class="fw-bold">Criado Por</label>
                                            <p id="vw_termo_criado_por" class="form-control-plaintext border rounded px-2 text-center"></p>
                                        </div>
                                        <div class="col-sm-4 mt-3">
                                            <label class="fw-bold">Status</label>
                                            <p id="vw_termo_status" class="form-control-plaintext border rounded px-2 text-center"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Coluna direita (preview do documento) -->
                                <div class="col-sm-4 text-center">
                                    <label class="fw-bold">Documento Anexado</label>
                                    <div id="view_documento" class="border rounded p-2" style="min-height: 200px;">
                                        <!-- Aqui você pode injetar via JS:
                                            <embed src="arquivo.pdf" type="application/pdf" width="100%" height="400px" />
                                            ou <img src="imagem.jpg" class="img-fluid" />
                                            ou até um link -->
                                    </div>
                                    <input type="hidden" id="vw_termo_documento">
                                    <button type="button" class="btn btn-outline-primary w-100 mt-3" onclick="f_preview_documento()">Ver</button>
                                </div>
                            </div>
                        </div>


                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Fechar</button>
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
    <script src="js/rh_equipamentos.js"></script>
</body>

</html>