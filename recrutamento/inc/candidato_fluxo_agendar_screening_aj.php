<?php
//
//- candidato_fluxo_agendar_screening_aj.php | Agenda o Screening (Google Calendar +
//- Meet) e move a candidatura para a etapa Screening - vaga_candidatos.php
//- (C)haia, 28/08/2026
//
//- Transição especial: assim como Rejeitado exige motivo, mover para Screening (3)
//- exige data/hora e não passa pelo endpoint genérico (candidato_fluxo_mover_aj.php).
//- O evento é criado na agenda da recrutadora responsável pela vaga (delegação de
//- domínio - ver app/includes/f_google_calendar.php), convidando o candidato.
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";
include "../../app/includes/f_google_calendar.php";

const FLUXO_CAND_SCREENING = 3;
const DURACAO_MINUTOS = 30;

$candidatura_id = filter_input(INPUT_POST, 'candidatura_id', FILTER_VALIDATE_INT);
$data = trim((string) filter_input(INPUT_POST, 'data', FILTER_DEFAULT));
$hora = trim((string) filter_input(INPUT_POST, 'hora', FILTER_DEFAULT));

if (!$candidatura_id || $data === '' || $hora === '') {
    echo json_encode(["status" => false, "msg" => "Informe a data e o horário do screening."]);
    exit;
}

$inicio_dt = DateTime::createFromFormat('Y-m-d H:i', "$data $hora");
if (!$inicio_dt) {
    echo json_encode(["status" => false, "msg" => "Data/horário inválidos."]);
    exit;
}
if ($inicio_dt < new DateTime()) {
    echo json_encode(["status" => false, "msg" => "Escolha uma data/horário futuro."]);
    exit;
}

//- O e-mail do recrutador precisa ser o login corporativo do Workspace
//- (rh_usuarios.login + "@gerar.org.br") - rh_pessoas.email pode ser um e-mail
//- pessoal (ex.: Gmail), que não é impersonável pela delegação de domínio.
$sql = "SELECT CA.pessoa_id, CA.vaga_id,
               P.nome AS candidato_nome, P.email AS candidato_email,
               V.identificador, C.nome AS cargo_ds,
               U.login AS recrutador_login, R.nome AS recrutador_nome
        FROM rs_vagas_candidaturas CA
        INNER JOIN rh_pessoas P ON P.idPessoa = CA.pessoa_id
        INNER JOIN rs_vagas V ON V.id = CA.vaga_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        LEFT JOIN rh_usuarios U ON U.idUsuario = V.recrutador_id
        LEFT JOIN rh_pessoas R ON R.idPessoa = U.idPessoa
        WHERE CA.id = :candidatura_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    echo json_encode(["status" => false, "msg" => "Candidatura não encontrada."]);
    exit;
}
if (empty($dados['recrutador_login'])) {
    echo json_encode(["status" => false, "msg" => "Esta vaga ainda não tem recrutador responsável definido - defina no Fluxo antes de agendar."]);
    exit;
}
$recrutador_email = $dados['recrutador_login'] . '@gerar.org.br';
if (empty($dados['candidato_email'])) {
    echo json_encode(["status" => false, "msg" => "Este candidato não tem e-mail cadastrado - não é possível convidá-lo."]);
    exit;
}

$titulo_vaga = $dados['identificador'] ?: ($dados['cargo_ds'] ?? 'Vaga');
$fim_dt = (clone $inicio_dt)->modify('+' . DURACAO_MINUTOS . ' minutes');

$erro = null;
$evento = google_criar_evento_screening(
    $recrutador_email,
    [$dados['candidato_email']],
    "Screening - {$titulo_vaga} - {$dados['candidato_nome']}",
    $inicio_dt->format('c'),
    $fim_dt->format('c'),
    $erro
);

if (!$evento) {
    echo json_encode(["status" => false, "msg" => "Falha ao agendar no Google Calendar: {$erro}"]);
    exit;
}

try {
    $conn->beginTransaction();

    $sql = "UPDATE rs_vagas_candidaturas
            SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(),
                screening_data = :screening_data, screening_meet_link = :meet, screening_evento_id = :evento_id
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':fluxo', FLUXO_CAND_SCREENING, PDO::PARAM_INT);
    $stmt->bindValue(':screening_data', $inicio_dt->format('Y-m-d H:i:s'));
    $stmt->bindValue(':meet', $evento['meetLink']);
    $stmt->bindValue(':evento_id', $evento['eventoId']);
    $stmt->bindValue(':id', $candidatura_id, PDO::PARAM_INT);
    $stmt->execute();

    $quem = $_SESSION['nmLogin'] ?? '';
    $oque = "Screening agendado com {$dados['candidato_nome']} para " . $inicio_dt->format('d/m/Y \à\s H:i') . " (Google Meet)";
    $stmt = $conn->prepare("INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, NOW(), :oque, :quem)");
    $stmt->bindValue(':vaga_id', $dados['vaga_id'], PDO::PARAM_INT);
    $stmt->bindValue(':oque', $oque);
    $stmt->bindValue(':quem', mb_substr($quem, 0, 45));
    $stmt->execute();

    $conn->commit();
    echo json_encode([
        "status" => true,
        "msg" => "Screening agendado e convite enviado por e-mail.",
        "meetLink" => $evento['meetLink'],
    ]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("candidato_fluxo_agendar_screening_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Evento criado no Calendar, mas falhou ao salvar no sistema - avise o suporte."]);
}
exit;
