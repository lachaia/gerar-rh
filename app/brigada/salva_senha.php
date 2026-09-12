<?php
//
//- salva_senha.php | Salva Nova Senha
//- (C)haia, 12/03/2026
//

session_start();
if (!isset($_SESSION['idLogin'])) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}


$idUsuario = $_SESSION['idUsuario'];

include_once "../includes/conexao_gerar.php";

$senha = $_POST['senha'];

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$sql = "UPDATE rh_usuarios SET senha = :senha WHERE idUsuario = :idUsuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':senha', $senhaHash);
$stmt->bindParam(':idUsuario', $idUsuario);

if( $stmt->execute() ){
    $retorno = [
        "status" => true,
        "msg" => "Senha alterada com sucesso!"
    ];
} else{
    $retorno = [
        "status" => false,
        "msg" => "Erro ao alterar senha!"
    ];
}

echo json_encode($retorno);