<?php
//
//- superintendente_exc_aj.php | Exclui registro de rs_superintendentes (bloqueado se já usado em rs_vagas_aprova)
//- (C)haia, 24/08/2026
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

//- Verifica se o superintendente já foi utilizado em alguma aprovação de vaga (não pode perder a integridade referencial)

$sql = "SELECT COUNT(*) AS qtd FROM rs_vagas_aprova WHERE super_id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$uso = $stmt->fetch(PDO::FETCH_ASSOC);

if ($uso['qtd'] > 0) {
    echo json_encode([
        "status" => false,
        "msg" => "Este superintendente já participou de {$uso['qtd']} aprovação(ões) de vaga e não pode ser excluído. Edite o registro e desmarque 'Ativo' para desativá-lo."
    ]);
    exit;
}

$sql = "DELETE FROM rs_superintendentes WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Superintendente excluído com sucesso."]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao excluir superintendente."]);
}
exit;
