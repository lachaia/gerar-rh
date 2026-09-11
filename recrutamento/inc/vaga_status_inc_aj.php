<?php
//
//- vaga_status_inc_aj.php | Inclui novo registro em rs_vagas_status
//- (C)haia, 24/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$status = strtoupper(trim((string) filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW)));
$descricao = trim((string) filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW));
$cor_frente = trim((string) filter_input(INPUT_POST, 'cor_frente', FILTER_UNSAFE_RAW));
$cor_fundo = trim((string) filter_input(INPUT_POST, 'cor_fundo', FILTER_UNSAFE_RAW));
$ativo = filter_input(INPUT_POST, 'ativo', FILTER_VALIDATE_INT) ? 1 : 0;

if ($status === '') {
    echo json_encode(["status" => false, "msg" => "Informe o nome do status."]);
    exit;
}
if ($descricao === '') {
    echo json_encode(["status" => false, "msg" => "Informe a descrição do status."]);
    exit;
}
if (!in_array($cor_frente, ['text-light', 'text-dark'], true)) {
    $cor_frente = 'text-light';
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor_fundo)) {
    $cor_fundo = '#6c757d';
}

$sql = "INSERT INTO rs_vagas_status (status, descricao, ativo, cor_frente, cor_fundo)
        VALUES (:status, :descricao, :ativo, :cor_frente, :cor_fundo)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
$stmt->bindParam(':cor_frente', $cor_frente, PDO::PARAM_STR);
$stmt->bindParam(':cor_fundo', $cor_fundo, PDO::PARAM_STR);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Status incluído com sucesso.", "id" => $conn->lastInsertId()]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao incluir status."]);
}
exit;
