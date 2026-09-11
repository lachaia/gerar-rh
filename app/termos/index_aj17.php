<?php
// index_aj17.php | Exclui termo de responsabilidade

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idLogin'])) {
    echo json_encode([
        'status' => false,
        'msg' => 'Sessão expirada. Faça login novamente.'
    ]);
    exit;
}

include_once "../includes/conexao_gerar.php";

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo json_encode([
        'status' => false,
        'msg' => 'ID do termo inválido.'
    ]);
    exit;
}

try {
    $conn->beginTransaction();

    $sqlBusca = "SELECT idPessoa, arquivo FROM rh_equip_termos WHERE id = :id LIMIT 1";
    $stmtBusca = $conn->prepare($sqlBusca);
    $stmtBusca->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtBusca->execute();
    $termo = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        throw new Exception('Termo não encontrado para exclusão.');
    }

    $arquivoPath = null;
    if (!empty($termo['arquivo'])) {
        $nomeArquivo = basename($termo['arquivo']);
        $arquivoPath = "../docs/pessoa_" . (int)$termo['idPessoa'] . "/" . $nomeArquivo;
    }

    $sqlLd = "DELETE FROM rh_equip_termos_ld WHERE idEquipTermo = :id";
    $stmtLd = $conn->prepare($sqlLd);
    $stmtLd->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtLd->execute();

    $sqlTermo = "DELETE FROM rh_equip_termos WHERE id = :id";
    $stmtTermo = $conn->prepare($sqlTermo);
    $stmtTermo->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtTermo->execute();

    if ($stmtTermo->rowCount() === 0) {
        throw new Exception('Falha ao excluir termo.');
    }

    if ($arquivoPath && is_file($arquivoPath) && !unlink($arquivoPath)) {
        throw new Exception('Não foi possível excluir o arquivo anexo do termo.');
    }

    $conn->commit();

    echo json_encode([
        'status' => true,
        'msg' => 'Termo excluído com sucesso.'
    ]);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        'status' => false,
        'msg' => 'Erro ao excluir termo: ' . $e->getMessage()
    ]);
}

