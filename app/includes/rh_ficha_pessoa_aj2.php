<?php
//
//- rh_ficha_pessoa_aj2.php | Devolve dados do E-MAIL específico 
//- (C)haia, 11/03/2025
//

session_start();

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados

$idEmail = filter_input(INPUT_POST, 'idEmail', FILTER_VALIDATE_INT);

if (!$idEmail) {
    die(json_encode(["status" => false, "msg" => "ID da EMAIL inválido!"]));
}

$sql = "SELECT E.*, D.arquivo 
            FROM rh_emails E
            LEFT OUTER JOIN rh_documentos D ON D.idDoc = E.idDoc 
            WHERE idEmail = :idEmail";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":idEmail", $idEmail, PDO::PARAM_INT);
$stmt->execute();

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
extract( $dados );

if ($dados) {
    echo json_encode(["status" => true, "dados" => $dados ]);
} else {
    echo json_encode(["status" => false, "msg" => "Nenhum e-Mail encontrado!"]);
}

$conn = null;
