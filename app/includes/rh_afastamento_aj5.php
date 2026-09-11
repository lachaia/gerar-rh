<?php
//
//- rh_afastamento_aj5.php | APROVA/REJEITA Documento de Afastamento
//- (C)haia, 13/08/2025
//

session_start();

$idModulo = 17; // Afastamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT)  );
*/

// Validação básica
if (!isset($id) || !isset($status) ) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Parâmetros!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $nmLogin = $_SESSION['nmLogin'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$idStatus = "";
if( $status == 'reprovado') $dsStatus = "Rejeitado";
if( $status == 'aprovado')  $dsStatus = "Aprovado";

$agora = date("Y-m-d H:i:s");

$sql = "UPDATE rh_afastamentos 
        SET status = :dsStatus, rh_por = :nmLogin, rh_em = :agora 
        WHERE id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'dsStatus' => $dsStatus,
    'nmLogin' => $nmLogin,
    'agora' => $agora,
    'id' => $id
]);
 
// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Atualizar o Status</div>";
    $response = ["status" => true, "msg" => $msg ];
}else{
    $msg = "<div class='alert alert-danger'><strong>ERRO!</strong> ao Atualizar o Status</div>";
    $response = ["status" => false, "msg" => $msg ];    
}

f_log("EXC", "$status pelo RH de Atestado de Afastamento, ID: $id", "rh_afastamentos", $idModulo, $id);

$conn = null;
die(json_encode($response));