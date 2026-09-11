<?php
//
//- candidato_cpf_lookup_aj.php | Verifica se o CPF já existe em rh_pessoas, pro modal
//- "Adicionar Candidato" auto-preencher os dados (mesmo dedupe usado no salvamento,
//- em candidato_manual_inc_aj.php) | vaga_candidatos.php
//- (C)haia, 27/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$cpf = trim((string) filter_input(INPUT_POST, 'cpf', FILTER_DEFAULT));
$cpf_limpo = preg_replace('/\D+/', '', $cpf);

if (strlen($cpf_limpo) !== 11) {
    echo json_encode(["status" => true, "encontrado" => false]);
    exit;
}

//- Mesmo critério tolerante a formatação usado em candidato_manual_inc_aj.php.
$sql = "SELECT idPessoa, nome, telefone, email FROM rh_pessoas
        WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = :cpf
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':cpf', $cpf_limpo, PDO::PARAM_STR);
$stmt->execute();
$pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pessoa) {
    echo json_encode(["status" => true, "encontrado" => false]);
    exit;
}

//- Colaborador ativo (sem data de rescisão) - candidatura interna, não externa.
$sql = "SELECT idColab FROM rh_colaboradores WHERE idPessoa = :idPessoa AND data_rescisao IS NULL LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idPessoa', $pessoa['idPessoa'], PDO::PARAM_INT);
$stmt->execute();
$colaborador = (bool) $stmt->fetchColumn();

//- LinkedIn já salvo no currículo (rh_cv) - reaproveitado do mesmo jeito que
//- nome/telefone/email, sem sobrescrever o que já está na base.
$sql = "SELECT linkedin FROM rh_cv WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idPessoa', $pessoa['idPessoa'], PDO::PARAM_INT);
$stmt->execute();
$linkedin = $stmt->fetchColumn();

echo json_encode([
    "status" => true,
    "encontrado" => true,
    "nome" => $pessoa['nome'],
    "telefone" => $pessoa['telefone'],
    "email" => $pessoa['email'],
    "linkedin" => $linkedin ?: '',
    "colaborador" => $colaborador,
]);
exit;
