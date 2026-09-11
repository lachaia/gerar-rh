<?php
//
//- rh_cv_af_aj1.php | Recupera dados CURRÍCULO: Formação Acadêmica para Edição/Visualização
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
    $sql = "SELECT C.idPessoa, C.id, N.nivel, C.curso, C.ano_conclusao, I.sigla, P.nome, I.nome as nmInstituicao,
		        DATE_FORMAT(L.dtLogin, '%d/%m/%Y %H:%i') AS dtLogin, U.login, I.idInstituicao,
                N.idNivel, ifnull(D.arquivo,'') as arquivo
                FROM rh_cv_fa C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_fa_niveis N on N.idNivel = C.idNivel
                INNER JOIN rh_fa_instituicoes I on I.idInstituicao = C.idInstituicao
                INNER JOIN rh_logins L on L.idLogin = C.idLogin
                INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                LEFT OUTER JOIN rh_documentos D on D.idDoc = C.idDoc
                WHERE C.id = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id );
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    $nome = $linha['nome'];

//- monta a URL para mostrar o Certificado
//
    $idPessoa = $linha['idPessoa'];
    $arquivo = $linha['arquivo'];
    if( ! empty($arquivo) ) $url = "/rh/docs/pessoa_$idPessoa/$arquivo"; else $url = "";

    $linha['url'] = $url;

if( isset($origem) && $origem == "visualizar" ){
    $dados = implode(", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Formação Acadêmica de $nome: Dados:( $dados )", "rh_cv_fa", $idModulo, $id);
}

$conn = null;
die( json_encode($linha, JSON_PRETTY_PRINT) );