<?php
//
//- rh_cv_idi_aj1.php | Recupera dados CURRÍCULO: IDIOMA para Edição/Visualização
//- (C)haia, 28/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM

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
    $sql = "SELECT I.*, P.nome, A.nome as nmIdioma, F.nome as nmFluencia, 
                    DATE_FORMAT(L.dtLogin, '%d/%m/%Y %H:%i') AS dtLogin, U.login
                FROM rh_cv_idiomas I
                INNER JOIN rh_pessoas P on P.idPessoa = I.idPessoa
                INNER JOIN rh_idiomas A on A.idIdioma = I.idIdioma
                INNER JOIN rh_fluencias F on F.idFluencia = I.idFluencia
                INNER JOIN rh_logins L on L.idLogin = I.idLogin
                INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                WHERE I.id = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    $nome = $linha['nome'];

if( isset($origem) && $origem == "visualizar" ){
    $dados = implode(", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de CV (Idiomas) de $nome: Dados:( $dados )", "rh_cv_idiomas", $idModulo, $id);
}

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );