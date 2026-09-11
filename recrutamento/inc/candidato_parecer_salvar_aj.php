<?php
//
//- candidato_parecer_salvar_aj.php | Salva o parecer de screening (upsert) e,
//- opcionalmente, envia por e-mail ao gestor da vaga - candidato_parecer.php
//- (C)haia, 28/08/2026 | (U) 2026-08-28
//
//- Campos de texto rico (chamou_atencao, tem_experiencia, analise_entrevista) vêm do
//- Summernote - guardados como HTML (conteúdo de staff, mesmo nível de confiança já
//- usado em descrição de vaga - ver conteudo_rico() em vaga_view.php).
//- Conclusão "Não apto" move a candidatura para Rejeitado automaticamente - reflete
//- o que já aconteceu de fato, sem precisar arrastar o card separadamente depois.
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

const FLUXO_CAND_REJEITADO = 6;

$candidatura_id = filter_input(INPUT_POST, 'candidatura_id', FILTER_VALIDATE_INT);
$enviar = filter_input(INPUT_POST, 'enviar', FILTER_VALIDATE_INT) === 1;

$data_nascimento = trim((string) filter_input(INPUT_POST, 'data_nascimento', FILTER_DEFAULT));
$endereco = trim((string) filter_input(INPUT_POST, 'endereco', FILTER_UNSAFE_RAW));
$chamou_atencao = trim((string) filter_input(INPUT_POST, 'chamou_atencao', FILTER_UNSAFE_RAW));
$tem_cnh = trim((string) filter_input(INPUT_POST, 'tem_cnh', FILTER_DEFAULT));
$nivel_office = trim((string) filter_input(INPUT_POST, 'nivel_office', FILTER_DEFAULT));
$tem_experiencia = trim((string) filter_input(INPUT_POST, 'tem_experiencia', FILTER_UNSAFE_RAW));
$pretensao_salarial = trim((string) filter_input(INPUT_POST, 'pretensao_salarial', FILTER_DEFAULT));
$analise_entrevista = trim((string) filter_input(INPUT_POST, 'analise_entrevista', FILTER_UNSAFE_RAW));
$conclusao = trim((string) filter_input(INPUT_POST, 'conclusao', FILTER_DEFAULT));
$gestor_nome = trim((string) filter_input(INPUT_POST, 'gestor_nome', FILTER_UNSAFE_RAW));
$gestor_email = trim((string) filter_input(INPUT_POST, 'gestor_email', FILTER_DEFAULT));

if (!$candidatura_id) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos."]);
    exit;
}
//- strip_tags pra checar se sobrou texto de verdade (Summernote vazio manda "<p><br></p>").
if (trim(strip_tags($analise_entrevista)) === '' || $conclusao === '') {
    echo json_encode(["status" => false, "msg" => "Preencha ao menos a análise da entrevista e a conclusão."]);
    exit;
}
if ($gestor_email !== '' && !filter_var($gestor_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "msg" => "E-mail do gestor inválido."]);
    exit;
}

$sql = "SELECT CA.pessoa_id, CA.vaga_id, P.nome AS candidato_nome,
               V.identificador, C.nome AS cargo_ds, V.gestor_nome, V.gestor_email
        FROM rs_vagas_candidaturas CA
        INNER JOIN rh_pessoas P ON P.idPessoa = CA.pessoa_id
        INNER JOIN rs_vagas V ON V.id = CA.vaga_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        WHERE CA.id = :candidatura_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();
$contexto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contexto) {
    echo json_encode(["status" => false, "msg" => "Candidatura não encontrada."]);
    exit;
}

//- E-mail do gestor informado na própria tela - reaproveita se a vaga ainda não tinha.
$gestor_email_usar = $gestor_email !== '' ? $gestor_email : $contexto['gestor_email'];
$gestor_nome_usar = $gestor_nome !== '' ? $gestor_nome : $contexto['gestor_nome'];

if ($enviar && empty($gestor_email_usar)) {
    echo json_encode(["status" => false, "msg" => "Informe o e-mail do gestor para enviar o parecer."]);
    exit;
}

try {
    $conn->beginTransaction();

    if ($gestor_email !== '' && $gestor_email !== $contexto['gestor_email']) {
        $stmt = $conn->prepare("UPDATE rs_vagas SET gestor_nome = :gestor_nome, gestor_email = :gestor_email WHERE id = :vaga_id");
        $stmt->bindValue(':gestor_nome', $gestor_nome_usar !== '' ? $gestor_nome_usar : null, $gestor_nome_usar !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':gestor_email', $gestor_email_usar, PDO::PARAM_STR);
        $stmt->bindValue(':vaga_id', $contexto['vaga_id'], PDO::PARAM_INT);
        $stmt->execute();
    }

    $sql = "INSERT INTO rs_candidatos_parecer
                (candidatura_id, data_nascimento, endereco, chamou_atencao, tem_cnh, nivel_office,
                 tem_experiencia, pretensao_salarial, analise_entrevista, conclusao, criado_por, criado_em)
            VALUES
                (:candidatura_id, :data_nascimento, :endereco, :chamou_atencao, :tem_cnh, :nivel_office,
                 :tem_experiencia, :pretensao_salarial, :analise_entrevista, :conclusao, :criado_por, NOW())
            ON DUPLICATE KEY UPDATE
                data_nascimento = VALUES(data_nascimento),
                endereco = VALUES(endereco),
                chamou_atencao = VALUES(chamou_atencao),
                tem_cnh = VALUES(tem_cnh),
                nivel_office = VALUES(nivel_office),
                tem_experiencia = VALUES(tem_experiencia),
                pretensao_salarial = VALUES(pretensao_salarial),
                analise_entrevista = VALUES(analise_entrevista),
                conclusao = VALUES(conclusao),
                atualizado_em = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
    $stmt->bindValue(':data_nascimento', $data_nascimento !== '' ? $data_nascimento : null, $data_nascimento !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':endereco', $endereco !== '' ? $endereco : null, $endereco !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':chamou_atencao', $chamou_atencao !== '' ? $chamou_atencao : null, $chamou_atencao !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':tem_cnh', $tem_cnh !== '' ? $tem_cnh : null, $tem_cnh !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':nivel_office', $nivel_office !== '' ? $nivel_office : null, $nivel_office !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':tem_experiencia', $tem_experiencia !== '' ? $tem_experiencia : null, $tem_experiencia !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':pretensao_salarial', $pretensao_salarial !== '' ? $pretensao_salarial : null, $pretensao_salarial !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':analise_entrevista', $analise_entrevista, PDO::PARAM_STR);
    $stmt->bindValue(':conclusao', $conclusao, PDO::PARAM_STR);
    $stmt->bindValue(':criado_por', mb_substr($_SESSION['nmLogin'] ?? '', 0, 45), PDO::PARAM_STR);
    $stmt->execute();

    $msg = "Parecer salvo com sucesso.";
    $quem = $_SESSION['nmLogin'] ?? '';

    //- "Não apto" reflete que o candidato já saiu do processo - move pra Rejeitado
    //- automaticamente, sem precisar arrastar o card à parte depois.
    $stmt = $conn->prepare("SELECT fluxo_id FROM rs_vagas_candidaturas WHERE id = :id");
    $stmt->bindValue(':id', $candidatura_id, PDO::PARAM_INT);
    $stmt->execute();
    $fluxo_atual = (int) $stmt->fetchColumn();

    if ($conclusao === 'Não apto' && $fluxo_atual !== FLUXO_CAND_REJEITADO) {
        $stmt = $conn->prepare("UPDATE rs_vagas_candidaturas
                                 SET fluxo_id = :fluxo, fluxo_alterado_em = NOW(), motivo_rejeicao = :motivo
                                 WHERE id = :id");
        $stmt->bindValue(':fluxo', FLUXO_CAND_REJEITADO, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', 'Reprovado no parecer de screening.');
        $stmt->bindValue(':id', $candidatura_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, NOW(), :oque, :quem)");
        $stmt->bindValue(':vaga_id', $contexto['vaga_id'], PDO::PARAM_INT);
        $stmt->bindValue(':oque', "{$contexto['candidato_nome']} movido automaticamente para Rejeitado (parecer de screening: Não apto)");
        $stmt->bindValue(':quem', mb_substr($quem, 0, 45));
        $stmt->execute();

        $msg = "Parecer salvo. Candidato movido para Rejeitado.";
    }

    if ($enviar) {
        $titulo_vaga = $contexto['identificador'] ?: ($contexto['cargo_ds'] ?? 'Vaga');
        $contexto['gestor_email'] = $gestor_email_usar;
        $contexto['gestor_nome'] = $gestor_nome_usar;
        $enviado_ok = parecer_enviar_email($contexto, $titulo_vaga, [
            'data_nascimento' => $data_nascimento,
            'endereco' => $endereco,
            'chamou_atencao' => $chamou_atencao,
            'tem_cnh' => $tem_cnh,
            'nivel_office' => $nivel_office,
            'tem_experiencia' => $tem_experiencia,
            'pretensao_salarial' => $pretensao_salarial,
            'analise_entrevista' => $analise_entrevista,
            'conclusao' => $conclusao,
        ]);

        if ($enviado_ok) {
            $stmt = $conn->prepare("UPDATE rs_candidatos_parecer SET enviado_em = NOW(), enviado_para = :enviado_para WHERE candidatura_id = :candidatura_id");
            $stmt->bindValue(':enviado_para', $gestor_email_usar, PDO::PARAM_STR);
            $stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, NOW(), :oque, :quem)");
            $stmt->bindValue(':vaga_id', $contexto['vaga_id'], PDO::PARAM_INT);
            $stmt->bindValue(':oque', "Parecer de screening de {$contexto['candidato_nome']} enviado ao gestor ({$gestor_email_usar})");
            $stmt->bindValue(':quem', mb_substr($quem, 0, 45));
            $stmt->execute();

            $msg .= " Enviado ao gestor ({$gestor_email_usar}).";
        } else {
            $msg .= " Porém houve falha ao enviar o e-mail - tente novamente em instantes.";
        }
    }

    $conn->commit();
    echo json_encode(["status" => true, "msg" => $msg]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("candidato_parecer_salvar_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Falha ao salvar o parecer."]);
}
exit;

//- Mesma regra de vaga_view.php: se o texto não tem tag nenhuma (campo digitado sem
//- formatação), aplica nl2br pra preservar quebras de linha; se já é HTML (Summernote
//- de verdade), usa como está.
function parecer_conteudo_rico(?string $valor): string
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        return '';
    }
    return strip_tags($valor) === $valor ? nl2br(htmlspecialchars($valor)) : $valor;
}

function parecer_enviar_email(array $contexto, string $titulo_vaga, array $p): bool
{
    require_once "../../app/includes/email_config.php";

    $nome = htmlspecialchars($contexto['candidato_nome']);
    $conclusao_cor = $p['conclusao'] === 'Apto' ? '#198754' : '#dc3545';

    $linhaTexto = function (string $label, ?string $valor) {
        if ($valor === null || trim($valor) === '') {
            return '';
        }
        return "<p style='margin:4px 0;'><strong>{$label}:</strong> " . htmlspecialchars($valor) . "</p>";
    };
    $linhaRica = function (string $label, ?string $valor) {
        $conteudo = parecer_conteudo_rico($valor);
        if ($conteudo === '') {
            return '';
        }
        return "<p style='margin:4px 0;'><strong>{$label}:</strong></p><div style='margin:0 0 8px 0;'>{$conteudo}</div>";
    };

    $body = "
        <div style='font-family: Segoe UI, Arial, sans-serif; color:#333; max-width:640px;'>
            <h2 style='color:#0dcaf0;'>Parecer Técnico: {$titulo_vaga}</h2>
            <h3 style='margin-bottom:0;'>{$nome}</h3>
            " . $linhaTexto('Data de nascimento', !empty($p['data_nascimento']) ? date('d/m/Y', strtotime($p['data_nascimento'])) : null) . "
            " . $linhaTexto('Endereço', $p['endereco']) . "
            <hr>
            " . $linhaRica('O que chamou atenção na vaga', $p['chamou_atencao']) . "
            " . $linhaTexto('Tem CNH B', $p['tem_cnh']) . "
            " . $linhaTexto('Nível de domínio no pacote Office', $p['nivel_office']) . "
            " . $linhaRica('Tem experiência para a vaga? Quais?', $p['tem_experiencia']) . "
            " . $linhaTexto('Pretensão salarial', !empty($p['pretensao_salarial']) ? ('R$ ' . number_format((float) $p['pretensao_salarial'], 2, ',', '.')) : null) . "
            <hr>
            <h4>Análise da Entrevista</h4>
            <div>" . parecer_conteudo_rico($p['analise_entrevista']) . "</div>
            <p style='margin-top:20px; padding:12px; background:#f8f9fa; border-left:4px solid {$conclusao_cor};'>
                <strong style='color:{$conclusao_cor};'>Conclusão: {$p['conclusao']}</strong>
            </p>
            <p style='color:#718096;font-size:13px; margin-top:20px;'>Enviado pelo time de Recrutamento &amp; Seleção - GERAR.</p>
        </div>";

    try {
        $mail = criarMailer();
        $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
        $mail->addAddress($contexto['gestor_email'], $contexto['gestor_nome'] ?: '');
        $mail->Subject = "Parecer Técnico: {$titulo_vaga} - {$contexto['candidato_nome']}";
        $mail->Body = $body;
        $mail->AltBody = "Parecer técnico de {$contexto['candidato_nome']} para a vaga {$titulo_vaga}. Conclusão: {$p['conclusao']}.";
        return $mail->send();
    } catch (\Exception $e) {
        error_log("candidato_parecer_salvar_aj.php | erro ao enviar e-mail: " . $e->getMessage());
        return false;
    }
}
