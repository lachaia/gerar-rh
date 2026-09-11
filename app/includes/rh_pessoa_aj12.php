<?php
//
//- rh_pessoa_aj12.php | Devolve a lista de Contatos de Emergência da Pessoa Selecionada
//- (C)haia, 07/03/2025
//

session_start();

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados

$idPessoa = filter_input(INPUT_POST, 'idPessoa', FILTER_VALIDATE_INT);

if (!$idPessoa) {
    die(json_encode(["status" => false, "msg" => "ID da pessoa inválido!"]));
}

$sql = "SELECT *  FROM rh_pessoas_emg WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":idPessoa", $idPessoa, PDO::PARAM_INT);
$stmt->execute();

$contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($contatos) {
    echo json_encode(["status" => true, "contatos" => $contatos]);
} else {
    echo json_encode(["status" => false, "msg" => "Nenhum Contato encontrado!"]);
}

$conn = null;
