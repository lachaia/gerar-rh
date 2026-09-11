<?php
//
//- rh_saude_aj1.php | Recupera dados EXAMES para Edição/Visualização
//- (C)haia, 23/04/2025
//

session_start();

$idModulo = 8; // SAÚDE

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
    $sql = "SELECT E.*, T.nmExame as dsExame, P.idPessoa, P.nome, D.arquivo
                FROM rh_exames E
                INNER JOIN rh_exames_tipos T on T.idExameTipo = E.idExameTipo
                INNER JOIN rh_colaboradores C on C.idColab = E.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                LEFT OUTER JOIN rh_documentos D ON D.idDoc = E.idDoc
                WHERE idExame = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    extract( $linha );

if( isset($origem) && $origem == "visualizar" ){
    $dados = implode(", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Exame: $dsExame de $nome: Dados:( $dados )", "rh_exames", $idModulo, $id);
}

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );