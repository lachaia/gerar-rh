<?php
//
//- rh_ficha_ouvidoria.php | exibe Ficha Deuncia RH
//- (C)haia, 24/07/2025
//

session_start();

$idModulo = 15; // Acolhimento do RH

$parametros = filter_input_array(INPUT_GET, FILTER_DEFAULT);

if ($parametros) extract($parametros);
if (empty($id)) {
    die("FALTOU PARÂMETROS ");
}

if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [3, 9], true)) {
    header("location: logout.php");
    die("<h1>ACESSO NEGADO</h1>");
}

include_once "includes/conexao_gerar.php";
include_once "includes/debug.php";

$_idUsuario = $_SESSION['idUsuario'];

//
//- SELECIONA DADOS DA DENÚNCIA
$pesquisa = "SELECT * FROM rh_ouvidoria WHERE id = :id";
$stmt = $conn->prepare($pesquisa);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
if ($stmt->rowCount() == 0) {
    die("DENÚNCIA NÃO ENCONTRADA");
}
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
//
extract($dados);

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
    <link href="css/rh_ficha_ouvidoria.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- jQuery (já incluído) -->
    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
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
                            <h4 class="card-title">
                                <i class="fa-solid fa-heart-circle-bolt"></i>&nbsp;<span id='idTipoFormulario'>OUVIDORIA (Relatos)</span>
                            </h4>
                        </div>
                        <div class="float-end">
                            <a href='#!' class="btn btn-outline-light btn-sm" onClick="f_voltar()"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <!-- PRIMEIRA COLUNA -->
                    <div class='col-sm-7'>
                        <div class="card shadow-lg border-0 mt-2">
                            <div class="card-header text-white cartao clearfix">
                                <div class="float-start">
                                    <h5 class="mb-0"><i class="fas fa-id-card-alt"></i> O que foi Informado</h5>
                                </div>

                                <div class="float-end d-flex align-items-center gap-2">
                                    <a id='linkOlho' href='#!' onclick='trocar()'><i class="fa-regular fa-eye-slash"></i></a>
                                    <h5 class="mb-0">ID: <?= $id ?></h5>
                                    <input type="hidden" id='esconder' value='1'>
                                    <input type="hidden" id='id' value='<?= $id ?>'>
                                </div>

                            </div>
                            <div class="card-body bg-light p-4">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <strong><i class="fas fa-user"></i> Nome completo:</strong>
                                        <p class="campo-destaque text-center" id='nome'><?= htmlspecialchars($nome) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-phone"></i> Telefone:</strong>
                                        <p class="campo-destaque" id='telefone'><?= htmlspecialchars($telefone) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <strong><i class="fas fa-envelope"></i> E-mail pessoal:</strong>
                                        <p class="campo-destaque" id='email'><?= htmlspecialchars($email) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-bullhorn"></i> Tipo de Relato:</strong>
                                        <p class="campo-destaque"><?= strtoupper(htmlspecialchars($tipo_assedio)) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-bullhorn"></i> Tipo (outro):</strong>
                                        <p class="campo-destaque" id='tipo_outro'><?= htmlspecialchars($tipo_outro) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <strong><i class="fas fa-comment-alt"></i> Relatado:</strong>
                                        <p class='obrigatorio' id='relato'><?= htmlspecialchars($relato) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <strong><i class="fa-solid fa-people-arrows"></i> Envolvidos:</strong>
                                        <p class="campo-destaque text-center" id='envolvidos'><?= htmlspecialchars($envolvidos) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <strong><i class="fa-solid fa-people-arrows"></i> Nomes das testemunhas:</strong>
                                        <p class="campo-destaque text-center" id='nomes_testemunhas'><?= htmlspecialchars($nomes_testemunhas) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <strong><i class="fa-solid fa-comments"></i> Comunicado?:</strong>
                                        <p class="campo-destaque text-center"><?= htmlspecialchars($comunicado) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-9 mb-3">
                                        <strong><i class="fa-solid fa-comments"></i> Resposta?:</strong>
                                        <p class="campo-destaque text-center" id='resposta_comunicado'><?= htmlspecialchars($resposta_comunicado) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <strong><i class="fa-solid fa-comments"></i> Acompanhamento?:</strong>
                                        <p class="campo-destaque text-center"><?= htmlspecialchars($acompanhamento) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-9 mb-3">
                                        <strong><i class="fa-solid fa-comments"></i> Nome da Pessoa?:</strong>
                                        <p class="campo-destaque text-center" id='nome_contato'><?= htmlspecialchars($nome_contato) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-phone"></i> Telefone:</strong>
                                        <p class="campo-destaque" id='telefone_contato'><?= htmlspecialchars($telefone_contato) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <strong><i class="fas fa-envelope"></i> E-mail:</strong>
                                        <p class="campo-destaque" id='email_contato'> <?= htmlspecialchars($email_contato) ?: 'Não informado' ?></p>
                                    </div>

                                    <div class="col-md-6 offset-md-6 mb-1 text-center">
                                        <strong><i class="fa-regular fa-clock"></i> Cadastrado em:</strong>
                                        <p class="campo-destaque"><?= "$data_envio" ?></p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- SEGUNDA COLUNA -->
                    <div class='col-sm-5'>
                        <div class="card mt-2">
                            <div class="card-header text-white cartao clear-fix">
                                <div class="float-start">
                                    <h5 class='text-center'><i class="fa-regular fa-hourglass"></i>&nbsp;Linha do Tempo do Processo</h5>
                                </div>
                                <div class="float-end">
                                    <button class='btn btn-sm btn-dark' onclick='inclui_ldt()'>Incluir</button>
                                </div>

                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <?php
                                $sql = "SELECT A.*, T.*, U.login 
                                        FROM rh_ouvidoria_ldt A 
                                        LEFT OUTER JOIN rh_ouvidoria_tldt T on T.idAcaoTipo = A.idAcaoTipo
                                        LEFT OUTER JOIN rh_usuarios U on U.idUsuario = A.idUsuario
                                        WHERE A.idDenuncia = :id";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                                $stmt->execute();
                                ?>
                                <table class="table table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th><i class="fa-solid fa-circle-down"></i></th>
                                            <th style="width: 150px;">Em</th>
                                            <th style="width: 150px;">Por</th>
                                            <th>Ação</th>
                                            <th class='text-end'><i class="fa-solid fa-bolt"></i> Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?PHP
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            $descricao = substr(strip_tags($descricao), 0, 30);
                                            //
                                            if ($idAcaoTipo == 1) {
                                                $link = " - ";
                                                $link_del = "";
                                            } else {
                                                if ($idUsuario == $_idUsuario) {
                                                    $link = "<a href='#!' class='btn btn-sm btn-outline-success' onClick='f_edita_cartao($idAcao)'><i class='fa-solid fa-pencil'></i></a>";;
                                                    $link_del = "<a href='#!' class='btn btn-sm btn-outline-danger' onClick='f_del($idAcao)'><i class='fa-solid fa-trash'></i></a>";
                                                } else {
                                                    $link = "<a href='#!' class='btn btn-sm btn-outline-success' onClick='f_ver_cartao($idAcao)'><i class='fa-solid fa-magnifying-glass'></i></a>";;
                                                    $link_del = "";
                                                }
                                            }
                                            //
                                            if (empty($login)) $login = "Sistema";
                                            //
                                            echo "<tr>
                                            <td><i class='fa $icone icon' style='color: $cor'></i></td>
                                            <td>$data</td>
                                            <td>$login</td>
                                            <td>$nome</td>
                                            <td class='text-end' style=' white-space: nowrap;'>$link_del $link</td>
                                        </tr>";
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal INCLUIR LINHA DO TEMPO -->
                <div class="modal fade" id="modalLinhaTempo" tabindex="-1" aria-labelledby="modalLinhaTempoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="modalLinhaTempoLabel"><i class="fa-solid fa-clock"></i> Registrar Linha do Tempo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formLinhaTempo">
                                    <input type="hidden" id="idDenuncia" name="idDenuncia" value="<?= $id ?>">
                                    <div class="row">
                                        <!-- Tipo da Ação -->
                                        <div class="col-md-7 mb-3" id="seletorAcao">
                                            <label for="idAcaoTipo" class="form-label"><i class="fa-solid fa-list"></i> Tipo de Ação</label>
                                            <?php echo seletor_tipo($conn); ?>
                                        </div>

                                        <!-- Data -->
                                        <?php $agora = date("Y-m-d\TH:i"); ?>
                                        <div class="col-md-5 mb-3">
                                            <label for="data" class="form-label"><i class="fa-solid fa-calendar-day"></i> Data</label>
                                            <input type="datetime-local" class="form-control form-sm text-center" id="data" name="data" value='<?= $agora ?>' required>
                                        </div>

                                    </div>

                                    <!-- Descrição -->
                                    <div class="mb-3">
                                        <label for="descricao" class="form-label"><i class="fa-solid fa-align-left"></i> Descreva o evento que deseja registrar</label>
                                        <textarea class="form-control" id="descricao" name="descricao"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div id="msgLinhaDoTempo" class='h5 text-center'></div>
                            <div class="modal-footer" id="botoesLDT">
                                <button type="reset" class="btn btn-outline-secondary" onclick="$('#descricao').summernote('reset');" id='btnReset'><i class="fa-solid fa-eraser"></i> Reset</button>
                                <button type="button" class="btn btn-outline-success" onclick="salvarLinhaTempo()"><i class="fa-solid fa-save"></i> Salvar</button>
                            </div>
                            <div class="h5 text-center" id='msgLinhaDoTempo'></div>
                        </div>
                    </div>
                </div>

                <!-- Modal: Novo Tipo de Ação -->
                <div class="modal fade" id="modalNovoTipoAcao" tabindex="-1" aria-labelledby="modalNovoTipoAcaoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered"> <!-- Centralização vertical -->
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="modalNovoTipoAcaoLabel">Novo Tipo de Ação</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formNovoTipoAcao">
                                    <div class="mb-3">
                                        <label for="nomeNovoTipo" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="nomeNovoTipo" name="nome" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="iconeNovoTipo" class="form-label">Ícone (Font Awesome, ex: <code>fa-comment</code>)</label>
                                        <input type="text" class="form-control" id="iconeNovoTipo" name="icone">
                                    </div>
                                    <div class="mb-3">
                                        <label for="corNovoTipo" class="form-label">Cor</label>
                                        <input type="color" class="form-control form-control-color" id="corNovoTipo" name="cor" value="#007bff">
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <div id='botoesNovoTipo'>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" onclick="salvarNovoTipoAcao()">Salvar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar LINHA DO TEMPO -->
                <div class="modal fade" id="modalLinhaTempoEdit" tabindex="-1" aria-labelledby="modalLinhaTempoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header text-white" style='background-color:darkslategray'>
                                <h5 class="modal-title" id="modalLinhaTempoLabel"><i class="fa-solid fa-clock"></i> Registrar Linha do Tempo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="xformLinhaTempo">
                                    <div class="row">
                                        <!-- Tipo da Ação -->
                                        <div class="col-md-7 mb-3" id="seletorAcao">
                                            <label for="xidAcaoTipo" class="form-label"><i class="fa-solid fa-list"></i> Tipo de Ação</label>
                                            <?php echo seletor_tipo($conn); ?>
                                        </div>

                                        <!-- Data -->
                                        <?php $agora = date("Y-m-d\TH:i"); ?>
                                        <div class="col-md-5 mb-3">
                                            <label for="xdata" class="form-label"><i class="fa-solid fa-calendar-day"></i> Data</label>
                                            <input type="datetime-local" class="form-control form-sm text-center" id="xdata" name="xdata" value='' required>
                                        </div>

                                    </div>

                                    <!-- Descrição -->
                                    <div class="mb-3">
                                        <label for="xdescricao" class="form-label"><i class="fa-solid fa-align-left"></i> Descreva o evento que deseja registrar</label>
                                        <textarea class="form-control" id="xdescricao" name="xdescricao"></textarea>
                                    </div>
                                    <input type="hidden" name='idAcao' id='idAcao'>
                                </form>
                            </div>
                            <div id="xmsgLinhaDoTempo" class='h5 text-center'></div>
                            <div class="modal-footer" id="xbotoesLDT">
                                <button type="reset" class="btn btn-outline-warning" onclick="$('#xdescricao').summernote('reset');" id='xbtnReset'>
                                    <i class="fa-solid fa-eraser"></i> Reset
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                                    <i class="fa-solid fa-times"></i> Fechar
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="xsalvarLinhaTempo()" id='xbtnSalvar'>
                                    <i class="fa-solid fa-save"></i> Salvar
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Modal Visualizar LINHA DO TEMPO -->
                <div class="modal fade" id="modalLinhaTempoView" tabindex="-1" aria-labelledby="modalLinhaTempoViewLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- centralizada verticalmente -->
                        <div class="modal-content">
                            <div class="modal-header text-white" style='background-color:slategray'>
                                <h5 class="modal-title" id="modalLinhaTempoViewLabel">
                                    <i class="fa-regular fa-eye"></i> Visualizar Linha do Tempo
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <form id="xformLinhaTempoView">
                                    <div class="row">
                                        <!-- Tipo da Ação -->
                                        <div class="col-md-7 mb-3">
                                            <label class="form-label"><i class="fa-solid fa-list"></i> Tipo de Ação</label>
                                            <input type="text" class="form-control bg-light" id="viewAcaoTipo" readonly>
                                        </div>

                                        <!-- Data -->
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label"><i class="fa-solid fa-calendar-day"></i> Data</label>
                                            <input type="datetime-local" class="form-control text-center bg-light" id="viewData" readonly>
                                        </div>
                                    </div>

                                    <!-- Descrição -->
                                    <div class="mb-3">
                                        <label class="form-label"><i class="fa-solid fa-align-left"></i> Descrição do Evento</label>
                                        <div id="viewDescricao" class="border p-2 rounded bg-light" style="min-height:150px"></div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Criado em:</strong> <span id="viewCriado"></span>
                                </div>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="fa-solid fa-times"></i> Fechar
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </main>

        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script src="js/rh_ficha_ouvidoria.js"></script>

</body>

</html>
<?php
function seletor_tipo($conn)
{
    //global $conn;

    $sql = "SELECT * FROM rh_ouvidoria_tldt where idAcaoTipo > 1 ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $retorno  = "<div class='input-group input-group-sm'>";
    $retorno .= "<select class='form-select' id='idTipoAcao' name='idTipoAcao'>";
    $retorno .= "<option value='0' selected>Tipo de Atividade</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $retorno .= "<option value='$idAcaoTipo' style='color: $cor'>$nome</option>";
    }

    $retorno .= "</select>";
    $retorno .= "<button type='button' class='btn btn-outline-primary' title='Novo tipo' onclick='inclui_tipo_acao()'>
                    <i class='fa fa-plus'></i>
                 </button>";
    $retorno .= "</div>";

    return $retorno;
}
