<?php
// rh_brigada_aj14.php | addTodosBrigadista
// by (C)haia, 25/06/2025
//

$idModulo = 10; // Brigada de Emergência

session_start();
include_once "../includes/conexao_gerar.php";

//header("Content-Type: application/json");

try {
    $sql = "SELECT b.id as idBrigadista, p.nome 
        FROM RH.rh_brigadistas b
        inner join rh_pessoas p on p.idPessoa = b.idPessoa
    WHERE b.data_final is null 
    ORDER BY p.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "dados" => $dados
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "msg" => "Erro ao consultar brigadistas: " . $e->getMessage()
    ]);
}
?>
