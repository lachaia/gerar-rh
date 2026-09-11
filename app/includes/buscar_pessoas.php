<?php
require 'conexao_gerar.php';

$termo = "%".$_GET['term']."%";

$sql = "SELECT idPessoa, nome, dtNascimento, cpf FROM rh_pessoas WHERE nome LIKE ? ORDER BY nome ASC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute([$termo]);

$resultados = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $resultados[] = [
        'idPessoa' => $row['idPessoa'],
        'nome'     => $row['nome'],
        'cpf'     => $row['cpf'],
        "dtNascimento" => $row["dtNascimento"] // <-- necessário
    ];
}

echo json_encode($resultados);
?>
