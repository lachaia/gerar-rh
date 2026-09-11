<?php
//
//- rh_brigada.php | BRIGADA 
// (C)haia, 29/04/2025

session_start();

$idModulo = 10; // Brigada

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    include_once "includes/conexao_gerar.php";
    //include_once "includes/f_logs.php";
    //
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
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

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_brigada.css" rel="stylesheet" />

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
</head>


<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <!-- 
                    Aqui COMEÇA o conteúdo da página 
                -->
            <main class="flex-grow-1">
                <div class="container">
                    <div class="card mt-4 bg-dark text-white">
                        <h3 class="m-3">
                            <i class="fa-solid fa-fire-extinguisher"></i> BRIGADA
                        </h3>
                    </div>
                    <div id='divAlertaCargo' class="text-center invisivel"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#menuMembros">Membros</a>
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
                                </ul>
                            </div>
                        </div>

                        <div class="card-body">


                            <div class="tab-content">

                                <!-- TAB: MEMBROS DA BRIGADA -->
                                <div class="tab-pane container active" id="menuMembros">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <div class="text-end"><button class="btn btn-sm btn-outline-primary m-2" onclick='btn_incluir_membro()'>Incluir</button></div>
                                                <table id="tabMembros" class="table table-striped table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>SubSede</th>
                                                            <th>Nome</th>
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
                                            <div class="table-responsive">
                                                <div class="text-end"><button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncAtendimento()'>Incluir</button></div>
                                                <table id="tabAtendimentos" class="table table-striped table-bordered" style="width:100%">
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
                                        </div>
                                    </div>
                                    <div id='msgAlertaAtende' class="h5 text-center"></div>

                                </div>

                                <!-- TAB: REUNIÕES -->
                                <div class="tab-pane container fade" id="menuReunioes">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <div class="text-end"><button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncReuniao()'>Incluir</button></div>
                                                <table id="tabReunioes" class="table table-striped table-bordered" style="width:100%">
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
                                                <div class="text-end"><button class="btn btn-sm btn-outline-primary m-2" onclick='btnIncAcao()'>Incluir</button></div>
                                                <table id="tabAcoes" class="table table-striped table-bordered" style="width:100%">
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
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Modal: MEMBRO | INCLUIR novo membro da Brigada -->
            <div class="modal fade" id="modalIncluirMembro" tabindex="-1" aria-labelledby="incMembroModalLabel" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="incMembroModalLabel">
                                <h5>Inclusão de Membro de Brigada</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">

                            <form id="formIncMembro" method="post">

                                <div class="row mb-2">
                                    <div class="col-md-12">
                                        <label for="nmPessoa" class="form-label cab">Nome da Pessoa</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control obrigatorio" id="nmPessoa" name='nmPessoa' placeholder="comece a digitar o nome" required>
                                            <button class="btn btn-outline-primary" onclick='btn_inclui_pessoa()'><i class="fa-solid fa-person-circle-plus"></i></button>
                                        </div>
                                        <input type="hidden" id='idPessoa' name='idPessoa' value="0">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-sm-12">
                                        <label for="idSeletorSubsedes" class="col-sm-2 col-form-label cab">SubSede</label>
                                        <span id="idSeletorSubsedes">
                                            <?= seletor_subsedes($idSubSede ?? 0) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="row mb-2">

                                    <div class="col-sm-12">
                                        <label for="idSeletorCargo" class="col-sm-2 col-form-label cab">Cargo</label>
                                        <span id="idSeletorCargo">
                                            <?= seletor_cargos($idCargo ?? 0) ?>
                                        </span>
                                    </div>
                                </div>


                                <div class="row mb-2">
                                    <div class="col-sm-6">
                                        <label for="dtInicio" class="col-form-label cab">Início na brigada</label>
                                        <input type="date" id="dtInicio" name="dtInicio" class="form-control text-center obrigatorio" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="dtFinal" class="col-form-label cab">Final</label>
                                        <input type="date" id="dtFinal" name="dtFinal" class="form-control text-center">
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

            <!-- Modal: MEMBRO | VISUALIZAR Membro da Brigada -->
            <div class="modal fade" id="modalVerMembro" tabindex="-1" aria-labelledby="visualizarMembroLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: gainsboro">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">
                                <h5><i class="fa-solid fa-eye"></i> Visualizar Membro da Brigada</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <label class="col-sm-3 col-form-label visLabel">Nome</label>
                                    <p id="v_nome" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <label class="col-form-label visLabel">Cargo</label>
                                    <p id="v_cargo" class="form-control-plaintext visCampo"></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <label class="col-form-label visLabel">SubSede</label>
                                    <p id="v_subsede" class="form-control-plaintext visCampo"></p>
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

                            <div class="row m-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                        <i class="fa fa-close"></i> Fechar
                                    </button>
                                </div>
                                <span id='divQuando' class="mt-2 ms-2" style='font-size: 12px; font-weight: 200'></span>
                            </div>
                        </div>
                        <div id='criadoMembro' class='m-2'></div>
                    </div>
                </div>
            </div>

            <!-- Modal: MEMBRO | ALTERAR Membro da Brigada -->
            <div class="modal fade" id="modalEditarMembro" tabindex="-1" aria-labelledby="altMembroModalLabel" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="incMembroModalLabel">
                                <h5>ALTERAÇÃO de Membro de Brigada</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">

                            <form id="formAltMembro" method="post">
                                <input type="hidden" id="idMembro">
                                <div class="row mb-2">
                                    <div class="col-md-12">
                                        <label for="nmPessoa" class="form-label cab">Nome da Pessoa</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control obrigatorio" id="nmPessoa" name='nmPessoa' placeholder="comece a digitar o nome" required>
                                            <button class="btn btn-outline-primary" onclick='btn_inclui_pessoa()'><i class="fa-solid fa-person-circle-plus"></i></button>
                                        </div>
                                        <input type="hidden" id='idPessoa' name='idPessoa' value="0">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-sm-12">
                                        <label for="idSeletorSubsedes" class="col-sm-2 col-form-label cab">SubSede</label>
                                        <span id="idSeletorSubsedes">
                                            <?= seletor_subsedes($idSubSede ?? 0) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="row mb-2">

                                    <div class="col-sm-12">
                                        <label for="idSeletorCargo" class="col-sm-2 col-form-label cab">Cargo</label>
                                        <span id="idSeletorCargo">
                                            <?= seletor_cargos($idCargo ?? 0) ?>
                                        </span>
                                    </div>
                                </div>


                                <div class="row mb-2">
                                    <div class="col-sm-6">
                                        <label for="dtInicio" class="col-form-label cab">Início na brigada</label>
                                        <input type="date" id="dtInicio" name="dtInicio" class="form-control text-center obrigatorio" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="dtFinal" class="col-form-label cab">Final</label>
                                        <input type="date" id="dtFinal" name="dtFinal" class="form-control text-center">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm rounded m-1" onclick="btnAltSalvarMembro()"><i class="fa fa-upload"></i> Salvar</button>
                                        <button type="reset" class="btn btn-secondary btn-sm rounded m-1" id="btnIncReset"><i class="fa fa-recycle"></i> Reset</button>
                                        <button type="button" class="btn btn-danger btn-sm rounded m-1" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Fechar</button>
                                    </div>
                                </div>

                            </form>
                            <div id="msgAlertMembroAlt" class="text-center h5"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: ATENDIMENTO | INCLUIR ATENDIMENTO da Brigada -->
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
                                        <label for="idBrigadista" class="form-label cab">Brigadista</label>
                                        <?= seletor_brigadista() ?>
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
                                    <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
                                </div>

                                <div class="mb-2">
                                    <label for="acao_realizada" class="form-label cab">Ação Realizada</label>
                                    <textarea class="form-control" id="acao_realizada" name="acao_realizada" rows="2"></textarea>
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

            <!-- Modal: ATENDIMENTO | ALTERAR ATENDIMENTO da Brigada -->
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
                                        <label for="idBrigadista" class="form-label cab">Brigadista</label>
                                        <?= seletor_brigadista() ?>
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
                                    <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
                                </div>

                                <div class="mb-2">
                                    <label for="acao_realizada" class="form-label cab">Ação Realizada</label>
                                    <textarea class="form-control" id="acao_realizada" name="acao_realizada" rows="2"></textarea>
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

            <!-- Modal: ATENDIMENTO | VISUALIZAR ATENDIMENTO da Brigada -->
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
                                        <label for="v_nome_brigadista" class="form-label cab">Brigadista</label>
                                        <p id="v_nome_brigadista" class="form-control-plaintext visCampo"></p>
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

            <!-- Modal: REUNIÃO | VISUALIZAR reunião de brigada (VER) -->
            <div class="modal fade" id="modalVerReuniao" tabindex="-1" aria-labelledby="modalVerReuniaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content" style="background-color: #f9f9f9;">
                        <form id="formVerReuniao">

                            <div class="modal-header bg-primary text-white">
                                <div class="container-fluid">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="modal-title mb-0" id="modalIncReuniaoLabel">Visualizar dados da Reunião da Brigada</h5>
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
                                                <div id="v_brigadistas" class="d-flex flex-wrap gap-2"></div>
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

            <!-- Modal: REUNIÃO | INCLUIR Nova REUNIÃO de brigada-->
            <div class="modal fade" id="modalIncReuniao" tabindex="-1" aria-labelledby="modalIncReuniaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content" style="background-color: #f9f9f9;">
                        <form id="formIncReuniao" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalIncReuniaoLabel">Registrar Reunião da Brigada</h5>
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
                                                    <button class="btn btn-primary" onclick="addBrigadista('#formIncReuniao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                                    <button class="btn btn-secondary" onclick="addTodosBrigadista('#formIncReuniao')"><i class="fa-solid fa-users"></i> Todos</button>
                                                </div>

                                                <div id="brigadistasContainer" class="d-flex flex-wrap gap-2"></div>
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
                                            <h5 class="modal-title mb-0" id="modalIncReuniaoLabel">Alterar dados da Reunião da Brigada</h5>
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
                                                    <button class="btn btn-primary" onclick="addBrigadista('#formAltReuniao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                                    <button class="btn btn-secondary" onclick="addTodosBrigadista('#formAltReuniao')"><i class="fa-solid fa-users"></i> Todos</button>
                                                </div>

                                                <div id="brigadistasContainer" class="d-flex flex-wrap gap-2"></div>
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

            <!-- Modal: AÇÃO | INCLUIR Nova AÇÃO de brigada-->
            <div class="modal fade" id="modalIncAcao" tabindex="-1" aria-labelledby="modalIncAcaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content" style="background-color: #f9f9f9;">
                        <form id="formIncAcao" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalIncReuniaoLabel">Registrar AÇÃO da Brigada</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="mb-3">
                                            <label for="data_reuniao" class="form-label">Data da Ação</label>
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
                                                    <button class="btn btn-primary" onclick="addBrigadista('#formIncAcao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                                    <button class="btn btn-secondary" onclick="addTodosBrigadista('#formIncAcao')"><i class="fa-solid fa-users"></i> Todos</button>
                                                </div>

                                                <div id="brigadistasContainer" class="d-flex flex-wrap gap-2"></div>
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

            <!-- Modal: AÇÃO | VISUALIZAR AÇÃO de brigada (VER) -->
            <div class="modal fade" id="modalVerAcao" tabindex="-1" aria-labelledby="modalVerAcaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content" style="background-color: #f9f9f9;">
                        <form id="formVerAcao">

                            <div class="modal-header bg-primary text-white">
                                <div class="container-fluid">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="modal-title mb-0" id="modalIncReuniaoLabel">Visualizar dados da Ação da Brigada</h5>
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
                                        <div class="mb-3">
                                            <label for="v_data_reuniao" class="form-label">Data da Ação</label>
                                            <p id="v_data_acao" class="form-control-plaintext visCampo text-center h5"></p>
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
                                            <label for="v_acao_arquivo" class="form-label">Documento anexo à Ação</label>
                                            <p id="v_acao_arquivo" class="form-control-plaintext visCampo"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Membros Participantes</label>

                                        <div class="card">
                                            <div class="card-body">
                                                <div id="v_brigadistas" class="d-flex flex-wrap gap-2"></div>
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
                                            <h5 class="modal-title mb-0" id="modalIncAcaoLabel">Alterar dados da Ação da Brigada</h5>
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
                                                    <button class="btn btn-primary" onclick="addBrigadista('#formAltAcao')"><i class="fa-solid fa-person-circle-plus"></i> Adicionar</button>
                                                    <button class="btn btn-secondary" onclick="addTodosBrigadista('#formAltAcao')"><i class="fa-solid fa-users"></i> Todos</button>
                                                </div>

                                                <div id="brigadistasContainer" class="d-flex flex-wrap gap-2"></div>
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

            <!-- 
                    Aqui Termina o conteúdo da página 
            -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script src="js/scripts.js"></script>
    <script src="js/rh_brigada.js"></script>
    <script src="js/cpf.js"></script>
</body>

</html>
<?PHP

//-- Função para carregar a lista de subsedes
//
function seletor_subsedes($_idSubSede = 0)
{
    global $conn;
    $consulta = "SELECT idSubSede, dsSubSede FROM RH.rh_subsedes WHERE ativo = 1";
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
    $consulta = "SELECT * FROM rh_brigada_cargos";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='idCargo' name='idCargo'>";
    $html .= "<option value='0'>Selecione um cargo...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($_idCargo == $row['idCargoBrigada']) ? "selected" : "";
        $html .= "<option value='{$row['idCargoBrigada']}' $selected>{$row['dsCargo']}</option>";
    }
    $html .= "</select>";
    return $html;
}

//-- Função para carregar a lista de BRIGADISTAS
//
function seletor_brigadista($_idMembro = 0)
{
    global $conn;
    $consulta = "SELECT B.id, P.nome
                    FROM rh_brigadistas B
                    INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                    WHERE data_final is null ORDER BY P.nome";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='idMembro' name='idMembro'>";
    $html .= "<option value='0'>Selecione um brigadista...</option>";
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
    $consulta = "SELECT * from rh_brigada_tipo_ocorrencia";
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

//-- Função para carregar a lista de BRIGADISTAS
//
function seletor_membros_reuniao($_idMembro = 0)
{
    global $conn;
    $consulta = "SELECT B.id, P.nome
                    FROM rh_brigadistas B
                    INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                    WHERE data_final is null ORDER BY P.nome";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='brigadista' name='brigadista'>";
    //$html .= "<option value='0'>Selecione um brigadista...</option>";
    $html .= "<option value='' disabled selected>Selecione um brigadista...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($_idMembro == $row['id']) ? "selected" : "";
        $html .= "<option value='{$row['id']}' $selected>{$row['nome']}</option>";
    }
    $html .= "</select>";
    return $html;
}
