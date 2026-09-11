<?php
//
//- motivos_aj.php | Popula Grade de Motivos - Módulo de Recrutamento
//- (C)haia, 24/08/2026
//

session_start();

if (isset($_SESSION['idLogin'])) {
    include "../../app/includes/conexao_gerar.php";
} else {
    header("location: ../logout.php");
    exit();
}

//- Obter dados a serem apresentados

$sql = "SELECT id, descricao, ativo FROM rs_vagas_mot ORDER BY descricao";
$stmt = $conn->prepare($sql);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    //
    $_status = ($ativo == 1)
        ? "<span class='badge bg-success'>Ativo</span>"
        : "<span class='badge bg-secondary'>Inativo</span>";
    $acoes = "<a href='#!' class='btn btn-outline-info btn-sm me-1' onclick='editar_motivo({$id})' title='Editar'><i class='fa-solid fa-pen'></i></a>" .
             "<a href='#!' class='btn btn-outline-danger btn-sm' onclick='excluir_motivo({$id})' title='Excluir'><i class='fa-solid fa-trash'></i></a>";
    //
    $dado[] = htmlspecialchars($descricao);
    $dado[] = $_status;
    $dado[] = $acoes;
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
echo json_encode($output);
exit;
