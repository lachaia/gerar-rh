<?php
//
//- vaga_fluxo_mover_aj.php | Move uma vaga entre as colunas do fluxo (Kanban) - atualiza rs_vagas.fluxo_id
//- (C)haia, 26/08/2026
//
//- rs_vagas.status_id é só o portão de aprovação (2 = APROVADA) e não muda aqui.
//- rs_vagas.fluxo_id referencia rs_vagas_fluxo; fluxo_id = 0 (recebido do board) significa
//- "voltar para Aprovada, aguardando recrutador" e é gravado como NULL.
//- FLUXO_ID de encerramento: 6 = CANCELADA | 7 = CONCLUIDA
//- Entrar em DIVULGAÇÃO (2) tem regras próprias (dados de publicação completos + expira_em
//- opcional) e passa por inc/vaga_fluxo_publicar_aj.php, não por aqui. As etapas seguintes
//- (EM TRIAGEM, ENTREVISTAS, CONTRATAÇÃO, CONCLUIDA) presumem que a vaga já foi publicada.
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

const STATUS_APROVADA = 2;
const FLUXO_DIVULGACAO = 2;
const FLUXO_CANCELADA = 6;
const FLUXO_CONCLUIDA = 7;
const FLUXO_EXIGE_PUBLICADA = [3, 4, 5, 7]; // EM TRIAGEM, ENTREVISTAS, CONTRATAÇÃO, CONCLUIDA

$vaga_id = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);
$fluxo_novo = filter_input(INPUT_POST, 'fluxo_id', FILTER_VALIDATE_INT);

if (!$vaga_id || $fluxo_novo === false || $fluxo_novo === null || $fluxo_novo < 0) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos."]);
    exit;
}

if ($fluxo_novo === FLUXO_DIVULGACAO) {
    echo json_encode(["status" => false, "msg" => "Para publicar a vaga, use a etapa de publicação (defina a validade do anúncio)."]);
    exit;
}

if ($fluxo_novo > 0) {
    $stmt = $conn->prepare("SELECT id FROM rs_vagas_fluxo WHERE id = :id AND ativo = 1");
    $stmt->bindValue(':id', $fluxo_novo, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetch()) {
        echo json_encode(["status" => false, "msg" => "Etapa de destino inválida."]);
        exit;
    }
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT status_id, fluxo_id, publicada_em FROM rs_vagas WHERE id = :id FOR UPDATE");
    $stmt->bindParam(':id', $vaga_id, PDO::PARAM_INT);
    $stmt->execute();
    $atual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$atual) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Vaga não encontrada."]);
        exit;
    }

    if ((int) $atual['status_id'] !== STATUS_APROVADA) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Esta vaga ainda não foi aprovada e não pertence ao fluxo de recrutamento."]);
        exit;
    }

    if (in_array($fluxo_novo, FLUXO_EXIGE_PUBLICADA, true) && $atual['publicada_em'] === null) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Esta vaga ainda não foi publicada — mova primeiro para Divulgação."]);
        exit;
    }

    $fluxo_atual = $atual['fluxo_id'] === null ? 0 : (int) $atual['fluxo_id'];

    if ($fluxo_atual === $fluxo_novo) {
        $conn->commit();
        echo json_encode(["status" => true, "msg" => "Nenhuma alteração necessária."]);
        exit;
    }

    $fecha = in_array($fluxo_novo, [FLUXO_CANCELADA, FLUXO_CONCLUIDA], true);
    $quem = $_SESSION['nmLogin'] ?? '';
    $fluxo_novo_ou_null = $fluxo_novo === 0 ? null : $fluxo_novo;

    if ($fecha) {
        $sql = "UPDATE rs_vagas SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(), fechada_em = NOW(), fechada_por = :quem WHERE id = :id";
    } else {
        $sql = "UPDATE rs_vagas SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(), fechada_em = NULL, fechada_por = NULL WHERE id = :id";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':fluxo', $fluxo_novo_ou_null, PDO::PARAM_INT);
    if ($fecha) {
        $stmt->bindValue(':quem', $quem, PDO::PARAM_STR);
    }
    $stmt->bindValue(':id', $vaga_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($fluxo_novo === 0) {
        $etapa_nome = "Aprovada (aguardando recrutador)";
    } else {
        $stmt = $conn->prepare("SELECT status FROM rs_vagas_fluxo WHERE id = :id");
        $stmt->bindValue(':id', $fluxo_novo, PDO::PARAM_INT);
        $stmt->execute();
        $etapa_nome = $stmt->fetchColumn() ?: "Etapa #{$fluxo_novo}";
    }

    vaga_timeline($conn, $vaga_id, date('Y-m-d H:i:s'), "Vaga movida no fluxo para '{$etapa_nome}'", $quem);

    $conn->commit();
    echo json_encode(["status" => true, "msg" => "Vaga movida para {$etapa_nome}."]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("vaga_fluxo_mover_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Falha ao mover a vaga."]);
}
exit;

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
