<?php
//
// registrar_ponto.php | Endpoint API do Módulo de Ponto Eletrônico
// (C)haia, 30/10/2025
//

session_start();
header('Content-Type: application/json; charset=utf-8');

// Ajusta para o fuso horário de São Paulo
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

/*
//- DEPURAÇÃO - Teste das variáveis do POST
include dirname(__DIR__) . '/../includes/debug.php';
debug(json_encode($parametros, JSON_PRETTY_PRINT));
$retorno = [
    'status' => 'teste',
    "mensagem" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode($retorno, JSON_PRETTY_PRINT) );
/*
 registrar_ponto.php | 2025-11-28 10:31:53 
{
    "lat": "-25.4998397",
    "lon": "-49.306971",
    "agora": "2025-11-28T13:38:09.486Z",
    "endereco": "Rua Senador Accioly Filho, 511 - Cidade Industrial de Curitiba - Curitiba\/PR",
    "colaborador_id": "1"
}
*/
/*
//-- PAREI AQUI --//
$retorno = [
    'status' => 'teste',
    "mensagem" => "<div class='alert alert-success'><strong>Eita!</strong> Cheguei até aqui!.</div>"
];
die( json_encode($retorno, JSON_PRETTY_PRINT) );
*/

if (!isset($colaborador_id) || !isset($lat) || !isset($lon) || !isset($agora)) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Parâmetros ausentes. Verifique os dados enviados.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

$idColab = intval($colaborador_id);
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Converte formato ISO 8601 -> MySQL
$hora_mysql = date('Y-m-d H:i:s', strtotime($agora));

$lat = isset($lat) ? floatval($lat) : null;
$lon = isset($lon) ? floatval($lon) : null;
if (empty($idEndereco)) $idEndereco = null;
if (empty($origem)) $origem = 'app';

//
//- Gera o Ticket
//
$data = date('Ymd');
$stmt = $conn->query("SELECT COUNT(*)+1 AS seq FROM rh_ponto_registros WHERE DATE(criado_em) = CURDATE()");
$seq = $stmt->fetchColumn();
$ticket = sprintf("TCK-%s-%05d", $data, $seq);

//
// ============================================================================
// 🔐 GERAR HASH DE INTEGRIDADE
// ============================================================================
//
// Criamos um JSON canônico contendo todos os campos críticos da batida.
// Qualquer alteração manual tornará o hash inválido.
//

$dadosParaHash = [
    'colaborador_id' => $idColab,
    'data_hora'      => $hora_mysql,
    'ip'             => $ip,
    'lat'            => $lat,
    'lon'            => $lon,
    'endereco_id'    => $idEndereco,
    'endereco_texto' => $dsEndereco,
    'ticket'         => $ticket
];

// JSON sem espaços, sem unicode escapado e com as chaves ordenadas
ksort($dadosParaHash);

$jsonCanonico = json_encode($dadosParaHash, JSON_UNESCAPED_UNICODE);

// Calcula o SHA-256
$hash_registro = hash('sha256', $jsonCanonico);

//
// ============================================================================
// 🔐 FIM DO HASH
// ============================================================================
//

$tipo = f_tipo_ponto( $idColab, $hora_mysql, $conn );

try {
    $sql = "INSERT INTO rh_ponto_registros 
            (colaborador_id, data_hora, ip, lat, lon, endereco_id, endereco_texto, ticket, hash_integridade, tipo, origem)
            VALUES 
            (:colaborador_id, :data_hora, :ip, :lat, :lon, :endereco_id, :endereco_texto, :ticket, :hash_integridade, :tipo, :origem)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':colaborador_id', $idColab);
    $stmt->bindParam(':data_hora', $hora_mysql);
    $stmt->bindParam(':ip', $ip);
    $stmt->bindParam(':lat', $lat);
    $stmt->bindParam(':lon', $lon);
    $stmt->bindParam(':endereco_id', $idEndereco);
    $stmt->bindParam(':endereco_texto', $dsEndereco);
    $stmt->bindParam(':ticket', $ticket);
    $stmt->bindParam(':hash_integridade', $hash_registro);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':origem', $origem);

    $stmt->execute();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => "Batida registrada com sucesso às " . date('H:i:s') . " — Ticket $ticket",
        'hash' => $hash_registro
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao gravar no banco: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

exit;

/**
 * Retorna 'Entrada' ou 'Saída' para a batida $data_hora do $idColab.
 * Se não encontrar exatamente, infere pela posição onde a batida se encaixaria.
 *
 * @param int $idColab
 * @param string $data_hora Datetime no formato reconhecível pelo strtotime
 * @param PDO $conn
 * @return string 'Entrada'|'Saída'
 */
function f_tipo_ponto($idColab, $data_hora, $conn)
{
    $data = date('Y-m-d', strtotime($data_hora));

    $sql = "SELECT data_hora
            FROM rh_ponto_registros
            WHERE colaborador_id = :idColab
              AND DATE(data_hora) = :data
            ORDER BY data_hora ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindParam(':data', $data, PDO::PARAM_STR);
    $stmt->execute();

    $batidas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // timestamp alvo
    $targetTs = strtotime($data_hora);
    if ($targetTs === false) {
        return 'ERRO';
    }

    // 1) tentar encontrar batida exata (ou com tolerância de 1s)
    foreach ($batidas as $idx => $b) {
        $ts = strtotime($b);
        if ($ts === false) continue;
        if ($ts === $targetTs || abs($ts - $targetTs) <= 1) {
            return ($idx % 2 === 0) ? 'Entrada' : 'Saída';
        }
    }

    // 2) se não encontrou, inferir posição (insertion index)
    $insertionIndex = 0;
    foreach ($batidas as $idx => $b) {
        $ts = strtotime($b);
        if ($ts === false) continue;
        if ($ts > $targetTs) {
            $insertionIndex = $idx;
            break;
        }
        $insertionIndex = $idx + 1;
    }

    // Posições pares => Entrada; ímpares => Saída
    return ($insertionIndex % 2 === 0) ? 'Entrada' : 'Saída';
}
