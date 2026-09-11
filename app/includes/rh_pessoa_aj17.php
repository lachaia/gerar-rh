<?php
//
//- rh_pessoa_aj17.php | Exclui PESSOA na tabela
//- (C)haia, 20/03/2025
//

session_start();

$idModulo = 2; // rh_pessoas

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
    $sql = "SELECT * FROM rh_pessoas WHERE idPessoa = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    $dados_pessoa = implode(", ", $linha);


f_log("EXC", "Exclusão de PESSOA, ID $id. Dados: ($dados_pessoa)", "rh_pessoas", $idModulo, $id);

$sql = "DELETE FROM rh_pessoas WHERE idPessoa = $id";
$consulta = $conn->prepare($sql);

if( $consulta->execute() ){
    $resposta = [ 'msg' => '<div class="alert alert-success">
                <strong>Successo!</strong> PESSOA Excluída com sucesso!
                </div>',
                'status' => true ];
} else{
    $resposta = [ 'msg' => '<div class="alert alert-danger">
                <strong>Erro!</strong> Falha ao Excluir PESSOA!
                </div>',
                'status' => true ];
}

$conn = null;
die( json_encode($resposta, JSON_PRETTY_PRINT) );