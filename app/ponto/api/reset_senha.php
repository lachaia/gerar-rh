<?php
//
//- reset_senha.php | Gera uma nova senha para o colaborador logado
// (C)haia, 28/11/2025, by chatGPT

session_start();
header('Content-Type: application/json');

include "../../includes/conexao_gerar.php";

if (!isset($_SESSION['idColab'])) {
    echo json_encode([
        "status" => "erro",
        "msg" => "Sessão expirada. Faça login novamente."
    ]);
    exit;
}

$id = $_SESSION['idColab'];
$nova = $_POST['novaSenha'] ?? '';

if (strlen($nova) < 6) {
    echo json_encode([
        "status" => "erro",
        "msg" => "A senha deve ter pelo menos 6 caracteres."
    ]);
    exit;
}

$hash = password_hash($nova, PASSWORD_DEFAULT);

$sql = "UPDATE rh_usuarios SET senha = :senha WHERE idColab = :id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(":senha", $hash);
$stmt->bindValue(":id", $id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "msg" => "Senha alterada com sucesso!"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "msg" => "Erro ao salvar a nova senha."
    ]);
}
