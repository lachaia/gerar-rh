<?php
//
//- competencia_tecnica_exc_aj.php | Exclui registro de rs_competencias_tecnicas
//- (C)haia, 24/08/2026
//
//- OBS: rs_vagas.c_tecnicas guarda um HTML já renderizado (snapshot) das competências
//- escolhidas no momento da solicitação, não os IDs. Por isso, ao contrário de
//- Motivos/Status/Superintendentes, aqui não há integridade referencial a proteger:
//- excluir uma competência não afeta vagas já solicitadas.
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

$sql = "DELETE FROM rs_competencias_tecnicas WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Competência excluída com sucesso."]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao excluir competência."]);
}
exit;
