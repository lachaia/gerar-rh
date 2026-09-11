<?php
//
//- rh_organograma_aj4.php | Recupera dados Órgão para Edição
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
    $sql = "SELECT O.*,
                    (select descricao from rh_organograma where idOrgao = O.idSupervisor) as dsSupervisor,
                    U.login as usuario,  DATE_FORMAT(L.dtLogin, '%d/%m/%Y %H:%i') AS data
                FROM rh_organograma O
                INNER JOIN rh_logins L on L.idLogin = O.idLogin
                INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                WHERE O.idOrgao = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    
$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );