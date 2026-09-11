<?php
//
//- vaga_fluxo_get_aj.php | Recupera 1 registro de rs_vagas_fluxo para edição
//- (C)haia, 26/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(["status" => false, "msg" => "ID inválido."]);
    exit;
}

$sql = "SELECT id, status, descricao, ativo, cor_frente, cor_fundo FROM rs_vagas_fluxo WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo json_encode(["status" => true, "dados" => $row]);
} else {
    echo json_encode(["status" => false, "msg" => "Registro não encontrado."]);
}
exit;
