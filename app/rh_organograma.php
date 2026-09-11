<?php
//
//- rh_organograma.php | Organograma Empresarial  
// (C)haia, 24/02/2025

session_start();

$idModulo = 3; // rh_organograma

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
    <link href="css/rh_organograma.css" rel="stylesheet" />

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                            <i class="fa-solid fa-sitemap"></i> ORGANOGRAMA EMPRESARIAL
                        </h3>
                    </div>
                    <div id='divAlertaOrganograma' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Distribuição do Orgãos da Empresa
                            </div>
                            <div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>+ Incluir</a></div>
                        </div>
                        <div class="card-body">
                            <table id="example" class="table nowrap table-striped table-hover table-bordered table-sm fb-8" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>ID</th>
                                        <th style='width: 300px'><sup>1</sup>Descrição</th>
                                        <th><sup>2</sup><i class="fa-solid fa-user"></i></th>
                                        <th><sup>3</sup>Nível</th>
                                        <th><sup>4</sup>Supervisor</th>
                                        <th><sup>5</sup>Staff</th>
                                        <th><sup>6</sup>Estratégico</th>
                                        <th><sup>7</sup>Ativo</th>
                                        <th><sup>8</sup>N1</th>
                                        <th><sup>9</sup>N2</th>
                                        <th><sup>10</sup>N3</th>
                                        <th><sup>11</sup>N4</th>
                                        <th><sup>12</sup>N5</th>
                                        <th><sup>13</sup>N6</th>
                                        <th><sup>14</sup>N7</th>
                                        <th><sup></sup>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- The Modal INCLUIR novo ORGÃO -->
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incPessoaModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="incUsuarioModalLabel">
                                <h5><i class="fa-solid fa-sitemap"></i> Inclusão de Orgão no Organograma</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <form id="formIncluir">
                                <div class="row mb-3">

                                    <div class="col-sm-10">
                                        <label for="_nome" class="col-sm-3 col-form-label">Nome do Órgão</label>
                                        <input type="text" name="_nome" value="" class="form-control" id="_nome" placeholder="Nome">
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="_nivel" class="col-form-label">Nivel</label>
                                        <input type="number" name="_nivel" class="form-control" id="_nivel">
                                    </div>

                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6" id='seletor_supervisor'>
                                        <label for="_idSupervisor" class="col-form-label">Órgão Supervisor</label>
                                        <select name="_idSupervisor" class="form-select" id="_idSupervisor">
                                            <option value="0">Nenhum</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="_staff" class="col-form-label">Staff</label>
                                        <select name="_staff" class="form-select" id="_staff">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="_estrategico" class="col-form-label">Estratégico</label>
                                        <select name="_estrategico" class="form-select" id="_estrategico">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel1" class="col-form-label">Nível 1</label>
                                        <input type="number" id="_nivel1" name="_nivel1" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel2" class="col-form-label">Nível 2</label>
                                        <input type="number" id="_nivel2" name="_nivel2" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel3" class="col-form-label">Nível 3</label>
                                        <input type="number" id="_nivel3" name="_nivel3" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel4" class="col-form-label">Nível 4</label>
                                        <input type="number" id="_nivel4" name="_nivel4" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel5" class="col-form-label">Nível 5</label>
                                        <input type="number" id="_nivel5" name="_nivel5" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel6" class="col-form-label">Nível 6</label>
                                        <input type="number" id="_nivel6" name="_nivel6" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel7" class="col-form-label">Nível 7</label>
                                        <input type="number" id="_nivel7" name="_nivel7" required value="" class="form-control text-center">
                                    </div>
                                </div>


                                <div class="row mb-3">
                                    <div class="btn-group" id="botoes_incluir">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Cancelar</button>
                                        <button type="reset" class="btn btn-outline-warning btn-sm rounded m-1"><i class="fa fa-recycle"></i> Reset</button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" value="Cadastrar" onclick="f_incluir_commit()"><i class="fa fa-upload"></i> Salvar</button>
                                    </div>
                                    <span id="msgAlertaIncluir" class='h5 text-center'></span>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <!-- The Modal EDITAR ORGÃO -->
            <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="incPessoaModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="incUsuarioModalLabel">
                                <h5><i class="fa-solid fa-sitemap"></i> Editar Orgão no Organograma</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <form id="formEditar">
                                <div class="row mb-3">

                                    <div class="col-sm-10">
                                        <label for="_nome" class="col-sm-3 col-form-label">Nome do Órgão</label>
                                        <input type="text" name="_nome" value="" class="form-control" id="_nome" placeholder="Nome">
                                        <input type="hidden" name="_id" value="" id="_id">
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="_nivel" class="col-form-label">Nivel</label>
                                        <input type="number" name="_nivel" class="form-control" id="_nivel">
                                    </div>

                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6" id='seletor_supervisor'>
                                        <label for="_idSupervisor" class="col-form-label">Órgão Supervisor</label>
                                        <select name="_idSupervisor" class="form-select" id="_idSupervisor">
                                            <option value="0">Nenhum</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="_staff" class="col-form-label">Staff</label>
                                        <select name="_staff" class="form-select" id="_staff">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="_estrategico" class="col-form-label">Estratégico</label>
                                        <select name="_estrategico" class="form-select" id="_estrategico">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel1" class="col-form-label">Nível 1</label>
                                        <input type="number" id="_nivel1" name="_nivel1" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel2" class="col-form-label">Nível 2</label>
                                        <input type="number" id="_nivel2" name="_nivel2" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel3" class="col-form-label">Nível 3</label>
                                        <input type="number" id="_nivel3" name="_nivel3" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel4" class="col-form-label">Nível 4</label>
                                        <input type="number" id="_nivel4" name="_nivel4" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel5" class="col-form-label">Nível 5</label>
                                        <input type="number" id="_nivel5" name="_nivel5" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel6" class="col-form-label">Nível 6</label>
                                        <input type="number" id="_nivel6" name="_nivel6" required value="" class="form-control text-center">
                                    </div>
                                    <div class="col-sm-2 d-flex flex-column align-items-center">
                                        <label for="_nivel7" class="col-form-label">Nível 7</label>
                                        <input type="number" id="_nivel7" name="_nivel7" required value="" class="form-control text-center">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="btn-group" id="botoes_editar">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Cancelar</button>
                                        <button type="reset" class="btn btn-outline-warning btn-sm rounded m-1"><i class="fa fa-recycle"></i> Reset</button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" value="Cadastrar" onclick="f_editar_commit()"><i class="fa fa-upload"></i> Salvar</button>
                                    </div>
                                    <span id="msgAlertaEditar"></span>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <!-- The Modal VISUALIZAR ÓRGÃO -->
            <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarOrgaoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-eye"></i> Visualizar Órgão no Organograma</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-sm-10">
                                    <label class="col-sm-3 col-form-label">Nome do Órgão</label>
                                    <p id="v_nome" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2">
                                    <label class="col-form-label">Nível</label>
                                    <p id="v_nivel" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label class="col-form-label">Órgão Supervisor</label>
                                    <p id="v_idSupervisor" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-3">
                                    <label class="col-form-label">Staff</label>
                                    <p id="v_staff" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-3">
                                    <label class="col-form-label">Estratégico</label>
                                    <p id="v_estrategico" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-2 text-center">
                                    <label class="col-form-label">Nível 1</label>
                                    <p id="v_nivel1" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2 text-center">
                                    <label class="col-form-label">Nível 2</label>
                                    <p id="v_nivel2" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2 text-center">
                                    <label class="col-form-label">Nível 3</label>
                                    <p id="v_nivel3" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2 text-center">
                                    <label class="col-form-label">Nível 4</label>
                                    <p id="v_nivel4" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2 text-center">
                                    <label class="col-form-label">Nível 5</label>
                                    <p id="v_nivel5" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2 text-center">
                                    <label class="col-form-label">Nível 6</label>
                                    <p id="v_nivel6" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-2 text-center">
                                    <label class="col-form-label">Nível 7</label>
                                    <p id="v_nivel7" class="form-control-plaintext visCampo"></p>
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
    <script src="js/rh_organograma.js"></script>
</body>

</html>