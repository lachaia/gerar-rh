<?php
//
//- rh_index_aj13.php | Salva BAIXA Termo de Responsabilidade (DIGITALIZADO)
//- (C)haia, 15/09/2025
//

session_start();

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

include_once "../includes/conexao_gerar.php";
include_once "../includes/debug.php";
debug(json_encode($parametros, JSON_PRETTY_PRINT));

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-primary">
        <strong>OK!</strong> Sucesso no Teste de Sistema!
        </div>'
];
//die( json_encode( $retorno ) );
/*
 index_aj13.php | 2025-09-16 11:03:07 
{
    "bxa_termo_data_dev": "2025-09-16T11:02",
    "bxa_termo_vistoriador": "luiz.chaia",
    "bxa_termo_observacoes_dev": "<p>os equipamentos foram devolvidos em perfeito estado<\/p>",
    "bxa_termo_id": "8"
}
*/

$sql = "SELECT * 
           FROM rh_equip_termos
           WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $bxa_termo_id, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract( $dados );

//
// Verifica se enviou arquivo
//
if (isset($_FILES['termo_arquivo_bxa']) && $_FILES['termo_arquivo_bxa']['error'] == UPLOAD_ERR_OK) {
    
    $idPessoa = (int) $idPessoa; // já deve estar definido no seu código
    $idTermo  = (int) $id;       // id do termo
    
    $uploadDir = "../docs/pessoa_{$idPessoa}/";

    // cria pasta se não existir
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // pega extensão do arquivo enviado
    $ext = pathinfo($_FILES['termo_arquivo_bxa']['name'], PATHINFO_EXTENSION);
    $arquivo_baixa = "responsa_{$idTermo}." . $ext;
    $destino = $uploadDir . $arquivo_baixa;

    // se já existir, substitui
    if (file_exists($destino)) {
        unlink($destino);
    }

    // move o arquivo temporário para destino
    if (move_uploaded_file($_FILES['termo_arquivo_bxa']['tmp_name'], $destino)) {

        //
        // Atualiza o nome do arquivo no banco de dados
        //

        $userIP = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $usuario = htmlspecialchars($bxa_termo_vistoriador);

        $sql = "UPDATE rh_equip_termos 
                    SET arquivo = :arquivo, origem='d', user_devolucao = :user_devolucao, 
                        data_devolucao = :data_devolucao, 
                        vistoria_devolucao = :vistoria_devolucao, userAgentDev = :userAgent, userIPDev = :userIP,
                        status = 'Baixado' 
                    WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':arquivo', $arquivo_baixa, PDO::PARAM_STR);
        $stmt->bindParam(':id', $idTermo, PDO::PARAM_INT);

        $stmt->bindParam(':user_devolucao', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':data_devolucao', $bxa_termo_data_dev, PDO::PARAM_STR);
        $stmt->bindParam(':vistoria_devolucao', $bxa_termo_observacoes_dev, PDO::PARAM_STR);
        $stmt->bindParam(':userAgent', $userAgent, PDO::PARAM_STR);
        $stmt->bindParam(':userIP', $userIP, PDO::PARAM_STR);
        $stmt->execute();

        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-success">
                <strong>OK!</strong> Termo baixado com sucesso!!
                </div>'
        ];
    } else {
        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro </strong>ao mover o arquivo para o sistema!
                </div>'
        ];
    }
}

die(json_encode($retorno));
