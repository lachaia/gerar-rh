<?php
//
//- candidatos.php | Portal do Candidato para Editar seu Currículo
//
session_start();

//- pessoa_id vem só da sessão aberta em auth.php - nunca de parâmetro do cliente,
//- senão qualquer um edita o currículo de qualquer pessoa só sabendo o ID dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    header('Location: index.php');
    exit;
}
$pessoa_id = (int) $_SESSION['candidato_idPessoa'];

//
//- RECUPERA DADOS PARA PREENCHER O FORMULÁRIO DO CURRÍCULO
//
    include_once "../app/includes/conexao_gerar.php";
    include_once "../app/includes/f_logs.php";

    $sql = "SELECT * 
            FROM rh_pessoas P 
            INNER JOIN rh_cv C on C.idPessoa = P.idPessoa 
            WHERE P.idPessoa = :idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idPessoa', $pessoa_id, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($linha);

    $idModulo = 7; // Currículo Vitae
    $agora = date("Y-m-d H:i:s");
    $idEmpresa = 1;
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

        #v_descricao {
            height: 150px;
            /* Define a altura fixa */
            overflow-y: auto;
            /* Adiciona scroll vertical se o conteúdo ultrapassar a altura */
            border: 1px solid #ccc;
            /* Opcional: adiciona uma borda para visualização */
            padding: 10px;
            /* Opcional: adiciona um espaçamento interno */
        }

        .note-editable {
            color: black !important;
            /* Define a cor do texto como branco */
            background-color: white;
            /* Opcional: define um fundo escuro para melhor contraste */
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <div id="layoutSidenav">
        <div id="layoutSidenav_content">
            <!-- Aqui COMEÇA o conteúdo da página -->
            <main class="container-fluid">
                <div class="card mt-2">
                    <div class="card-header bg-dark text-light d-flex align-items-center">
                        <img src="../app/imagens/logo_resized.png" alt="Logo_GERAR" style="width:50px;" class="me-2">
                        <h3 class="mb-0">RH-GERAR | Currículo de Candidato</h3>
                        
                        <button type="button" class="btn btn-outline-light btn-sm ms-auto" onClick="f_voltar()">
                            <i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar
                        </button>
                    </div>
                </div>
                <div class='row'>
                    <div id="msgAlertaPessoa" class="text-center h4"></div>
                    <!-- PRIMEIRA COLUNA -->
                    <div class='col-sm-6'>

                        <!-- FORM - DADOS PESSOAIS / DEFICIÊNCIA / LINKEDIN / UPLOAD-->
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'><i class="fa-regular fa-address-card"></i> DADOS PESSOAIS</h5>
                                <div class='float-end'></div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <form id='formCV'>
                                    <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $pessoa_id ?>'>
                                    <input type="hidden" id='idCV' name='idCV' value='<?= $idCV ?>'>

                                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
                                    <div class="container mt-4" onChange='showBtnCV()'>

                                        <!-- Bloco dos Dados Pessoais -->
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">CPF *</label>
                                                <input type="text" id="cpf" name="cpf" class='form-control' required
                                                    oninput="this.value = maskCPF(this.value)"
                                                    onblur="testar_cpf(this)"
                                                    maxlength="14" placeholder="000.000.000-00">
                                            </div>

                                            <div class="col-md-9">
                                                <label class="form-label">Nome completo *</label>
                                                <input type="text" class="form-control" id="nome" name="nome" required onblur='testa_bloco1()'>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label">Nome social</label>
                                                <input type="text" class="form-control" id="nomeSocial" name="nomeSocial" onblur='testa_bloco1()'>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Sexo Biológico</label>
                                                <select class="form-select" id="sexo" name="sexo" onblur='testa_bloco1()'>
                                                    <option value="">Selecione</option>
                                                    <option value="F">Feminino</option>
                                                    <option value="M">Masculino</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Telefone *</label>
                                                <input type="text" id="telefone" name="telefone" required class="form-control"
                                                    oninput="this.value = maskTelefone(this.value)"
                                                    maxlength="15" placeholder="(00) 00000-0000" onblur='testa_bloco1()'>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">E-mail *</label>
                                                <input type="email" class="form-control" id="email" name="email" required onblur='testa_bloco1()'>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Nacionalidade*</label>
                                                <input type="text" class="form-control" id="nacionalidade" name="nacionalidade" onblur='testa_bloco1()'>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Grau Escolar *</label>
                                                <?= f_escolaridade(0, $conn); ?>
                                            </div>
                                        </div>
                                    
                                        <!-- Bloco de Gênero -->
                                        <div class="card bg-light shadow-sm p-3 rounded-3 border-0">
                                            <div class="row align-items-center">
                                                <div class="col-sm-2">
                                                    <label class="form-label fw-bold text-primary"><i class="fa-solid fa-venus-mars"></i> Gênero</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="F"
                                                                <?php echo ($genero == 'F') ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="feminino">Feminino</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="M"
                                                                <?php echo ($genero == 'M') ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="masculino">Masculino</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="N"
                                                                <?php echo ($genero == 'N') ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="nao-binario">Não-Binário</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="O"
                                                                <?php echo ($genero == 'O') ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="outros">Outros</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="0"
                                                                <?php echo ($genero == '0') ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="prefiro-nao-responder">Prefiro não responder</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2 border-start border-3 border-dark ps-3">
                                                    <input class="form-check-input me-2" type="checkbox" id="deficiente" name="deficiente" value="<?= $deficiente ?>"
                                                        onclick="toggleDeficiencia(this)" <?php echo ($deficiente == '1') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="deficiente">Deficiente?</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bloco de Deficiência (inicia oculto) -->
                                        <div id="deficiencia-bloco" class="card bg-light shadow-sm p-3 mt-3 rounded-3 border-0 collapse">
                                            <div class="row align-items-center">
                                                <div class="col-sm-2">
                                                    <label class="form-label fw-bold text-primary"><i class="fa-brands fa-accessible-icon"></i> Deficiência</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="fisica" id="fisica" value="<?= $def_fisica ?>"
                                                                onchange='toggleDefValue(this)' <?php echo ($def_fisica == 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="fisica">Física</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="visual" id="visual" value="<?= $def_visual ?>"
                                                                onchange='toggleDefValue(this)' <?php echo ($def_visual == 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="visual">Visual</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="auditiva" id="auditiva" value="<?= $def_auditiva ?>"
                                                                onchange='toggleDefValue(this)' <?php echo ($def_auditiva == 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="auditiva">Auditiva</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="mental" id="mental" value="<?= $def_mental ?>"
                                                                onchange='toggleDefValue(this)' <?php echo ($def_mental == 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="mental">Mental</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="intelectual" id="intelectual" value="<?= $def_intelectual ?>"
                                                                onchange='toggleDefValue(this)' <?php echo ($def_intelectual == 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="intelectual">Intelectual</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="autista" id="autista" value="<?= $def_autista ?>"
                                                                onchange='toggleDefValue(this)' <?php echo ($def_autista == 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="autista">Autista</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2 border-start border-3 border-dark ps-3">
                                                    <label class="form-label" for="cid">CID</label>
                                                    <input class="form-control" type="text" id="cid" name="cid" placeholder="Código CID" value="<?= $cid ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row collapse" id='btnSalvaCV'>
                                            <div class="d-flex w-100 gap-2">
                                                <button type="button" class="btn btn-outline-secondary flex-fill" onclick='btnResetBloco1()'>
                                                    <i class="fa-solid fa-recycle"></i> Reset (desfaz)
                                                </button>
                                                <button type="button" class="btn btn-outline-primary flex-fill" onclick='salvar_cv()'>
                                                    <i class="fa-solid fa-download"></i> Salvar
                                                </button>
                                            </div>                                            
                                        </div>

                                        <!-- Bloco LinkedIn -->
                                        <div class="card bg-light shadow-sm p-3 mt-3 rounded-3 border-0">
                                            <label class="form-label fw-bold" for="linkedin">Perfil do LinkedIn</label>
                                            
                                            <div class="input-group">
                                                <input class="form-control" type="text" id="linkedin" name="linkedin" 
                                                    placeholder="Insira o link do seu perfil" 
                                                    value='<?= $linkedin ?>' onChange='showBtnCV()'>
                                                
                                                <button class="btn btn-primary" type="button" onClick="mostra_linkedin()">
                                                    <i class="fa-brands fa-linkedin"></i> Abrir
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Bloco UPLOAD  -->
                                        <div class="card bg-light shadow-sm p-3 mt-3 rounded-3 border-0">
                                            <label class="form-label fw-bold" for="arquivo"><b>Opcional</b>: envie o arquivo do CV personalizado</label>
                                            <div class="input-group">
                                                <input class="form-control" type="file" id="arquivo" name="arquivo" accept=".pdf, .doc, .docx">
                                                <button type='button' class="btn btn-primary" onclick="envia_arquivoCV()">Enviar</button>
                                            </div>
                                            <div id="mensagemUpload" class="mt-2">
                                                <?php
                                                if (! empty($arquivo)) {
                                                    echo '<i class="fa-regular fa-folder-open"></i> ' . "<b>Arquivo de CV: </b> $arquivo <a href='#' onclick='mostra_cv($pessoa_id,\"$arquivo\")'><i class='fa-solid fa-magnifying-glass'></i></a>";
                                                }
                                                ?>
                                            </div> <!-- Exibe mensagens de sucesso/erro -->
                                        </div>

                                    </div>
                                    <div id='msgformCV' class='text-center'></div>
                                </form>
                            </div>
                        </div>

                        <!-- GRID FORMAÇÃO ACADÊMICA -->
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'> <i class="fa-solid fa-user-graduate"></i> FORMAÇÃO
                                    ACADÊMICA</h5>
                                <div class='float-end'>
                                    <button type='button' class='btn btn-outline-success btn-sm'
                                        onClick='fa_incluir()'><i class="fa-solid fa-plus"></i> Novo</button>
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
                                            <th style='width: 50px' class="text-center"><i
                                                    class="fa-solid fa-circle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $sql = "SELECT C.id, N.nivel, C.curso, C.ano_conclusao, I.sigla
                                                    FROM rh_cv_fa C
                                                    INNER JOIN rh_fa_niveis N on N.idNivel = C.idNivel
                                                    INNER JOIN rh_fa_instituicoes I on I.idInstituicao = C.idInstituicao
                                                    WHERE C.idPessoa = :idPessoa
                                                    ORDER BY C.ano_conclusao";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':idPessoa', $pessoa_id, PDO::PARAM_INT);
                                    $stmt->execute();

                                    ?>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            //
                                            $acoes = "<button type='button' class='btn btn-outline-success btn-sm' onClick='fa_ver($id)'><i class='fa-solid fa-magnifying-glass' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
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

                        <!-- GRID IDIOMAS -->
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'> <i class="fa-solid fa-globe"></i> IDIOMAS</h5>
                                <div class='float-end'>
                                    <button type='button' class='btn btn-outline-success btn-sm'
                                        onClick='idioma_incluir()'><i class="fa-solid fa-plus"></i> Novo</button>
                                </div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <table class='table table-striped table-hover table-sm'>
                                    <thead>
                                        <tr>
                                            <th>Idioma</th>
                                            <th>Fluência</th>
                                            <th>Incluído em</th>
                                            <th>Incluído por</th>
                                            <th style='width: 50px' class="text-center"><i
                                                    class="fa-solid fa-circle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $sql = "SELECT C.*, I.nome as nmIdioma, F.nome as nmFluencia, U.login,
                                                DATE_FORMAT(L.dtLogin, '%d/%m/%Y %H:%i') AS dtLogin
                                                FROM rh_cv_idiomas C
                                                INNER JOIN rh_idiomas I on I.idIdioma = C.idIdioma
                                                INNER JOIN rh_fluencias F on F.idFluencia = C.idFluencia
                                                INNER JOIN rh_logins L on L.idLogin = C.idLogin
                                                INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                                                WHERE C.idPessoa = :idPessoa";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':idPessoa', $pessoa_id, PDO::PARAM_INT);
                                    $stmt->execute();

                                    ?>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            //
                                            $acoes = "<button type='button' class='btn btn-outline-primary btn-sm' onClick='idioma_editar($id)'><i class='fa-regular fa-pen-to-square' data-bs-toggle='tooltip' title='Editar!'></i></button>";
                                            $acoes .= "<button type='button' class='btn btn-outline-danger btn-sm' onClick='idioma_excluir($id)'><i class='far fa-trash-alt' data-bs-toggle='tooltip' title='Excluir!'></i></button>";
                                            //
                                            echo "<tr>";
                                            echo "<td>$nmIdioma</td>";
                                            echo "<td>$nmFluencia</td>";
                                            echo "<td>$dtLogin</td>";
                                            echo "<td>$login</td>";
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

                        <!-- GRID EXPERIÊNCIA PROFISSIONAL -->
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'><i class="fa-solid fa-briefcase"></i> EXPERIÊNCIA
                                    PROFISSIONAL</h5>
                                <div class='float-end'>
                                    <button type='button' class='btn btn-outline-success btn-sm'
                                        onClick='exp_incluir()'><i class="fa-solid fa-plus"></i> Novo</button>
                                </div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <table class='table table-striped table-hover table-sm'>
                                    <thead>
                                        <tr>
                                            <th class="text-center">Início</th>
                                            <th class="text-center">Fim</th>
                                            <th class="text-center">Atual</th>
                                            <th>Empresa</th>
                                            <th>Cargo</th>
                                            <th style='width: 50px' class="text-center"><i
                                                    class="fa-solid fa-circle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $sql = "SELECT E.*
                                                    FROM rh_cv_exp E
                                                    WHERE E.idPessoa = :idPessoa
                                                    ORDER BY ano_ini, ano_fim";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':idPessoa', $pessoa_id, PDO::PARAM_INT);
                                    $stmt->execute();

                                    ?>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            if (empty($ativo)) $ativo = '-';
                                            else $ativo = '<i class="fa-solid fa-circle" style="color: green"></i>';
                                            //
                                            if(empty($ano_fim)) $ano_fim = '-';
                                            //
                                            $acoes = "<button type='button' class='btn btn-outline-success btn-sm' onClick='exp_ver($id)'><i class='fa-solid fa-magnifying-glass' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
                                            $acoes .= "<button type='button' class='btn btn-outline-primary btn-sm' onClick='exp_editar($id)'><i class='fa-regular fa-pen-to-square' data-bs-toggle='tooltip' title='Editar!'></i></button>";
                                            $acoes .= "<button type='button' class='btn btn-outline-danger btn-sm'  onClick='exp_excluir($id)'><i class='far fa-trash-alt' data-bs-toggle='tooltip' title='Excluir!'></i></button>";
                                            echo "<tr>";
                                            echo "<td class='text-center'>$ano_ini</td>";
                                            echo "<td class='text-center'>$ano_fim</td>";
                                            echo "<td class='text-center'>$ativo</td>";
                                            echo "<td>$empresa</td>";
                                            echo "<td>$cargo</td>";
                                            echo "<td class='text-end' style='white-space: nowrap;'>$acoes</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>

                        <!-- GRID CONQUISTAS & CERTIFICADOS -->
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'><i class="fa-solid fa-medal"></i> Conquistas ou Certificados</h5>
                                <div class='float-end'>
                                    <button type='button' class='btn btn-outline-success btn-sm'
                                        onClick='con_incluir()'><i class="fa-solid fa-plus"></i> Novo</button>
                                </div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <table class='table table-striped table-hover table-sm'>
                                    <thead>
                                        <tr>
                                            <th class="text-center">Ano</th>
                                            <th>Tipo</th>
                                            <th>Título</th>
                                            <th style='width: 50px' class="text-center"><i
                                                    class="fa-solid fa-circle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $sql = "SELECT C.*, T.nome as dsTipo
                                                FROM rh_cv_conq C
                                                INNER JOIN rh_conqTipos T on T.idConqTipo = C.idConqTipo
                                                WHERE C.idPessoa = :idPessoa
                                                ORDER BY ano";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':idPessoa', $pessoa_id, PDO::PARAM_INT);
                                    $stmt->execute();

                                    ?>
                                    <tbody>
                                        <?php
                                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($linha);
                                            if (empty($ativo)) $ativo = '-';
                                            else $ativo = '<i class="fa-solid fa-circle" style="color: green"></i>';
                                            //
                                            $acoes  = "<button type='button' class='btn btn-outline-success btn-sm' onClick='con_ver($id)'><i class='fa-solid fa-magnifying-glass' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
                                            $acoes .= "<button type='button' class='btn btn-outline-primary btn-sm' onClick='con_editar($id)'><i class='fa-regular fa-pen-to-square' data-bs-toggle='tooltip' title='Editar!'></i></button>";
                                            $acoes .= "<button type='button' class='btn btn-outline-danger btn-sm'  onClick='con_excluir($id)'><i class='far fa-trash-alt' data-bs-toggle='tooltip' title='Excluir!'></i></button>";
                                            echo "<tr>";
                                            echo "<td class='text-center'>$ano</td>";
                                            echo "<td>$dsTipo</td>";
                                            echo "<td>$titulo</td>";
                                            echo "<td class='text-end' style='white-space: nowrap;'>$acoes</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>

                        <!-- FORM - DIVERSIDADE -->
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'><i class="fa-solid fa-hands-holding-circle"></i> DIVERSIDADE</h5>
                                <div class='float-end'></div>
                            </div>
                            <div class="card-body container" style="font-size: 13px;">
                                <form name='formDiversidade' id='formDiversidade'>
                                    <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $pessoa_id ?>'>
                                    <input type="hidden" id='idCV' name='idCV' value='<?= $idCV ?>'>
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        <i class="fa-solid fa-circle-info" style='font-size: 18px'></i> O preenchimento desta seção é opcional. As informações
                                        serão usadas em todos os processos que utilizem a solução de Diversidade. Nenhum dado será utilizado como critério de eliminação.
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label for="cidade">Local de Origem</label>
                                            <div class="input-group">
                                                <input type="text" id="cidade" name="cidade" class='form-control' placeholder="Digite o nome da cidade" onchange='troggle_on_btn_diversidade()'>
                                                <span class="input-group-text"><a href="#" data-bs-toggle="modal" data-bs-target="#modalOrigem"><i class="fa-regular fa-circle-question"></i></a></span>
                                            </div>
                                            <input type="hidden" id="cidade_id" name="cidade_id">
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="cor">Qual sua cor ou raça</label>
                                            <div class="input-group">
                                                <select name="cor" id="cor" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                                    <option value="">Escolha</option>
                                                    <option value="Amarela">Amarela</option>
                                                    <option value="Branca">Branca</option>
                                                    <option value="Indigena">Indígena</option>
                                                    <option value="Parda">Parda</option>
                                                    <option value="Preta">Preta</option>
                                                    <option value="não">Prefiro não responder</option>
                                                </select>
                                                <span class="input-group-text">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalCorRaca">
                                                        <i class="fa-regular fa-circle-question"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <label for="pronome">Pronome adequado para você</label>
                                            <select name="pronome" id="pronome" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                                <option value="">Escolha</option>
                                                <option value="ela">Ela / Dela</option>
                                                <option value="ele">Ele / Dele</option>
                                                <option value="não">Prefiro não responder</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-sm-4">
                                            <label for="orientacao">Sua orientação sexual</label>
                                            <div class="input-group">
                                                <select name="orientacao" id="orientacao" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                                    <option value="">Escolha</option>
                                                    <option value="Assexual">Assexual</option>
                                                    <option value="Bissexual">Bissexual</option>
                                                    <option value="Heterossexual">Heterossexual</option>
                                                    <option value="Homossexual">Homossexual</option>
                                                    <option value="Pansexual">Pansexual</option>
                                                    <option value="não">Prefiro não responder</option>
                                                </select>
                                                <span class="input-group-text">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalOrientacao">
                                                        <i class="fa-regular fa-circle-question"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <label for="pronome">Sua identidade de gênero</label>
                                            <div class="input-group">
                                                <select name="identgenero" id="identgenero" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                                    <option value="">Escolha</option>
                                                    <option value="Cisgênero">Cisgênero</option>
                                                    <option value="Transgênero">Transgênero</option>
                                                    <option value="não">Prefiro não responder</option>
                                                </select>
                                                <span class="input-group-text">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalIdentGenero">
                                                        <i class="fa-regular fa-circle-question"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="col text-end mt-3 collapse" id='btnDiversidade'>
                                                <button type="button" class="btn btn-outline-secondary px-4" onclick='btnResetBloco2()'><i class="fa-solid fa-recycle"></i> Reset</button>
                                                <button type="button" class="btn btn-outline-primary px-4" onclick='salvar_cv2()'><i class="fa-solid fa-download"></i> Salvar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div id='msgformDiversidade' class="text-center"></div>
                                </div>
                            </div>
                        </div>

                        <!-- FORM - HABILIDADES -->
                        <div class="card">
                            <div class="card-header clear-fix align-middle">
                                <h5 class='float-start mt-1'><i class="fa-solid fa-award"></i> HABILIDADES</h5>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <input type="text" id="inputSkill" class="form-control" placeholder="Digite uma habilidade..." maxlength="20">
                                    <button class="btn btn-primary" onclick="addSkill()">Adicionar</button>
                                </div>
                                <div id="skillsContainer" class="d-flex flex-wrap gap-2"></div>
                                <div class="text-end collapse" id='btnHabilidades'><button class="btn btn-outline-success mt-3" onclick="saveSkills()">Salvar</button></div>

                            </div>
                            <div id='msgHabilidades' class="text-center h5"></div>
                        </div>

                    </div><!-- FIM DA ROW -->

                    <!-- MODAL - faModalVer | Formação Acadêmica - VISUALIZAR -->
                    <div class="modal fade" id="faModalVer" tabindex="-1" aria-labelledby="visualizarfaLabel"
                        aria-hidden="true" data-bs-backdrop="static">
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
                                            <p id="v_curso" class="visCampo"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Instituição</label>
                                            <p id="v_instituicao" class="visCampo"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <label class="col-form-label">Sigla</label>
                                            <p id="v_sigla" class="visCampo text-center"></p>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="col-form-label">Nível</label>
                                            <p id="v_nivel" class="visCampo text-center"></p>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="col-form-label">Conclusão</label>
                                            <p id="v_ano_conclusao" class="visCampo text-center"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Arquivo do Diploma/Certificado</label>
                                            <p id="fa_arquivo" class="visCampo"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                data-bs-dismiss="modal">
                                                <i class="fa fa-close"></i> Fechar
                                            </button>
                                        </div>
                                        <span id='divQuando' class="mt-2 ms-2"
                                            style='font-size: 12px; font-weight: 200'></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - faModalInc | Formação Acadêmica - INCLUIR -->
                    <div class="modal fade" id="faModalInc" tabindex="-1" aria-labelledby="incluirfaLabel"
                        aria-hidden="true" data-bs-backdrop="static">
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
                                        <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $pessoa_id ?>'>
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
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Arquivo para upload</label>
                                                <input type="file" id='arquivo' name='arquivo' class='form-control'>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesFA'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (azul) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetFA'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='fa_incluir_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformIncFA' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - faMovalIncIE | Formação Acadêmica - INCLUIR IE-->
                    <div class="modal fade" id="faModalIncIE" tabindex="-1" aria-labelledby="incluirfaLabelIE"
                        aria-hidden="true" data-bs-backdrop="static">
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
                                                <input type="text" id="_nomeInstituicao" name="_nomeInstituicao"
                                                    class="form-control">
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
                                                <input type="text" id='_pais' name='_pais' class='form-control'
                                                    value='Brasil'>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="btn-group" id="botoes_fa_id">
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (azul) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1">
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='f_inclui_ie_salva()'>
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

                    <!-- MODAL - faModalAlt | Formação Acadêmica - ALTERAR -->
                    <div class="modal fade" id="faModalAlt" tabindex="-1" aria-labelledby="alterarfaLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-regular fa-pen-to-square"></i> ALTERAR Formação Acadêmica</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form action="#" id='formAltFA'>
                                        <input type="hidden" id='idCurso' name='idCurso' value=''>
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
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Arquivo para upload</label>
                                                <input type="file" id='arquivo' name='arquivo' class='form-control'>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesFAAlt'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (azul) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetFAAlt'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='fa_editar_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformAltFA' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - expModalVer | VISUALIZAR | EXPERIÊNCIA PROFISSIONAL -->
                    <div class="modal fade" id="expModalVer" tabindex="-1" aria-labelledby="visualizarExpLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-eye"></i> Visualizar Experiência Profissional</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-10">
                                            <label class="col-form-label">Empresa</label>
                                            <p id="v_empresa" class="visCampo"></p>
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Atual</label>
                                            <p id="v_atual" class="visCampo"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-8">
                                            <label class="col-form-label">Cargo</label>
                                            <p id="v_cargo" class="visCampo"></p>
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Início</label>
                                            <p id="v_ano_ini" class="visCampo"></p>
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Fim</label>
                                            <p id="v_ano_fim" class="visCampo"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Descrição</label>
                                            <p id="v_descricao" class="visCampo"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                data-bs-dismiss="modal">
                                                <i class="fa fa-close"></i> Fechar
                                            </button>
                                        </div>
                                        <span id='divQuandoExp' class="mt-2 ms-2"
                                            style='font-size: 12px; font-weight: 200'></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - expModalInc | INCLUIR | EXPERIÊNCIA PROFISSIONAL -->
                    <div class="modal fade" id="expModalInc" tabindex="-1" aria-labelledby="incluirExpLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-briefcase"></i> Incluir Experiência Profissional</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form action="#" id='formIncExp'>
                                        <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $pessoa_id ?>'>
                                        <div class="row mb-3">
                                            <div class="col-sm-10">
                                                <label class="col-form-label">Empresa</label>
                                                <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Nome da Empresa">
                                            </div>
                                            <div class="col-sm-2 mt-4">
                                                <div class="form-check form-switch mt-3">
                                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="0" onchange='exp_atual(this)'>
                                                    <label class="form-check-label" for="ativo">Atual</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-8">
                                                <label class="col-form-label">Cargo</label>
                                                <input type="text" id="cargo" name="cargo" class="form-control" placeholder="Informe o cargo ocupado">
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="col-form-label">Início</label>
                                                <input type="number" id="ano_ini" name="ano_ini" class="form-control" placeholder="ano">
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="col-form-label">Fim</label>
                                                <input type="number" id="ano_fim" name="ano_fim" class="form-control" placeholder="ano">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Descrição</label>
                                                <textarea name="descricao" id="descricao" class="form-control" placeholder="Descreva aqui sua experiência"></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesExp'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (azul) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetExp'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='exp_incluir_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformIncExp' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - expModalAlt | ALTERAR | EXPERIÊNCIA PROFISSIONAL -->
                    <div class="modal fade" id="expModalAlt" tabindex="-1" aria-labelledby="alterarExpLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-briefcase"></i> ALTERAR Experiência Profissional</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form action="#" id='formAltExp'>
                                        <input type="hidden" id='idExp' name='idExp' value=''>
                                        <div class="row mb-3">
                                            <div class="col-sm-10">
                                                <label class="col-form-label">Empresa</label>
                                                <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Nome da Empresa">
                                            </div>
                                            <div class="col-sm-2 mt-4">
                                                <div class="form-check form-switch mt-3">
                                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="0" onchange='exp_atual(this)'>
                                                    <label class="form-check-label" for="ativo">Atual</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-8">
                                                <label class="col-form-label">Cargo</label>
                                                <input type="text" id="cargo" name="cargo" class="form-control" placeholder="Informe o cargo ocupado">
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="col-form-label">Início</label>
                                                <input type="number" id="ano_ini" name="ano_ini" class="form-control" placeholder="ano">
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="col-form-label">Fim</label>
                                                <input type="number" id="ano_fim" name="ano_fim" class="form-control" placeholder="ano">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Descrição</label>
                                                <textarea name="descricao" id="descricao" class="form-control" placeholder="Descreva aqui sua experiência"></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesExpAlt'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (azul) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetExpAlt'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='exp_editar_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformAltExp' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - idiModalInc | INCLUIR | IDIOMAS -->
                    <div class="modal fade" id="idiModalInc" tabindex="-1" aria-labelledby="incluirIdiLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-globe"></i> Incluir IDIOMA</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form action="#" id='formIncIdi'>
                                        <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $pessoa_id ?>'>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="col-form-label">Idioma</label>
                                                <?= seletor_idiomas() ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="col-form-label">Idioma</label>
                                                <?= seletor_fluencia() ?>
                                            </div>

                                        </div>

                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesIdi'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (azul) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetIdi'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='idioma_incluir_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformIncIdi' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - idiModalAlt | EDITAR | IDIOMAS -->
                    <div class="modal fade" id="idiModalAlt" tabindex="-1" aria-labelledby="editarIdiLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-globe"></i> Alterar IDIOMA</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form action="#" id='formAltIdi'>
                                        <input type="hidden" id='idIdi' name='idIdi' value=''>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="col-form-label">Idioma</label>
                                                <?= seletor_idiomas() ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="col-form-label">Idioma</label>
                                                <?= seletor_fluencia() ?>
                                            </div>

                                        </div>

                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesIdiAlt'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (azul) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetIdiAlt'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='idioma_editar_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformAltIdi' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - conModalInc | INCLUIR | CONQUISTAS & CERTIFICADOS -->
                    <div class="modal fade" id="conModalInc" tabindex="-1" aria-labelledby="incluirConLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-briefcase"></i> Incluir Certificado / Conquista</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form id="formIncCon" enctype="multipart/form-data" method="post">
                                        <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $pessoa_id ?>'>
                                        <div class="row mb-3">
                                            <div class="col-sm-10">
                                                <label class="col-form-label">Tipo</label>
                                                <?= seletorConquistas() ?>
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="col-form-label">Ano</label>
                                                <input type="number" id="ano" name="ano" class="form-control" placeholder="Ano">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Título do Certificado/Conquista</label>
                                                <input type='text' name="titulo" id="titulo" class="form-control" placeholder="Título do Certificado/Conquista">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Descrição</label>
                                                <textarea name="descricao" id="descricao" class="form-control" placeholder="Descreva aqui"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Arquivo para upload</label>
                                                <input type="file" id='arquivoCert' name='arquivoCert' class='form-control'>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesCon'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (cinza escuro) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetCon' onClick='btn_reset_con()'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='con_incluir_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformIncCon' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - conModalVer | VISUALIZAR | CERTIFICADO OU CONQUISTA -->
                    <div class="modal fade" id="conModalVer" tabindex="-1" aria-labelledby="visualizarConLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-eye"></i> Visualizar Certificado/Conquista</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-10">
                                            <label class="col-form-label">Tipo</label>
                                            <p id="v_tipo" class="visCampo"></p>
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="col-form-label">Ano</label>
                                            <p id="v_ano" class="visCampo"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Título</label>
                                            <p id="v_titulo" class="visCampo"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Descrição</label>
                                            <p id="v_descricao" class="visCampo"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <label class="col-form-label">Arquivo do Certificado</label>
                                            <p id="v_arquivo" class="visCampo"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                data-bs-dismiss="modal">
                                                <i class="fa fa-close"></i> Fechar
                                            </button>
                                        </div>
                                        <span id='divQuandoExp' class="mt-2 ms-2"
                                            style='font-size: 12px; font-weight: 200'></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - conModalAlt | EDITAR | CONQUISTAS & CERTIFICADOS -->
                    <div class="modal fade" id="conModalAlt" tabindex="-1" aria-labelledby="editarConLabel"
                        aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="background-color: gainsboro">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <h5><i class="fa-solid fa-pen-to-square"></i> Editar Certificado / Conquista</h5>
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form action="#" id='formAltCon' enctype="multipart/form-data" method="post">
                                        <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $pessoa_id ?>'>
                                        <input type="hidden" id='idConq' name='idConq' value=''>
                                        <div class="row mb-3">
                                            <div class="col-sm-10">
                                                <label class="col-form-label">Tipo</label>
                                                <?= seletorConquistas() ?>
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="col-form-label">Ano</label>
                                                <input type="number" id="ano" name="ano" class="form-control" placeholder="Ano">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Título do Certificado/Conquista</label>
                                                <input type='text' name="titulo" id="titulo" class="form-control" placeholder="Título do Certificado/Conquista">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Descrição</label>
                                                <textarea name="descricao" id="descricao" class="form-control" placeholder="Descreva aqui"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <label class="col-form-label">Arquivo para upload</label>
                                                <input type="file" id='arquivoCert' name='arquivoCert' class='form-control'>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="btn-group" id='divBotoesConAlt'>
                                                <!-- Botão Fechar (vermelho) -->
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                                    data-bs-dismiss="modal">
                                                    <i class="fa fa-close"></i> Fechar
                                                </button>
                                                <!-- Botão Reset (cinza escuro) -->
                                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                                    id='btnResetConAlt' onClick='btn_reset_con_alt()'>
                                                    <i class="fa fa-undo"></i> Reset
                                                </button>
                                                <!-- Botão Salvar (verde) -->
                                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                                    onclick='con_editar_salva()'>
                                                    <i class="fa fa-save"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                        <div id='msgformAltCon' class='text-center'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - modalOrigem | TEXTO EXPLICATIVO | Importância da Naturalidade -->
                    <div class="modal fade" id="modalOrigem">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Qual a importância de preencher sua naturalidade?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>O local de origem de uma pessoa, além de influenciar sua cultura e costumes, também pode impactar suas oportunidades de emprego.</p>
                                    <p>No Brasil, muitas cidades concentram grande parte das vagas de trabalho, o que leva candidatos de outras regiões a enfrentarem desafios extras para conquistar uma posição no mercado. Além do esforço necessário para se inserir profissionalmente, essas pessoas podem enfrentar preconceitos relacionados ao seu local de origem.</p>
                                    <p>Nosso compromisso é transformar essa realidade por meio de dados, promovendo ações afirmativas e aprimorando as estratégias de Recrutamento & Seleção.</p>
                                    <p>É importante ressaltar que suas informações nunca serão utilizadas para excluir candidatos em processos seletivos. Pelo contrário, elas contribuem para análises de diversidade e ajudam a minimizar vieses na tomada de decisão.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - modalCorRaca | TEXTO EXPLICATIVO | Importância da Cor / Raça -->
                    <div class="modal fade" id="modalCorRaca">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Qual a importância de preencher sua cor/raça?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>A identidade racial de uma pessoa pode impactar diversos aspectos da sua vida, incluindo acesso à renda, serviços, oportunidades profissionais e muito mais. No Brasil, ainda há desigualdades estruturais que afetam diferentes grupos de maneira distinta.</p>
                                    <p>No ambiente de trabalho, essa realidade se reflete em diferenças significativas na distribuição de oportunidades. Alguns grupos enfrentam mais barreiras para inserção e crescimento profissional, o que se torna ainda mais evidente em cargos de liderança.</p>
                                    <p>O objetivo é transformar esse cenário por meio da coleta e análise de dados, promovendo estratégias afirmativas dentro dos processos de Recrutamento & Seleção.</p>
                                    <p>É importante reforçar que essas informações jamais serão utilizadas como critério de exclusão em seleções. Pelo contrário, elas permitem que as empresas ampliem suas iniciativas de diversidade e reduzam vieses nas contratações.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - modalOrientacao | TEXTO EXPLICATIVO | Importância da Orientação Sexual -->
                    <div class="modal fade" id="modalOrientacao">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Qual a importância de preencher sua orientação sexual?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>A orientação sexual ainda influencia a forma como o mérito e a capacidade profissional são percebidos. Em algumas organizações, visões conservadoras podem impactar as decisões de contratação, limitando oportunidades para pessoas não heterossexuais.</p>
                                    <p>O propósito deste trabalho é transformar essa realidade por meio de dados e apoiar iniciativas afirmativas dentro das estratégias de Recrutamento & Seleção.</p>
                                    <p>É importante ressaltar que essas informações jamais serão utilizadas como critério de exclusão em processos seletivos. Pelo contrário, elas permitem análises mais abrangentes sobre diversidade e auxiliam na redução de vieses durante a seleção.</p>
                                    <p><strong>Assexual</strong> é a pessoa que experimenta pouco ou nenhuma atração sexual.</p>
                                    <p><strong>Bissexual</strong> é a pessoa que sente atração por dois ou mais gêneros.</p>
                                    <p><strong>Heterossexual</strong> é a pessoa que sente atração por pessoas de gênero oposto ao seu.</p>
                                    <p><strong>Homossexual</strong> é a pessoa que sente atração por pessoas do mesmo gênero.</p>
                                    <p><strong>Pansexual</strong> é a pessoa que sente atração por pessoas de todos os gêneros.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL - modalIdentGenero | TEXTO EXPLICATIVO | Importância da Identidade de Gênero -->
                    <div class="modal fade" id="modalIdentGenero">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Qual a importância de preencher sua identidade de gênero?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>A sociedade, em grande parte, encara o gênero como algo fixo e binário. No entanto, a identidade de gênero abrange um espectro diverso, que nem sempre corresponde ao gênero atribuído no nascimento.</p>
                                    <p>Pessoas trans enfrentam desafios adicionais no mercado de trabalho, muitas vezes tendo suas oportunidades de emprego reduzidas devido a preconceitos estruturais.</p>
                                    <p>Nosso objetivo é transformar essa realidade por meio da coleta e análise de dados, promovendo ações afirmativas dentro das estratégias de Recrutamento & Seleção.</p>
                                    <p>É fundamental destacar que essas informações nunca serão utilizadas para excluir candidatos nos processos seletivos. Pelo contrário, elas possibilitam uma visão mais ampla da diversidade e ajudam a reduzir vieses na seleção.</p>
                                    <p><strong>Cisgênero</strong> ou (Cis) é a pessoa que se identifica com o gênero que lhe foi atribuído ao nascer.</p>
                                    <p><strong>Transgênero</strong> ou (trans) é a pessoa cuja identidade não corresponde ao gênero que lhe foi atribuído ao nascer.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Aqui Termina o conteúdo da página -->
        </div>
    </div>

    <script src="candidatos.js"></script>
</body>

</html>
<?php // --- ROTINAS AUXILIARES

//- Cria o Seletor de ESCOLARIDADE para o form
function f_escolaridade($_id = 0, $conn)
{
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

//- Cria o Seletor de UF para o form
function seletorUF($_uf = '')
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_uf";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='uf' name='uf' required>";
    if ($_uf == '')
        $select .= "<option value='0' selected>UF</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_uf == $uf)
            $selected = "selected";
        else
            $selected = "";
        $select .= "<option value='$uf' $selected>$nome</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

//- Cria o Seletor de NÍVEIS DE CURSO para o form
function seletorTipoCurso($id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM RH.rh_fa_niveis;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idNivel' name='idNivel' required>";
    if ($id == 0)
        $select .= "<option value='0' selected>Selecione um Nível</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($id == $idNivel)
            $selected = "selected";
        else
            $selected = "";
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
    if ($id == 0)
        $select .= "<option value='0' selected>Selecione uma IE</option>";

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

//- Cria o Seletor de IDIOMAS para o form
function seletor_idiomas($id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_idiomas;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idIdioma' name='idIdioma' required>";
    if ($id == 0)
        $select .= "<option value='0' selected>Selecione um Idioma</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($id == $idIdioma)
            $selected = "selected";
        else
            $selected = "";
        $select .= "<option value='$idIdioma' $selected>$nome</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

//- Cria o Seletor de IDIOMAS para o form
function seletor_fluencia($id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_fluencias;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idFluencia' name='idFluencia' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione um Idioma</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($id == $idFluencia)
            $selected = "selected";
        else
            $selected = "";
        $select .= "<option value='$idFluencia' $selected>$nome</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

function seletorConquistas($id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_conqTipos;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idConqTipo' name='idConqTipo' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione uma Conquista</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($id == $idConqTipo)
            $selected = "selected";
        else
            $selected = "";
        $select .= "<option value='$idConqTipo' $selected>$nome</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}
