<?php
//
// edita_cartao_aj4.php | Solicitação de Abono de Falta
// (C)haia, 13/11/2025
//
session_start();
require_once dirname(__DIR__) . '/../includes/conexao_gerar.php';
header('Content-Type: application/json; charset=utf-8');

$idColab = $_POST['idColab'] ?? null;
$data    = $_POST['data'] ?? null;
$motivo  = $_POST['motivo'] ?? null;
$arquivo = $_FILES['arquivo'] ?? null;
$idLogin = $_SESSION['idLogin'];

if (empty($idColab) || empty($data) || empty($motivo)) {
    echo json_encode(['status' => false, 'mensagem' => 'Campos obrigatórios faltando.']);
    exit;
}

//
//- Busca dados do Supervisor na API
//
    $url = "https://rh.gerar.org.br/api/api_supervisor.php?id=" . $idColab;
    $resposta = file_get_contents($url);
    $dados = json_decode($resposta, true); // converte JSON em array associativo

// (opcional) tratamento de upload
$caminho_arquivo = null;
if (!empty($arquivo['name'])) {
    $pasta = dirname(__DIR__) . '/uploads/abonos/';
    if (!is_dir($pasta)) mkdir($pasta, 0777, true);
    $nome_final = uniqid('abono_') . '_' . basename($arquivo['name']);
    move_uploaded_file($arquivo['tmp_name'], $pasta . $nome_final);
    $caminho_arquivo = 'uploads/abonos/' . $nome_final;
}

try {
    $sql = "INSERT INTO rh_ponto_solicitacoes (colaborador_id, supervisor_id, data_hora, tipo, status, motivo, anexo, idLogin)
            VALUES (:colab, :supervisor_id, :data, 'ABO', 'AGUARDANDO', :motivo, :anexo, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':colab' => $idColab,
        ':data' => $data,
        ':motivo' => $motivo,
        ':anexo' => $caminho_arquivo,
        ':supervisor_id' => $dados['gestor_id'],
        ':idLogin' => $idLogin
    ]);

    echo json_encode(['status' => true, 'msg' => 'Solicitação de abono enviada com sucesso!']);

} catch (Exception $e) {
    echo json_encode(['status' => false, 'mensagem' => $e->getMessage()]);
}
