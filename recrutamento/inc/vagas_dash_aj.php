<?php
//
// - vagas_dash_aj.php | Popula Campos do Dashboard - Módulo de Recrutamento
// 
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit;
}

include "../../app/includes/conexao_gerar.php";

/*
 * Mapeamento por ID de Status (RH.rs_vagas_status - portão de aprovação):
 * 1 = SOLICITADA | 2 = APROVADA | 3 = REPROVADA
 *
 * Mapeamento por ID de Fluxo (RH.rs_vagas_fluxo - etapa do processo seletivo,
 * só existe depois de aprovada, ver fluxo.php):
 * 3 = EM TRIAGEM | 6 = CANCELADA | 7 = CONCLUIDA
 *
 * fechada_em é setado quando a vaga entra em CANCELADA/CONCLUIDA (ver
 * inc/vaga_fluxo_mover_aj.php), então "WHERE fechada_em IS NULL" já cobre
 * esses dois status de fluxo.
 */

$sql = "SELECT
            -- EM RECRUTAMENTO: Vagas já aprovadas e ainda em aberto
            SUM(CASE WHEN status_id = 2 THEN qtd ELSE 0 END) AS vagas_abertas,

            -- AGUARDANDO SUPERINTENDÊNCIA: Apenas com status SOLICITADA [1]
            SUM(CASE WHEN status_id = 1 THEN qtd ELSE 0 END) AS vagas_diretoria,

            -- TRIAGEM: Apenas com fluxo EM TRIAGEM [3]
            SUM(CASE WHEN fluxo_id = 3 THEN qtd ELSE 0 END) AS vagas_triagem,

            -- VAGAS CRÍTICAS (>30 dias): Em aberto (exclui Reprovadas [3]) e criadas há mais de 30 dias
            SUM(CASE WHEN status_id != 3
                     AND DATEDIFF(CURRENT_DATE(), DATE(criado_em)) > 30 THEN qtd ELSE 0 END) AS vagas_criticas

        FROM rs_vagas
        WHERE fechada_em IS NULL";

$stmt = $conn->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Formatação com 2 dígitos para manter o padrão visual Dark Mode
$retorno = [
    'vagas_abertas'   => sprintf("%02d", $row['vagas_abertas'] ?? 0),
    'vagas_diretoria' => sprintf("%02d", $row['vagas_diretoria'] ?? 0),
    'vagas_triagem'   => sprintf("%02d", $row['vagas_triagem'] ?? 0),
    'vagas_criticas'  => sprintf("%02d", $row['vagas_criticas'] ?? 0)
];

header('Content-Type: application/json');
echo json_encode($retorno);
exit;