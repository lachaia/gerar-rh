<?php
//
//- candidato_fluxo_mover_aj.php | Move uma candidatura entre as colunas do Kanban de
//- candidatos - atualiza rs_vagas_candidaturas.fluxo_id | vaga_candidatos.php
//- (C)haia, 27/08/2026 | (U) 2026-08-27
//
//- Mover para Rejeitado (6) exige motivo e passa por candidato_fluxo_rejeitar_aj.php,
//- não por aqui - mesma lógica de vaga_fluxo_mover_aj.php/vaga_fluxo_publicar_aj.php.
//
//- Ao mover o primeiro candidato para Triagem, promove a VAGA de DIVULGAÇÃO para
//- EM TRIAGEM no fluxo dela (rs_vagas.fluxo_id) - sinal de que o recrutador já
//- começou a trabalhar a vaga de verdade, não só esperando currículos chegarem.
//- Não mexe se a vaga já estiver além de DIVULGAÇÃO (ex.: já em ENTREVISTAS).
//

session_start();

if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [6, 9], true)) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

const FLUXO_CAND_TRIAGEM = 2;
const FLUXO_CAND_SCREENING = 3;
const FLUXO_CAND_REJEITADO = 6;
const FLUXO_VAGA_DIVULGACAO = 2;
const FLUXO_VAGA_EM_TRIAGEM = 3;

$candidatura_id = filter_input(INPUT_POST, 'candidatura_id', FILTER_VALIDATE_INT);
$fluxo_novo = filter_input(INPUT_POST, 'fluxo_id', FILTER_VALIDATE_INT);

if (!$candidatura_id || !$fluxo_novo) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos."]);
    exit;
}

if ($fluxo_novo === FLUXO_CAND_REJEITADO) {
    echo json_encode(["status" => false, "msg" => "Para rejeitar, informe o motivo."]);
    exit;
}

if ($fluxo_novo === FLUXO_CAND_SCREENING) {
    echo json_encode(["status" => false, "msg" => "Para agendar o Screening, informe a data e o horário."]);
    exit;
}

$stmt = $conn->prepare("SELECT id, status FROM rs_candidatos_fluxo WHERE id = :id AND ativo = 1");
$stmt->bindValue(':id', $fluxo_novo, PDO::PARAM_INT);
$stmt->execute();
$destino = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$destino) {
    echo json_encode(["status" => false, "msg" => "Etapa de destino inválida."]);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT vaga_id FROM rs_vagas_candidaturas WHERE id = :id FOR UPDATE");
    $stmt->bindValue(':id', $candidatura_id, PDO::PARAM_INT);
    $stmt->execute();
    $vaga_id = $stmt->fetchColumn();

    if (!$vaga_id) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Candidatura não encontrada."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE rs_vagas_candidaturas
                             SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(), motivo_rejeicao = NULL
                             WHERE id = :id");
    $stmt->bindValue(':fluxo', $fluxo_novo, PDO::PARAM_INT);
    $stmt->bindValue(':id', $candidatura_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($fluxo_novo === FLUXO_CAND_TRIAGEM) {
        $stmt = $conn->prepare("SELECT fluxo_id FROM rs_vagas WHERE id = :id FOR UPDATE");
        $stmt->bindValue(':id', $vaga_id, PDO::PARAM_INT);
        $stmt->execute();
        $fluxo_vaga_atual = (int) $stmt->fetchColumn();

        if ($fluxo_vaga_atual === FLUXO_VAGA_DIVULGACAO) {
            $stmt = $conn->prepare("UPDATE rs_vagas SET fluxo_id = :fluxo, fluxo_alterado_em = NOW() WHERE id = :id");
            $stmt->bindValue(':fluxo', FLUXO_VAGA_EM_TRIAGEM, PDO::PARAM_INT);
            $stmt->bindValue(':id', $vaga_id, PDO::PARAM_INT);
            $stmt->execute();

            $quem = $_SESSION['nmLogin'] ?? '';
            vaga_timeline($conn, (int) $vaga_id, date('Y-m-d H:i:s'), "Vaga movida automaticamente no fluxo para 'EM TRIAGEM' (primeiro candidato em triagem)", $quem);
        }
    }

    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("candidato_fluxo_mover_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Falha ao mover o candidato."]);
    exit;
}

echo json_encode(["status" => true, "msg" => "Candidato movido para " . $destino['status'] . "."]);
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
