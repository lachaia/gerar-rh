<?php
//- rh_notificacoes_aj3.php

session_start();

include "conexao_gerar.php";

// Verifica se os dados esperados estão presentes
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['usuario'], $data['evento'], $data['acao'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$idUsuario = intval($data['usuario']);
$idEvento  = intval($data['evento']);
$acao      = $data['acao'];
$idLogin   = isset($_SESSION['idLogin']) ? intval($_SESSION['idLogin']) : null;

if (!$idLogin) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão inválida']);
    exit;
}

if ($acao === 'adicionar') {
    $stmt = $conn->prepare("
        INSERT INTO rh_notificacoes_usuarios (idEvento, idUsuario, idLogin)
        SELECT ?, ?, ?
        WHERE NOT EXISTS (
            SELECT 1 FROM rh_notificacoes_usuarios
            WHERE idEvento = ? AND idUsuario = ?
        )
    ");
    $sucesso = $stmt->execute([$idEvento, $idUsuario, $idLogin, $idEvento, $idUsuario]);

} elseif ($acao === 'remover') {
    $stmt = $conn->prepare("DELETE FROM rh_notificacoes_usuarios WHERE idEvento = ? AND idUsuario = ?");
    $sucesso = $stmt->execute([$idEvento, $idUsuario]);

} else {
    $sucesso = false;
}

echo json_encode(['sucesso' => $sucesso]);

