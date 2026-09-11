<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega as classes
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

/**
 * Cria e retorna uma instância configurada do PHPMailer.
 * Credenciais vêm de variáveis de ambiente (.env na raiz) — ver .env.example
 */
function criarMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = $_ENV['SES_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SES_USERNAME'];
        $mail->Password   = $_ENV['SES_PASSWORD'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = (int) ($_ENV['SES_PORT'] ?? 587);
        $mail->CharSet    = "UTF-8";
        $mail->isHTML(true);

        // Remetente padrão
        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'rh@gerar.org.br', $_ENV['MAIL_FROM_NAME'] ?? 'RH-Gerar');

    } catch (Exception $e) {
        error_log("Erro ao configurar PHPMailer: " . $e->getMessage());
    }

    return $mail;
}
