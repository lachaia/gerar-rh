<?php
//
//- index.php | CIPA
// (C)haia, 29/04/2025

session_start();

$idModulo = 11; // CIPA

if (!isset($_SESSION['idLogin'])) {
    header('location: ../logout.php');
    exit();
} else {
    include_once "../includes/conexao_gerar.php";
    //include_once "../includes/f_logs.php";
    //
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
}

//
// VERIFICAÇÃO DA SESSÃO
//
if (!isset($_SESSION['idSubSede']) || empty($_SESSION['idSubSede'])) {
    echo "<script>alert('SubSede não definida!'); location.href='../logout.php';</script>";
    exit();
} else {
    $idSubSede = $_SESSION['idSubSede'];
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="CIPA" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="index.css" rel="stylesheet" />

    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- JS: jQuery sempre antes do jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- Bootstrap + DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Summernote -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <style>
        body {
            background-image: url('../imagens/abstrato_tzuru_fundo.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: rgba(0, 0, 0, 0.7);
            /* camada escura */
            background-blend-mode: darken;
            /* mistura a cor com a imagem */
        }
    </style>
</head>


<body class="d-flex flex-column min-vh-100">
    <!-- 
                    Aqui COMEÇA o conteúdo da página 
    -->
    <main class="flex-grow-1">
        <div class="container">
            <div id='divCabecalho' class="card mt-4 bg-dark text-white d-flex flex-row justify-content-between align-items-center px-3 w-100">

                <!-- MENU SUPERIOR -->
                <div class="d-flex align-items-center">
                    <h3 class="m-3 text-nowrap">
                        <i class="fa-solid fa-helmet-safety text-success"></i> CIPA
                    </h3>

                    <div class="ms-4 menu">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#menuMembros"><i class="fa-solid fa-users me-1"></i> Membros</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#menuAtendimentos">Atendimentos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#menuReunioes">Reuniões</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#menuAcoes">Ações</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#menuDocs">Documentos</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="m-3 d-flex align-items-center">
                    <div class="dropdown me-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="idFotoPerfil">
                                <img src="<?php echo '../fotos/' . $_SESSION['perfil'] ?>"
                                    alt="Foto"
                                    class="rounded-circle border border-secondary"
                                    width="35" height="35"
                                    style="object-fit: cover;">
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="config.php" target='_blank'><i class="fa-solid fa-gear me-2"></i>Configurações</a></li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalAltSenha">
                                    <i class="fa-solid fa-key me-2"></i>Alterar Senha
                                </a>
                            </li>
                            <?PHP
                            if($_SESSION['idGrupo'] == 9){
                            ?>
                            <li>
                                <a class="dropdown-item" href="#" onclick='abrirTabUsuarios()'>
                                    <i class="fa-solid fa-users me-2"></i>Usuários
                                </a>
                            </li>
                            <?PHP
                            }
                            ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Sair</a></li>
                        </ul>
                    </div>

                    <a href="../logout.php" class="text-white opacity-75 hover-opacity-100" title="Sair do Sistema">
                        <i class="fas fa-sign-out-alt fa-lg"></i>
                    </a>
                </div>
            </div>

            <div class="card mb-4 mt-3" id='cartoes'>

                <div class="card-body">

                    <div class="tab-content">

                        <!-- TAB: MEMBROS da CIPA -->
                        <div class="tab-pane container active" id="menuMembros">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <div class='clearfix'>
                                            <div class="float-start h5">
                                                Membros da CIPA
                                            </div>
                                            <div class="float-end">
                                                <button class="btn btn-sm btn-outline-primary m-2" onclick='btn_incluir_membro()'><i class="fa-solid fa-user-plus me-1"></i> Incluir Cipeiro</button>
                                            </div>
                                        </div>
                                        <table id="tabMembros" class="table table-striped table-bordered nowrap w-100" style="width:100%">
                                            <thead class='w-100'>
                                                <tr>
                                                    <th>SubSede</th>
                                                    <th>Nome</th>
                                                    <th>Perfil</th>
                                                    <th>Cargo</th>
                                                    <th>Início</th>
                                                    <th>Fim</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Os dados serão preenchidos via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id='msgAlertaMembros' class="h5 text-center"></div>
                        </div>

                        <!-- TAB: ATENDIMENTOS -->
                        <div class="tab-pane container fade" id="menuAtendimentos">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive" id='divTabAtendimentos'>
                                        <div class="clearfix">
                                            <div class="float-start h5">
                                                Atendimentos
                                            </div>
                                            <div class="float-end">
                                                <div class="d-flex">
                                                    <!-- <button class="btn btn-sm btn-outline-primary m-2" onclick='btnRelAtendimento("ir")'>Estatísticas</button> -->
                                                    <button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncAtendimento()'>Incluir</button>
                                                </div>
                                            </div>
                                        </div>
                                        <table id="tabAtendimentos" class="table table-striped table-bordered nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>SubSede</th>
                                                    <th>Data</th>
                                                    <th>Brigadista</th>
                                                    <th>Paciente</th>
                                                    <th>Ocorrência</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Os dados serão preenchidos via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-none" id='divGraficoAtendimento'>
                                        <div class="row">
                                            <div class="clearfix">
                                                <div class="float-end">
                                                    <button class="btn btn-sm btn-outline-primary m-2" onclick='btnRelAtendimento("voltar")'>voltar</button>
                                                </div>
                                            </div>
                                            <div class="card col-sm-6 text-center">
                                                <table class="table table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <td>Tipo</td>
                                                            <td>qtd</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $labels = [];
                                                        $valores = [];

                                                        $sql = "SELECT T.descricao, COUNT(T.id) AS qtd
                                                                FROM rh_cipa_atendimentos A
                                                                INNER JOIN rh_cipa_tipo_ocorrencia T ON T.id = A.tipo_ocorrencia
                                                                GROUP BY T.descricao
                                                                ORDER BY qtd DESC";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->execute();
                                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                            extract($linha);
                                                            echo "<tr>
                                                                    <td>$descricao</td>
                                                                    <td>$qtd</td>
                                                                </tr>";
                                                            // guarda para o gráfico
                                                            $labels[] = $descricao;
                                                            $valores[] = $qtd;
                                                        }
                                                        //
                                                        // Calculo da média mensal
                                                        //
                                                        $sql = "SELECT 
                                                                COUNT(A.id) AS total_ocorrencias,
                                                                COUNT(A.id) / COUNT(DISTINCT DATE_FORMAT(A.data_ocorrencia, '%Y-%m')) AS media_mensal
                                                                FROM rh_cipa_atendimentos A;";
                                                        $res = $conn->prepare($sql);
                                                        $res->execute();
                                                        $linha = $res->fetch(PDO::FETCH_ASSOC);
                                                        extract($linha);
                                                        $media_mensal = round($media_mensal);
                                                        //
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <div class="text-center h5 mt-2">
                                                    Media Mensal: <?= $media_mensal ?>
                                                </div>
                                            </div>
                                            <div class="card col-sm-6 d-flex align-items-center justify-content-center">
                                                <div style="width: 450px; height: 450px;">
                                                    <canvas id="graficoPizza"></canvas>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id='msgAlertaAtende' class="h5 text-center"></div>
                        </div>

                        <!-- TAB: REUNIÕES -->
                        <div class="tab-pane container fade" id="menuReunioes">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <div class="clearfix">
                                            <div class="float-start h5">Reuniões da CIPA</div>
                                            <div class="float-end">
                                                <button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncReuniao()'><i class="fa-solid fa-plus"></i> Incluir Reunião</button>
                                            </div>
                                        </div>
                                        <table id="tabReunioes" class="table table-striped table-bordered nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>SubSede</th>
                                                    <th>Data</th>
                                                    <th>Assunto</th>
                                                    <th>Participantes</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Os dados serão preenchidos via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id='msgAlertaReunioes' class="h5 text-center"></div>
                        </div>

                        <!-- TAB: EVENTOS/AÇÕES -->
                        <div class="tab-pane container fade" id="menuAcoes">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <div class="clearfix">
                                            <div class="float-start h5">Ações da CIPA</div>
                                            <div class="float-end">
                                                <button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncAcao()'><i class="fa-solid fa-plus"></i> Incluir Ação</button>
                                            </div>    
                                        </div>
                                        
                                        <table id="tabAcoes" class="table table-striped table-bordered nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>SubSede</th>
                                                    <th>Data</th>
                                                    <th>Assunto</th>
                                                    <th>Participantes</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Os dados serão preenchidos via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id='msgAlertaAcoes' class="h5 text-center"></div>
                        </div>

                        <!-- TAB: DOCUMENTOS -->
                        <div class="tab-pane container fade" id="menuDocs">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="clearfix">
                                        <div class="float-start h5">Documentos da CIPA</div>
                                        <div class="float-end">
                                            <button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncDoc()'><i class="fa-solid fa-plus"></i>Incluir Documento</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        
                                        <table id="tabDocs" class="table table-striped table-bordered nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Tipo</th>
                                                    <th>Assunto</th>
                                                    <th>Ext</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Os dados serão preenchidos via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id='msgAlertaDocs' class="h5 text-center"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TAB: USUÁRIOS -->
            <div class="card mb-4 mt-3 d-none" id='cartaoUsuarios'>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <div class="clearfix">
                                    <div class='float-start h5 mb-2'><i class="fa-solid fa-users"></i> Usuários do Sistema</div>
                                    <div class="float-end"><button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncUsuario()'>Incluir Usuário</button></div>
                                </div>
                                <table id="tabUsuarios" class="table table-striped table-bordered nowrap w-100" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>SubSede</th>
                                            <th>Login</th>
                                            <th>Nome</th>
                                            <th>Cargo</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Os dados serão preenchidos via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id='msgAlertaUsuarios' class="h5 text-center"></div>
                </div>
            </div>

        </div>
    </main>

    <!-- MODAL: ALTERAR SENHA -->
    <div class="modal fade" id="modalAltSenha" tabindex="-1" aria-labelledby="labelAltSenha" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="labelAltSenha"><i class="fa-solid fa-key me-2"></i>Alterar Senha</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAltSenha">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nova Senha</label>
                            <div class="input-group">
                                <input type="password" id="nova_senha" name="nova_senha" class="form-control bg-secondary text-white border-0" required>
                                <button class="btn btn-outline-light toggle-password" type="button">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text text-light opacity-50">Mínimo de 6 caracteres.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <input type="password" name="confirma_senha" id='confirma_senha' class="form-control bg-secondary text-white border-0" required>
                                <button class="btn btn-outline-light toggle-password" type="button">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary" id='botoesAltSenha'>
                        <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick='btn_salvar_senha()'>Salvar Alterações</button>
                    </div>
                </form>
                <div id='msgAlertaSenha' class='text-center h5'></div>
            </div>
        </div>
    </div>

    <!-- Modal: MEMBRO | INCLUIR novo membro DA CIPA -->
    <div class="modal fade" id="modalIncluirMembro" tabindex="-1" aria-labelledby="incMembroModalLabel" data-bs-backdrop="static">

        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="incMembroModalLabel">
                        <h5>Inclusão de Membro de CIPA</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->

                <div class="modal-body">
                    <form id="formIncMembro" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <!-- COLUNA PRINCIPAL (Formulário) -->
                            <div class="col-md-7">

                                <div class="mb-2">
                                    <label for="nmPessoa" class="form-label cab">Nome da Pessoa</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control obrigatorio" id="nmPessoa" name="nmPessoa" placeholder="comece a digitar o nome" required>
                                        <button class="btn btn-outline-primary" onclick="btn_inclui_pessoa()"><i class="fa-solid fa-person-circle-plus"></i></button>
                                    </div>
                                    <input type="hidden" id="idPessoa" name="idPessoa" value="0">
                                </div>

                                <div class="mb-2">
                                    <label for="idSeletorSubsedes" class="form-label cab">SubSede</label>
                                    <span id="idSeletorSubsedes">
                                        <?= seletor_subsedes($idSubSede ?? 0) ?>
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <label for="idSeletorCargo" class="form-label cab">Cargo</label>
                                    <span id="idSeletorCargo">
                                        <?= seletor_cargos($idCargo ?? 0) ?>
                                    </span>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-sm-6">
                                        <label for="dtInicio" class="form-label cab">Início na CIPA</label>
                                        <input type="date" id="dtInicio" name="dtInicio" class="form-control text-center obrigatorio" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="dtFinal" class="form-label cab">Final</label>
                                        <input type="date" id="dtFinal" name="dtFinal" class="form-control text-center">
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label for="foto" class="form-label cab">Foto do Perfil</label>
                                    <input type="file" id="foto" name="foto" class="form-control" accept="image/*" onchange="previewFoto(event)">
                                </div>

                            </div>

                            <!-- COLUNA DO PREVIEW DA FOTO -->
                            <div class="col-md-5 text-center d-flex align-items-center justify-content-center">
                                <img id="preview" src="../fotos/perfil.png" class="img-thumbnail" style="max-height: 200px;">

                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="btn-group">
                                <div class="col-sm-2"></div>
                                <button type="button" class="btn btn-success btn-sm rounded m-1" onclick="btnIncSalvarMembro()"><i class="fa fa-upload"></i> Salvar</button>
                                <button type="reset" class="btn btn-secondary btn-sm rounded m-1" id="btnIncReset"><i class="fa fa-recycle"></i> Reset</button>
                                <button type="button" class="btn btn-danger btn-sm rounded m-1" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Fechar</button>
                            </div>
                        </div>
                    </form>

                    <div id="msgAlertMembro" class="text-center h5"></div>
                </div>



            </div>
        </div>
    </div>

    <!-- Modal: PESSOA | INCLUIR nova Pessoa -->
    <div class="modal fade" id="modalIncluirPessoa" tabindex="-1" aria-labelledby="incPessoaModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-grande">
            <div class="modal-content" style="background-color: gainsboro;">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="incPessoaModalLabel">
                        <h5>Inclusão de Registro de Pessoas</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <form method="POST" id="form-cad-pessoa">
                        <div class="row mb-3">
                            <label for="_nomePessoa" class="col-sm-3 col-form-label">Nome</label>
                            <div class="col-sm-9">
                                <input type="text" name="_nomePessoa" value="" class="form-control" id="_nomePessoa" placeholder="Nome">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="_nomeSocial" class="col-sm-3 col-form-label">Nome Social</label>
                            <div class="col-sm-9">
                                <input type="text" name="_nomeSocial" value="" class="form-control" id="_nomeSocial" placeholder="Nome Social da Pessoa...">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="_cpf" class="col-sm-3 col-form-label">CPF</label>
                            <div class="col-sm-9">
                                <input type="text" name="_cpf" class="form-control" id="_cpf" placeholder="CPF" value="">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="_email" class="col-sm-3 col-form-label">Sexo Biológico</label>
                            <div class="col-sm-9">
                                <select class='form-select' name="_sexo" id="_sexo">
                                    <option value="0">Selecione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Feminino</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="_email" class="col-sm-3 col-form-label">e-Mail</label>
                            <div class="col-sm-9">
                                <input type="email" name="_email" class="form-control" id="_email" placeholder="e-Mail">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="_celular" class="col-sm-3 col-form-label">Celular</label>
                            <div class="col-sm-9">
                                <input type="text" name="_celular" class="form-control" id="_celular" placeholder="Celular">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm rounded m-1" value="Cadastrar" onclick="btnIncSalvarPessoa()"><i class="fa fa-upload"></i> Salvar</button>
                                <button type="reset" class="btn btn-secondary btn-sm rounded m-1" id="btnResetIncPessoa"><i class="fa fa-recycle"></i> Reset</button>
                                <button type="button" class="btn btn-danger btn-sm rounded m-1" data-bs-dismiss="modal" value="Fechar" id="btnFecharIncPessoa"><i class="fa fa-close"></i> Fechar</button>
                            </div>
                        </div>
                        <div id="msgAlertIncPessoa" class="text-center"></div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal: MEMBRO | VISUALIZAR Membro DA CIPA -->
    <div class="modal fade" id="modalVerMembro" tabindex="-1" aria-labelledby="visualizarMembroLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-eye"></i> Visualizar Membro DA CIPA</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <!-- COLUNA DE DADOS -->
                        <div class="col-md-8 mb-2">
                            <label class="col-form-label visLabel">Nome</label>
                            <p id="v_nome" class="form-control-plaintext visCampo"></p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="col-form-label visLabel">ID</label>
                            <p id="v_id" class="form-control-plaintext visCampo w-100 text-center"></p>
                        </div>
                        <div class="col-md-8">

                            <div class="mb-2">
                                <label class="col-form-label visLabel">Cargo</label>
                                <p id="v_cargo" class="form-control-plaintext visCampo"></p>
                            </div>
                            <div class="mb-2">
                                <label class="col-form-label visLabel">SubSede</label>
                                <p id="v_subsede" class="form-control-plaintext visCampo"></p>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <label class="col-form-label visLabel">e-Mail</label>
                                    <p id="v_email" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="col-form-label visLabel">Telefone</label>
                                    <p id="v_telefone" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <label class="col-form-label visLabel">Data Ini</label>
                                    <p id="v_dataini" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="col-form-label visLabel">Data Fim</label>
                                    <p id="v_datafim" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                            </div>
                        </div>

                        <!-- COLUNA DE FOTO -->
                        <div class="col-md-4 text-center d-flex align-items-center justify-content-center">
                            <img id="v_foto" src="../fotos/perfil.png" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>

                    <div class="row m-3">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                <i class="fa fa-close"></i> Fechar
                            </button>
                        </div>
                        <span id="divQuando" class="mt-2 ms-2" style="font-size: 12px; font-weight: 200"></span>
                    </div>
                </div>
                <div id="criadoMembro" class="m-2"></div>
            </div>
        </div>
    </div>

    <!-- Modal: MEMBRO | ALTERAR Membro DA CIPA -->
    <div class="modal fade" id="modalEditarMembro" tabindex="-1" aria-labelledby="altMembroModalLabel" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="altMembroModalLabel">ALTERAÇÃO de Membro de CIPA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="formAltMembro" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="idMembro" name="idMembro">

                        <div class="row">
                            <!-- Coluna esquerda: campos -->
                            <div class="col-md-7">
                                <div class="mb-2">
                                    <label for="nmPessoa" class="form-label cab">Nome da Pessoa</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control obrigatorio" id="nmPessoa" name="nmPessoa" placeholder="comece a digitar o nome" required>
                                        <button class="btn btn-outline-primary" onclick="btn_inclui_pessoa()">
                                            <i class="fa-solid fa-person-circle-plus"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" id="idPessoa" name="idPessoa" value="0">
                                </div>

                                <div class="mb-2">
                                    <label for="idSeletorSubsedes" class="form-label cab">SubSede</label>
                                    <span id="idSeletorSubsedes">
                                        <?= seletor_subsedes($idSubSede ?? 0) ?>
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <label for="idSeletorCargo" class="form-label cab">Cargo</label>
                                    <span id="idSeletorCargo">
                                        <?= seletor_cargos($idCargo ?? 0) ?>
                                    </span>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-sm-6">
                                        <label for="dtInicio" class="form-label cab">Início na CIPA</label>
                                        <input type="date" id="dtInicio" name="dtInicio" class="form-control text-center obrigatorio" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="dtFinal" class="form-label cab">Final</label>
                                        <input type="date" id="dtFinal" name="dtFinal" class="form-control text-center">
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label for="fotoAlt" class="form-label cab">Foto do Perfil</label>
                                    <input type="file" id="fotoAlt" name="fotoAlt" class="form-control" accept="image/*" onchange="previewFoto(event, 'previewImagemAlt')">
                                </div>
                            </div>

                            <!-- Coluna direita: Preview -->
                            <div class="col-md-5 text-center d-flex align-items-center justify-content-center">
                                <img id="previewImagemAlt" src="../fotos/perfil.png" class="img-thumbnail" style="max-height: 200px;">
                                <input type="hidden" id="fotoAtual" name="fotoAtual" value="">
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="btn-group" id="btnGroupAltMembro">
                                <button type="button" class="btn btn-success btn-sm rounded m-1" onclick="btnAltSalvarMembro()">
                                    <i class="fa fa-upload"></i> Salvar
                                </button>
                                <button type="reset" class="btn btn-secondary btn-sm rounded m-1" id="btnAltReset">
                                    <i class="fa fa-recycle"></i> Reset
                                </button>
                                <button type="button" class="btn btn-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Fechar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="msgAlertMembroAlt" class="text-center h5"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: ATENDIMENTO | INCLUIR ATENDIMENTO DA CIPA -->
    <div class="modal fade" id="modalIncAtendimento" tabindex="-1" aria-labelledby="tituloModalAtendimento" aria-hidden="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formIncAtendimento">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tituloModalAtendimento">Registrar Atendimento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label for="data_ocorrencia" class="form-label cab">Data e Hora</label>
                                <input type="datetime-local" class="form-control" id="data_ocorrencia" name="data_ocorrencia" required>
                            </div>

                            <div class="col-md-8">
                                <label for="idCipeiro" class="form-label cab">Cipeiro</label>
                                <?= seletor_cipeiro() ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-8">
                                <label for="nmPessoaAtendida" class="form-label cab">Pessoa Atendida</label>
                                <input type="text" class="form-control" id="nmPessoaAtendida" name="nmPessoaAtendida" placeholder="Digite o nome...">
                                <input type="hidden" id="nmPessoaAtendida" name="nmPessoaAtendida">
                            </div>

                            <div class="col-md-4">
                                <label for="tipo_ocorrencia" class="form-label cab">Tipo de Ocorrência</label>
                                <?= seletor_ocorrencia() ?>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="local_ocorrencia" class="form-label cab">Local da Ocorrência</label>
                            <input type="text" class="form-control" id="local_ocorrencia" name="local_ocorrencia">
                        </div>

                        <div class="mb-2">
                            <label for="descricao" class="form-label cab">Descrição do ocorrido</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="4"></textarea>
                        </div>

                        <div class="mb-2">
                            <label for="acao_realizada" class="form-label cab">Ação Realizada</label>
                            <textarea class="form-control" id="acao_realizada" name="acao_realizada" rows="4"></textarea>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-12">
                                <label for="encaminhamento" class="form-label cab">Encaminhamento</label>
                                <input type="text" class="form-control" id="encaminhamento" name="encaminhamento">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick='btnSalvarAtendimento()'>Salvar Atendimento</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
                <div class="text-center h5" id='msgIncAtendimento'></div>
            </div>
        </div>
    </div>

    <!-- Modal: ATENDIMENTO | ALTERAR ATENDIMENTO DA CIPA -->
    <div class="modal fade" id="modalAltAtendimento" tabindex="-1" aria-labelledby="tituloModalAtendimento" aria-hidden="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formAltAtendimento">
                    <input type="hidden" id="idAtendimento">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tituloModalAtendimento">Alterar Registro Atendimento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label for="data_ocorrencia" class="form-label cab">Data e Hora</label>
                                <input type="datetime-local" class="form-control" id="data_ocorrencia" name="data_ocorrencia" required>
                            </div>

                            <div class="col-md-8">
                                <label for="idCipeiro" class="form-label cab">Cipeiro</label>
                                <?= seletor_cipeiro() ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-8">
                                <label for="nmPessoaAtendida" class="form-label cab">Pessoa Atendida</label>
                                <input type="text" class="form-control" id="nmPessoaAtendida" name="nmPessoaAtendida" placeholder="Digite o nome...">
                                <input type="hidden" id="nmPessoaAtendida" name="nmPessoaAtendida">
                            </div>

                            <div class="col-md-4">
                                <label for="tipo_ocorrencia" class="form-label cab">Tipo de Ocorrência</label>
                                <?= seletor_ocorrencia() ?>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="local_ocorrencia" class="form-label cab">Local da Ocorrência</label>
                            <input type="text" class="form-control" id="local_ocorrencia" name="local_ocorrencia">
                        </div>

                        <div class="mb-2">
                            <label for="descricao" class="form-label cab">Descrição do ocorrido</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="4"></textarea>
                        </div>

                        <div class="mb-2">
                            <label for="acao_realizada" class="form-label cab">Ação Realizada</label>
                            <textarea class="form-control" id="acao_realizada" name="acao_realizada" rows="4"></textarea>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-12">
                                <label for="encaminhamento" class="form-label cab">Encaminhamento</label>
                                <input type="text" class="form-control" id="encaminhamento" name="encaminhamento">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick='btnSalvarAltAtendimento()'>Salvar Atendimento</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
                <div class="text-center h5" id='msgAltAtendimento'></div>
            </div>
        </div>
    </div>

    <!-- Modal: ATENDIMENTO | VISUALIZAR ATENDIMENTO DA CIPA -->
    <div class="modal fade" id="modalVerAtendimento" tabindex="-1" aria-labelledby="tituloModalAtendimento" aria-hidden="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formIncAtendimento">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tituloModalAtendimento">Visualizar Atendimento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label for="v_data_ocorrencia" class="form-label cab">Data e Hora</label>
                                <p id="v_data_ocorrencia" class="form-control-plaintext visCampo"></p>
                            </div>

                            <div class="col-md-8">
                                <label for="v_nome_cipeiro" class="form-label cab">Cipeiro</label>
                                <p id="v_nome_cipeiro" class="form-control-plaintext visCampo"></p>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label for="v_paciente" class="form-label cab">Pessoa Atendida</label>
                                <p id="v_paciente" class="form-control-plaintext visCampo"></p>
                            </div>

                            <div class="col-md-6">
                                <label for="v_tipo_ocorrencia" class="form-label cab">Tipo de Ocorrência</label>
                                <p id="v_tipo_ocorrencia" class="form-control-plaintext visCampo"></p>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="v_local_ocorrencia" class="form-label cab">Local da Ocorrência</label>
                            <p id="v_local_ocorrencia" class="form-control-plaintext visCampo"></p>
                        </div>

                        <div class="mb-2">
                            <label for="v_descricao" class="form-label cab">Descrição do ocorrido</label>
                            <p id="v_descricao" class="form-control-plaintext visCampoScroll2L"></p>
                        </div>

                        <div class="mb-2">
                            <label for="v_acao_realizada" class="form-label cab">Ação Realizada</label>
                            <p id="v_acao_realizada" class="form-control-plaintext visCampoScroll2L"></p>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-12">
                                <label for="v_encaminhamento" class="form-label cab">Encaminhamento</label>
                                <p id="v_encaminhamento" class="form-control-plaintext visCampo"></p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div id="criado_em" class="text-muted small"></div>

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Modal: OCORRÊNCIA | INCLUIR novo "Tipo de Ocorrência" -->
    <div class="modal fade" id="modalNovoTipoOcorrencia" tabindex="-1" aria-labelledby="tituloModalNovoTipo" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">

            <form id="formNovoTipoOcorrencia">
                <div class="modal-content" style="background-color: gainsboro; width: 500px;">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title" id="tituloModalNovoTipo">Novo Tipo de Ocorrência</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="novoTipoOcorrencia" class="form-label">Descrição do Tipo</label>
                            <input type="text" class="form-control" id="novoTipoOcorrencia" name="novoTipoOcorrencia" placeholder="Ex: Pressão baixa" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick='btnSalvarTipo()'>Salvar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
            <div class="text-center" id='msgIncTipo'></div>
        </div>
    </div>

    <!-- Modal: REUNIÃO | VISUALIZAR reunião de CIPA (VER) -->
    <div class="modal fade" id="modalVerReuniao" tabindex="-1" aria-labelledby="modalVerReuniaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="background-color: #f9f9f9;">
                <form id="formVerReuniao">

                    <div class="modal-header bg-primary text-white">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="modal-title mb-0" id="modalIncReuniaoLabel">Visualizar dados da Reunião DA CIPA</h5>
                                </div>
                                <div class="col-auto text-end">
                                    <span id="idReuniaoVisual" class="fw-bold">ID: 123</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="mb-3">
                                    <label for="v_data_reuniao" class="form-label">Data da Reunião</label>
                                    <p id="v_data_reuniao" class="form-control-plaintext visCampo text-center h5"></p>
                                </div>

                                <div class="mb-3">
                                    <label for="v_assunto" class="form-label">Assunto</label>
                                    <p id="v_assunto" class="form-control-plaintext visCampo"></p>
                                </div>

                                <div class="mb-3">
                                    <label for="v_observacoes" class="form-label">Observações</label>
                                    <p id="v_observacoes" class="form-control-plaintext"></p>
                                </div>

                                <div class="mb-3">
                                    <label for="v_ata_arquivo" class="form-label">Arquivo da Ata da Reunião</label>
                                    <p id="v_ata_arquivo" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Membros Presentes</label>

                                <div class="card">
                                    <div class="card-body">
                                        <div id="v_cipeiros" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div id="v_criado_em" class="text-muted small"></div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: REUNIÃO | INCLUIR Nova REUNIÃO de CIPA-->
    <div class="modal fade" id="modalIncReuniao" tabindex="-1" aria-labelledby="modalIncReuniaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="background-color: #f9f9f9;">
                <form id="formIncReuniao" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalIncReuniaoLabel">Registrar Reunião DA CIPA</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="mb-3">
                                    <label for="data_reuniao" class="form-label">Data da Reunião</label>
                                    <input type="date" class="form-control text-center h6" id="data_reuniao" name="data_reuniao" required>
                                </div>

                                <div class="mb-3">
                                    <label for="assunto" class="form-label">Assunto</label>
                                    <input type="text" class="form-control" id="assunto" name="assunto" required>
                                </div>

                                <div class="mb-3">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" placeholder="descreva a reunião..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="ata_arquivo" class="form-label">Upload da Ata (PDF ou imagem) <i class="text-secondary">(opcional)</i></label>
                                    <input type="file" class="form-control" id="ata_arquivo" name="ata_arquivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Membros Presentes</label>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <?= seletor_membros_reuniao() ?>
                                            <button class="btn btn-primary" onclick="addCipeiro('#formIncReuniao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                            <button class="btn btn-secondary" onclick="addTodosCipeiro('#formIncReuniao')"><i class="fa-solid fa-users"></i> Todos</button>
                                        </div>

                                        <div id="cipeirosContainer" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div id="criado_em" class="text-muted small"></div>
                            <div>
                                <button type="button" class="btn btn-primary" onclick='btnSalvarReuniao()'>Salvar Reunião</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="text-center h5" id='msgAlertaIncReuniao'></div>
            </div>
        </div>
    </div>

    <!-- Modal: REUNIÃO | ALTERAÇÃO de reunião -->
    <div class="modal fade" id="modalAltReuniao" tabindex="-1" aria-labelledby="modalAltReuniaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="background-color: #f9f9f9;">
                <form id="formAltReuniao" enctype="multipart/form-data">
                    <input type="hidden" id="idReuniaoAlt" name="idReuniaoAlt" value='0'>

                    <div class="modal-header bg-primary text-white">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="modal-title mb-0" id="modalIncReuniaoLabel">Alterar dados da Reunião DA CIPA</h5>
                                </div>
                                <div class="col-auto text-end">
                                    <span id="idReuniaoVisual" class="fw-bold">ID: 123</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="mb-3">
                                    <label for="data_reuniao" class="form-label">Data da Reunião</label>
                                    <input type="date" class="form-control text-center h6" id="data_reuniao" name="data_reuniao" required>
                                </div>

                                <div class="mb-3">
                                    <label for="assunto" class="form-label">Assunto</label>
                                    <input type="text" class="form-control" id="assunto" name="assunto" required>
                                </div>

                                <div class="mb-3">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" placeholder="descreva a reunião..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="ata_arquivo" class="form-label">Upload da Ata (PDF ou imagem) <i class="text-secondary">(opcional)</i></label>
                                    <input type="file" class="form-control" id="ata_arquivo" name="ata_arquivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Membros Presentes</label>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <?= seletor_membros_reuniao() ?>
                                            <button class="btn btn-primary" onclick="addCipeiro('#formAltReuniao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                            <button class="btn btn-secondary" onclick="addTodosCipeiro('#formAltReuniao')"><i class="fa-solid fa-users"></i> Todos</button>
                                        </div>

                                        <div id="cipeirosContainer" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div id="criado_em" class="text-muted small"></div>
                            <div>
                                <button type="button" class="btn btn-primary" onclick='btnSalvarAltReuniao()'>Salvar Reunião</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="text-center h5" id='msgAlertaAltReuniao'></div>
            </div>
        </div>
    </div>

    <!-- Modal: AÇÃO | INCLUIR Nova AÇÃO de CIPA-->
    <div class="modal fade" id="modalIncAcao" tabindex="-1" aria-labelledby="modalIncAcaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="background-color: #f9f9f9;">
                <form id="formIncAcao" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalIncReuniaoLabel">Registrar AÇÃO DA CIPA</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">

                                <div class="mb-2">
                                    <label for="idSeletorSubsedes" class="form-label cab">SubSede</label>
                                    <span id="idSeletorSubsedes">
                                        <?= seletor_subsedes($idSubSede ?? 0) ?>
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <label for="data_reuniao" class="form-label">Data da Ação</label>
                                    <input type="date" class="form-control text-center h6" id="data_acao" name="data_acao" required>
                                </div>

                                <div class="mb-2">
                                    <label for="assunto" class="form-label">Assunto</label>
                                    <input type="text" class="form-control" id="assunto" name="assunto" required>
                                </div>

                                <div class="mb-2">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" placeholder="descreva a reunião..."></textarea>
                                </div>

                                <div class="mb-2">
                                    <label for="ata_arquivo" class="form-label">Upload de Documento (PDF ou imagem) <i class="text-secondary">(opcional)</i></label>
                                    <input type="file" class="form-control" id="acao_arquivo" name="acao_arquivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Membros Presentes</label>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <?= seletor_membros_reuniao() ?>
                                            <button class="btn btn-primary" onclick="addCipeiro('#formIncAcao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                            <button class="btn btn-secondary" onclick="addTodosCipeiro('#formIncAcao')"><i class="fa-solid fa-users"></i> Todos</button>
                                        </div>

                                        <div id="cipeirosContainer" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div id="criado_em" class="text-muted small"></div>
                            <div>
                                <button type="button" class="btn btn-primary" onclick='btnSalvarAcao()'>Salvar Reunião</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="text-center h5" id='msgAlertaIncAcao'></div>
            </div>
        </div>
    </div>

    <!-- Modal: AÇÃO | VISUALIZAR AÇÃO de CIPA (VER) -->
    <div class="modal fade" id="modalVerAcao" tabindex="-1" aria-labelledby="modalVerAcaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="background-color: #f9f9f9;">
                <form id="formVerAcao">

                    <div class="modal-header bg-primary text-white">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="modal-title mb-0" id="modalIncReuniaoLabel">Visualizar dados da Ação DA CIPA</h5>
                                </div>
                                <div class="col-auto text-end">
                                    <span id="idAcaoVisual" class="fw-bold">ID: 123</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="mb-2">
                                    <label for="v_data_reuniao" class="form-label">Data/Local da Ação</label>
                                    <p id="v_data_acao" class="form-control-plaintext visCampo text-center h5"></p>
                                    <p id="v_subsede" class="form-control-plaintext visCampo text-center"></p>
                                </div>
                                <div class="mb-2">
                                    <label for="v_assunto" class="form-label">Assunto</label>
                                    <p id="v_assunto" class="form-control-plaintext visCampo"></p>
                                </div>

                                <div class="mb-2">
                                    <label for="v_observacoes" class="form-label">Observações</label>
                                    <p id="v_observacoes" class="form-control-plaintext"></p>
                                </div>

                                <div class="mb-2">
                                    <label for="v_acao_arquivo" class="form-label">Documento anexo à Ação</label>
                                    <p id="v_acao_arquivo" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Membros Participantes</label>

                                <div class="card">
                                    <div class="card-body">
                                        <div id="v_cipeiros" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div id="v_criado_em" class="text-muted small"></div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: AÇÃO | ALTERAÇÃO de AÇÃO -->
    <div class="modal fade" id="modalAltAcao" tabindex="-1" aria-labelledby="modalAltAcaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="background-color: #f9f9f9;">
                <form id="formAltAcao" enctype="multipart/form-data">
                    <input type="hidden" id="idAcaoAlt" name="idAcaoAlt" value='0'>

                    <div class="modal-header bg-primary text-white">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="modal-title mb-0" id="modalIncAcaoLabel">Alterar dados da Ação DA CIPA</h5>
                                </div>
                                <div class="col-auto text-end">
                                    <span id="idAcaoVisual" class="fw-bold">ID: 123</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="mb-2">
                                    <label for="idSeletorSubsedes" class="form-label cab">SubSede</label>
                                    <span id="idSeletorSubsedes">
                                        <?= seletor_subsedes($idSubSede ?? 0) ?>
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <label for="data_acao" class="form-label">Data da Ação</label>
                                    <input type="date" class="form-control text-center h6" id="data_acao" name="data_acao" required>
                                </div>

                                <div class="mb-3">
                                    <label for="assunto" class="form-label">Assunto</label>
                                    <input type="text" class="form-control" id="assunto" name="assunto" required>
                                </div>

                                <div class="mb-3">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" placeholder="descreva a reunião..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="ata_arquivo" class="form-label">Upload da Ata (PDF ou imagem) <i class="text-secondary">(opcional)</i></label>
                                    <input type="file" class="form-control" id="acao_arquivo" name="acao_arquivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Participantes da Ação</label>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <?= seletor_membros_reuniao() ?>
                                            <button class="btn btn-primary" onclick="addCipeiro('#formAltAcao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                            <button class="btn btn-secondary" onclick="addTodosCipeiro('#formAltAcao')"><i class="fa-solid fa-users"></i> Todos</button>
                                        </div>

                                        <div id="cipeirosContainer" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div id="criado_em" class="text-muted small"></div>
                            <div>
                                <button type="button" class="btn btn-primary" onclick='btnSalvarAltAcao()'>Salvar Reunião</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="text-center h5" id='msgAlertaAltAcao'></div>
            </div>
        </div>
    </div>

    <!-- Modal: DOCUMENTOS | VISUALIZAR -->
    <div class="modal fade" id="modalVerDoc" tabindex="-1" aria-labelledby="verDocLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: #f8f9fa">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-eye"></i> Visualizar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- Coluna esquerda com detalhes -->
                        <div class="col-md-7">
                            <div class="mb-2">
                                <label class="form-label">Arquivo</label>
                                <p id="v_arquivo" class="form-control-plaintext visCampo"></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Título do Documento</label>
                                <p id="v_titulo" class="form-control-plaintext visCampo"></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Data</label>
                                <p id="v_data" class="form-control-plaintext visCampo"></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nome Original</label>
                                <p id="v_original" class="form-control-plaintext visCampo"></p>
                            </div>
                        </div>

                        <!-- Coluna direita com o preview -->
                        <div class="col-md-5 text-center d-flex align-items-center justify-content-center">
                            <div id="previewDoc">
                                <!-- Inserido via JS -->
                            </div>
                        </div>
                    </div>

                    <div class="row m-3">
                        <div class="btn-group">

                            <a id="btnVisualizarDoc" href="#" class="btn btn-outline-success btn-sm rounded" target="_blank">
                                <i class="fa fa-eye"></i> Visualizar
                            </a>

                            <a id="btnDownloadDoc" href="#" class="btn btn-outline-primary btn-sm rounded" target="_blank" download>
                                <i class="fa fa-download"></i> Baixar
                            </a>

                            <button type="button" class="btn btn-outline-danger btn-sm rounded" data-bs-dismiss="modal">
                                <i class="fa fa-close"></i> Fechar
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal: DOCUMENTOS | INCLUIR | UPLOAD -->
    <div class="modal fade" id="modalIncDoc" tabindex="-1" aria-labelledby="uploadDocumentoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background-color: #f8f9fa;">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadDocumentoLabel">
                        <i class="fa-solid fa-upload"></i> Upload de Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="formIncDoc" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <!-- COLUNA PRINCIPAL -->
                            <div class="col-md-7">
                                <div class="mb-2">
                                    <label for="dataDocumento" class="form-label cab">Data</label>
                                    <input type="date" id="data" name="data" class="form-control text-center obrigatorio" required>
                                </div>

                                <div class="mb-2">
                                    <label for="tipoDocumento" class="form-label cab">Tipo de Documento</label>
                                    <div id='seletor_tipos_doc'></div>
                                </div>

                                <div class="mb-2">
                                    <label for="assunto" class="form-label cab">Assunto</label>
                                    <input type="text" id="assunto" name="assunto" class="form-control obrigatorio" maxlength="100" required>
                                </div>

                                <div class="mb-2">
                                    <label for="tags" class="form-label cab">Tags</label>
                                    <input type="text" id="tags" name="tags" class="form-control" placeholder="ex: cipa, segurança, reunião">
                                </div>

                                <div class="mb-2">
                                    <label for="documento" class="form-label cab">Documento (PDF)</label>
                                    <input type="file" name="documento" id="documento" class="form-control" accept="application/pdf" onchange="previewDocumento(event)">

                                </div>
                            </div>

                            <!-- COLUNA DE PREVIEW -->
                            <div class="col-md-5">
                                <div id="previewContainer" style="
                                    height: 300px;
                                    width: 100%;
                                    position: relative;
                                    border: 1px solid #ccc;
                                    background: url('../fotos/pdf_placeholder.png') center center no-repeat;
                                    background-size: contain;
                                ">
                                    <!-- Iframe será inserido aqui via JS -->
                                </div>
                            </div>

                        </div>

                        <hr>

                        <!-- BOTÕES -->
                        <div class="text-center" id='divBtnSalvarDoc'>
                            <button type="button" class="btn btn-success btn-sm rounded m-1" onclick='btnSalvarDoc()'>
                                <i class="fa fa-upload"></i> Enviar
                            </button>
                            <button type="reset" class="btn btn-secondary btn-sm rounded m-1">
                                <i class="fa fa-recycle"></i> Limpar
                            </button>
                            <button type="button" class="btn btn-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                <i class="fa fa-close"></i> Fechar
                            </button>
                        </div>
                    </form>

                    <div id="msgAlertaIncDoc" class="text-center mt-2 h5"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: DOCUMENTOS | EDITAR -->
    <div class="modal fade" id="modalEditDoc" tabindex="-1" aria-labelledby="editarDocumentoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background-color: #f8f9fa;">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="editarDocumentoLabel">
                        <i class="fa-solid fa-pen-to-square"></i> Editar Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="formAltDoc" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="idDoc" id="idDoc"> <!-- ID oculto -->

                        <div class="row">
                            <!-- COLUNA PRINCIPAL -->
                            <div class="col-md-7">
                                <div class="mb-2">
                                    <label for="dataEdit" class="form-label cab">Data</label>
                                    <input type="date" id="dataEdit" name="data" class="form-control text-center obrigatorio" required>
                                </div>

                                <div class="mb-2">
                                    <label for="tipoDocumentoEdit" class="form-label cab">Tipo de Documento</label>
                                    <div id='seletor_tipos_doc_edit'></div>
                                </div>

                                <div class="mb-2">
                                    <label for="assuntoEdit" class="form-label cab">Assunto</label>
                                    <input type="text" id="assuntoEdit" name="assunto" class="form-control obrigatorio" maxlength="100" required>
                                </div>

                                <div class="mb-2">
                                    <label for="tagsEdit" class="form-label cab">Tags</label>
                                    <input type="text" id="tagsEdit" name="tags" class="form-control" placeholder="ex: cipa, segurança, reunião">
                                </div>

                                <div class="mb-2">
                                    <label for="documentoEdit" class="form-label cab">Documento (PDF) | opcional</label>
                                    <input type="file" name="documento_edit" id="documento_edit" class="form-control" accept="application/pdf" onchange="previewDocumento(event, 'previewContainerEdit')">
                                    <small class="text-muted">Se não selecionar, o arquivo atual será mantido.</small>
                                </div>
                            </div>

                            <!-- COLUNA DE PREVIEW DA MODAL DE EDIÇÃO -->
                            <div class="col-md-5">
                                <div id="previewContainerEdit" style="
                                    height: 300px;
                                    width: 100%;
                                    position: relative;
                                    border: 1px solid #ccc;
                                    background: url('../fotos/pdf_placeholder.png') center center no-repeat;
                                    background-size: contain;">
                                    <!-- Iframe será inserido aqui via JS -->
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- BOTÕES -->
                        <div class="text-center" id='divBtnSalvarDocEdit'>
                            <button type="button" class="btn btn-primary btn-sm rounded m-1" onclick='btnSalvarEdicaoDoc()'>
                                <i class="fa fa-save"></i> Salvar Alterações
                            </button>
                            <button type="button" class="btn btn-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                <i class="fa fa-close"></i> Fechar
                            </button>
                        </div>
                    </form>

                    <div id="msgAlertaAltDoc" class="text-center mt-2 h5"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- The Modal Visualizar Usuário -->
    <div class="modal fade" id="modalVisUsuario" tabindex="-1" aria-labelledby="visUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header modal-header-gray">
                    <h4 class="modal-title" id="visUsuarioModalLabel">
                        <h5 id="msgCabVisualisar">Detalhamento dos Dados</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <span id="msgAlertaVisual"></span>
                    <div class="row">
                        <div class='col-md-8'>
                            <dl class="row">
                                <dt class="col-sm-3">ID Usuario: </dt>
                                <dd class="col-sm-3 border_bottom"><span id="idUsuario"></span></dd>
                                <dt class="col-sm-3">ID Pessoa: </dt>
                                <dd class="col-sm-3 border-bottom"><span id="v_usu_idPessoa"></span></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-3">Grupo: </dt>
                                <dd class="col-sm-9 border-bottom"><span id="idGrupo"></span></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-3">Login: </dt>
                                <dd class="col-sm-9 border-bottom"><span id="login"></span></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-3">Nome: </dt>
                                <dd class="col-sm-9 border-bottom"><span id="nome"></span></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-3">Nm Social: </dt>
                                <dd class="col-sm-9 border-bottom"><span id="nomeSocial"></span></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-3">CPF: </dt>
                                <dd class="col-sm-9 border-bottom"><span id="cpf"></span></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-3">e-Mail: </dt>
                                <dd class="col-sm-9 border-bottom"><span id="email"></span></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-3">ChaveApp: </dt>
                                <dd class="col-sm-9 border-bottom"><span id="_chaveApp"></span></dd>
                            </dl>
                            <dl class="row">
                                <div class="col-sm-3"><b>Usuário Ativo:</b> <span id="uAtivo"></span></div>
                                <div class="col-sm-3"><b>Pessoa Ativa:</b> <span id="pAtivo"></span></div>
                                <div class="col-sm-3"><b>CIPA:</b> <span id="pCipa"></span></div>
                                <div class="col-sm-3"><b>Brigada:</b> <span id="pBrigada"></span></div>
                            </dl>
                        </div>
                        <div class="col-md-4 text-center">
                            <img id="fichaFoto" src="../fotos/perfil.png" alt="Foto do usuário" class="img-fluid rounded shadow" style="max-height: 350px; object-fit: cover;">
                            <div class="text-muted mt-2" style="font-size: 12px;">Clique para ampliar</div>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer" id="rodape">
                    <div class="d-flex justify-content-between w-100">
                        <div id="criado_em">Criado em: 2025-06-16 09:30</div>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- The Modal Incluir Usuário -->
    <div class="modal fade" id="modalIncUsuario" tabindex="-1" aria-labelledby="incUsuarioModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="incUsuarioModalLabel">
                        <h5>Inclusão de Usuário do Módulo: CIPA</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <span id="msgAlertErroUsuInc"></span>
                    <form id="form-cad-usuario" enctype="multipart/form-data" method="post">

                        <input type="hidden" id='inputIdEmpresa' name='inputIdEmpresa' value='<?= $_SESSION['idEmpresa'] ?>'>

                        <div class="row mb-3">
                            <label for="pessoa" class="col-sm-2 col-form-label">Pessoa</label>
                            <div class="col-sm-10">
                                <span id="idSeletorPessoas">
                                    <select class='form-control danger' style="border-color: red"></select>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="pessoa" class="col-sm-2 col-form-label">Colab.:</label>
                            <div class="col-sm-10">
                                <span id="idSeletorColaborador">
                                    <select class='form-select'></select>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="idSeletorSubsedes" class="col-sm-2 col-form-label">SubSede</label>
                            <div class="col-sm-10">
                                <span id="idSeletorSubsedes">
                                    <select class='form-control danger' style="border-color: red">
                                        <option>Carregando...</option>
                                    </select>
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputLogin" class="col-sm-2 col-form-label">Login</label>
                            <div class="col-sm-10">
                                <input type="text" name="inputLogin" value="" class="form-control border-danger" id="inputLogin" placeholder="Login do usuário...">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputSenha" class="col-sm-2 col-form-label">Senha</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control border-danger" name="inputSenha" value="" id="inputSenha" placeholder="Senha de acesso...">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputFoto" class="col-sm-2 col-form-label">Foto</label>
                            <div class="col-sm-10">
                                <input type="file" id="inputFoto" name="foto" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="btn-group">
                                <div class="col-sm-2"></div>
                                <button type="button" class="btn btn-success btn-sm rounded   me-1" onclick="btnIncSalvarUsuario()"><i class="fa fa-upload"></i> Salvar</button>
                                <button type="reset" class="btn btn-secondary btn-sm rounded me-1" id="btnIncUsuReset"><i class="fa fa-recycle"></i> Reset</button>
                                <button type="button" class="btn btn-danger btn-sm rounded    me-1" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Fechar</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- The Modal ALTERAR Usuário -->
    <div class="modal fade" id="modalAltUsuario" tabindex="-1" aria-labelledby="altUsuarioModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="altUsuarioModalLabel">
                        <h5>Alteração de Usuário do Módulo: CIPA</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <span id="msgAlertErroUsuAlt"></span>
                    <form id="form-alt-usuario" enctype="multipart/form-data" method="post">

                        <input type="hidden" id='inputIdEmpresa' name='inputIdEmpresa' value='<?= $_SESSION['idEmpresa'] ?>'>
                        <input type="hidden" id='idAltUsuario' name='idAltUsuario' value='0'>
                        <input type="hidden" id='idAltUsuarioGrupo' name='idAltUsuarioGrupo' value='0'>

                        <input type="hidden" id='inputCheckCipa' name='inputCheckCipa' value='0'>
                        <input type="hidden" id='inputAltChave' name='inputAltChave' value='0'>

                        <div class="row mb-3">
                            <label for="idAltSeletorPessoas" class="col-sm-2 col-form-label">Pessoa</label>
                            <div class="col-sm-10">
                                <span id="idAltSeletorPessoas">
                                    <select class='form-control danger' style="border-color: red"></select>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="idAltSeletorColaborador" class="col-sm-2 col-form-label">Colab.:</label>
                            <div class="col-sm-10">
                                <span id="idAltSeletorColaborador">
                                    <select class='form-select'></select>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="idAltSeletorSubsedes" class="col-sm-2 col-form-label">SubSede</label>
                            <div class="col-sm-10">
                                <span id="idAltSeletorSubsedes">
                                    <select class='form-control danger' style="border-color: red">
                                        <option>Carregando...</option>
                                    </select>
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputLogin" class="col-sm-2 col-form-label">Login</label>
                            <div class="col-sm-10">
                                <input type="text" name="inputAltLogin" value="" class="form-control border-danger" id="inputAltLogin" placeholder="Login do usuário...">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputSenha" class="col-sm-2 col-form-label">Senha</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control border-danger" name="inputAltSenha" value="" id="inputAltSenha" placeholder="Senha de acesso...">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class='col-sm-2'></div>
                            <div class="col-sm-9">
                                <div class="d-flex justify-content-between align-items-center w-100 px-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="inputAltAtivo" name="inputAltAtivo" value="SIM" checked onclick="testeInputAtivo()">
                                        <label class="form-check-label" for="inputAltAtivo">
                                            <span id="textoAltAtivo">Usuário ativo</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='col-sm-1'></div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputAltFoto" class="col-sm-2 col-form-label">Nova foto</label>
                            <div class="col-sm-10">
                                <input type="file" id="inputAltFoto" name="foto" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="btn-group">
                                <div class="col-sm-2"></div>
                                <button type="button" class="btn btn-success btn-sm rounded   me-1" onclick="btnAltSalvarUsuario()"><i class="fa fa-upload"></i> Salvar</button>
                                <button type="reset" class="btn btn-secondary btn-sm rounded me-1" id="btnAltUsuReset"><i class="fa fa-recycle"></i> Reset</button>
                                <button type="button" class="btn btn-danger btn-sm rounded    me-1" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Fechar</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 
                    Aqui Termina o conteúdo da página 
            -->
    <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">Copyright &copy; GERAR RH 2025</div>
                <div class="text-center flex-fill">
                    <span class="text-muted">
                        ID: <?= $_SESSION['idLogin'] ?> | IP: <?= $_SERVER['REMOTE_ADDR']; ?> | <?= $_SESSION['nmLogin'] ?>
                    </span>
                </div>
                <div>
                    <a href="../rh_politica_priv.php" class="_PoliticaDePrivacidade">Política de Privacidade</a>
                    &middot;
                    <a href="../rh_termosdeuso.php" class="_PoliticaDePrivacidade">Termos &amp; Condições</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="../js/scripts.js"></script>
    <script src="index.js"></script>
    <script src="../js/cpf.js"></script>

    <!-- Importa Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Importa plugin para datalabels -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById("graficoPizza").getContext("2d");

            const valores = <?= json_encode($valores) ?>;
            const total = valores.reduce((a, b) => a + b, 0);

            const graficoPizza = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        data: valores,
                        backgroundColor: [
                            "#007bff", "#28a745", "#dc3545", "#ffc107", "#17a2b8",
                            "#6f42c1", "#fd7e14", "#20c997", "#6610f2", "#e83e8c"
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: "bottom"
                        },
                        datalabels: {
                            color: "#fff",
                            font: {
                                weight: "bold",
                                size: 14
                            },
                            formatter: function(value, context) {
                                let percent = (value / total * 100).toFixed(1) + "%";
                                return percent;
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw;
                                    let percent = ((value / total) * 100).toFixed(1) + "%";
                                    return context.label + ": " + percent;
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels] // ativa o plugin
            });
        });
    </script>

</body>

</html>
<?PHP

//-- Função para carregar a lista de subsedes
//
function seletor_subsedes($_idSubSede = 0)
{
    global $conn;
    $consulta = "SELECT subsede_id as idSubSede, identificador as dsSubSede FROM RH.rh_subsedes WHERE ativo = 1";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='idSubSede' name='idSubSede'>";
    $html .= "<option value='0'>Selecione uma SubSede...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($_idSubSede == $row['idSubSede']) ? "selected" : "";
        $html .= "<option value='{$row['idSubSede']}' $selected>{$row['dsSubSede']}</option>";
    }
    $html .= "</select>";
    return $html;
}

//-- Função para carregar a lista de cargos
//
function seletor_cargos($_idCargo = 0)
{
    global $conn;
    $consulta = "SELECT * FROM rh_cipa_cargos";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='idCargo' name='idCargo'>";
    $html .= "<option value='0'>Selecione um cargo...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($_idCargo == $row['idCargo']) ? "selected" : "";
        $html .= "<option value='{$row['idCargo']}' $selected>{$row['dsCargo']}</option>";
    }
    $html .= "</select>";
    return $html;
}

//-- Função para carregar a lista de CipeiroS
//
function seletor_cipeiro($_idMembro = 0)
{
    global $conn;
    $consulta = "SELECT B.id, P.nome
                    FROM rh_cipeiros B
                    INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                    WHERE data_final is null ORDER BY P.nome";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='idMembro' name='idMembro'>";
    $html .= "<option value='0'>Selecione um Cipeiro...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($_idMembro == $row['id']) ? "selected" : "";
        $html .= "<option value='{$row['id']}' $selected>{$row['nome']}</option>";
    }
    $html .= "</select>";
    return $html;
}

//-- Função para carregar a lista de OCORRÊNCIAS
//
function seletor_ocorrencia($_idTipoOco = 0)
{
    global $conn;
    $consulta = "SELECT * from rh_cipa_tipo_ocorrencia";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<div class='input-group'>";
    $html .= "<select class='form-select obrigatorio' id='idTipoOco' name='idTipoOco'>";
    $html .= "<option value='0'>Ocorrência...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($_idTipoOco == $row['id']) ? "selected" : "";
        $html .= "<option value='{$row['id']}' $selected>{$row['descricao']}</option>";
    }
    $html .= "</select>";
    $html .= "<button type='button' class='btn btn-outline-secondary' title='Adicionar novo tipo' onclick=\"$('#modalNovoTipoOcorrencia').modal('show')\">";
    $html .= "<i class='fa fa-plus'></i></button>";
    $html .= "</button>";
    $html .= "</div>";
    return $html;
}

//-- Função para carregar a lista de CipeiroS
//
function seletor_membros_reuniao($_idMembro = 0)
{
    global $conn;
    $consulta = "SELECT B.id, P.nome
                    FROM rh_cipeiros B
                    INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                    WHERE data_final is null ORDER BY P.nome";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='cipeiro' name='cipeiro'>";
    //$html .= "<option value='0'>Selecione um Cipeiro...</option>";
    $html .= "<option value='' disabled selected>Selecione um Cipeiro...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($_idMembro == $row['id']) ? "selected" : "";
        $html .= "<option value='{$row['id']}' $selected>{$row['nome']}</option>";
    }
    $html .= "</select>";
    return $html;
}
