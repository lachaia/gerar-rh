<?php
require 'conexao_gerar.php';

$termo = "%".$_GET['term']."%";

$sql = "SELECT idCidade, nome, uf FROM rh_cidades WHERE nome LIKE ? ORDER BY nome ASC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute([$termo]);

$resultados = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cidade = $row['nome'] . "/" . $row['uf'];
    
    $resultados[] = [
        'id'   => $row['idCidade'],
        'nome' => $cidade
    ];
}

echo json_encode($resultados);
?>
