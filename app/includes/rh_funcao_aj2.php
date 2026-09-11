<?php
//
//- rh_funcao_aj2.php | Salva Registro do Funcao
//- (C)haia, 21/03/2025
//

session_start();

$idModulo = 6;// Funcaos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO"];
die(json_encode($response));

 rh_funcao_aj2.php | 2025-03-21 16:49:54 
{
    "_nome": "faxineiro",
    "_nivel": "1",
    "_descricao": "<p>faxinando dia e noite<\/p>"
}
*/

extract($parametros);

// Validação básica
if (!isset($_nome, $_descricao )) {
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

$sql = "INSERT INTO rh_funcoes (idEmpresa, nome, descricao, idLogin) VALUES ( :idEmpresa, :nome, :descricao, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
$stmt->bindParam(':nome',      $_nome, PDO::PARAM_STR);
$stmt->bindParam(':descricao', $_descricao, PDO::PARAM_STR);
$stmt->bindParam(':idLogin',   $idLogin, PDO::PARAM_INT);

// Executa a inserção
if ($stmt->execute()) {
    $idFuncao = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idFuncao</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

$dados = implode(", ", $parametros);
f_log("INC", "INCLUSÃO de Funcao no Sistema: Dados( $dados )", "rh_funcoes", $idModulo, $idFuncao);

$conn = null;
die(json_encode($response));