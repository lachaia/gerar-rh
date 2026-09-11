<?php
//
//- rh_pessoa_cv_aj2.php | CURRÍCULO | Salva nova IE 
//- (C)haia, 25/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
echo json_encode( $retorno );

 rh_pessoa_cv_aj2.php | 2025-03-25 09:54:41 
{
    "_nomeInstituicao": "UNIVERSIDADE CAMPO REAL",
    "_sigla": "UNICAMPO",
    "_cidade": "GUARAPUAVA",
    "uf": "PR",
    "_pais": "Brasil"
}
*/

if( ! isset($_nomeInstituicao) || empty( $_nomeInstituicao )){
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

$sql = "INSERT INTO rh_fa_instituicoes (nome, sigla, cidade, uf, pais, idLogin) 
    VALUES (:nome, :sigla, :cidade, :uf, :pais, :idLogin)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':nome', $_nomeInstituicao );
$consulta->bindParam(':sigla', $_sigla );
$consulta->bindParam(':cidade', $_cidade );
$consulta->bindParam(':uf', $uf );
$consulta->bindParam(':pais', $_pais );
$consulta->bindParam(':idLogin', $idLogin );

// Executa a inserção
if ($consulta->execute()) {
    $idInstituicao = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idInstituicao</div>";
    //
    $sql = "SELECT * FROM rh_fa_instituicoes WHERE idInstituicao = $idInstituicao";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados = implode(", ", $linha);
    //
    $response = ["status" => true, "msg" => $msg, "dados"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("INC", "INCLUSÃO de IE no Sistema: Dados( $dados )", "rh_cargos", $idModulo, $idInstituicao);

$conn = null;
die(json_encode($response));