<?php
//
// candidatos_aj0.php | Devolve dados do Currículo para formulário candidatos.php
// (C)haia, 17/04/2026
//

session_start();

$idModulo = 7; // CURRICULUM

include_once "../app/includes/conexao_gerar.php";
include_once "../app/includes/f_logs.php";

//- pessoa_id vem só da sessão aberta em auth.php - nunca de parâmetro do cliente,
//- senão qualquer um lê o currículo de qualquer pessoa só sabendo o ID dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Faça login novamente."]));
}
$pessoa_id = (int) $_SESSION['candidato_idPessoa'];

$sql = "SELECT * 
    FROM rh_pessoas P 
    INNER JOIN rh_cv C on C.idPessoa = P.idPessoa 
    WHERE P.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $pessoa_id, PDO::PARAM_INT);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);

$retorno = [
    'status'=> true,
    'dados' => $linha
];

die( json_encode($retorno) );