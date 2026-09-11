<?php
//- Programa para gravar a senha alterada
//- $modulo = "rh_altsenha";  | Tabela
//- $oper, $historico, $tabela, $idModulo=0, $idOperacao=0

$idModulo = 10; // rh_altsenha.php
$idTabela = 1;  // ti_usuarios


session_start();
include_once __DIR__ . "/includes/conexao_gerar.php";
include_once __DIR__ . "/includes/f_logs.php";

$idUsuario = $_SESSION['idUsuario'];

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( ! $dados['senha1'] == $dados['senha2']){
    die('{"status":"0", "mensagem":"Senhas estão diferentes uma da outra!"}');
}

$ksenha = password_hash( $dados['senha1'], PASSWORD_DEFAULT );

$sql = "UPDATE rh_usuarios SET senha = :ksenha WHERE idUsuario = :idUsuario;";
$stmt = $conn->prepare( $sql );
$stmt->bindParam( 'idUsuario', $idUsuario, PDO::PARAM_INT );
$stmt->bindParam( 'ksenha'   , $ksenha   , PDO::PARAM_STR );
$result = $stmt->execute(); 

if ($result) {
    f_log( "ALT", "Alterou a senha de usuário ID $idUsuario", 'ti_usuarios', $idModulo, $idUsuario );
    //header('Content-Type: application/json');
    $retorno = [ "status" => true, "mensagem" => "Atualização bem sucedida" ];
    $conn = null;
    die( json_encode( $retorno ) );
} else {
    $errorInfo = $stmt->errorInfo(); // Obtém informações do erro
    $conn = null;
    $mensagem = "Sinto muito, deu erro ao atualizar! Detalhes: " . $errorInfo[2];
    $retorno = [ "status" => true, "mensagem" => $mensagem ];
    die( json_encode( $retorno ) );
}


