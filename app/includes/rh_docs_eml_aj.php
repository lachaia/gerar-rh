<?php
//
//- rh_docs_eml_aj.php | Envia o arquivo por e-mail ao destinatário avulso
// (C) 2024-01-14 by Chaia | (U) 2025-06-02
//

session_start();
ob_start(); // logo no início do arquivo | Captura saídas de tela

//$retorna = [ "status" => false, "msg" => '<div class="alert alert-success" role="alert">TESTE REALIZADO COM SUCESSO!</div>'];
//die( json_encode( $retorna ) );

$idUsuario = $_SESSION['idUsuario'];
$idGrupo   = $_SESSION['idGrupo'  ];
$idLogin   = $_SESSION['idLogin'  ];
$nmFrom    = $_SESSION['nmUsuario'];
$emailFrom = $_SESSION['email'    ]; //- quem envia
$idEmpresa = $_SESSION['idEmpresa'];

$idModulo = 14; // RH-GED 

$agora = date("Y-m-d H:i:s");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer-master/src/Exception.php';
require '../includes/PHPMailer-master/src/PHPMailer.php';
require '../includes/PHPMailer-master/src/SMTP.php';

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);
/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
*/
$documento = "../docs/pessoa_$idPessoa/$arquivo"; // caminho do arquivo

if (empty($para)) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: sem destinatário!</div>'];
    die(json_encode($retorna));
}
if (empty($arquivo)) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: sem arquivo anexado!</div>'];
    die(json_encode($retorna));
}
if ( ! file( $documento )) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: arquivo não encontrado!</div>'];
    die(json_encode($retorna));
}

$mail = new PHPMailer(true);

//Server settings
$mail->SMTPDebug = SMTP::DEBUG_SERVER; 
$mail->isSMTP(); 
$mail->Host       = 'email-smtp.sa-east-1.amazonaws.com'; 
$mail->SMTPAuth   = true; 
$mail->Username   = 'AKIAZI2LFT22DNNCJ765'; 
$mail->Password   = 'BJHae6l3wKylf8k5WoD5wS87EtG0gINIwJ7oFBsLIGpl'; 
$mail->SMTPSecure = 'tls'; 
$mail->Port       = 587; 
$mail->CharSet    = "UTF-8"; 
$mail->isHTML(true); 

$mail->setFrom("rh@gerar.org.br", "Equipe RH");  // Nome e e-Mail do Remetente
$mail->isHTML(true);                             // Set email format to HTML
$mail->Subject = $titulo;
$mail->Body = $mensagem;

$mail->addAddress($para, $nome);                 //Add a recipient
if (!empty($cc)) $mail->addCC($cc, '');
if (!empty($cco)) $mail->addBCC($cco, '');

// Adiciona o anexo ao e-mail

$mail->addAttachment( $documento, $arquivo);

//-- ENVIA A MENSAGEM
//
    if( $mail->send() ){
        $retorna = ["status" => true, "msg" => '<div class="alert alert-success" role="alert">e-Mail enviado com Sucesso!</div>'];
    } else{
        $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: e-Mail NÃO enviado! ' . $mail->ErrorInfo . '</div>'];
    }
$mailOutput = ob_end_clean();

include_once "../includes/f_logs.php";
include_once "../includes/conexao_gerar.php";

//
//- SALVA EMAIL
//

$agora = date('Y-m-d H:i:s');

$sql = "INSERT INTO rh_emails (idPessoa, data, destinatario, cc, cco, titulo, mensagem, status, idDoc, idEmpresa, idLogin) 
        values ($idPessoa, '$agora', '$para', '$cc', '$cco', '$titulo', '$mensagem', 1, $_idDoc, $idEmpresa, $idLogin)";
//debug( json_encode($dados, JSON_PRETTY_PRINT) );
//debug( $sql );
//exit();
$stmt = $conn->prepare($sql);
$stmt->execute();
$idEmail = $conn->lastInsertId();

//
//- SALVA LOG
//
$agora = date('Y-m-d H:i:s');
$sql = "INSERT INTO rh_logs (idLogin, dtOper, oper, historico, tabela, idModulo, idOperacao) 
				VALUES ($idLogin, '$agora', 'EML', 'Envio do documento $arquivo por e-mail para $para', 'rh_emails', $idModulo, $idEmail)";
$stmt = $conn->prepare($sql);
$stmt->execute();
$conn = null;

echo json_encode($retorna);
