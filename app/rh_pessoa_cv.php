<?PHP
//
// - rh_pessoa_cv.php | Formulário de cadastro de CURRICULUM VITAE
// (C)haia, 24/02/2025

session_start();

$idModulo = 7; // Currículo Vitae

if (isset($_GET['id'])) {
    $idPessoa = $_GET['id'];
} else {
    header("location: rh_pessoas.php");
}

if (!isset($_SESSION['idLogin'])) {
    header("location: logout.php");
} else {
    include_once "includes/conexao_gerar.php";
}

$sql = "SELECT nome FROM rh_pessoas WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
extract($linha);

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
    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        label {
            font-weight: bold;
        }

        .obrigatorio {
            border-color: red;
        }

        .visCampo {
            border: 1px solid #ccc;
            /* Borda suave e discreta */
            border-radius: 5px;
            /* Cantos levemente arredondados */
            display: block;
            /* Ocupa a largura total disponível */
            width: 100%;
            /* Garante alinhamento correto */
            padding: 6px 10px;
            /* Espaçamento interno para melhor visualização */
            background-color: #f9f9f9;
            /* Fundo levemente acinzentado para destacar */
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
                            <h4 class="card-title"><i class="fa-solid fa-user-tie"></i>&nbsp;<span id='idTipoFormulario'>CURRICULUM VITAE - <?= $nome ?></span></h4>
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
                                <h5 class='float-start mt-1'>FORMAÇÃO ACADÊMICA</h5>
                                <div class='float-end'>
                                    <button type='button' class='btn btn-outline-success btn-sm' onClick='fa_incluir()'><i class="fa-solid fa-plus"></i> Novo</button>
                                </div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <table class='table table-striped table-hover table-sm'>
                                    <thead>
                                        <tr>
                                            <th>Nível</th>
                                            <th>Curso</th>
                                            <th><i class="fa-solid fa-graduation-cap"></i></th>
                                            <th class="text-center">Conclusão</th>
                                            <th style='width: 50px' class="text-center"><i class="fa-solid fa-circle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $sql = "SELECT C.id, N.nivel, C.curso, C.ano_conclusao, I.sigla
                                                    FROM rh_pessoa_cvfa C
                                                    INNER JOIN rh_fa_niveis N on N.idNivel = C.idNivel
                                                    INNER JOIN rh_fa_instituicoes I on I.idInstituicao = C.idInstituicao
                                                    WHERE C.idPessoa = :idPessoa";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
                                    $stmt->execute();

                                    ?>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            //
                                            $acoes =  "<button type='button' class='btn btn-outline-success btn-sm' onClick='fa_ver($id)'><i class='fa-solid fa-magnifying-glass' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
                                            $acoes .= "<button type='button' class='btn btn-outline-primary btn-sm' onClick='fa_editar($id)'><i class='fa-regular fa-pen-to-square' data-bs-toggle='tooltip' title='Editar!'></i></button>";
                                            $acoes .= "<button type='button' class='btn btn-outline-danger btn-sm' onClick='fa_excluir($id)'><i class='far fa-trash-alt' data-bs-toggle='tooltip' title='Excluir!'></i></button>";
                                            //
                                            echo "<tr>";
                                            echo "<td>$nivel</td>";
                                            echo "<td>$curso</td>";
                                            echo "<td>$sigla</td>";
                                            echo "<td class='text-center'>$ano_conclusao</td>";
                                            echo "<td class='text-end' style='white-space: nowrap;'>$acoes</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- SEGUNDA COLUNA -->
                    <div class='col-sm-6'>


                    </div>

                </div><!-- FIM DA ROW -->

                <!-- MODAL - faMovalVer | Formação Acadêmica - VISUALIZAR -->
                <div class="modal fade" id="faModalVer" tabindex="-1" aria-labelledby="visualizarfaLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="background-color: gainsboro">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <h5><i class="fa-solid fa-eye"></i> Visualizar Formação Acadêmica</h5>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label class="col-form-label">Curso</label>
                                        <p id="v_curso" class="form-control-plaintext visCampo"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label class="col-form-label">Instituição</label>
                                        <p id="v_instituicao" class="form-control-plaintext visCampo"></p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <label class="col-form-label">Sigla</label>
                                        <p id="v_sigla" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="col-form-label">Nível</label>
                                        <p id="v_nivel" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="col-form-label">Conclusão</label>
                                        <p id="v_ano_conclusao" class="form-control-plaintext visCampo text-center"></p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                            <i class="fa fa-close"></i> Fechar
                                        </button>
                                    </div>
                                    <span id='divQuando' class="mt-2 ms-2" style='font-size: 12px; font-weight: 200'></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL - faMovalInc | Formação Acadêmica - INCLUIR -->
                <div class="modal fade" id="faModalInc" tabindex="-1" aria-labelledby="incluirfaLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="background-color: gainsboro">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <h5><i class="fa-solid fa-eye"></i> Incluir Formação Acadêmica</h5>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <form action="#" id='formIncFA'>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Curso</label>
                                            <input type="text" id="curso" name="curso" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Instituição</label>
                                            <div id='seletorInstituicao'><?= seletorInstituicoes() ?></div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-8">
                                            <label class="col-form-label">Nível</label>
                                            <div id='seletorTipoCurso'><?= seletorTipoCurso() ?></div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="col-form-label">Conclusão</label>
                                            <input type="number" id='ano' name='ano' class='form-control'>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="btn-group" id='divBotoesFA'>
                                            <!-- Botão Fechar (vermelho) -->
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                                <i class="fa fa-close"></i> Fechar
                                            </button>
                                            <!-- Botão Reset (azul) -->
                                            <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1">
                                                <i class="fa fa-undo"></i> Reset
                                            </button>
                                            <!-- Botão Salvar (verde) -->
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick='fa_incluir_salva()'>
                                                <i class="fa fa-save"></i> Salvar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL - faMovalIncIE | Formação Acadêmica - INCLUIR IE-->
                <div class="modal fade" id="faModalIncIE" tabindex="-1" aria-labelledby="incluirfaLabelIE" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="background-color: #C8C8C8 ">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <h5><i class="fa-solid fa-eye"></i> Incluir Instituição de Ensino</h5>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body">
                                <form action="#" id='formIncFAIE' class='mt-4'>
                                    <div class="row mb-3">
                                        <div class="col-sm-9">
                                            <label class="col-form-label">Nome da Instituição</label>
                                            <input type="text" id="_nomeInstituicao" name="_nomeInstituicao" class="form-control">
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="col-form-label">Sigla</label>
                                            <input type="text" id="_sigla" name="_sigla" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label class="col-form-label">Cidade</label>
                                            <input type="text" id="_cidade" name="_cidade" class="form-control">
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="col-form-label">UF</label>
                                            <div id='seletorTipoUF'><?= seletorUF() ?></div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="col-form-label">País</label>
                                            <input type="text" id='_pais' name='_pais' class='form-control' value='Brasil'>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="btn-group" id="botoes_fa_id">
                                            <!-- Botão Fechar (vermelho) -->
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                                <i class="fa fa-close"></i> Fechar
                                            </button>
                                            <!-- Botão Reset (azul) -->
                                            <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1">
                                                <i class="fa fa-undo"></i> Reset
                                            </button>
                                            <!-- Botão Salvar (verde) -->
                                            <button type="button" class="btn btn-outline-success btn-sm rounded m-1" onclick='f_inclui_ie_salva()'>
                                                <i class="fa fa-save"></i> Salvar
                                            </button>

                                        </div>
                                    </div>
                                    <div id='msgformIncFAIE' class="text-center h4"></div>
                                </form>
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
    <script src="js/rh_pessoa_cv.js"></script>
</body>

</html>
<?php // --- ROTINAS AUXILIARES

//- Cria o Seletor de UF para o form
function seletorUF( $_uf = '' ) 
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_uf";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='uf' name='uf' required>";
    if ($_uf == '') $select .= "<option value='0' selected>UF</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_uf == $uf) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$uf' $selected>$nome</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

//- Cria o Seletor de NÍVEIS DE CURSO para o form
function seletorTipoCurso( $id = 0 ) 
{
    global $conn;
    //
    $sql = "SELECT * FROM RH.rh_fa_niveis;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idNivel' name='idNivel' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione um Nível</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($id == $idNivel) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idNivel' $selected>$nivel</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

//- Cria o Seletor de INSTITUIÇÕES DE ENSINO para o form
function seletorInstituicoes($id = 0) 
{
    global $conn;
    
    $sql = "SELECT * FROM RH.rh_fa_instituicoes order by nome;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $select = "<div class='input-group'>"; // Inicia o grupo de input
    
    // Select de instituições
    $select .= "<select class='form-select fs-13' id='idInstituicao' name='idInstituicao' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione uma IE</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = ($id == $idInstituicao) ? "selected" : "";
        $select .= "<option value='$idInstituicao' $selected>$nome ($cidade/$uf-$pais)</option>";
    }
    $select .= "</select>";
    
    // Botão "+" para adicionar nova instituição
    $select .= "<button type='button' class='btn btn-outline-secondary' onclick='f_inclui_ie()'>";
    $select .= "<i class='fa fa-plus'></i>";
    $select .= "</button>";
    
    $select .= "</div>"; // Fecha o input-group
    
    echo $select;
}