<?PHP
//
//- rh_avaliacoes_aj3.php | retorna lista de colaboradores
// (C)haia, 09/09/2025

require 'conexao_gerar.php';

$sql = "SELECT C.idColab, C.idPessoa, P.nome, P.dtNascimento, P.cpf, C.salario_base 
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa 
            ORDER BY nome ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();

$resultados = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $resultados[] = [
        'idColab'  => $row['idColab'],
        'idPessoa' => $row['idPessoa'],
        'nome'     => $row['nome'],
        'cpf'      => $row['cpf'],
        'salario'  => $row['salario_base'],
        "dtNascimento" => $row["dtNascimento"]
    ];
}

echo json_encode($resultados);