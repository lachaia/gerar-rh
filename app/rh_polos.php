<?php
//
// - rh_polos.php | Grid: Polos das Subsedes
// - (C) Chaia, 27/07/2026 Seg
//

$idModulo = 24; // Polos

session_start();

$idEmpresa = $_SESSION['idEmpresa'];

if ($_SESSION['idGrupo'] > 2 && $_SESSION['idGrupo'] != 9 && $_SESSION['idGrupo'] != 7) {
    header('Location: proibido.php');
}

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
} else {
    include_once "includes/conexao_gerar.php";
    include_once "includes/f_logs.php";
    f_log("CON", "Consulta grade de Polos da Gerar", "rh_polos", $idModulo, 0);
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tabela de Polos da Gerar" />
    <meta name="author" content="LAChaia" />
    <title>GERAR | Polos</title>

    <!-- =================================================== -->
    <!-- 1. ESTILOS (CSS)                                    -->
    <!-- =================================================== -->
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- jQuery UI CSS (Autocomplete) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Summernote CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">

    <!-- Seus estilos customizados (Sempre por último para poder sobrescrever os frameworks) -->
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/rh_polos.css" />

    <!-- =================================================== -->
    <!-- 2. SCRIPTS (JS) - ORDEM ESTRITA DE DEPENDÊNCIA       -->
    <!-- =================================================== -->
    <!-- 2.1 jQuery Base (DEVE SER O PRIMEIRO SCRIPT!) -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>

    <!-- 2.2 jQuery UI (Requer jQuery) -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- 2.3 Bootstrap 5 JS Bundle (Requerido para Modais e Dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 2.4 FontAwesome Icons -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- 2.5 DataTables JS (Requer jQuery) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- 2.6 Summernote JS (Requer jQuery e Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">

            <main>
                <div class="container-fluid px-4">

                    <div class="card mt-2 bg-dark text-white">
                        <h3 class="m-4">
                            <i class="fa-solid fa-building-flag"></i> Polos
                        </h3>
                    </div>
                    <div id='divAlertaCargo' class="text-center invisivel"></div>

                    <div class="card shadow-lg border-0 mb-4 mt-1">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Polos das SubSedes
                            </div>
                            <div class="float-end ms-2 me-2" id='seletores'>
                                <button class="btn btn-outline-primary btn-sm" onClick="f_incluir()"><i class="fa-solid fa-plus"></i> Incluir Polo</button>
                            </div>
                        </div>
                        <div class="card-body custom-dark">
                            <table id="example" class="table nowrap custom-table table-striped table-hover table-sm fb-8" style="width:100%">
                                <thead>
                                    <tr>
                                        <th><sup>0</sup>SubSede</th>
                                        <th><sup>1</sup>Polo</th>
                                        <th><sup>2</sup>Z:ID</th>
                                        <th><sup>3</sup>Telefone</th>
                                        <th><sup>4</sup>e-mail</th>
                                        <th><sup>5</sup>Cidade</th>
                                        <th><sup>6</sup>UF</th>
                                        <th><sup>7</sup>País</th>
                                        <th><sup>8</sup>Qtd Colb</th>
                                        <th><sup>9</sup>Criado em</th>
                                        <th class="text-center">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Modal de inclusão de nova Unidade -->
            <div class="modal fade" id="modalIncUnidade" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalIncUnidadeLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title" id="modalIncUnidadeLabel"><i class="fa-solid fa-building-columns me-2"></i>Incluir Nova Unidade</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="form-inc">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Identificador *</label>
                                        <input type="text" name="identificador" id="identificador" class="form-control bg-dark text-white border-secondary text-center" placeholder="Nome da Unidade" required onblur="maiusculas(this)">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">SubSede *</label>
                                        <?= subsedes($conn) ?>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Zum-ID</label>
                                        <input type="text" name="polo_id" id="polo_id" class="form-control bg-dark text-white border-secondary" placeholder="ID" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Responsável</label>
                                        <input type="text" name="responsavel" id="responsavel" class="form-control bg-dark text-white border-secondary" placeholder="Digite o nome" required>
                                    </div>

                                    <div class='col-md-2'>
                                        <label class="form-label">CEP</label>
                                        <input type="text" name="cep" id="cep" class="form-control bg-dark text-white border-secondary" placeholder="00000000" maxlength="8" onblur="busca_cep(this, 'form-inc')">
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">Endereço</label>
                                        <input type="text" id="endereco" name="endereco" class="form-control bg-dark text-white border-secondary" placeholder="logradouro...">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Número</label>
                                        <input type="text" id="numero" name="numero" class="form-control bg-dark text-white border-secondary" placeholder="nº...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Complemento</label>
                                        <input type="text" id="complemento" name="complemento" class="form-control bg-dark text-white border-secondary" placeholder="complemento">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bairro</label>
                                        <input type="text" id="bairro" name="bairro" class="form-control bg-dark text-white border-secondary" placeholder="bairro">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Cidade</label>
                                        <div class="input-group">
                                            <input type="text" id="busca_cidade" name='cidade_ds' class="form-control bg-dark text-white border-secondary" placeholder="Comece a digitar o nome da cidade...">

                                            <button class="btn btn-outline-secondary" type="button" onclick="f_incluir_cidade( this )" title="Cadastrar nova cidade">
                                                <i class="fas fa-plus"></i>
                                            </button>

                                            <input type="hidden" name="cidade_id" id="cidade_id">
                                        </div>
                                    </div>

                                    <div class="col-md-1">
                                        <label class="form-label">UF</label>
                                        <input type="text" name="uf" id="uf" class="form-control bg-dark text-white border-secondary" placeholder="--">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">País</label>
                                        <input type="text" name="pais" id="pais" class="form-control bg-dark text-white border-secondary" placeholder="País" value='Brasil'>
                                    </div>

                                    <div class="col-md-7">
                                        <label class="form-label">E-mail</label>
                                        <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary" placeholder="email@exemplo.com" onblur="minusculas(this)">
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" name="telefone" id="telefone" class="form-control bg-dark text-white border-secondary" placeholder="(00) 00000-0000">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Observações</label>
                                        <textarea name="obs" id="inc_obs_summernote" class='form-control bg-dark text-light border-secondary'></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" onclick="f_salvar_unidade()">Salvar Unidade</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de inclusão de nova CIDADE -->
            <div class="modal fade" id="modalNovaCidade" tabindex="-1" aria-labelledby="modalNovaCidadeLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-secondary shadow-lg custom-modal-light">
                        <div class="modal-header border-secondary p-2">
                            <h6 class="modal-title" id="modalNovaCidadeLabel"><i class="fas fa-globe-americas me-2"></i>Nova Cidade</h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="form-rapido-cidade">
                                <div class="mb-2">
                                    <label class="form-label small mb-0">Cidade</label>
                                    <input type="text" id="n_cidade_nome" class="form-control form-control-sm bg-dark text-white border-secondary">
                                </div>

                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label small mb-0">UF</label>
                                        <input type="text" id="n_cidade_uf" class="form-control form-control-sm bg-dark text-white border-secondary" maxlength="2" placeholder="EX: PR">
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label small mb-0">País</label>
                                        <input type="text" id="n_cidade_pais" list="lista-paises" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Brasil">
                                        <datalist id="lista-paises">
                                            <option value="Brasil">
                                            <option value="Argentina">
                                            <option value="Estados Unidos">
                                            <option value="Portugal">
                                            <option value="Paraguai">
                                            <option value="Uruguai">
                                        </datalist>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-secondary p-2">
                            <button type="button" class="btn btn-xs btn-outline-light" data-bs-dismiss="modal">Sair</button>
                            <button type="button" class="btn btn-xs btn-primary" onclick="f_salvar_cidade_rapido()">Cadastrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de ALTERAÇÃO de Unidade -->
            <div class="modal fade" id="modalAltUnidade" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalAltUnidadeLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title" id="modalAltUnidadeLabel"><i class="fa-solid fa-building-columns me-2"></i>Editar Unidade</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="form-alt">
                                <input type="hidden" id='id' name='id' value=''> <!-- id para edição -->
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Identificador *</label>
                                        <input type="text" name="identificador" id="identificador" class="form-control bg-dark text-white border-secondary text-center" placeholder="Nome da Unidade" required onblur="maiusculas(this)">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">SubSede *</label>
                                        <?= subsedes($conn) ?>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Zum-ID</label>
                                        <input type="text" name="polo_id" id="polo_id" class="form-control bg-dark text-white border-secondary" placeholder="ID" required>
                                    </div>                                    

                                    <div class="col-md-3">
                                        <label class="form-label">Responsável</label>
                                        <input type="text" name="responsavel" id="responsavel" class="form-control bg-dark text-white border-secondary" placeholder="Digite o nome">
                                    </div>

                                    <div class='col-md-2'>
                                        <label class="form-label">CEP</label>
                                        <input type="text" name="cep" id="cep" class="form-control bg-dark text-white border-secondary" placeholder="00000000" maxlength="8" onblur="busca_cep(this, 'form-alt')">
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">Endereço</label>
                                        <input type="text" id="endereco" name="endereco" class="form-control bg-dark text-white border-secondary" placeholder="logradouro...">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Número</label>
                                        <input type="text" id="numero" name="numero" class="form-control bg-dark text-white border-secondary" placeholder="nº...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Complemento</label>
                                        <input type="text" id="complemento" name="complemento" class="form-control bg-dark text-white border-secondary" placeholder="complemento">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bairro</label>
                                        <input type="text" id="bairro" name="bairro" class="form-control bg-dark text-white border-secondary" placeholder="bairro">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Cidade</label>
                                        <div class="input-group">
                                            <input type="text" id="busca_cidade" name='cidade_ds' class="form-control bg-dark text-white border-secondary" placeholder="Comece a digitar o nome da cidade...">

                                            <button class="btn btn-outline-secondary" type="button" onclick="f_incluir_cidade( this )" title="Cadastrar nova cidade">
                                                <i class="fas fa-plus"></i>
                                            </button>

                                            <input type="hidden" name="cidade_id" id="cidade_id" value='0'>
                                        </div>
                                    </div>

                                    <div class="col-md-1">
                                        <label class="form-label">UF</label>
                                        <input type="text" name="uf" id="uf" class="form-control bg-dark text-white border-secondary" placeholder="--">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">País</label>
                                        <input type="text" name="pais" id="pais" class="form-control bg-dark text-white border-secondary" placeholder="País" value='Brasil'>
                                    </div>

                                    <div class="col-md-7">
                                        <label class="form-label">E-mail</label>
                                        <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary" placeholder="email@exemplo.com" onblur="minusculas(this)">
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" name="telefone" id="telefone" class="form-control bg-dark text-white border-secondary" placeholder="(00) 00000-0000">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Observações</label>
                                        <textarea name="obs" id="alt_obs_summernote" class='form-control bg-dark text-light border-secondary'></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" onclick="f_salvar_alteracao()">Salvar Unidade</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de VISUALIZAÇÃO de Unidade -->
            <div class="modal fade" id="modalVisUnidade" tabindex="-1" aria-labelledby="modalVisUnidadeLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-dark text-white border-info">
                        <div class="modal-header border-info">
                            <h5 class="modal-title" id="modalVisUnidadeLabel"><i class="fas fa-id-card me-2"></i> Unidade: <spam class='text-info' id='v_identificador'></spam>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="text-info small fw-bold">Responsável</label>
                                    <div id="v_responsavel" class="p-2 border-bottom border-secondary text-uppercase"></div>
                                </div>
                                <div class="col-md-8">
                                    <label class="text-info small fw-bold">SubSede</label>
                                    <div id="v_subsede" class="p-2 border-bottom border-secondary text-uppercase"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-info small fw-bold">ID Unidade</label>
                                    <div id="v_id" class="p-2 border-bottom border-secondary text-center"></div>
                                </div>

                                <div class="col-md-7">
                                    <label class="text-info small fw-bold">E-MAIL</label>
                                    <div id="v_email" class="p-2 border-bottom border-secondary"></div>
                                </div>
                                <div class="col-md-5">
                                    <label class="text-info small fw-bold">TELEFONE</label>
                                    <div id="v_telefone" class="p-2 border-bottom border-secondary"></div>
                                </div>

                                <div class="col-md-12">
                                    <label class="text-info small fw-bold">LOCALIZAÇÃO</label>
                                    <div class="p-2 border border-secondary rounded bg-secondary bg-opacity-10">
                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                        <span id="v_localizacao"></span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="text-info small fw-bold">OBSERVAÇÕES</label>
                                    <div id="v_obs" class="p-3 border border-secondary rounded text-light" style="min-height: 100px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-info">
                            <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Fechar Visualização</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/rh_polos.js"></script>
    <script data-cfasync="false" src="js/scripts.js"></script>
</body>
</html>
<?php
//- ROTINAS AUXILIARES

function subsedes( $conn ){
    $sql = "SELECT subsede_id, identificador 
                FROM rh_subsedes 
                ORDER BY subsede_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo "<select class='form-select bg-secondary text-light' id='subsede_id' name='subsede_id' required>";
    echo "<option value='0'>Escolha</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        echo "<option value='$subsede_id'>$identificador</option>";
    }
    echo "</select>";
}
