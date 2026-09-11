<?php 
//
//- pdf_espelho.php | Espelho de Cartão Ponto - PDF
//- (C)haia, 17/11/2025 
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

// Recebe o colaborador logado
$idColab = $_SESSION['idColab'] ?? null;

if (empty($idColab)) {
    header('Location: ../logout.php');
    exit();
}

include dirname(__DIR__) . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

//
// Busca dados básicos do colaborador
//
    $sql = "SELECT 
                P.nome,
                C.idColab, 
                C.horario_ini, 
                C.horario_fim, 
                S.cidade AS cidade_id,
                S.estado AS colaborador_uf
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
            LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
            WHERE C.data_rescisao IS NULL AND C.idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idColab' => $idColab]);
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    $horario_ini = $linha['horario_ini'] ?? '08:20';    //- Padrão Gerar
    $horario_fim = $linha['horario_fim'] ?? '18:05';    //- Padrão Gerar
    $cidade_id = $linha['cidade_id'] ?? 3281;           //- Curitiba
    $colaborador_uf = $linha['colaborador_uf'] ?? 'PR'; //- Curitiba-PR

// ===============================
// Monte os dados do espelho
// ===============================
$periodo = date("01/m/Y") . " a " . date("t/m/Y");
$colaborador = $linha['nome'];

// —— Batidas do mês atual ——
$stmt = $conn->prepare("
    SELECT DATE(data_hora) AS data, DATE_FORMAT(data_hora,'%H:%i') AS hora
    FROM rh_ponto_registros
    WHERE colaborador_id = :id
    AND MONTH(data_hora) = MONTH(CURDATE())
    AND YEAR(data_hora) = YEAR(CURDATE())
    ORDER BY data_hora
");
$stmt->execute([':id' => $idColab]);

$batidas = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $batidas[$r['data']][] = $r['hora'];
}

$linhas = [];
$totExtra = $totFalta = $totSaldo = 0;

foreach ($batidas as $dia => $horas) {
    $res = calcJornada($horas, "08:00", "17:00"); // aqui ajuste como no seu sistema

    $linhas[] = [
        'data'       => date("d/m/Y", strtotime($dia)),
        'dia'        => ucfirst(strftime('%A', strtotime($dia))),
        'inicio'     => $horas[0] ?? "--:--",
        'fim'        => end($horas) ?? "--:--",
        'trabalhada' => $res['trabalhada'],
        'extra'      => $res['extra'],
        'faltante'   => $res['faltante'],
        'saldo'      => $res['saldo'],
    ];

    $totExtra  += tempoEmSeg($res['extra']);
    $totFalta  += tempoEmSeg($res['faltante']);
    $totSaldo  += (tempoEmSeg($res['extra']) - tempoEmSeg($res['faltante']));
}

$totais = [
    'extra'    => gmdate("H:i", $totExtra),
    'faltante' => gmdate("H:i", $totFalta),
    'saldo'    => gmdate("H:i", abs($totSaldo))
];

function tempoEmSeg($t) {
    list($h,$m) = explode(':', $t);
    return $h * 3600 + $m * 60;
}

// =========================================
// Renderiza o template
// =========================================
ob_start();
include "../templates/espelho_ponto.php";
$html = ob_get_clean();

// =========================================
// Configuração do DOMPDF
// =========================================
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Envia para download
$dompdf->stream("Espelho_Ponto_$idColab.pdf", [
    "Attachment" => true
]);

exit;

function calcJornada($horas, $horarioIni, $horarioFim)
{
    // Se não tem batida, retorna 0
    if (empty($horas)) {
        return [
            'trabalhada' => '00:00',
            'faltante'   => '00:00',
            'extra'      => '00:00',
            'saldo'      => '00:00'
        ];
    }

    // Jornada prevista
    $previstaSeg = (strtotime($horarioFim) - strtotime($horarioIni)) - 3600; // tirando 1h almoço
    if ($previstaSeg < 0) $previstaSeg = 0;

    // Jornada trabalhada
    $primeira = reset($horas);
    $ultima   = end($horas);

    $trabSeg  = strtotime($ultima) - strtotime($primeira);
    if ($trabSeg < 0) $trabSeg = 0;

    // Cálculo de saldo
    $saldoSeg = $trabSeg - $previstaSeg;

    return [
        'trabalhada' => gmdate("H:i", $trabSeg),
        'faltante'   => $saldoSeg < 0 ? gmdate("H:i", -$saldoSeg) : "00:00",
        'extra'      => $saldoSeg > 0 ? gmdate("H:i",  $saldoSeg) : "00:00",
        'saldo'      => gmdate("H:i", abs($saldoSeg)),
    ];
}
