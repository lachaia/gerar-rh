<?php
//
//- new_aj17_div.php | RETORNA DADOS DA PESSOA - CASO EXISTA - BLOCO 1
//- (C)haia, 01/04/2025
//

session_start();

include_once "../app/includes/debug.php";

$idModulo = 7; // CV

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) {
    extract($parametros);
}

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra ler os
//- dados de diversidade/CV de qualquer pessoa só sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    $resposta = [
        'msg' => '<div class="alert alert-danger"><strong>ERRO!</strong> Sessão expirada. Verifique o CPF novamente.</div>',
        'status' => false
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

if (isset($_SESSION['idLogin'])) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
if (isset($_SESSION['idEmpresa'])) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;
//
include_once "../app/includes/conexao_gerar.php";
include_once "../app/includes/f_logs.php";

$sql = "SELECT A.*, ifnull( B.nome,'') as nmCidade, ifnull(B.uf,'') as uf 
            FROM rh_cv A
            LEFT OUTER JOIN rh_cidades B ON B.idCidade = A.idCidade 
            WHERE A.idPessoa = :idPessoa";
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
