<?php
// index.php | Painel do Gestor - página inicial
// (C)haia, 02/10/2025

session_start();

$idModulo = 16; // Portal do Gestor

if (isset($_SESSION['idLogin'])) {
    $login = $_SESSION['nmLogin'];
    $idColab = $_SESSION['idColab'];
    $idPessoa = $_SESSION['idPessoa'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    $foto_perfil = $_SESSION['perfil'];
} else {
    header("Location: ../logout.php");
    exit;
}

require_once "../includes/conexao_gerar.php";
global $conn;

//- RECUPERA DADOS DO COLABORADOR
//
    $sql = "SELECT C.*, P.*, CV.*, EC.categoria as dsEstadoCivil, ET.categoria as dsEtnia, CG.nome as dsCargo, F.nome as dsFuncao,
		O.idOrgao, O.descricao as dsOrgao, O.idSupervisor, O.nivel,
        (SELECT nivel FROM rh_organograma WHERE idOrgao = O.idSupervisor) as nivelSupervisor
                FROM rh_colaboradores C 
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                LEFT OUTER JOIN rh_estadoCivil EC on EC.idEstadoCivil = P.idEstadoCivil
                LEFT OUTER JOIN rh_etnias ET on ET.idEtnia = P.idEtnia
				LEFT OUTER JOIN rh_cv CV on CV.idPessoa = C.idPessoa
				LEFT OUTER JOIN rh_cargos CG on CG.idCargo = C.idCargo
				LEFT OUTER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
				LEFT OUTER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                WHERE idColab = :idColab";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
extract($linha);

$modulo = "Home";
include "includes/header.php";

//
//- VERIFICA SE TEM SOLICITAÇÃO PENDENTE
//
    $sql = "SELECT *
            FROM rh_ponto_solicitacoes
            WHERE supervisor_id = :idColab
            AND status = 'AGUARDANDO'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $pendentes = $stmt->rowCount();
?>
<!-- Main -->

<main class="main">
    <div class="container-fluid">
        <!-- Top controls -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="mb-0">Área do Gestor</h2>
                <small style="color:var(--muted)">Bem-vindo ao seu painel</small>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href='config.php'><i class="fa-solid fa-gear"></i></a>
                <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>


        <!-- HOME Section -->
        <section id="home" class="section active">
            <div class="profile-card">
                <div class="profile-photo">
                    <?php
                    // Se tiver foto use <img>, senão iniciais
                    if (!empty($foto_perfil)) {
                        $url = "../fotos/" . $foto_perfil;
                        echo '<img src="' . $url . '" alt="foto" style="width:100%;height:100%;object-fit:cover;border-radius:.6rem;">';
                    } else {
                        // iniciais
                        $parts = explode(' ', $nome);
                        $inic = '';
                        foreach ($parts as $p) {
                            if (!empty($p)) $inic .= mb_substr($p, 0, 1, 'UTF-8');
                        }
                        echo '<div style="font-size:1.15rem;color: #e6eef8;">' . mb_substr($inic, 0, 2, 'UTF-8') . '</div>';
                    }
                    ?>
                </div>

                <div class="profile-meta flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 style="margin:0; letter-spacing:.6px; font-size:1.25rem;"><?php echo htmlspecialchars($nome); ?></h3>
                            <div style="color:var(--muted); margin-top:.25rem;">
                                <small>Admissão: <strong><?php echo htmlspecialchars($data_admissao); ?></strong> • Cargo: <strong><?php echo htmlspecialchars($dsCargo); ?></strong></small>
                            </div>
                            <div style="margin-top:.65rem;">
                                <span class="chip">Lotação: <?php echo htmlspecialchars($dsOrgao); ?></span>
                            </div>
                        </div>

                        <div class="text-end">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas / cards -->
            <div class="cards-grid mt-3">
                <div class="big-card position-relative p-3">
                    <input type="hidden" id="idGestor" value="<?= $idColab ?>">
                    <!-- Título à esquerda -->
                    <div style="font-size:1.2rem">
                        <i class="fa-solid fa-users"></i> ATIVOS
                    </div>
                    <!-- Número centralizado -->
                    <div id="divQtdAtivos"
                        class="h3 text-center w-100">
                    </div>
                </div>

                <div class="big-card">
                    <!-- Título à esquerda -->
                    <div style="font-size:1.2rem">
                        <i class="fa-solid fa-users-slash"></i> Desligados
                    </div>
                    <!-- Número centralizado -->
                    <div id="divQtdDesligados" class="h4 text-center w-100">0</div>
                </div>

                <div class="big-card">
                    <!-- Título à esquerda -->
                    <div style="font-size:1.2rem">
                        <i class="fa-solid fa-file-medical"></i> Afastados
                    </div>
                    <!-- Número centralizado -->
                    <div id="divQtdAfastados"class="h4 text-center w-100">0</div>
                </div>

                <div class="big-card">
                    <!-- Título à esquerda -->
                    <div style="font-size:1.2rem">
                        <i class="fa-solid fa-plane"></i> Em Férias
                    </div>
                    <!-- Número centralizado -->
                    <div id="total_colab_ferias" class="h4 text-center w-100">0</div>
                </div>




            </div>

            <!-- Ações rápidas 
                <div class="mt-4 d-flex flex-column flex-md-row gap-2">
                    <button class="btn btn-lg" style="background:var(--accent);border:0;color:#fff;"><i class="bi bi-calendar-check-fill"></i> Agendar Férias</button>
                    <button class="btn btn-lg btn-outline-light"><i class="bi bi-file-earmark-arrow-up-fill"></i> Enviar Currículo</button>
                    <button class="btn btn-lg btn-outline-light" id="btnOpenAfastamento"><i class="bi bi-journal-medical"></i> Enviar Atestado</button>
                </div>
                -->
        </section>

        <section>
            <h4 class="mt-4">Notificações</h4>
            <?PHP
            if( $pendentes > 0){
                echo '<div class="profile-card mt-3">
                    <div class="card-body text-warning d-flex align-items-center">
                        <i class="fa-solid fa-circle-check fa-2x me-2"></i>
                        <span>Há solicitações de ajuste de ponto pendentes.</span>
                    </div>
                </div>';
            } else{
                echo '<div class="profile-card mt-3">
                    <div class="card-body text-success d-flex align-items-center">
                        <i class="fa-solid fa-circle-check fa-2x me-2"></i>
                        <span>Tudo tranquilo por aqui! Nenhuma notificação pendente.</span>
                    </div>
                </div>';
            }
            ?>
        </section>


    </div>

</main>
<script src="js/index.js"></script>
<?PHP
include('includes/footer.php');
?>