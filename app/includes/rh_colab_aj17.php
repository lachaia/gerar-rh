<?php
//
//- rh_colab_aj17.php | EXCLUIR DEPENDENTE
//- (C)haia, 15/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}
/*
// TESTE DE RECEBIMENTO DE DADOS
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
*/

if ( empty($idDependente) ) {
    $mensagem = 'Erro! Faltou parâmetros! ';
    die( $mensagem );
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

$sql = "DELETE FROM rh_dependentes WHERE idDependente = :idDependente";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idDependente', $idDependente, PDO::PARAM_INT);
if( $stmt->execute() ){
    $retorno = [
        $mensagem = "Sucesso! Dependente excluído com sucesso!."
    ];
} else {
    $retorno = [
        $mensage = "Erro! Não foi possível excluir o dependente!"
    ];      
}

$conn = null; // Fecha a conexão com o banco de dados
die( $mensagem );