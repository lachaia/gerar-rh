<?php
//
//- equipe.php | Minha Equipe de Trabalho
// (C)haia, 28/11/2025
//

session_start();

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

$modulo = 22;

include "../../includes/conexao_gerar.php";
include "../../includes/debug.php";

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
} else {
    $idColab = $_SESSION['idColab'];
    $hoje = date('Y-m-d');
}

//
// --- Busca DADOS do colaborador ---
//
    $sql = "SELECT 
                O.idOrgao, O.descricao as dsOrgao, O.idSupervisor, O.nivel,
                (SELECT nivel FROM rh_organograma WHERE idOrgao = O.idSupervisor) as nivelSupervisor,
                C.horario_ini, 
                C.horario_fim, 
                S.cidade AS cidade_id, CD.nome as cidade_ds,
                S.estado AS colaborador_uf,
                P.nome, U.foto
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
            LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
            LEFT JOIN rh_usuarios U ON U.idColab = C.idColab
            LEFT JOIN rh_cidades CD ON CD.idCidade = S.cidade
            LEFT OUTER JOIN rh_organograma O on O.idOrgao = C.idOrgao
            WHERE C.idColab = :idColab
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idColab' => $idColab]);
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    $orgao_id = $linha['idOrgao'] ?? 0;
    $orgao_gestor = $linha['idSupervisor'] ?? 0;
    $nivel = $linha['nivel'] ?? 0;
    $nivelSupervisor = $linha['nivelSupervisor'] ?? 0;

//
//- Busca dados do Supervisor na API
//
    $url = "https://rh.gerar.org.br/api/api_supervisor.php?id=" . $idColab;
    $resposta = file_get_contents($url);
    $dados = json_decode($resposta, true); // converte JSON em array associativo

    if ($dados) {
        extract($dados);
        //
        if( empty($gestor_foto) || $gestor_foto == 'perfil.png') {
            $url_foto_gestor = "https://ui-avatars.com/api/?name=$gestor_nome";
        }else{
            $url_foto_gestor = "../../fotos/$gestor_foto";
        }        
    } else {
        die("FALTA O CADASTRO DO SUPERVISOR DO COLABORADOR - INFORME O RH...");
    }

//
//- CARREGA VARIAVEIS
//
    $horario_ini = $linha['horario_ini'] ?? '08:20';
    $horario_fim = $linha['horario_fim'] ?? '18:05';
    $cidade_id = $linha['cidade_id'] ?? 3281;
    $cidade_ds = $linha['cidade_ds'] ?? 'N/D';
    $colaborador_uf = $linha['colaborador_uf'] ?? 'PR';
    $nomeColab = $linha['nome'] ?? ($_SESSION['nmLogin'] ?? 'Colaborador');
    $foto = $linha['foto'] ?? '';

//
//- TRATA FOTO DO PERFIL
//
    if (empty($foto) || $foto == 'perfil.png') {
        $url_foto = "https://ui-avatars.com/api/?name=" . urlencode($nomeColab) . "&background=0D6EFD&color=fff&size=256";
    } else {
        $url_foto = "../../fotos/$foto";
    }

//
//- LISTA DOS COLABORADORES QUE TRABALHAM COMIGO
//
    $listaColegas = getColegas($orgao_id, $conn);

    $sql = "SELECT C.idColab, O.descricao, P.nome, U.foto, F.nome as dsFuncao
                    FROM rh_colaboradores C
                    INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                    INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                    LEFT OUTER JOIN rh_usuarios U on U.idColab = C.idColab
                    LEFT OUTER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
                    WHERE C.idColab in (" . implode(",", $listaColegas) . ")
                    ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $trabalham_comigo = $stmt->fetchAll(PDO::FETCH_ASSOC);

//
//- LISTA DOS SUBORDINADOS
//
    $listaSubordinados = getSubordinados($orgao_id, $conn);
    $subordinados = [];
    if( ! empty($listaSubordinados) ){
        $sql = "SELECT C.idColab, O.descricao, P.nome, U.foto, F.nome as dsFuncao, C.bate_ponto
                        FROM rh_colaboradores C
                        INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                        INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                        LEFT OUTER JOIN rh_usuarios U on U.idColab = C.idColab
                        LEFT OUTER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
                        WHERE C.data_rescisao IS NULL and C.idColab in (" . implode(",", $listaSubordinados) . ")
                        ORDER BY P.nome";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute()) {
            $subordinados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

$listaTotal = array_unique(array_merge($listaColegas, $listaSubordinados));

?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Ponto Eletrônico — Web</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            background: #f4f6f9;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }

        .card-quiet {
            box-shadow: 0 6px 18px rgba(30, 41, 59, 0.06);
            border: none;
        }

        .big-clock {
            font-weight: 700;
            letter-spacing: -1px;
        }

        .small-clock {
            opacity: .8;
        }

        .avatar-sm {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 50%;
        }

        .btn-primary-strong {
            background: #0d6efd;
            border-color: #0d6efd;
        }

        .address-box {
            min-height: 72px;
        }

        .cartao_colab {
            transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
            background-color: #ffffff;
            border-radius: 10px;
        }

        .cartao_colab:hover {
            transform: scale(1.03);
            background-color: #f1f3f5;
            /* leve escurecida */
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
            cursor: pointer;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="../index_web.php">
                <img src="../../imagens/logo.png" alt="Logo" style="height:36px">
                <span class="fw-bold text-dark">Gerar RH</span>
            </a>
            <a href="../index_web.php"><i class="fa-solid fa-house"></i></a>

            <div class="d-flex align-items-center ms-auto">
                <!-- nome do colaborador (opcional) -->
                <div class="me-3 text-end d-none d-md-block">
                    <div class="small text-muted">Olá,</div>
                    <div class="fw-semibold"><?= htmlspecialchars($nomeColab) ?></div>
                </div>

                <!-- avatar com dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $url_foto ?>" alt="avatar" class="avatar-sm border">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalResetSenha">
                                <i class="fa-solid fa-key me-2"></i> Resetar senha
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- MENSAGENS -->
    <div class="text-center m-2 h4" id='mensagem'></div>

    <!-- CONTENT -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- COLUNA MINHA EQUIPE -->
            <div class="col-sm-6">
                <?php
                //- MEU SUPERVISOR
                echo "<p class='text-center fs-5 mb-2 bg-secondary p-2 text-white rounded'>
                            Meu Supervisor
                      </p>";
                        if( empty($gestor_id) ){
                            echo "<span class='text-danger'>FALTA O CADASTRO DO SUPERVISOR...</span>";
                        }else{
                            echo "
                            <div class='card shadow-sm border-0 mb-3' style='border-radius: 14px; overflow:hidden;'>
                                <div class='d-flex'>
                                    
                                    <!-- COLUNA ESQUERDA — FOTO -->
                                    <div class='d-flex justify-content-center align-items-center p-3' 
                                        style='min-width: 110px; background:#f8f9fa; border-right:1px solid #e1e5ea;'>
                                        <img src='$url_foto_gestor' 
                                            alt='$gestor_nome' 
                                            class='rounded-circle shadow-sm'
                                            style='width:70px; height:70px; object-fit:cover;'>
                                    </div>

                                    <!-- COLUNA DIREITA — INFORMAÇÕES -->
                                    <div class='p-3 flex-grow-1'>
                                        <p class='mb-1 fw-bold text-dark' style='font-size:1.15rem;'>$gestor_nome</p>

                                        <p class='text-muted small mb-1'>
                                            <i class='fa fa-briefcase me-1'></i> $gestor_funcao
                                        </p>

                                        <p class='text-muted small mb-1'>
                                            <i class='fa fa-envelope me-1'></i> $gestor_email
                                        </p>

                                        <p class='text-muted small mb-0'>
                                            <i class='fa fa-phone me-1'></i> $gestor_telefone
                                        </p>
                                    </div>

                                </div>
                            </div>
                            ";


                        }                      
                //- LISTA DOS QUE TRABALHAM COMIGO
                if (!empty($trabalham_comigo)) {
                    echo "
                        <p class='text-center fs-5 mb-2 bg-secondary p-2 text-white rounded'>
                            No mesmo Setor
                        </p>";

                    foreach ($trabalham_comigo as $colab) {
                        $nome = $colab['nome'];
                        $descricao = $colab['descricao'];
                        $idColab = $colab['idColab'];
                        $dsFuncao = $colab['dsFuncao'];
                        $foto = $colab['foto'];
                        if (empty($foto) || $foto == 'perfil.png') {
                            $url_foto = "https://ui-avatars.com/api/?name=$nome";
                        } else {
                            $url_foto = "../../fotos/$foto";
                        }
                        $status = f_status($conn, $idColab);
                        //
                        if ($status == 'Ausente') $status = "<i class='text-red-600'>Ausente</i>";
                        //
                        echo "
                            <div class='card shadow-sm mb-2 cartao_colab' onclick='ver($idColab)' style='cursor:pointer;'>
                                <div class='card-body py-2'>
                                    <div class='d-flex align-items-center'>
                                        <img src='$url_foto' alt='$nome' class='rounded-circle me-3' style='width:40px; height:40px;'>                                        
                                        <div>
                                            <p class='mb-0 fw-semibold text-dark'>$nome</p>
                                            <p class='text-muted text-sm'><i class='fa-solid fa-sitemap'></i> $descricao</p>
                                            <p class='text-muted text-sm'><i class='fa-solid fa-briefcase'></i> $dsFuncao</p>
                                        </div>
                                    </div>
                                    <p class='text-end mt-2 mb-0 text-success small'>$status</p>
                                </div>
                            </div>";
                    }
                }
                //- LISTA DOS SUBORDINADOS
                if (!empty($subordinados)) {
                    echo "
                        <p class='text-center fs-5 mb-2 bg-secondary p-2 text-white rounded'>
                            Subordinados
                        </p>";

                    foreach ($subordinados as $colab) {
                        $nome = $colab['nome'];
                        $descricao = $colab['descricao'];
                        $idColab = $colab['idColab'];
                        $dsFuncao = $colab['dsFuncao'];
                        $foto = $colab['foto'];
                        $bate_ponto = $colab['bate_ponto'];
                        //
                        if (empty($foto) || $foto == 'perfil.png') {
                            $url_foto = "https://ui-avatars.com/api/?name=$nome";
                        } else {
                            $url_foto = "../../fotos/$foto";
                        }
                        $status = '';
                        if( $bate_ponto == 1 ) $status = f_status($conn, $idColab);
                        //
                        if ($status == 'Ausente') $status = "<i class='text-red-600'>Ausente</i>";
                        //
                        echo "
                            <div class='card shadow-sm mb-2 cartao_colab' onclick='ver_sub($idColab)' style='cursor:pointer;'>
                                <div class='card-body py-2'>
                                    <div class='d-flex align-items-center'>
                                        <img src='$url_foto' alt='$nome' class='rounded-circle me-3' style='width:40px; height:40px;'>                                        
                                        <div>
                                            <p class='mb-0 fw-semibold text-dark'>$nome</p>
                                            <p class='text-muted text-sm'><i class='fa-solid fa-sitemap'></i> $descricao</p>
                                            <p class='text-muted text-sm'><i class='fa-solid fa-briefcase'></i> $dsFuncao</p>
                                        </div>
                                    </div>
                                    <p class='text-end mt-2 mb-0 text-success small'>$status</p>
                                </div>
                            </div>";
                    }
                } ?>
            </div>

            <!-- COLUNA PAINEL DA EQUIPE -->
            <div class="col-sm-6">

                <!-- RESUMO DA EQUIPE -->
                <p class="text-center fs-5 mb-2 bg-secondary p-2 text-white rounded">
                    Painel da Equipe
                </p>

                <div class="row g-3">

                    <!-- CARD: QUANTIDADE NO SETOR -->
                    <div class="col-6">
                        <div class="card shadow-sm text-center p-3">
                            <h6 class="text-muted mb-1">No Setor</h6>
                            <h3 class="fw-bold"><?= count($trabalham_comigo) ?></h3>
                        </div>
                    </div>

                    <!-- CARD: SUBORDINADOS -->
                    <div class="col-6">
                        <div class="card shadow-sm text-center p-3">
                            <h6 class="text-muted mb-1">Subordinados</h6>
                            <h3 class="fw-bold text-primary"><?= count($subordinados) ?></h3>
                        </div>
                    </div>

                    <!-- CARD: AFASTADOS HOJE -->
                    <div class="col-6">
                        <div class="card shadow-sm text-center p-3">
                            <h6 class="text-muted mb-1">Afastados Hoje</h6>
                            <h3 class="fw-bold text-danger">
                                <?= qtdeAfastadosHoje($conn) ?>
                            </h3>
                        </div>
                    </div>

                    <!-- CARD: EM FÉRIAS -->
                    <div class="col-6">
                        <div class="card shadow-sm text-center p-3">
                            <h6 class="text-muted mb-1">Em Férias</h6>
                            <h3 class="fw-bold text-warning">
                                <?= qtdeFeriasHoje($conn, $listaTotal) ?>
                            </h3>
                        </div>
                    </div>

                    <!-- CARD: TRABALHANDO HOJE -->
                    <div class="col-6">
                        <div class="card shadow-sm text-center p-3">
                            <h6 class="text-muted mb-1">Em Jornada Hoje</h6>
                            <h3 class="fw-bold text-success">
                                <?= qtdeTrabalhandoHoje($conn, $listaTotal) ?>
                            </h3>
                        </div>
                    </div>

                    <!-- CARD: AUSENTES HOJE -->
                    <div class="col-6">
                        <div class="card shadow-sm text-center p-3">
                            <h6 class="text-muted mb-1">Ausentes Hoje</h6>
                            <h3 class="fw-bold text-danger">
                                <?= qtdeAusentesHoje($conn, $listaTotal) ?>
                            </h3>
                        </div>
                    </div>

                    <!-- CARD: ORGANOGRAMA -->
                    <p class="text-center fs-5 mb-2 bg-secondary p-2 text-white rounded">
                        <i class="fa-solid fa-sitemap"></i> Organograma da Equipe
                    </p>                    
                    <div class="col-md-12 offset-md-2">
                        <div style="white-space: pre-wrap; font-family: Courier;">
                            <?= printOrgText($orgao_id, $conn, "", true, true) ?>
                        </div>                        
                    </div>
                </div>


            </div>

        </div>
    </div>

    <!-- MODAL COLABORADOR -->
    <div class="modal fade" id="modalColaborador" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Dados do Colaborador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="modalColaboradorBody">
                    <div class="text-center text-muted py-3">
                        Carregando...
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function ver(idColab) {
            // abre a modal imediatamente
            let modal = new bootstrap.Modal(document.getElementById('modalColaborador'));
            modal.show();
            // mostra loading rápido
            document.getElementById('modalColaboradorBody').innerHTML =
                "<div class='text-center text-muted py-3'>Carregando...</div>";
            // requisição AJAX
            $.post(
                "equipe_aj.php", {
                    idColab: idColab
                },
                function(html) {
                    document.getElementById('modalColaboradorBody').innerHTML = html;
                }
            );
        }

        function ver_sub(idColab) {
            // abre a modal imediatamente
            let modal = new bootstrap.Modal(document.getElementById('modalColaborador'));
            modal.show();
            // mostra loading rápido
            document.getElementById('modalColaboradorBody').innerHTML =
                "<div class='text-center text-muted py-3'>Carregando...</div>";
            // requisição AJAX
            $.post(
                "equipe_aj2.php", {
                    idColab: idColab
                },
                function(html) {
                    document.getElementById('modalColaboradorBody').innerHTML = html;
                }
            );
        }
    </script>
</body>

</html>
<?php

function qtdeAfastadosHoje($conn) {
    $sql = "SELECT count(id) as qtd 
            FROM rh_afastamentos
            WHERE status = 'Aprovado' and curdate() between data_inicio and data_retorno";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row['qtd'];
    return 0;
}

function qtdeFeriasHoje($conn, $lista){
    $sql = "SELECT COUNT(DISTINCT idColab) AS qtd
            FROM rh_ferias
            WHERE idColab in (" . implode(",", $lista) . ") AND (
                (
                    data_parte1 IS NOT NULL AND
                    CURDATE() BETWEEN data_parte1 AND DATE_ADD(data_parte1, INTERVAL dias_parte1 - 1 DAY)
                )
                OR
                (
                    data_parte2 IS NOT NULL AND
                    CURDATE() BETWEEN data_parte2 AND DATE_ADD(data_parte2, INTERVAL dias_parte2 - 1 DAY)
                )
                OR
                (
                    data_parte3 IS NOT NULL AND
                    CURDATE() BETWEEN data_parte3 AND DATE_ADD(data_parte3, INTERVAL dias_parte3 - 1 DAY)
                ))";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row['qtd'];
    return 0;
}

function f_status($conn, $idColab)
{
    $sql = "SELECT tipo
            FROM rh_ponto_registros 
            WHERE colaborador_id = :idColab AND DATE(data_hora) = CURDATE()
            ORDER BY data_hora DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //
    if (! $row) return "Ausente"; //- sem registros
    $tipo = strtolower(trim($row['tipo'])); //- normaliza para evitar erros
    if ($tipo == 'entrada') return 'Trabalhando';
    return "Ausente";
}

function qtdeTrabalhandoHoje($conn, $lista) {
    $sql = "SELECT count(C.idColab) as qtd
                FROM rh_colaboradores C
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                LEFT OUTER JOIN rh_usuarios U on U.idColab = C.idColab
                WHERE   C.data_rescisao IS NULL and 
                        C.idColab in (" . implode(",", $lista) . ") and 
                        ( select count(*) from rh_ponto_registros where colaborador_id = C.idColab and DATE(data_hora) = CURDATE() ) > 0    
                ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row['qtd'];
    return 0;
}

function qtdeAusentesHoje($conn, $lista) {
    $sql = "SELECT count(C.idColab) as qtd
                FROM rh_colaboradores C
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                LEFT OUTER JOIN rh_usuarios U on U.idColab = C.idColab
                WHERE   C.data_rescisao IS NULL and 
                        C.idColab in (" . implode(",", $lista) . ") and 
                        ( select count(*) from rh_ponto_registros where colaborador_id = C.idColab and DATE(data_hora) = CURDATE() ) = 0    
                ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row['qtd'];
    return 0;
}

function getSubordinados($idOrgao, $pdo)
{
    // 1. Buscar a linha do organograma do gestor
    $sql = "SELECT * FROM rh_organograma WHERE idOrgao = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idOrgao]);
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gestor) {
        return [];
    }

    // 2. Descobrir em qual nível o gestor está (último nível > 0)
    $nivelGestor = 0;
    for ($i = 1; $i <= 7; $i++) {
        if (!empty($gestor["nivel_$i"]) && $gestor["nivel_$i"] > 0) {
            $nivelGestor = $i;
        }
    }

    // 3. Montar condição dinâmica para os níveis anteriores
    $conds = [];
    $params = [];

    for ($i = 1; $i <= $nivelGestor; $i++) {
        $conds[] = "O.nivel_$i = :n$i";
        $params[":n$i"] = $gestor["nivel_$i"];
    }

    // 4. O próximo nível precisa ser > 0
    $proximoNivel = $nivelGestor + 1;
    if ($proximoNivel <= 7) {
        $conds[] = "O.nivel_$proximoNivel > 0";
    }

    // 5. Montar SQL final
    $where = implode(" AND ", $conds);

    $sql = "
        SELECT C.idColab
        FROM rh_colaboradores C
        INNER JOIN rh_organograma O ON O.idOrgao = C.idOrgao
        WHERE $where
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 6. Extrair apenas os IDs em um vetor simples
    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row["idColab"];
    }

    return $ids;
}

function getColegas( $idOrgao, $conn){
    $sql = "SELECT C.idColab
                    FROM rh_colaboradores C
                    INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                    WHERE O.idOrgao = :orgao_id AND C.data_rescisao IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':orgao_id', $idOrgao, PDO::PARAM_INT);
    $stmt->execute();
    $trabalham_comigo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_column($trabalham_comigo, 'idColab');  
}

function printOrgText($orgao_id, $conn, $prefix = "", $isLast = true, $isRoot = false) {

    // pega registro
    $sql = "SELECT idOrgao, descricao
            FROM rh_organograma 
            WHERE idOrgao = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $orgao_id]);
    $org = $stmt->fetch(PDO::FETCH_ASSOC);

    // Desenha o órgão
    $dsOrgao = "<strong class='text-blue-600 dark:text-sky-600 text-lg'>" .$org['descricao'] . "</strong>";
    if ($isRoot) {
        echo "<br><i class='fa-solid fa-house'></i> " . $dsOrgao . "<br>";
    } else {
        echo $prefix . ($isLast ? "└── " : "├── ") . $dsOrgao . "<br>";
    }

    // Lista de colaboradores do órgão
    $sql = "SELECT P.nome 
            FROM rh_colaboradores C 
            INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
            WHERE C.idOrgao = :orgao_id AND C.data_rescisao IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':orgao_id' => $orgao_id]);
    $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($colaboradores as $i => $col) {
        $nome = ucwords(strtolower($col['nome']));
        echo $prefix . ($isLast ? "    " : "│   ") . "<i class='text-muted text-sm'> <i class='fa-solid fa-user text-gray-400'></i> $nome<br>";
    }

    // busca filhos
    $sql = "SELECT idOrgao FROM rh_organograma WHERE idSupervisor = :idPai";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idPai' => $orgao_id]);
    $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lastIndex = count($subs) - 1;

    foreach ($subs as $i => $sub) {
        $newPrefix = $prefix . ($isLast ? "    " : "│   ");
        printOrgText($sub['idOrgao'], $conn, $newPrefix, $i === $lastIndex, false);
    }
}



