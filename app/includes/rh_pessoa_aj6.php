<?php
//
//- rh_pessoa_aj6.php | Devolve a lista de Endereços da Pessoa Selecionada
//- (C)haia, 05/03/2025
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados

$idPessoa = filter_input(INPUT_POST, 'idPessoa', FILTER_VALIDATE_INT);

if (!$idPessoa) {
    die(json_encode(["status" => false, "msg" => "ID da pessoa inválido!"]));
}

$sql = "SELECT E.*, T.dsTipoEndereco 
            FROM RH.rh_enderecos E
            INNER JOIN rh_enderecos_tipo T on T.idTipoEndereco = E.idTipoEndereco  
            WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":idPessoa", $idPessoa, PDO::PARAM_INT);
$stmt->execute();

$enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($enderecos) {
    echo json_encode(["status" => true, "enderecos" => $enderecos]);
} else {
    echo json_encode(["status" => false, "msg" => "Nenhum endereço encontrado!"]);
}

$conn = null;
