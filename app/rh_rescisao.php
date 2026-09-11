<?php
//
//- rh_rescisao.php | Módulo das Rescisões
// (C)haia, 24/04/2025

session_start();


$idModulo = 9; // Rescisão | <i class="fa-solid fa-users-slash"></i>

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
    <link href="css/rh_rescisao.css" rel="stylesheet" />

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
                            <i class="fa-solid fa-users-slash"></i> RESCISÕES
                        </h3>
                    </div>
                    <div id='msgAlertaRescisao' class="text-center"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Rescisões de Contrato
                            </div>
                            <div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a></div>
                        </div>
                        <div class="card-body">
                            <table id="example" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>ID</th>
                                        <th><sup>1</sup>Colaborador</th>
                                        <th><sup>2</sup>Tipo</th>
                                        <th><sup>3</sup>Dt Aviso</th>
                                        <th><sup>4</sup>Dt Desligamento</th>
                                        <th><sup>5</sup>Status</th>
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

            <!-- Modal INCLUIR RESCISÃO -->
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incluirRescisaoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content" style="background-color: gainsboro">

                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8'>
                            <h5 class="modal-title">
                                <i class="fa-solid fa-user-slash"></i> Incluir Rescisão
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Formulário -->
                        <form id="formIncluir">
                            <div class="modal-body">

                                <!-- Dados principais -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nmPessoa" class="form-label">Colaborador</label>
                                        <input type='text' class='form-control' id='nmPessoa' name='nmPessoa' placeholder="comece a digitar...">
                                        <input type='hidden' id='idColab' name='idColab'>
                                        <input type='hidden' id='idPessoa' name='idPessoa'>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="idRescisaoTipo" class="form-label">Tipo de Rescisão</label>
                                        <?= seletor_tipo(); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_tipoAviso" class="form-label">Tipo de Aviso</label>
                                        <select class="form-select" id="_tipoAviso" name="_tipoAviso">
                                            <option value="">Selecione...</option>
                                            <option value="Trabalhado">Trabalhado</option>
                                            <option value="Indenizado">Indenizado</option>
                                            <option value="Dispensado">Dispensado</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="_dtAviso" class="form-label">Data do Aviso</label>
                                        <input type="date" class="form-control" id="_dtAviso" name="_dtAviso">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_dtDesligamento" class="form-label">Data do Desligamento</label>
                                        <input type="date" class="form-control" id="_dtDesligamento" name="_dtDesligamento" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_status" class="form-label">Status</label>
                                        <select class="form-select" id="_status" name="_status">
                                            <option value="Pendente">Pendente</option>
                                            <option value="Finalizada">Finalizada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_saldoSalario" class="form-label">Saldo Salário</label>
                                        <input type="number" step="0.01" class="form-control" id="_saldoSalario" name="_saldoSalario" onchange="f_calcularTotal('formIncluir')" required>
                                    </div>
                                </div>

                                <!-- Valores da rescisão -->
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label for="_feriasVencidas" class="form-label">Férias Vencidas</label>
                                        <input type="number" step="0.01" class="form-control" id="_feriasVencidas" name="_feriasVencidas" onchange="f_calcularTotal('formIncluir')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_feriasProporcionais" class="form-label">Férias Proporcionais</label>
                                        <input type="number" step="0.01" class="form-control" id="_feriasProporcionais" name="_feriasProporcionais" onchange="f_calcularTotal('formIncluir')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_decimoTerceiro" class="form-label">13º Salário</label>
                                        <input type="number" step="0.01" class="form-control" id="_decimoTerceiro" name="_decimoTerceiro" onchange="f_calcularTotal('formIncluir')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_multaFgts" class="form-label">Multa FGTS</label>
                                        <input type="number" step="0.01" class="form-control" id="_multaFgts" name="_multaFgts" onchange="f_calcularTotal('formIncluir')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_descontos" class="form-label">Descontos</label>
                                        <input type="number" step="0.01" class="form-control" id="_descontos" name="_descontos" onchange="f_calcularTotal('formIncluir')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_totalLiquido" class="form-label">Total Líquido</label>
                                        <input type="number" step="0.01" class="form-control fw-bold" id="_totalLiquido" name="_totalLiquido" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">

                                    <div class="col-md-12">
                                        <label for="_motivo" class="form-label">Motivo Detalhado</label>
                                        <textarea class="form-control" id="_motivo" name="_motivo" rows="2" placeholder="Descreva o motivo da rescisão (opcional)"></textarea>
                                    </div>
                                </div>

                                <!-- Botões -->
                                <div class="row">
                                    <div class="btn-group d-flex justify-content-end" id="botoes_incluir">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Cancelar
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_limparRescisao()'>
                                            <i class="fa-solid fa-recycle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick='f_incluir_commit()'>
                                            <i class="fa fa-save"></i> Salvar
                                        </button>
                                    </div>
                                    <div id="msgAlertaIncluir" class="text-center mt-2"></div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- The Modal EDITAR RESCISÃO -->
            <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarRescisaoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content" style="background-color: gainsboro">

                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8'>
                            <h5 class="modal-title">
                                <i class="fa-solid fa-user-slash"></i> Editar Rescisão
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Formulário -->
                        <form id="formEditar">
                            <div class="modal-body">

                                <!-- Dados principais -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nmPessoa" class="form-label">Colaborador</label>
                                        <input type='text' class='form-control' id='nmPessoa' name='nmPessoa' placeholder="comece a digitar...">
                                        <input type='hidden' id='idColab' name='idColab'>
                                        <input type='hidden' id='idPessoa' name='idPessoa'>
                                        <input type='hidden' id='idRescisao' name='idRescisao'>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="idRescisaoTipo" class="form-label">Tipo de Rescisão</label>
                                        <?= seletor_tipo(); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_tipoAviso" class="form-label">Tipo de Aviso</label>
                                        <select class="form-select" id="_tipoAviso" name="_tipoAviso">
                                            <option value="">Selecione...</option>
                                            <option value="Trabalhado">Trabalhado</option>
                                            <option value="Indenizado">Indenizado</option>
                                            <option value="Dispensado">Dispensado</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="_dtAviso" class="form-label">Data do Aviso</label>
                                        <input type="date" class="form-control text-center" id="_dtAviso" name="_dtAviso">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_dtDesligamento" class="form-label">Data do Desligamento</label>
                                        <input type="date" class="form-control text-center" id="_dtDesligamento" name="_dtDesligamento" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_status" class="form-label">Status</label>
                                        <select class="form-select" id="_status" name="_status">
                                            <option value="Pendente">Pendente</option>
                                            <option value="Finalizada">Finalizada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="_saldoSalario" class="form-label">Saldo Salário</label>
                                        <input type="number" step="0.01" class="form-control text-center" id="_saldoSalario" name="_saldoSalario" onchange="f_calcularTotal('formEditar')" required>
                                    </div>
                                </div>

                                <!-- Valores da rescisão -->
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label for="_feriasVencidas" class="form-label">Férias Vencidas</label>
                                        <input type="number" step="0.01" class="form-control text-center" id="_feriasVencidas" name="_feriasVencidas" onchange="f_calcularTotal('formEditar')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_feriasProporcionais" class="form-label">Férias Proporcionais</label>
                                        <input type="number" step="0.01" class="form-control text-center" id="_feriasProporcionais" name="_feriasProporcionais" onchange="f_calcularTotal('formEditar')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_decimoTerceiro" class="form-label">13º Salário</label>
                                        <input type="number" step="0.01" class="form-control text-center" id="_decimoTerceiro" name="_decimoTerceiro" onchange="f_calcularTotal('formEditar')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_multaFgts" class="form-label">Multa FGTS</label>
                                        <input type="number" step="0.01" class="form-control text-center" id="_multaFgts" name="_multaFgts" onchange="f_calcularTotal('formEditar')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_descontos" class="form-label">Descontos</label>
                                        <input type="number" step="0.01" class="form-control text-center" id="_descontos" name="_descontos" onchange="f_calcularTotal('formEditar')" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="_totalLiquido" class="form-label">Total Líquido</label>
                                        <input type="number" step="0.01" class="form-control fw-bold text-center" id="_totalLiquido" name="_totalLiquido" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">

                                    <div class="col-md-12">
                                        <label for="_motivo" class="form-label">Motivo Detalhado</label>
                                        <textarea class="form-control" id="_motivo" name="_motivo" rows="2" placeholder="Descreva o motivo da rescisão (opcional)"></textarea>
                                    </div>
                                </div>

                                <!-- Botões -->
                                <div class="row">
                                    <div class="btn-group d-flex justify-content-end" id="botoes_editar">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Cancelar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_reset()'>
                                            <i class="fa-solid fa-recycle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick='f_editar_commit()'>
                                            <i class="fa fa-save"></i> Salvar
                                        </button>
                                    </div>
                                    <div id="msgAlertaEditar" class="text-center mt-2"></div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- The Modal VISUALIZAR RESCISÃO -->
            <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarRescisaoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Header -->
                        <div class="modal-header" style='background-color: #C8C8C8'>
                            <h5 class="modal-title">
                                <i class="fa-solid fa-eye"></i> Visualizar Rescisão
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <!-- Dados principais -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Colaborador</label>
                                    <p id="v_nmPessoa" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Rescisão</label>
                                    <p id="v_idRescisaoTipo" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Aviso</label>
                                    <p id="v_tipoAviso" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Data do Aviso</label>
                                    <p id="v_dtAviso" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Data de Desligamento</label>
                                    <p id="v_dtDesligamento" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <p id="v_status" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Saldo Salário</label>
                                    <p id="v_saldoSalario" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>

                            <!-- Valores -->
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">Férias Vencidas</label>
                                    <p id="v_feriasVencidas" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Férias Proporcionais</label>
                                    <p id="v_feriasProporcionais" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">13º Salário</label>
                                    <p id="v_decimoTerceiro" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Multa FGTS</label>
                                    <p id="v_multaFgts" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Descontos</label>
                                    <p id="v_descontos" class="form-control-plaintext visCampo"></p>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Total Líquido</label>
                                    <p id="v_totalLiquido" class="form-control-plaintext fw-bold visCampo"></p>
                                </div>
                            </div>

                            <!-- Motivo -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Motivo Detalhado</label>
                                    <div id="v_motivo" class="form-control-plaintext visCampo" style="white-space: pre-wrap;"></div>
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="row">
                                <div class="btn-group d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                        <i class="fa fa-close"></i> Fechar
                                    </button>
                                </div>
                                <span id="v_infoComplementar" class="mt-2 ms-2" style="font-size: 12px; font-weight: 200"></span>
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
    <script src="js/rh_rescisao.js"></script>
</body>

</html>
<?PHP
//---------------- ROTINAS AUXILIARES

function seletor_tipo($_idTipo = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_rescisao_tipos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idTipoRescisao' name='idTipoRescisao'>";
    if ($_idTipo == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idTipo == $idTipoRescisao) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idTipoRescisao' $selected>$descricao</option>";
    }
    $select .= "</select>";
    echo $select;
    //idExameTipo, nmExame
}
