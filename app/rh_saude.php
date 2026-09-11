<?php
//
//- rh_exames.php | EXAMES - SAÚDE OCUPACIONAL
// (C)haia, 17/04/2025

session_start();


$idModulo = 5; // Exames

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
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
    <link href="css/rh_saude.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- jQuery + jQuery UI -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    <style>
        .ui-autocomplete {
            z-index: 99999 !important;
            background: white;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
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
                            <i class="fa-solid fa-stethoscope"></i> EXAMES - SAÚDE OCUPACIONAL
                        </h3>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Saúde Ocupacional: Exames
                            </div>
                            <div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a></div>
                        </div>
                        <div class="card-body">
                            <div id='msgAlertaExame' class="text-center">teste</div>
                            <table id="example" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>ID</th>
                                        <th><sup>1</sup>Nome</th>
                                        <th><sup>2</sup>Data</th>
                                        <th><sup>3</sup>Exame</th>
                                        <th><sup>4</sup>Titulo</th>
                                        <th><sup>5</sup>Validade</th>
                                        <th><sup>6</sup>Status</th>
                                        <th><sup>7</sup>Clínica</th>
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

            <!-- The Modal INCLUIR EXAME -->
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incluirExameLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-plus"></i> Incluir Exame</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formIncluir">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-9">
                                        <label for="nmPessoa" class="col-form-label">Nome do Colaborador</label>
                                        <input type="text" class="form-control" id="nmPessoa" name="nmPessoa" placeholder="começe a digitar..." value=''>
                                        <input type="hidden" id='idPessoa' name='idPessoa' value="0">
                                        <input type="hidden" id='idColab' name='idColab' value="0">
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="data" class="col-form-label">Data</label>
                                        <input type="date" class="form-control text-center" id="data" name="data" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <label for="idTipo" class="col-form-label">Tipo</label>
                                        <?= seletor_tipo() ?>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="dtVencimento" class="col-form-label">Vencimento</label>
                                        <input type="date" class="form-control text-center" id="dtVencimento" name="dtVencimento">
                                    </div>
                                    <div class="col-sm-5">
                                        <label for="dsExame" class="col-form-label">Título do Exame</label>
                                        <input type="text" class="form-control text-center" id="dsExame" name="dsExame" placeholder="exame de quê?">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-5">
                                        <label for="nmClinica" class="col-form-label">Nome da Clínica</label>
                                        <input type="text" class="form-control text-center" id="nmClinica" name="nmClinica" placeholder="onde foi feito?">
                                    </div>
                                    <div class="col-sm-5">
                                        <label for="nmMedico" class="col-form-label">Médico</label>
                                        <input type="text" class="form-control text-center" id="nmMedico" name="nmMedico" placeholder="qual médico?">
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="status" class="col-form-label">Status</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="Apto">Apto</option>
                                            <option value="Inapto">Inapto</option>
                                            <option value="Pendente">Pendente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-12">
                                        <label for="obs" class="col-form-label">Descrição</label>
                                        <textarea class="form-control" id="obs" name="obs" rows="4" placeholder="Observações"></textarea>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-3">
                                        <label for="arquivo" class="col-form-label">Tipo do doc</label>
                                        <?= seletor_tipo_doc() ?>
                                    </div>
                                    <div class="col-sm-9">
                                        <label for="arquivo" class="col-form-label">Arquivo para upload</label>
                                        <input type='file' class="form-control" id="arquivo" name="arquivo" accept="application/pdf" onchange="verificaArquivo()">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="btn-group" id='botoes_editar'>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Cancelar
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_reset()'>
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

            <!-- The Modal EDITAR Exame -->
            <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarExameLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-pen-to-square"></i> Editar Exame</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->

                        <form id="formEditar">
                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-sm-9">
                                        <label for="nmPessoa" class="col-form-label">Nome do Colaborador</label>
                                        <input type="text" class="form-control" id="nmPessoa" name="nmPessoa" placeholder="começe a digitar...">
                                        <input type="hidden" id='idExame' name='idExame' value="0">
                                        <input type="hidden" id='idPessoa' name='idPessoa' value="0">
                                        <input type="hidden" id='idColab' name='idColab' value="0">
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="data" class="col-form-label">Data</label>
                                        <input type="date" class="form-control text-center" id="data" name="data" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <label for="idTipo" class="col-form-label">Tipo</label>
                                        <?= seletor_tipo() ?>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="dtVencimento" class="col-form-label">Vencimento</label>
                                        <input type="date" class="form-control text-center" id="dtVencimento" name="dtVencimento">
                                    </div>
                                    <div class="col-sm-5">
                                        <label for="dsExame" class="col-form-label">Título do Exame</label>
                                        <input type="text" class="form-control text-center" id="dsExame" name="dsExame" placeholder="exame de quê?">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-5">
                                        <label for="nmClinica" class="col-form-label">Nome da Clínica</label>
                                        <input type="text" class="form-control text-center" id="nmClinica" name="nmClinica" placeholder="onde foi feito?">
                                    </div>
                                    <div class="col-sm-5">
                                        <label for="nmMedico" class="col-form-label">Médico</label>
                                        <input type="text" class="form-control text-center" id="nmMedico" name="nmMedico" placeholder="qual médico?">
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="status" class="col-form-label">Status</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="Apto">Apto</option>
                                            <option value="Inapto">Inapto</option>
                                            <option value="Pendente">Pendente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-12">
                                        <label for="obs" class="col-form-label">Descrição</label>
                                        <textarea class="form-control" id="obs" name="obs" rows="4" placeholder="Observações"></textarea>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                <div class="col-sm-3">
                                        <label for="arquivo" class="col-form-label">Tipo do doc</label>
                                        <?= seletor_tipo_doc() ?>
                                    </div>
                                    <div class="col-sm-9">
                                        <label for="arquivo" class="col-form-label">Arquivo para upload</label>
                                        <input type='file' class="form-control" id="arquivo" name="arquivo" accept="application/pdf" onchange="verificaArquivo()">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="btn-group" id='botoes_editar'>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Cancelar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_reset()'>
                                            <i class="fa-solid fa-recycle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="btnSalvar" onclick='f_editar_commit()'>
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

            <!-- The Modal VISUALIZAR Exame -->
            <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarExameLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa-solid fa-eye"></i> Visualizar Exame</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row mb-2">
                                <div class="col-sm-9">
                                    <label class="col-form-label">Nome do Colaborador</label>
                                    <p id="v_nome" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-3">
                                    <label class="col-form-label">Data</label>
                                    <p id="v_data" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Tipo</label>
                                    <p id="v_tipo" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-sm-3">
                                    <label class="col-form-label">Vencimento</label>
                                    <p id="v_vencimento" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-5">
                                    <label class="col-form-label">Título do Exame</label>
                                    <p id="v_dsExame" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Nome da Clínica</label>
                                    <p id="v_nmClinica" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-8">
                                    <label class="col-form-label">Médico</label>
                                    <p id="v_nmMedico" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Status</label>
                                    <p id="v_status" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Descrição</label>
                                    <p id="v_obs" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Arquivo</label>
                                    <div class="border p-2 bg-white rounded">
                                        <i class="fa-solid fa-file-pdf text-danger"></i> <span id="v_nomeArquivo"></span>
                                        <a href="#" id="v_linkArquivo" target="_blank" class="btn btn-sm btn-outline-primary float-end">
                                            <i class="fa-solid fa-download"></i> Baixar
                                        </a>

                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                        <i class="fa fa-close"></i> Fechar
                                    </button>
                                </div>
                                <span id="divQuando" class="mt-2 ms-2" style="font-size: 12px; font-weight: 200"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The Modal ADD Tipo Documento -->
            <div class="modal fade" id="modalAddTipoDoc" tabindex="-1" aria-labelledby="addTipoDocLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background-color: LightBlue">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa-solid fa-eye"></i> Incluir tipo de Documento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Nome do Tipo de Documento</label>
                                    <input type="text" class="form-control" id="nmTipoDoc" name="nmTipoDoc" placeholder="nome do tipo de documento...">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Validade (meses)</label>
                                    <input type="text" class="form-control text-center" id="validade" name="validade" placeholder="meses" value="0">
                                </div>
                                <div class="col-sm-8 mt-3">
                                    <button type="button" class="btn btn-outline-primary rounded mt-4 w-100" id="btnSalvarTipoDoc" onclick='tipo_doc_commit()'>
                                        <i class="fa fa-save"></i> Salvar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id='msgAlertaTipoDoc'></div>
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
    <script src="js/rh_saude.js"></script>
</body>

</html>
<?PHP
//---------------- ROTINAS AUXILIARES

function seletor_tipo($_idTipo = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_exames_tipos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idExameTipo' name='idExameTipo'>";
    if ($_idTipo == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idTipo == $idExameTipo) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idExameTipo' $selected>$nmExame</option>";
    }
    $select .= "</select>";
    echo $select;
    //idExameTipo, nmExame
}

function seletor_tipo_doc( $id = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_docs_tipo";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<div class='input-group mb-3'>";
    $select .= "<select class='form-select fs-13' id='idTipoDoc' name='idTipoDoc'>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($id == $idTipoDoc) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idTipoDoc' $selected>$nome</option>";
    }
    $select .= "</select>";
    $select .= "<button class='btn btn-outline-primary' type='button' onclick='add_tipo_doc()'>
                    <i class='fa-solid fa-plus'></i>
                </button>";
    $select .= "</div>";
    echo $select;
    //idExameTipo, nmExame
}
