<?PHP
//
// - rh_colab_frm.php | Formulário de cadastro de COLABORADORES
// (C)haia, 04/04/2025

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
    $idColab = 0;
}

$idColab = 1;

//
// Calcula a Próxima Matrícula
$sql = "SELECT MAX(matricula) as matricula FROM rh_colaboradores WHERE idColab > 0";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$matricula = $result['matricula'] + 1;
if ($matricula == 0) {
    $matricula = 1;
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
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.html"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <!-- Aqui COMEÇA o conteúdo da página -->
            <main class="container-fluid">
                <div class="card">
                    <div class="card-header bg-dark text-light mt-1 mb-1 clearfix">
                        <div class="float-start">
                            <h4 class="card-title"><i class="fa-solid fa-user-plus"></i>&nbsp;<span id='idTipoFormulario'> COLABORADORES - INCLUSÃO</span></h4>
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
                                <div class='float-end'></div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <form id='formCadastro'>
                                    <input type="hidden" id='idColab' name='idColab' value='<?= $idColab ?>'>
                                    <div class="row">
                                        <!-- Nome e Nome Social -->
                                        <div class="col-md-9">
                                            <label for="nmPessoa" class="form-label">Nome da Pessoa</label>
                                            <input type="text" class="form-control mb-3 obrigatorio" id="nmPessoa" name='nmPessoa' placeholder="comece a digitar o nome" required>
                                            <input type="hidden" id='idPessoa' name='idPessoa' value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="matricula" class="form-label">Matrícula</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="matricula" name='matricula' placeholder="Nº da matrícula" required value='<?= $matricula ?>'>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Nome e Nome Social -->
                                        <div class="col-md-3">
                                            <label for="idOrgao" class="form-label">Local de Lotação</label>
                                            <?= seletor_orgao() ?>
                                        </div>
                                        <div class="col-md-1 d-flex flex-column align-items-center justify-content-center">
                                            <label for="lider" class="form-label text-center">Líder</label>
                                            <input type="checkbox" id="lider" name="lider" class="form-check-input checkbox-grande mt-1" onclick='marquei_lider(this)'>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="idCargo" class="form-label">Cargo</label>
                                            <?= seletor_cargo() ?>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="idFuncao" class="form-label">Função</label>
                                            <?= seletor_funcao() ?>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <label for="matricula" class="form-label">Admissão</label>
                                            <input type="date" class="form-control mb-3 obrigatorio text-center" id="admissao" name='admissao' required value='<?= date("Y-m-d") ?>'>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="idTipoContrato" class="form-label">Regime de Admissão</label>
                                            <?= seletor_regime() ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="horario_ini" class="form-label">Horário Ini</label>
                                            <input type="time" class="form-control mb-3 obrigatorio text-center" id="horario_ini" name='horario_ini' required value='08:20'>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="horario_fim" class="form-label">Horário Fim</label>
                                            <input type="time" class="form-control mb-3 obrigatorio text-center" id="horario_fim" name='horario_fim' required value='18:05'>
                                        </div>
                                    </div>
                                    <div class="row mt-1">
                                        <div class="col-md-3">
                                            <label for="carga_h" class="form-label">Carga Hor</label>
                                            <input type="number" class="form-control mb-3 obrigatorio text-center" id="carga_h" name='carga_h' required value='44'>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="salario" class="form-label">Salário</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="salario" name='salario' required value='0.00'>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tipo_contrato" class="form-label">Prazo Contratação</label>
                                            <select name="tipo_contrato" id="tipo_contrato" class='form-select obrigatorio'>
                                                <option value="0">Selecione</option>
                                                <option value="I">Inderminado</option>
                                                <option value="D">Determinado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tipo_contrato" class="form-label">Forma de Pagamento</label>
                                            <select name="tipo_contrato" id="tipo_contrato" class='form-select obrigatorio'>
                                                <option value="0">Selecione</option>
                                                <option value="H">Hora</option>
                                                <option value="S">Semanal</option>
                                                <option value="M">Mensal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tipo_contrato" class="form-label">Jornada de Trabalho</label>
                                            <select name="jornada" id="jornada" class='form-select obrigatorio'>
                                                <option value="0">Selecione</option>
                                                <option value="D">Diária</option>
                                                <option value="S">Semanal</option>
                                                <option value="M">Mensal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tipo_contrato" class="form-label">Local: Subsede</label>
                                            <?= seletor_subsedes() ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="horario_fim" class="form-label">PIS/PASEP/NIT</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="pis" name='pis' required value=''>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="ctps" class="form-label">CTPS nº</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="ctps" name='ctps' required value=''>
                                        </div>
                                    </div>

                                    <div class="row" class='mt-1'>
                                        <div class="col-md-6">
                                            <label for="mae" class="form-label">Nome da Mãe</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="mae" name='mae' required value=''>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="mae" class="form-label">Grau de Instrução do Colaborador</label>
                                            <?= seletor_grau_de_instrucao() ?>
                                        </div>
                                    </div>
                                    <div class="row mt-1">
                                        <div class="col-md-5">
                                            <label for="idBanco" class="form-label">Banco</label>
                                            <?= seletor_bancos() ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="bco_agencia" class="form-label">Agência</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="bco_agencia" name='bco_agencia' required value=''>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="bco_cc" class="form-label">Conta Corrente</label>
                                            <input type="text" class="form-control mb-3 obrigatorio text-center" id="bco_cc" name='bco_cc' required value=''>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="btn-group" id="botoes_principal">
                                            <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"><i class="fa fa-recycle"></i> Reset</button>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick="f_incluir_commit()"><i class="fa fa-upload"></i> Salvar</button>
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
                                    <div class="col-md-3 mb-2">
                                        <label for="vale_refeicao" class="form-label">Cartão Sodexo</label>
                                        <select name="vale_refeicao" id="vale_refeicao" class='form-select'>
                                            <option value="Nenhum">Selecione</option>
                                            <option value="Alimentacao">Alimentação</option>
                                            <option value="Refeicao">Refeição</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label for="vale_refeicao" class="form-label">Vale Transporte</label>
                                        <select name="vale_transporte" id="vale_transporte" class='form-select'>
                                            <option value="Nenhum">Selecione</option>
                                            <option value="Alimentacao">V.Transportes</option>
                                            <option value="Elo">Cartão Elo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label for="plano_saude" class="form-label">Plano Saúde</label>
                                        <?= seletor_plano_saude(); ?>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label for="plano_odonto" class="form-label">Plano Odonto</label>
                                        <?= seletor_plano_odonto(); ?>
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
                                            O endereço de trabalho é o mesmo da SubSede:
                                        </label>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="mesmoEndereco" name="mesmoEndereco"
                                                onclick='toggle_endereco(this)' checked value='1'>
                                        </div>
                                    </div>
                                </div>
                                <div class="row collapse" id=divSeletorEndereco>
                                    <div class="col-sm-12">
                                        <div class="input-group" id='seletorEndereco'>
                                            <select name="teste" id="teste" class="form-select fs-13">
                                                <option value="0">Select</option>
                                            </select>
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
                                        <div class='collapse form-control h5' id='dsArquivo_ctps'></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mt-3">
                                        <label for="arquivo_dd" class="form-label">Declaração de Dependentes para IR</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="arquivo_ddir" name='arquivo_ddir' accept=".pdf, .jpg, .png, .jpeg">
                                            <span class="input-group-text"><a class='envia_arquivo' href='#!' onclick='envia_arquivo_ddir()'>enviar</a></span>
                                        </div>
                                        <div class='collapse form-control h5' id='dsArquivo_ddir'></div>
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
                                <table class='table table-striped tabke-hover w-100'>
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Parentesco</th>
                                            <th>Idade</th>
                                            <th>IR</th>
                                            <th>P.Saúde</th>
                                            <th>P.Odonto</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    /*
                                    $sql = "SELECT C.id, N.nivel, C.curso, C.ano_conclusao, I.sigla
                                                    FROM rh_cv_fa C
                                                    INNER JOIN rh_fa_niveis N on N.idNivel = C.idNivel
                                                    INNER JOIN rh_fa_instituicoes I on I.idInstituicao = C.idInstituicao
                                                    WHERE C.idPessoa = :idPessoa";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
                                    $stmt->execute();
                                    */
                                    ?>
                                    <tbody></tbody>
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
                                                <option value="0">Sim</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="usaPlanoOdonto" class="form-label">Plano Odontológico</label>
                                            <select name="usaPlanoOdonto" id="usaPlanoOdonto" class="form-select fs-13">
                                                <option value="0" selected>Não</option>
                                                <option value="0">Sim</option>
                                            </select>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-end gap-2" id="botoesModalDependentes">
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded px-4"><i class="fa fa-recycle"></i> Reset</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded px-4" onclick="f_add_dependente_commit()"><i class="fa fa-upload"></i> Salvar</button>
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
    <script src="js/scripts.js"></script>
    <script src="js/rh_colab_frm.js"></script>
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
    $select = "<select class='form-select fs-13 obrigatorio' id='idOrgao' name='idOrgao' required>";
    if ($_idOrgao == 0) $select .= "<option value='0' selected>Selecione um local</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $margem = $nivel * 3;
        if ($_idOrgao == $idOrgao) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idOrgao' $selected>" . str_repeat("&nbsp;", $margem) . "$idOrgao-$descricao</option>";
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
    $select = "<select class='form-select fs-13 obrigatorio' id='idCargo' name='idCargo' required>";
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
    $select .= "<select class='form-select fs-13 obrigatorio' id='idFuncao' name='idFuncao' required>";
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
        if ($_idTipoContrato == $idContratoTipo) $selected = "selected";
        else $selected = "";
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

function seletor_bancos($_idBanco = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_bancos
                ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idBanco' name='idBanco' required>";
    if ($_idBanco == 0) $select .= "<option value='0' selected>Selecione um banco</option>";

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
                ORDER BY dsSubSede";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idSubSede' name='idSubSede' required>";
    if ($_idSubSede == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idSubSede == $idSubSede) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idSubSede' $selected>$dsSubSede</option>";
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
    $select = "<select class='form-select fs-13 obrigatorio' id='idPlanoSaude' name='idPlanoSaude'>";
    if ($_idPlano == 0) $select .= "<option value='0' selected>Selecione</option>";

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
    $select = "<select class='form-select fs-13 obrigatorio' id='idPlanoOdonto' name='idPlanoOdonto'>";
    if ($_idPlano == 0) $select .= "<option value='0' selected>Selecione</option>";

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
