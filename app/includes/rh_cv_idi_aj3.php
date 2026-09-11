<?php
//
//- rh_cv_exp_aj3.php | CURRÍCULO | Salva nova Habilidade - IDIOMA 
//- (C)haia, 26/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

/*
// TESTE DE RECEBIMENTO DE DADOS
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
/*
 rh_cv_idi_aj3.php | 2025-03-28 16:11:03 
{
    "idPessoa": "1",
    "idIdioma": "19",
    "idFluencia": "1"
}

*/
if( empty( $idPessoa ) || empty( $idIdioma ) || empty( $idFluencia ) ){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

if( isset($_SESSION['idLogin']) ){
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}

$sql = "INSERT INTO rh_cv_idiomas ( idEmpresa, idPessoa, idIdioma, idFluencia, idLogin) 
            VALUES ( :idEmpresa, :idPessoa, :idIdioma, :idFluencia, :idLogin)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idEmpresa', $idEmpresa );
$consulta->bindParam(':idPessoa', $idPessoa );
$consulta->bindParam(':idIdioma', $idIdioma );
$consulta->bindParam(':idFluencia', $idFluencia );
$consulta->bindParam(':idLogin', $idLogin );

// Executa a inserção
if ($consulta->execute()) {
    $idIdi = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idIdi</div>";
    //
    $sql = "SELECT * FROM rh_cv_idiomas WHERE id = $idIdi";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados = implode(", ", $linha);
    //
    $response = ["status" => true, "msg" => $msg, "dados"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("INC", "INCLUSÃO no CV de Idioma: Dados( $dados )", "rh_cv_idiomas", $idModulo, $idIdi);

$conn = null;
die(json_encode($response));