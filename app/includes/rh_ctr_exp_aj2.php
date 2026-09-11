<?php
//
//- rh_ctr_exp_aj2.php | Salva Inclusão do Contrato de Experiência
//- (C)haia, 22/09/2025
//

session_start();

$idModulo = 20; // Contratos de Experiência

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "<div class='alert alert-primary'>TESTE REALIZADO COM SUCESSO</div>"];
die(json_encode($response));
/*
rh_ctr_exp_aj2.php | 2025-09-22 15:39:44 
{
    "nome": "ENZO SILVEIRA MENDES",
    "idPessoa": "115",
    "idColab": "5",
    "dtInicial": "2025-09-22",
    "duracao": "60+30",
    "dtProrrogacao": "2025-11-20",
    "dtFinal": "2025-12-20",
    "observacao": "<p>teste<\/p>"
}
*/

// Validação básica
if (!isset($nome, $idColab, $dtInicial, $duracao, $dtFinal)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Campos obrigatórios!</div>";
    $response = ["status" => false, "msg" => $msg];
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_linha_do_tempo.php";
    include_once "f_logs.php";
    include_once "f_ocr.php";
    //
} else {
    header("Location: ../logout.php");
}
//
$idLogin = $_SESSION['idLogin'];
$nmLogin = $_SESSION['nmLogin'];
$status  = 'ATIVO';
if (empty($dtProrrogacao)) $dtProrrogacao = null;
//
$sql = "INSERT INTO rh_ctr_exp 
        (idColab, data_inicio, data_fim, data_prorrogacao, duracao, status, observacoes, criado_por, idLogin) 
        VALUES ( :idColab, :data_inicio, :data_fim, :data_prorrogacao, :duracao, :status, :observacoes, :criado_por, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab',          $idColab, PDO::PARAM_INT);
$stmt->bindParam(':data_inicio',      $dtInicial, PDO::PARAM_STR);
$stmt->bindParam(':data_fim',         $dtFinal, PDO::PARAM_STR);
$stmt->bindParam(':data_prorrogacao', $dtProrrogacao, PDO::PARAM_STR);
$stmt->bindParam(':duracao',          $duracao, PDO::PARAM_STR);
$stmt->bindParam(':status',           $status, PDO::PARAM_STR);
$stmt->bindParam(':observacoes',      $observacao, PDO::PARAM_STR);
$stmt->bindParam(':criado_por',       $nmLogin, PDO::PARAM_STR);
$stmt->bindParam(':idLogin',          $idLogin, PDO::PARAM_INT);

// Executa a inserção
if ($stmt->execute()) {
    $idContrato = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idContrato</div>";
    $response = ["status" => true, "msg" => $msg];
    //
    //-- SOBE O ARQUIVO, se houver
    //
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        //
        $nome_original = $_FILES['arquivo']['name']; //- Nome original
        $tamanho = $_FILES['arquivo']['size'];       // Tamanho do arquivo em bytes
        $tmp = $_FILES['arquivo']['tmp_name'];       // Caminho temporário
        $ext = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION)); // Extensão do arquivo (segura e sem pontos)

        $agora = date("YmdHis");

        // Gera nome único para evitar sobrescrita
        $nome_final = "contrato_$idColab" . "_" . $agora . "." . $ext;
        $diretorio  = "../docs/pessoa_$idPessoa";
        $destino    = $diretorio . "/" . $nome_final;

        // cria o diretório se não existir
        if (!is_dir($diretorio)) {
            if (!mkdir($diretorio, 0777, true)) {
                die(json_encode([
                    "status" => false,
                    "msg" => "Erro ao criar diretório de destino"
                ]));
            }
        } 

        // Move o arquivo
        if (move_uploaded_file($tmp, $destino)) {
            // Aqui você pode salvar $nome_final no banco de dados, se quiser
            //echo json_encode(['msg' => 'Arquivo enviado com sucesso!']);
            //
            // ATUALIZA o nome do arquivo na tabela de reuniões
            //
            $sqlUpdate = "UPDATE rh_ctr_exp SET arquivo = :arquivo WHERE id = :idContrato";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':arquivo', $nome_final, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':idContrato', $idContrato, PDO::PARAM_INT);
            $stmtUpdate->execute(); 
            //
            //- INSERE na tabela DOCUMENTOS
            //
                $idTipoDoc = 20; //- Contrato de Experiência
                //
                    $data = new DateTime($dtInicial);
                    $data->modify('+10 years');
                    $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
                //
                    $tags = '#Contrato #Ficha #Experiência';
                //
                    $texto_ocr = ocr($destino);
                    $texto_ocr = "$nome_original | " . addslashes($texto_ocr);
                //
                $assunto = "Contrato de Experiência de $nome";
                $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, descricao, data_validade, arquivo, 
                            nome_original, ocr, tags, extensao, tamanho, status, idLoginAprova, origem)
                            VALUES
                            ($idEmpresa, $idPessoa, $idTipoDoc, '$dtInicial', '$assunto', '$dataMais10Anos', '$nome_final', '$nome_original', 
                            '$texto_ocr', '$tags', '$ext', $tamanho, 1, $idLogin, 'CTR')";
                //debug( $sql );
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                //
                //- INCLUI LINHA DO TEMPO 
                //
                $tipo = 38; // Contrato de Experiência
                $descricao = "Contrato de Experiência em $dtInicial por $duracao dias. Válido até $dtFinal.";
                f_ldt( $tipo, $idPessoa, $descricao, $idContrato, null, 'CTR');
        }
    }
    //
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}

$dados = implode(", ", $parametros);
f_log("INC", "INCLUSÃO de Contrato de Experiência no Sistema: Dados( $dados )", "rh_ctr_exp", $idModulo, $idContrato);

$conn = null;
die(json_encode($response));

