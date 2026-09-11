<?php
//
//- rh_organograma_aj6.php | Devolve Órgão Supervisor
//- (C)haia, 29/07/2025
//

session_start();

$idModulo = 3; // Organograma

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
} else{
    header("location: logout.php");
}

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

if( empty($idOrgao) ){
    $resposta = [
        "status" => false,
        "msg" => "Faltou parâmetros"
    ];
    die( json_encode($resposta) );
}

//
//- dados
//
    $sql = "SELECT *
                FROM rh_organograma
                WHERE idOrgao = $idOrgao";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    die( json_encode( $dados ) );
 