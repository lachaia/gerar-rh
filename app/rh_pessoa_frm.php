<?PHP
//
// - rh_pessoa_frm.php | Formulário de cadastro de pessoas
// (C)haia, 22/02/2025

session_start();

$idModulo = 2; // rh_pessoas

if (!isset($_SESSION['idLogin'])) {
    header("location: logout.php");
} else {
    include_once "includes/conexao_gerar.php";
}

if (isset($_GET['id'])) {
    $idPessoa = $_GET['id'];
} else {
    $idPessoa = 0;
}

$acao_tipo = 'incluir';
if (isset($_GET['edit']) && $_GET['edit'] == '1') $acao_tipo = 'editar';

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Ficha Cadastral" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <script data-cfasync="false" type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        label {
            font-weight: bold;
        }

        .obrigatorio {
            border-color: red;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <!-- Aqui COMEÇA o conteúdo da página -->
            <main class="container-fluid">
                <div class="card">
                    <div class="card-header bg-dark text-light mt-1 mb-1 clearfix">
                        <div class="float-start">
                            <h4 class="card-title"><i class="fa-solid fa-user-plus"></i>&nbsp;<span id='idTipoFormulario'>PESSOAS - INCLUSÃO</span></h4>
                        </div>
                        <div class="float-end">
                            <button type="button" class="btn btn-outline-light btn-sm" onClick="f_voltar()"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</button>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <!-- PRIMEIRA COLUNA -->
                    <div class='col-sm-6'>
                        <div id="msgAlertaPessoa" class="text-center h4"></div>
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'>INFORMAÇÕES PESSOAIS</h5>
                                <div class='float-end'>
                                    <?php
                                    if ($idPessoa > 0) {
                                    ?>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="dcAtivo" name='dcAtivo' onchange="toggleAtivo()" checked value='1'>
                                            <label class="form-check-label" for="toggleAtivo" id="labelAtivo">Ativo</label>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <form id='formPessoa'>
                                    <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $idPessoa ?>'>
                                    <input type='hidden' id='acao_tipo' name='acao_tipo' value='<?= $acao_tipo ?>'>
                                    <div class="row">
                                        <!-- Nome e Nome Social -->
                                        <div class="col-md-6">
                                            <label for="nome" class="form-label">Nome completo</label>
                                            <input type="text" class="form-control mb-3 obrigatorio" id="nome" name='nome' placeholder="Digite o nome completo" 
                                                    onkeyup="document.getElementById('nomeSocial').value = this.value">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="nomeSocial" class="form-label">Nome social</label>
                                            <input type="text" class="form-control mb-3" id="nomeSocial" name='nomeSocial' placeholder="Digite o nome social (se aplicável)">
                                        </div>

                                        <!-- Data de nascimento e Sexo -->
                                        <div class="col-md-4">
                                            <label for="dataNascimento" class="form-label">Data de nascimento</label>
                                            <input type="date" class="form-control mb-3" id="dataNascimento" name='dataNascimento'>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="sexo" class="form-label">Sexo</label>
                                            <select class="form-select mb-3" id="sexo" name='sexo'>
                                                <option value="" selected disabled>Selecione</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Feminino</option>
                                            </select>
                                        </div>

                                        <!-- Estado Civil  -->
                                        <div class="col-md-4">
                                            <?php echo f_estadoCivil(); ?>
                                        </div>
                                        <!-- Nacionalidade  -->
                                        <div class="col-md-4">
                                            <label for="nacionalidade" class="form-label">Nacionalidade</label>
                                            <input type="text" class="form-control mb-3" id="nacionalidade" name='nacionalidade' value='Brasileira'>
                                        </div>

                                        <!-- CPF + RG + PIS-PASEP -->
                                        <div class="col-md-4">
                                            <label for="cpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control mb-3 obrigatorio" id="cpf" name='cpf' placeholder="Digite seu CPF" onblur='testar_cpf(this)'>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="rg" class="form-label">RG (e órgão emissor)</label>
                                            <input type="text" class="form-control mb-3" id="rg" name='rg' placeholder="Digite seu RG e órgão emissor">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="rg" class="form-label">PIS</label>
                                            <input type="text" class="form-control mb-3" id="pis" name='pis' placeholder="Digite seu PIS/PASEP">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="rg" class="form-label">CTPS</label>
                                            <input type="text" class="form-control mb-3" id="ctps" name='ctps' placeholder="Digite a CTPS nº/série">
                                        </div>

                                        <!-- Título de eleitor e Endereço -->
                                        <div class="col-md-4">
                                            <label for="tituloEleitor" class="form-label">Título de eleitor</label>
                                            <input type="text" class="form-control mb-3" id="tituloEleitor" name='tituloEleitor' placeholder="Digite seu título de eleitor">
                                        </div>

                                        <!-- Telefone e E-mail -->
                                        <div class="col-md-3">
                                            <label for="telefone" class="form-label">Telefone</label>
                                            <input type="tel" class="form-control mb-3" id="telefone" name='telefone' placeholder="Digite seu telefone">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">E-mail pessoal</label>
                                            <input type="email" class="form-control mb-3" id="email" name='email' placeholder="Digite seu e-mail">
                                        </div>
                                        <div class="col-md-3">
                                            <?= f_escolaridade(); ?>
                                        </div>

                                        <!-- CNH: Nro, Categoria, Vencimento -->
                                        <div class="col-md-4">
                                            <label for="rg" class="form-label">CNH (Nº)</label>
                                            <input type="text" class="form-control mb-3" id="cnh" name='cnh' placeholder="Número da CNH">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="cnh_categoria" class="form-label">CNH (Categoria)</label>
                                            <input type="text" class="form-control mb-3" id="cnh_categoria" name='cnh_categoria' placeholder="Categoria">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="cnh_vencimento" class="form-label">CNH (Vencimento)</label>
                                            <input type="date" class="form-control mb-3" id="cnh_vencimento" name='cnh_vencimento'>
                                        </div>

                                        <!-- Tamanho de camiseta e Etnia -->
                                        <div class="col-md-3">
                                            <label for="tamanhoCamiseta" class="form-label">Camiseta</label>
                                            <input type="text" class="form-control mb-3" id="tamanhoCamiseta" name='tamanhoCamiseta' placeholder="tamanho?">
                                        </div>
                                        <div class="col-md-3">
                                            <?php echo f_etnias(); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="nome_mae" class="form-label">Nome da Mãe</label>
                                            <input type="text" class="form-control mb-3" id="nome_mae" name='nome_mae' placeholder="Informe onome da mãe">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="btn-group" id="botoes_principal">
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded m-1" onClick="f_voltar()"><i class="fa-solid fa-arrow-left"></i> Voltar</button>
                                            <button type="reset" class="btn btn-outline-warning btn-sm rounded m-1"><i class="fa fa-recycle"></i> Reset</button>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="f_pessoa_commit()"><i class="fa fa-upload"></i> Salvar e Fechar</button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- SEGUNDA COLUNA -->
                    <div class='col-sm-6'>

                        <!-- ENDEREÇOS -->
                        <div class="card mt-2" id='cardEnderecos'>
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium">ENDEREÇOS</h5>
                                <div class="float-end">
                                    <a class="" id="toggleEnd">
                                        <i class="fa-solid fa-chevron-down" id='idSetaEnd'></i>
                                    </a>
                                </div>
                            </div>
                            <span id="msgAlertaEndereco"></span>
                            <div class="card-body container collapse" style="font-size: 13px;" id='bodyEnd'>
                                <form id='formEnderecos'>
                                    <div class="row">
                                        <!-- Tipo de Endereço -->
                                        <div class="col-md-6">
                                            <?php echo f_tipo_endereco(); ?>
                                        </div>

                                        <!-- CEP -->
                                        <div class="col-md-6">
                                            <label for="cep" class="form-label">CEP</label>
                                            <input type="text" class="form-control mb-3 text-center" id="cep" name="cep" placeholder="Somente números" maxlength="8" onBlur='busca_cep(this)'>
                                        </div>

                                        <!-- Logradouro e Número -->
                                        <div class="col-md-8">
                                            <label for="logradouro" class="form-label">Logradouro</label>
                                            <input type="text" class="form-control mb-3" id="logradouro" name="logradouro" placeholder="Rua, Avenida, etc.">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="numero" class="form-label">Número</label>
                                            <input type="text" class="form-control mb-3" id="numero" name="numero" placeholder="Nº">
                                        </div>

                                        <!-- Complemento e Bairro -->
                                        <div class="col-md-6">
                                            <label for="complemento" class="form-label">Complemento</label>
                                            <input type="text" class="form-control mb-3" id="complemento" name="complemento" placeholder="Apto, Bloco, etc.">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="bairro" class="form-label">Bairro</label>
                                            <input type="text" class="form-control mb-3" id="bairro" name="bairro" placeholder="Digite o bairro">
                                        </div>

                                        <!-- Cidade e Estado (UF) -->
                                        <div class="col-md-8">
                                            <label for="cidade" class="form-label">Cidade</label>
                                            <input type="text" class="form-control mb-3" id="cidade" name="cidade" placeholder="Digite a cidade">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="uf" class="form-label">Estado (UF)</label>
                                            <input type="text" class="form-control mb-3 text-center" id="uf" name="uf" maxlength="2">

                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="btn-group" id="botoes_endereco">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" onclick='f_endereco_cancel()'><i class="fa fa-close"></i> Cancelar</button>
                                            <button type="reset" class="btn btn-outline-warning btn-sm rounded m-1" id='btnResetEndereco'><i class="fa fa-recycle"></i> Reset</button>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="f_endereco_commit('so_salvar')"><i class="fa fa-upload"></i> Salvar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="listaEnderecos"></div>

                        <!-- DOCUMENTAÇÃO E REGULAMENTAÇÃO | COMPROVANTE DE ENDEREÇO -->
                        <div class="card mt-2">
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium">Comprovante de Endereço</h5>
                                <div class="float-end">
                                    <a class="" id="toggleCE">
                                        <i class="fa-solid fa-chevron-down" id='idSetaCE'></i>
                                    </a>
                                </div>
                            </div>
                            <span id="msgAlertaCE"></span>
                            <div class="card-body collapse" style="font-size: 13px;" id='bodyCE'>
                                <form id='form_ce' enctype="multipart/form-data">
                                    <div class="row align-items-end"> <!-- Alinha os elementos na parte inferior -->
                                        <div class="col-sm-3">
                                            <label for="ce_data" class="form-label">Data</label>
                                            <input type="date" class="form-control" id="ce_data" name='ce_data'>
                                        </div>
                                        <div class="col-sm-8">
                                            <label for="ce_arquivo" class="form-label">Água, Energia, Telefone...</label>
                                            <input type="file" class="form-control" id="ce_arquivo" name='ce_arquivo'>
                                        </div>
                                        <div class="col-sm-1"> <!-- Alinha o botão à direita -->
                                            <!-- Botão para enviar o formulário -->
                                            <button type="button" class="btn btn-outline-primary" onClick="f_form_ce()">
                                                <i class="fa-solid fa-share"></i>
                                            </button>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                        <div id="listaComprovantes"></div>

                        <!-- FOTOS -->
                        <div class="card mt-2">
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium"><i class="fa-solid fa-camera"></i>&nbsp;Fotos</h5>
                                <div class="float-end">
                                    <a class="" id="toggleFotos">
                                        <i class="fa-solid fa-chevron-down" id='idSetaFotos'></i>
                                    </a>
                                </div>
                            </div>
                            <span id="msgAlertaFotos"></span>
                            <div class="card-body collapse" style="font-size: 13px;" id='bodyFotos'>
                                <form id='form_fotos' enctype="multipart/form-data">
                                    <div class="row align-items-end"> <!-- Alinha os elementos na parte inferior -->
                                        <div class="col-sm-11">
                                            <label for="foto_arquivo" class="form-label">Foto (Perfil, Ficha)</label>
                                            <input type="file" class="form-control" id="foto_arquivo" name="foto_arquivo">
                                        </div>
                                        <div class="col-sm-1"> <!-- Alinha o botão à direita -->
                                            <button type="button" class="btn btn-outline-primary" onClick="f_form_fotos()">
                                                <i class="fa-solid fa-share"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="listaFotos"></div>

                        <!-- DOCUMENTAÇÃO E REGULAMENTAÇÃO | ANTECEDENTES CRIMINAIAS -->
                        <div class="card mt-2">
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium">Antecedentes Criminais</h5>
                                <div class="float-end">
                                    <a class="" id="toggleAA">
                                        <i class="fa-solid fa-chevron-down" id='idSetaAA'></i>
                                    </a>
                                </div>
                            </div>
                            <span id="msgAlertaAA"></span>
                            <div class="card-body collapse" style="font-size: 13px;" id='bodyAA'>
                                <form id='form_aa' enctype="multipart/form-data">
                                    <div class="row align-items-end"> <!-- Alinha os elementos na parte inferior -->
                                        <div class="col-sm-3">
                                            <label for="aa_data" class="form-label">Data</label>
                                            <input type="date" class="form-control" id="aa_data" name='aa_data'>
                                        </div>
                                        <div class="col-sm-8">
                                            <label for="aa_arquivo" class="form-label">Atestado de Antecedentes Criminais (se aplicável)</label>
                                            <input type="file" class="form-control" id="aa_arquivo" name='aa_arquivo'>
                                        </div>
                                        <div class="col-sm-1"> <!-- Alinha o botão à direita -->
                                            <button type="button" class="btn btn-outline-primary" onClick="f_form_aa()">
                                                <i class="fa-solid fa-share"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="listaAntecedentes"></div>

                        <!-- CONTATO DE EMERGÊNCIA -->
                        <div class="card mt-2">
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium">Contato de Emergência</h5>
                                <div class="float-end">
                                    <a href="#" id="toggleEmergencia">
                                        <i class="fa-solid fa-chevron-down" id='idSetaEmergencia'></i>
                                    </a>
                                </div>
                            </div>
                            <div id='msgAlertaEmergencia'></div>
                            <div class="card-body container collapse" style="font-size: 13px;" id='bodyEmergencia'>
                                <form id='formEmergencia'>
                                    <div class="row">
                                        <!-- Nome do Contato -->
                                        <div class="col-md-6">
                                            <label for="nomeContato" class="form-label">Nome do Contato</label>
                                            <input type="text" class="form-control mb-3" id="emg_nome" name='emg_nome' placeholder="Nome completo">
                                        </div>

                                        <!-- Grau de Parentesco -->
                                        <div class="col-md-6">
                                            <label for="grauParentesco" class="form-label">Grau de Parentesco</label>
                                            <input type="text" class="form-control mb-3" id="emg_grau" name='emg_grau' placeholder="Ex: Pai, Mãe, Tio(a)">
                                        </div>

                                        <!-- Telefone FIXO-->
                                        <div class="col-md-6">
                                            <label for="telefone" class="form-label">Telefone Fixo</label>
                                            <input type="text" class="form-control mb-3" id="emg_telefone" name='emg_telefone' placeholder="(XX) XXXXX-XXXX">
                                        </div>
                                        <!-- Telefone Clular-->
                                        <div class="col-md-6">
                                            <label for="telefone" class="form-label">Celular</label>
                                            <input type="text" class="form-control mb-3" id="emg_celular" name='emg_celular' placeholder="(XX) X XXXXX-XXXX">
                                        </div>

                                        <!-- Endereço -->
                                        <div class="col-md-12">
                                            <label for="endereco" class="form-label">Endereço</label>
                                            <textarea class="form-control mb-3" id="emg_endereco" name='emg_endereco' rows="3" placeholder="Rua, número, bairro, cidade, estado"></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="btn-group" id="botoes_endereco">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" onclick='f_emergencia_cancel()'><i class="fa fa-close"></i> Cancelar</button>
                                            <button type="reset" class="btn btn-outline-warning btn-sm rounded m-1" id='btnResetEmergencia'><i class="fa fa-recycle"></i> Reset</button>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="f_emergencia_commit('so_salvar')"><i class="fa fa-upload"></i> Salvar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="listaEmergencia"></div>

                        <div class="text-center m-5 p-5">
                            Por favor ... preencha os campos obrigatórios ao lado <br>
                            para habilitar essas caixas.
                        </div>
                    </div>

                </div><!-- FIM DA ROW -->
            </main>


            <!-- Aqui Termina o conteúdo da página -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_pessoa_frm.js"></script>
    <script src="js/cpf.js"></script>
</body>

</html>
<?php // --- ROTINAS AUXILIARES

//- Cria o Seletor de Tipos de Endereço para o form
function f_tipo_endereco($_id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM RH.rh_enderecos_tipo;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = '<label for="idTipoEndereco" class="form-label">Tipo de Endereço</label>';
    $select .= "<select class='form-select fs-13' id='idTipoEndereco' name='idTipoEndereco' required>";
    if ($_id == 0) $select .= "<option value='0' selected>Selecione um tipo</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $idTipoEndereco) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idTipoEndereco' $selected>$dsTipoEndereco</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

//- Cria o Seletor de ETNIAS para o form
function f_etnias($_id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM RH.rh_etnias;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = '<label for="idEtnia" class="form-label">Etnia</label>';
    $select .= "<select class='form-select fs-13' id='idEtnia' name='idEtnia' required>";
    if ($_id == 0) $select .= "<option value='0' selected>Selecione...</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $idEtnia) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idEtnia' $selected>$categoria</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

//- Cria o Seletor de ESTADO CIVIL para o form
function f_estadoCivil($_id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_estadoCivil;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = '<label for="idEtnia" class="form-label">Estado Civil</label>';
    $select .= "<select class='form-select fs-13' id='idEstadoCivil' name='idEstadoCivil' required>";
    if ($_id == 0) $select .= "<option value='0' selected>Selecione um tipo</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $idEstadoCivil) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idEstadoCivil' $selected>$categoria</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

function f_escolaridade($_id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_graus_instrucao;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = '<label for="idGrauInstrucao" class="form-label">Escolaridade</label>';
    $select .= "<select class='form-select fs-13' id='idGrauInstrucao' name='idGrauInstrucao' required>";
    if ($_id == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$id' $selected>$descricao</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}
