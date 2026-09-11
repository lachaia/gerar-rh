<?PHP
//
// equipe.php - Módulo GESTOR - Equipe do Gestor
// (C)haia, 18/09/2025
//

session_start();

$idModulo = 16; // Portal do Gestor

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
} else {
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    f_log("CON", "Consulta Equipe no Portal do Gestor", "rh_colaboradores", $idModulo, 0);
}

include "includes/header.php";
?>

<body>

    <main class="container">
        <div class="text-end"><a href="index.php" class="btn btn-success btn-sm"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a></div>
        <!-- Aqui COMEÇA o conteúdo da página -->
        <div class="row mt-2">
            <div class="col-md-12">
                <h4 class="text-white"><i class="fa-solid fa-user-injured"></i> A F A S T A M E N T O S</h4>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100" id='tblAtestados'>
                        <thead class="table-dark text-center">
                            <tr>
                                <th><sup>0</sup>ID</th>
                                <th><sup>1</sup>Colaborador</th>
                                <th><sup>2</sup>Tipo</th>
                                <th><sup>3</sup>Início</th>
                                <th><sup>4</sup>Retorno</th>
                                <th><sup>5</sup>Qtd</th>
                                <th><sup>6</sup>Status</th>
                                <th style='width: 110px'><sup></sup>Ações</th>
                            </tr>
                        </thead>
                        <tbody id='corpoTabela'>
                            <!-- Conteúdo será carregado via AJAX -->
                        </tbody>
                    </table>
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


    </main>

    <script src="js/index.js"></script>
    <script src="js/atestados.js"></script>

</body>

</html>