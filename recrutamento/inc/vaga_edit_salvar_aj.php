<?php
//
//- vaga_edit_salvar_aj.php | Salva a atribuição de recrutador e os dados de publicação da vaga
//- (C)haia, 24/08/2026
//
//- STATUS_ID (rs_vagas_status, portão de aprovação): 2 = APROVADA
//- FLUXO_ID (rs_vagas_fluxo, etapa do processo seletivo): 1 = ALINHAMENTO
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

const STATUS_APROVADA  = 2;
const FLUXO_ALINHAMENTO = 1;

$id                = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$recrutador_id     = filter_input(INPUT_POST, 'recrutador_id', FILTER_VALIDATE_INT);
$gestor_nome       = trim((string) filter_input(INPUT_POST, 'gestor_nome', FILTER_UNSAFE_RAW));
$gestor_email      = trim((string) filter_input(INPUT_POST, 'gestor_email', FILTER_UNSAFE_RAW));
$identificador     = trim((string) filter_input(INPUT_POST, 'identificador', FILTER_UNSAFE_RAW));
$codigo_vaga       = trim((string) filter_input(INPUT_POST, 'codigo_vaga', FILTER_UNSAFE_RAW));
$setor_ds          = trim((string) filter_input(INPUT_POST, 'setor_ds', FILTER_UNSAFE_RAW));
$area              = trim((string) filter_input(INPUT_POST, 'area', FILTER_UNSAFE_RAW));
$nivel_experiencia = trim((string) filter_input(INPUT_POST, 'nivel_experiencia', FILTER_UNSAFE_RAW));
$descricao         = trim((string) filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW));
$resumo            = trim((string) filter_input(INPUT_POST, 'resumo', FILTER_UNSAFE_RAW));
$diferenciais      = trim((string) filter_input(INPUT_POST, 'diferenciais', FILTER_UNSAFE_RAW));
$beneficios        = trim((string) filter_input(INPUT_POST, 'beneficios', FILTER_UNSAFE_RAW));

if (!$id) {
    echo json_encode(["status" => false, "msg" => "ID da vaga inválido."]);
    exit;
}
if (!$recrutador_id) {
    echo json_encode(["status" => false, "msg" => "Selecione o recrutador responsável."]);
    exit;
}
if ($identificador === '' || $codigo_vaga === '' || $setor_ds === '' || $area === '' || $nivel_experiencia === '' || $descricao === '' || $resumo === '') {
    echo json_encode(["status" => false, "msg" => "Preencha todos os campos obrigatórios (*) antes de salvar."]);
    exit;
}
if ($gestor_email !== '' && !filter_var($gestor_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "msg" => "E-mail do gestor inválido."]);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT status_id, fluxo_id, recrutador_id FROM rs_vagas WHERE id = :id FOR UPDATE");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $atual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$atual) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "Vaga não encontrada."]);
        exit;
    }

    $primeira_atribuicao = empty($atual['recrutador_id'])
        && (int) $atual['status_id'] === STATUS_APROVADA
        && $atual['fluxo_id'] === null;
    $novo_fluxo = $primeira_atribuicao ? FLUXO_ALINHAMENTO : $atual['fluxo_id'];

    //- fluxo_alterado_em só é tocado quando a vaga realmente muda de etapa (primeira atribuição);
    //- editar os dados de publicação de uma vaga já em andamento não deve "zerar" o indicador.
    $sql_fluxo_alterado = $primeira_atribuicao ? ', fluxo_alterado_em = NOW()' : '';

    $sql = "UPDATE rs_vagas SET
                recrutador_id = :recrutador_id,
                gestor_nome = :gestor_nome,
                gestor_email = :gestor_email,
                identificador = :identificador,
                codigo_vaga = :codigo_vaga,
                setor_ds = :setor_ds,
                area = :area,
                nivel_experiencia = :nivel_experiencia,
                descricao = :descricao,
                resumo = :resumo,
                diferenciais = :diferenciais,
                beneficios = :beneficios,
                fluxo_id = :fluxo_id
                {$sql_fluxo_alterado}
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':recrutador_id', $recrutador_id, PDO::PARAM_INT);
    $stmt->bindValue(':gestor_nome', $gestor_nome !== '' ? $gestor_nome : null, $gestor_nome !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':gestor_email', $gestor_email !== '' ? $gestor_email : null, $gestor_email !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
    $stmt->bindParam(':codigo_vaga', $codigo_vaga, PDO::PARAM_STR);
    $stmt->bindParam(':setor_ds', $setor_ds, PDO::PARAM_STR);
    $stmt->bindParam(':area', $area, PDO::PARAM_STR);
    $stmt->bindParam(':nivel_experiencia', $nivel_experiencia, PDO::PARAM_STR);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindParam(':resumo', $resumo, PDO::PARAM_STR);
    $stmt->bindParam(':diferenciais', $diferenciais, PDO::PARAM_STR);
    $stmt->bindParam(':beneficios', $beneficios, PDO::PARAM_STR);
    $stmt->bindValue(':fluxo_id', $novo_fluxo, $novo_fluxo === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($primeira_atribuicao) {
        $stmt = $conn->prepare("SELECT P.nome FROM rh_usuarios U INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa WHERE U.idUsuario = :id");
        $stmt->bindParam(':id', $recrutador_id, PDO::PARAM_INT);
        $stmt->execute();
        $recrutador_nome = $stmt->fetchColumn() ?: 'Recrutador';

        vaga_timeline($conn, $id, date('Y-m-d H:i:s'), "Recrutador {$recrutador_nome} atribuído à vaga — em alinhamento", $_SESSION['nmLogin'] ?? $recrutador_nome);
    }

    $conn->commit();

    $msg = "Vaga atualizada com sucesso." . ($primeira_atribuicao ? " Vaga entrou no fluxo em ALINHAMENTO." : "");
    echo json_encode(["status" => true, "msg" => $msg]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("vaga_edit_salvar_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Falha ao salvar a vaga."]);
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
