<?php
//
// inc_email.php | Cria $mail (PHPMailer) já configurado, via email_config.php
//

require_once __DIR__ . '/../../includes/email_config.php';

$mail = criarMailer();
