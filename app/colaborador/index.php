<?php
// index.php | Painel do Colaborar - página inicial
// (C)haia, 02/10/2025

session_start();

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

$idModulo = 13; // Colaborador

require_once "../includes/conexao_gerar.php";
global $conn;

//- RECUPERA DADOS DO COLABORADOR
//
    $sql = "SELECT C.*, P.*, CV.*, EC.categoria as dsEstadoCivil, ET.categoria as dsEtnia, CG.nome as dsCargo, 
                F.nome as dsFuncao, O.idOrgao, O.descricao as dsOrgao, O.idSupervisor, O.nivel, C.bate_ponto
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

//
//- Busca dados do Supervisor na API
//
    $url = "https://rh.gerar.org.br/app/api/api_supervisor.php?id=" . $idColab;
    $resposta = file_get_contents($url);
    $dados = json_decode($resposta, true); // converte JSON em array associativo

    if ($dados) {
        extract($dados);
        $_SESSION['BATE_PONTO'] = $bate_ponto;
        //
        if( empty($gestor_foto) || $gestor_foto == 'perfil.png') {
            $url_foto_gestor = "https://ui-avatars.com/api/?name=$gestor_nome";
        }else{
            $url_foto_gestor = "../fotos/$gestor_foto";
            
        }
    } else {
        die("FALTA O CADASTRO DO SUPERVISOR DO COLABORADOR - INFORME O RH...");
    }

//
//- Calcula a quantidade de dias de férias que pode ser agendada
//
    $sql = "SELECT 
                        SUM(
                            FLOOR(LEAST(12, TIMESTAMPDIFF(MONTH, F.inicio_aquisitivo, CURDATE())) * 2.5)
                            - (COALESCE(F.dias_parte1,0) + COALESCE(F.dias_parte2,0) + COALESCE(F.dias_parte3,0))
                        ) AS total_dias_pendentes, max( atualizado_em ) as ultima_atualizacao
                    FROM RH.rh_ferias F
                    WHERE F.idColab = :idColab
                    AND CURDATE() BETWEEN F.inicio_concessivo AND F.fim_concessivo;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($linha);

//
//- Calcula quantidade de Afastamentos
//
    $sql = "SELECT sum(dias_afastado) as qtdAfastado, max(data_inicio) as ultimo_atestado 
                    FROM RH.rh_afastamentos 
                    where idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($linha);

//
//- Obtém a lista dos equipamentos
//
    $sql = "SELECT TP.descricao as dsTipo
                    FROM RH.rh_equip_termos_ld E
                    INNER JOIN rh_equip_termos T on T.id = E.idEquipTermo
                    INNER JOIN rh_equip_tipos TP ON TP.id = E.idTipo
                    WHERE T.status = 'Assinado' AND T.idPessoa = :idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->execute();

// Pega todas as linhas em um array
    $linhas = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // só a coluna "dsTipo"

// Cria string separada por vírgulas
    $listaEquip = implode(", ", $linhas);

// Total de equipamentos listados
    $totalEquip = count($linhas);

//
//- Obtém Ultimo Salário
//
    $sql = "SELECT id, data as dtUltimoSalario, valor as vlUltimoSalario 
                    FROM rh_historico_sal
                    WHERE idColab = :idColab
                    ORDER BY id desc
                    Limit 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($linha);

$modulo = "Home";
include "header.php";
?>
<!-- Main -->
<style>

    .foto_perfil{
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: .6rem;
    }

</style>
<main class="main">
    <div class="container-fluid">
        <!-- Top controls -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="mb-0">Área do Colaborador</h2>
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
                        echo '<img class="foto_perfil" src="' . $url . '" alt="foto">';
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

                        <div class="d-flex justify-content-end align-items-center gap-2 text-end">
                            <div class="me-2">
                                <div style="font-size:.9rem;color:var(--muted)">Gestor Imediato</div>
                                <div style="font-weight:700"><?php echo htmlspecialchars($gestor_nome); ?></div>
                                <div style="font-size:.85rem;color:var(--muted)"><?php echo htmlspecialchars($gestor_email); ?></div>
                                <div style="font-size:.85rem;color:var(--muted)"><?php echo htmlspecialchars($gestor_telefone); ?></div>
                            </div>

                            <img class="foto_perfil" src="<?= $url_foto_gestor ?>" alt="foto">

                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas / cards -->
            <div class="cards-grid mt-3">
                <div class="big-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size:.85rem;color:var(--muted)">Saldo de Férias</div>
                            <div style="font-size:1.35rem;font-weight:800"><?= $total_dias_pendentes ?> dias</div>
                        </div>
                        <div class="text-end">
                            <small style="color:var(--muted)">Última atualização</small><br>
                            <strong><?= $ultima_atualizacao ?></strong>
                        </div>
                    </div>
                </div>

                <div class="big-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size:.85rem;color:var(--muted)">Afastamentos (total)</div>
                            <div style="font-size:1.35rem;font-weight:800"><?= $qtdAfastado ?> dias</div>
                        </div>
                        <div class="text-end">
                            <small style="color:var(--muted)">Último</small><br>
                            <strong><?= $ultimo_atestado ?></strong>
                        </div>
                    </div>
                </div>

                <div class="big-card">
                    <div>
                        <div style="font-size:.85rem;color:var(--muted)">Equipamentos</div>
                        <div style="font-size:1.35rem;font-weight:800"><?= $totalEquip ?> itens</div>
                        <div style="color:var(--muted);margin-top:.5rem"><?= $listaEquip ?></div>
                    </div>
                </div>

                <div class="big-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <input type="hidden" id="vlUltimoSalario" value='<?= "R$ " . number_format($vlUltimoSalario, 2, ',', '.') ?>'>
                            <div style="font-size:.85rem;color:var(--muted)">Salário</div>
                            <div style="font-size:1.35rem;font-weight:800" id='divUltimoSalario'>R$ *.***,**</div>
                        </div>
                        <div class="text-end">
                            <small style="color:var(--muted)">reajustado em</small><br>
                            <strong><?= $dtUltimoSalario ?></strong>
                            <div style="color:var(--muted);margin-top:.5rem" onclick="mostrarSalario()"><i id='olhoSalario' class="fa-solid fa-eye-slash"></i></div>
                        </div>
                    </div>
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
            $sql = "SELECT N.*, N.id as idNotificacao, E.descricao as dsEvento, T.cor, T.icone
                    FROM RH.rh_notificacoes N 
                    LEFT OUTER JOIN rh_notificacoes_eventos E on E.id = N.idEvento
                    LEFT OUTER JOIN rh_notificacoes_tipo T on T.idTipo = N.idTipo
                    WHERE N.colaborador_id = :idColab AND N.lido_em IS NULL
                    ORDER BY N.id DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idColab', $idColab);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                while ($not = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $idNotificacao = $not['idNotificacao'];
                    $data = date('d/m/Y', strtotime($not['criado_em']));
                    $hora = date('H:i', strtotime($not['criado_em']));
                    $cor = $not['cor'];
                    $icone = $not['icone'];
                    $titulo = $not['dsEvento'];
                    $mensagem = $not['mensagem'];
                    //
                    echo "
                    <div id='card_$idNotificacao' class='profile-card mt-3'>
                        <div class='card-body text-$cor d-flex align-items-center justify-content-between'>
                            <div class='d-flex align-items-center'>
                                <i class='fa-solid $icone fa-2x me-2'></i>
                                <span>$mensagem</span>
                            </div>
                            <button type='button' 
                                class='btn-close btn-close-white fw-bold border-0 bg-transparent fs-5' 
                                aria-label='Fechar'
                                onclick='f_marcar_lido($idNotificacao)'>
                            </button>
                        </div>
                    </div>";
                }
            }
            ?>
        </section>


    </div>

</main>
<script>

    function f_marcar_lido(id) {
        // Remove visualmente o cartão com efeito suave
        const card = document.getElementById(`card_${id}`);
        if (card) {
            card.style.transition = "opacity 0.3s";
            card.style.opacity = "0";
            setTimeout(() => card.remove(), 300); // remove após fade-out
        }

        // Chama o backend para marcar como lido
        fetch('index_aj7.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id)
            })
            .then(resp => resp.text())
            .then(data => {
                console.log('Servidor respondeu:', data);
            })
            .catch(err => console.error('Erro ao marcar como lido:', err));
    }
</script>

<?PHP
include('footer.php');
?>