<?php
//
//- rh_cv_idi_aj5.php | CURRÍCULO | Salva ALTERAÇÃO de IDIOMA 
//- (C)haia, 28/03/2025
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
 rh_cv_idi_aj5.php | 2025-03-28 17:33:27 
{
    "idIdi": "1",
    "idIdioma": "1",
    "idFluencia": "1"
}
*/

if( empty($idIdi) || empty($idIdioma) || empty( $idFluencia ) ){
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

//
// Carrega dados antigos
$sql = "SELECT I.*, P.nome 
            FROM rh_cv_idiomas I 
            INNER JOIN rh_pessoas P on P.idPessoa = I.idPessoa
            WHERE I.id = $idIdi";
$stmt = $conn->prepare($sql);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
$dados_old = implode(", ", $linha);

$sql = "UPDATE rh_cv_idiomas SET idIdioma = :idIdioma, idFluencia = :idFluencia WHERE id = :idIdi";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idIdi', $idIdi );
$consulta->bindParam(':idIdioma', $idIdioma );
$consulta->bindParam(':idFluencia', $idFluencia );

// Executa a inserção
if ($consulta->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao alterar registro. ID: $idIdi</div>";
    //
    $dados = implode(", ", $parametros);
    //
    $response = ["status" => true, "msg" => $msg, "dados"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}
$nome = $linha['nome'];
f_log("ALT", "ALTERAÇÃO de CURRÍCULO (Idioma) de $nome, Dados Anteriores ($dados_old), Dados Novos ($dados)", "rh_cv_idiomas", $idModulo, $idIdi);

$conn = null;
die(json_encode($response));