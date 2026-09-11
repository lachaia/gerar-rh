<?PHP
//
// ferias_aj2.php | Salva APROVAÇÃO de Férias pelo SUPERVISOR
// (C)haia, 29/09/2025
//

session_start();

include_once "../../includes/parametros.php";

$idModulo = 16; // Portal do Gestor

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

//-- PARCELAS SELECIONADAS PARA APROVAÇÃO
$parcelas = isset($_POST['parcelas']) ? explode(',', $_POST['parcelas']) : [];
$parcelas_desmarcadas = isset($_POST['parcelas_desmarcadas']) ? explode(',', $_POST['parcelas_desmarcadas']) : [];


include "../../includes/conexao_gerar.php";

$agora = date('Y-m-d H:i:s');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../../includes/email_config.php';

$mail = criarMailer();

/*
// TESTE DE RECEBIMENTO DE DADOS
include "../includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
/*
 ferias_aj2.php | 2025-09-29 15:26:04 
{
    "idFerias": "2",
    "idGestor": "2",
    "senha": "Gerar2025",
    "parcelas": "1,2",
    "parcelas_desmarcadas": "3",
    "motivoNaoAprovacao": "nesse periodo teremos planejamento orçamentário"
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
$stmt->bindParam(':idColab', $idGestor, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract($dados);

$parcelas = explode(',', $_POST['parcelas']);
$parcelas = array_map('trim', $parcelas);      // remove espaços
$parcelas = array_map('intval', $parcelas);    // garante inteiros

if (password_verify($senha, $hash_salvo)) {
    $campos = [];

    if (in_array(1, $parcelas)) {
        $campos[] = "aprova_1_por = '$login'";
        $campos[] = "aprova_1_em = '$agora'";
    }

    if (in_array(2, $parcelas)) {
        $campos[] = "aprova_2_por = '$login'";
        $campos[] = "aprova_2_em = '$agora'";
    }

    if (in_array(3, $parcelas)) {
        $campos[] = "aprova_3_por = '$login'";
        $campos[] = "aprova_3_em = '$agora'";
    }

//
//-- atualiza as DESMARCADAS
//
    // Parcelas desmarcadas
    $parcelas_desmarcadas = isset($_POST['parcelas_desmarcadas']) && !empty($_POST['parcelas_desmarcadas']) 
        ? explode(',', $_POST['parcelas_desmarcadas']) 
        : [];

    // Se tiver alguma parcela desmarcada
    if (!empty($parcelas_desmarcadas)) {
        foreach ($parcelas_desmarcadas as $p) {
            $p = (int)$p; // segurança: força pra inteiro

            // Exemplo: zerar os campos da parcela desmarcada
            $sql = "UPDATE rh_ferias 
                    SET dias_parte{$p} = NULL, 
                        agenda_parte{$p} = NULL, 
                        aprova_{$p}_por = NULL, 
                        aprova_{$p}_em = NULL
                    WHERE id = :idFerias";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':idFerias', $idFerias, PDO::PARAM_INT);
            $stmt->execute();
        }
        //
        //- Registra motivo da não aprovação
        //
        $idUsuario = $_SESSION['idUsuario'];
        $motivo = isset($_POST['motivoNaoAprovacao']) ? $_POST['motivoNaoAprovacao'] : '';
        $sql = "INSERT INTO rh_ferias_repro (idFerias, motivo, idUsuario, criado_em) 
                    VALUES ( :idFerias, :motivo, :idUsuario, :dataHora )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idFerias', $idFerias, PDO::PARAM_INT);
        $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
        $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(':dataHora', $agora, PDO::PARAM_STR);
        $stmt->execute();
        //
    }

    if (!empty($campos)) {
        $sql = "UPDATE rh_ferias SET " . implode(', ', $campos) . " WHERE id = $idFerias";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        //
        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-primary">
                <strong>APROVADO!</strong>
            </div>'
        ];
        //-- ENVIA E-MAIL DE NOTIFICAÇÃO AO COLABORADOR SOLICITANTE
        //

            $textoParcelas = implode(", ", array_map(function($n) {
                return "Parcela {$n}";
            }, $parcelas));

        ob_start();

        $mail->addAddress($emailColaborador, $nmColaborador); //- Destinatario
        $mail->setFrom('rh@gerar.org.br', 'Mailer'); //- remetente

        $mail->Subject = "GERAR|RH: Aprovação de Férias - $nmColaborador"; // assunto

        $html = "<h3>Agendamento de Férias</h3>
        <p>Prezado(a) Senhor(a) <strong>$nmColaborador</strong>, 
        informamos que seu supervisor aprovou a(s) seguinte(s) parcela(s) de férias: <strong>$textoParcelas</strong>.</p>";
        if (!empty($parcelas_desmarcadas)) {
            $textoDesmarcadas = implode(", ", array_map(function($n) {
                return "Parcela {$n}";
            }, $parcelas_desmarcadas));
            $html .= "<p>As seguintes parcelas NÃO foram aprovadas: <strong>$textoDesmarcadas</strong>.</p>";
            if (!empty($motivo)) {
                $html .= "<p><strong>Motivo da não aprovação:</strong> $motivo</p>";
            }
        }
        $html .="<br><p>Atenciosamente</p>
        <br><p>Equipe RH</p>";

        $mail->Body = $html;
        $resposta = $mail->send();

        //-- Envia E-MAIL AO RH informando da Aprovação
        //
        $mail->addAddress( $e_mail_rh, "RH"); //- Destinatario (SUBSTITUIR PELO RH)
        $mail->setFrom('rh@gerar.org.br', 'Mailer'); //- remetente
        $mail->Subject = "GERAR|RH: Agendamento de Férias - $nmColaborador"; //- assunto

        $html = "<h3>Agendamento de Férias</h3>
        <p>Informamos que o Supervisor <strong>$nmSupervisor</strong> aprovou a(s) seguinte(s) parcela(s): <strong>$textoParcelas</strong> do colaborador abaixo identificado:</p>
        <ul>
            <li><strong>ID Férias:</strong> $idFerias</li>    
            <li><strong>ID Colaborador:</strong> $idColaborador</li>
            <li><strong>Colaborador:</strong> $nmColaborador</li>                    
            <li><strong>E-mail:</strong> $emailColaborador</li>
        </ul>";

        $mail->Body = $html;
        //$resposta = $mail->send();

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

