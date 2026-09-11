<?PHP
//
// afastamentos.php | Módulo de AFASTAMENTOS do Colaborador para o Portal do Colaborador
// (C)haia, 16/10/2025

session_start();

$idModulo = 13; // Colaborador

$modulo = "Afastamentos";
include 'header.php';

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include "../includes/conexao_gerar.php";

$idColab = $_SESSION['idColab'];
$idPessoa = $_SESSION['idPessoa'];
$_nome = $_SESSION['nmUsuario'];
?>
<link rel="stylesheet" href="css/afastamentos.css" />
<main class="main">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"> <i class="fa-solid fa-file-medical"></i> Afastamentos</h2>
            <p style="color:var(--muted)">Envio de atestados, acompanhamento e histórico de afastamentos.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Afastamentos -->
    <div class="big-card">
        <div class="container vh-100 text-white mt-4">
            <div class="row justify-content-center">
                <div class="col-10">
                    <div id='divAlertaAfastamento' class="text-center invisivel"></div>
                    <div class="d-flex justify-content-center position-relative mb-3">
                        <h3 class="text-center w-100 m-0">
                            Lista de Atestados e Afastamentos
                        </h3>
                        <button id="btnUploadAtestado" class="btn position-absolute end-0" style="background:var(--accent);border:0;color:#fff;" onclick='f_afa_incluir()'>Enviar Atestado</button>
                    </div>

                    <table class="table table-striped table-hover table-bordered table-responsive w-100 table-dark" id="tabAfastamentos">
                        <thead>
                            <tr>
                                <th><sup>0</sup>ID</th>
                                <th width="400px"><sup>1</sup>Tipo de Afastamento</th>
                                <th><sup>2</sup>Data</th>
                                <th><sup>3</sup>Dias</th>
                                <th><sup>4</sup>Retorno</th>
                                <th><sup>5</sup>Status</th>
                                <th style="width: 110px"><sup></sup>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pesquisa = "SELECT A.*,  P.nome, T.descricao
                                                    FROM RH.rh_afastamentos A
                                                    INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                                                    INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                                                    INNER JOIN rh_afastamento_tipos T on T.id = A.idTipo
                                                    WHERE A.idColab = $idColab";
                            $stmt = $conn->prepare($pesquisa);
                            $stmt->execute();
                            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                extract($linha);
                                $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm me-1' onClick='f_afa_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
                                $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm me-1' onClick='f_afa_editar($id)'><i class='fa-solid fa-pen'></i></a>";
                                if ($status == 3) {
                                    $acoes .=  "<a href='#!' class='btn btn-outline-secondary btn-sm'><i class='fa-solid fa-trash-can'></i></a>";
                                } else {
                                    $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_afa_excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
                                }
                                //
                                $dsStatus = '';
                                if ($status == 'Gestor') {
                                    $dsStatus = "<span class='badge bg-warning text-dark px-2 py-1 w-100'><i class='fa-solid fa-hourglass-end'></i> Gestor</span>";
                                } elseif ($status == 'RH') {
                                    $dsStatus = "<span class='badge bg-warning text-dark px-2 py-1 w-100'><i class='fa-solid fa-hourglass-end'></i> RH</span>";
                                } elseif ($status == 'Rejeitado') {
                                    $dsStatus = "<span class='badge bg-danger text-white px-2 py-1 w-100'>Rejeitado</span>";
                                } elseif ($status == 'Aprovado') {
                                    $dsStatus = "<span class='badge bg-success text-white px-2 py-1 w-100'>Aprovado</span>";
                                } else {
                                    $dsStatus = "<span class='badge bg-secondary text-white px-2 py-1 w-100'>Desconhecido</span>";
                                }
                                //
                                echo "
                                <tr>
                                    <td class='text-center'>$id</td>
                                    <td>$descricao</td>
                                    <td class='text-center text-nowrap'>$data_inicio</td>
                                    <td class='text-center text-nowrap'>$dias_afastado</td>
                                    <td class='text-center text-nowrap'>$data_retorno</td>
                                    <td class='text-center text-nowrap'>$dsStatus</td>
                                    <td class='text-center text-nowrap'>$acoes</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <div class="alert alert-info border-0 py-2 px-3 mt-3" role="note" aria-live="polite">
                        <div class="d-flex align-items-start">
                            <div class="me-2">
                                <i class="fa-solid fa-notes-medical fa-lg text-primary" aria-hidden="true"></i>
                            </div>
                            <div>
                                <strong class="m-2">Observação:</strong>
                                <div class="small text-muted m-2" style="font-size: 16px;">
                                    <h6 class="text-primary">Orientações sobre o envio de atestados médicos e odontológicos:</h6>
                                    <ul class="m-2">
                                        <li>O colaborador deve encaminhar o atestado médico ou odontológico <strong>imediatamente após o atendimento</strong>, preferencialmente no mesmo dia ou, no máximo, <strong>até 48 horas após o início do afastamento</strong>.<br><br></li>
                                        <li>O documento deve estar <strong>legível</strong>, conter <strong>identificação do profissional emissor</strong> (nome e CRM/CRO), <strong>data de emissão</strong>, <strong>tempo de afastamento</strong> e, se possível, <strong>CID</strong> (opcional ao colaborador).<br><br></li>
                                        <li>O envio pode ser feito digitalizando o atestado (<em>foto ou PDF</em>) e anexando-o através deste sistema.<br><br></li>
                                        <li>Após o envio, o atestado será <strong>avaliado pelo Gestor e pelo RH</strong>, podendo ser <strong>aceito</strong>, <strong>devolvido</strong> ou <strong>indeferido</strong> caso não atenda aos requisitos legais.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- The Modal VISUALIZAR AFASTAMENTO -->
    <div class="modal fade" id="modalAfaVisualizar" tabindex="-1" aria-labelledby="visualizarCargoLabel" aria-hidden="true" data-bs-backdrop="static">
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
                            <p id="v_afa_nome" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                        <div class="col-sm-4">
                            <label for="v_afa_data" class="col-form-label">Data</label>
                            <p id="v_afa_data" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                        <div class="col-sm-4">
                            <label for="v_afa_qtd" class="col-form-label">Qtd Dias</label>
                            <p id="v_afa_qtd" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                        <div class="col-sm-4">
                            <label for="v_afa_retorno" class="col-form-label">Retorno</label>
                            <p id="v_afa_retorno" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label class="col-form-label">Tipo de Afastamento</label>
                            <p id="v_afa_tipo" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                        <div class="col-sm-8">
                            <label class="col-form-label">Emitido por:</label>
                            <p id="v_afa_emitido_por" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                        <div class="col-sm-4">
                            <label class="col-form-label">CID</label>
                            <p id="v_afa_cid" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label class="col-form-label">Arquivo enviado</label>
                            <p id="v_afa_arquivo" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" data-bs-dismiss="modal">
                                <i class="fa fa-close"></i> Fechar
                            </button>
                        </div>
                        <span id='v_afa_quando' class="mt-2 ms-2" style='font-size: 12px; font-weight: 200'></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- The Modal INCLUIR AFASTAMENTO -->
    <div class="modal fade" id="modalAfaIncluir" tabindex="-1" aria-labelledby="incluirLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-plus"></i> Incluir Atestado Médico</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->

                <form id="formIncluirAfastamento" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">

                        <div class="row mb-3">
                            <input type="hidden" id="afa_nome" name="afa_nome" value='<?= $_nome ?>'>
                            <input type="hidden" id='afa_idColab' name='afa_idColab' value='<?= $idColab ?>'>
                            <input type="hidden" id='afa_idPessoa' name='afa_idPessoa' value='<?= $idPessoa ?>'>
                            <div class="col-sm-4">
                                <label for="afa_data" class="col-form-label">Data</label>
                                <input type="date" class="form-control text-center" id="afa_data" name="afa_data" onBlur='verifica_data(this)'>
                            </div>
                            <div class="col-sm-4">
                                <label for="afa_qtd" class="col-form-label">Qtd Dias</label>
                                <input type="number" class="form-control text-center" id="afa_qtd" name="afa_qtd" placeholder="Qtd" onBlur='calc_dias("incluir")'>
                            </div>
                            <div class="col-sm-4">
                                <label for="afa_dtRetorno" class="col-form-label">Retorno</label>
                                <input type="date" class="form-control text-center" id="afa_dtRetorno" name="afa_dtRetorno" readonly onfocus='desloca("#formIncluirAfastamento #idTipo")' style='background-color: #d99d9dff'>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="tipo_afastamento" class="col-form-label">Tipo Afastamento</label>
                                <?= seletor_tipo_afastamento() ?> <!-- deve conter: <select id="tipo_afastamento" ...> -->
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-8">
                                <label for="_tipo" class="col-form-label">Emitido por:</label>
                                <input type="text" class="form-control text-center" id="afa_emitidoPor" name="afa_emitidoPor">
                            </div>
                            <div class="col-sm-4">
                                <label for="afa_cid" class="col-form-label">CID:</label>
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
                            <div class="btn-group" id='afa_botoes_incluir'>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Cancelar
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" onclick='f_limpar()'>
                                    <i class="fa-solid fa-recycle"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="btnSalvar" onclick='f_afa_incluir_commit()'>
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                            </div>
                            <div id='afa_msgAlertaIncluir' class="text-center"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- The Modal EDITAR AFASTAMENTO -->
    <div class="modal fade" id="modalAfaEditar" tabindex="-1" aria-labelledby="editarCargoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-regular fa-pen-to-square"></i> Alterar Registro de Afastamento</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->

                <form id="formEditarAfastamento" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="e_afa_id" name="e_id">
                    <div class="modal-body">

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="e_nome" class="col-sm-3 col-form-label">Colaborador</label>
                                <input type="hidden" id="e_afa_nome" name="e_nome" value='<?= $_nome ?>'>
                                <input type="hidden" id='e_afa_idColab' name='e_idColab' value='<?= $idColab ?>'>
                                <input type="hidden" id='e_afa_idPessoa' name='e_idPessoa' value='<?= $idPessoa ?>'>
                            </div>
                            <div class="col-sm-4">
                                <label for="e_afa_data" class="col-form-label">Data</label>
                                <input type="date" class="form-control text-center" id="e_afa_data" name="e_data" onBlur='verifica_data(this)'>
                            </div>
                            <div class="col-sm-4">
                                <label for="e_afa_qtd" class="col-form-label">Qtd Dias</label>
                                <input type="number" class="form-control text-center" id="e_afa_qtd" name="e_qtd" placeholder="Qtd" onBlur='calc_dias("editar")'>
                            </div>
                            <div class="col-sm-4">
                                <label for="e_afa_dtRetorno" class="col-form-label">Retorno</label>
                                <input type="date" class="form-control text-center" id="e_afa_dtRetorno" name="e_dtRetorno" readonly onfocus='desloca("#formEditarAfastamento #idTipo")' style='background-color: #d99d9dff'>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="tipo_afastamento" class="col-form-label">Tipo Afastamento</label>
                                <?= seletor_tipo_afastamento() ?> <!-- deve conter: <select id="tipo_afastamento" ...> -->
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-8">
                                <label for="e_afa_emitidoPor" class="col-form-label">Emitido por:</label>
                                <input type="text" class="form-control text-center" id="e_afa_emitidoPor" name="e_emitidoPor">
                            </div>
                            <div class="col-sm-4">
                                <label for="e_afa_cid" class="col-form-label">CID:</label>
                                <input type="text" class="form-control text-center" id="e_afa_cid" name="e_cid">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="e_afa_arquivo" class="col-form-label">Arquivo para substituir o anterior</label>
                                <input type="file" class="form-control text-center" id="e_afa_arquivo" name="e_arquivo">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="btn-group" id='afa_botoes_editar'>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Cancelar
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded m-1" id="e_btnReset" onclick='f_afa_reset()'>
                                    <i class="fa-solid fa-recycle"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="e_btnSalvar" onclick='f_afa_editar_commit()'>
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                            </div>
                            <div id='msgAlertaEditarAfastamento' class="text-center"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</main>
<script src="js/afastamentos.js"></script>
<?php

include 'footer.php';

function seletor_tipo_afastamento($_idTipo = 0)
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
