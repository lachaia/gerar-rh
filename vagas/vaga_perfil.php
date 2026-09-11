<?php
//
//- vaga_perfil.php | Ficha pública da Vaga (candidatura)
//- (C)haia, 2026-07-23
//

session_start();

include "../app/includes/conexao_gerar.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$vaga = null;
if ($id) {
    //- Mesma regra de elegibilidade da listagem (index.php): aprovada, publicada,
    //- ainda aberta e dentro da validade do anúncio.
    $sql = "SELECT V.*, C.nome AS cargo_ds
            FROM rs_vagas V
            LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
            WHERE V.id = :id
              AND V.status_id = 2
              AND V.fechada_em IS NULL
              AND V.publicada_em IS NOT NULL
              AND (V.expira_em IS NULL OR V.expira_em >= CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $vaga = $stmt->fetch(PDO::FETCH_ASSOC);
}

$titulo_vaga = $vaga ? ($vaga['identificador'] ?: ($vaga['cargo_ds'] ?? 'Vaga')) : null;

//- Candidatura: pessoa_id vem só da sessão aberta em talentos/auth.php ou
//- talentos/new_aj1.php - nunca de um campo do cliente.
$candidato_logado = !empty($_SESSION['candidato_idPessoa']);
$ja_candidatou = false;
//- Distingue "acabei de me candidatar agora" de "já estava candidatado antes" -
//- as duas situações resultam em $ja_candidatou = true, mas merecem mensagens
//- diferentes (senão fica ambíguo pra quem está lendo a tela).
$candidatura_recem_enviada = false;

if ($vaga && $candidato_logado) {
    $pessoa_id = (int) $_SESSION['candidato_idPessoa'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidatar'])) {
        $sql = "SELECT id FROM rs_vagas_candidaturas WHERE vaga_id = :vaga_id AND pessoa_id = :pessoa_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':vaga_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
        $stmt->execute();

        if (!$stmt->fetch()) {
            //- fluxo_id 1 = Aplicado (rs_candidatos_fluxo); origem_id 1 = Portal de Vagas
            //- (rs_candidatos_origem) - candidatura veio direto do portal público.
            $sql = "INSERT INTO rs_vagas_candidaturas (vaga_id, pessoa_id, fluxo_id, fluxo_alterado_em, origem_id, criado_em)
                    VALUES (:vaga_id, :pessoa_id, 1, NOW(), 1, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':vaga_id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
            $stmt->execute();
            $candidatura_recem_enviada = true;

            $stmt = $conn->prepare("SELECT nome FROM rh_pessoas WHERE idPessoa = :pessoa_id");
            $stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
            $stmt->execute();
            $nome_candidato = $stmt->fetchColumn() ?: 'Candidato';

            $stmt = $conn->prepare("INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, NOW(), :oque, :quem)");
            $stmt->bindParam(':vaga_id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':oque', 'Nova candidatura recebida: ' . $nome_candidato);
            $stmt->bindValue(':quem', mb_substr($nome_candidato, 0, 45));
            $stmt->execute();
        }
    }

    $sql = "SELECT id FROM rs_vagas_candidaturas WHERE vaga_id = :vaga_id AND pessoa_id = :pessoa_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':vaga_id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
    $stmt->execute();
    $ja_candidatou = (bool) $stmt->fetch();
}

//- Voltando do login/cadastro com a intenção de já se candidatar - dispara o
//- envio sozinho, sem precisar clicar em "Candidatar-se" de novo.
$auto_candidatar = $candidato_logado && !$ja_candidatou && isset($_GET['auto']);

//- "Voltar" tem que respeitar de onde a pessoa veio: quem chegou aqui pelo
//- próprio painel (candidaturas já enviadas) espera voltar pro painel, não
//- cair na vitrine pública de vagas - ver o link em talentos/painel.php.
if (($_GET['from'] ?? '') === 'painel' && $candidato_logado) {
    $voltar_url = '../talentos/painel.php';
    $voltar_label = 'Voltar ao meu painel';
} else {
    $voltar_url = 'index.php';
    $voltar_label = 'Voltar às vagas';
}

//- descricao/experiencia/diferenciais/beneficios podem vir do editor rico (Summernote, já
//- em HTML) ou como texto simples com quebras de linha normais (ex.: preenchido por IA).
//- Só escapa e quebra linha quando não há tag nenhuma - HTML de verdade sai intacto.
function conteudo_rico(?string $valor): string
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        return '';
    }
    return strip_tags($valor) === $valor ? nl2br(htmlspecialchars($valor)) : $valor;
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $vaga ? htmlspecialchars($titulo_vaga) . ' - GERAR | RH' : 'Vaga não encontrada - GERAR | RH' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="vaga_perfil.css">
</head>

<body class="bg-body-tertiary d-flex flex-column min-vh-100">

    <?php if (!$vaga): ?>

    <header class="vaga-hero text-white py-4 mb-4 shadow-sm">
        <div class="container">
            <a href="<?= htmlspecialchars($voltar_url) ?>" class="btn btn-light text-primary fw-semibold rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i><?= htmlspecialchars($voltar_label) ?>
            </a>
        </div>
    </header>

    <main class="container mb-5 flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="alert alert-warning text-center py-5 rounded-4 shadow-sm mx-auto" style="max-width: 560px;" role="alert">
            <i class="fa-solid fa-circle-exclamation fa-2x mb-3 d-block"></i>
            <h5 class="alert-heading">Vaga não encontrada</h5>
            <p class="mb-0">Esta vaga não existe, foi encerrada ou não está mais disponível para candidatura.</p>
        </div>
    </main>

    <?php else:
        $localizacao = $vaga['modalidade'] === 'Remoto'
            ? $vaga['modalidade']
            : trim(($vaga['trab_cidade'] ?? '') . ' - ' . ($vaga['trab_uf'] ?? '') . (($vaga['trab_bairro'] ?? '') !== '' ? ' | ' . $vaga['trab_bairro'] : ''), ' -|');
    ?>

    <!-- HERO com título e principais tags da vaga -->
    <header class="vaga-hero text-white pt-4 pb-5 mb-n5 shadow-sm">
        <div class="container">
            <a href="<?= htmlspecialchars($voltar_url) ?>" class="btn btn-light text-primary fw-semibold rounded-pill px-4 shadow-sm mb-4">
                <i class="fa-solid fa-arrow-left me-2"></i><?= htmlspecialchars($voltar_label) ?>
            </a>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php if (!empty($vaga['setor_ds'])): ?>
                <span class="badge vaga-badge-hero"><i class="fa-solid fa-layer-group me-1"></i><?= htmlspecialchars($vaga['setor_ds']) ?></span>
                <?php endif; ?>
                <span class="badge vaga-badge-hero"><i class="fa-solid fa-house-laptop me-1"></i><?= htmlspecialchars($vaga['modalidade'] ?? 'Presencial') ?></span>
                <?php if (!empty($vaga['tipo_contrato'])): ?>
                <span class="badge vaga-badge-hero"><i class="fa-solid fa-file-signature me-1"></i><?= htmlspecialchars($vaga['tipo_contrato']) ?></span>
                <?php endif; ?>
            </div>

            <h1 class="fw-800 mb-2 display-6"><?= htmlspecialchars($titulo_vaga) ?></h1>
            <?php if (!empty($vaga['cargo_ds']) && $vaga['cargo_ds'] !== $titulo_vaga): ?>
            <p class="opacity-75 mb-2 fs-5"><?= htmlspecialchars($vaga['cargo_ds']) ?></p>
            <?php endif; ?>
            <p class="opacity-75 mb-0">
                <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($localizacao ?: '-') ?>
            </p>
        </div>
    </header>

    <main class="container mb-5 flex-grow-1">
        <div class="row g-4">

            <div class="col-lg-8">
                <div class="card vaga-card-principal shadow rounded-4 border-0">
                    <div class="card-body p-4 p-md-5 vaga-conteudo">

                        <?php if (!empty($vaga['resumo'])): ?>
                        <p class="lead fs-6 fw-medium"><?= nl2br(htmlspecialchars($vaga['resumo'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($vaga['descricao'])): ?>
                        <h6 class="vaga-secao-titulo"><i class="fa-solid fa-briefcase"></i>Sobre a vaga</h6>
                        <div class="vaga-rich"><?= conteudo_rico($vaga['descricao']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($vaga['formacao']) || !empty($vaga['experiencia'])): ?>
                        <h6 class="vaga-secao-titulo"><i class="fa-solid fa-graduation-cap"></i>Requisitos</h6>
                        <?php if (!empty($vaga['formacao'])): ?>
                        <p class="mb-2 fw-medium"><?= htmlspecialchars($vaga['formacao']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($vaga['experiencia'])): ?>
                        <div class="vaga-rich"><?= conteudo_rico($vaga['experiencia']) ?></div>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($vaga['diferenciais'])): ?>
                        <h6 class="vaga-secao-titulo"><i class="fa-solid fa-star"></i>Diferenciais</h6>
                        <div class="vaga-rich"><?= conteudo_rico($vaga['diferenciais']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($vaga['beneficios'])): ?>
                        <h6 class="vaga-secao-titulo"><i class="fa-solid fa-gift"></i>Benefícios</h6>
                        <div class="vaga-rich"><?= conteudo_rico($vaga['beneficios']) ?></div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card vaga-sidebar shadow rounded-4 border-0">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Detalhes</h6>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fa-solid fa-users me-2 text-primary mt-1"></i>
                                <span><?= (int) $vaga['qtd'] ?> vaga(s)<?php if (!empty($vaga['pcd'])): ?><br><span class="badge bg-info-subtle text-info mt-1">Aceita PCD</span><?php endif; ?></span>
                            </li>
                            <?php if (!empty($vaga['horario'])): ?>
                            <li class="mb-3 d-flex align-items-start"><i class="fa-solid fa-clock me-2 text-primary mt-1"></i><span><?= htmlspecialchars($vaga['horario']) ?></span></li>
                            <?php endif; ?>
                            <?php if (!empty($vaga['exibir_salario']) && !empty($vaga['salario'])): ?>
                            <li class="mb-3 d-flex align-items-start"><i class="fa-solid fa-sack-dollar me-2 text-primary mt-1"></i><span>R$ <?= number_format((float) $vaga['salario'], 2, ',', '.') ?></span></li>
                            <?php endif; ?>
                            <li class="mb-0 d-flex align-items-start"><i class="fa-solid fa-calendar-days me-2 text-primary mt-1"></i><span>Publicada em <?= date('d/m/Y', strtotime($vaga['publicada_em'])) ?></span></li>
                        </ul>
                        <?php if ($candidatura_recem_enviada): ?>
                        <div class="alert alert-success text-center mb-0 py-2">
                            <i class="fa-solid fa-circle-check me-2"></i>Candidatura enviada com sucesso!
                        </div>
                        <?php elseif ($ja_candidatou): ?>
                        <div class="alert alert-secondary text-center mb-0 py-2">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Você já havia se candidatado a esta vaga.
                        </div>
                        <?php elseif ($candidato_logado): ?>
                        <form method="POST" id="formCandidatar">
                            <input type="hidden" name="candidatar" value="1">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                                <i class="fa-solid fa-paper-plane me-2"></i>Candidatar-se
                            </button>
                        </form>
                        <?php else: ?>
                        <a href="../talentos/index.php?vaga_id=<?= $id ?>" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                            <i class="fa-solid fa-paper-plane me-2"></i>Candidatar-se
                        </a>
                        <p class="text-muted small text-center mt-2 mb-0">
                            Faça login ou <a href="../talentos/new.php?vaga_id=<?= $id ?>">crie seu currículo</a> para se candidatar.
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php endif; ?>

    <footer class="py-4 text-center text-body-secondary border-top mt-auto">
        <div class="container">
            <small>&copy; <?= date('Y') ?> GERAR - Todos os direitos reservados.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vaga_perfil.js"></script>
    <?php if ($auto_candidatar): ?>
    <script>
        // Voltando do login/cadastro com intenção de se candidatar - envia sozinho.
        document.getElementById('formCandidatar')?.submit();
    </script>
    <?php endif; ?>
    <?php if ($candidatura_recem_enviada): ?>
    <script>
        // Mostra a confirmação por alguns segundos e volta pra tela de origem.
        setTimeout(function () {
            window.location.href = <?= json_encode($voltar_url) ?>;
        }, 2500);
    </script>
    <?php endif; ?>
</body>
</html>
