<?php
//
//- rh_docs_eml_aj.php | Envia o arquivo por e-mail ao destinatário avulso
// (C) 2024-01-14 by Chaia | (U) 2025-06-02
//

session_start();
ob_start(); // logo no início do arquivo | Captura saídas de tela

if (!isset($_SESSION['idLogin'])) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

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

require_once '../includes/email_config.php';
include_once "../includes/conexao_gerar.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idUsuario = $_SESSION['idUsuario'];
$idGrupo   = $_SESSION['idGrupo'  ];
$idLogin   = $_SESSION['idLogin'  ];
$nmFrom    = $_SESSION['nmUsuario'];
$emailFrom = $_SESSION['email'    ]; //- quem envia
$idEmpresa = $_SESSION['idEmpresa'];
/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
*/

if (empty($para) || !filter_var($para, FILTER_VALIDATE_EMAIL)) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: sem destinatário!</div>'];
    die(json_encode($retorna));
}
if (!empty($cc) && !filter_var($cc, FILTER_VALIDATE_EMAIL)) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: CC inválido!</div>'];
    die(json_encode($retorna));
}
if (!empty($cco) && !filter_var($cco, FILTER_VALIDATE_EMAIL)) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: CCO inválido!</div>'];
    die(json_encode($retorna));
}

// idPessoa e arquivo NUNCA vêm do cliente: o POST original permitia
// montar qualquer caminho (../docs/pessoa_<qualquer coisa>/<qualquer arquivo>)
// e anexá-lo + enviar por e-mail para um destinatário arbitrário — um
// exfiltrador de arquivo arbitrário via e-mail. Agora o documento real é
// buscado no banco pelo idDoc, e o caminho é remontado a partir do dado
// do banco (nunca do que o cliente mandou).
$_idDoc = (int) ($_idDoc ?? 0);
if ($_idDoc <= 0) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: documento inválido!</div>'];
    die(json_encode($retorna));
}

$sql = "SELECT idPessoa, arquivo FROM rh_documentos WHERE idDoc = :idDoc";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idDoc', $_idDoc, PDO::PARAM_INT);
$stmt->execute();
$docRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$docRow) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: arquivo não encontrado!</div>'];
    die(json_encode($retorna));
}

$idPessoa = (int) $docRow['idPessoa'];
$arquivo  = basename($docRow['arquivo']);
$documento = "../docs/pessoa_$idPessoa/$arquivo"; // caminho do arquivo

if ( ! is_file( $documento )) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: arquivo não encontrado!</div>'];
    die(json_encode($retorna));
}

$mail = criarMailer();
$mail->setFrom("rh@gerar.org.br", "Equipe RH");  // Nome e e-Mail do Remetente
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

//
//- SALVA EMAIL
//

$agora = date('Y-m-d H:i:s');

$sql = "INSERT INTO rh_emails (idPessoa, data, destinatario, cc, cco, titulo, mensagem, status, idDoc, idEmpresa, idLogin)
        values (:idPessoa, :data, :destinatario, :cc, :cco, :titulo, :mensagem, 1, :idDoc, :idEmpresa, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':idPessoa'     => $idPessoa,
    ':data'         => $agora,
    ':destinatario' => $para,
    ':cc'           => $cc ?? '',
    ':cco'          => $cco ?? '',
    ':titulo'       => $titulo,
    ':mensagem'     => $mensagem,
    ':idDoc'        => $_idDoc,
    ':idEmpresa'    => $idEmpresa,
    ':idLogin'      => $idLogin,
]);
$idEmail = $conn->lastInsertId();

//
//- SALVA LOG
//
$agora = date('Y-m-d H:i:s');
$historico = "Envio do documento $arquivo por e-mail para $para";
$sql = "INSERT INTO rh_logs (idLogin, dtOper, oper, historico, tabela, idModulo, idOperacao)
				VALUES (:idLogin, :dtOper, 'EML', :historico, 'rh_emails', :idModulo, :idOperacao)";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':idLogin'    => $idLogin,
    ':dtOper'     => $agora,
    ':historico'  => $historico,
    ':idModulo'   => $idModulo,
    ':idOperacao' => $idEmail,
]);
$conn = null;

echo json_encode($retorna);
