<?php
//
//- vaga_fluxo_alt_aj.php | Altera registro existente em rs_vagas_fluxo
//- (C)haia, 26/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = strtoupper(trim((string) filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW)));
$descricao = trim((string) filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW));
$cor_frente = trim((string) filter_input(INPUT_POST, 'cor_frente', FILTER_UNSAFE_RAW));
$cor_fundo = trim((string) filter_input(INPUT_POST, 'cor_fundo', FILTER_UNSAFE_RAW));
$ativo = filter_input(INPUT_POST, 'ativo', FILTER_VALIDATE_INT) ? 1 : 0;

if (!$id) {
    echo json_encode(["status" => false, "msg" => "ID inválido."]);
    exit;
}
if ($status === '') {
    echo json_encode(["status" => false, "msg" => "Informe o nome da etapa."]);
    exit;
}
if ($descricao === '') {
    echo json_encode(["status" => false, "msg" => "Informe a descrição da etapa."]);
    exit;
}
if (!in_array($cor_frente, ['text-light', 'text-dark'], true)) {
    $cor_frente = 'text-light';
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor_fundo)) {
    $cor_fundo = '#6c757d';
}

$sql = "UPDATE rs_vagas_fluxo
        SET status = :status, descricao = :descricao, ativo = :ativo,
            cor_frente = :cor_frente, cor_fundo = :cor_fundo
        WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
$stmt->bindParam(':cor_frente', $cor_frente, PDO::PARAM_STR);
$stmt->bindParam(':cor_fundo', $cor_fundo, PDO::PARAM_STR);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Etapa atualizada com sucesso."]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao atualizar etapa."]);
}
exit;
