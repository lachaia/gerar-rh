<?php
//
//- rh_termo_aj2.php | Recupera dados do TERMO para Edição/Visualização
//- (C)haia, 08/08/2025
//

session_start();

$idModulo = 18; // Termos Gerais

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

//- Recupera dados do Termo
//
    $sql = "SELECT A.*,  P.nome, T.descricao, C.idPessoa
                FROM RH.rh_termos A
                INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_termos_tipos T on T.id = A.idTipoTermo
                WHERE A.id = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    
    if( $linha ){
        $dados = implode(", ", $linha);
        f_log("VIS", "VISUALIZAÇÃO de Termo: Dados:( $dados )", "rh_termos", $idModulo, $id);
        extract( $linha );
    } 
    else die("erro processando link do arquivo para visualização");

    if( isset($origem) && ! empty($origem) ){
        $link = " <a href='../docs/pessoa_$idPessoa/$arquivo' target='_blank'><i class='fa-solid fa-magnifying-glass'></i></a>";
    }else{
        $link = " <a href='./docs/pessoa_$idPessoa/$arquivo' target='_blank'><i class='fa-solid fa-magnifying-glass'></i></a>";
    }

    
    $linha['arquivo_link'] = $arquivo . $link;

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );