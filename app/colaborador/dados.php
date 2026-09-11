<?php
//
//- dados.php | Módulo Colaborador - Dados Pessoais
// (C)haia, 07/10/2025

session_start();

$modulo = "MeusDados";
include 'header.php';

if (isset($_SESSION['idLogin'])) {
    $login = $_SESSION['nmLogin'];
    $idColab = $_SESSION['idColab'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
} else {
    header("Location: ../logout.php");
    exit;
}

$idModulo = 13; // Colaborador

require_once "../includes/conexao_gerar.php";
global $conn;

$sql = "SELECT C.*, P.*, EC.categoria as dsEstadoCivil, ET.categoria as dsEtnia,  CG.nome as dsCargo, F.nome as dsFuncao, 
                    O.idOrgao, O.descricao as dsOrgao, O.idSupervisor, O.nivel, 
                    (SELECT nivel FROM rh_organograma WHERE idOrgao = O.idSupervisor) as nivelSupervisor,
                    C.dcLider, P.*, CV.*
            FROM rh_colaboradores C 
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT OUTER JOIN rh_cv CV on CV.idPessoa = C.idPessoa
            LEFT OUTER JOIN rh_cargos CG on CG.idCargo = C.idCargo
            LEFT OUTER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
            LEFT OUTER JOIN rh_organograma O on O.idOrgao = C.idOrgao
            LEFT OUTER JOIN rh_estadoCivil EC on EC.idEstadoCivil = P.idEstadoCivil
            LEFT OUTER JOIN rh_etnias ET on ET.idEtnia = P.idEtnia
            WHERE C.idColab = :idColab";
//
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
$stmt->execute();
$registro = $stmt->fetch(PDO::FETCH_ASSOC);
extract($registro);

?>
<link rel="stylesheet" href="css/dados.css">
<main class="main" data-bs-theme="dark">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Dados do Colaborador</h2>
            <small style="color:var(--muted)">Atualize seu dados pessoais e seus dados profissionais</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Dados Pessoais (skeleton) -->
    <div class="big-card">

        <!-- Cartão Informações Pessoais -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-middle">
                <h5 class='mb-0 mt-1'>
                    <i class="fa-solid fa-address-card"></i>
                    INFORMAÇÕES PESSOAIS
                </h5>
                <div class='d-flex align-items-center gap-2'>
                    <a href='index.php' class="btn btn-sm btn-outline-light" id='btnVoltar'><i class="fa-solid fa-rotate-left"></i> Voltar</a>
                    <button class="btn btn-sm btn-outline-light ms-2" data-bs-toggle="collapse" data-bs-target="#infoPessoalBody" aria-expanded="true" aria-controls="infoPessoalBody">
                        <i class="fa-solid fa-chevron-up" id="iconeToggle"></i>
                    </button>
                </div>
            </div>
            <div id="infoPessoalBody" class="collapse show">
                <div class="card-body container" style="font-size: 13px;">
                    <form id='formPessoa'>
                        <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $idPessoa ?>'>
                        <div class="row">
                            <!-- Nome e Nome Social -->
                            <div class="col-md-6">
                                <label for="nome" class="form-label">Nome completo</label>
                                <input type="text" class="form-control mb-3 obrigatorio edCampo" id="nome" name='nome' placeholder="Digite o nome completo" value='<?= $nome ?>'>
                            </div>
                            <div class="col-md-5">
                                <label for="nomeSocial" class="form-label">Nome social</label>
                                <input type="text" class="form-control mb-3 edCampo" id="nomeSocial" name='nomeSocial' placeholder="Digite o nome social (se aplicável)" value='<?= $nomeSocial ?>'>
                            </div>
                            <div class="col-md-1">
                                <label for="nomeSocial" class="form-label">ID</label>
                                <input type="text" class="form-control mb-3 edCampo text-center" id="idColab" name='idColab' Value='<?= $idColab ?>' readonly style='background-color: #e9ecef'>
                            </div>

                            <!-- Data de nascimento e Sexo -->
                            <div class="col-md-2">
                                <label for="dataNascimento" class="form-label">Data de nascimento</label>
                                <input type="date" class="form-control mb-3" id="dataNascimento" name='dataNascimento' value="<?= $dtNascimento ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="sexo" class="form-label">Sexo biológico</label>
                                <select class="form-select mb-3" id="sexo" name='sexo'>
                                    <option value="" selected disabled>Selecione</option>
                                    <option value="M" <?= $sexo == "M" ? "selected" : "" ?>>Masculino</option>
                                    <option value="F" <?= $sexo == "F" ? "selected" : "" ?>>Feminino</option>
                                </select>
                            </div>

                            <!-- Estado Civil e Nacionalidade -->
                            <div class="col-md-2">
                                <?php echo f_estadoCivil($idEstadoCivil); ?>
                            </div>
                            <div class="col-md-2">
                                <label for="nacionalidade" class="form-label">Nacionalidade</label>
                                <input type="text" class="form-control mb-3" id="nacionalidade" name='nacionalidade' placeholder="Digite sua nacionalidade" value='<?= $nacionalidade ?>'>
                            </div>

                            <!-- CPF e RG -->
                            <div class="col-md-2">
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text" class="form-control mb-3 obrigatorio" id="cpf" name='cpf' value='<?= $cpf ?>' placeholder="Digite seu CPF" onblur='testar_cpf(this)'>
                            </div>
                            <div class="col-md-2">
                                <label for="rg" class="form-label">RG (e órgão emissor)</label>
                                <input type="text" class="form-control mb-3" id="rg" name='rg' placeholder="Digite seu RG e órgão emissor" value='<?= $rg ?>'>
                            </div>

                            <!-- Título de eleitor e Endereço -->
                            <div class="col-md-2">
                                <label for="tituloEleitor" class="form-label">Título de eleitor</label>
                                <input type="text" class="form-control mb-3" id="tituloEleitor" name='tituloEleitor' placeholder="Digite seu título de eleitor" value='<?= $titulo_eleitor ?>'>
                            </div>

                            <!-- Telefone e E-mail -->
                            <div class="col-md-2">
                                <label for="telefone" class="form-label">Telefone pessoal</label>
                                <input type="tel" class="form-control mb-3" id="telefone" name='telefone' placeholder="Digite seu telefone" value='<?= $telefone ?>'>
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">E-mail pessoal</label>
                                <input type="email" class="form-control mb-3" id="email" name='email' placeholder="Digite seu e-mail" value="<?= $email ?>">
                            </div>

                            <!-- Tamanho de camiseta e Etnia -->
                            <div class="col-md-2">
                                <label for="tamanhoCamiseta" class="form-label">Tamanho de camiseta</label>
                                <input type="text" class="form-control mb-3" id="tamanhoCamiseta" name='tamanhoCamiseta' placeholder="tamanho?" value="<?= $camiseta ?>">
                            </div>
                            <div class="col-md-2">
                                <?php echo f_etnias($idEtnia); ?>
                            </div>
                            <div class="col-md-3">
                                <label for="cnh" class="form-label">CNH nº</label>
                                <input type="text" class="form-control mb-3" id="cnh" name='cnh' placeholder="Nro da CNH?" value="<?= $cnh ?>">
                            </div>
                            <div class="col-md-1">
                                <label for="cnh_categoria" class="form-label">CNH Categ</label>
                                <input type="text" class="form-control mb-3" id="cnh_categoria" name='cnh_categoria' placeholder="Categoria" value="<?= $cnh_categoria ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="cnh_vencimento" class="form-label">CNH Vcto</label>
                                <input type="date" class="form-control mb-3" id="cnh_vencimento" name='cnh_vencimento' placeholder="Vcto" value="<?= $cnh_vencimento ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="celular_corporativo" class="form-label">Celular Corporativo</label>
                                <input type="text" class="form-control mb-3" id="celular_corporativo" name='celular_corporativo' placeholder="Celular" value="<?= $celular_corporativo ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="email_corporativo" class="form-label">e-Mail Corporativo</label>
                                <input type="text" class="form-control mb-3" id="email_corporativo" name='email_corporativo' placeholder="e-Mail" value="<?= $email_corporativo ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="nome_mae" class="form-label">Nome da Mãe</label>
                                <input type="text" class="form-control mb-3" id="nome_mae" name='nome_mae' placeholder="Informe o nome da mãe" value="<?= $nome_mae ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="pis" class="form-label">PIS nº</label>
                                <input type="text" class="form-control mb-3" id="pis" name='pis' placeholder="Informe o nro do PIS" value="<?= $pis ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="ctps" class="form-label">CTPS</label>
                                <input type="text" class="form-control mb-3" id="ctps" name='ctps' placeholder="CTPS nº" value="<?= $ctps ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="idGrau" class="form-label">Grau de Instrução do Colaborador</label>
                                <?= seletor_grau_de_instrucao($idGrauEscola) ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="btn-group" id="dados_botoes">
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"><i class="fa fa-recycle"></i> Reset</button>
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="salvar_dados()"><i class="fa fa-upload"></i> Salvar Alterações</button>
                            </div>
                        </div>
                        <div id="msgAlertaPessoa" class="text-center h4"></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cartão Meus Endereços -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-middle">
                <h5 class='mb-0 mt-1'>
                    <i class="fa-solid fa-map-location"></i>
                    Meus endereços
                </h5>
                <div class='d-flex align-items-center gap-2'>
                    <button type="button" class="btn btn-outline-light btn-sm" id='btnNovoEndereco' onclick='f_incluir_endereco()'>
                        <i class="fa-solid fa-plus"></i> Novo Endereço
                    </button>
                    <button class="btn btn-sm btn-outline-light ms-2" data-bs-toggle="collapse" data-bs-target="#infoEnderecosBody" aria-expanded="true" aria-controls="infoEnderecosBody">
                        <i class="fa-solid fa-chevron-up" id="iconeToggle"></i>
                    </button>
                </div>
            </div>
        </div> <!-- Cartão Meus Endereços -->
        <div id="infoEnderecosBody" class="collapse show" data-bs-theme="dark">
            <div id="listaEnderecos"></div>
        </div>

        <!-- Cartão Meus Comprovantes de Endereços -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-middle" data-bs-theme="dark">
                <h5 class='float-start mt-1'>
                    <i class="fa-solid fa-note-sticky"></i>
                    Meus Comprovantes de Endereço
                </h5>
                <div class='d-flex align-items-center gap-2'>
                    <button type="button" class="btn btn-outline-light btn-sm" id='btnNovoEndereco' onclick='f_incluir_comprovante()'>
                        <i class="fa-solid fa-plus"></i> Novo Comprovante de Endereço
                    </button>
                    <button class="btn btn-sm btn-outline-light ms-2" data-bs-toggle="collapse" data-bs-target="#infoComprovanteBody" aria-expanded="true" aria-controls="infoComprovanteBody">
                        <i class="fa-solid fa-chevron-up" id="iconeToggle"></i>
                    </button>
                </div>
            </div>
        </div> <!-- Cartão Meus Endereços -->
        <div id="infoComprovanteBody" class="collapse show">
            <div id="listaComprovantes" data-bs-theme="dark"></div>
        </div>

        <!-- Cartão Minhas Fotos -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-middle">
                <h5 class='mb-0 mt-1'>
                    <i class="fa-solid fa-camera"></i>
                    Minhas Fotos
                </h5>
                <div class='d-flex align-items-center gap-2'>
                    <button type="button" class="btn btn-outline-light btn-sm" id='btnNovoEndereco' onclick='f_incluir_foto()'>
                        <i class="fa-solid fa-plus"></i> Nova Foto
                    </button>
                    <button class="btn btn-sm btn-outline-light ms-2" data-bs-toggle="collapse" data-bs-target="#infoFotosBody" aria-expanded="true" aria-controls="infoFotosBody">
                        <i class="fa-solid fa-chevron-up" id="iconeToggle"></i>
                    </button>
                </div>
            </div>
        </div> <!-- Cartão Meus Endereços -->
        <div id="infoFotosBody" class="collapse show">
            <div id="listaFotos" data-bs-theme="dark"></div>
        </div>

        <!-- Cartão Meus Contados de Emergência -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-middle">
                <h5 class='mb-0 mt-1'>
                    <i class="fa-solid fa-kit-medical"></i>
                    Meus Contatos de Emergência
                </h5>
                <div class='d-flex align-items-center gap-2'>
                    <button type="button" class="btn btn-outline-light btn-sm" id='btnNovoContato' onclick='f_incluir_contato()'>
                        <i class="fa-solid fa-plus"></i> Novo Contato
                    </button>
                    <button class="btn btn-sm btn-outline-light ms-2" data-bs-toggle="collapse" data-bs-target="#infoContatosBody" aria-expanded="true" aria-controls="infoContatosBody">
                        <i class="fa-solid fa-chevron-up" id="iconeToggle"></i>
                    </button>
                </div>
            </div>
        </div> <!-- Cartão Meus Endereços -->
        <div id="infoContatosBody" class="collapse show" data-bs-theme="dark">
            <div id="listaEmergencia" data-bs-theme="dark"></div>
        </div>

    </div>

    <!-- The Modal INCLUIR ENDEREÇO -->
    <div class="modal fade" id="modalIncEnd" tabindex="-1" aria-labelledby="enderecoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id='modalEnderecoLabel'>
                        <i class="fa-regular fa-pen-to-square"></i> Novo Endereço
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal body -->
                <form id="formNovoEndereco">
                    <input type="hidden" id="idColab" name="idColab" value='<?= $idColab ?>'>
                    <input type="hidden" id="idEndereco" name="idEndereco">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <?php echo f_tipo_endereco(); ?>
                            </div>
                            <div class="col-sm-6">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control text-center obrigatorio" id="cep" name="cep" placeholder="Somente números" maxlength="8" onBlur='busca_cep(this)'>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <!-- Logradouro e Número -->
                            <div class="col-md-8">
                                <label for="logradouro" class="form-label">Logradouro</label>
                                <input type="text" class="form-control mb-3 obrigatorio" id="logradouro" name="logradouro" placeholder="Rua, Avenida, etc.">
                            </div>
                            <div class="col-md-4">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control mb-3 obrigatorio" id="numero" name="numero" placeholder="Nº">
                            </div>

                            <!-- Complemento e Bairro -->
                            <div class="col-md-6">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control mb-3" id="complemento" name="complemento" placeholder="Apto, Bloco, etc.">
                            </div>
                            <div class="col-md-6">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control mb-3 obrigatorio" id="bairro" name="bairro" placeholder="Digite o bairro">
                            </div>
                            <!-- Cidade e Estado (UF) -->
                            <div class="col-md-8">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control mb-3 obrigatorio" id="cidade" name="cidade" placeholder="Digite a cidade">
                            </div>
                            <div class="col-md-4">
                                <label for="uf" class="form-label">Estado (UF)</label>
                                <input type="text" class="form-control mb-3 text-center obrigatorio" id="uf" name="uf" maxlength="2">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="btn-group" id='botoes_novo_endereco'>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Cancelar
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" id='btnResetNovoEndereco'><i class="fa-solid fa-recycle"></i> Reset</button>
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick='f_endereco_commit("novo")' id='btnSalvarEndereco'><i class="fa fa-save
                                        "></i> Salvar</button>
                            </div>
                            <div id='msgAlertaNovoEndereco' class="text-center"></div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <!-- The Modal INCLUIR COMPROVANTE DE ENDEREÇO -->
    <div class="modal fade" id="modalIncComprovante" tabindex="-1" aria-labelledby="comprovanteLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-regular fa-pen-to-square"></i> Novo Comprovante de Endereço
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id='form_ce' enctype="multipart/form-data">
                        <input type="hidden" id="idColab" name="idColab" value='<?= $idColab ?>'>
                        <input type="hidden" id="idPessoa" name="idPessoa" value='<?= $idPessoa ?>'>
                        <div class="row align-items-end"> <!-- Alinha os elementos na parte inferior -->
                            <div class="col-sm-3">
                                <label for="ce_data" class="form-label">Data do documento</label>
                                <input type="date" class="form-control" id="ce_data" name='ce_data'>
                            </div>
                            <div class="col-sm-8">
                                <label for="ce_arquivo" class="form-label">Água, Energia, Telefone...</label>
                                <input type="file" class="form-control" id="ce_arquivo" name='ce_arquivo'>
                            </div>
                            <div class="col-sm-1"> <!-- Alinha o botão à direita -->
                                <!-- Botão para enviar o formulário -->
                                <button type="button" id="btnEnviarComprovante" class="btn btn-outline-primary" onclick="f_envia_comprovante()">
                                    <i class="fa-solid fa-share"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="row mb-3">
                        <div id='msgAlertaCE' class="text-center"></div>
                    </div>
                </div><!-- Modal body -->
            </div>
        </div>
    </div>

    <!-- The Modal INCLUIR nova FOTO -->
    <div class="modal fade" id="modalIncFoto" tabindex="-1" aria-labelledby="fotoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-regular fa-pen-to-square"></i> Novo upload de Foto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
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
                    <div class="row mb-3">
                        <div id='msgAlertaFotos' class="text-center"></div>
                    </div>
                </div><!-- Modal body -->
            </div>
        </div>
    </div>

    <!-- The Modal INCLUIR novo CONTATO DE EMERGÊNCIA -->
    <div class="modal fade" id="modalIncEmergencia" tabindex="-1" aria-labelledby="emergenciaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-regular fa-address-book"></i>
                        Novo Contato de Emergência
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
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
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="f_contato_commit('')"><i class="fa fa-upload"></i> Salvar</button>
                            </div>
                        </div>
                    </form>
                    <div id='msgAlertaEmergencia'></div>
                    <div class="row mb-3">
                        <div id="listaEmergencia"></div>
                    </div>
                </div><!-- Modal body -->
            </div>
        </div>
    </div>
</main>


<?php include 'footer.php';

//- Cria o Seletor de Estados Civis para o form
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

//- Cria o Seletor de GRAUS DE INSTRUÇÃO para o form
function seletor_grau_de_instrucao($_idGrau = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_graus_instrucao
                WHERE ativo = 1
                ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idGrau' name='idGrau' required>";
    if ($_idGrau == 0) $select .= "<option value='0' selected>Selecione um grau</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idGrau == $id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$id' $selected>$descricao</option>";
    }
    $select .= "</select>";
    echo $select;
}

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
?>
<script src="js/dados.js"></script>