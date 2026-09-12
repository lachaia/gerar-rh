<?php
//
//- rh_ficha_denuncia_aj1.php | Salva novo Tipo de Ação
//- (C)haia, 25/07/2025
//

session_start();

if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [3, 9], true)) {
    http_response_code(403);
    die(json_encode(['status' => false, 'msg' => 'Acesso negado.']));
}

include_once "conexao_gerar.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract( $dados);

if( empty($nome) || empty($cor) ){
    $retorno = [
        'status' => 'erro',
        'msg' => 'Nome e cor são obrigatórios'
    ];
    echo json_encode( $retorno );
    exit;
}

if(empty($cor)) $cor = '#000000'; // Cor padrão se não for fornecida
if(empty($icone)) $icone = 'fa-solid fa-circle'; // Ícone padrão se não for fornecido

    $sql = "INSERT INTO rh_ouvidoria_tldt (nome, icone, cor) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $sucesso = $stmt->execute([$nome, $icone, $cor]);
    $id = $conn->lastInsertId();
    echo json_encode([
        'status' => $sucesso,
        'id' => $id,
        'nome' => $nome
    ]);
    exit;
