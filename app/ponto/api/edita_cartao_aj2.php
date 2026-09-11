<?php
//
// edita_cartao_aj2.php | Recupera dados da Batida para Edição
// (C)haia, 13/11/2025
//

session_start();
include dirname(__DIR__) . '/../includes/conexao_gerar.php';

header('Content-Type: application/json; charset=utf-8');

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

try {
    $sql = "SELECT * FROM rh_ponto_registros WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dados) {
        echo json_encode([
            'status' => true,
            'data' => [
                'data' => date('Y-m-d', strtotime($dados['data_hora'])),
                'hora' => date('H:i', strtotime($dados['data_hora'])),
                'motivo' => ''
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['status' => false, 'mensagem' => 'Registro não encontrado.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => false, 'mensagem' => $e->getMessage()]);
}
