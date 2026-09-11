<?php
//
//- index_aj5.php | Recupera dados do id modelo de termo para Edit e View
//- (C)haia, 22/03/2025
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

$sql = "SELECT * FROM rh_equip_modelos WHERE id = :id";
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