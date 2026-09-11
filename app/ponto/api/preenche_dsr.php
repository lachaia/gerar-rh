<?php
//
// popula_dsr_2025.php | Gera DSR (Descanso Semanal Remunerado) para 2025
// (C)haia, 07/11/2025
//

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

date_default_timezone_set('America/Sao_Paulo');

$ano = 2025;
$dataInicial = new DateTime("$ano-01-01");
$dataFinal   = new DateTime("$ano-12-31");

$sql = "INSERT INTO rh_ponto_calendario 
        (data, tipo, descricao, ativo)
        VALUES (:data, 'DSR', 'Descanso Semanal Remunerado', 1)";
$stmt = $conn->prepare($sql);

$contador = 0;

while ($dataInicial <= $dataFinal) {
    $diaSemana = $dataInicial->format('N'); // 6 = sábado, 7 = domingo

    if ($diaSemana == 6 || $diaSemana == 7) {
        $stmt->execute([':data' => $dataInicial->format('Y-m-d')]);
        $contador++;
    }

    $dataInicial->modify('+1 day');
}

echo "✅ Inseridos $contador registros de DSR (sábados e domingos) no ano de $ano.";
