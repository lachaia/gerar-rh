<?php
//
//- new_aj16_hab.php | SALVA HABILIDADES - CV
//- (C)haia, 03/04/2025 | (U) 2026-04-15
//

/*
include "debug.php";
$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );

    "idPessoa": "83",
    "habilidades": [
        "teste"
    ]
*/

session_start();

include "../app/includes/conexao_gerar.php"; // Certifique-se de que a conexão está correta

header('Content-Type: application/json');

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra apagar e
//- substituir as habilidades do currículo de qualquer pessoa só sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];
$habilidades = $_POST['habilidades'] ?? [];

if (empty($habilidades)) {
    $msg = '<div class="alert alert-danger"><strong>ERRO!</strong> Dados inválidos!</div';
    die(json_encode(["status" => false, "msg" => $msg]));
}

try {
    $conn->beginTransaction();

    // Remover habilidades antigas antes de inserir as novas
    $stmtDelete = $conn->prepare("DELETE FROM rh_cv_habilidades WHERE idPessoa = ?");
    $stmtDelete->execute([$idPessoa]);

    // Inserir novas habilidades
    $stmtInsert = $conn->prepare("INSERT INTO rh_cv_habilidades (idPessoa, habilidade) VALUES (?, ?)");
    
    foreach ($habilidades as $habilidade) {
        $stmtInsert->execute([$idPessoa, $habilidade]);
    }

    $conn->commit();
    $msg = '<div class="alert alert-success"><strong>Sucesso!</strong> Habilidades salvas!</div';
    echo json_encode(["status" => true, "msg" => $msg]);
} catch (Exception $e) {
    $conn->rollBack();
    $erro =  $e->getMessage();
    $msg = '<div class="alert alert-danger"><strong>ERRO!</strong> Erro ao salvar: '.$erro.'</div>'; 
    echo json_encode(["status" => false, "msg" => $msg]);
}