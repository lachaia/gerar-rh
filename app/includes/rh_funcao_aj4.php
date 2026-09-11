<?php
//
//- rh_funcao_aj4.php | Salva Registro do Funcao da EDIÇÃO
//- (C)haia, 21/03/2025
//

session_start();

$idModulo = 6;// Funcaos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$msg = "<div class='alert alert-primary'><strong>OK: </strong> TESTE REALIZADO COM SUCESSO!</div>";
$response = ["status" => true, "msg" => $msg ]; 
die(json_encode($response));

 rh_funcao_aj4.php | 2025-03-21 20:16:35 
{
    "e_idFuncao": "1",
    "e_nome": "Analista Administrativo I",
    "e_nivel": "1",
    "e_ativo": "1",
    "e_descricao": "Realiza tarefas administrativas b\u00e1sicas,  como organiza\u00e7\u00e3o de documentos,  atendimento telef\u00f4nico e apoio geral \u00e0s opera\u00e7\u00f5es do departamento."
}
*/

extract($parametros);

// Validação básica
if (!isset($e_nome, $e_descricao, $e_idFuncao )) {
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

$sql = "SELECT * FROM rh_funcoes WHERE idFuncao = :id";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $e_idFuncao);
$consulta->execute();
$dados = $consulta->fetch(PDO::FETCH_ASSOC);
$old_dados = implode(", ", $dados);

$sql = "UPDATE rh_funcoes SET nome = :nome, descricao = :descricao, ativo = :ativo WHERE idFuncao = :idFuncao";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idFuncao',  $e_idFuncao, PDO::PARAM_INT);
$stmt->bindParam(':nome',      $e_nome, PDO::PARAM_STR);
$stmt->bindParam(':descricao', $e_descricao, PDO::PARAM_STR);
$stmt->bindParam(':ativo',     $e_ativo, PDO::PARAM_STR);

// Executa a inserção
if ($stmt->execute()) {
    $idFuncao = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao atualizar o registro. ID: $idFuncao</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao atualizar registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

$dados = implode(", ", $parametros);
f_log("ALT", "ALTERAÇÃO de Funcao no Sistema: Dados anteriores ($old_dados) |  Dados Novos( $dados )", "rh_funcoes", $idModulo, $idFuncao);

$conn = null;
die(json_encode($response));