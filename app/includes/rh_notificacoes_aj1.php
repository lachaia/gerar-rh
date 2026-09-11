<?php
// marcar_notificacao_lida.php
session_start();
include "conexao_gerar.php";

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID inválido']);
    exit;
}

$id = (int)$_POST['id'];
$idUsuario = $_SESSION['idUsuario'];

$sql = "UPDATE rh_notificacoes SET lido_em = NOW() WHERE id = :id AND idUsuario = :idUsuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(['sucesso' => true]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao atualizar']);
}
