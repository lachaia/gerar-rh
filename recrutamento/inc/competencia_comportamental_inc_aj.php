<?php
//
//- competencia_comportamental_inc_aj.php | Inclui novo registro em rs_competencias_comportamentais
//- (C)haia, 24/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$descricao = trim((string) filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW));
$ativo = filter_input(INPUT_POST, 'ativo', FILTER_VALIDATE_INT) ? 1 : 0;

if ($descricao === '') {
    echo json_encode(["status" => false, "msg" => "Informe a descrição da competência."]);
    exit;
}

$sql = "INSERT INTO rs_competencias_comportamentais (descricao, ativo, login_id) VALUES (:descricao, :ativo, :login_id)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
$stmt->bindParam(':login_id', $_SESSION['idLogin'], PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Competência incluída com sucesso.", "id" => $conn->lastInsertId()]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao incluir competência."]);
}
exit;
