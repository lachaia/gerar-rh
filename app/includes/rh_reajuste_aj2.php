<?php
//
//- rh_reajuste_aj2.php - Aplica Reajuste no Salário Base dos Colaboradores e registra histórico
// (C)haia, 07/10/2025
//

include_once "conexao_gerar.php";
include_once "debug.php";
global $conn;

session_start();
$idLogin = $_SESSION['idLogin'] ?? 0; // usuário que está aplicando o reajuste

// Recebe os dados enviados
$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
} else{
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

debug( json_encode($dados, JSON_PRETTY_PRINT) );

$percentual = floatval($dados['percentual'] ?? 0);
$motivo = trim($dados['motivo'] ?? '');
$idColaboradores = $dados['idColaboradores'] ?? [];

if ($percentual <= 0 || empty($motivo) || empty($idColaboradores)) {
    die(json_encode([
        'status' => false,
        'msg' => 'Preencha percentual, motivo e selecione pelo menos um colaborador.'
    ]));
}

// Garante que são inteiros e cria a lista separada por vírgula
$listaColabs = implode(",", array_map('intval', $idColaboradores));

// Salva o salário atual em salario_old
$sql = "UPDATE rh_colaboradores SET salario_old = salario_base WHERE idColab IN ($listaColabs)";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Aplica o reajuste com arredondamento para cima na 3ª decimal e 2 decimais finais
$sql = "UPDATE rh_colaboradores 
        SET salario_base = ROUND(CEIL(salario_base * (1 + :percentual / 100) * 1000) / 1000, 2) 
        WHERE idColab IN ($listaColabs)";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':percentual', $percentual);
$stmt->execute();

// Insere no histórico de salário
$sqlSelect = "SELECT idColab, salario_base FROM rh_colaboradores WHERE idColab IN ($listaColabs)";
$stmtSelect = $conn->prepare($sqlSelect);
$stmtSelect->execute();
$colabsAtualizados = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

$sqlInsert = "INSERT INTO rh_historico_sal (idColab, data, valor, motivo, idLogin, indice) 
              VALUES (:idColab, NOW(), :valor, :motivo, :idLogin, :indice)";
$stmtInsert = $conn->prepare($sqlInsert);

foreach ($colabsAtualizados as $colab) {
    $stmtInsert->execute([
        ':idColab' => $colab['idColab'],
        ':valor' => $colab['salario_base'],
        ':motivo' => $motivo,
        ':indice' => $percentual,
        ':idLogin' => $idLogin
    ]);
}

echo json_encode([
    'status' => true,
    'msg' => "<div class='alert alert-success text-center h5'>Salários Atualizados e Histórico Registrado</div>",
    'linhasAfetadas' => count($colabsAtualizados)
]);
exit();
