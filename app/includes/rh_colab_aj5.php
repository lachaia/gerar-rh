<?php
// rh_colab_aj5.php - Devolve o nível do órgao selecionado
// (C)haia, 08/04/2025

//header("Content-Type: application/json");

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
}

include 'conexao_gerar.php';

$idOrgao = $_POST['idOrgao'] ?? 0;

$stmt = $conn->prepare("SELECT nivel, staff FROM rh_organograma WHERE idOrgao = ?");
$stmt->execute([$idOrgao]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "nivel" => $row['nivel'] ?? null
]);