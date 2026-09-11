<?PHP
//
// - rh_colab_edt.php | Formulário de EDIÇÃO de cadastro de COLABORADORES
// (C)haia, 14/04/2025

session_start();

$idModulo = 4; // colaboradores

if (!isset($_SESSION['idLogin'])) {
    header("location: logout.php");
} else {
    include_once "includes/conexao_gerar.php";
}

if (isset($_GET['id'])) {
    $idColab = $_GET['id'];
} else {
    header("location: rh_colab.php");
}

$sql = "SELECT C.*, P.nome as nmColab, P.idPessoa, P.cnh, P.cnh_categoria, P.cnh_vencimento, P.email_corporativo, P.celular_corporativo
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
        WHERE C.idColab = :idColab";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);

if ($stmt->execute()) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        extract($result);
        //
        //- Tratamento de Dados
        if( empty($idPlanoSaude) ) $idPlanoSaude = 0;
        if( empty($idPlanoOdonto) ) $idPlanoOdonto = 0;
        if( empty($vale_transporte) ) $vale_transporte = 0;
        if( empty($vale_refeicao) ) $vale_refeicao = 0;

    } else {
        die("ERRO: Nenhum dado encontrado para o colaborador ID $idColab.");
    }
} else {
    die("ERRO: Falha ao executar a consulta.");
}


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

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <link href="css/styles.css" rel="stylesheet" />
    <!-- jQuery (se ainda não estiver incluído) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI (CSS para o estilo e JS para o autocomplete) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <style>
        label {
            font-weight: bold;
        }

        .obrigatorio {
            border-color: red;
        }

        .envia_arquivo {
            text-decoration: none;
            /* Remove o sublinhado */
            cursor: pointer;
            /* Muda o cursor para "apontador" */
            transition: transform 0.2s;
            /* Suaviza o efeito ao passar o mouse */
            display: inline-block;
            /* Necessário para o transform funcionar bem */
        }

        .envia_arquivo:hover {
            transform: scale(1.1);
            /* Aumenta 10% ao passar o mouse */
        }

        .checkbox-grande {
            transform: scale(1.5);
            /* aumenta o tamanho */
            cursor: pointer;
        }

        #mesmoEndereco {
            width: 3.5em;
            height: 1.5em;
            transform: scale(1.1);
            /* aumenta proporcionalmente */
            cursor: pointer;
        }

        .ui-autocomplete {
            z-index: 1055 !important;
            /* um valor acima do z-index da modal do Bootstrap (geralmente 1050) */
            position: absolute;
        }

        #bate_ponto, #lider {
            width: 3em;
            height: 1.5em;
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
                            <h4 class="card-title"><i class="fa-solid fa-user-tag"></i>&nbsp;<span id='idTipoFormulario'> COLABORADORES - EDIÇÃO</span></h4>
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
                                <h5 class='float-start mt-1'><i class="fa-solid fa-user-tie"></i> INFORMAÇÕES PROFISSIONAIS</h5>
                                <div class='float-end'>ID: <span><?= $idColab ?></span></div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <form id='formCadastro'>
                                    <input type="hidden" id='idColab' name='idColab' value='<?= $idColab ?>'>
                                    <div class="row">
                                        <!-- Nome e Nome Social -->
                                        <div class="col-md-9">
                                            <label for="nmPessoa" class="form-label">Nome da Pessoa</label>
                                            <input type="text" class="form-control mb-3 obrigatorio" id="nmPessoa" name='nmPessoa' placeholder="comece a digitar o nome" required value='<?= $nmColab ?>'>
                                            <input type="hidden" id='idPessoa' name='idPessoa' value="<?= $idPessoa ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="matricula" class="form-label">Matrícula</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="matricula" name='matricula' placeholder="Nº da matrícula" required value='<?= $matricula ?>'>
                                        </div>
                                    </div>

                                    <!-- LOCAL DE TRABALHO -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="idOrgao" class="form-label">Local de Lotação</label>
                                            <input type="hidden" id='idOrgaoOld' name='idOrgaoOld' value='<?= $idOrgao ?>'>
                                            <input type="hidden" id="motivoOrgao" name='motivoOrgao'>
                                            <?= seletor_orgao($idOrgao) ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="idSubSede" class="form-label">Subsede</label>
                                            <?= seletor_subsedes($idSubSede) ?>
                                        </div>                                        
                                        <div class="col-md-3">
                                            <label for="idPolo" class="form-label">Polo</label>
                                            <?= seletor_polos($polo_id) ?>
                                        </div> 
                                        <div class="col-md-3">
                                            <label for="idCentroCusto" class="form-label">Centro de Custo</label>
                                            <?= seletor_centroCusto($idCentroCusto) ?>
                                        </div>                                                                            
                                    </div>

                                    <!-- CARGO & FUNÇÃO -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="idCargo" class="form-label">Cargo</label>
                                            <input type="hidden" id='idCargoOld' name='idCargoOld' value='<?= $idCargo ?>'>
                                            <input type="hidden" id="motivoCargo" name='motivoCargo'>
                                            <?= seletor_cargo($idCargo) ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="idFuncao" class="form-label">Função</label>
                                            <input type="hidden" id='idFuncaoOld' name='idFuncaoOld' value='<?= $idFuncao ?>'>
                                            <input type="hidden" id="motivoFuncao" name='motivoFuncao'>
                                            <?= seletor_funcao($idFuncao) ?>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <label for="matricula" class="form-label">Admissão</label>
                                            <input type="date" class="form-control mb-3 obrigatorio text-center" id="admissao" name='admissao' required value='<?= $data_admissao ?>'>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="idTipoContrato" class="form-label">Regime de Admissão</label>
                                            <?= seletor_regime($idContratoTipo) ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="horario_ini" class="form-label">Horário Ini</label>
                                            <input type="time" class="form-control mb-3 obrigatorio text-center" id="horario_ini" name='horario_ini' required value='<?= $horario_ini ?>'>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="horario_fim" class="form-label">Horário Fim</label>
                                            <input type="time" class="form-control mb-3 obrigatorio text-center" id="horario_fim" name='horario_fim' required value='<?= $horario_fim ?>'>
                                        </div>
                                    </div>

                                    <div class="row mt-1">

                                        <!-- CARGA HORÁRIA-->
                                        <div class="col-md-3">
                                            <label for="carga_h" class="form-label">Carga Hor</label>
                                            <input type="number" class="form-control mb-3 obrigatorio text-center" id="carga_h" name='carga_h' required value='<?= $carga_horaria ?>'>
                                        </div>

                                        <!-- SALÁRIO BASE -->
                                        <div class="col-md-3">
                                            <label for="salario" class="form-label">Salário</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="salario" name='salario' required value='<?= $salario_base ?>'  onchange='f_motivo("motivoSalario")'>
                                            <input type="hidden" id="salarioOld" name='salarioOld' value='<?= $salario_base ?>'>
                                            <input type="hidden" id="motivoSalario" name='motivoSalario'>
                                        </div>

                                        <!-- PRAZO DE CONTRATAÇÃO -->
                                        <div class="col-md-3">
                                            <label for="tipo_prazo" class="form-label">Prazo Contratação</label>
                                            <select name="tipo_prazo" id="tipo_prazo" class='form-select obrigatorio'>
                                                <option value="0">Selecione</option>
                                                <option value="I">Inderminado</option>
                                                <option value="D">Determinado</option>
                                            </select>
                                        </div>

                                        <!-- FORMA DE PAGAMENTO -->
                                        <div class="col-md-3">
                                            <label for="tipo_forma" class="form-label">Forma de Pagamento</label>
                                            <select name="tipo_forma" id="tipo_forma" class='form-select obrigatorio'>
                                                <option value="0">Selecione</option>
                                                <option value="H">Hora</option>
                                                <option value="S">Semanal</option>
                                                <option value="M">Mensal</option>
                                                <option value="A">Aulista</option>
                                            </select>
                                        </div>

                                        <!-- JORNADA DE TRABALHO -->
                                        <div class="col-md-3">
                                            <label for="jornada" class="form-label">Jornada de Trabalho</label>
                                            <select name="jornada" id="jornada" class='form-select obrigatorio'>
                                                <option value="0">Selecione</option>
                                                <option value="D">Diária</option>
                                                <option value="S">Semanal</option>
                                                <option value="M">Mensal</option>
                                            </select>
                                        </div>

                                        <!-- CBO -->
                                       <div class="col-md-3">
                                            <label for="cbo" class="form-label">CBO</label>
                                            <input type="text" class="form-control mb-3 text-center" id="cbo" name='cbo' value='<?= $cbo ?>' placeholder="CBO">
                                        </div>

                                        <!-- E-MAIL CORPORATIVO -->
                                        <div class="col-md-6">
                                            <label for="email_corporativo" class="form-label">e-Mail Corporativo</label>
                                            <input type="email" class="form-control mb-3 text-center" id="email_corporativo" name='email_corporativo' value='<?= $email_corporativo ?>' placeholder="eMail Corporativo">
                                        </div>

                                        <!-- CELULAR CORPORATIVO -->
                                        <div class="col-md-6">
                                            <label for="celular_corporativo" class="form-label">Celular Corporativo</label>
                                            <input type="text" class="form-control mb-3 text-center" id="celular_corporativo" name='celular_corporativo' value='<?= $celular_corporativo ?>' placeholder="nro corporativo">
                                        </div>

                                        <!-- CHAVE PIX -->
                                        <div class="col-md-6">
                                            <label for="chave_pix" class="form-label">Chave Pix</label>
                                            <input type="text" class="form-control mb-3 text-center" id="chave_pix" name='chave_pix' value='<?= $chave_pix ?>' placeholder="Chave Pix">
                                        </div>
       
                                    </div>

                                    <!-- DADOS BANCÁRIOS -->
                                    <div class="row mt-1">
                                        <div class="col-md-5">
                                            <label for="idBanco" class="form-label">Banco</label>
                                            <?= seletor_bancos($idBanco) ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="bco_agencia" class="form-label">Agência</label>
                                            <input type="text" class="form-control mb-3 text-center" id="bco_agencia" name='bco_agencia' required value='<?= $bco_agencia ?>' placeholder="nro da agência"> 
                                        </div>
                                        <div class="col-md-4">
                                            <label for="bco_cc" class="form-label">Conta Corrente</label>
                                            <input type="text" class="form-control mb-3 text-center" id="bco_cc" name='bco_cc' required value='<?= $bco_cc ?>' placeholder="nro da conta corrente">
                                        </div>
                                    </div>

                                    <div class="row mt-1">

                                        <!-- E-SOCIAL -->
                                        <div class="col-md-4">
                                            <label for="esocial" class="form-label">Nr. E-Social (Contabilidade)</label>
                                            <input type="text" class="form-control mb-3 text-center" id="esocial" name='esocial' required value='<?= $esocial_id ?>' placeholder="Cód. Contabilidade">
                                        </div>

                                        <!-- BATE PONTO? -->
                                        <div class="col-md-4 d-flex align-items-center text-center">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="bate_ponto" id="bate_ponto" value="<?= $bate_ponto ?>" <?= $bate_ponto ? 'checked' : '' ?> onclick="toggle_bate_ponto(this)">
                                                <label class="form-check-label ms-2" for="bate_ponto">Colaborador bate ponto?</label>
                                            </div>
                                        </div>

                                        <!-- É LIDER DE STAFF? -->
                                        <div class="col-md-4 d-flex align-items-center text-center">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="lider" id="lider" value="<?= $dcLider ?>" <?= $dcLider ? 'checked' : '' ?> onclick="marquei_lider(this)">
                                                <label class="form-check-label ms-2" for="lider">Colaborador é lider de staff?</label>
                                            </div>
                                        </div>                                         
                                    </div>
                                    
                                    <!-- BLOCO DOS BOTÕES -->
                                    <div class="row">
                                        <div class="btn-group" id="botoes_principal">
                                            <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"><i class="fa fa-recycle"></i> Reset</button>
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" onclick="f_voltar()"><i class="fa-solid fa-rotate-left"></i> Voltar</button>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="f_editar_commit()"><i class="fa fa-upload"></i> Salvar</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="collapse" id="btnVoltar">
                                            <div class="text-end">
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded m-1" onclick="f_voltar()"><i class="fa-solid fa-arrow-rotate-left"></i> Voltar</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>
                                <div class="text-center h5" id="msgCadastro"></div>
                            </div>
                        </div>

                    </div>

                    <!-- SEGUNDA COLUNA -->
                    <div class='col-sm-6'>

                        <!-- BENEFÍCIOS-->
                        <div class="card mt-2" id='cardBeneficios'>
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium"><i class="fa-brands fa-cc-visa"></i> Dados para Benefícios</h5>
                                <div class="float-end"></div>
                            </div>
                            <span id="msgAlertaCartoes"></span>
                            <div class="card-body container" style="font-size: 13px;" id='bodyEnd'>
                                <div class="row">
                                    <div class="col-md-2 mb-2">
                                        <label for="vale_refeicao" class="form-label">Cartão Sodexo</label>
                                        <select name="vale_refeicao" id="vale_refeicao" class='form-select obrigatorio'>
                                            <option value="Nenhum">Selecione</option>
                                            <option value="Pluxe">Pluxe</option>
                                            <option value="Ticket">Ticket</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label for="vale_refeicao" class="form-label">Mobilidade</label>
                                        <select name="vale_transporte" id="vale_transporte" class='form-select obrigatorio'>
                                            <option value="Nenhum">Selecione</option>
                                            <option value="Transportes">V.Transportes</option>
                                            <option value="Elo">Cartão Elo</option>
                                            <option value="Flash">Cartão Flash</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label for="plano_saude" class="form-label">Plano Saúde</label>
                                        <?= seletor_plano_saude($idPlanoSaude); ?>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label for="plano_odonto" class="form-label">Plano Odonto</label>
                                        <?= seletor_plano_odonto($idPlanoOdonto); ?>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label for="planoFarmacia" class="form-label">Farmácia</label>
                                        <select name="planoFarmacia" id="planoFarmacia" class='form-select'>
                                            <option value="Nenhum">Nenhum</option>
                                            <option value="Nissei">Nissei</option>
                                        </select>
                                    </div>                                    
                                    <div class="col-md-2 mb-2 mt-4">
                                        <button type='button' class='btn btn-outline-primary mt-1 w-100' onClick='salvar_cartoes()'><i class="fa-solid fa-upload"></i> Salvar</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ENDEREÇO DE TRABALHO -->
                        <div class="card mt-2" id='cardDocumentos'>
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium"><i class="fa-solid fa-house"></i> Endereço de Trabalho</h5>
                                <div class="float-end">
                                    <a class="" id="toggleEnd"></a>
                                </div>
                            </div>
                            <span id="msgAlertaDocs"></span>
                            <div class="card-body container" style="font-size: 13px;" id='bodyEnd'>
                                <div class="row mb-3">
                                    <div class="col-sm-12 d-flex align-items-center">
                                        <label for="switchMesmoEndereco" class="form-label me-2 mb-0">
                                            O endereço de trabalho é o mesmo do Polo onde está lotado:
                                        </label>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="mesmoEndereco" name="mesmoEndereco"
                                                onclick='toggle_endereco(this)' <?= ($idEnderecoTrab > 0) ? "" : "checked" ?>
                                                value='1'>
                                        </div>
                                    </div>
                                </div>
                                <div class="row <?= ($idEnderecoTrab > 0) ? "" : "collapse" ?>" id='divSeletorEndereco'>
                                    <div class="col-sm-12">
                                        <div class="input-group" id='seletorEndereco'>
                                            <?= seletor_enderecos($idEnderecoTrab) ?>
                                            <span class='input-group-text'><a class='envia_arquivo' href='#!' onclick='add_endereco()'><i class='fa-solid fa-plus'></i></a></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- DOCUMENTAÇÃO: CTPS & DECL IR -->
                        <div class="card mt-2" id='cardDocumentos'>
                            <div class="card-header clear-fix align-middle">
                                <h5 class="float-start mt-1 fw-medium"><i class="fa-solid fa-folder-open"></i> Documentação e Regulamentação</h5>
                                <div class="float-end"></div>
                            </div>
                            <span id="msgAlertaDocs"></span>
                            <div class="card-body container" style="font-size: 13px;" id='bodyEnd'>
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label for="arquivo_ctps" class="form-label">Cópia da CTPS</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="arquivo_ctps" name='arquivo_ctps' accept=".pdf, .jpg, .png, .jpeg">
                                            <span class="input-group-text"><a class='envia_arquivo' href='#!' onclick='envia_arquivo_ctps()'>enviar</a></span>
                                        </div>
                                        <div class='<?= (empty($arquivo_ctps) ? "collapse" : "") ?> form-control h5' id='dsArquivo_ctps'>
                                            <ul>
                                                <li>
                                                    <?= $arquivo_ctps ?>&nbsp;<a href='#' onclick='mostra_arquivo("<?= $arquivo_ctps ?>")'><i class="fa-solid fa-magnifying-glass"></i></a>
                                                    &nbsp;<a href='#' onclick='exclui_ctps(<?= $idColab ?>)'><i class="fa-regular fa-trash-can text-danger"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mt-3">
                                        <label for="arquivo_dd" class="form-label">Declaração de Dependentes para IR</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="arquivo_ddir" name='arquivo_ddir' accept=".pdf, .jpg, .png, .jpeg">
                                            <span class="input-group-text"><a class='envia_arquivo' href='#!' onclick='envia_arquivo_ddir()'>enviar</a></span>
                                        </div>
                                        <div class='<?= (empty($arquivo_ddir) ? "collapse" : "") ?> form-control h5' id='dsArquivo_ddir'>
                                            <ul>
                                                <li>
                                                    <?= $arquivo_ddir ?>&nbsp;<a href='#' onclick='mostra_arquivo("<?= $arquivo_ddir ?>")'><i class="fa-solid fa-magnifying-glass"></i></a>
                                                    &nbsp;<a href='#' onclick='exclui_ddir(<?= $idColab ?>)'><i class="fa-regular fa-trash-can text-danger"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="listaArquivos"></div>

                        <!-- DEPENDENTES -->
                        <div class="card mt-2" id='cardDependentes'>
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'><i class="fa-solid fa-users"></i> DEPENDENTES</h5>
                                <div class='float-end'>
                                    <button type='button' class='btn btn-outline-success btn-sm'
                                        onClick='add_dependente()'><i class="fa-solid fa-plus"></i> Novo</button>
                                </div>
                            </div>
                            <span id="msgAlertaDependentes"></span>
                            <div class="card-body container" style="font-size: 13px;" id='bodyEnd'>
                                <table class='table table-striped tabke-hover w-100' id='tabela_dependentes'>
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Parentesco</th>
                                            <th>Idade</th>
                                            <th>IR</th>
                                            <th>P.Saúde</th>
                                            <th>P.Odonto</th>
                                            <th>Creche</th>
                                            <th><i class="fa-solid fa-circle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT D.*, PD.nome as nmDependente, C.idPessoa, PC.nome as nmColab, 
                                                TIMESTAMPDIFF(YEAR, D.dataNascimento, CURDATE()) AS idade, P.dsParentesco
                                            FROM rh_dependentes D
                                            INNER JOIN rh_pessoas PD on PD.idPessoa = D.idPessoaDep
                                            INNER JOIN rh_colaboradores C ON C.idColab = D.idColab
                                            INNER JOIN rh_pessoas PC on PC.idPessoa = C.idPessoa
                                            INNER JOIN rh_parentescos P on P.idParentesco = D.idParentesco
                                            WHERE C.idColab = :idColab";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
                                        if ($stmt->execute()) {
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                extract($row);
                                                //
                                                $acoes = "<a href='#' onclick='excluir_dependente($idDependente)'><i class='fa-regular fa-trash-can text-danger'></i></a>";
                                                //
                                                echo "<tr>";
                                                echo "<td>$nmDependente</td>";
                                                echo "<td>$dsParentesco</td>";
                                                echo "<td>$idade</td>";
                                                echo "<td>" . ($ir ? "Sim" : "Não") . "</td>";
                                                echo "<td>" . ($usaPlanoSaude ? "Sim" : "Não") . "</td>";
                                                echo "<td>" . ($usaPlanoOdonto ? "Sim" : "Não") . "</td>";
                                                echo "<td>" . ($usaCreche ? "Sim" : "Não") . "</td>";
                                                echo "<td>$acoes</td>";
                                                echo "</tr>";
                                            }
                                        } 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </div><!-- FIM DA ROW -->


                <!-- MODAL - addModalFuncao | CADASTRAR FUNÇÃO -->
                <div class="modal fade" id="addModalFuncao" tabindex="-1" aria-labelledby="editarConLabel"
                    aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="background-color: gainsboro">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <h5><i class="fa-solid fa-user-plus"></i> Incluir Função</h5>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <form action="" id='formFuncao'>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="nmFuncao" class="form-label">Nome da Função</label>
                                            <input type="text" class="form-control mb-3 obrigatorio" id="nmFuncao" name='nmFuncao' placeholder="Nome da Função" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <label for="dsFuncao" class="form-label">Descrição</label>
                                            <textarea name="dsFuncao" id="dsFuncao" placeholder="Descreva a função..." class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-end gap-2" id="botoesModalFuncao">
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded"><i class="fa fa-recycle"></i> Reset</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded" onclick="f_add_funcao_commit()"><i class="fa fa-upload"></i> Salvar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="text-center h5" id='msgFuncao'></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL - addModalEndereco | CADASTRAR ENDEREÇOS -->
                <div class="modal fade" id="addModalEndereco" tabindex="-1" aria-labelledby="incluirEnderecoLabel"
                    aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="background-color: gainsboro">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <h5><i class="fa-solid fa-map-location-dot"></i> Incluir Endereço</h5>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <form action="" id='formEndereco'>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="idTipoEndereco" class="form-label">Tipo de Endereço</label>
                                            <div id='seletor_tipo_endereco'></div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="nmFuncao" class="form-label">CEP</label>
                                            <input type="text" class="form-control mb-3 obrigatorio" id="cep" name='cep' placeholder="CEP" required onblur='busca_cep(this)'>
                                        </div>
                                        <div class="col-md-7 mb-2">
                                            <label for="dsFuncao" class="form-label">Logradouro</label>
                                            <input type='text' name="logradouro" id="logradouro" placeholder="Logradouro" class="form-control">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label for="numero" class="form-label">Número</label>
                                            <input type='text' name="numero" id="numero" placeholder="Número" class="form-control">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="complemento" class="form-label">Complemento</label>
                                            <input type='text' name="complemento" id="complemento" placeholder="Ap./bloco..." class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="bairro" class="form-label">bairro</label>
                                            <input type='text' name="bairro" id="bairro" placeholder="bairro..." class="form-control">
                                        </div>
                                        <div class="col-md-10 mb-3">
                                            <label for="cidade" class="form-label">Cidade</label>
                                            <input type='text' name="cidade" id="cidade" placeholder="cidade..." class="form-control">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="uf" class="form-label text-center">UF</label>
                                            <input type='text' name="uf" id="uf" placeholder="uf..." class="form-control text-center">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-end gap-3" id="botoesModalEndereco">
                                                <button type="reset" class="btn btn-outline-secondary btn px-4 py-2 rounded-3 d-flex align-items-center gap-2">
                                                    <i class="fa fa-recycle"></i> Resetar
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn px-4 py-2 rounded-3 d-flex align-items-center gap-2" onclick="add_endereco_commit()">
                                                    <i class="fa fa-upload"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                                <div class="text-center h5" id='msgEndereco'></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL - addModalDependente | CADASTRAR DEPENDENTES -->
                <div class="modal fade" id="addModalDependente" tabindex="-1" aria-labelledby="addDependenteLabel"
                    aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="background-color: gainsboro">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <h5><i class="fa-solid fa-user-plus"></i> Incluir DEPENDENTES</h5>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <form action="" id='formDependente'>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="nmDependente" class="form-label">Nome do Dependente</label>
                                            <input type="text" class="form-control mb-3 obrigatorio" id="nmDependente" name='nmDependente' placeholder="começe a digitar o nome" required>
                                            <input type="hidden" id='idPessoaDep' name='idPessoaDep' value="0">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="idParentesco" class="form-label">Parentesco</label>
                                            <?= seletor_parentesco() ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="dataNascimento" class="form-label">Dt Nascimento</label>
                                            <input type="date" class="form-control mb-3 obrigatorio" id="dataNascimento" name='dataNascimento' required>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="usaPlanoSaude" class="form-label">Plano Saúde</label>
                                            <select name="usaPlanoSaude" id="usaPlanoSaude" class="form-select fs-13">
                                                <option value="0" selected>Não</option>
                                                <option value="1">Sim</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="usaPlanoOdonto" class="form-label">Plano Odontológico</label>
                                            <select name="usaPlanoOdonto" id="usaPlanoOdonto" class="form-select fs-13">
                                                <option value="0" selected>Não</option>
                                                <option value="1">Sim</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="usaCreche" class="form-label">Usa Creche?</label>
                                            <select name="usaCreche" id="usaCreche" class="form-select fs-13">
                                                <option value="0" selected>Não</option>
                                                <option value="1">Sim</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="ir" class="form-label">Imposto de Renda</label>
                                            <select name="ir" id="ir" class="form-select fs-13">
                                                <option value="0" selected>Não</option>
                                                <option value="1">Sim</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="cpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="cpf" name='cpf' placeholder="somente números" required onblur='testar_cpf(this)' maxlength="11">
                                        </div>                                        
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-6"></div>
                                        <div class="col-sm-6">
                                            <div class="d-flex" id="botoesModalDependentes">
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded w-100 me-1" id='resetDependente'><i class="fa fa-recycle"></i> Reset</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded w-100" onclick="f_add_dependente_commit()"><i class="fa fa-upload"></i> Salvar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="text-center h5" id='msgDependentes'></div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>


            <!-- Aqui Termina o conteúdo da página -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script data-cfasync="false" src="js/rh_colab_edt.js"></script>
    <script data-cfasync="false" src="js/cpf.js"></script>
</body>

</html>
<?PHP

function seletor_orgao($_idOrgao = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_organograma
                WHERE ativo = 1
                ORDER BY nivel_1, nivel_2, nivel_3, nivel_4, nivel_5, nivel_6 ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idOrgao' name='idOrgao' required onchange='f_motivo(\"motivoOrgao\")'>";
    if ($_idOrgao == 0) $select .= "<option value='0' selected>Selecione um local</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $margem = $nivel * 3;
        $cor = "";
        if($nivel==3) $cor = "text-danger"; 
        if($nivel==4) $cor = "text-primary"; 
        if($nivel==7) $cor = "text-success"; 
        if ($_idOrgao == $idOrgao) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idOrgao' $selected class='$cor'>" . str_repeat("&nbsp;", $margem) . "$idOrgao-$descricao</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_cargo($_idCargo = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_cargos
                WHERE ativo = 1
                ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idCargo' name='idCargo' required onchange='f_motivo(\"motivoCargo\")'>";
    if ($_idCargo == 0) $select .= "<option value='0' selected>Selecione um cargo</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idCargo == $idCargo) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idCargo' $selected>$nome</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_centroCusto($id=0)
{
    global $conn;
    $sql = "SELECT id as idCentroCusto, nome
                FROM rh_centrocusto
                ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idCentroCusto' name='idCentroCusto' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione um CC</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($id == $idCentroCusto) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idCentroCusto' $selected>$nome</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_funcao($_idFuncao = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_funcoes
                WHERE ativo = 1
                ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = '<div class="input-group">';
    $select .= "<select class='form-select fs-13 obrigatorio' id='idFuncao' name='idFuncao' required onchange='f_motivo(\"motivoFuncao\")'>";
    if ($_idFuncao == 0) $select .= "<option value='0' selected>Selecione uma função</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idFuncao == $idFuncao) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idFuncao' $selected>$nome</option>";
    }
    $select .= "</select>";
    $select .= '<span class="input-group-text" id="btnAddFuncao" onclick="f_add_funcao()"><i class="fa-solid fa-plus"></i></span>';
    $select .= "</div>";
    echo $select;
}

function seletor_regime($_idTipoContrato = 0)
{
    global $conn;
    $sql = "SELECT idContratoTipo, descricao FROM rh_contratos_tipo where ativo = 1 order by descricao;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idContratoTipo' name='idContratoTipo' required>";
    if ($_idTipoContrato == 0) $select .= "<option value='0' selected>Selecione o Regime</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = "";
        if ( $_idTipoContrato == 0 && $idContratoTipo==1) $selected = "selected";
        if ($_idTipoContrato == $idContratoTipo) $selected = "selected";
        $select .= "<option value='$idContratoTipo' $selected>$descricao</descricao>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_grau_de_instrucao($_idGrau = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_graus_instrucao
                WHERE ativo = 1
                ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idGrau' name='idGrau' required>";
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

function seletor_bancos($_idBanco = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_bancos
                ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idBanco' name='idBanco' required>";
    if ($_idBanco == 0){
        $select .= "<option value='0' selected>Selecione um banco</option>";
    } else{
        $select .= "<option value='0'>Selecione um banco</option>";
    }

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idBanco == $id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$id' $selected>$nome ($codigo)</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_subsedes($_idSubSede = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_subsedes
                WHERE ativo = 1
                ORDER BY identificador";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idSubSede' name='idSubSede' required>";
    if ($_idSubSede == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idSubSede == $subsede_id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$subsede_id' $selected>$identificador</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_polos($_idPolo = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_polos
                WHERE ativo = 1
                ORDER BY identificador";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idPolo' name='idPolo' required>";
    if ($_idPolo == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idPolo == $id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$id' $selected>$identificador</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_plano_saude($_idPlano = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_planos_saude
                WHERE tipoPlano = 1
                ORDER BY nomePlano ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    if ($_idPlano == 0) $selected = "selected";
    else $selected = "";
    $select = "<select class='form-select fs-13' id='idPlanoSaude' name='idPlanoSaude'>";
    $select .= "<option value='0' $selected>Nenhum</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idPlano == $idPlano) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idPlano' $selected>$nomePlano</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_plano_odonto($_idPlano = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_planos_saude
                WHERE tipoPlano = 2
                ORDER BY nomePlano ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idPlanoOdonto' name='idPlanoOdonto'>";
    if ($_idPlano == 0) $selected = "selected";
    else $selected = "";
    $select .= "<option value='0' $selected>Nenhum</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idPlano == $idPlano) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idPlano' $selected>$nomePlano</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_parentesco($_idParentesco = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_parentescos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idParentesco' name='idParentesco'>";
    if ($_idParentesco == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idParentesco == $idParentesco) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idParentesco' $selected>$dsParentesco</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_enderecos($_idEnderecoTrab = 0)
{
    global $conn, $idPessoa;
    $sql = "SELECT E.idEndereco, T.dsTipoEndereco, logradouro, numero, complemento, bairro, cidade, uf 
    FROM RH.rh_enderecos E
    INNER JOIN rh_enderecos_tipo T on T.idTipoEndereco = E.idTipoEndereco
    WHERE idPessoa = $idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idEndereco' name='idEndereco' onchange='change_endereco(this)'>";
    $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idEnderecoTrab == $idEndereco) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idEndereco' $selected>$dsTipoEndereco: $logradouro, $numero, $complemento, $bairro, $cidade, $uf</option>";
    }
    $select .= "</select>";
    echo $select;
}
