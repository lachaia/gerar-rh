<?php
//
//- rh_termo_aj1.php | Salva Registro do Termo Geral do Colaborador
//- (C)haia, 08/08/2025
//

session_start();

$idModulo = 18; // Termos Gerais

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO"];
die(json_encode($response));
/*
 rh_termo_aj1.php | 2025-08-08 11:14:50 
{
    "_nome": "Luiz Augusto Chaia",
    "_idColab": "1",
    "_idPessoa": "1",
    "_data": "2025-08-08",
    "idTipo": "1",
    "dsTipo": "Código de Ética e Conduta"
}
*/

// Validação básica
if (!isset($_idColab, $_data, $_idPessoa, $idTipo)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Todos os Campos são necessários!</div>";
    $response = ["status" => false, "msg" => $msg];
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $idUsuario = $_SESSION['idUsuario'];
    //
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    include_once "f_linha_do_tempo.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "INSERT INTO rh_termos ( idColab, idTipoTermo, data, criado_por, idLogin  )
            VALUES ( :idColab, :idTipo, :data, :criado_por, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab',       $_idColab, PDO::PARAM_INT);
$stmt->bindParam(':idTipo',        $idTipo, PDO::PARAM_INT);
$stmt->bindParam(':data',          $_data, PDO::PARAM_STR);
$stmt->bindParam(':idLogin',       $idLogin, PDO::PARAM_INT);
$stmt->bindParam(':criado_por',    $nmLogin, PDO::PARAM_STR);

// Executa a inserção
if ($stmt->execute()) {
    $idTermo = $conn->lastInsertId();
    //
    // INSERE ARQUIVO - Se houver
    //

    if (isset($_FILES['_arquivo']) && $_FILES['_arquivo']['error'] === 0) {
        //
        $nome_original = $_FILES['_arquivo']['name']; //- Nome original
        $tamanho = $_FILES['_arquivo']['size'];       // Tamanho do arquivo em bytes
        $tmp = $_FILES['_arquivo']['tmp_name']; // Caminho temporário
        $ext = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION)); // Extensão do arquivo (segura e sem pontos)

        $agora = date("YmdHis");

        // Gera nome único para evitar sobrescrita
        $nome_final = "termo_$_idColab" . "_" . $agora . "." . $ext;

        // Define diretório de destino
        $destino = "../docs/pessoa_$_idPessoa/" . $nome_final;

        // Move o arquivo
        if (move_uploaded_file($tmp, $destino)) {
            // Aqui você pode salvar $nome_final no banco de dados, se quiser
            //echo json_encode(['msg' => 'Arquivo enviado com sucesso!']);

            //
            // ATUALIZA o nome do arquivo na tabela de reuniões
            //
                $sqlUpdate = "UPDATE rh_termos SET arquivo = :arquivo WHERE id = :idTermo";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':arquivo', $nome_final, PDO::PARAM_STR);
                $stmtUpdate->bindParam(':idTermo', $idTermo, PDO::PARAM_INT);
                $stmtUpdate->execute();

                //
                //- INSERE na tabela DOCUMENTOS
                //
                    $idTipoDoc = 28; //- Termo
                //
                    $data = new DateTime($_data);
                    $data->modify('+10 years');
                    $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
                //
                    $tags = '#Termo';
                //
                    $texto_ocr = ocr($destino);
                    $texto_ocr = "$nome_original | " . addslashes($texto_ocr);
                //
                $assunto = "Termo de $_nome ()";
                $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, descricao, data_validade, arquivo, 
                            nome_original, ocr, tags, extensao, tamanho, status, idLoginAprova, origem)
                            VALUES
                            ($idEmpresa, $_idPessoa, $idTipoDoc, '$_data', '$assunto', '$dataMais10Anos', '$nome_final', '$nome_original', 
                            '$texto_ocr', '$tags', '$ext', $tamanho, 1, $idLogin, 'TRM')";
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

    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idTermo</div>";
    $response = ["status" => true, "msg" => $msg];

    //
    //- INCLUI LINHA DO TEMPO 
    //
    $tipo = 36; // Aceite de Termo
    $descricao = "Termo ($dsTipo) de $_nome";
    f_ldt( $tipo, $_idPessoa, $descricao, $idTermo, null, 'TRM');
    //
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}

$dados = implode(", ", $parametros);
f_log("INC", "INCLUSÃO de Termo:( $dados )", "rh_termos", $idModulo, $idTermo);

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