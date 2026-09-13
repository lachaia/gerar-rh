<?php
//
//- rh_ficha_pessoa_aj5.php | Salva Ação na Linha do Tempo
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

"idTipoAcao": "16",
"xdata": "2025-03-20T08:15",
"xdescricao": "<p>agora \u00e9 VIP<\/p>",
"idAcao": "39",
"idPessoa": "57"
*/

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idUsuario = $_SESSION['idUsuario'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    //
} else {
    header("Location: ../logout.php");
    exit(); // faltava — sem isso a execução continuava mesmo sem sessão válida
}

// Validação básica
if (!isset($idAcao, $idTipoAcao, $idPessoa, $xdata, $xdescricao)) {
    $response = ["status" => false, "msg" => "Todos os campos são obrigatórios.!"];
    die(json_encode($response));
}

extract($parametros);
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idLogin = $_SESSION['idLogin'];
$idUsuario = $_SESSION['idUsuario'];
$sql = "UPDATE rh_pessoas_ldt SET idAcaoTipo = :idAcaoTipo, data = :data, descricao = :descricao
            WHERE idAcao = :idAcao";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idAcaoTipo', $idTipoAcao,  PDO::PARAM_INT);
$stmt->bindParam(':data',       $xdata,       PDO::PARAM_STR);
$stmt->bindParam(':descricao',  $xdescricao,  PDO::PARAM_STR);
$stmt->bindParam(':idAcao',     $idAcao,      PDO::PARAM_INT);

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