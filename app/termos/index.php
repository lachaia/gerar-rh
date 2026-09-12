<?php
//
//-index.php | Página inicial do módulo de Termos de Responsabilidade
//- (C)haia, 20/08/2025
//

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

$idModulo = 19; // Equipamentos

$token = $_GET['token'] ?? null;

$abasPermitidas = ['home', 'termos', 'modelos'];
$abaAtiva = $_SESSION['termos_aba_ativa'] ?? 'termos';
if (!in_array($abaAtiva, $abasPermitidas, true)) {
    $abaAtiva = 'termos';
}

if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [4, 7, 9], true)) {
    header("Location: login.php");
    exit();
}

include_once "../includes/conexao_gerar.php";

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar - Gestão de Equipamentos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI (CSS e JS) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS do Summernote -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">

    <!-- JS do Summernote + dependência do Bootstrap 5 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>

    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }

        .navbar {
            min-height: 80px;
        }

        main {
            padding-top: 95px;
        }

        .menu-header .nav-link {
            color: #fff;
            border: 1px solid transparent;
            padding: .35rem .8rem;
        }

        .menu-header .nav-link.active {
            background: #fff;
            color: #111;
            border-color: #fff;
        }



        label {
            color: #282626ff;
        }

        .campo {
            background-color: #e9ecefcd;
            color: black;
            min-height: 36px;
        }

        /* Tema escuro para DataTables */
        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #fff !important;
            /* branco */
        }

        /* Campo de pesquisa e select de páginas */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background-color: #222;
            /* fundo escuro */
            color: #fff;
            /* texto branco */
            border: 1px solid #555;
            /* borda discreta */
        }

        #view_html {
            height: 600px;
            overflow: auto;
        }

        .note-editable {
            color: black !important;
            /* Define a cor do texto como branco */
            background-color: white;
            /* Opcional: define um fundo escuro para melhor contraste */
        }

        .ui-autocomplete {
            z-index: 1056 !important;
            /* Bootstrap modal geralmente usa até 1055 */
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
        }

        #vw_termo_vistoria{
            height: 100px;
            overflow: auto;
        }
    </style>
</head>

<body>
    <!-- Barra superior -->
    <nav class="navbar navbar-dark bg-black fixed-top px-3">
        <div class="container-fluid position-relative d-flex justify-content-between align-items-center">

            <!-- Logo e nome -->
            <div class="d-flex align-items-center">
                <img src="../imagens/logo.png" alt="Logo Gerar" height="35" class="me-2">
                <span class="fw-bold text-white">Gerar | <?= $_SESSION['nmLogin'] ?></span>
            </div>

            <!-- Título centralizado -->
            <div class="position-absolute start-50 translate-middle-x">
                <ul class="nav nav-tabs menu-header border-0">
                    <li class="nav-item">
                        <a class="nav-link <?= ($abaAtiva === 'termos') ? 'active' : '' ?>" data-bs-toggle="tab" href="#termos">Termos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($abaAtiva === 'home') ? 'active' : '' ?>" data-bs-toggle="tab" href="#home">Solicitações</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($abaAtiva === 'modelos') ? 'active' : '' ?>" data-bs-toggle="tab" href="#modelos">Modelos</a>
                    </li>
                </ul>
            </div>

            <!-- Botão de logout -->
            <div>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
            </div>

        </div>
    </nav>

    <main
        <div class="tab-content">

        <!-- Conteúdo da aba SOLICITAÇÕES -->
        <div class="tab-pane container fade <?= ($abaAtiva === 'home') ? 'show active' : '' ?>" id="home">
            <div class="container">
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="card-header h4 text-center m-4">
                            <i class="fas fa-table me-1"></i> Solicitações de Equipamentos
                        </div>
                        <div class="card-body">
                            <table id="tabelaSolicitacoes" class="table table-dark table-striped table-bordered w-100 nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Responsável</th>
                                        <th>Usuário Final</th>
                                        <th>Equipamentos</th>
                                        <th>GLPI ID</th>
                                        <th><i class="fa-solid fa-magnifying-glass"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Os dados serão preenchidos pelo DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo da aba TERMOS -->
        <div class="tab-pane container fade <?= ($abaAtiva === 'termos') ? 'show active' : '' ?>" id="termos">
            <div class="container">
                <div class="row mt-1">
                    <div class="col-sm-12 mx-auto">
                        <div class="card-header d-flex justify-content-between align-items-center mb-3">
                            <div class="flex-grow-1 text-center h3">
                                <i class="fas fa-table me-1"></i> TERMOS DE RESPONSABILIDADE / COMPROMISSOS
                            </div>
                            <button class="btn btn-primary btn-sm m-2" onClick='f_incluir_termo()'>
                                <i class="fas fa-plus"></i> Incluir
                            </button>
                        </div>
                        <div class="card-body">
                            <table id="tabelaTermos" class="table table-dark table-striped table-bordered w-100 nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Responsável</th>
                                        <th>Devolvido em</th>
                                        <th>Recebido por</th>
                                        <th>Status</th>
                                        <th><i class="fa-brands fa-font-awesome"></i></th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Os dados serão preenchidos pelo DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo da aba MODELOS -->
        <div class="tab-pane container fade <?= ($abaAtiva === 'modelos') ? 'show active' : '' ?>" id="modelos">
            <div class="container">
                <div class="row mt-1">
                    <div class="col-sm-8 mx-auto"> <!-- <= largura reduzida e centralizada -->
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1 text-center h3">
                                <i class="fas fa-table me-1"></i> MODELOS de TERMOS
                            </div>
                            <button class="btn btn-primary btn-sm m-2" onClick='f_incluir_modelo()'>
                                <i class="fas fa-plus"></i> Incluir
                            </button>
                        </div>

                        <div class="card-body mt-1">
                            <table id="tabelaModelos" class="table table-dark table-striped table-bordered w-100 text-center nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Modelo</th>
                                        <th>Qtd</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Os dados serão preenchidos pelo DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>

        <!-- The Modal VISUALISAR Solicitação-->
        <div class="modal fade" id="modalVerSolic" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-secondary text-white">
                        <h4 class="modal-title"><i class="fa-solid fa-eye"></i> Detalhes da Solicitação</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="fw-bold">Responsável pelo termo</label>
                                <p id="view_responsavel" class="form-control-plaintext border rounded px-2"></p>
                            </div>

                            <div class="col-sm-12 mt-3">
                                <label class="fw-bold">Usuário final dos equipamentos</label>
                                <p id="view_usuario" class="form-control-plaintext border rounded px-2"></p>
                            </div>

                            <div class="col-sm-12 mt-3">
                                <label class="fw-bold">Equipamentos necessários</label>
                                <p id="view_equipamentos" class="form-control-plaintext border rounded px-2"></p>
                            </div>

                            <div class="col-sm-12 mt-3">
                                <label class="fw-bold">Observações</label>
                                <p id="view_observacoes" class="form-control-plaintext border rounded px-2"></p>
                            </div>
                            <div class="col-sm-4 mt-3">
                                <label class="fw-bold">Criado em</label>
                                <p id="view_criado_em" class="form-control-plaintext border rounded px-2 text-center"></p>
                            </div>
                            <div class="col-sm-4 mt-3">
                                <label class="fw-bold">Criado Por</label>
                                <p id="view_criado_por" class="form-control-plaintext border rounded px-2 text-center"></p>
                            </div>
                            <div class="col-sm-4 mt-3">
                                <label class="fw-bold">ID GLPI</label>
                                <p id="view_glpi" class="form-control-plaintext border rounded px-2 text-center"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Fechar</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- The Modal INCLUIR TERMOS -->
        <div class="modal fade" id="modalIncTermos" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-secondary text-white">
                        <h4 class="modal-title"><i class="fa-regular fa-file-word"></i> INCLUSÃO DE TERMO</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <form id="formIncTermo" name="formIncTermo">
                            <div class="row">
                                <div class="col-sm-2">
                                    <label class="fw-bold">Nº da Reserva</label>
                                    <div class="input-group">
                                        <input type="text" id="inc_termo_reserva" name="inc_termo_reserva" class="form-control text-center" placeholder="nro...">
                                        <button class="btn btn-outline-secondary" type="button" id="btnBuscarReserva" onclick='buscarReservaEquip()'>
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <label class="fw-bold">Nome da Pessoa Responsável</label>
                                    <input type="text" id="termo_inc_nome" name="termo_inc_nome" class="form-control" placeholder="Comece a digitar o nome da pessoa...">
                                    <input type="hidden" id="termo_idPessoa" name="termo_idPessoa">
                                </div>
                                <div class="col-sm-2">
                                    <label class="fw-bold">GLPI ID</label>
                                    <p id="termo_glpi" class="form-control campo text-center"></p>
                                </div>
                                <div class="col-sm-2 mt-2">
                                    <label class="fw-bold">Modelo</label>
                                    <?= seletor_modelo_termo("termo_idModelo", "termo_idModelo") ?>
                                </div>
                                <div class="col-sm-2 mt-2">
                                    <label class="fw-bold">CPF</label>
                                    <input type="text" id="termo_cpf" name="termo_cpf" class="form-control" placeholder="CPF" maxlength="11" onchange='testar_cpf(this)'>
                                </div>
                                <div class="col-sm-8 mt-2">
                                    <label class="fw-bold">Lista de Equipamentos solicitados</label>
                                    <p id="termo_lista" class="form-control campo text-center"></p>
                                </div>
                            </div>
                            <div class='row' id='divEndereco'>
                                <div class="col-sm-2 mt-2">
                                    <label class="fw-bold">CEP</label>
                                    <input type="text" id="termo_cep" name="termo_cep" class="form-control" placeholder="CEP" onchange='pesquisarCEP()'>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <label class="fw-bold">Endereço</label>
                                    <input type="text" id="termo_endereco" name="termo_endereco" class="form-control" placeholder="Logradouro...">
                                </div>
                                <div class="col-sm-1 mt-2">
                                    <label class="fw-bold">nº</label>
                                    <input type="text" id="termo_end_nro" name="termo_end_nro" class="form-control" placeholder="nº">
                                </div>
                                <div class="col-sm-3 mt-2">
                                    <label class="fw-bold">Complemento</label>
                                    <input type="text" id="termo_end_cpl" name="termo_end_cpl" class="form-control" placeholder="apto, conj...">
                                </div>
                                <div class="col-sm-3 mt-2">
                                    <label class="fw-bold">Bairro</label>
                                    <input type="text" id="termo_bairro" name="termo_bairro" class="form-control" placeholder="Bairro...">
                                </div>
                                <div class="col-sm-8 mt-2">
                                    <label class="fw-bold">Cidade</label>
                                    <input type="text" id="termo_cidade" name="termo_cidade" class="form-control" placeholder="Cidade...">
                                </div>
                                <div class="col-sm-1 mt-2">
                                    <label class="fw-bold">UF</label>
                                    <input type="text" id="termo_uf" name="termo_uf" class="form-control text-center" placeholder="">
                                </div>

                                <div class="col-sm-6 mt-2">
                                    <label class="fw-bold">e-Mail</label>
                                    <input type="text" id="termo_email" name="termo_email" class="form-control" placeholder="e-Mail...">
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <label class="fw-bold">Telefone</label>
                                    <input type="text" id="termo_telefone" name="termo_telefone" class="form-control" placeholder="telefone...">
                                </div>
                            </div>

                            <div class="row mt-3" id='divObservacoes' style="display:none;">
                                <div class="col-sm-12">
                                    <label class="fw-bold">Observações da Vistoria</label>
                                    <textarea id="termo_observacoes" name="termo_observacoes" class="form-control" placeholder="Observações..."></textarea>
                                </div>
                            </div>

                            <div class="row mt-3" id='divBaixa' style="display:none;">
                                <div class="col-sm-6">
                                    <label class="fw-bold">Data da devolução</label>
                                    <input type="datetime-local" id="termo_data_dev" name="termo_data_dev" class="form-control text-center" value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="fw-bold">Usuário Vistoriador</label>
                                    <input type="text" id="termo_user_dev" name="termo_user_dev" class="form-control text-center" value="<?= $_SESSION['nmLogin'] ?>" maxlength="45">
                                </div>
                                <div class="col-sm-12">
                                    <label class="fw-bold">Vistoria da devolução</label>
                                    <textarea id="termo_observacoes_dev" name="termo_observacoes_dev" class="form-control" placeholder="informe aqui como foi a devolução dos equipamentos..."></textarea>
                                </div>
                            </div>

                            <div class="row border-top mt-3 pt-3" id='divItensTermo'>
                                <div class="clearfix">
                                    <div class="h5 float-start text-dark">
                                        <i class="fa-solid fa-file-lines"></i> Itens do Termo:
                                    </div>
                                    <div class="h5 float-end">
                                        <div class="btn btn-outline-secondary btn-sm" onclick='f_add_item_termo()'>
                                            <i class="fa-solid fa-plus"></i> Adicionar Item
                                        </div>
                                    </div>
                                </div>

                                <div id="itens_termo">
                                    <!-- Primeiro item já visível -->
                                    <div class="row g-2 mt-2 item-termo">
                                        <div class="col-sm-4">
                                            <label class="fw-bold">Tipo</label>
                                            <?= seletorTipoEquipamento() ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="fw-bold">Modelo</label>
                                            <input type="text" class='form-control' name='modelo[]' placeholder="Modelo...">
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="fw-bold">Patrimônio/Serial</label>
                                            <input type="text" class='form-control' name='patrimonio[]' placeholder="Patrimônio...">
                                        </div>
                                        <div class="col-sm-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="remover_item(this)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3" id='divUpload'>
                                <div class="col-sm-8">
                                    <label class="fw-bold">Termo Já Existente para Upload</label>
                                    <input type="file" id="termo_arquivo" name="termo_arquivo" class="form-control">
                                </div>
                                <div class="col-sm-2">
                                    <label class="fw-bold">Dt. Entrega</label>
                                    <input type="datetime-local" id="termo_dt_entrega" name="termo_dt_entrega" class="form-control text-center" value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="col-sm-2">
                                    <label class="fw-bold">Status</label>
                                    <select name="termo_status" id="termo_status" class="form-select" onchange='f_select_status_termo(this)'>
                                        <option value="">Selecione...</option>
                                        <option value="Assinado">Assinado</option>
                                        <option value="Baixado">Baixado</option>
                                        <option value="">Novo</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer" id="divBotoesTermo">
                        <div class="row w-100">
                            <div class="col">
                                <button type="button" class="btn btn-outline-secondary w-100"
                                    onclick="f_adicionar_vistoria()" id="botaoAdicionarVistoria">
                                    Adicionar Vistoria
                                </button>
                                <button type="button" class="btn btn-outline-secondary w-100"
                                    onclick="f_mostrar_endereco()" id="botaoVoltarTermo" style="display:none;">
                                    Voltar
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-warning w-100"
                                    onclick="f_dados_baixa()" id="botaoDadosBaixa" style="display:none;">
                                    Dados da Baixa
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-danger w-100" data-bs-dismiss="modal">
                                    Fechar
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="f_incluir_termo_salvar()">
                                    Enviar
                                </button>
                            </div>
                        </div>
                    </div>


                    <div id='msgAlertaTermo' class="text-center h5"></div>

                </div>
            </div>
        </div>

        <!-- The Modal ALTERAR TERMOS -->
        <div class="modal fade" id="modalAltTermos" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h4 class="modal-title"><i class="fa-solid fa-pen-to-square"></i> ALTERAÇÃO DE TERMO</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form id="formAltTermo" name="formAltTermo">
                            <input type="hidden" id="alt_id_termo" name="id">

                            <div class="row">
                                <div class="col-sm-2">
                                    <label class="fw-bold">Nº da Reserva</label>
                                    <input type="text" id="alt_termo_reserva" name="alt_termo_reserva" class="form-control text-center" readonly>
                                </div>
                                <div class="col-sm-8">
                                    <label class="fw-bold">Nome da Pessoa Responsável</label>
                                    <input type="text" id="alt_termo_nome" name="alt_termo_nome" class="form-control" readonly>
                                    <input type="hidden" id="alt_termo_idPessoa" name="alt_termo_idPessoa">
                                </div>
                                <div class="col-sm-2">
                                    <label class="fw-bold">GLPI ID</label>
                                    <input type="text" id="alt_termo_glpi" class="form-control text-center" readonly>
                                </div>

                                <div class="col-sm-2 mt-2">
                                    <label class="fw-bold">Modelo</label>
                                    <?= seletor_modelo_termo("alt_termo_idModelo", "alt_termo_idModelo") ?>
                                </div>
                                <div class="col-sm-2 mt-2">
                                    <label class="fw-bold">CPF</label>
                                    <input type="text" id="alt_termo_cpf" name="alt_termo_cpf" class="form-control" maxlength="11">
                                </div>
                                <div class="col-sm-8 mt-2">
                                    <label class="fw-bold">e-Mail</label>
                                    <input type="text" id="alt_termo_email" name="alt_termo_email" class="form-control">
                                </div>
                            </div>

                            <div class='row mt-2'>
                                <div class="col-sm-2">
                                    <label class="fw-bold">CEP</label>
                                    <input type="text" id="alt_termo_cep" name="alt_termo_cep" class="form-control" onchange='pesquisarCEP("alt")'>
                                </div>
                                <div class="col-sm-6">
                                    <label class="fw-bold">Endereço</label>
                                    <input type="text" id="alt_termo_endereco" name="alt_termo_endereco" class="form-control">
                                </div>
                                <div class="col-sm-1">
                                    <label class="fw-bold">nº</label>
                                    <input type="text" id="alt_termo_end_nro" name="alt_termo_end_nro" class="form-control">
                                </div>
                                <div class="col-sm-3">
                                    <label class="fw-bold">Bairro</label>
                                    <input type="text" id="alt_termo_bairro" name="alt_termo_bairro" class="form-control">
                                </div>
                            </div>

                            <div class="row border-top mt-3 pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="h5 mb-0" style="color:#343a40 !important;"><i class="fa-solid fa-file-lines"></i> Itens do Termo:</div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="f_add_item_alteracao()">
                                        <i class="fa-solid fa-plus"></i> Adicionar Item
                                    </button>
                                </div>
                                <div id="alt_itens_termo">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-sm-8">
                                    <label class="fw-bold">Substituir Arquivo (Opcional)</label>
                                    <input type="file" id="alt_termo_arquivo" name="alt_termo_arquivo" class="form-control">
                                </div>
                                <div class="col-sm-4">
                                    <label class="fw-bold">Status</label>
                                    <select name="alt_termo_status" id="alt_termo_status" class="form-select">
                                        <option value="Pendente">Pendente</option>
                                        <option value="Assinado">Assinado</option>
                                        <option value="Baixado">Baixado</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="f_salvar_alteracao_termo()">Salvar Alterações</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- The Modal INCLUIR MODELO de TERMO -->
        <div class="modal fade" id="modalIncModelo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-secondary text-white">
                        <h4 class="modal-title"><i class="fa-regular fa-file-word"></i> MODELO DE TERMO</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="row">
                            <form id='formIncModelo' name="id='formIncModelo'">
                                <div class="col-sm-12">
                                    <label class="fw-bold">Nome do Termo (ex: Responsabilidade)</label>
                                    <input type="text" id="inc_nome" name='inc_nome' class="form-control" placeholder="Digite o nome do modelo">
                                </div>
                                <div class="col-sm-12 mt-3">
                                    <label class="fw-bold">Conteúdo do Termo</label>
                                    <textarea name="inc_html" id="inc_html" class='form-control' placeholder="Escreva aqui as cláusulas do termo..."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer" id='divBotoesModelo'>
                        <div class="row w-100">
                            <div class="col">
                                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                                    Fechar
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-primary w-100" onclick='f_incluir_modelo_salvar()'>
                                    Salvar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id='msgAlertaModelo' class="text-center h5"></div>

                </div>
            </div>
        </div>

        <!-- The Modal VISUALIZAR MODELO TERMO -->
        <div class="modal fade" id="modalVerModelo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-secondary text-white">
                        <h4 class="modal-title text-center">
                            <i class="fa-solid fa-eye"></i> VISUALIZAÇÃO DO MODELO DE TERMO
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="mb-3">
                            <h5 class="fw-bold text-dark text-center" id="view_nome">Nome do Termo</h5>
                        </div>
                        <div id="view_html" class="border rounded p-3 text-dark" style="min-height:200px; background:#f8f9fa;">
                            <!-- Conteúdo HTML do termo será inserido aqui via JS -->
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                            Fechar
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- The Modal EDITAR MODELO de TERMO -->
        <div class="modal fade" id="modalAltModelo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-primary text-white">
                        <h4 class="modal-title"><i class="fa-regular fa-file-word"></i> EDITAR MODELO DE TERMO</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="row">
                            <form id="formEditModelo" name="formEditModelo">
                                <input type="hidden" id="edit_id" name="edit_id"> <!-- ID do modelo para edição -->
                                <div class="col-sm-12">
                                    <label class="fw-bold">Nome do Termo</label>
                                    <input type="text" id="edit_nome" name="edit_nome" class="form-control" placeholder="Digite o nome do modelo">
                                </div>
                                <div class="col-sm-12 mt-3">
                                    <label class="fw-bold">Conteúdo do Termo</label>
                                    <textarea name="edit_html" id="edit_html" class="form-control" placeholder="Edite as cláusulas do termo..."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer" id="divBotoesAltModelo">
                        <div class="row w-100">
                            <div class="col">
                                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-success w-100" onclick="f_editar_modelo_salvar()">
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="msgAlertaAltModelo" class="text-center h5"></div>

                </div>
            </div>
        </div>

        <!-- The Modal VISUALISAR TERMOS-->
        <div class="modal fade" id="modalVerTermo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-secondary text-white">
                        <h4 class="modal-title"><i class="fa-solid fa-eye"></i> Detalhes do Termo</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="row">
                            <!-- Coluna esquerda (campos de visualização) -->
                            <div class="col-sm-8">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="fw-bold">Responsável pelo termo</label>
                                        <p id="vw_termo_responsavel" class="form-control-plaintext border rounded px-2"></p>
                                    </div>
                                    <div class="col-sm-12">
                                        <label class="fw-bold">Endereço</label>
                                        <p id="vw_termo_endereco" class="form-control-plaintext border rounded px-2"></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-bold">e-Mail</label>
                                        <p id="vw_termo_email" class="form-control-plaintext border rounded px-2"></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-bold">Celular</label>
                                        <p id="vw_termo_celular" class="form-control-plaintext border rounded px-2"></p>
                                    </div>

                                    <div class="col-sm-12 mt-3">
                                        <label class="fw-bold">Equipamentos entregues</label>
                                        <p id="vw_termo_equipamentos" class="form-control-plaintext border rounded px-2"></p>
                                    </div>

                                    <div class="col-sm-4 mt-3">
                                        <label class="fw-bold">Criado em</label>
                                        <p id="vw_termo_criado_em" class="form-control-plaintext border rounded px-2 text-center"></p>
                                    </div>
                                    <div class="col-sm-4 mt-3">
                                        <label class="fw-bold">Criado Por</label>
                                        <p id="vw_termo_criado_por" class="form-control-plaintext border rounded px-2 text-center"></p>
                                    </div>
                                    <div class="col-sm-4 mt-3">
                                        <label class="fw-bold">Status</label>
                                        <p id="vw_termo_status" class="form-control-plaintext border rounded px-2 text-center"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4 mt-3">
                                        <label class="fw-bold">Devolvido em</label>
                                        <p id="vw_termo_recebido_em" class="form-control-plaintext border rounded px-2 text-center"></p>
                                    </div>
                                    <div class="col-sm-4 mt-3">
                                        <label class="fw-bold">Recepcionado Por</label>
                                        <p id="vw_termo_recebido_por" class="form-control-plaintext border rounded px-2 text-center"></p>
                                    </div>
                                    <div class="col-sm-4 mt-3">
                                        <label class="fw-bold">Arquivo</label>
                                        <p id="vw_termo_arquivo" class="form-control-plaintext border rounded px-2 text-center"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Coluna direita (preview do documento) -->
                            <div class="col-sm-4 text-center">
                                <div class="row">
                                    <div class="col">
                                        <label class="fw-bold">Documento Anexado</label>
                                        <div id="view_documento" class="border rounded p-2" style="min-height: 200px;">
                                            <!-- Aqui você pode injetar via JS:
                                            <embed src="arquivo.pdf" type="application/pdf" width="100%" height="400px" />
                                            ou <img src="imagem.jpg" class="img-fluid" />
                                            ou até um link -->
                                        </div>
                                        <input type="hidden" id="vw_termo_documento">
                                        <button type="button" class="btn btn-outline-primary w-100 mt-3" onclick="f_preview_documento()">Ver</button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col">
                                        <label class="fw-bold">Laudo</label>
                                        <p id="vw_termo_vistoria" class="form-control-plaintext border rounded px-2 text-center"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="row w-100">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-danger w-100" id="btnExcluirTermo">
                                    <i class="fa-solid fa-trash-can"></i> Excluir
                                </button>
                            </div>
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-warning w-100" id="btnEditarTermo">
                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                </button>
                            </div>
                            <div class="col-sm-6 text-end">
                                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- The Modal BAIXAR TERMOS-->
        <div class="modal fade" id="modalBxaTermo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-secondary text-white">
                        <h4 class="modal-title"><i class="fa-solid fa-people-carry-box"></i> DEVOLUÇÃO DOS EQUIPAMENTOS</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-7">
                                <label class="fw-bold">Responsável pelo termo</label>
                                <p id="bxa_termo_responsavel" class="form-control-plaintext border rounded px-2 text-center"></p>
                            </div>
                            <div class="col-sm-3">
                                <label class="fw-bold">Data Devolução</label>
                                <input type="datetime-local" id="bxa_termo_data_dev" name="bxa_termo_data_dev" class="form-control text-center" value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                            <div class="col-sm-2">
                                <label class="fw-bold">Vistoriador</label>
                                <input type='text' id="bxa_termo_vistoriador" name="bxa_termo_vistoriador" class="form-control text-center" maxlength="45" value="<?= $_SESSION['nmLogin'] ?>">
                            </div>
                            <div class="col-sm-12 mt-3">
                                <label class="fw-bold">Observações da Vistoria</label>
                                <textarea id="bxa_termo_observacoes_dev" name="bxa_termo_observacoes_dev" class="form-control" placeholder="informe aqui como foi a devolução dos equipamentos..."></textarea>
                                <input type="hidden" id="bxa_termo_id" name="bxa_termo_id">
                                <input type="hidden" id="bxa_termo_origem" name="bxa_termo_origem">
                            </div>
                            <div class="col-sm-12" id='divUploadBxaTermo'>
                                <label class="fw-bold">Termo Já Assinado para Upload</label>
                                <input type="file" id="termo_arquivo_bxa" name="termo_arquivo_bxa" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer d-flex gap-2" id='botoes_bxa_termo'>
                        <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                            Fechar
                        </button>
                        <button type="button" class="btn btn-outline-primary flex-fill" onclick="f_incluir_bxa_termo_salvar()">
                            Enviar
                        </button>
                    </div>
                    <div class="text-center h5 text-dark" id='msgAlertaBxaTermo'></div>

                </div>
            </div>
        </div>

        <!-- Modal para digitar usuário/senha -->
        <div class="modal fade" id="modalAssTermo" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered custom-width">
                <div class="modal-content rounded-4 shadow" style='background-color: #8fa7beff'>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-center">Confirmar Assinatura</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <form id="formLogin">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuário</label>
                                <input type="text" class="form-control text-center" id="usuario" required>
                                <input type="hidden" id='idTermo' name='idTermo' value='<?= $idTermo ?>'>
                                <input type="hidden" id='token' name='token' value='<?= $token ?>'>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="senha" class="form-label">Senha</label>
                                <div class="input-group">
                                    <input type="password" class="form-control text-center" id="senha" required>
                                    <button type="button" class="btn btn-outline-secondary" id="toggleSenha">
                                        <i class="fa fa-eye" id="iconSenha"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id='msgAlerta' class="h5 text-center"></div>
                    </div>
                    <div class="modal-footer d-flex gap-2">
                        <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal" onclick='fechar_assinatura()'>Cancelar</button>
                        <button type="button" class="btn btn-success flex-fill" id="btnConfirmar" onclick='confirma_assinatura()'>Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="index.js"></script>
    <script src="../js/cpf.js"></script>
</body>

</html>
<?PHP

function seletor_modelo_termo($id = "termo_idModelo", $name = "termo_idModelo")
{
    global $conn;

    $sql = "SELECT id, nome FROM rh_equip_modelos ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $options = '<select id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '" class="form-select">
                    <option value="" selected>-- selecione um modelo --</option>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $options .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome']) . '</option>';
    }
    $options .= '</select>';

    return $options;
}

function seletorTipoEquipamento()
{
    global $conn;

    $sql = "SELECT id, descricao FROM rh_equip_tipos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $options = '<select name="idTipoEquip[]" class="form-select">
                    <option value="" selected>-- selecione um tipo --</option>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $options .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['descricao']) . '</option>';
    }
    $options .= '</select>';

    return $options;
}
