<?php
//
//- rh_ctr_exp_aj3.php | Salva ALTERAÇÃO do Contrato de Experiência
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
 rh_ctr_exp_aj3.php | 2025-12-15 17:30:29 
{
    "e_nome": "Adailson Cordeiro Gon\u00e7alves",
    "e_idPessoa": "570",
    "e_idColab": "13",
    "e_idContrato": "7",
    "e_dtInicial": "2025-11-05",
    "e_duracao": "45+45",
    "e_dtProrrogacao": "2025-12-19",
    "e_dtFinal": "2026-02-02",
    "e_observacao": "..."
}
*/

// Validação básica
if (!isset($e_idContrato, $e_idColab, $e_dtInicial, $e_duracao, $e_dtFinal)) {
    $msg = "<div class='alert alert-danger'><strong>Erro:</strong> Faltaram campos obrigatórios!</div>";
    $response = ["status" => false, "msg" => $msg];
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin   = $_SESSION['idLogin'];
    $nmLogin   = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php";
    include_once "f_logs.php";
    include_once "f_erros.php";
    include_once "f_ocr.php";
} else {
    header("Location: ../logout.php");
    exit;
}

//
//- RECUPERA DADOS ANTERIORES
//
    $sql = "SELECT * FROM rh_ctr_exp WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $e_idContrato, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $dados_old = $stmt->fetch(PDO::FETCH_ASSOC);
        $arquivo_old = $dados_old['arquivo'];
    }


// Ajustes
$status = 'ATIVO';
if (empty($e_dtProrrogacao)) $e_dtProrrogacao = null;

// Query de Update
$sql = "UPDATE rh_ctr_exp 
           SET data_inicio     = :data_inicio,
               data_fim        = :data_fim,
               data_prorrogacao= :data_prorrogacao,
               duracao         = :duracao,
               status          = :status,
               observacoes     = :observacoes,
               atualizado_por  = :atualizado_por,
               idLogin         = :idLogin,
               atualizado_em   = NOW()
         WHERE id = :idContrato";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':data_inicio',      $e_dtInicial, PDO::PARAM_STR);
$stmt->bindParam(':data_fim',         $e_dtFinal, PDO::PARAM_STR);
$stmt->bindParam(':data_prorrogacao', $e_dtProrrogacao, PDO::PARAM_STR);
$stmt->bindParam(':duracao',          $e_duracao, PDO::PARAM_STR);
$stmt->bindParam(':status',           $status, PDO::PARAM_STR);
$stmt->bindParam(':observacoes',      $e_observacao, PDO::PARAM_STR);
$stmt->bindParam(':atualizado_por',   $nmLogin, PDO::PARAM_STR);
$stmt->bindParam(':idLogin',          $idLogin, PDO::PARAM_INT);
$stmt->bindParam(':idContrato',       $e_idContrato, PDO::PARAM_INT);

// Executa a alteração
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Sucesso!</strong> Registro atualizado com sucesso.</div>";
    $response = ["status" => true, "msg" => $msg];
    //
    //- SE HOUVER, SOBE O ARQUIVO
    //
    if (isset($_FILES['e_arquivo']) && $_FILES['e_arquivo']['error'] === UPLOAD_ERR_OK) {
        //
        $nome_original = $_FILES['e_arquivo']['name']; //- Nome original
        $tamanho = $_FILES['e_arquivo']['size'];       // Tamanho do arquivo em bytes
        $tmp = $_FILES['e_arquivo']['tmp_name'];       // Caminho temporário
        $ext = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION)); // Extensão do arquivo (segura e sem pontos)

        // Gera nome único para evitar sobrescrita
        $agora = date("YmdHis");
        $nome_final = "contrato_$e_idColab" . "_" . $agora . "." . $ext;
        $diretorio = "../docs/pessoa_$e_idPessoa";
        $destino   = $diretorio . "/" . $nome_final;

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
                            //
                // ATUALIZA o nome do arquivo na tabela de contratos
                //
                    $sqlUpdate = "UPDATE rh_ctr_exp SET arquivo = :arquivo WHERE id = :idContrato";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bindParam(':arquivo', $nome_final, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':idContrato', $e_idContrato, PDO::PARAM_INT);
                    $stmtUpdate->execute();
                //
                //- ATUALIZA na tabela DOCUMENTOS
                //
                    $idTipoDoc = 20; //- Contrato de Experiência
                    //
                    $data = new DateTime($e_dtInicial);
                    $data->modify('+10 years');
                    $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
                    //
                    $tags = '#Contrato #Ficha #Experiência';
                    //
                    $texto_ocr = ocr($destino);
                    $texto_ocr = "$nome_original | " . addslashes($texto_ocr);
                    //
                    $assunto = "Contrato de Experiência de $e_nome";
                    $sql = "UPDATE rh_documentos SET data='$e_dtInicial', descricao='$assunto', data_validade='$dataMais10Anos',
                                    arquivo='$nome_final', nome_original='$nome_original', ocr='$texto_ocr', tags='$tags', 
                                    extensao='$ext', tamanho=$tamanho 
                            WHERE idPessoa=$e_idPessoa AND idTipoDoc=$idTipoDoc AND
                                    arquivo = '$arquivo_old'";
                    //debug( $sql );
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                //
                //- EXCLUI o arquivo antigo, se houver
                //
                    if (!empty($arquivo_old) && file_exists("../docs/pessoa_$e_idPessoa/" . $arquivo_old)) {
                        unlink("../docs/pessoa_$e_idPessoa/" . $arquivo_old); // Apaga o arquivo antigo
                    }
                
                //
                //- ALTERA LINHA DO TEMPO 
                //
                    $tipo = 38; // Contrato de Experiência
                    $descricao = "Contrato de Experiência em $e_dtInicial por $e_duracao dias. Válido até $e_dtFinal.";
                    $sql = "UPDATE rh_pessoas_ldt SET data = '$e_dtInicial', descricao='$descricao' 
                            WHERE idOrigem = $e_idContrato and origem='CTR'";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
            }
        
    }
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro:</strong> Falha ao atualizar registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}

// Log
$dados = implode(", ", $parametros);
f_log("ALT", "ALTERAÇÃO de Contrato de Experiência: Dados( $dados )", "rh_ctr_exp", $idModulo, $e_idContrato);

$conn = null;
die(json_encode($response));
