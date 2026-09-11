<?php
//
//- rh_cargo_aj4.php | Salva Registro do Cargo da EDIÇÃO
//- (C)haia, 21/03/2025
//

session_start();

$idModulo = 5; // Cargos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$msg = "<div class='alert alert-primary'><strong>OK: </strong> TESTE REALIZADO COM SUCESSO!</div>";
$response = ["status" => true, "msg" => $msg ]; 
die(json_encode($response));

 rh_cargo_aj4.php | 2025-03-21 20:16:35 
{
    "e_idCargo": "1",
    "e_nome": "Analista Administrativo I",
    "e_nivel": "1",
    "e_ativo": "1",
    "e_descricao": "Realiza tarefas administrativas b\u00e1sicas,  como organiza\u00e7\u00e3o de documentos,  atendimento telef\u00f4nico e apoio geral \u00e0s opera\u00e7\u00f5es do departamento."
}
*/

extract($parametros);

// Validação básica
if (!isset($e_nome, $e_descricao, $e_nivel, $e_idCargo )) {
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

$sql = "SELECT * FROM rh_cargos WHERE idCargo = :id";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $e_idCargo);
$consulta->execute();
$dados = $consulta->fetch(PDO::FETCH_ASSOC);
$old_dados = implode(", ", $dados);

$sql = "UPDATE rh_cargos SET nome = :nome, descricao = :descricao, nivel = :nivel WHERE idCargo = :idCargo";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idCargo',   $e_idCargo, PDO::PARAM_INT);
$stmt->bindParam(':nome',      $e_nome, PDO::PARAM_STR);
$stmt->bindParam(':nivel',     $e_nivel, PDO::PARAM_INT);
$stmt->bindParam(':descricao', $e_descricao, PDO::PARAM_STR);

// Executa a inserção
if ($stmt->execute()) {
    $idCargo = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao atualizar o registro. ID: $idCargo</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao atualizar registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

$dados = implode(", ", $parametros);
f_log("ALT", "ALTERAÇÃO de Cargo no Sistema: Dados anteriores ($old_dados) |  Dados Novos( $dados )", "rh_cargos", $idModulo, $idCargo);

$conn = null;
die(json_encode($response));