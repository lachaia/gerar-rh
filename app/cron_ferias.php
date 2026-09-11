<?php

session_start();
if (!isset($_SESSION['idLogin'])) {
    header("Location: index.php");
    exit;
}
include "includes/conexao_gerar.php";

$sql = "SELECT idColab, data_admissao FROM rh_colaboradores WHERE data_rescisao IS NULL";
$stmt = $conn->prepare($sql);
$stmt->execute();

$agora = date('Y-m-d');
$idLogin = $_SESSION['idLogin']; // ou defina manualmente quem está executando
$hoje = date('Y-m-d H:i:s');

while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $idColab = $linha['idColab'];
    $dataAdmissao = new DateTime($linha['data_admissao']);

    // Gera períodos aquisitivos até o atual
    $inicioAquisitivo = clone $dataAdmissao;

    $hoje = new DateTime();

    // Define a data mínima para iniciar os períodos (ex: 01/03/2024)
    $dataMinima = DateTime::createFromFormat('d/m/Y', '01/01/2023');

    // Avança o início aquisitivo até ultrapassar a data mínima
    while ($inicioAquisitivo < $dataMinima) {
        $inicioAquisitivo->modify('+1 year');
    }

    while ($inicioAquisitivo < new DateTime()) {
        $fimAquisitivo = clone $inicioAquisitivo;
        $fimAquisitivo->modify('+1 year -1 day');

        $inicioConcessivo = clone $fimAquisitivo;
        $inicioConcessivo->modify('+1 day');

        $fimConcessivo = clone $fimAquisitivo;
        $fimConcessivo->modify('+1 year');

        // Verifica se já existe o registro para esse colaborador e período
        $check = $conn->prepare("SELECT 1 FROM rh_ferias 
                                 WHERE idColab = ? AND inicio_aquisitivo = ?");
        $check->execute([$idColab, $inicioAquisitivo->format('Y-m-d')]);
        if (!$check->fetch()) {
            // Insere o período
            $insert = $conn->prepare("INSERT INTO rh_ferias 
                (idColab, inicio_aquisitivo, fim_aquisitivo, inicio_concessivo, fim_concessivo, 
                 observacao, criado_em, atualizado_em, 
                 data_parte1, dias_parte1, data_parte2, dias_parte2, data_parte3, dias_parte3, admissao)
                VALUES (?, ?, ?, ?, ?, '', ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, ?)");

            $insert->execute([
                $idColab,
                $inicioAquisitivo->format('Y-m-d'),
                $fimAquisitivo->format('Y-m-d'),
                $inicioConcessivo->format('Y-m-d'),
                $fimConcessivo->format('Y-m-d'),
                $hoje->format('Y-m-d'),           // <--- corrigido
                $hoje->format('Y-m-d'),           // <--- corrigido
                $dataAdmissao->format('Y-m-d')    // <--- corrigido
            ]);
        }

        // Próximo período
        $inicioAquisitivo->modify('+1 year');
    }
}

echo "Períodos gerados com sucesso.";
