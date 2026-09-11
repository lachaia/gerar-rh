<?PHP
//
//- teste.php | Gerar batidas de ponto para os colaboradores - Teste de sistema
// (C)haia, 25/11/2025
//

session_start();

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');    

include dirname(__DIR__) . '../../includes/conexao_gerar.php';
include "../api/inc_verifica_data.php";

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>

        .cor_domingo{
            color: 'red';
            font-style: italic;
        }
        .cor_sabado{
            color: 'blue';
            font-style: italic;
        }

    </style>
</head>
<body>
<?php

echo "<h1 class='text-center'>GERA BATIDAS PARA TESTE DE SISTEMA</h1>";

//
//- Busca todos os colaboradores ativos (com cidade e UF)
//
    $onde = "C.data_rescisao IS NULL AND C.bate_ponto = 1";
    if( ! empty($colaborador_id) ) $onde .= " AND C.idColab = $colaborador_id";
    $sqlColabs = "
        SELECT 
            P.nome as nmColaborador,
            C.idColab, 
            C.horario_ini, 
            C.horario_fim, 
            S.cidade AS cidade_id,
            S.estado AS colaborador_uf,
            CONCAT(X.nome,'-',X.uf) AS dsCidade
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
        LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
        LEFT OUTER JOIN rh_cidades X ON X.idCidade = S.cidade 
        WHERE $onde";
    $stmtColabs = $conn->query($sqlColabs);
    echo "<div class='text-center h3 m-4'>➡️ COLABORADORES IDENTIFICADOS<br></div>";
    $colaboradores = $stmtColabs->fetchAll(PDO::FETCH_ASSOC);

//
//- LOOP - TODOS OS COLABORADORES
//
foreach ($colaboradores as $colaborador) {

    $nome           = $colaborador['nmColaborador'];
    $colaborador_id = $colaborador['idColab'];
    $horario_ini    = $colaborador['horario_ini']; // formato HH:MM:SS
    $horario_fim    = $colaborador['horario_fim'];
    //
    $cidade_id = $colaborador['cidade_id']; 
    $uf = $colaborador['colaborador_uf'];
    //
    echo "<hr><div class='text-center h3'>
        <p class='h2 text-primary'>$nome (ID: $colaborador_id)</p>
        <p>Cidade: {$colaborador['cidade_id']} - UF: {$colaborador['colaborador_uf']} - {$colaborador['dsCidade']}</p>
        <p>Horario: $horario_ini — $horario_fim</p>
    </div>";

    // ---------------------------------------------
    // Gera datas de 01/01/2025 até 31/12/2025
    // ---------------------------------------------
    $dataInicio = new DateTime('2025-01-01');
    $dataFim    = new DateTime('2025-11-25'); 

    for ($data = clone $dataInicio; $data < $dataFim; $data->modify('+1 day')) {

        $diaSemana = $data->format('N'); // 1=seg ... 7=dom

        // pula fins de semana
        if ($diaSemana == 6 || $diaSemana == 7) continue;

        // pula colaborador 1 em novembro/2025
        if (
            $colaborador_id == 1 &&
            $data->format('Y-m') == '2025-11'
        ) {
            continue;
        }

        //
        //- PULA: Feriados, DSR, Expediente Especial
        //
            $string_data = $data->format('Y-m-d');
            $vetor_data = verifica_data($string_data, $cidade_id, $uf, $conn);
            $tipoDia = $vetor_data['tipo'];

            if ($tipoDia != 'UTIL') {
                continue;
            }

        // cria timestamps de batidas
        $d = $data->format('Y-m-d');

        $entrada1 = "$d $horario_ini";
        $saida1   = date("Y-m-d H:i:s", strtotime("$d $horario_ini +4 hours"));
        $entrada2 = date("Y-m-d H:i:s", strtotime("$d $horario_ini +5 hours"));
        $saida2   = "$d $horario_fim";

        // ---------------------------------------------
        // Inserções (4 batidas)
        // ---------------------------------------------
        $ins = $conn->prepare("
            INSERT INTO rh_ponto_registros 
            (colaborador_id, data_hora, tipo, ip, lat, lon, endereco_id, endereco_texto, criado_em, ticket, hash_integridade, origem) 
            VALUES 
            (:colab, :dh, :tipo, :ip, :lat, :lon, :endereco_id, :endereco_texto, NOW(), :ticket, :hash, 'ajuste')
        ");

        // dados fixos para testes — personalize depois
        $ip    = "189.112.64.46";
        $lat   = "-25.4988082";
        $lon   = "-49.3127462";
        $endereco_id = 15;
        $endereco_txt = "Rua Senador Accioly Filho, 511, CIC, Curitiba-PR";

        $batidas = [
            ['tipo'=>'Entrada', 'dh'=>$entrada1],
            ['tipo'=>'Saida',   'dh'=>$saida1],
            ['tipo'=>'Entrada', 'dh'=>$entrada2],
            ['tipo'=>'Saida',   'dh'=>$saida2],
        ];

        foreach ($batidas as $b) {
            $ticket = geraTicket($colaborador_id, $b['dh']);
            $hash   = geraHash($b['dh']);

            $ins->execute([
                ':colab'         => $colaborador_id,
                ':dh'            => $b['dh'],
                ':tipo'          => $b['tipo'],
                ':ip'            => $ip,
                ':lat'           => $lat,
                ':lon'           => $lon,
                ':endereco_id'   => $endereco_id,
                ':endereco_texto'=> $endereco_txt,
                ':ticket'        => $ticket,
                ':hash'          => $hash,
            ]);
        }

        echo "<p class='text-success text-center'>Inserido: $nome — {$data->format('d/m/Y')}</p>";
    }
}


        // função simples p/ gerar ticket/hash
        function geraTicket($id, $datah) {
            return "TCK-$id-" . date("Ymd", strtotime($datah)) . "-" . str_pad(rand(1,99999),5,"0",STR_PAD_LEFT);
        }
        function geraHash($datah) {
            return hash("sha256", $datah . microtime(true) . rand());
        }

?>
</body>
</html>
