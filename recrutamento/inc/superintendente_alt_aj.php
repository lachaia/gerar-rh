<?php
//
//- superintendente_alt_aj.php | Altera registro existente em rs_superintendentes
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
$identificador = trim((string) filter_input(INPUT_POST, 'identificador', FILTER_UNSAFE_RAW));
$email = trim((string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$ativo = filter_input(INPUT_POST, 'ativo', FILTER_VALIDATE_INT) ? 1 : 0;

if (!$id) {
    echo json_encode(["status" => false, "msg" => "ID inválido."]);
    exit;
}
if ($identificador === '') {
    echo json_encode(["status" => false, "msg" => "Informe o nome do superintendente."]);
    exit;
}
if ($email === '') {
    echo json_encode(["status" => false, "msg" => "Informe um e-mail válido."]);
    exit;
}

$sql = "UPDATE rs_superintendentes
        SET identificador = :identificador, email = :email, ativo = :ativo
        WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Superintendente atualizado com sucesso."]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao atualizar superintendente."]);
}
exit;
