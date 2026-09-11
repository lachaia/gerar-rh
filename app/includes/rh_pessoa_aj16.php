<?php
//
//- rh_pessoa_aj0.php | RETORNA DADOS DA PESSOA - CASO EXISTA
//- (C)haia, 05/03/2025
//

session_start();

include_once "../includes/debug.php";

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) {
    extract($parametros);
}

if( empty($idPessoa) ){
    $resposta = [
        'msg' => '<div class="alert alert-danger"><strong>ERRO!</strong> Faltou parâmetros!</div>',
        'status' => false
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

$sql = "SELECT * FROM rh_pessoas WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $retorno = [
        "status" => true,
        "msg" => '<div class="alert alert-success">
                    <strong>Sucesso!</strong> Pessoa encontrada.
                  </div>',
        "dados" => $result
    ];
} else {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
                    <strong>Erro!</strong> Nenhuma pessoa encontrada com esse CPF.
                  </div>',
        "dados" => null
    ];
}

$conn = null;
die(json_encode($retorno, JSON_PRETTY_PRINT));
