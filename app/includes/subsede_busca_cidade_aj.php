<?php
//
//- subsede_busca_cidade_aj.php | Busca Cidade por AJAX vindo de busca_cep
//- (C)haia, 2026-06-23
//

include "../includes/conexao_gerar.php";

$localidade = $_POST['cidade'];

$sql = "SELECT * FROM rh_cidades WHERE nome like '$localidade'";

$stmt = $conn->prepare($sql);
$stmt->execute();  

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if( $dados ) $dados['status'] = 1; else $dados['status'] = 0;

echo json_encode($dados);