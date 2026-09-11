<?php
//
//- rh_pessoa_cv_aj1.php | Recupera dados CURRÍCULO: Formação Acadêmica para Edição/Visualização
//- (C)haia, 24/03/2025
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
    $sql = "SELECT C.id, N.nivel, C.curso, C.ano_conclusao, I.sigla, P.nome, I.nome as nmInstituicao,
		        DATE_FORMAT(L.dtLogin, '%d/%m/%Y %H:%i') AS dtLogin, U.login
                FROM rh_pessoa_cvfa C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_fa_niveis N on N.idNivel = C.idNivel
                INNER JOIN rh_fa_instituicoes I on I.idInstituicao = C.idInstituicao
                INNER JOIN rh_logins L on L.idLogin = C.idLogin
                INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                WHERE C.id = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    $nome = $linha['nome'];

if( isset($origem) && $origem == "visualizar" ){
    $dados = implode(", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Formação Acadêmica de $nome: Dados:( $dados )", "rh_pessoa_cvfa", $idModulo, $id);
}

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );