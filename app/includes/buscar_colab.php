<?php
require 'conexao_gerar.php';

$termo = "%".$_GET['term']."%";

$sql = "SELECT C.idColab, C.idPessoa, P.nome, P.dtNascimento, P.cpf, C.salario_base 
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            WHERE nome LIKE ? 
            ORDER BY nome ASC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute([$termo]);

$resultados = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $resultados[] = [
        'idColab'  => $row['idColab'],
        'idPessoa' => $row['idPessoa'],
        'nome'     => $row['nome'],
        'cpf'      => $row['cpf'],
        'salario'  => $row['salario_base'],
        "dtNascimento" => $row["dtNascimento"] // <-- necessário
    ];
}

echo json_encode($resultados);
?>
