<?php
//
//- candidato_origem_exc_aj.php | Exclui registro de rs_candidatos_origem (bloqueado se já
//- usado em rs_vagas_candidaturas)
//- (C)haia, 27/08/2026
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

//- Verifica se a origem já foi utilizada em alguma candidatura (não pode perder a integridade referencial)

$sql = "SELECT COUNT(*) AS qtd FROM rs_vagas_candidaturas WHERE origem_id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$uso = $stmt->fetch(PDO::FETCH_ASSOC);

if ($uso['qtd'] > 0) {
    echo json_encode([
        "status" => false,
        "msg" => "Esta origem já foi utilizada em {$uso['qtd']} candidatura(s) e não pode ser excluída. Edite o registro e desmarque 'Ativo' para desativá-la."
    ]);
    exit;
}

$sql = "DELETE FROM rs_candidatos_origem WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Origem excluída com sucesso."]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao excluir origem."]);
}
exit;
