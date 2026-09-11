<?php
//
//- esqueci_senha.php | envia e-mail de recuperação de senha
//- (C)haia, 2026-04-02
//

session_start();

include "debug.php";

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( isset( $parametros )){
    extract( $parametros );
} else{
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger" role="alert">ERRO: Faltou parâmetros!</div>'
    ];
    debug( json_encode($parametros, JSON_PRETTY_PRINT) );
    echo json_encode($retorno, JSON_PRETTY_PRINT);
    exit; // Encerra o script para evitar execução desnecessária
}

include "../app/includes/conexao_gerar.php";

//- Pesquisa se existe o e-mail na Base de dados
$sql = "SELECT P.email_corporativo as email, login, idUsuario 
            FROM rh_usuarios U 
            INNER JOIN rh_pessoas P on P.idPessoa = U.idPessoa
            WHERE P.email_corporativo like :email and U.idEmpresa = $idEmpresa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (! $stmt->rowCount() > 0) {
    $retorna = [
        "status" => false,
        "msg" => '<div class="alert alert-danger" role="alert">ERRO: e-Mail não encontrado!</div>'
    ];
    $conn = null;
    die(json_encode($retorna, JSON_PRETTY_PRINT));
} else {
    //- cria o token
    extract($dados);
    $data_solicitacao = date("Y-m-d H:i:s");
    $sql = "INSERT INTO rh_token (idEmpresa, idUsuario, email, login, data_solicitacao, tipo) 
    VALUES (:idEmpresa, :idUsuario, :email, :login, :data_solicitacao, 1)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $stmt->bindParam(':data_solicitacao', $data_solicitacao, PDO::PARAM_STR);

    $stmt->execute();

    // Obtém o último ID inserido
    $idToken = $conn->lastInsertId();
}

//- envia o link para e-Mail
//

include "../app/includes/inc_email.php";

// Inicia o buffer de saída para evitar qualquer saída inesperada
ob_start();

$emailFrom = "ti@gerar.org.br";
$nmFrom    = "GERAR SISTEMAS";
$titulo    = "RESET da sua SENHA (RH)";
//
$mail->isHTML(true);
$mail->Subject = $titulo;
$mail->setFrom($emailFrom, $nmFrom);

$mail->addAddress($email, $login);   // Add a recipient 

$resetLink = "https://rh.gerar.org.br/includes/reset_senha.php?token=$idToken"; // Substitua pelo link real

$html = "
    <p>Olá, <strong>$login</strong>,</p>
    <p>Recebemos uma solicitação para redefinir sua senha no sistema RH.</p>
    <p>Para continuar com a redefinição, clique no link abaixo:</p>
    <p><a href='$resetLink' target='_blank'>$resetLink</a></p>
    <p>Se você não solicitou esta alteração, ignore este e-mail.</p>
    <p>Atenciosamente,</p>
    <p><strong>Equipe GERAR SISTEMAS</strong></p>
";

$mail->Body = $html;

if ($mail->send()) {
    $msg = '<div class="alert alert-success"><strong>Successo: </strong> link enviado!</div>';
    $retorno = [
        "status" => true,
        "msg"    => $msg
    ];
} else{
    $msg = '<div class="alert alert-danger"><strong>ERRO: </strong> e-Mail não enviado!</div>';
    $retorno = [
        "status" => false,
        "msg"    => $msg
    ];
}

// Limpa e descarta qualquer saída acumulada
ob_end_clean();

$conn = null;

die( json_encode($retorno, JSON_PRETTY_PRINT));
