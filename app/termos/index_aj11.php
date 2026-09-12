<?php
// index_aj11.php | Assina documento: Termo de Responsabilidade

session_start();
header('Content-Type: application/json; charset=utf-8');
ob_start();
ini_set('display_errors', 0);

require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include "../includes/conexao_gerar.php";

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
        if (ob_get_length()) {
            ob_clean();
        }
        echo json_encode(array(
            'status' => false,
            'msg' => '<div class="alert alert-danger">Erro fatal ao assinar termo.</div>'
        ), JSON_UNESCAPED_UNICODE);
    }
});

function resposta_json($status, $msg)
{
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'status' => (bool)$status,
        'msg' => $msg
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?: [];

$idTermoPost = isset($parametros['idTermo']) ? trim((string)$parametros['idTermo']) : '';
$usuario = isset($parametros['usuario']) ? trim((string)$parametros['usuario']) : '';
$senha = isset($parametros['senha']) ? (string)$parametros['senha'] : '';
$token = isset($parametros['token']) ? trim((string)$parametros['token']) : '';

if ($usuario === '' || $senha === '' || $token === '') {
    resposta_json(false, '<div class="alert alert-danger"><strong>Erro!</strong> Falta de parametros.</div>');
}

try {
    // Busca o termo PELO TOKEN antes de checar credenciais — o token já
    // amarra a operação a uma pessoa específica (idPessoa). Antes, a senha
    // era verificada contra QUALQUER login de rh_usuarios, sem nenhum
    // vínculo com o dono do termo: um usuário autenticado como qualquer
    // outra pessoa conseguia assinar o termo de terceiros. Também
    // funcionava como oráculo de força bruta de senha, já que a checagem
    // de senha nem dependia do token ser válido.
    $sqlTermo = "SELECT id, idPessoa, termo_html, hashPDF FROM rh_equip_termos WHERE token = :token LIMIT 1";
    $stmtTermo = $conn->prepare($sqlTermo);
    $stmtTermo->bindValue(':token', $token, PDO::PARAM_STR);
    $stmtTermo->execute();
    $row = $stmtTermo->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        resposta_json(false, '<div class="alert alert-danger">Token inválido ou expirado.</div>');
    }

    if (!empty($row['hashPDF'])) {
        resposta_json(false, '<div class="alert alert-warning">Este termo já estava assinado.</div>');
    }

    $idTermo = (int)$row['id'];
    $idPessoa = (int)$row['idPessoa'];
    $termo = (string)$row['termo_html'];

    if ($idTermoPost !== '' && ctype_digit($idTermoPost) && (int)$idTermoPost !== $idTermo) {
        resposta_json(false, '<div class="alert alert-danger">Termo inconsistente para este token.</div>');
    }

    // A senha só é validada para um login que seja OU o dono do termo
    // (idPessoa vindo do token, nunca do cliente) OU alguém de RH/Super
    // Usuário (grupo 1/9) assinando em nome de quem não tem login próprio
    // — existe pelo menos um termo real na produção nesse caso (dono sem
    // conta em rh_usuarios). Fora isso, nenhum outro login serve — antes
    // qualquer conta válida do sistema conseguia assinar termo de terceiro.
    $sqlUser = "SELECT senha FROM rh_usuarios WHERE login = :login AND (idPessoa = :idPessoa OR idUsuarioGrupo IN (1, 9)) LIMIT 1";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bindValue(':login', $usuario, PDO::PARAM_STR);
    $stmtUser->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmtUser->execute();
    $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$rowUser || !isset($rowUser['senha']) || !password_verify($senha, $rowUser['senha'])) {
        resposta_json(false, '<div class="alert alert-danger">Usuário ou senha inválidos.</div>');
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $dtAssinatura = date('Y-m-d H:i:s');
    $status = 'Assinado';
    $userIP = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $usuarioSeguro = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');

    $termo .= "<div style='text-align: center; margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; font-size: 12px;'>"
        . "Assinado eletronicamente por: {$usuarioSeguro}<br>IP: {$userIP}<br>Data/Hora: {$dtAssinatura}</div>";

    $dompdf->loadHtml($termo);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $pdfOutput = $dompdf->output();

    $dirPessoa = "../docs/pessoa_{$idPessoa}";
    if (!is_dir($dirPessoa) && !mkdir($dirPessoa, 0775, true)) {
        resposta_json(false, '<div class="alert alert-danger">Falha ao criar pasta do documento.</div>');
    }

    $arquivo = "{$dirPessoa}/responsa_{$idTermo}.pdf";
    if (file_put_contents($arquivo, $pdfOutput) === false) {
        resposta_json(false, '<div class="alert alert-danger">Falha ao gravar PDF assinado.</div>');
    }

    $hashPDF = hash('sha256', $pdfOutput);

    $sqlUp = "UPDATE rh_equip_termos
              SET dtAssinatura = :dtAssinatura,
                  status = :status,
                  userAssinatura = :usuario,
                  userIP = :userIP,
                  userAgent = :userAgent,
                  termo_html = :termo,
                  hashPDF = :hashPDF
              WHERE id = :id";

    $stmtUp = $conn->prepare($sqlUp);
    $stmtUp->bindValue(':id', $idTermo, PDO::PARAM_INT);
    $stmtUp->bindValue(':dtAssinatura', $dtAssinatura, PDO::PARAM_STR);
    $stmtUp->bindValue(':status', $status, PDO::PARAM_STR);
    $stmtUp->bindValue(':usuario', $usuarioSeguro, PDO::PARAM_STR);
    $stmtUp->bindValue(':userIP', $userIP, PDO::PARAM_STR);
    $stmtUp->bindValue(':userAgent', $userAgent, PDO::PARAM_STR);
    $stmtUp->bindValue(':termo', $termo, PDO::PARAM_STR);
    $stmtUp->bindValue(':hashPDF', $hashPDF, PDO::PARAM_STR);

    if (!$stmtUp->execute()) {
        resposta_json(false, '<div class="alert alert-danger">Erro ao assinar termo.</div>');
    }

    resposta_json(true, '<div class="alert alert-success">Assinado com sucesso!</div>');
} catch (Throwable $e) {
    resposta_json(false, '<div class="alert alert-danger">Erro na assinatura do termo.</div>');
}
