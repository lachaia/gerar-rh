<?php
//
//- rh_termos.php | Grid Termos Gerais
// (C)haia, 30/07/2025

//use phpseclib3\Math\BigInteger\Engines\PHP;

session_start();

$idModulo = 18; // Termos Gerais

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
    <meta name="description" content="Termos Gerais do Colaborador" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_termos.css" rel="stylesheet" />

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
                            <i class="fa-solid fa-file-signature"></i> Termos Gerais
                        </h3>
                    </div>
                    <div id='divAlertaTermo' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Registros dos Termos aceites pelo colaborador
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
                                        <th><sup>3</sup>Data Termo</th>
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

            <!-- The Modal INCLUIR TERMO -->
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incluirLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header" style='background-color: #C8C8C8 '>
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
                                        <input type="text" class="form-control obrigatorio" id="_nome" name="_nome" placeholder="comece digitando o nome do colaborador..." required>
                                        <input type="hidden" id='_idColab' name='_idColab'>
                                        <input type="hidden" id='_idPessoa' name='_idPessoa'>
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

                        <form id="formEditar" method="POST" enctype="multipart/form-data">
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
                                <div class="col-sm-12">
                                    <label class="col-sm-3 col-form-label">Colaborador</label>
                                    <p id="v_nome" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-4">
                                    <label for="v_data" class="col-form-label">Data</label>
                                    <p id="v_data" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-8">
                                    <label class="col-form-label">Tipo de Termo</label>
                                    <p id="v_tipo_trm" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Arquivo enviado</label>
                                    <p id="v_arquivo_trm" class="form-control-plaintext visCampo text-center"></p>
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

            <!-- The Modal INCLUIR TERMO -->
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
                                        <label for="_nome" class="col-sm-3 col-form-label fw-bold text-primary">
                                            Novo tipo de Termo
                                        </label>
                                        <input type="text" class="form-control obrigatorio" id="_nome" name="_nome" placeholder="Informe o novo tipo" required>
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
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" id="btnSalvar" onclick="f_incluir_tipo_commit()">
                                            <i class="fa fa-save"></i> Salvar
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </form>

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
    <script src="js/rh_termos.js"></script>
</body>

</html>
<?PHP

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
    $html .= "<button type='button' class='btn btn-outline-secondary' onclick='incluir_novo_tipo()' title='Incluir novo tipo'><i class='fa-solid fa-plus'></i></button>";

    $html .= "</div>";

    echo $html;
}
