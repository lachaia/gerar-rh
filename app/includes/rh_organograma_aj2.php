<?php
//
//- rh_organograma_aj2.php | Insere Novo Órgão no Organograma
//- (C)haia, 24/02/2025
//

session_start();

$idModulo = 3; // Organograma

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)){
    extract( $parametros );
} else{
    $resposta = '<div class="alert alert-danger">
                <strong>Erro!</strong> Faltou parâmetros!
                </div>';
    die( json_encode($resposta) );
}

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}
/*
include "../includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
die(); rh_organograma_aj2.php | 2025-07-29 10:22:05 
{
    "_nome": "TI-Operacional",
    "_nivel": "7",
    "idSupervisor": "11",
    "_staff": "0",
    "_estrategico": "0",
    "_nivel1": "1",
    "_nivel2": "1",
    "_nivel3": "6",
    "_nivel4": "1",
    "_nivel5": "1",
    "_nivel6": "1",
    "_nivel7": "1"
}
*/

$sql = "INSERT INTO rh_organograma (descricao, nivel, idSupervisor, staff, estrategico, nivel_1, nivel_2, nivel_3, nivel_4, nivel_5, nivel_6, nivel_7, idLogin) 
        VALUES (:descricao, :nivel, :idSupervisor, :staff, :estrategico, :nivel1, :nivel2, :nivel3, :nivel4, :nivel5, :nivel6, :nivel7, :idLogin)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':descricao', $_nome );
$consulta->bindParam(':nivel', $_nivel );
$consulta->bindParam(':idSupervisor', $idSupervisor );
$consulta->bindParam(':staff', $_staff );
$consulta->bindParam(':estrategico', $_estrategico );
$consulta->bindParam(':nivel1', $_nivel1 );
$consulta->bindParam(':nivel2', $_nivel2 );
$consulta->bindParam(':nivel3', $_nivel3 );
$consulta->bindParam(':nivel4', $_nivel4 );
$consulta->bindParam(':nivel5', $_nivel5 );
$consulta->bindParam(':nivel6', $_nivel6 );
$consulta->bindParam(':nivel7', $_nivel7 );
$consulta->bindParam(':idLogin', $idLogin );

if( $consulta->execute() ){
    $resposta = '<div class="alert alert-success">
                <strong>Successo!</strong> Órgão inserido com sucesso!
                </div>';
    $idOrgao = $conn->lastInsertId();
    $dados = implode(", ", $parametros);
    f_log("INC", "INCLUSÃO de Órgão no Organograma: Dados( $dados )", "rh_organograma", $idModulo, $idOrgao);
    //
} else{
    $resposta = '<div class="alert alert-danger">
                <strong>Erro!</strong> Falha ao inserir Órgão!
                </div>';
}

$conn = null;
die( $resposta );