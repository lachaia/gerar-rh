<?php
require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados

header('Content-Type: application/json');

$idPessoa = $_POST['idPessoa'] ?? null;

if (!$idPessoa) {
    die(json_encode(["status" => false, "msg" => "ID da pessoa não informado"]));
}

try {
    $stmt = $conn->prepare("SELECT habilidade FROM rh_cv_habilidades WHERE idPessoa = ?");
    $stmt->execute([$idPessoa]);
    $habilidades = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(["status" => true, "habilidades" => $habilidades]);
} catch (Exception $e) {
    echo json_encode(["status" => false, "msg" => "Erro ao buscar habilidades: " . $e->getMessage()]);
}
?>
