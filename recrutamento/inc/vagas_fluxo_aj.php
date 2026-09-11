<?php
//
//- vagas_fluxo_aj.php | Popula Grade de Etapas do Fluxo - Módulo de Recrutamento
//- (C)haia, 26/08/2026
//

session_start();

if (isset($_SESSION['idLogin'])) {
    include "../../app/includes/conexao_gerar.php";
} else {
    header("location: ../logout.php");
    exit();
}

//- Obter dados a serem apresentados

$sql = "SELECT id, status, descricao, ativo, cor_frente, cor_fundo FROM rs_vagas_fluxo ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    //
    $_status = "<span class='badge {$cor_frente} status' style='background-color: {$cor_fundo};'>{$status}</span>";
    $_situacao = ($ativo == 1)
        ? "<span class='badge bg-success'>Ativo</span>"
        : "<span class='badge bg-secondary'>Inativo</span>";
    $acoes = "<a href='#!' class='btn btn-outline-info btn-sm me-1' onclick='editar_fluxo({$id})' title='Editar'><i class='fa-solid fa-pen'></i></a>" .
             "<a href='#!' class='btn btn-outline-danger btn-sm' onclick='excluir_fluxo({$id})' title='Excluir'><i class='fa-solid fa-trash'></i></a>";
    //
    $dado[] = $_status;
    $dado[] = htmlspecialchars($descricao);
    $dado[] = $_situacao;
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
