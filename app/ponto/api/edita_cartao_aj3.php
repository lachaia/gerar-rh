<?php
//
// edita_cartao_aj3.php | Exclui Solicitação de Ajuste
// (C)haia, 13/11/2025
//

session_start();
require_once dirname(__DIR__) . '/../includes/conexao_gerar.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['idLogin']) || empty($_SESSION['idColab'])) {
    http_response_code(403);
    echo json_encode(['status' => false, 'mensagem' => 'Sessão inválida.']);
    exit;
}

// Captura e valida parâmetros
$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
$id = isset($parametros['id']) ? (int)$parametros['id'] : 0;

if ($id <= 0) {
    echo json_encode(['status' => false, 'mensagem' => 'ID inválido.']);
    exit;
}

//
//- CONFIRMA QUE STATUS = 'AGUARDANDO' E QUE A SOLICITAÇÃO É DO PRÓPRIO USUÁRIO
//
    $sql = "SELECT status, colaborador_id FROM rh_ponto_solicitacoes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$solicitacao || (int) $solicitacao['colaborador_id'] !== (int) $_SESSION['idColab']) {
        http_response_code(403);
        echo json_encode(['status' => false, 'mensagem' => 'Acesso negado.']);
        exit;
    }
    if ($solicitacao['status'] != 'AGUARDANDO') {
        echo json_encode([
            'status' => false,
            'msg' => 'Solicitação j&aacute; processada.'
        ]);
        exit;
    }

try {
    $sql = "DELETE FROM rh_ponto_solicitacoes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => true,
            'msg' => 'Solicitação excluída com sucesso!'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'msg' => 'Registro não encontrado ou já excluído.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'msg' => 'Erro: ' . $e->getMessage()
    ]);
}
