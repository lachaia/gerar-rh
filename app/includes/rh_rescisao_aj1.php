<?php
//
//- rh_rescisao_aj1.php | Recupera dados de RESCISÃO para Edição/Visualização
//- (C)haia, 24/04/2025
//

session_start();

$idModulo = 9; // RESCISÕES

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

if( isset($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [1, 9], true) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

//- Recupera dados do Órgão
//
    $sql = "SELECT R.*, P.nome, T.descricao as dsTipoRescisao
                FROM rh_rescisoes R
                INNER JOIN rh_colaboradores C on C.idColab = R.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_rescisao_tipos T on T.idTipoRescisao = R.idTipoRescisao
                WHERE R.idRescisao = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    extract( $linha );

if( isset($origem) && $origem == "visualizar" ){
    $dados = implode(", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de RESCISÃO de $nome", "rh_rescisoes", $idModulo, $id);
}

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );