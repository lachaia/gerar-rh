<?PHP
//
// aprova_aj.php | Salva REPROVAÇÃO de Férias pelo SUPERVISOR
// (C)haia, 30/05/2025
//

session_start();

$idModulo = 13; // Módulo do Colaborador

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
} else {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

include "includes/conexao_gerar.php";
include "includes/debug.php";

$agora = date('Y-m-d H:i:s');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer-master/src/Exception.php';
require 'includes/PHPMailer-master/src/PHPMailer.php';
require 'includes/PHPMailer-master/src/SMTP.php';

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

/*
// TESTE DE RECEBIMENTO DE DADOS
include "includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
/*
reprova_aj.php | 2025-06-13 14:22:31 
{
    "idFerias": "2",
    "idSupervisor": "11",
    "senha": "asdf",
    "motivo": "asdf",
    "parcelas": "2"
}
*/
//die();

//- e-Mail do Colaborador que solicitou as férias
//
$sql = "SELECT F.idColab as idColaborador, P.email_corporativo as emailColaborador, P.nome as nmColaborador
                FROM RH.rh_ferias F
                INNER JOIN rh_colaboradores C on C.idColab = F.idColab 
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                where F.id=$idFerias";
$stmt = $conn->prepare($sql);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract($dados);

//- Login & Senha do Supervisor
//
$sql = "SELECT login, senha as hash_salvo 
                FROM rh_usuarios 
                WHERE idColab = :idColab";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab', $idSupervisor, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract($dados);

$parcelas = explode(',', $_POST['parcelas']);
$parcelas = array_map('trim', $parcelas);      // remove espaços
$parcelas = array_map('intval', $parcelas);    // garante inteiros

if (password_verify($senha, $hash_salvo)) {
    $campos = [];

    $quais = "";

if (in_array(1, $parcelas)) {
    $campos[] = "agenda_parte1 = NULL";
    $campos[] = "dias_parte1 = NULL";
    $quais .= " 1ºPeríodo ";
}

if (in_array(2, $parcelas)) {
    $campos[] = "agenda_parte2 = NULL";
    $campos[] = "dias_parte2 = NULL";
    $quais .= " 2ºPeríodo ";
}

if (in_array(3, $parcelas)) {
    $campos[] = "agenda_parte3 = NULL";
    $campos[] = "dias_parte2 = NULL";
    $quais .= " 3ºPeríodo ";
}


    if (!empty($campos)) {
        //
        $sql = "UPDATE rh_ferias SET " . implode(', ', $campos) . " WHERE id = $idFerias";
        debug( $sql );
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        //
        $sql = "INSERT INTO rh_ferias_repro (idFerias, motivo, idColabSupervisor) 
                    VALUES ( $idFerias, '$motivo', $idSupervisor )";
        $stmt = $conn->prepare($sql);
        $stmt->execute();        
        //
        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-primary">
                <strong>REPROVADO!</strong>
            </div>'
        ];
        //-- ENVIA E-MAIL DE NOTIFICAÇÃO AO COLABORADOR SOLICITANTE
        //

        ob_start();
        $mail->addAddress($emailColaborador, $nmColaborador); //- Destinatario
        $mail->setFrom('rh@gerar.org.br', 'Mailer'); //- remetente
        $mail->Subject = "GERAR|RH: Agendamento de Férias - $nmColaborador"; //- assunto
        $html = "<h3>Agendamento de Férias</h3>
                <p>Prezado(a) Senhor(a) <strong>$nmColaborador</strong>, 
                informamos que seu supervisor NÃO aprovou sua solicitação de férias!</p>
                <p>Períodos: $quais</p>
                <p>Motivo: <strong>$motivo</strong></p>
                <p>Por favor, entre em contato com o seu supervisor para mais detalhes.</p>
                <br><p>Atenciosamente</p>
                <br><p>Equipe RH</p>";
        $mail->Body = $html;
       // $resposta = $mail->send();


        $mailOutput = ob_get_clean();
        //
    } else {
        // Nenhuma parcela válida informada
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>ERRO!</strong> Nenhuma parcela válida para aprovar.
            </div>'
        ];
    }
    //
} else {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-primary">
            <strong>Erro!</strong> SENHA INVÁLIDA!
            </div>'
    ];
}

die(json_encode($retorno));
