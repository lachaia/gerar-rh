<?PHP
//
// termos.php | Módulo de CURRÍCULO do Portal do Colaborador
// (C)haia, 16/10/2025

session_start();

$idModulo = 13; // Colaborador

$modulo = "Termos";
include 'header.php';

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include "../includes/conexao_gerar.php";

$idColab  = $_SESSION['idColab'  ];
$idPessoa = $_SESSION['idPessoa' ];
$_nome    = $_SESSION['nmUsuario'];
?>
<link rel="stylesheet" href="css/termos.css" />
<main class="main">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"> <i class="fa-solid fa-file-signature"></i> Termos</h2>
            <p style="color:var(--muted)">Área onde termos e políticas são listados para leitura e aceite.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Termos -->
    <div class="big-card">
        <div class="container vh-100 text-white mt-4">
            <div class="row justify-content-center">
                <div class="col-12">

                    <div class="d-flex justify-content-center position-relative mb-3">
                        <h3 class="text-center w-100 m-0">
                            Declarações, Termos e Políticas Corporativas
                        </h3>
                        <button id="btnIncluir" class="btn position-absolute end-0" style="background:var(--accent);border:0;color:#fff;" onclick='f_trm_incluir()'>Incluir</button>
                    </div>

                    <table class="table table-striped table-hover table-responsive w-100 nowrap table-dark" id="tabTermos">
                        <thead>
                            <tr>
                                <th><sup>0</sup>ID</th>
                                <th><sup>1</sup>Termo</th>
                                <th class="text-center"><sup>2</sup>Data</th>
                                <th class="text-center"><sup></sup>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pesquisa = "SELECT A.*, P.nome, T.descricao
                                     FROM RH.rh_termos A
                                     INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                                     INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                                     INNER JOIN rh_termos_tipos T on T.id = A.idTipoTermo
                                     WHERE A.idColab = $idColab";
                            $stmt = $conn->prepare($pesquisa);
                            $stmt->execute();
                            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                extract($linha);
                                $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm me-1' onClick='f_trm_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
                                $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm me-1' onClick='f_trm_editar($id)'><i class='fa-solid fa-pen'></i></a>";
                                $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_trm_excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
                                echo "
                                <tr>
                                    <td class='text-center'>$id</td>
                                    <td>$descricao</td>
                                    <td class='text-center text-nowrap'>$data</td>
                                    <td class='text-center text-nowrap'>$acoes</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                </div>
            </div>

            <!-- Observação sobre assinatura de termos (colocar abaixo da tabela) -->
            <div class="alert alert-info border-0 py-2 px-3 mt-3" role="note" aria-live="polite">
                <div class="d-flex align-items-start">
                    <div class="me-2">
                        <i class="fa-solid fa-file-signature fa-lg text-primary" aria-hidden="true"></i>
                    </div>
                    <div>
                        <strong class="m-2">Observação:</strong>
                        <div class="small text-muted m-2" style='font-size: 16px;'>
                            Sempre que houver a publicação de um novo termo, política ou declaração corporativa, o colaborador deverá:
                            <ul class="m-2">
                                <li>ler o documento na íntegra;</li>
                                <li>efetuar a assinatura (eletrônica ou manual);</li>
                                <li>digitalizar o documento assinado (se assinou a mão) e anexá-lo à lista acima para registro do aceite.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- The Modal INCLUIR TERMO -->
    <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incluirLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-plus"></i> Incluir Documento com Aceite Assinado</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->

                <form id="formIncluir" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="_nome" class="col-sm-3 col-form-label">Colaborador</label>
                                <input type="text" class="form-control text-center" id="_nome" name="_nome" readonly value='<?= $_nome ?>' style='background-color: #9bb9bece; color: white; font-weight: 700;'>
                                <input type="hidden" id='_idColab' name='_idColab' value='<?= $idColab ?>'>
                                <input type="hidden" id='_idPessoa' name='_idPessoa' value='<?= $idPessoa ?>'>
                            </div>
                            <div class="col-sm-4">
                                <label for="_data" class="col-form-label">Data</label>
                                <input type="date" class="form-control text-center obrigatorio" id="_data" name="_data" onBlur='verifica_data(this)'>
                            </div>
                            <div class="col-sm-8">
                                <label for="tipo_termo" class="col-form-label">Tipo do Termo</label>
                                <?= seletor_tipo("#formIncluir") ?> <!-- deve conter: <select id="tipo_afastamento" ...> -->
                                <input type="hidden" id='dsTipo' name='dsTipo' value=''>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="_tipo" class="col-form-label">Arquivo para upload</label>
                                <input type="file" class="form-control text-center obrigatorio" id="_arquivo" name="_arquivo">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="btn-group" id='botoes_incluir'>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Cancelar
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1">
                                    <i class="fa-solid fa-recycle"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="btnSalvar" onclick='f_trm_incluir_commit()'>
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

    <!-- The Modal EDITAR TERMO -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarCargoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-regular fa-pen-to-square"></i> Alterar Cargo</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->

                <form id="formEditarTRM" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="e_id" name="e_id">
                    <div class="modal-body">

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="e_nome" class="col-sm-3 col-form-label">Colaborador</label>
                                <input type="text" class="form-control text-center" id="e_nome" name="e_nome" readonly style="background-color: #BEBEBE; color: white; font-weight: 700;">
                                <input type="hidden" id='e_idColab' name='e_idColab'>
                                <input type="hidden" id='e_idPessoa' name='e_idPessoa'>
                            </div>
                            <div class="col-sm-4">
                                <label for="e_data" class="col-form-label">Data</label>
                                <input type="date" class="form-control text-center" id="e_data" name="e_data" onBlur='verifica_data(this)'>
                            </div>
                            <div class="col-sm-8">
                                <label for="idTipo" class="col-form-label">Tipo do Termo</label>
                                <?= seletor_tipo("#formEditar") ?> <!-- deve conter: <select id="tipo_afastamento" ...> -->
                                <input type="hidden" id='dsTipo' name='dsTipo' value=''>
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
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded m-1" id="e_btnReset">
                                    <i class="fa-solid fa-recycle"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="e_btnSalvar" onclick='f_trm_editar_commit()'>
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

    <!-- The Modal VISUALIZAR TERMO -->
    <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-labelledby="visualizarCargoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-eye"></i> Visualizar Registro do Termo</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-sm-8">
                            <label class="col-sm-3 col-form-label">Colaborador</label>
                            <p id="v_nome" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                        <div class="col-sm-4">
                            <label for="v_data" class="col-form-label">Data</label>
                            <p id="v_data" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                        <div class="col-sm-12">
                            <label class="col-form-label">Tipo de Termo</label>
                            <p id="v_tipo_trm" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label class="col-form-label">Arquivo enviado</label>
                            <p id="v_arquivo_trm" class="form-control-plaintext visCampo text-center" style='background-color: #f9f9f9'></p>
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

    <!-- The Modal INCLUIR NOVO TIPO DE TERMO -->
    <div class="modal fade" id="modalIncluirTipo" tabindex="-1" aria-labelledby="incluirLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: #E6F0FA; border: 3px solid #5A9BD5; border-radius: 8px;">

                <!-- Modal Header -->
                <div class="modal-header" style="background-color: #5A9BD5; color: white; border-bottom: 2px solid #4178A9;">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-plus"></i> Incluir novo Tipo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <form id="formIncluirTipo">
                    <div class="modal-body">

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label for="_nome_tipo" class="col-sm-3 col-form-label fw-bold text-primary">
                                    Novo tipo de Termo
                                </label>
                                <input type="text" class="form-control obrigatorio" id="_nome_tipo" name="_nome_tipo" placeholder="Informe o novo tipo" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="btn-group" id="botoes_incluir">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Cancelar
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1">
                                    <i class="fa-solid fa-recycle"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" id="btnSalvar" onclick="f_trm_incluir_tipo_commit()">
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

</main>
<script src="js/termos.js"></script>
<?php include 'footer.php'; 

function seletor_tipo($formulario)
{
    global $conn;
    $sql = "SELECT * FROM rh_termos_tipos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $html = "<div class='input-group input-group-sm'>";

    // select
    $html .= "<select class='form-select fs-13 obrigatorio' id='idTipo' name='idTipo' onchange=\"selecionou_tipo('$formulario')\">";
    $html .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $html .= "<option value='$id'>$descricao</option>";
    }
    $html .= "</select>";

    // botão no grupo
    $html .= "<button type='button' class='btn btn-outline-secondary' onclick='f_trm_incluir_novo_tipo()' title='Incluir novo tipo'><i class='fa-solid fa-plus'></i></button>";

    $html .= "</div>";

    echo $html;
}