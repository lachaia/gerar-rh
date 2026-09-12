<?php
// rh_cipa_aj14.php | addTodoscipeiro
// by (C)haia, 25/06/2025
//

$idModulo = 11; // CIPA de Emergência

session_start();
if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcCIPA']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

include_once "../includes/conexao_gerar.php";

//header("Content-Type: application/json");

try {
    $sql = "SELECT b.id as idCipeiro, p.nome 
        FROM RH.rh_cipeiros b
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
        "msg" => "Erro ao consultar cipeiros: " . $e->getMessage()
    ]);
}
?>
