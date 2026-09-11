<?php
//
//- superintendente_inc_aj.php | Inclui novo registro em rs_superintendentes
//- (C)haia, 24/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$identificador = trim((string) filter_input(INPUT_POST, 'identificador', FILTER_UNSAFE_RAW));
$email = trim((string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$ativo = filter_input(INPUT_POST, 'ativo', FILTER_VALIDATE_INT) ? 1 : 0;

if ($identificador === '') {
    echo json_encode(["status" => false, "msg" => "Informe o nome do superintendente."]);
    exit;
}
if ($email === '') {
    echo json_encode(["status" => false, "msg" => "Informe um e-mail válido."]);
    exit;
}

$sql = "INSERT INTO rs_superintendentes (identificador, email, ativo, login_id)
        VALUES (:identificador, :email, :ativo, :login_id)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
$stmt->bindParam(':login_id', $_SESSION['idLogin'], PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Superintendente incluído com sucesso.", "id" => $conn->lastInsertId()]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao incluir superintendente."]);
}
exit;
