<?php
//
//- g_docs_edt_aj.php | Grava Alterações de Formulário no BD | EDIÇÃO de Registro do DOCUMENTO
//- (C) 2024-03-08 by Chaia.
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";
include_once "../includes/f_upload_seguro.php";

$idModulo  = 32;  //- GED
$agora     = date("Y-m-d H:i:s");

$nome_original = "";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if($dados) extract($dados);

// idUsuario/idLogin/idGrupo reafirmados DEPOIS do extract(): esse
// extract() roda por cima de qualquer chave do POST, então sem isso um
// POST forjado poderia sobrescrever identidade (ex.: falsificar quem fez
// a alteração no log). idPessoa fica de fora de propósito — este arquivo
// já implementa "troca de proprietário" do documento via POST, decisão
// confirmada com o usuário.
$idUsuario = $_SESSION['idUsuario'];
$idLogin   = $_SESSION['idLogin'  ];
$idGrupo   = $_SESSION['idGrupo'  ];

// idDoc/idPessoa precisam ser inteiros: idDoc vai para SQL, idPessoa
// monta caminho de arquivo em disco ("../docs/pessoa_$idPessoa") — uma
// string ali seria path traversal.
$idDoc    = (int) ($idDoc ?? 0);
$idPessoa = (int) ($idPessoa ?? 0);
if ($idDoc <= 0 || $idPessoa <= 0) {
    $conn = null;
    die(json_encode(["status" => false, "msg" => "Documento ou pessoa inválidos."]));
}

$mensagemSucesso = '<div class="alert alert-success text-center">Informações registradas com <b>sucesso</b>.</div > ';
$mensagemErro    = '<div class="alert alert-danger text-center"><strong>Erro!</strong> Não foi possível processar a requisição!</div > ';
/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
die(json_encode(["status" => true, "msg" => $mensagemErro]));
/*
rh_docs_edt_aj.php | 2025-12-11 10:25:46 
{
    "idDoc": "223",
    "nmPessoa": "LUIZ AUGUSTO CHAIA",
    "idPessoa": "1",
    "ed_dataDoc": "2025-12-10",
    "idTipo": "29",
    "doc_descricao": "Aviso de Férias",
    "tags": "Aviso | Férias",
    "dsTipoDoc": "",
    "alt_ocr": "2025-12-10 - LACHAIA - Aviso Previo de Ferias - LUIZ AUGUSTO CHAIA .pdf | AVISO DE FERIAS\r\nSr. LUIZ AUGUSTO CHAIA\r\nC.T.P.S.: 05276 Serie: 0009\r\nCURITIBA, 5 de Dezembro de 2025\r\nNos termos das disposicoes legais vigentes, suas ferias\r\nser\u00e3o concedidas conforme o demonstrativo abaixo:\r\nPeriodo Aquisitivo.\r\nPeriodo de Gozo..\r\nRetorno ao trabalho....:\r\n: 05\/04\/2024\r\n-\r\n: 05\/01\/2026\r\n04\/04\/2025\r\n03\/02\/2026\r\n04\/02\/2026\r\nA remuneracao correspondente as ferias, e se for o caso, ao\r\nabono pecuniario e ao adiantamento da gratificacao de natal\r\nencontra-se no caixa e podera ser recebida em 02\/01\/2026.\r\nFavor apresentar a sua Carteira de Trabalho e Previdencia\r\nSocial\r\nDepartamento de Pessoal para as anotacoes\r\nao\r\nnecessarias.\r\nGERAR GERACAO DE EMPREGO RENDA E APOIO AO\r\nDESENVOLVIMENTO REGIONAL\r\nLUIZ AUGUSTO CHAIA\r\n#b4e71711-9f0d-445a-9d8f-3fb7cc2d7dd2\r\nComprovante de Assinatura Eletr\u00f4nica\r\ncontraktor\r\nntp.br\r\nDatas e hor\u00e1rios baseados no fuso hor\u00e1rio (GMT -3:00) em Bras\u00edlia, Brasil\r\nSincronizado com o NTP.br e Observat\u00f3rio Nacional (ON)\r\nCertificado de assinatura gerado em 10\/12\/2025 \u00e0s 16:56:06 (GMT -3:00)\r\nAviso Pr\u00e9vio de F\u00e9rias - LUIZ AUGUSTO CHAIA\r\nID \u00fanica do documento: #b4e71711-9f0d-445a-9d8f-3fb7cc2d7dd2\r\nHash do documento original (SHA256): 30053125410EAA1D2669DC92E3F6E33CFBD055665EF273599ABF846A99F8940F\r\nEste Log \u00e9 exclusivo ao documento n\u00famero #b4e71711-9f0d-445a-9d8f-3fb7cc2d7dd2 e deve ser considerado parte do mesmo, com\r\nos efeitos prescritos nos Termos de Uso.\r\nAssinaturas (2)\r\nWANDERSON ANGELICO (Contratante)\r\nAssinou em 10\/12\/2025 \u00e0s 10:40:47 (GMT -3:00)\r\nLUIZ AUGUSTO CHAIA (Contratada)\r\nAssinou em 10\/12\/2025 \u00e0s 16:56:06 (GMT -3:00)\r\nHist\u00f3rico completo\r\nData e hora\r\n10\/12\/2025 \u00e0s 10:06:35\r\n(GMT -3:00)\r\n10\/12\/2025 \u00e0s 10:40:47\r\n(GMT -3:00)\r\n10\/12\/2025 \u00e0s 16:56:06\r\n(GMT -3:00)\r\nEvento\r\nRecursos Humanos GERAR solicitou as assinaturas.\r\nWANDERSON ANGELICO (CPF 849.387.499-04; E-mail\r\nwanderson.angelico@gerar.org.br; IP 189.112.64.46), assinou via email.\r\nAutenticidade deste documento poder\u00e1 ser verificada em https:\/\/\r\nverificador.contraktor.com.br. Assinatura com validade jur\u00eddica conforme\r\nMP 2.200-2\/01, Art. 100, \u00a72.\r\nLUIZ AUGUSTO CHAIA (CPF 571.606.009-91; E-mail\r\nluiz.chaia@gerar.org.br; IP 189.112.64.46), assinou via email.\r\nAutenticidade deste documento poder\u00e1 ser verificada em https:\/\/\r\nverificador.contraktor.com.br. Assinatura com validade jur\u00eddica conforme\r\nMP 2.200-2\/01, Art. 100, \u00a72.\r\nP\u00e1gina 1 de 2\r\nComprovante de Assinatura Eletr\u00f4nica\r\nData e hora\r\n10\/12\/2025 \u00e0s 16:56:06\r\n(GMT -3:00)\r\nEvento\r\nDocumento assinado por todos os participantes.\r\ncontraktor\r\ncontraktor\r\n#b4e71711-9f0d-445a-9d8f-3fb7cc2d7dd2\r\nP\u00e1gina 2 de 2\r\nDocumento assinado eletronicamente, conforme MP 2.200-2\/01, Art. 100, \u00a72.\r\n"
}
*/
//
//- Guarda dados Antigos
//
    $sql = "SELECT * FROM rh_documentos WHERE idDoc = :idDoc";
    $res = $conn->prepare($sql);
    $res->bindParam(':idDoc', $idDoc, PDO::PARAM_INT);
    $res->execute();
    $_dados = $res->fetch(PDO::FETCH_ASSOC);
    if (!$_dados) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => "Documento não encontrado."]));
    }
    $dadosAntigos = "Dados Antigos: " . implode(', ', $_dados);
    $dadosAntigos = addslashes( $dadosAntigos );

//
//- Atualiza dados Gerais
//
try {
    // Prepare the SQL statement
    $sql = "UPDATE rh_documentos SET 
                idTipoDoc = :idTipoDoc, 
                descricao = :descricao, 
                data = :data, 
                tags = :tags, 
                ocr = :ocr
            WHERE idDoc = :idDoc";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':idTipoDoc', $idTipo, PDO::PARAM_INT);
    $stmt->bindParam(':descricao', $doc_descricao, PDO::PARAM_STR);
    $stmt->bindParam(':data', $ed_dataDoc, PDO::PARAM_STR);
    $stmt->bindParam(':tags', $tags, PDO::PARAM_STR);
    $stmt->bindParam(':idDoc', $idDoc, PDO::PARAM_INT);
    $stmt->bindParam(':ocr', $alt_ocr, PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();
    $mensagem = $mensagemSucesso;
    $status = true;
    //
} catch (PDOException $e) {
    $status = false;
    $mensagem = $mensagemErro . " | " . $e->getMessage();
}

//
//-- TROCA ARQUIVO FÍSICO
//
if ( ! empty($_FILES['doc_arquivo']['tmp_name'])) {
    //
    // rh_docs_inc_aj.php (upload de documento novo) já validava extensão +
    // MIME real; esta troca de arquivo físico não validava nada.
    $validacao = upload_seguro_validar($_FILES['doc_arquivo'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    if ($validacao !== true) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => $validacao]));
    }

    $diretorio = "../docs/pessoa_$idPessoa";
    //
    $nome_original = $_FILES['doc_arquivo']['name'];
    $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);
    $file_tmp = $_FILES['doc_arquivo']['tmp_name'];
    
    // nome único para o arquivo
    $arquivo = "doc_" . date("ymdHis") . "_" . uniqid() . "." . $extensao;
    
    // Move o arquivo para o diretório
    $caminho_arquivo = $diretorio . '/' . $arquivo;
    $target_file = $caminho_arquivo;
    
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    if (move_uploaded_file($file_tmp, $target_file)) {
        //
        if( empty($alt_ocr) ){
            $texto_ocr = ocr($target_file);
        }
        else{
            $texto_ocr = $alt_ocr;
        }        
        $texto_ocr = "$nome_original | " . addslashes($texto_ocr);
        //
        //- ATUALIZA tabela com o novo nome
        $sql = "UPDATE rh_documentos SET nome_original=:nome_original, arquivo=:arquivo, extensao=:extensao, ocr=:ocr
                WHERE idDoc = :idDoc";
        $stmt_files = $conn->prepare($sql);
        $stmt_files->execute([
            ':nome_original' => $nome_original,
            ':arquivo'       => $arquivo,
            ':extensao'      => $extensao,
            ':ocr'           => $texto_ocr,
            ':idDoc'         => $idDoc,
        ]);
        //
        //-- exclui arquivo anterior
        $arquivoAntigo = "../docs/pessoa_$idPessoa/" . basename($_dados['arquivo'] ?? '');
        if (file_exists($arquivoAntigo)) {
            unlink( $arquivoAntigo );
        }
        //
    } else {
        $status = false;
        //header('HTTP/1.0 403 Forbidden');
        error_log("Erro ao mover arquivo: " . print_r(error_get_last(), true));
        if (!is_writable($diretorio)) {
            error_log("Diretório não gravável: $diretorio");
        }
        $mensagem = '<div class="alert alert-danger text-center"><strong>Erro!</strong> Não foi possível substituir o arquivo!</div > ';
        $resposta = [
            "status"=> false,
            "msg"=> $mensagem
        ];
        die( json_encode( $resposta, JSON_PRETTY_PRINT) );
    } 
    //
}

//
//-- TROCA DE PROPRIETÁRIO (idPessoa)
//
    if ($idPessoa != $_dados['idPessoa']) {

        $idPessoaAntigo = $_dados['idPessoa'];   // dono anterior
        $idPessoaNovo   = $idPessoa;             // novo dono

        $arquivoAtualNome = basename($_dados['arquivo'] ?? '');  // nome do arquivo atual

        $origem = "../docs/pessoa_$idPessoaAntigo/$arquivoAtualNome";
        $destinoDir = "../docs/pessoa_$idPessoaNovo";

        // cria pasta se não existir
        if (!is_dir($destinoDir)) {
            mkdir($destinoDir, 0777, true);
        }

        $destino = "$destinoDir/$arquivoAtualNome";

        // move arquivo somente se ele existir
        if (file_exists($origem)) {
            if (rename($origem, $destino)) {

                // atualiza tabela com o novo idPessoa
                $sql = "UPDATE rh_documentos 
                        SET idPessoa = :idPessoaNovo 
                        WHERE idDoc = :idDoc";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':idPessoaNovo', $idPessoaNovo, PDO::PARAM_INT);
                $stmt->bindParam(':idDoc', $idDoc, PDO::PARAM_INT);
                $stmt->execute();

            } else {
                // falha ao mover arquivo
                $status = false;
                $mensagem = '<div class="alert alert-danger text-center">
                                <strong>Erro!</strong> Não foi possível mover o arquivo para o novo proprietário.
                            </div>';
                die(json_encode(["status" => false, "msg" => $mensagem], JSON_PRETTY_PRINT));
            }
        }
    }

//
//- REGISTRA LOG 
//
    if( empty($nome_original) ) $nome_original = $_dados['nome_original'];

    $historico = "Alterado dados de registro de DOC: $nome_original | $dadosAntigos";
    $sql = "INSERT INTO rh_logs (idLogin, dtOper, oper, historico, tabela, idModulo, idOperacao)
                    VALUES (:idLogin, :agora, 'ALT', :historico, 'ti_docs', :idModulo, :idDoc)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':idLogin'  => $idLogin,
        ':agora'    => $agora,
        ':historico'=> $historico,
        ':idModulo' => $idModulo,
        ':idDoc'    => $idDoc,
    ]);

$conn = null;
die(json_encode(["status" => $status, "msg" => $mensagem]));

//
//---------- ROTINAS AUXILIARES
//

function ocr($origem)
{
    //
    //- Função para extrair texto de documentos - com cURL
    //
    //$url = 'localhost/rh/ocr_gerar.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário
    $url = 'https://ti.gerar.org.br/ocr_gerar.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário

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