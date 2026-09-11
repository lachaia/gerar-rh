<?php
//
//- rh_saude_aj5.php | Salva Registro do tipo de documento
//- (C)haia, 29/04/2025
//

session_start();

$idModulo = 8; // Saúde Ocupacional

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "<div class='alert alert-primary'><strong>OK: </strong> Teste Realizado com Sucesso!</div>"];
die(json_encode($response));

rh_saude_aj5.php | 2025-04-29 09:46:43 
{
    "nmTipoDoc": "Exames de Diagn\u00f3stico",
    "validade": "6"
}
*/
// Validação básica
if (!isset( $nmTipoDoc, $validade )) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Todos os Campos são necessários!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "INSERT INTO rh_docs_tipo (nome, validade, ativo) VALUES (:nome, :validade, 1)";
$stmt = $conn->prepare($sql); 
$stmt->bindParam(':nome', $nmTipoDoc, PDO::PARAM_STR);
$stmt->bindParam(':validade', $validade, PDO::PARAM_INT);
$stmt->execute();
$novoId = $conn->lastInsertId(); // Obter o ID do último registro inserido

echo json_encode([
    'msg' => '<div class="alert alert-success"><strong>Successo!</strong>Salvo com sucesso!</div>',
    'id' => $novoId,       // ID do tipo recém-inserido
    'nome' => $nmTipoDoc   // Nome do tipo recém-inserido
]);

