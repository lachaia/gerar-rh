<?php
//
//- rh_pessoa_ver.php | exibe Ficha Pessoa
//- (C)haia, 07/03/2025
//

session_start();

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_GET, FILTER_DEFAULT);

if ($parametros) extract($parametros);
if (empty($id)) {
    die("FALTOU PARÂMETROS ");
}

if (!isset($_SESSION['idLogin'])) {
    header("location: logout.php");
} else {
    include_once "includes/conexao_gerar.php";
    $_idUsuario = $_SESSION['idUsuario'];
}

//
//- SELECIONA DADOS DA PESSOA
//

$sql = "SELECT P.* , ifnull(EC.categoria,'ND') AS dsEstadoCivil, ifnull( ET.categoria, 'ND') as dsEtnia
            FROM RH.rh_pessoas P
            LEFT OUTER JOIN rh_estadoCivil EC ON EC.idEstadoCivil = P.idEstadoCivil
            LEFT OUTER JOIN rh_etnias ET ON ET.idEtnia = P.idEtnia
            WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $id, PDO::PARAM_STR);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract($dados);

$cpf_formatado = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpf);

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
    <link href="css/rh_ficha_pessoa.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- jQuery (já incluído) -->
    <script data-cfasync="false" type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>

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
                            <h4 class="card-title"><i class="fa-solid fa-users"></i>&nbsp;<span id='idTipoFormulario'>PESSOAS</span></h4>
                        </div>
                        <div class="float-end">
                            <a href='rh_pessoas.php' class="btn btn-outline-light btn-sm" onClick="f_voltar()"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <!-- PRIMEIRA COLUNA -->
                    <div class='col-sm-7'>

                        <!-- DADOS PESSOAIS -->
                        <div class="card shadow-lg border-0 mt-2">
                            <div class="card-header text-white cartao clearfix">
                                <div class="float-start">
                                    <h5 class="mb-0"><i class="fas fa-id-card-alt"></i> Ficha Pessoal</h5>
                                </div>
                                <div class="float-end"><?= "<h5>ID: $id</h5>" ?></div>
                            </div>
                            <div class="card-body bg-light p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-user"></i> Nome completo:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($nome) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-user-tag"></i> Nome social:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($nomeSocial) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-calendar-alt"></i> Data de nascimento:</strong>
                                        <p class="campo-destaque"><?= date('d/m/Y', strtotime($dtNascimento)) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-venus-mars"></i> Sexo:</strong>
                                        <p class="campo-destaque"><?= $sexo == 'M' ? 'Masculino' : ($sexo == 'F' ? 'Feminino' : 'Não informado') ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-ring"></i> Estado Civil:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($dsEstadoCivil) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-globe"></i> Nacionalidade:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($nacionalidade) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-id-card"></i> CPF:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($cpf_formatado) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-id-badge"></i> RG (e órgão emissor):</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($rg) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-vote-yea"></i> Título de Eleitor:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($titulo_eleitor) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong><i class="fas fa-phone"></i> Telefone:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($telefone) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <strong><i class="fas fa-envelope"></i> E-mail pessoal:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($email) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <strong><i class="fas fa-tshirt"></i> Camiseta:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($camiseta) ?: 'Não informado' ?></p>
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <strong><i class="fas fa-users"></i> Etnia:</strong>
                                        <p class="campo-destaque"><?= htmlspecialchars($dsEtnia) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENDEREÇOS -->
                        <?php
                        $sql = "SELECT E.*, T.dsTipoEndereco, L.dtLogin, U.login
                                FROM RH.rh_enderecos E
                                INNER JOIN rh_enderecos_tipo T on T.idTipoEndereco = E.idTipoEndereco
                                INNER JOIN rh_logins L on L.idLogin = E.idLogin
                                INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                                WHERE E.idPessoa = :idPessoa";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':idPessoa', $id, PDO::PARAM_STR);
                        $stmt->execute();
                        ?>
                        <div class="card shadow-lg border-0">
                            <div class="card-header text-white cartao">
                                <h5 class="mb-0"><i class="fa-solid fa-map"></i> ENDEREÇOS</h5>
                            </div>
                            <div class="card-body bg-light p-4">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fas fa-map-marker-alt"></i> Classe</th>
                                            <th><i class="fas fa-road"></i> Logradouro</th>
                                            <th><i class="fas fa-envelope"></i> CEP</th>
                                            <th><i class="fas fa-city"></i> Cidade/UF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            $endereco = trim("$logradouro, $numero, $complemento, $bairro", ", ");
                                            $cidadeuf = "$cidade/$uf";
                                            if (! empty($cep)) $cep = preg_replace("/^(\d{5})(\d{3})$/", "$1-$2", $cep);
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($dsTipoEndereco) ?></td>
                                                <td><?= htmlspecialchars($endereco) ?: 'Não informado' ?></td>
                                                <td><?= htmlspecialchars($cep) ?: 'Não informado' ?></td>
                                                <td><?= htmlspecialchars($cidadeuf) ?: 'Não informado' ?></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- CONTATOS DE EMERGÊNCIA -->
                        <?php
                        $sql = "SELECT * FROM rh_pessoas_emg
                                WHERE idPessoa = :idPessoa";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':idPessoa', $id, PDO::PARAM_STR);
                        $stmt->execute();
                        ?>
                        <div class="card shadow-lg border-0">
                            <div class="card-header text-white cartao">
                                <h5 class="mb-0"><i class="fa-solid fa-truck-medical"></i> Contatos de Emergência</h5>
                            </div>
                            <div class="card-body bg-light p-4">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fas fa-user"></i> Pessoa</th>
                                            <th><i class="fas fa-road"></i> Logradouro</th>
                                            <th><i class="fas fa-phone"></i> Telefone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            $pessoa = "$grau | $nome";
                                            $telefone = !empty($telefone) ? $telefone : "Não Informado";
                                            $celular = !empty($celular) ? $celular : "Não Informado";
                                            $telefones = $telefone . "<br>" . $celular;
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($pessoa) ?></td>
                                                <td><?= htmlspecialchars($endereco) ?: 'Não informado' ?></td>
                                                <td><?= $telefones ?></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ANEXOS  -->
                        <?php
                        $sql = "SELECT D.* , T.nome as dsTipoDoc
                                FROM rh_documentos D
                                INNER JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
                                WHERE idPessoa = :idPessoa";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':idPessoa', $id, PDO::PARAM_STR);
                        $stmt->execute();
                        ?>
                        <div class="card shadow-lg border-0">
                            <div class="card-header text-white cartao">
                                <h5 class="mb-0"><i class="fa-regular fa-folder-open"></i> Anexos</h5>
                            </div>
                            <div class="card-body bg-light p-4">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fa-regular fa-font-awesome"></i> Tipo</th>
                                            <th><i class="fas fa-file"></i> Arquivo</th>
                                            <th><i class="fas fa-calendar"></i> Validade</th>
                                            <th style="text-align: center;"><i class="fa-solid fa-circle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            $acao = "<button class='btn btn-sm btn-outline-primary' onclick=\"f_mostra($idDoc, $idPessoa, '$arquivo')\"><i class='fa-solid fa-magnifying-glass'></i></button>";
                                            $acao .= "<button class='btn btn-sm btn-outline-secondary' onclick=\"f_down($idDoc, $idPessoa, '$arquivo')\"><i class='fa-solid fa-download'></i></button>";
                                            $acao .= "<button class='btn btn-sm btn-outline-success' onclick=\"f_email($idDoc, $idPessoa, '$arquivo')\"><i class='fa-regular fa-envelope'></i></button>";
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($dsTipoDoc) ?></td>
                                                <td><?= htmlspecialchars($nome_original) ?></td>
                                                <td style='text-align: center;' class='nowrap'><?= htmlspecialchars($data_validade) ?></td>
                                                <td style='text-align: center;' class='nowrap '><?= $acao ?></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- SEGUNDA COLUNA -->
                    <div class='col-sm-5'>
                        <div class="card mt-2">
                            <div class="card-header text-white cartao clear-fix">
                                <div class="float-start">
                                    <h5 class='text-center'><i class="fa-regular fa-hourglass"></i>&nbsp;LINHA DO TEMPO da Pessoa</h5>
                                </div>
                                <div class="float-end">
                                    <button class='btn btn-sm btn-dark' onclick='inclui_ldt()'>Incluir</button>
                                </div>

                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <?php
                                $sql = "SELECT A.*, T.*, U.login 
                                        FROM RH.rh_pessoas_ldt A 
                                        INNER JOIN rh_ldt_tipos T on T.idAcaoTipo = A.idAcaoTipo
                                        INNER JOIN rh_usuarios U on U.idUsuario = A.idUsuario
                                        WHERE A.idPessoa = :idPessoa";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_STR);
                                $stmt->execute();
                                ?>
                                <table class="table table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th><i class="fa-solid fa-circle-down"></i></th>
                                            <th style="width: 150px;">Em</th>
                                            <th style="width: 150px;">Por</th>
                                            <th>Descrição</th>
                                            <th class='text-end'><i class="fa-solid fa-bolt"></i> Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?PHP
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            $descricao = strip_tags($descricao);
                                            //
                                            //- e-Mail Enviado
                                            if ($idAcaoTipo == 5 && $idEmail > 0) {
                                                $link = "<a href='#!' class='btn btn-sm btn-outline-success' onClick='f_mostra_email($idEmail)'><i class='fa-solid fa-magnifying-glass'></i></a>";
                                            } else {
                                                $link = "<a href='#!' class='btn btn-sm btn-outline-success' onClick='f_mostra_cartao($idAcao)'><i class='fa-solid fa-magnifying-glass'></i></a>";;
                                            }
                                            if( $idUsuario == $_idUsuario){
                                                $link_del = "<a href='#!' class='btn btn-sm btn-outline-danger' onClick='f_del($idAcao)'><i class='fa-solid fa-trash'></i></a>";
                                            } else{
                                                $link_del = "";
                                            }
                                            
                                            //
                                            echo "<tr>
                                            <td><i class='fa $icone icon' style='color: $cor'></i></td>
                                            <td>$data</td>
                                            <td>$login</td>
                                            <td>$descricao</td>
                                            <td class='text-end' style=' white-space: nowrap;'>$link_del $link</td>
                                        </tr>";
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- FIM DA ROW -->
            </main>

            <!-- Modal Visualizar Documento -->
            <div class="modal fade" id="modalArquivo" tabindex="-1" aria-labelledby="modalArquivoLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalArquivoLabel">Visualização de Arquivo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <iframe id="iframeArquivo" src="" width="100%" height="600px" style="border:none;"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Envio de E-mail -->
            <div class="modal fade" id="modalEmail" tabindex="-1" aria-labelledby="modalEmailLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalEmailLabel">
                                <i class="fa-solid fa-envelope"></i> Enviar Documento por E-mail
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="emailForm">
                                <input type="hidden" id="idPessoa" name="idPessoa" value='<?= $idPessoa ?>'>
                                <input type="hidden" id="idDoc" name="idDoc">
                                <input type="hidden" id="arquivo" name="arquivo">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="emailDestinatario" class="form-label"><strong>Destinatário</strong></label>
                                        <input type="email" class="form-control" id="emailDestinatario" name='emailDestinatario' required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emailCC" class="form-label"><strong>CC</strong></label>
                                        <input type="email" class="form-control" id="emailCC" name='emailCC'>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emailCCO" class="form-label"><strong>CCO</strong></label>
                                        <input type="email" class="form-control" id="emailCCO" name='emailCCO'>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3">
                                        <label for="emailTitulo" class="form-label"><strong>Título</strong></label>
                                        <input type="text" class="form-control" id="emailTitulo" value="GERAR (RH) - Envio de documento" name='emailTitulo'>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3">
                                        <label for="emailCorpo" class="form-label"><strong>Corpo do E-mail</strong></label>
                                        <textarea class="form-control" id="emailCorpo" name='emailCorpo' rows="5"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class='col-sm-6'></div>
                                    <div class="col-sm-6 d-flex">
                                        <button type='button' class='btn btn-sm btn-outline-danger w-100 m-2' data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Fechar</button>
                                        <button type='reset' class='btn btn-sm btn-outline-secondary w-100 m-2'><i class="fa-solid fa-recycle"></i> Reset</button>
                                        <button type='button' class='btn btn-sm btn-outline-success w-100 m-2' onclick="f_email_commit()"><i class="fa-solid fa-share"></i> Enviar</button>
                                    </div>
                                </div>
                                <div id='msgEmail'></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Visualizar E-mail -->
            <div class="modal fade" id="modalVisualizarEmail" tabindex="-1" aria-labelledby="modalVisualizarEmailLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalVisualizarEmailLabel">
                                <i class="fa-solid fa-envelope"></i> Visualizar E-mail Enviado
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Destinatário -->
                                <div class="mb-3">
                                    <label class="form-label"><strong>📩 Destinatário:</strong></label>
                                    <div class="form-control" id="v_emailDestinatario"></div>
                                </div>

                            </div>
                            <div class="row">
                                <!-- CC (Com Cópia) -->
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label"><strong>📑 CC:</strong></label>
                                    <div class="form-control" id="v_emailCC"></div>
                                </div>

                                <!-- CCO (Com Cópia Oculta) -->
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label"><strong>🔒 CCO:</strong></label>
                                    <div class="form-control" id="v_emailCCO"></div>
                                </div>
                            </div>

                            <!-- Assunto -->
                            <div class="mb-3">
                                <label class="form-label"><strong>📌 Assunto:</strong></label>
                                <div class="form-control" id="v_emailAssunto"></div>
                            </div>

                            <!-- Corpo do E-mail -->
                            <div class="mb-3">
                                <label class="form-label"><strong>📝 Corpo do E-mail:</strong></label>
                                <div class="form-control bg-light p-2" id="v_emailCorpo"
                                    style="height: 120px; overflow-y: auto; white-space: pre-wrap;"></div>
                            </div>

                            <!-- Anexo -->
                            <div class="mb-3">
                                <label class="form-label"><strong>📎 Anexo:</strong></label>
                                <div class="d-flex align-items-center border p-2 rounded">
                                    <span id="v_emailAnexo" class="flex-grow-1">Nenhum anexo</span>
                                    <span id='v_botao'>
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="btnVisualizarAnexo">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </button>
                                        </spam>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark"></i> Fechar
                            </button>
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
                                <input type="hidden" name='idEmpresa' value="<?= $idEmpresa ?>">
                            </form>
                        </div>
                        <div id="msgLinhaDoTempo" class='h5 text-center'></div>
                        <div class="modal-footer" id="botoesLDT">
                            <button type="reset" class="btn btn-outline-secondary" onclick="$('#descricao').summernote('reset');" id='btnReset'><i class="fa-solid fa-eraser"></i> Reset</button>
                            <button type="button" class="btn btn-outline-success" onclick="salvarLinhaTempo()"><i class="fa-solid fa-save"></i> Salvar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal VISUALIZAR LINHA DO TEMPO -->
            <div class="modal fade" id="modalLinhaTempoVer" tabindex="-1" aria-labelledby="modalLinhaTempoLabel" aria-hidden="true">
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

            <!-- Aqui Termina o conteúdo da página -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/cpf.js"></script>
    <script src="js/rh_ficha_pessoa.js"></script>
</body>

</html>
<?PHP

function seletor_tipo($conn)
{
    global $conn;
    $sql = "SELECT * FROM rh_ldt_tipos;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $retorno = "<select class='form-select form-select-sm' id='idTipoAcao' name='idTipoAcao'>";
    if (empty($id)) $retorno .= "<option value='0' selected>Tipo de Atividade</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $retorno .= "<option value='$idAcaoTipo' style='color: $cor'>$nome</option>";
    }
    $retorno .= "</select>";
    return $retorno;
}