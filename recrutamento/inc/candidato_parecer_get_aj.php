<?php
//
//- candidato_parecer_get_aj.php | Recupera o parecer de screening de uma candidatura
//- (se já existir) + dados de contexto do candidato/vaga pro modal - vaga_candidatos.php
//- (C)haia, 28/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$candidatura_id = filter_input(INPUT_GET, 'candidatura_id', FILTER_VALIDATE_INT);
if (!$candidatura_id) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos."]);
    exit;
}

$sql = "SELECT CA.pessoa_id, CA.vaga_id, CA.screening_data, CA.screening_meet_link,
               P.nome, P.telefone, P.dtNascimento,
               V.gestor_nome, V.gestor_email, V.identificador,
               G.descricao AS escolaridade
        FROM rs_vagas_candidaturas CA
        INNER JOIN rh_pessoas P ON P.idPessoa = CA.pessoa_id
        INNER JOIN rs_vagas V ON V.id = CA.vaga_id
        LEFT JOIN rh_graus_instrucao G ON G.id = P.idGrauEscola
        WHERE CA.id = :candidatura_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();
$contexto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contexto) {
    echo json_encode(["status" => false, "msg" => "Candidatura não encontrada."]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM rs_candidatos_parecer WHERE candidatura_id = :candidatura_id");
$stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();
$parecer = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

echo json_encode([
    "status" => true,
    "contexto" => $contexto,
    "parecer" => $parecer,
]);
exit;
