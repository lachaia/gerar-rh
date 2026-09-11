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
    <meta name="description" content="Tabela de Cargos do RH" />
    <meta name="author" content="LAChaia" />
    <title>GERAR|Cargos</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_cargos.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
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
                            <i class="fa-solid fa-sitemap"></i> CARGOS
                        </h3>
                    </div>
                    <div id='divAlertaCargo' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Distribuição do Orgãos da Empresa
                            </div>
                            <div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a></div>
                        </div>
                        <div class="card-body">
                            <table id="example" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>ID</th>
                                        <th><sup>1</sup>Cargo</th>
                                        <th><sup>2</sup>Descrição</th>
                                        <th><sup>3</sup>Nível</th>
                                        <th><sup>4</sup>Qtd</th>
                                        <th><sup>5</sup>ativo</th>
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

            <!-- The Modal INCLUIR CARGO -->
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incluirCargoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-plus"></i> Incluir Cargo</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formIncluir">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-10">
                                        <label for="_nome" class="col-sm-3 col-form-label">Nome do Cargo</label>
                                        <input type="text" class="form-control" id="_nome" name="_nome" placeholder="Digite o nome do cargo">
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="_nivel" class="col-form-label">Nível</label>
                                        <input type="number" class="form-control text-center" id="_nivel" name="_nivel" placeholder="Nível">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="_descricao" class="col-form-label">Descrição</label>
                                        <textarea class="form-control" id="_descricao" name="_descricao" rows="4" placeholder="Digite a descrição do cargo"></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
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

            <!-- The Modal EDITAR CARGO -->
            <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarCargoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-regular fa-pen-to-square"></i> Alterar Cargo</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formEditar">
                            <input type="hidden" id="e_idCargo" name="e_idCargo">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-8">
                                        <label for="_nome" class="col-sm-3 col-form-label">Nome do Cargo</label>
                                        <input type="text" class="form-control" id="e_nome" name="e_nome" placeholder="Digite o nome do cargo">
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="_nivel" class="col-form-label">Nível</label>
                                        <input type="number" class="form-control text-center" id="e_nivel" name="e_nivel" placeholder="Nível">
                                    </div>
                                    <div class="col-sm-2 mt-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="e_ativo" name='e_ativo' onchange="toggleAtivo()">
                                            <label class="form-check-label" for="e_ativo"><span id='labelAtivo'>Ativo</span></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="_descricao" class="col-form-label">Descrição</label>
                                        <textarea class="form-control" id="e_descricao" name="e_descricao" rows="4" placeholder="Digite a descrição do cargo"></textarea>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="btn-group" id='botoes_editar'>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Cancelar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded m-1" id="e_btnReset" onclick='f_reset()'>
                                            <i class="fa-solid fa-recycle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="e_btnSalvar" onclick='f_editar_commit()'>
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
    <script src="js/rh_cargos.js"></script>
</body>

</html>