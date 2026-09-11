<?php
//
//- candidatura_aj.php | Registra a candidatura do candidato logado numa vaga publicada
//- (C)haia, 2026-08-27
//

session_start();

header('Content-Type: application/json');

include_once "../app/includes/conexao_gerar.php";

//- pessoa_id vem só da sessão aberta em auth.php/new_aj1.php - nunca de um campo do
//- cliente, senão qualquer um registraria candidatura em nome de outra pessoa.
if (empty($_SESSION['candidato_idPessoa'])) {
    echo json_encode(["status" => false, "msg" => "Faça login para se candidatar.", "precisaLogin" => true]);
    exit;
}
$pessoa_id = (int) $_SESSION['candidato_idPessoa'];

$vaga_id = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);

if (!$vaga_id) {
    echo json_encode(["status" => false, "msg" => "Vaga inválida."]);
    exit;
}

//- Só permite candidatura em vaga realmente publicada e ainda em aberto (mesma regra
//- de elegibilidade usada em vagas/index.php e vagas/vaga_perfil.php).
$sql = "SELECT id FROM rs_vagas
        WHERE id = :vaga_id
          AND status_id = 2
          AND fechada_em IS NULL
          AND publicada_em IS NOT NULL
          AND (expira_em IS NULL OR expira_em >= CURDATE())";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->execute();

if (!$stmt->fetch()) {
    echo json_encode(["status" => false, "msg" => "Esta vaga não está mais disponível para candidatura."]);
    exit;
}

$sql = "SELECT id FROM rs_vagas_candidaturas WHERE vaga_id = :vaga_id AND pessoa_id = :pessoa_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->fetch()) {
    echo json_encode(["status" => true, "msg" => "Você já havia se candidatado a esta vaga.", "jaExistia" => true]);
    exit;
}

//- fluxo_id 1 = Aplicado (rs_candidatos_fluxo); origem_id 1 = Portal de Vagas
//- (rs_candidatos_origem) - candidatura veio direto do portal público.
$sql = "INSERT INTO rs_vagas_candidaturas (vaga_id, pessoa_id, fluxo_id, fluxo_alterado_em, origem_id, criado_em)
        VALUES (:vaga_id, :pessoa_id, 1, NOW(), 1, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    $stmt = $conn->prepare("SELECT nome FROM rh_pessoas WHERE idPessoa = :pessoa_id");
    $stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
    $stmt->execute();
    $nome_candidato = $stmt->fetchColumn() ?: 'Candidato';

    $stmt = $conn->prepare("INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, NOW(), :oque, :quem)");
    $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindValue(':oque', 'Nova candidatura recebida: ' . $nome_candidato);
    $stmt->bindValue(':quem', mb_substr($nome_candidato, 0, 45));
    $stmt->execute();

    echo json_encode(["status" => true, "msg" => "Candidatura enviada com sucesso!"]);
} else {
    echo json_encode(["status" => false, "msg" => "Falha ao registrar candidatura."]);
}
