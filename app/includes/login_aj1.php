<?php
//
//- login_aj1.php | Envia o e-Mail para CADASTRAR NOVO USUÁRIO
// (C)haia, 05/05/2025

session_start();

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

$_idColab = $_POST['idColab'] ?? null;

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
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-primary" role="alert">TESTES REALIZADOS COM SUCESSO!</div>'
];
echo json_encode($retorno, JSON_PRETTY_PRINT);
exit; // Encerra o script para evitar execução desnecessária
/*
 login_aj1.php | 2025-11-11 15:40:28 
{
    "idColab": "1",
    "nome": "LUIZ AUGUSTO CHAIA",
    "email": "luiz.chaia@gerar.org.br",
    "idEmpresa": "1"
}
*/

include "conexao_gerar.php";

//- Pesquisa se existe o e-mail na Base de dados (idEmpresa vem do próprio registro, nunca do cliente)
$sql = "SELECT idColab, email, login, idUsuario, U.idEmpresa
            FROM rh_usuarios U
            INNER JOIN rh_pessoas P on P.idPessoa = U.idPessoa
            WHERE email_corporativo like :email
                    and U.idColab = :idColab";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':idColab', $idColab, PDO::PARAM_STR);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if( ! $dados ){
        $retorna = [
            "status" => false,
            "msg" => '<div class="alert alert-danger" role="alert">ERRO: e-Mail Não Cadastrado!</div>'
        ];
        $conn = null;
        die(json_encode($retorna, JSON_PRETTY_PRINT));
    }

    //- cria o token (segredo aleatório, não sequencial)
    extract($dados);
    $tokenSecreto = bin2hex(random_bytes(32));
    $data_solicitacao = date("Y-m-d H:i:s");
    $sql = "INSERT INTO rh_token (token, idEmpresa, idUsuario, email, login, data_solicitacao, tipo)
    VALUES (:token, :idEmpresa, :idUsuario, :email, :login, :data_solicitacao, 2)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':token', $tokenSecreto, PDO::PARAM_STR);
    $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $stmt->bindParam(':data_solicitacao', $data_solicitacao, PDO::PARAM_STR);

    $stmt->execute();

//- envia o link para e-Mail
//

include "inc_email.php";

// Inicia o buffer de saída para evitar qualquer saída inesperada
ob_start();

$emailFrom = "ti@gerar.org.br";
$nmFrom    = "GERAR SISTEMAS";
$titulo    = "RH: Sua solicitação de Cadastro";
//
$mail->isHTML(true);
$mail->Subject = $titulo;
$mail->setFrom($emailFrom, $nmFrom);

$mail->addAddress($email, $login);   // Add a recipient 

$link = "https://rh.gerar.org.br/includes/cadastrar_senha.php?token=$tokenSecreto&id=$_idColab"; // Substitua pelo link real

$html = "
    <p>Olá, <strong>$login</strong>,</p>
    <p>Recebemos uma solicitação para CADASTRAR seu acesso ao sistema RH.</p>
    <p>Para continuar com a solicitação, clique no link abaixo:</p>
    <p><a href='$link' target='_blank'>$link</a></p>
    <p>Se não foi você que solicitou: ignore este e-mail.</p>
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
