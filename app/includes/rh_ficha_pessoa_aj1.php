<?php
//
//- rh_ficha_pessoa_aj1.php | Envia o EMAIL vindo do Formulário rh_ficha_pessoa.php
// - (C) 10/03/2025 by Chaia
//

session_start();

$idUsuario = $_SESSION['idUsuario'];
$idLogin   = $_SESSION['idLogin'];
$idEmpresa = $_SESSION['idEmpresa'];

$idModulo = 2; //- PESSOAS

$agora = date("Y-m-d H:i:s");

include_once "../includes/debug.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
/*
debug( json_encode($dados, JSON_PRETTY_PRINT));
$retorna = [ "status" => false, "msg" => '<div class="alert alert-primary" role="alert">TESTE REALIZADO COM SUCESSO!</div>'];
die( json_encode( $retorna ) );

 rh_pessoa_aj16.php | 2025-03-11 09:07:59 
{
    "idPessoa": "1",
    "idDoc": "14",
    "arquivo": "arq-20250307184838.pdf",
    "emailDestinatario": "lachaia@gmail.com",
    "emailCC": "",
    "emailCCO": "",
    "emailTitulo": "Envio de Documento - arq-20250307184838.pdf",
    "emailCorpo": "<p>Prezado(a),<\/p><p>Segue anexo o documento <strong>arq-20250307184838.pdf<\/strong>.<\/p><p>Atenciosamente,<\/p>"
}
*/

if( $dados ){
     extract($dados);
     // reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
     $idUsuario = $_SESSION['idUsuario'];
     $idLogin   = $_SESSION['idLogin'];
     $idEmpresa = $_SESSION['idEmpresa'];
} else{
    $retorna = [ "status" => false, "msg" => '<div class="alert alert-danger" role="alert">Faltou Parâmetros!</div>'];
    die( json_encode( $retorna ) ); 
}

if (empty($emailDestinatario)) {
    $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: sem destinatário!</div>'];
    die(json_encode($retorna));
}


include_once "../includes/inc_email.php";

$mail->setFrom('rh@gerar.org.br', 'GERAR-RH');
$mail->Subject = $emailTitulo;

ob_start();
$mail->addAddress($emailDestinatario, '');     //Add a recipient
if (! empty($cc)) $mail->addCC($emailCC, '');
if (! empty($cco)) $mail->addBCC($emailCCO, '');

// Adiciona anexos ao e-mail, se existirem

    // Definindo o diretório de origem
    $caminhoArquivo = "../docs/pessoa_$idPessoa/$arquivo"; // Caminho do arquivo
    $mail->addAttachment($caminhoArquivo);
    $emailCorpo .= "<br><br>Anexo: " . $arquivo;

$mail->Body = $emailCorpo;

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

$sql = "INSERT INTO rh_emails (data, idPessoa, destinatario, cc, cco, titulo, mensagem, status, idLogin, idEmpresa, idDoc)
                    VALUES ( '$agora', $idPessoa, '$emailDestinatario', '$emailCC', '$emailCCO', '$emailTitulo', '$emailCorpo', 1, $idLogin, $idEmpresa, $idDoc)";
//debug( $sql );            
$stmt = $conn->prepare($sql);
$stmt->execute();
$idEmail = $conn->lastInsertId();

//- SALVA AÇÃO NA LINHA DO TEMPO DA PESSOA
$idAcaoTipo = 5; // e-Mail enviado
$sql = "INSERT INTO rh_pessoas_ldt ( idAcaoTipo, idPessoa, idEmpresa, data, descricao, idLogin, idEmail, idDoc) 
                VALUES ( $idAcaoTipo, $idPessoa, $idEmpresa, '$agora', '$emailTitulo', $idLogin, $idEmail, $idDoc)";
$stmt = $conn->prepare($sql);
$stmt->execute();
//
//- SALVA LOG
//
$sql = "INSERT INTO rh_logs (idLogin, dtOper, oper, historico, tabela, idModulo, idOperacao) 
				VALUES ($idLogin, '$agora', 'EML', 'Envio de e-mail para $emailDestinatario', 'rh_emails', $idModulo, $idEmail)";
$stmt = $conn->prepare($sql);
$stmt->execute();
$conn = null;

echo json_encode($retorna);
