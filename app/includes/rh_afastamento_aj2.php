<?php
//
//- rh_afastamento_aj2.php | Recupera dados AFASTAMENTO para Edição/Visualização
//- (C)haia, 04/08/2025
//

session_start();

$idModulo = 17; // Afastamentos

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
    $sql = "SELECT A.*,  P.nome, T.descricao, U.login, C.idPessoa
                FROM RH.rh_afastamentos A
                INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_afastamento_tipos T on T.id = A.idTipo
                LEFT OUTER JOIN rh_usuarios U on U.idUsuario = A.idUsuario
                WHERE A.id = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    if( $linha ){
        $dados = implode(", ", $linha);
        f_log("VIS", "VISUALIZAÇÃO de Afastamento: Dados:( $dados )", "rh_afastamentos", $idModulo, $id);
        extract( $linha );
    } 
    else die("erro processando link do arquivo para visualização");

    $arquivoUrl = rawurlencode($arquivo);
    $link = " <a href='docs_view.php?pessoa=$idPessoa&arquivo=$arquivoUrl' target='_blank'><i class='fa-solid fa-magnifying-glass'></i></a>";
    
    $linha['arquivo_link'] = $arquivo . $link;

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );