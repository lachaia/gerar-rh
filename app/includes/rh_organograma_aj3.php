<?php
//
//- rh_organograma_aj3.php | Exclui Órgão no Organograma
//- (C)haia, 24/02/2025
//

session_start();

$idModulo = 3; // Organograma

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

if( empty( $id )){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

if( isset($_SESSION['idLogin']) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}

//- Recupera dados do Órgão
//
    $sql = "SELECT * FROM rh_organograma WHERE idOrgao = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    $dados_orgao = implode(", ", $linha);


f_log("EXC", "Exclusão de Órgão, ID $id. Dados: ($dados_orgao)", "rh_organograma", $idModulo, $id);

$sql = "DELETE FROM rh_organograma WHERE idOrgao = $id";
$consulta = $conn->prepare($sql);

if( $consulta->execute() ){
    $resposta = [ 'msg' => '<div class="alert alert-success">
                <strong>Successo!</strong> Órgão Excluído com sucesso!
                </div>',
                'status' => true ];
} else{
    $resposta = [ 'msg' => '<div class="alert alert-danger">
                <strong>Erro!</strong> Falha ao Excluir Órgão!
                </div>',
                'status' => true ];
}

$conn = null;
die( json_encode($resposta, JSON_PRETTY_PRINT) );