<?php
//
//- rh_termo_aj5.php | Inclui novo tipo de TERMO
//- (C)haia, 11/08/2025
//

session_start();

$idModulo = 18; // Termos Gerais

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

extract($parametros);

require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados

$descricao = trim($_POST['descricao']);

$stmt = $conn->prepare("INSERT INTO rh_termos_tipos (descricao) VALUES (?)");
$stmt->execute([$descricao]);
$id = $conn->lastInsertId();

echo json_encode([
    "id" => $id,
    "descricao" => $descricao
]);
