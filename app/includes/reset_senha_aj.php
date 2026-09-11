<?php
include "conexao_gerar.php";

$idUsuario = filter_input(INPUT_POST, 'idUsuario', FILTER_SANITIZE_NUMBER_INT);
$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_NUMBER_INT);
$senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

if (!$idUsuario || !$senha) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos!"]);
    exit;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$agora = date("Y-m-d H:i:s");

$sql = "UPDATE rh_usuarios SET senha = :senha WHERE idUsuario = :idUsuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":senha", $senhaHash, PDO::PARAM_STR);
$stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Senha redefinida com sucesso!"]);
} else {
    echo json_encode(["status" => false, "msg" => "Erro ao atualizar senha!"]);
}

// Supondo que $agora e $token já estejam corretamente definidos e validados

$sql = "UPDATE rh_token SET data_reset = :agora WHERE idToken = :token";
$stmt = $conn->prepare($sql);

// Usando bindParam para evitar SQL Injection
$stmt->bindParam(':agora', $agora, PDO::PARAM_STR);
$stmt->bindParam(':token', $token, PDO::PARAM_INT);

// Executa a consulta
$stmt->execute();

// Fechar a conexão com o banco de dados
$conn = null;

