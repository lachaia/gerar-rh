<?php
//
// - g_usuarios.php  -  Grade de manutenção do Cadastro de Usuários (Inclui, Altera, Exclui, Conculta)
// - (C) Chaia, 18/08/2023
//

$idModulo = 1; // rh_usuarios.php

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
    f_log("CON", "Consulta grade de Usuários do Sistema", "rh_usuarios", $idModulo, 0);
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tabela de Usuários do Sistema" />
    <meta name="author" content="LAChaia" />
    <title>Gerar: Usuários</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_usuarios.css" rel="stylesheet" />

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                    <div class="card mb-2 mt-2">
                        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
                            <div class="d-flex">
                                <i class="fa-solid fa-users-gear"></i>
                                <h5>&nbsp;Usuários do Sistema</h5>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="f_incluir()">Incluir</button>
                        </div>
                        <div class="card-body">
                            <div id="msgAlert"></div>
                            <table id="example" class="table table-striped table-hover table-bordered fb-8">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>Empresa</th>
                                        <th><sup>1</sup>Grupo</th>
                                        <th><sup>2</sup>ID</th>
                                        <th><sup>3</sup>Login</th>
                                        <th><sup>4</sup>ID Pessoa</th>
                                        <th><sup>5</sup>Nome</th>
                                        <th><sup>6</sup>CPF</th>
                                        <th><sup>7</sup>Última</th>
                                        <th><sup>8</sup>Ativo</th>
                                        <th><sup>9</sup>P</th>
                                        <th><sup>10</sup>Entradas</th>
                                        <th><sup>11</sup>Operações</th>
                                        <th><sup>12</sup>SubSede</th>
                                        <th><sup>13</sup>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- The Modal Visualizar Usuário -->
            <div class="modal fade" id="modalVisualisar" tabindex="-1" aria-labelledby="visUsuarioModalLabel" aria-hidden="true">
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
                                        <dd class="col-sm-3 border-bottom"><span id="idPessoa"></span></dd>
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
                                    <img id="fichaFoto" src="fotos/perfil.png" alt="Foto do usuário" class="img-fluid rounded shadow" style="max-height: 350px; object-fit: cover;">
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
            <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="incUsuarioModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="incUsuarioModalLabel">
                                <h5>Inclusão de Usuário</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <span id="msgAlertErroUsuInc"></span>
                            <form id="form-cad-usuario" enctype="multipart/form-data" method="post">
                                <div class="row mb-3">
                                    <label for="nome" class="col-sm-2 col-form-label">Grupo</label>
                                    <div class="col-sm-10" id='idSeletorGrupo'>
                                        <?php echo grupoUsuarios(0); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="idSeletorEmpresa" class="col-sm-2 col-form-label">Empresa</label>
                                    <div class="col-sm-10" id='idSeletorEmpresa'>
                                        <?php echo select_empresas(1); ?>
                                    </div>
                                </div>

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
                                    <label class="col-sm-2 col-form-label">Membros:</label>
                                    <div class="col-sm-10 d-flex justify-content-center gap-5">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="chkCipa" name="chkCipa" value='0' onchange='membros(this)'>
                                            <label class="form-check-label" for="chkCipa">CIPA</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="chkBrigada" name="chkBrigada" value='0' onchange='membros(this)'>
                                            <label class="form-check-label" for="chkBrigada">Brigada</label>
                                        </div>
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
                                    <label for="inputChave" class="col-sm-2 col-form-label">Chave</label>
                                    <div class="col-sm-10">
                                        <input type="text" id="inputChave" name="inputChave" class="form-control text-center" placeholder="chave app gmail">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="btn-group">
                                        <div class="col-sm-2"></div>
                                        <button type="button" class="btn btn-success btn-sm rounded" onclick="btnIncSalvarUsuario()"><i class="fa fa-upload"></i> Salvar</button>
                                        <button type="reset" class="btn btn-secondary btn-sm rounded" id="btnIncReset"><i class="fa fa-recycle"></i> Reset</button>
                                        <button type="button" class="btn btn-danger btn-sm rounded" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Fechar</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The Modal ALTERAR Usuário -->
            <div class="modal fade" id="modalAlterar" tabindex="-1" aria-labelledby="altUsuarioModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="altUsuarioModalLabel">
                                <h5>Alterar dados de Usuário</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <span id="msgAlertErroUsuAlt"></span>
                            <form id="form-alt-usuario" enctype="multipart/form-data" method="post">
                                <input type="hidden" id="idAltUsuario" name="idAltUsuario" value="">
                                <div class="row mb-3">
                                    <label for="idSeletorAltGrupo" class="col-sm-2 col-form-label">Grupo</label>
                                    <div class="col-sm-10">
                                        <span id="idSeletorAltGrupo">
                                            <input type="text" class="form-control border-danger" value="" placeholder="carregando...">
                                        </span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="idSeletorPessoas" class="col-sm-2 col-form-label">Pessoa</label>
                                    <div class="col-sm-10">
                                        <span id="idAltSeletorPessoas">
                                            <select class='form-control danger' style="border-color: red">
                                                <option>Carregando...</option>
                                            </select>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="pessoa" class="col-sm-2 col-form-label">Colab.:</label>
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
                                    <label for="inputAltLogin" class="col-sm-2 col-form-label">Login</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="inputAltLogin" value="" class="form-control border-danger" id="inputAltLogin" placeholder="Login do usuário...">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="inputAltSenha" class="col-sm-2 col-form-label">Senha</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="inputAltSenha" value="" id="inputAltSenha" placeholder="NOVA Senha de acesso...">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class='col-sm-1'></div>
                                    <div class="col-sm-10">
                                        <div class="d-flex justify-content-between align-items-center w-100 px-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="inputAltAtivo" name="inputAltAtivo" value="SIM" checked onclick="testeInputAtivo()">
                                                <label class="form-check-label" for="inputAltAtivo">
                                                    <span id="textoAltAtivo">Usuário ativo</span>
                                                </label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="inputCheckCipa" name="inputCheckCipa" value="0" onchange="membros(this)">
                                                <label class="form-check-label" for="inputCheckCipa">CIPA</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="inputChkBrigada" name="inputChkBrigada" value="0" onchange="membros(this)">
                                                <label class="form-check-label" for="inputChkBrigada">Brigada</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-sm-1'></div>
                                </div>

                                <div class="row mb-3">
                                    <label for="inputAltFoto" class="col-sm-2 col-form-label">Foto</label>
                                    <div class="col-sm-10">
                                        <input type="file" id="inputAltFoto" name="inputAltFoto" class="form-control">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="inputAltChave" class="col-sm-2 col-form-label">Chave</label>
                                    <div class="col-sm-10">
                                        <input type="text" id="inputAltChave" name="inputAltChave" class="form-control text-center" placeholder="chave app gmail">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="btn-group">
                                        <div class="col-sm-2"></div>
                                        <button type="button" class="btn btn-success btn-sm rounded" onclick="btnAltSalvarUsuario()"><i class="fa fa-upload"></i> Salvar</button>
                                        <button type="reset" class="btn btn-secondary btn-sm rounded" id="btnResetAltUsuario><i class=" fa fa-recycle"></i> Reset</button>
                                        <button type="button" class="btn btn-danger btn-sm rounded" data-bs-dismiss="modal" value="Fechar"><i class="fa fa-close"></i> Fechar</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The Modal INCLUIR Novo Grupo -->
            <div class="modal fade" id="modalIncluirGrupo" tabindex="-1" aria-labelledby="incPessoaGrupoLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background-color: #92a8d1;">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="incPessoaModalLabel">
                                <h5>Inclusão de GRUPO DE USUÁRIOS</h5>
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <span id="msgAlertErroCadPessoa"></span>
                            <form method="POST" id="form-inc_grupo">
                                <div class="row mb-3">
                                    <label for="g_descricao" class="col-sm-3 col-form-label">Nome</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="g_descricao" value="" class="form-control" id="g_descricao" placeholder="Nome do Grupo">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="g_sigla" class="col-sm-3 col-form-label">Sigla</label>
                                    <div class="col-sm-3">
                                        <input type="text" name="g_sigla" value="" maxlength=3 class="form-control" id="g_sigla" placeholder="Sigla do Grupo..">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="btn-group">
                                        <div class="col-sm-2"></div>
                                        <button type="button" class="btn btn-success btn-sm rounded" value="Cadastrar" onclick="f_incluiGrupo_commit()"><i class="fa fa-upload"></i> Salvar</button>
                                        <button type="reset" class="btn btn-secondary btn-sm rounded" id="btnResetIncGrupo"><i class="fa fa-recycle"></i> Reset</button>
                                        <button type="button" class="btn btn-danger btn-sm rounded" data-bs-dismiss="modal" value="Fechar" id="btnFecharIncGrupo"><i class="fa fa-close"></i> Fechar</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <!-- The Modal Incluir Nova Pessoa -->
            <div class="modal fade" id="modalIncluirPessoa" tabindex="-1" aria-labelledby="incPessoaModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background-color: #92a8d1;">
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
                                        <div class="col-sm-2"></div>
                                        <button type="button" class="btn btn-success btn-sm rounded" value="Cadastrar" onclick="btnIncSalvarPessoa()"><i class="fa fa-upload"></i> Salvar</button>
                                        <button type="reset" class="btn btn-secondary btn-sm rounded" id="btnResetIncPessoa"><i class="fa fa-recycle"></i> Reset</button>
                                        <button type="button" class="btn btn-danger btn-sm rounded" data-bs-dismiss="modal" value="Fechar" id="btnFecharIncPessoa"><i class="fa fa-close"></i> Fechar</button>
                                    </div>
                                </div>
                                <h2 id="msgAlertIncPessoa" class="text-center invisivel">Aguarde...</h2>
                            </form>
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
    <script src="js/rh_usuarios.js"></script>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/cpf.js"></script>
</body>

</html>
<?php

function grupoUsuarios($idGrupo = 2)
{
    global $conn;
    global $idEmpresa;
    //
    $consulta = "SELECT idUsuarioGrupo, descricao 
        FROM rh_usuariosgrupo 
        WHERE idEmpresa = $idEmpresa AND ativo=1 
        ORDER BY descricao";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    echo '<div class="input-group">';
    echo "<select class='form-select obrigatorio' id='inputIdUsuarioGrupo' name='inputIdUsuarioGrupo'>";
    echo "<option value='0'>Selecione um Grupo...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        if ($idGrupo == $idUsuarioGrupo) $selected = "selected";
        else $selected = "";
        echo "<option value='$idUsuarioGrupo' $selected>$descricao</option>";
    }
    echo "</select>";
    echo '<a href="#" class="btn btn-outline-primary" onClick="f_incluiGrupo()"><i class="fa-solid fa-plus"></i></a>';
    echo '</div>';
}

//-- Função para carregar a lista de empresas
//
function select_empresas($_idEmpresa)
{
    global $conn;
    global $idEmpresa;
    //
    $consulta = "SELECT idEmpresa, nome 
        FROM rh_empresas 
        WHERE dcAtivo = 1  
        ORDER BY nome";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    echo "<select class='form-select obrigatorio' id='inputIdEmpresa' name='inputIdEmpresa'>";
    echo "<option value='0'>Selecione uma Empresa...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        if ($_idEmpresa == $idEmpresa) $selected = "selected";
        else $selected = "";
        echo "<option value='$idEmpresa' $selected>$nome</option>";
    }
    echo "</select>";
}
