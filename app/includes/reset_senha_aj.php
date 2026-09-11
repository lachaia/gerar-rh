<?php
include "conexao_gerar.php";

$token = filter_input(INPUT_POST, 'token');
$senha = filter_input(INPUT_POST, 'senha');

if (!$token || !ctype_xdigit($token) || strlen($token) !== 64 || !$senha) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos!"]);
    exit;
}

// Valida o token: existe, é do tipo "reset de senha", não expirou (60 min) e não foi usado.
// O idUsuario vem do registro do token, NUNCA do POST do cliente.
$sql = "SELECT idToken, idUsuario FROM rh_token
        WHERE token = :token AND tipo = 1 AND data_reset IS NULL
        AND data_solicitacao >= (NOW() - INTERVAL 60 MINUTE)
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':token', $token, PDO::PARAM_STR);
$stmt->execute();
$linhaToken = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$linhaToken) {
    echo json_encode(["status" => false, "msg" => "Token inválido, expirado ou já utilizado!"]);
    exit;
}

$idUsuario = $linhaToken['idUsuario'];
$idToken   = $linhaToken['idToken'];

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$agora = date("Y-m-d H:i:s");

$sql = "UPDATE rh_usuarios SET senha = :senha WHERE idUsuario = :idUsuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":senha", $senhaHash, PDO::PARAM_STR);
$stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_INT);

if ($stmt->execute()) {
    // Marca o token como usado (impede reaproveitar o mesmo link)
    $sqlToken = "UPDATE rh_token SET data_reset = :agora WHERE idToken = :idToken";
    $stmtToken = $conn->prepare($sqlToken);
    $stmtToken->bindParam(':agora', $agora, PDO::PARAM_STR);
    $stmtToken->bindParam(':idToken', $idToken, PDO::PARAM_INT);
    $stmtToken->execute();

    echo json_encode(["status" => true, "msg" => "Senha redefinida com sucesso!"]);
} else {
    echo json_encode(["status" => false, "msg" => "Erro ao atualizar senha!"]);
}

// Fechar a conexão com o banco de dados
$conn = null;

