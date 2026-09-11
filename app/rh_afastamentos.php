<?php
//
//- rh_afastamentos.php | Grid Afastamentos
// (C)haia, 30/07/2025

//use phpseclib3\Math\BigInteger\Engines\PHP;

session_start();

$idModulo = 17; // Afastamentos

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    include "includes/conexao_gerar.php";
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
                            <i class="fa-solid fa-hospital-user"></i> Afastamentos
                        </h3>
                    </div>
                    <div id='divAlertaAfastamento' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Registros dos Afastamentos dos Colaboradores
                            </div>
                            <div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a></div>
                        </div>
                        <div class="card-body">
                            <table id="example" class="table table-striped table-hover table-bordered table-sm nowrap w-100">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>ID</th>
                                        <th><sup>1</sup>Colaborador</th>
                                        <th><sup>2</sup>Tipo</th>
                                        <th><sup>3</sup>Data Ini</th>
                                        <th><sup>4</sup>Data Fim</th>
                                        <th><sup>5</sup>Qtd</th>
                                        <th><sup>6</sup>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- The Modal INCLUIR AFASTAMENTO -->
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incluirLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-plus"></i> Incluir Atestado Médico</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formIncluirAfastamento" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="afa_nome" class="col-sm-3 col-form-label">Colaborador</label>
                                        <input type="text" class="form-control" id="afa_nome" name="afa_nome" placeholder="comece digitando o nome do colaborador..." required>
                                        <input type="hidden" id='afa_idColab' name='afa_idColab'>
                                        <input type="hidden" id='afa_idPessoa' name='afa_idPessoa'>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="afa_data" class="col-form-label">Data</label>
                                        <input type="date" class="form-control text-center" id="afa_data" name="afa_data" onBlur='verifica_data(this)'>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="afa_qtd" class="col-form-label">Qtd Dias</label>
                                        <input type="number" class="form-control text-center" id="afa_qtd" name="afa_qtd" placeholder="Qtd" onBlur='calc_dias()'>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="afa_dtRetorno" class="col-form-label">Retorno</label>
                                        <input type="date" class="form-control text-center" id="afa_dtRetorno" name="afa_dtRetorno"
                                            onfocus='desloca("formIncluirAfastamento")' readonly style='background-color: #d3a2a2ff'>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="tipo_afastamento" class="col-form-label">Tipo Afastamento</label>
                                        <?= seletor_tipo() ?> <!-- deve conter: <select id="tipo_afastamento" ...> -->
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-8">
                                        <label for="_tipo" class="col-form-label">Emitido por:</label>
                                        <input type="text" class="form-control text-center" id="afa_emitidoPor" name="afa_emitidoPor">
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="_tipo" class="col-form-label">CID:</label>
                                        <input type="text" class="form-control text-center" id="afa_cid" name="afa_cid">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="afa_arquivo" class="col-form-label">Arquivo para upload</label>
                                        <input type="file" class="form-control text-center" id="afa_arquivo" name="afa_arquivo">
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

            <!-- The Modal EDITAR AFASTAMENTO -->
            <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarAfastamentoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-regular fa-pen-to-square"></i> Alterar Registro de Afastamento</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formEditar" method="POST" enctype="multipart/form-data">
                            <input type="hidden" id="e_id" name="e_id">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="e_nome" class="col-sm-3 col-form-label">Colaborador</label>
                                        <input type="text" class="form-control" id="e_nome" name="e_nome" placeholder="comece digitando o nome do colaborador..." required>
                                        <input type="hidden" id='e_idColab' name='e_idColab'>
                                        <input type="hidden" id='e_idPessoa' name='e_idPessoa'>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="e_data" class="col-form-label">Data</label>
                                        <input type="date" class="form-control text-center" id="e_data" name="e_data" onBlur='verifica_data(this)'>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="e_qtd" class="col-form-label">Qtd Dias</label>
                                        <input type="number" class="form-control text-center" id="e_qtd" name="e_qtd" placeholder="Qtd" onBlur='calc_dias()'>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="e_dtRetorno" class="col-form-label">Retorno</label>
                                        <input type="date" class="form-control text-center" id="e_dtRetorno" name="e_dtRetorno" 
                                            onfocus='desloca("formEditar")' readonly style='background-color: #d3a2a2ff'>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="tipo_afastamento" class="col-form-label">Tipo Afastamento</label>
                                        <?= seletor_tipo() ?> <!-- deve conter: <select id="tipo_afastamento" ...> -->
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-8">
                                        <label for="_tipo" class="col-form-label">Emitido por:</label>
                                        <input type="text" class="form-control text-center" id="e_emitidoPor" name="e_emitidoPor">
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="_tipo" class="col-form-label">CID:</label>
                                        <input type="text" class="form-control text-center" id="e_cid" name="e_cid">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="_tipo" class="col-form-label">Arquivo para substituir o anterior</label>
                                        <input type="file" class="form-control text-center" id="e_arquivo" name="e_arquivo">
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

            <!-- The Modal VISUALIZAR AFASTAMENTO -->
            <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarAfastamentoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-eye"></i> Visualizar Registro do Afastamento</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label class="col-sm-3 col-form-label">Colaborador</label>
                                    <p id="v_nome" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-4">
                                    <label for="v_data" class="col-form-label">Data</label>
                                    <p id="v_data" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-4">
                                    <label for="v_qtd" class="col-form-label">Qtd Dias</label>
                                    <p id="v_qtd" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-4">
                                    <label for="v_retorno" class="col-form-label">Retorno</label>
                                    <p id="v_retorno" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Tipo de Afastamento</label>
                                    <p id="v_tipo" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-8">
                                    <label class="col-form-label">Emitido por:</label>
                                    <p id="v_emitido_por" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">CID</label>
                                    <p id="v_cid" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Arquivo enviado</label>
                                    <p id="v_arquivo" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" data-bs-dismiss="modal">
                                        <i class="fa fa-close"></i> Fechar
                                    </button>
                                </div>
                                <span id='v_quando' class="mt-2 ms-2" style='font-size: 12px; font-weight: 200'></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The Modal APROVAR AFASTAMENTO -->
            <div class="modal fade" id="modalAprovar" tabindex="-1" aria-labelledby="aprovarAfastamentoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-eye"></i> Registro do Afastamento</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <form action="form_aprova">
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label class="col-sm-3 col-form-label">Colaborador</label>
                                        <p id="v_apr_nome" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="v_apr_data" class="col-form-label">Data</label>
                                        <p id="v_apr_data" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="v_apr_qtd" class="col-form-label">Qtd Dias</label>
                                        <p id="v_apr_qtd" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="v_apr_retorno" class="col-form-label">Retorno</label>
                                        <p id="v_apr_retorno" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                </div>


                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label class="col-form-label">Tipo de Afastamento</label>
                                        <p id="v_apr_tipo" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-8">
                                        <label class="col-form-label">Emitido por:</label>
                                        <p id="v_apr_emitido_por" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="col-form-label">CID</label>
                                        <p id="v_apr_cid" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label class="col-form-label">Arquivo enviado</label>
                                        <p id="v_apr_arquivo" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="btn-group" id='botoes_aprovar'>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick='f_aprovar_commit( "aprovado" )'>
                                            <i class="fa-regular fa-thumbs-up"></i> Aceitar
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" onclick='f_aprovar_commit( "reprovado" )'>
                                            <i class="fa-regular fa-thumbs-down"></i> Rejeitar
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Fechar
                                        </button>
                                    </div>
                                    <div id='msgAlertaAprovar' class="text-center h5"></div>
                                    <input type="hidden" id='v_apr_id' name='v_apr_id'>
                                </div>
                            </form>
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
    <script src="js/rh_afastamentos.js"></script>
</body>

</html>
<?PHP

function seletor_tipo($_idTipo = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_afastamento_tipos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idTipo' name='idTipo'>";
    if ($_idTipo == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idTipo == $id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$id' $selected>$descricao</option>";
    }
    $select .= "</select>";
    echo $select;
}
