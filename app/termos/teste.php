<?PHP
$termo_email = "luiz.chaia@gerar.org.br";
$termo_inc_nome = "LUIZ AUGUSTO CHAIA";
$token = bin2hex(random_bytes(16));
$expira = date("Y-m-d H:i:s", strtotime("+3 days"));
$link = "https://rh.gerar.org.br/assinar.php?token=$token";

include_once "../includes/inc_email.php";

$emailFrom = "ti@gerar.org.br";
$nmFrom    = "GERAR SISTEMAS";
$titulo    = "Termo de Responsabilidade - Assinatura";
//
$mail->isHTML(true);
$mail->Subject = $titulo;
$mail->setFrom($emailFrom, $nmFrom);

$mail->addAddress($termo_email, $termo_inc_nome);   // Add a recipient 
//$mail->addAddress( "ti@gerar.org.br", 'TI');   // Add a recipient 

$html_email = "Olá, clique no link abaixo para assinar o termo:<br><br><a href='$link'>$link</a>";

$mail->Body = $html_email;
$mail->send();
