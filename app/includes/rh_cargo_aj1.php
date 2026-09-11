<?php
//
//- rh_cargo_aj1.php | Recupera dados CARGO para Edição/Visualização
//- (C)haia, 21/03/2025
//

session_start();

$idModulo = 5; // Cargos

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
    $sql = "SELECT * FROM rh_cargos
                WHERE idCargo = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);

if( isset($origem) && $origem == "visualizar" ){
    $dados = implode(", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Cargo: Dados:( $dados )", "rh_cargos", $idModulo, $id);
}

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );