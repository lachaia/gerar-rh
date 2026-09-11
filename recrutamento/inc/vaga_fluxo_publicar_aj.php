<?php
//
//- vaga_fluxo_publicar_aj.php | Publica a vaga (move para DIVULGAÇÃO) - fluxo.php
//- (C)haia, 26/08/2026
//
//- Transição especial: só permite publicar se os dados de publicação (identificador,
//- código, setor, área, nível, descrição, resumo - ver vaga_edit.php) já estiverem
//- preenchidos. Marca publicada_em/publicada_por na primeira publicação e grava o
//- expira_em (opcional) escolhido no modal do fluxo.php.
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

const STATUS_APROVADA = 2;
const FLUXO_DIVULGACAO = 2;
const CAMPOS_PUBLICACAO_OBRIGATORIOS = ['identificador', 'codigo_vaga', 'setor_ds', 'area', 'nivel_experiencia', 'descricao', 'resumo'];

$vaga_id = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);
$expira_em = trim((string) filter_input(INPUT_POST, 'expira_em', FILTER_DEFAULT));

if (!$vaga_id) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos."]);
    exit;
}

$expira_em_valor = null;
if ($expira_em !== '') {
    $data = DateTime::createFromFormat('Y-m-d', $expira_em);
    if (!$data || $data->format('Y-m-d') !== $expira_em) {
        echo json_encode(["status" => false, "msg" => "Data de expiração inválida."]);
        exit;
    }
    $expira_em_valor = $expira_em;
}

try {
    $conn->beginTransaction();

    $sql = "SELECT status_id, fluxo_id, publicada_em, " . implode(', ', CAMPOS_PUBLICACAO_OBRIGATORIOS) . "
            FROM rs_vagas WHERE id = :id FOR UPDATE";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $vaga_id, PDO::PARAM_INT);
    $stmt->execute();
    $atual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$atual) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Vaga não encontrada."]);
        exit;
    }
    if ((int) $atual['status_id'] !== STATUS_APROVADA) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Esta vaga ainda não foi aprovada."]);
        exit;
    }

    foreach (CAMPOS_PUBLICACAO_OBRIGATORIOS as $campo) {
        if (trim((string) ($atual[$campo] ?? '')) === '') {
            $conn->rollBack();
            echo json_encode(["status" => false, "msg" => "Preencha os dados de publicação da vaga (botão 'Editar Vaga') antes de divulgar."]);
            exit;
        }
    }

    $quem = $_SESSION['nmLogin'] ?? '';
    $primeira_publicacao = $atual['publicada_em'] === null;

    if ($primeira_publicacao) {
        $sql = "UPDATE rs_vagas SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(),
                    publicada_em = NOW(), publicada_por = :quem, expira_em = :expira_em
                WHERE id = :id";
    } else {
        $sql = "UPDATE rs_vagas SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(), expira_em = :expira_em
                WHERE id = :id";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':fluxo', FLUXO_DIVULGACAO, PDO::PARAM_INT);
    if ($primeira_publicacao) {
        $stmt->bindValue(':quem', $quem, PDO::PARAM_STR);
    }
    $stmt->bindValue(':expira_em', $expira_em_valor, $expira_em_valor === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':id', $vaga_id, PDO::PARAM_INT);
    $stmt->execute();

    $validade_txt = $expira_em_valor ? (' — válida até ' . date('d/m/Y', strtotime($expira_em_valor))) : '';
    $evento = $primeira_publicacao
        ? "Vaga publicada por {$quem}{$validade_txt}"
        : "Vaga movida no fluxo para 'DIVULGAÇÃO'{$validade_txt}";
    vaga_timeline($conn, $vaga_id, date('Y-m-d H:i:s'), $evento, $quem);

    $conn->commit();
    echo json_encode(["status" => true, "msg" => "Vaga publicada com sucesso."]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("vaga_fluxo_publicar_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Falha ao publicar a vaga."]);
}
exit;

function vaga_timeline(PDO $conn, int $vaga_id, string $agora, string $oque, string $quem): int
{
    $sql = "INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, :quando, :oque, :quem)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindParam(':quando', $agora, PDO::PARAM_STR);
    $stmt->bindParam(':oque', $oque, PDO::PARAM_STR);
    $stmt->bindParam(':quem', $quem, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $conn->lastInsertId();
}
