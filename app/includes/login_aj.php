<?php
//
//- login_aj.php | Envia o e-Mail para Reset de Senha
// (C)haia, 11/02/2025

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

include "conexao_gerar.php";

//- Pesquisa se existe o e-mail na Base de dados (idEmpresa vem do próprio registro, nunca do cliente)
$sql = "SELECT P.email_corporativo as email, login, idUsuario, U.idEmpresa
            FROM rh_usuarios U
            INNER JOIN rh_pessoas P on P.idPessoa = U.idPessoa
            WHERE P.email_corporativo like :email";
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
    //- cria o token (segredo aleatório, não sequencial)
    extract($dados);
    $tokenSecreto = bin2hex(random_bytes(32));
    $data_solicitacao = date("Y-m-d H:i:s");
    $sql = "INSERT INTO rh_token (token, idEmpresa, idUsuario, email, login, data_solicitacao, tipo)
    VALUES (:token, :idEmpresa, :idUsuario, :email, :login, :data_solicitacao, 1)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':token', $tokenSecreto, PDO::PARAM_STR);
    $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $stmt->bindParam(':data_solicitacao', $data_solicitacao, PDO::PARAM_STR);

    $stmt->execute();
}

//- envia o link para e-Mail
//

include "inc_email.php";

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

$resetLink = "https://rh.gerar.org.br/includes/reset_senha.php?token=$tokenSecreto"; // Substitua pelo link real

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
