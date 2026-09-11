<?php
//
//- competencia_comportamental_alt_aj.php | Altera registro existente em rs_competencias_comportamentais
//- (C)haia, 24/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$descricao = trim((string) filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW));
$ativo = filter_input(INPUT_POST, 'ativo', FILTER_VALIDATE_INT) ? 1 : 0;

if (!$id) {
    echo json_encode(["status" => false, "msg" => "ID inválido."]);
    exit;
}
if ($descricao === '') {
    echo json_encode(["status" => false, "msg" => "Informe a descrição da competência."]);
    exit;
}

$sql = "UPDATE rs_competencias_comportamentais SET descricao = :descricao, ativo = :ativo WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Competência atualizada com sucesso."]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao atualizar competência."]);
}
exit;
