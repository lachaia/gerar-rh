<?php
//
//- aprova.php | Programa para o Superintendente avaliar a solicitação da vaga
//- (C)haia, 17/08/2026
//

session_start();

include "../app/includes/conexao_gerar.php";

//
//- STATUS_ID de referência (rs_vagas_status): 2 = APROVADA | 3 = REPROVADA
//
const STATUS_APROVADA  = 2;
const STATUS_REPROVADA = 3;

//
//- O link enviado por e-mail (ver inc/vaga_new_inc_aj.php) é sempre GET e traz:
//- id = rs_vagas.id (vaga_id) | super_id = rs_superintendentes.id | token = rs_vagas.token
//
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vaga_id  = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $super_id = filter_input(INPUT_POST, 'super_id', FILTER_VALIDATE_INT);
    $token    = filter_input(INPUT_POST, 'token', FILTER_DEFAULT);
} else {
    $vaga_id  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $super_id = filter_input(INPUT_GET, 'super_id', FILTER_VALIDATE_INT);
    $token    = filter_input(INPUT_GET, 'token', FILTER_DEFAULT);
}

$erro         = null;
$sucesso      = null;
$dados_aprova = null;
$dados_vaga   = null;

if (!$vaga_id || !$super_id || !$token) {
    $erro = "Link inválido ou incompleto. Verifique o endereço recebido por e-mail ou contate o suporte de TI.";
} else {
    try {
        $sql = "SELECT V.*,
                        S.identificador as subsede_ds,
                        O.descricao as orgao_ds,
                        date(V.criado_em) as _criado_em,
                        P.identificador as polo_ds,
                        DATEDIFF(CURRENT_DATE(), DATE(V.criado_em)) AS dias,
                        ST.status as status_ds,
                        C.nome as cargo_ds,
                        M.descricao as motivo_ds
                    FROM rs_vagas V
                    LEFT JOIN rh_subsedes S on S.subsede_id = V.subsede_id
                    LEFT JOIN rh_polos P on P.polo_id = V.polo_id
                    LEFT JOIN rh_organograma O on O.idOrgao = V.orgao_id
                    LEFT JOIN rs_vagas_status ST ON ST.id = V.status_id
                    LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
                    LEFT JOIN rs_vagas_mot M ON M.id = V.motivo_id
                    WHERE V.id = :vaga_id AND V.token = :token";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $dados_vaga = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados_vaga) {
            $erro = "Link inválido: o token de segurança não confere com a vaga.";
        } else {
            $sql = "SELECT A.*, S.identificador AS super_nome
                    FROM rs_vagas_aprova A
                    LEFT JOIN rs_superintendentes S ON S.id = A.super_id
                    WHERE A.vaga_id = :vaga_id AND A.super_id = :super_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
            $stmt->bindParam(':super_id', $super_id, PDO::PARAM_INT);
            $stmt->execute();
            $dados_aprova = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dados_aprova) {
                $erro = "Registro de aprovação não encontrado para este superintendente.";
            } elseif ($dados_aprova['aprovado_em'] || $dados_aprova['reprovado_em']) {
                $decisao_anterior = $dados_aprova['aprovado_em'] ? 'APROVADA' : 'REPROVADA';
                $data_decisao     = $dados_aprova['aprovado_em'] ?: $dados_aprova['reprovado_em'];
                $erro = "Este link já foi utilizado. Sua decisão (<strong>$decisao_anterior</strong>) foi registrada em "
                      . date('d/m/Y \à\s H:i', strtotime($data_decisao)) . ".";
            }
        }
    } catch (PDOException $e) {
        error_log("aprova.php | erro ao consultar dados: " . $e->getMessage());
        $erro = "Erro ao consultar os dados. Tente novamente mais tarde ou contate o suporte de TI.";
    }
}

//
//- PROCESSA A DECISÃO DO SUPERINTENDENTE
//
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$erro) {

    $decisao = filter_input(INPUT_POST, 'decisao', FILTER_DEFAULT);
    $obs     = trim((string) filter_input(INPUT_POST, 'obs', FILTER_DEFAULT));

    if (!in_array($decisao, ['aprovar', 'reprovar'], true)) {
        $erro = "Selecione uma decisão válida (Aprovar ou Reprovar).";
    } elseif ($decisao === 'reprovar' && $obs === '') {
        $erro = "É obrigatório informar o motivo da reprovação.";
    } else {
        try {
            $conn->beginTransaction();

            $aprova_id = $dados_aprova['id'];

            //- Trava o registro para evitar duplo envio (duplo clique / duas abas)
            $stmt = $conn->prepare("SELECT aprovado_em, reprovado_em FROM rs_vagas_aprova WHERE id = :id FOR UPDATE");
            $stmt->bindParam(':id', $aprova_id, PDO::PARAM_INT);
            $stmt->execute();
            $trava = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($trava['aprovado_em'] || $trava['reprovado_em']) {
                $conn->rollBack();
                $erro = "Este link já foi utilizado por outra ação.";
            } else {

                $obs_salvar = $obs === '' ? null : $obs;

                $super_nome = $dados_aprova['super_nome'] ?? 'Superintendente';

                if ($decisao === 'aprovar') {

                    $stmt = $conn->prepare("UPDATE rs_vagas_aprova SET aprovado_em = NOW(), obs = :obs WHERE id = :id");
                    $stmt->bindParam(':obs', $obs_salvar);
                    $stmt->bindParam(':id', $aprova_id, PDO::PARAM_INT);
                    $stmt->execute();

                    vaga_timeline($conn, $vaga_id, date('Y-m-d H:i:s'), "Aprovada por {$super_nome}", $super_nome);

                    //- A vaga só é considerada Aprovada quando TODOS os superintendentes
                    //- designados já se manifestaram favoravelmente (ver rs_vagas_status.id=2)
                    $stmt = $conn->prepare("SELECT COUNT(*) AS pendentes FROM rs_vagas_aprova
                                             WHERE vaga_id = :vaga_id AND aprovado_em IS NULL AND reprovado_em IS NULL");
                    $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $pendentes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['pendentes'];

                    if ($pendentes === 0) {
                        //- fluxo_alterado_em marca a entrada na coluna "Início" do fluxo.php (fluxo_id ainda NULL)
                        $stmt = $conn->prepare("UPDATE rs_vagas SET status_id = :status, aprovada = 1, fluxo_alterado_em = NOW() WHERE id = :vaga_id");
                        $stmt->bindValue(':status', STATUS_APROVADA, PDO::PARAM_INT);
                        $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
                        $stmt->execute();

                        vaga_timeline($conn, $vaga_id, date('Y-m-d H:i:s'), "Vaga aprovada por todos os superintendentes — liberada para o Recrutamento", $super_nome);

                        $sucesso = "Aprovação registrada com sucesso! Todos os superintendentes já aprovaram e a vaga foi liberada para o Recrutamento.";
                    } else {
                        $sucesso = "Aprovação registrada com sucesso! Aguardando a decisão dos demais superintendentes.";
                    }

                    $conn->commit();

                } else {

                    $stmt = $conn->prepare("UPDATE rs_vagas_aprova SET reprovado_em = NOW(), obs = :obs WHERE id = :id");
                    $stmt->bindParam(':obs', $obs_salvar);
                    $stmt->bindParam(':id', $aprova_id, PDO::PARAM_INT);
                    $stmt->execute();

                    $stmt = $conn->prepare("UPDATE rs_vagas SET status_id = :status, aprovada = 0 WHERE id = :vaga_id");
                    $stmt->bindValue(':status', STATUS_REPROVADA, PDO::PARAM_INT);
                    $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
                    $stmt->execute();

                    vaga_timeline($conn, $vaga_id, date('Y-m-d H:i:s'), "Reprovada por {$super_nome} — Motivo: {$obs}", $super_nome);

                    $conn->commit();

                    enviar_email_reprovacao($dados_vaga, $obs, $super_nome);

                    $sucesso = "Reprovação registrada. O solicitante foi notificado por e-mail.";
                }
            }
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("aprova.php | erro ao registrar decisão (vaga_id=$vaga_id, super_id=$super_id): " . $e->getMessage());
            $erro = "Erro ao registrar sua decisão. Tente novamente ou contate o suporte de TI.";
        }
    }
}

function enviar_email_reprovacao(array $vaga, string $motivo, string $super_nome): bool
{
    if (empty($vaga['email_resposta'])) {
        return false;
    }

    require_once "../app/includes/email_config.php";

    $titulo_vaga = htmlspecialchars($vaga['identificador'] ?: $vaga['codigo_vaga'] ?: $vaga['cargo_ds'] ?: ('Vaga #' . $vaga['id']));
    $motivo_html = nl2br(htmlspecialchars($motivo));
    $super_html  = htmlspecialchars($super_nome);

    try {
        $mail = criarMailer();
        $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
        $mail->addAddress($vaga['email_resposta']);
        $mail->Subject = "Solicitação de Vaga Reprovada - $titulo_vaga";
        $mail->Body = "
            <div style='font-family: Segoe UI, Arial, sans-serif; color:#333;'>
                <h2 style='color:#dc3545;'>Solicitação de Vaga Reprovada</h2>
                <p>Olá,</p>
                <p>A solicitação de vaga <strong>$titulo_vaga</strong> foi <strong>reprovada</strong> por um dos superintendentes responsáveis pela aprovação.</p>
                <p><strong>Motivo informado:</strong></p>
                <blockquote style='background:#f8f9fa;border-left:4px solid #dc3545;padding:10px 15px;margin:10px 0;'>$motivo_html</blockquote>
                <p style='color:#718096;font-size:13px;'>Reprovado por: <strong>$super_html</strong></p>
                <p>Para mais informações ou para reencaminhar a solicitação, entre em contato com o time de Recrutamento &amp; Seleção.</p>
                <p>Atenciosamente,<br>RH - GERAR</p>
            </div>";
        $mail->AltBody = "A solicitação de vaga \"$titulo_vaga\" foi reprovada.\nMotivo: $motivo\nReprovado por: $super_nome";
        return $mail->send();
    } catch (Exception $e) {
        error_log("aprova.php | erro ao enviar e-mail de reprovação (vaga_id={$vaga['id']}): " . $e->getMessage());
        return false;
    }
}

function vaga_timeline(PDO $conn, int $vaga_id, string $agora, string $oque, string $quem): int
{
    $sql = "INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, :quando, :oque, :quem)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindParam(':quando', $agora, PDO::PARAM_STR);
    $stmt->bindParam(':oque', $oque, PDO::PARAM_STR);
    $stmt->bindParam(':quem', $quem, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $conn->lastInsertId();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GERAR|R&S - Aprovação de Vaga</title>
    <link rel="icon" type="image/x-icon" href="img/logo.ico">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link href="css/styles.css" rel="stylesheet" />

    <style>
        main.container { max-width: 1200px; }
        .card { background-color: #1e1e1e; border-color: #333; }
        .card-header { background-color: transparent; border-color: #333; }
        .vaga-campo { margin-bottom: 1rem; }
        .vaga-campo label { display: block; font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #999; font-weight: 600; }
        .vaga-campo .valor { color: #e0e0e0; }
        .vaga-campo .valor :is(ul, ol) { padding-left: 1.2rem; margin-bottom: 0; }
        .decisao-radio { display: none; }
        .decisao-label { cursor: pointer; }
        .status-badge { font-size: .8rem; padding: .35em .8em; }
        .centro-tela { min-height: 80vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>

<body>
<main class="container py-4">

    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-secondary">
        <img src="img/loginho.png" alt="GERAR" style="height:48px;">
        <div>
            <h4 class="mb-0 text-white fw-bold">Aprovação de Solicitação de Vaga</h4>
            <p class="text-white-50 small mb-0">Portal Intranet — Módulo de Recrutamento &amp; Seleção</p>
        </div>
    </div>

<?php if ($erro): ?>

    <div class="centro-tela">
        <div class="card shadow-sm" style="max-width: 560px;">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-circle-exclamation fa-3x text-warning mb-3"></i>
                <h5 class="text-white mb-3">Não foi possível continuar</h5>
                <p class="text-white-50 mb-0"><?= $erro /* mensagens fixas do próprio script */ ?></p>
            </div>
        </div>
    </div>

<?php elseif ($sucesso): ?>

    <div class="centro-tela">
        <div class="card shadow-sm" style="max-width: 560px;">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                <h5 class="text-white mb-3">Decisão registrada!</h5>
                <p class="text-white-50 mb-0"><?= htmlspecialchars($sucesso) ?></p>
            </div>
        </div>
    </div>

<?php else:
    $titulo_vaga = $dados_vaga['identificador'] ?: $dados_vaga['codigo_vaga'] ?: $dados_vaga['cargo_ds'] ?: ('Vaga #' . $dados_vaga['id']);
?>

    <div class="row g-4">

        <!-- QUADRO ESQUERDO: DADOS DA VAGA -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-primary fw-bold">
                        <i class="fa-solid fa-briefcase me-2"></i>Detalhes da Vaga
                    </h6>
                    <span class="badge status-badge" style="background-color: <?= htmlspecialchars($dados_vaga['cor_fundo'] ?? '#6c757d') ?>; color: <?= ($dados_vaga['cor_frente'] ?? '') === 'text-dark' ? '#000' : '#fff' ?>;">
                        <?= htmlspecialchars($dados_vaga['status_ds'] ?? '') ?>
                    </span>
                </div>
                <div class="card-body">

                    <h4 class="text-white fw-bold mb-1"><?= htmlspecialchars($titulo_vaga) ?></h4>
                    <p class="text-white-50 mb-2"><?= htmlspecialchars($dados_vaga['cargo_ds'] ?? '') ?></p>

                    <?php if (!empty($dados_vaga['arquivo_aut_gestor'])): ?>
                    <button type="button" class="btn btn-outline-info btn-sm mb-4" data-bs-toggle="modal" data-bs-target="#modalAutGestor">
                        <i class="fa-solid fa-file-lines me-1"></i>Ver Autorização do Gestor
                    </button>
                    <?php else: ?>
                    <div class="mb-4"></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4 vaga-campo">
                            <label>Órgão / Departamento</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['orgao_ds'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-4 vaga-campo">
                            <label>Subsede</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['subsede_ds'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-4 vaga-campo">
                            <label>Polo</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['polo_ds'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-3 vaga-campo">
                            <label>Quantidade</label>
                            <div class="valor"><?= (int) $dados_vaga['qtd'] ?></div>
                        </div>
                        <div class="col-md-3 vaga-campo">
                            <label>Modalidade</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['modalidade'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-3 vaga-campo">
                            <label>Tipo de Contrato</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['tipo_contrato'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-3 vaga-campo">
                            <label>Recrutamento</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['forma_recrutamento'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-4 vaga-campo">
                            <label>Horário</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['horario'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-4 vaga-campo">
                            <label>Local de Trabalho</label>
                            <div class="valor"><?= htmlspecialchars(trim(($dados_vaga['trab_cidade'] ?? '') . ' / ' . ($dados_vaga['trab_uf'] ?? ''), ' /')) ?: '-' ?></div>
                        </div>
                        <div class="col-md-4 vaga-campo">
                            <label>Aberta há</label>
                            <div class="valor"><?= (int) $dados_vaga['dias'] ?> dia(s)</div>
                        </div>

                        <?php if (!empty($dados_vaga['exibir_salario'])): ?>
                        <div class="col-md-4 vaga-campo">
                            <label>Salário</label>
                            <div class="valor">R$ <?= number_format((float) $dados_vaga['salario'], 2, ',', '.') ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-4 vaga-campo">
                            <label>Motivo da Solicitação</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['motivo_ds'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-4 vaga-campo">
                            <label>Solicitado por</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['criado_por'] ?? '-') ?> em <?= date('d/m/Y', strtotime($dados_vaga['criado_em'])) ?></div>
                        </div>

                        <div class="col-md-4 vaga-campo">
                            <label>Formação Exigida</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['formacao'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-4 vaga-campo">
                            <label>CNH</label>
                            <div class="valor"><?= htmlspecialchars($dados_vaga['cnh'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-4 vaga-campo">
                            <label>Aceita PCD</label>
                            <div class="valor"><?= !empty($dados_vaga['pcd']) ? 'Sim' : 'Não' ?></div>
                        </div>

                        <?php if (!empty($dados_vaga['descricao'])): ?>
                        <div class="col-12 vaga-campo">
                            <label>Descrição</label>
                            <div class="valor"><?= $dados_vaga['descricao'] ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($dados_vaga['experiencia'])): ?>
                        <div class="col-12 vaga-campo">
                            <label>Experiência Desejada</label>
                            <div class="valor"><?= $dados_vaga['experiencia'] ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($dados_vaga['atividades'])): ?>
                        <div class="col-12 vaga-campo">
                            <label>Atividades da Vaga</label>
                            <div class="valor"><?= $dados_vaga['atividades'] ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($dados_vaga['c_comportamentais'])): ?>
                        <div class="col-md-6 vaga-campo">
                            <label>Competências Comportamentais</label>
                            <div class="valor"><?= $dados_vaga['c_comportamentais'] ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($dados_vaga['c_tecnicas'])): ?>
                        <div class="col-md-6 vaga-campo">
                            <label>Competências Técnicas</label>
                            <div class="valor"><?= $dados_vaga['c_tecnicas'] ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($dados_vaga['equipamentos'])): ?>
                        <div class="col-12 vaga-campo">
                            <label>Equipamentos Solicitados</label>
                            <div class="valor"><?= $dados_vaga['equipamentos'] ?></div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- QUADRO DIREITO: FORMULÁRIO DE DECISÃO -->
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="mb-0 text-primary fw-bold">
                        <i class="fa-solid fa-clipboard-check me-2"></i>Sua Decisão
                    </h6>
                </div>
                <div class="card-body">

                    <p class="text-white-50 small">
                        Superintendente: <strong class="text-white"><?= htmlspecialchars($dados_aprova['super_nome'] ?? '-') ?></strong>
                    </p>

                    <form method="POST" action="aprova.php" id="formDecisao">
                        <input type="hidden" name="id" value="<?= (int) $vaga_id ?>">
                        <input type="hidden" name="super_id" value="<?= (int) $super_id ?>">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="d-flex gap-2 mb-3">
                            <input type="radio" class="decisao-radio btn-check" name="decisao" id="decisao_aprovar" value="aprovar" required>
                            <label class="decisao-label btn btn-outline-success flex-fill py-3 fw-bold" for="decisao_aprovar">
                                <i class="fa-solid fa-thumbs-up me-2"></i>Aprovar
                            </label>

                            <input type="radio" class="decisao-radio btn-check" name="decisao" id="decisao_reprovar" value="reprovar" required>
                            <label class="decisao-label btn btn-outline-danger flex-fill py-3 fw-bold" for="decisao_reprovar">
                                <i class="fa-solid fa-thumbs-down me-2"></i>Reprovar
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small fw-bold" for="obs">
                                Observação <span id="obs_obrigatoria" class="text-danger d-none">*</span>
                            </label>
                            <textarea name="obs" id="obs" class="form-control" rows="5"
                                      placeholder="Comentários sobre a decisão. Obrigatório em caso de reprovação."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold" id="btnEnviar">
                            <i class="fa-solid fa-paper-plane me-2"></i>Enviar Decisão
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>

    <?php if (!empty($dados_vaga['arquivo_aut_gestor'])):
        $arquivo_gestor  = $dados_vaga['arquivo_aut_gestor'];
        $url_arquivo     = 'docs/' . rawurlencode($arquivo_gestor);
        $extensao_gestor = strtolower(pathinfo($arquivo_gestor, PATHINFO_EXTENSION));
    ?>
    <div class="modal fade" id="modalAutGestor" tabindex="-1" aria-labelledby="modalAutGestorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background-color:#1e1e1e;border-color:#333;">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white" id="modalAutGestorLabel">
                        <i class="fa-solid fa-file-lines me-2"></i>Autorização do Gestor Imediato
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body text-center">
                    <?php if (in_array($extensao_gestor, ['jpg', 'jpeg', 'png'], true)): ?>
                        <img src="<?= htmlspecialchars($url_arquivo) ?>" class="img-fluid rounded" alt="Autorização do Gestor">
                    <?php elseif ($extensao_gestor === 'pdf'): ?>
                        <iframe src="<?= htmlspecialchars($url_arquivo) ?>" style="width:100%; height:70vh; border:0;"></iframe>
                    <?php else: ?>
                        <p class="text-white-50 py-4">
                            Este tipo de arquivo (.<?= htmlspecialchars($extensao_gestor) ?>) não pode ser exibido diretamente.
                            Utilize o botão abaixo para abrir.
                        </p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-secondary">
                    <a href="<?= htmlspecialchars($url_arquivo) ?>" target="_blank" rel="noopener" class="btn btn-primary">
                        <i class="fa-solid fa-up-right-from-square me-2"></i>Abrir em nova aba
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        (function () {
            var radios = document.querySelectorAll('input[name="decisao"]');
            var obs = document.getElementById('obs');
            var obsObrigatoria = document.getElementById('obs_obrigatoria');
            var form = document.getElementById('formDecisao');

            radios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    var reprovar = radio.value === 'reprovar' && radio.checked;
                    obs.required = reprovar;
                    obsObrigatoria.classList.toggle('d-none', !reprovar);
                });
            });

            form.addEventListener('submit', function (e) {
                var decisaoLabel = document.querySelector('input[name="decisao"]:checked')?.value === 'aprovar' ? 'APROVAR' : 'REPROVAR';
                if (!confirm('Confirma a decisão de ' + decisaoLabel + ' esta vaga? Esta ação não poderá ser desfeita.')) {
                    e.preventDefault();
                    return;
                }
                document.getElementById('btnEnviar').disabled = true;
            });
        })();
    </script>

<?php endif; ?>

</main>
</body>
</html>
