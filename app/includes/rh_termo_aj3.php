<?php
//
//- rh_termo_aj3.php | Salva Registro de ALTERAÇÃO do Termo
//- (C)haia, 11/08/2025
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
 rh_termo_aj3.php | 2025-08-11 09:49:35 
{
    "e_id": "3",
    "e_nome": "Luiz Augusto Chaia",
    "e_idColab": "1",
    "e_idPessoa": "1",
    "e_data": "2025-07-29",
    "idTipo": "3",
    "dsTipo": "Política Ambiental"
}
*/

// Validação básica
if (!isset($e_idColab, $e_data, $e_idPessoa, $idTipo)) {
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

//
//- RECUPERA DADOS ANTERIORES
//
$sql = "SELECT A.*,  P.nome, T.descricao, C.idPessoa
                FROM RH.rh_termos A
                INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_termos_tipos T on T.id = A.idTipoTermo 
                WHERE A.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $e_id, PDO::PARAM_INT);
if ($stmt->execute()) {
    $dados_old = $stmt->fetch(PDO::FETCH_ASSOC);
    $arquivo_old = $dados_old['arquivo'];
}

$sql = "UPDATE rh_termos 
        SET idTipoTermo = :idTipo,
            data = :data
        WHERE id = :id"; 

$stmt = $conn->prepare($sql);

$stmt->bindParam(':idTipo',  $idTipo, PDO::PARAM_INT);
$stmt->bindParam(':data',    $e_data, PDO::PARAM_STR);
$stmt->bindParam(':id',      $e_id, PDO::PARAM_INT); // ID do registro a ser atualizado

// Executa a inserção
if ($stmt->execute()) {
    //
    // INSERE ARQUIVO - Se houver
    //

    if (isset($_FILES['e_arquivo']) && $_FILES['e_arquivo']['error'] === 0) {
        //
        $nome_original = $_FILES['e_arquivo']['name'];  //- Nome original
        $tamanho = $_FILES['e_arquivo']['size'];        // Tamanho do arquivo em bytes
        $tmp = $_FILES['e_arquivo']['tmp_name'];        // Caminho temporário
        $ext = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION)); // Extensão do arquivo (segura e sem pontos)

        $agora = date("YmdHis");

        // Gera nome único para evitar sobrescrita
        $nome_final = "termo_$e_idColab" . "_" . $agora . "." . $ext;

        // Define diretório de destino
        $destino = "../docs/pessoa_$e_idPessoa/" . $nome_final;

        // Move o arquivo
        if (move_uploaded_file($tmp, $destino)) {
            // Aqui você pode salvar $nome_final no banco de dados, se quiser
            //echo json_encode(['msg' => 'Arquivo enviado com sucesso!']);

            //
            // ATUALIZA o nome do arquivo na tabela de reuniões
            //
            $sqlUpdate = "UPDATE rh_termos SET arquivo = :arquivo WHERE id = :e_id";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':arquivo', $nome_final, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':e_id', $e_id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            //
            //- ATUALIZA na tabela DOCUMENTOS
            //
            $idTipoDoc = 28; //- Termo
            //
            $data = new DateTime($e_data);
            $data->modify('+10 years');
            $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
            //
            $tags = "#Termo #$dsTipo";
            //
            $texto_ocr = ocr($destino);
            $texto_ocr = "$nome_original | " . addslashes($texto_ocr);
            //
            $assunto = "Aceite de Termo: $dsTipo";
            $sql = "UPDATE rh_documentos SET data='$e_data', descricao='$assunto', data_validade='$dataMais10Anos',
                            arquivo='$nome_final', nome_original='$nome_original', ocr='$texto_ocr', tags='$tags', 
                            extensao='$ext', tamanho=$tamanho WHERE idPessoa=$e_idPessoa AND idTipoDoc=$idTipoDoc AND
                            arquivo = '$arquivo_old'";
            //debug( $sql );
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            //
            //- EXCLUI ARQUIVO ANTERIOR
            //
            $url = "../docs/pessoa_$e_idPessoa/" . $arquivo_old;
            if (file_exists($url)) {
                unlink($url); // Apaga o arquivo
            }
        } else {
            echo json_encode(['msg' => 'Erro ao mover o arquivo.']);
        }
    } 

    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $e_id</div>";
    $response = ["status" => true, "msg" => $msg];

    //
    //- ALTERA LINHA DO TEMPO 
    //
    $tipo = 36; // Aceite de Termo
    $descricao = "Termo ($dsTipo) de $e_nome";
    $sql = "UPDATE rh_pessoas_ldt SET data = '$e_data', descricao='$descricao' WHERE idOrigem = $e_id and origem='TRM'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao alterar registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}

$dados = implode(", ", $parametros);
f_log("INC", "INCLUSÃO de Atestado de Afastamento: Dados( $dados )", "rh_afastamentos", $idModulo, $e_id);

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
