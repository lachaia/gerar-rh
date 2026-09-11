<?php
require_once 'conexao_gerar.php';

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) extract($dados);
/*
require_once 'debug.php';
debug( json_encode( $dados, JSON_PRETTY_PRINT) );
*/

if (empty($nome)) {
    $retorno = [
        "status" => false,
        "msg" => "Faltou parâmetros"
    ];
    die(json_encode($retorno));
}

try {
    $stmt = $conn->prepare("INSERT INTO rh_docs_tipo (nome, validade, ativo) VALUES (:nome, NULL, 1)");
    $stmt->execute([':nome' => $nome]);
    $id = $conn->lastInsertId();

    echo json_encode(['status' => true, 'id' => $id, 'nome' => $nome]);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'msg' => $e->getMessage()]);
}
