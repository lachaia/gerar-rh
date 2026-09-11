<?php
//
//- cron_status.php | Atualiza Status dos Colaboradores
// (C)haia, 21/10/2025
//

include "includes/conexao_gerar.php";

//
//- VERIFICAÇÃO DAS FÉRIAS

//echo "<br>-- Atualiza para 'Afastado' (idStatus = 6)";

$sql = "UPDATE rh_colaboradores C
            SET C.idStatus = 6
            WHERE EXISTS (
                SELECT 1
                FROM rh_afastamentos A
                WHERE A.idColab = C.idColab
                AND A.status = 'Aprovado'
                AND NOW() BETWEEN A.data_inicio AND A.data_retorno
            );";
$stmt = $conn->prepare($sql);
$stmt->execute();


//echo "<br>-- Atualiza para 'Em férias' (idStatus = 3)";
$sql = "UPDATE rh_colaboradores C
            SET C.idStatus = 3
            WHERE EXISTS (
                SELECT 1
                FROM rh_ferias F
                WHERE F.idColab = C.idColab
                AND (
                        (NOW() BETWEEN F.data_parte1 AND DATE_ADD(F.data_parte1, INTERVAL F.dias_parte1 - 1 DAY)
                        AND F.aprova_rh_1_em IS NOT NULL)
                    OR (NOW() BETWEEN F.data_parte2 AND DATE_ADD(F.data_parte2, INTERVAL F.dias_parte2 - 1 DAY)
                        AND F.aprova_rh_2_em IS NOT NULL)
                    OR (NOW() BETWEEN F.data_parte3 AND DATE_ADD(F.data_parte3, INTERVAL F.dias_parte3 - 1 DAY)
                        AND F.aprova_rh_3_em IS NOT NULL)
                    )
            );";
$stmt = $conn->prepare($sql);
$stmt->execute();

//echo "<br>-- DESATIVA (VOLTA STATUS ATIVO)";

$sql = "UPDATE rh_colaboradores C
SET C.idStatus = 1
WHERE 
    (
        -- não está de férias
        C.idColab NOT IN (
            SELECT idColab
            FROM rh_ferias
            WHERE (
                    (NOW() BETWEEN data_parte1 AND DATE_ADD(data_parte1, INTERVAL dias_parte1 - 1 DAY)
                        AND aprova_rh_1_em IS NOT NULL)
                 OR (NOW() BETWEEN data_parte2 AND DATE_ADD(data_parte2, INTERVAL dias_parte2 - 1 DAY)
                        AND aprova_rh_2_em IS NOT NULL)
                 OR (NOW() BETWEEN data_parte3 AND DATE_ADD(data_parte3, INTERVAL dias_parte3 - 1 DAY)
                        AND aprova_rh_3_em IS NOT NULL)
                 )
        )
        -- e não está afastado
        AND C.idColab NOT IN (
            SELECT idColab
            FROM rh_afastamentos
            WHERE NOW() BETWEEN data_inicio AND data_retorno
              AND status = 'Aprovado'
        )
    )
    -- e atualmente o status está como férias ou afastado
    AND C.idStatus IN (3, 6);";

$stmt = $conn->prepare($sql);
$stmt->execute();