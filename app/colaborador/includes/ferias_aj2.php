<?PHP
//
// ferias_aj2.php | Salva Agendamento de Férias
// (C)haia, 21/05/2025 | 15/10/2025
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

include "inc_email.php";

/*
// TESTE DE RECEBIMENTO DE DADOS
include "../../includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
/*
 index_aj6.php | 2025-09-25 11:12:19 
{
    "id": "2",
    "agenda1": "2026-01-05",
    "dias1": "30",
    "agenda2": "",
    "dias2": "",
    "agenda3": "",
    "dias3": "",
    "obs": "teste",
    "emailSupervisor": "luiz.chaia@gerar.org.br",
    "nmSupervisor": "Ronny Essert",
    "orgao": "9 - Coordena\u00e7\u00e3o de TI",
    "idCSuper": "2"
}
*/
//
//- Verifica se os parâmetros essenciais foram recebidos
//

$erros = [];
if (empty($id)) $erros[] = "ID do contrato";
if (empty($idCSuper)) $erros[] = "Supervisor";
if (empty($agenda1)) $erros[] = "Primeira data de agendamento";
if (empty($dias1)) $erros[] = "Primeira quantidade de dias";

if (!empty($erros)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou informar: ' . implode(", ", $erros) . '.
            </div>'
    ];
    die(json_encode($retorno));
}

// normaliza para inteiro (se vier vazio vira zero)
$d1 = (int)$dias1;
$d2 = !empty($dias2) ? (int)$dias2 : 0;
$d3 = !empty($dias3) ? (int)$dias3 : 0;

// soma total
$totalDias = $d1 + $d2 + $d3;

if ($totalDias > 30) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> A soma dos dias de férias não pode ultrapassar 30 (informado: ' . $totalDias . ').
            </div>'
    ];
    die(json_encode($retorno));
}

//
//- Busca informações da Sessão
//
if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include "../../includes/conexao_gerar.php";
    include "../../includes/f_notificacoes.php";
} else {
    header("Location: ../../logout.php");
}

//
//- Busca informações da Pessoa e das férias
//
$sql = "SELECT P.nome, F.*
                FROM rh_ferias F 
                INNER JOIN rh_colaboradores C on C.idColab = F.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                WHERE F.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
//
$nome = $dados['nome'];
//
$dias_parte1 = $dados['dias_parte1'];
$dias_parte2 = $dados['dias_parte2'];
$dias_parte3 = $dados['dias_parte3'];
//
$data_parte1 = $dados['data_parte1'];
$data_parte2 = $dados['data_parte2'];
$data_parte3 = $dados['data_parte3'];
//
$aprova1 = $dados['aprova_1_em'];
$aprova2 = $dados['aprova_2_em'];
$aprova3 = $dados['aprova_3_em'];
//extract( $dados );

// Tratamento de dados
//
if (empty($agenda2)) $agenda2 = null;
if (empty($agenda3)) $agenda3 = null;
if (empty($dias2))   $dias2 = null;
if (empty($dias3))   $dias3 = null;

$sql = "UPDATE rh_ferias SET agenda_parte1 = :agenda1, dias_parte1 = :dias1, agenda_parte2 = :agenda2, 
            dias_parte2 = :dias2, agenda_parte3 = :agenda3, dias_parte3 = :dias3, observacao = :obs,
            email = :emailSupervisor, idColabSupervisor = :idSupervisor
            WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':agenda1', $agenda1);
$stmt->bindParam(':dias1', $dias1);
$stmt->bindParam(':agenda2', $agenda2);
$stmt->bindParam(':dias2', $dias2);
$stmt->bindParam(':agenda3', $agenda3);
$stmt->bindParam(':dias3', $dias3);
$stmt->bindParam(':obs', $obs);
$stmt->bindParam(':id', $id);
$stmt->bindParam(':emailSupervisor', $emailSupervisor);
$stmt->bindParam(':idSupervisor', $idCSuper);
$stmt->execute();

$linhas = $stmt->rowCount();

if ($linhas > 0) {
    $retorno = [
        "status" => true,
        "msg" => '<div class="alert alert-success">
            <strong>Sucesso!</strong> Agendamento de férias atualizado com sucesso!
            </div>'
    ];

    //
    //-- registra notificação ao RH
    //
        $idEvento = 1; //-férias
        $idTipo = 1;   //-info
        $descricao = "$nome fez Solicitação de férias pendente de aprovação.";
        $url = "";
        notificar_usuarios_evento( $idEvento, $descricao, $url, $idTipo );
//
//- Envia e-mail de confirmação
//

    ob_start();

    $mail->addAddress($emailSupervisor, $nmSupervisor); //- Destinatario
    $mail->setFrom('sistema@gerar.org.br', 'Mailer'); //- remetente
    $mail->Subject = "GERAR|RH: Agendamento de Férias - $nome"; //- assunto
    $html = "<h3>Agendamento de Férias</h3>
    <p>O colaborador <strong>$nome</strong> ($orgao) submeteu o agendamento de férias conforme abaixo:</p>";

    if (!empty($agenda1) && !empty($dias1)) {
        $inicio = DateTime::createFromFormat('Y-m-d', $data_parte1 ?: $agenda1);
        $fim = clone $inicio;
        $fim->modify('+' . ($dias1 - 1) . ' days');

        if (!empty($data_parte1)) {
            $html .= "<p><strong>Parcela 1:</strong> {$dias1} dias já usufruídos de <strong>{$inicio->format('d/m/Y')}</strong> a <strong>{$fim->format('d/m/Y')}</strong> (originalmente agendada para {$agenda1})</p>";
        } else {
            $html .= "<p><strong>Parcela 1:</strong> agendada para <strong>{$inicio->format('d/m/Y')}</strong> a <strong>{$fim->format('d/m/Y')}</strong> ({$dias1} dias)</p>";
        }
    }

    if (!empty($agenda2) && !empty($dias2)) {
        $inicio = DateTime::createFromFormat('Y-m-d', $data_parte2 ?: $agenda2);
        $fim = clone $inicio;
        $fim->modify('+' . ($dias2 - 1) . ' days');

        if (!empty($data_parte2)) {
            $html .= "<p><strong>Parcela 2:</strong> {$dias2} dias já usufruídos de <strong>{$inicio->format('d/m/Y')}</strong> a <strong>{$fim->format('d/m/Y')}</strong> (originalmente agendada para {$agenda2})</p>";
        } else {
            $html .= "<p><strong>Parcela 2:</strong> agendada para <strong>{$inicio->format('d/m/Y')}</strong> a <strong>{$fim->format('d/m/Y')}</strong> ({$dias2} dias)</p>";
        }
    }

    if (!empty($agenda3) && !empty($dias3)) {
        $inicio = DateTime::createFromFormat('Y-m-d', $data_parte3 ?: $agenda3);
        $fim = clone $inicio;
        $fim->modify('+' . ($dias3 - 1) . ' days');

        if (!empty($data_parte3)) {
            $html .= "<p><strong>Parcela 3:</strong> {$dias3} dias já usufruídos de <strong>{$inicio->format('d/m/Y')}</strong> a <strong>{$fim->format('d/m/Y')}</strong> (originalmente agendada para {$agenda3})</p>";
        } else {
            $html .= "<p><strong>Parcela 3:</strong> agendada para <strong>{$inicio->format('d/m/Y')}</strong> a <strong>{$fim->format('d/m/Y')}</strong> ({$dias3} dias)</p>";
        }
    }


    // Adiciona observações, se houver
    if (!empty($obs)) {
        $html .= "<p><strong>Observações:</strong> $obs</p>";
    }
    //
    $html .= "<p><a href='http://rh.gerar.org.br/aprova.php?id=$id'>Clique aqui para responder à solicitação!</a>";

    //
    $mail->Body = $html;
    $resposta = $mail->send();
    $mailOutput = ob_get_clean();
    if ($resposta) {
        //
        $sql = "INSERT INTO rh_emails
                (idPessoa, data, destinatario, titulo, mensagem, status, idEmpresa, idLogin)
                VALUES
                (idPessoa, data, destinatario, titulo, mensagem, 1, $idEmpresa, $idLogin)";
        $stmt = $conn->prepare($sql);
        //
        $retorno = [
            "status" => false,
            "msg" => "<div class='alert alert-success'>
                        <strong>Sucesso!</strong> Messagem enviada para: $emailSupervisor ($nmSupervisor)
                        </div>"
        ];
    } else {
        $retorno = [
            "status" => false,
            "msg" => "<div class='alert alert-danger'>
                        <strong>Erro!</strong> Messagem não pôde serenviada | ".$mail->ErrorInfo."
                        </div>"
        ];
    }
} else {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Não foi possível atualizar o agendamento de férias!
            </div>'
    ];
}

$conn = null; // Fecha a conexão
echo json_encode($retorno);
