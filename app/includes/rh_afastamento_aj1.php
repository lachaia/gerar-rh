<?php
//
//- rh_afastamento_aj1.php | Salva Registro do Afastamento
//- (C)haia, 31/07/2025
//

session_start();

$idModulo = 17; // Afastamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

require_once "email_config.php"; //- Importa configurações de e-mail

/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO"];
die(json_encode($response));
/*
 rh_afastamento_aj1.php | 2025-08-13 13:56:03 
{
    "afa_nome": "Luiz Augusto Chaia",
    "afa_idColab": "1",
    "afa_idPessoa": "1",
    "afa_data": "2025-08-13",
    "afa_qtd": "3",
    "afa_dtRetorno": "2025-08-16",
    "idTipo": "1",
    "afa_emitidoPor": "DR FRANK STEIN, CRM 1212",
    "afa_cid": "G11"
}}
*/

// Validação básica
if (!isset($afa_idColab, $afa_data, $afa_qtd, $idTipo, $afa_emitidoPor, $afa_dtRetorno)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Todos os Campos são necessários!</div>";
    $response = ["status" => false, "msg" => $msg];
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $idUsuario = $_SESSION['idUsuario'];
    //
    $nmSupervisor = $_SESSION['nmSupervisor'];
    $emailSupervisor = $_SESSION['emailSupervisor'];
    //
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    include_once "f_linha_do_tempo.php";
    include_once "f_notificacoes.php";
    //
} else {
    header("Location: ../logout.php");
}

$rh_por = null;
$rh_em  = null;
$dsStatus = "Gestor";

if (isset($origem) && !empty($origem) && $origem == "RH") {
    //-Já Salva o Afastamento Aprovado!!
    $dsStatus = "Aprovado";
    $rh_por = $_SESSION['nmLogin'];
    $rh_em  = date('Y-m-d H:i:s');
}
if (empty($afa_cid)) $afa_cid = null; // porque CID é opcional.

$sql = "INSERT INTO rh_afastamentos ( idColab, idTipo, data_inicio, dias_afastado, data_retorno, cid, 
            emitido_por, idLogin, idUsuario, status, rh_por, rh_em )
            VALUES ( :idColab, :idTipo, :data_inicio, :dias_afastado, :data_retorno, :cid, 
            :emitido_por, :idLogin, :idUsuario, :status, :rh_por, :rh_em )";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab',       $afa_idColab, PDO::PARAM_INT);
$stmt->bindParam(':idTipo',        $idTipo, PDO::PARAM_INT);
$stmt->bindParam(':data_inicio',   $afa_data, PDO::PARAM_STR);
$stmt->bindParam(':dias_afastado', $afa_qtd, PDO::PARAM_INT);
$stmt->bindParam(':data_retorno',  $afa_dtRetorno, PDO::PARAM_STR);
$stmt->bindParam(':cid',           $afa_cid, PDO::PARAM_STR);
$stmt->bindParam(':emitido_por',   $afa_emitidoPor, PDO::PARAM_STR);
$stmt->bindParam(':idLogin',       $idLogin, PDO::PARAM_INT);
$stmt->bindParam(':idUsuario',     $idUsuario, PDO::PARAM_INT);
$stmt->bindParam(':status',        $dsStatus, PDO::PARAM_STR);
$stmt->bindParam(':rh_por',        $rh_por, PDO::PARAM_STR);
$stmt->bindParam(':rh_em',         $rh_em, PDO::PARAM_STR);

// Executa a inserção
if ($stmt->execute()) {
    $idAfastamento = $conn->lastInsertId();
    //
    // INSERE ARQUIVO - Se houver
    //

    if (isset($_FILES['afa_arquivo']) && $_FILES['afa_arquivo']['error'] === 0) {
        //
        $nome_original = $_FILES['afa_arquivo']['name']; //- Nome original
        $tamanho = $_FILES['afa_arquivo']['size'];       // Tamanho do arquivo em bytes
        $tmp = $_FILES['afa_arquivo']['tmp_name']; // Caminho temporário
        $ext = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION)); // Extensão do arquivo (segura e sem pontos)

        $agora = date("YmdHis");

        // Gera nome único para evitar sobrescrita
        $nome_final = "atestado_$afa_idColab" . "_" . $agora . "." . $ext;

        // Define diretório de destino
        $diretorio = "../docs/pessoa_$afa_idPessoa/";
        $destino = $diretorio . $nome_final;

        // Verifica se o diretório existe; se não, cria
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true); // cria recursivamente com permissão total
        }

        // Move o arquivo
        if (move_uploaded_file($tmp, $destino)) {
            //
            // ATUALIZA o nome do arquivo na tabela de reuniões
            //
            $sqlUpdate = "UPDATE rh_afastamentos SET arquivo = :arquivo WHERE id = :idAfastamento";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':arquivo', $nome_final, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':idAfastamento', $idAfastamento, PDO::PARAM_INT);
            $stmtUpdate->execute();

            //
            //- INSERE na tabela DOCUMENTOS
            //
            $idTipoDoc = 27; //- Atestado Médico
            //
            $data = new DateTime($afa_data);
            $data->modify('+10 years');
            $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
            //
            $tags = '#Afastamento #Atestado Médico';
            //
            $texto_ocr = ocr($destino);
            $texto_ocr = "$nome_original | " . addslashes($texto_ocr);
            //
            $assunto = "Atestado Médico de $afa_nome de $afa_qtd dias";
            $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, descricao, data_validade, arquivo, 
                            nome_original, ocr, tags, extensao, tamanho, status, idLoginAprova, origem)
                            VALUES
                            ($idEmpresa, $afa_idPessoa, $idTipoDoc, '$afa_data', '$assunto', '$dataMais10Anos', '$nome_final', '$nome_original', 
                            '$texto_ocr', '$tags', '$ext', $tamanho, 1, $idLogin, 'AFA')";
            //debug( $sql );
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            //

        } else {
            echo json_encode(['msg' => 'Erro ao mover o arquivo.']);
        }
    } else {
        echo json_encode(['msg' => 'Nenhum arquivo enviado ou erro no upload.']);
    }
    //
    //- INCLUI LINHA DO TEMPO 
    //
        $tipo = 35; // Atestado Médico
        $descricao = "Atestado de Afastamento de $afa_qtd dias em $afa_data";
        f_ldt($tipo, $afa_idPessoa, $descricao, $idAfastamento, null, 'AFA');
    //
    //- INCLUIR MENSAGEM ao RH
    //
        $idEvento = 3; //- Afastamento
        $mensagem = "Atestado Médico de $afa_nome de $afa_qtd dias";
        $link = "";  //- sem url
        $idTipo = 1; //- Info
        notificar_usuarios_evento($idEvento, $mensagem, $link, $idTipo);
    //
    //- ENVIA E-MAIL PARA SUPERVISOR APROVAR
    //
    if ($dsStatus == "Gestor") {
        //
        $vetorSupervisor = supervisor( $afa_idColab ); // Busca o Supervisor
        //
        $emailSupervisor = $vetorSupervisor['emailSupervisor'];
        $nmSupervisor    = $vetorSupervisor['nmSupervisor'];
        if( ! empty($emailSupervisor) ){
            ob_start();
            $mail = criarMailer();
            $mail->addAddress($emailSupervisor, $nmSupervisor); //- Destinatario
            $mail->Subject = 'Envio de Atestado de Saúde';
            $html = "<h3>Envio de Atestado de Saúde</h3>
                <p>Prezado(a) Gestor(a), o colaborador <strong>$afa_nome</strong> enviou o atestado de saúde nesta data.</p>
                <p>Favor entrar na sua área de trabalho (como Gestor) para validar.</p>
                <p>Atenciosamente</p>
                <p>Equipe RH</p>";
            $mail->Body = $html;
            $resposta = $mail->send();
            $mailOutput = ob_get_clean();            
        }
    }
    //
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idAfastamento</div>";
    $response = ["status" => true, "msg" => $msg];
    //
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}

$dados = implode(", ", $parametros);
f_log("INC", "INCLUSÃO de Atestado de Afastamento: Dados( $dados )", "rh_afastamentos", $idModulo, $idAfastamento);

$conn = null;
die(json_encode($response));


function ocr($origem)
{
    //
    //- Função para extrair texto de documentos - com cURL
    //
    $url = 'http://rh.gerar.org.br/ocr_gerar.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário

    // Prepara o arquivo para envio
    $file = curl_file_create($origem, mime_content_type($origem), basename($origem));

    // Configura os parâmetros POST
    $postParams = [
        'arquivo' => $file,
    ];

    // Configura as opções da solicitação cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: multipart/form-data',
    ]);
    //
    $response = curl_exec($ch); // Executa a solicitação cURL e obtém a resposta
    //
    // Verifica se ocorreu algum erro na solicitação cURL
    if (curl_errno($ch)) {
        echo 'Erro na solicitação cURL: ' . curl_error($ch);
    } else {
        //echo 'Resposta cURL: ' . $response;
        curl_close($ch); // Fecha a sessão cURL
        return $response;
    }
}

function supervisor($idColab = null)
{
    global $conn;

    if (empty($idColab)) {
        return null;
    }

    // Busca o órgão e nível do colaborador
    $sql = "SELECT 
                O.idOrgao, 
                O.descricao AS dsOrgao, 
                O.idSupervisor, 
                O.nivel,
                (SELECT nivel FROM rh_organograma WHERE idOrgao = O.idSupervisor) AS nivelSupervisor
            FROM rh_colaboradores C 
            INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
            LEFT JOIN rh_organograma O ON O.idOrgao = C.idOrgao
            WHERE idColab = :idColab";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$linha) {
        return null;
    }

    extract($linha);

    // Monta a query para buscar o supervisor
    if ($nivel == $nivelSupervisor) {
        $sql = "SELECT 
                    C.idColab AS idColabSupervisor, 
                    P.nome AS nmSupervisor, 
                    P.email_corporativo AS emailSupervisor
                FROM rh_colaboradores C
                INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                WHERE idOrgao = :idOrgao AND dcLider = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idOrgao', $idOrgao, PDO::PARAM_INT);
    } else {
        $sql = "SELECT 
                    C.idColab AS idColabSupervisor, 
                    P.nome AS nmSupervisor, 
                    P.email_corporativo AS emailSupervisor
                FROM rh_colaboradores C
                INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                WHERE idOrgao = :idOrgao";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idOrgao', $idSupervisor, PDO::PARAM_INT);
    }

    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retorna o array de dados do supervisor (ou valores nulos)
    if ($linha) {
        return [
            "idColabSupervisor" => $linha["idColabSupervisor"],
            "nmSupervisor"      => $linha["nmSupervisor"],
            "emailSupervisor"   => $linha["emailSupervisor"]
        ];
    } else {
        return [
            "idColabSupervisor" => null,
            "nmSupervisor"      => null,
            "emailSupervisor"   => null
        ];
    }
}
