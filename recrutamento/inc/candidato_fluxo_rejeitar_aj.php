<?php
//
//- candidato_fluxo_rejeitar_aj.php | Move uma candidatura para Rejeitado, com motivo -
//- vaga_candidatos.php
//- (C)haia, 27/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

const FLUXO_REJEITADO = 6;

$candidatura_id = filter_input(INPUT_POST, 'candidatura_id', FILTER_VALIDATE_INT);
$motivo = trim((string) filter_input(INPUT_POST, 'motivo', FILTER_DEFAULT));

if (!$candidatura_id) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos."]);
    exit;
}

$stmt = $conn->prepare("UPDATE rs_vagas_candidaturas
                         SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(), motivo_rejeicao = :motivo
                         WHERE id = :id");
$stmt->bindValue(':fluxo', FLUXO_REJEITADO, PDO::PARAM_INT);
$stmt->bindValue(':motivo', $motivo !== '' ? $motivo : null, $motivo !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
$stmt->bindValue(':id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo json_encode(["status" => false, "msg" => "Candidatura não encontrada."]);
    exit;
}

echo json_encode(["status" => true, "msg" => "Candidato movido para Rejeitado."]);
exit;
