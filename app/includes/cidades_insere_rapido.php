<?php
include "../includes/conexao_gerar.php";

$nome = $_POST['nome'] ?? '';
$uf   = $_POST['uf'] ?? '';
$pais = $_POST['pais'] ?? 'Brasil';

if(!empty($nome)) {
    try {
        $sql = "INSERT INTO rh_cidades (nome, uf, pais) VALUES (UPPER(?), UPPER(?), UPPER(?))";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome, $uf, $pais]);
        $id = $conn->lastInsertId();

        echo json_encode(['status' => true, 'id_inserido' => $id]);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'msg' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => false, 'msg' => 'Nome vazio']);
}