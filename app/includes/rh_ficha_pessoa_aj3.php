<?php
//
//- rh_ficha_pessoa_aj3.php | Salva Ação na Linha do Tempo
//- (C)haia, 19/03/2025
//

session_start();

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
rh_ficha_pessoa_aj3.php | 2025-03-20 08:03:55 
{
    "idTipoAcao": "5",
    "data": "2025-03-20T08:03",
    "descricao": "<p>asdfasdfasdfasdf<\/p>",
    "idEmpresa": "1",
    "idPessoa": "57"
}
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
if (!isset($idTipoAcao, $idPessoa, $idEmpresa, $data, $descricao)) {
    $response = ["status" => false, "msg" => "Todos os campos são obrigatórios.!"];
    die(json_encode($response));
}

extract($parametros);
$sql = "INSERT INTO rh_pessoas_ldt (idAcaoTipo, idPessoa, idEmpresa, data, descricao, idLogin, idUsuario) 
            VALUES (:idAcaoTipo, :idPessoa, :idEmpresa, :data, :descricao, :idLogin, :idUsuario)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idAcaoTipo', $idTipoAcao, PDO::PARAM_INT);
$stmt->bindParam(':idPessoa',   $idPessoa,   PDO::PARAM_INT);
$stmt->bindParam(':idEmpresa',  $idEmpresa,  PDO::PARAM_INT);
$stmt->bindParam(':data',       $data,       PDO::PARAM_STR);
$stmt->bindParam(':descricao',  $descricao,  PDO::PARAM_STR);
$stmt->bindParam(':idLogin',    $idLogin,    PDO::PARAM_INT);
$stmt->bindParam(':idUsuario',  $idUsuario,  PDO::PARAM_INT);

// Executa a inserção
if ($stmt->execute()) {
    $idInserido = $conn->lastInsertId();
    $response = ["status" => true, "msg" => "Registro inserido com sucesso!", "id" => $idInserido];
} else {
    $response = ["status" => false, "msg" => "Erro ao inserir registro!"];
}

$conn = null;
die(json_encode($response));