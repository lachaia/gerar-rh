<?php
//
//- rh_saude_aj2.php | Salva Registro do Exame
//- (C)haia, 23/04/2025
//

session_start();

$idModulo = 8; // Saúde Ocupacional

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "<div class='alert alert-primary'><strong>OK: </strong> Teste Realizado com Sucesso!</div>"];
die(json_encode($response));
/*
 rh_saude_aj2.php | 2025-04-29 09:51:59 
{
    "nmPessoa": "Luiz Augusto Chaia",
    "idPessoa": "1",
    "idColab": "1",
    "data": "2025-04-29",
    "idExameTipo": "3",
    "dtVencimento": "2026-01-25",
    "dsExame": "Abreugrafia",
    "nmClinica": "Inst Radiol\u00f3gico Cuiab\u00e1",
    "nmMedico": "Dr. Ferdinando",
    "status": "Apto",
    "obs": "<p>Raio X do t\u00f3rax<\/p>",
    "idTipoDoc": "8"
}
*/

extract($parametros);

// Validação básica
if (!isset($idPessoa, $idColab, $data, $idExameTipo, $dtVencimento, $dsExame, $nmClinica, $nmMedico, $status, $obs)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Todos os Campos são necessários!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [1, 9], true)) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
    $arquivo = $_FILES['arquivo'];
    $fileSize = $arquivo['size'];
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $original = basename($arquivo['name']);
    $tmp = $arquivo['tmp_name'];

    // Define um nome único para salvar
    $nomeFinal = "exame_" . uniqid() . '.' . $extensao;

    // Define o caminho de destino
    $destino = "../docs/pessoa_$idPessoa/" . $nomeFinal;

    if (move_uploaded_file($tmp, $destino)) {
        //echo "Arquivo enviado com sucesso!";
        // Aqui você pode salvar no banco o caminho ou nome do arquivo
        $sql = "INSERT INTO rh_documentos
            (idEmpresa, idPessoa, idTipoDoc, data, data_validade, arquivo, nome_original, extensao, 
                tamanho, status, idLoginAprova)
            VALUES
            (:idEmpresa, :idPessoa, :idTipoDoc, :data, :data_validade, :arquivo, :nome_original, :extensao, 
                :tamanho, :status, :idLoginAprova)";
        $_status = 1;
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
        $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->bindParam(':idTipoDoc', $idTipoDoc, PDO::PARAM_INT);
        $stmt->bindParam(':data', $data, PDO::PARAM_STR);
        $stmt->bindParam(':data_validade', $dtVencimento, PDO::PARAM_STR);
        $stmt->bindParam(':arquivo', $nomeFinal, PDO::PARAM_STR);
        $stmt->bindParam(':nome_original', $original, PDO::PARAM_STR);
        $stmt->bindParam(':extensao', $extensao, PDO::PARAM_STR);
        $stmt->bindParam(':tamanho', $fileSize, PDO::PARAM_STR);
        $stmt->bindParam(':status', $_status, PDO::PARAM_STR);
        $stmt->bindParam(':idLoginAprova', $idLogin, PDO::PARAM_STR); // já sobe aprovado
        $stmt->execute();
        $idDoc = $conn->lastInsertId(); // Pega o ID do último documento inserido
    } else {
        echo "Erro ao mover o arquivo.";
    }
} else {
    //echo "Nenhum arquivo foi enviado. (e tá tudo bem 😄)";
    $idDoc = 0; // Nenhum arquivo enviado
}

$sql = "INSERT INTO rh_exames (idColab, idExameTipo, data, dtValidade, nmExame, nmClinica, nmMedico, status, observacoes, idDoc, idLogin) 
VALUES 
( :idColab, :idExameTipo, :data, :dtValidade, :nmExame, :nmClinica, :nmMedico, :status, :observacoes, :idDoc, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab', $idColab, PDO::PARAM_STR);
$stmt->bindParam(':idExameTipo', $idExameTipo, PDO::PARAM_INT);
$stmt->bindParam(':data', $data, PDO::PARAM_STR);
$stmt->bindParam(':dtValidade', $dtVencimento, PDO::PARAM_STR);
$stmt->bindParam(':nmExame', $dsExame, PDO::PARAM_STR);
$stmt->bindParam(':nmClinica', $nmClinica, PDO::PARAM_STR);
$stmt->bindParam(':nmMedico', $nmMedico, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':observacoes', $obs, PDO::PARAM_STR);
$stmt->bindParam(':idDoc', $idDoc, PDO::PARAM_INT);
$stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);
// Executa a inserção
if ($stmt->execute()) {
    $idExame = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idExame</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

$dados = implode(", ", $parametros);
f_log("INC", "INCLUSÃO de Exame $dsExame de $nmPessoa: Dados( $dados )", "rh_exames", $idModulo, $idExame);

$conn = null;
die(json_encode($response));