<?php
//
//- vaga_fluxo_recrutador_aj.php | Define o recrutador de uma vaga na coluna "Aguardando Recrutador"
//- do fluxo.php e avança a vaga para ALINHAMENTO. Só atua sobre vagas aprovadas com fluxo_id ainda
//- NULL - depois disso, o recrutador só é alterado pela tela cheia (vaga_edit.php).
//- (C)haia, 26/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

const STATUS_APROVADA = 2;
const FLUXO_ALINHAMENTO = 1;

$vaga_id = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);
$recrutador_id = filter_input(INPUT_POST, 'recrutador_id', FILTER_VALIDATE_INT);

if (!$vaga_id || !$recrutador_id) {
    echo json_encode(["status" => false, "msg" => "Selecione um recrutador válido."]);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT status_id, fluxo_id FROM rs_vagas WHERE id = :id FOR UPDATE");
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
        echo json_encode(["status" => false, "msg" => "Esta vaga ainda não foi aprovada."]);
        exit;
    }
    if ($atual['fluxo_id'] !== null) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Esta vaga já está em outra etapa do fluxo. Atualize a página."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT P.nome
                             FROM rh_usuarios U INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa
                             WHERE U.idUsuario = :id AND U.idUsuarioGrupo = 6 AND U.ativo = 1");
    $stmt->bindParam(':id', $recrutador_id, PDO::PARAM_INT);
    $stmt->execute();
    $recrutador_nome = $stmt->fetchColumn();

    if (!$recrutador_nome) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Recrutador inválido."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE rs_vagas SET recrutador_id = :recrutador_id, fluxo_id = :fluxo_id, fluxo_alterado_em = NOW() WHERE id = :id");
    $stmt->bindValue(':recrutador_id', $recrutador_id, PDO::PARAM_INT);
    $stmt->bindValue(':fluxo_id', FLUXO_ALINHAMENTO, PDO::PARAM_INT);
    $stmt->bindValue(':id', $vaga_id, PDO::PARAM_INT);
    $stmt->execute();

    $quem = $_SESSION['nmLogin'] ?? $recrutador_nome;
    vaga_timeline($conn, $vaga_id, date('Y-m-d H:i:s'), "Recrutador {$recrutador_nome} atribuído à vaga — em alinhamento", $quem);

    $conn->commit();
    echo json_encode(["status" => true, "msg" => "Recrutador definido. Vaga movida para ALINHAMENTO."]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("vaga_fluxo_recrutador_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Falha ao definir o recrutador."]);
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
