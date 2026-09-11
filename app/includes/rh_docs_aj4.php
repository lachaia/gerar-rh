<?php 
//
//- g_docs_aj4.php | Recupera dados do documento
// (C)haia, 06/03/2024 | (U) 2025-06-02

include_once "./conexao_gerar.php";

$id = filter_input(INPUT_POST, "id", FILTER_DEFAULT);

$sql = "SELECT idPessoa, nome_original, arquivo as nome_arquivo 
        FROM rh_documentos 
        WHERE idDoc = $id";
$result = $conn->prepare($sql);
$result->execute();
$dados = $result->fetch(PDO::FETCH_ASSOC);
 
echo json_encode( $dados );

$conn = null;

