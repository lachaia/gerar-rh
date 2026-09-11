<?php
//
//- rh_index_aj8.php | Recupera dados da PESSOA + Endereço
//- (C)haia, 25/08/2025
//

session_start();

$idModulo = 19; // Equipamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

if (empty($id)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

$sql = "SELECT  P.*,
                e.logradouro, e.complemento, e.numero, e.bairro, e.cidade, e.uf, e.cep
            FROM rh_pessoas P
            LEFT JOIN rh_enderecos e 
                ON e.idPessoa = P.idPessoa 
                AND e.idTipoEndereco = 1
            WHERE P.idPessoa = :id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

die( json_encode([
    "status" => true,
    "msg" => '<div class="alert alert-success">
        <strong>OK!</strong> Modelo encontrado!
        </div>',
    "dados" => $dados
]) );