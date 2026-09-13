<?php
//
//- rh_pessoas_eml_aj.php | Envia o EMAIL vindo do Formulário rh_pessoas.php
// - (C) 10/03/2025 by Chaia
//

session_start();

//$retorna = [ "status" => true, "msg" => '<div class="alert alert-success" role="alert">e-Mail enviado com Sucesso!</div>'];
//echo json_encode( $retorna );

$idUsuario = $_SESSION['idUsuario'];
$idLogin   = $_SESSION['idLogin'];
$idEmpresa = $_SESSION['idEmpresa'];

$idModulo = 2; //- PESSOAS

$agora = date("Y-m-d H:i:s");

//include_once "../includes/debug.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
extract($dados);
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idUsuario = $_SESSION['idUsuario'];
$idLogin   = $_SESSION['idLogin'];
$idEmpresa = $_SESSION['idEmpresa'];

if (empty($destino)) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: sem destinatário!</div>'];
    die(json_encode($retorna));
}

include("inc_email.php");


$mail->setFrom('rh@gerar.org.br', 'GERAR-RH');
$mail->Subject = $titulo;

ob_start();
$mail->addAddress($destino, '');     //Add a recipient
if (! empty($cc)) $mail->addCC($cc, '');
if (! empty($cco)) $mail->addBCC($cco, '');

// Adiciona anexos ao e-mail, se existirem

if (! empty($_FILES['arquivos']['tmp_name'][0])) {
    if (!empty($_FILES['arquivos']['error']) && is_array($_FILES['arquivos']['error'])) {
        foreach ($_FILES['arquivos']['error'] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $file_name = $_FILES['arquivos']['name'][$key];
                $file_tmp = $_FILES['arquivos']['tmp_name'][$key];
                $mail->addAttachment($file_tmp, $file_name);
                //
                $mensagem .= "<br>Anexo: " . $file_name;
                //
            }
        }
    }
}

$mail->Body = $mensagem;

$resposta = $mail->send();
$mailOutput = ob_get_clean();
if ($resposta) {
    $retorna = ["status" => true, "msg" => '<div class="alert alert-success" role="alert">e-Mail enviado com Sucesso!</div>'];
} else {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: e-Mail NÃO enviado! ' . $mail->ErrorInfo . '</div>'];
}

include_once "../includes/f_logs.php";
include_once "../includes/conexao_gerar.php";

//
//- SALVA EMAIL
//
$agora = date('Y-m-d H:i:s');

$sql = "INSERT INTO rh_emails (data, idPessoa, destinatario, cc, cco, titulo, mensagem, status, idLogin, idEmpresa)
                    VALUES ( '$agora', $idPessoa, '$destino', '$cc', '$cco', '$titulo', '$mensagem', 1, $idLogin, $idEmpresa)";
//debug( $sql );            
$stmt = $conn->prepare($sql);
$stmt->execute();
$idEmail = $conn->lastInsertId();

//- SALVA AÇÃO NA LINHA DO TEMPO DA PESSOA
$idAcaoTipo = 5; // e-Mail enviado
$sql = "INSERT INTO rh_pessoas_ldt ( idAcaoTipo, idPessoa, idEmpresa, data, descricao, idLogin) 
                VALUES ( $idAcaoTipo, $idPessoa, $idEmpresa, '$agora', '$titulo', $idLogin)";
$stmt = $conn->prepare($sql);
$stmt->execute();
//
//- SALVA LOG
//
$sql = "INSERT INTO rh_logs (idLogin, dtOper, oper, historico, tabela, idModulo, idOperacao) 
				VALUES ($idLogin, '$agora', 'EML', 'Envio de e-mail para $destino', 'rh_emails', $idModulo, $idEmail)";
$stmt = $conn->prepare($sql);
$stmt->execute();
$conn = null;

echo json_encode($retorna);
