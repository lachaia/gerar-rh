<?php
//
//- rh_autocadastro_aj2.php | Envia o formulário para o novo colaborador
// (C)haia, 23/10/2025;
//  

session_start();    

$idModulo = 21; //-Autocadastro

include_once "../includes/conexao_gerar.php";

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

$idLogin = $_SESSION['idLogin'];

include_once "email_config.php";

ob_start(); // <-- inicia o buffer de saída AQUI

/*
include "../includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT)  );
/*
 rh_autocadastro_aj2.php | 2025-10-23 11:09:53 
{
    "nome": "luiz augusto chaia",
    "email": "lachaia@gmail.com"
}
*/

$agora = date("Y-m-d H:i:s");
$expira = date("Y-m-d H:i:s", strtotime("+2 day"));

$token = bin2hex(random_bytes(16));

//
//- ENVIA O EMAIL
//

$mail = criarMailer();

try {
    $mail->addAddress($email, $nome);
    $mail->Subject = 'Formulário de Dados Iniciais';
    $mail->Body    = '
        <h2>Bem-vindo à GERAR!</h2>
        <p>Por favor, preencha seu formulário de dados iniciais através do link abaixo:</p>
        <p><a href="https://rh.gerar.org.br/autocadastro.php?token='.$token.'">
            Clique aqui para preencher</a></p>';
    $mail->AltBody = "Bem-vindo à GERAR! Acesse o formulário pelo link: https://rh.gerar.org.br/autocadastro.php?token=$token";

    $mail->send();
    $msg = 'E-mail enviado com sucesso!';
} catch (Exception $e) {
    $msg = "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}

ob_end_clean(); // <-- encerra e limpa o buffer, eliminando qualquer saída inesperada

$sql = "INSERT INTO rh_autocadastro_ctr (data, nome, email, token, expira, status, idLogin) 
            VALUES ('$agora', '$nome', '$email', '$token', '$expira','Enviado', $idLogin)";
$stmt = $conn->prepare($sql);
$stmt->execute();

echo $msg; 