<?php
session_start();

require 'conexao_gerar.php';

$term = $_GET['term'];

// Busca cidades que começam com o termo digitado
$sql = "SELECT idCidade, nome, uf, pais FROM rh_cidades WHERE nome LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute(["%$term%"]);

$results = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $textoExibicao = $row['nome'] . " - " . $row['uf'] . " (" . $row['pais'] . ")";
    $results[] = [
        'id' => $row['idCidade'],
        'nome' => $row['nome'],
        'uf' => $row['uf'],
        'pais' => $row['pais'],
        'label' => $textoExibicao,      //--> O que vai aparecer na LISTA de opções
        'value' => $row['nome']         //--> O que aparece no input ao selecionar
    ];
}

echo json_encode($results);