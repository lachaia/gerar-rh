<?php
//
//- rh_ficha_pessoa_aj6.php | DELETA Ação na Linha do Tempo
//- (C)haia, 20/03/2025
//

session_start();

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE OK!"];
die(json_encode($response));
*/

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idUsuario = $_SESSION['idUsuario'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    //
} else {
    header("Location: ../logout.php");
}

// Validação básica
if (!isset($idAcao)) {
    $response = ["status" => false, "msg" => "Todos os campos são obrigatórios.!"];
    die(json_encode($response));
}

$sql = "DELETE FROM rh_pessoas_ldt WHERE idAcao = :idAcao";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':idAcao', $idAcao, PDO::PARAM_INT);

// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Atualizar Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>ERRO!</strong> ao Atualizar Registro</div>";
    $response = ["status" => false, "msg" => $msg];
}

$conn = null;
die(json_encode($response));