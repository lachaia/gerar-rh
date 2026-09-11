<?php
//
//- cron_banco_horas.php | Rotina para calcular o Saldo Diário de horas
// (C)haia, 07/11/2025 | 18/11/2025
//

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

session_start();

ini_set('max_execution_time', 600); // 10 minutos
set_time_limit(600);

$idLogin = $_SESSION['idLogin'] ?? null;
$horario_ini = "08:20:00"; //- Padrão Gerar
$horario_fim = "18:05:00"; //- Padrão Gerar

if( isset($_GET['colaborador_id']) ) $colaborador_id = $_GET['colaborador_id']; else $colaborador_id = null;

/*
    Deve calcular (em segundos para maior precisão):
    1. Total de Horas trabalhadas no dia.
    2. Jornada prevista para o dia.
    3. Identificar se é dia Útil, Feriado, DSR, Facultativo ou Reduzido.
    4. Calcular o intervalo de almoço
    5. Calcular as Horas Extras 50% e 100%
    6. Calcular o Adicional Noturno
    7. Calcular o Saldo Diário para efeito de Banco de Horas (pode ser + ou -)
*/

include "inc_funcoes.php";
include "inc_verifica_data.php";

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
//
//- INICIALIZA RELATÓRIO
//
    include dirname(__DIR__) . '/../includes/conexao_gerar.php';
    //include dirname(__DIR__) . '/../includes/debug.php';
    echo "<h1 class='text-center m-4'>CRON APURAÇÃO DAS HORAS DIÁRIAS</h1>";
    if( empty($colaborador_id) ){
        echo "<h2 class='text-center m-4'>APURAÇÃO GERAL</h2>";
    } else{
        echo "<h2 class='text-center m-4'>APURAÇÃO INDIVIDUAL</h2>";
    }
    echo "➡️ Banco de Dados aberto<br>";

//
//- IDENTIFICA O PERÍODO DE APURAÇÃO
//
    $sql = "SELECT * 
            FROM RH.rh_ponto_banco 
            WHERE status = 'ABERTO' 
            ORDER BY periodo_inicial 
            LIMIT 1";
    $stmt = $conn->query($sql);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($stmt->rowCount() > 0) {
        $banco_id = $dados['id'];
        $periodo_inicial = $dados['periodo_inicial'];
        //$periodo_final   = date('Y-m-d', strtotime('-1 day'));
        $periodo_final   = date('Y-m-d');
    } else {
        die("<h1>SEM PERÍODO ABERTO PARA APURAÇÃO</h1>");
    }
    echo "➡️ PERÍODO DE APURAÇÃO IDENTIFICADO<br>";


//$periodo_inicial = '2025-01-01';
//$periodo_final = '2025-11-24';

//
//- LIMPA SALDO DE HORAS DO PERÍODO ATUAL
//
    $onde = "banco_id = $banco_id";
    if( ! empty($colaborador_id) ) $onde .= " AND colaborador_id = $colaborador_id";
    $sql = "DELETE 
                FROM rh_ponto_banco_saldo 
                WHERE $onde";
    $stmt = $conn->prepare($sql);
    if( $stmt->execute() ){
        echo "➡️ TABELA SALDO-HORAS PERÍODO ATUAL LIMPA<br>";
    }
//
//- LIMPA SALDO DE HORAS-DIA DO PERÍODO ATUAL
//
    $onde = "data_ref >= '$periodo_inicial' AND data_ref <= '$periodo_final'";
    if( ! empty($colaborador_id) ) $onde .= " AND colaborador_id = $colaborador_id";
    $sql = "DELETE 
                FROM rh_ponto_banco_horas 
                WHERE $onde";
    $stmt = $conn->prepare($sql);
    if( $stmt->execute() ){
        echo "➡️ TABELA SALDO-HORAS-DIA PERÍODO ATUAL LIMPA<br>";
    }

//
//- APAGA NOTIFICAÇOES NÃO LIDAS SOBRE PONTO
//
    $onde = "idTipo = 2 AND idEvento = 7 AND lido_em is null";
    if( ! empty($colaborador_id) ) $onde .= " AND colaborador_id = $colaborador_id";
    $sql = "DELETE 
            FROM rh_notificacoes
            WHERE $onde";
    $stmt = $conn->prepare($sql);
    if( $stmt->execute() ){
        echo "➡️ NOTIFICAÇÕES NÃO LIDAS SOBRE PONTO APAGADAS<br>";
    }

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
    echo "➡️ COLABORADORES IDENTIFICADOS<br><br>";
//
//- PERCORRE CADA COLABORADOR
//
while ($colab = $stmtColabs->fetch(PDO::FETCH_ASSOC)) {
    //
    //- CABEÇALHO DO DEMONSTRATIVO
    //
        $idColab   = $colab['idColab'];
        $cidade_id = $colab['cidade_id'];
        $uf        = $colab['colaborador_uf'];
        $dsCidade  = $colab['dsCidade'];
        $nmColaborador = $colab['nmColaborador'];
        //
        $horario_ini = $colab['horario_ini'];
        $horario_fim = $colab['horario_fim'];
        //
        $jornada   = (strtotime($horario_fim) - strtotime($horario_ini)) - 3600;

        echo "<hr class='mt-4'>
            <h1 class='text-center'>$nmColaborador</h1>
            <div class='text-center mb-4'>
                <b>Colaborador:</b> $idColab | $nmColaborador — <b>Cidade:</b> $dsCidade | Hora Inicial: $horario_ini — Hora Final: $horario_fim
            </div>";

    //
    // Percorre cada dia do período de apuração
    //
        $dataAtual = strtotime($periodo_inicial);
        $dataFim   = strtotime($periodo_final);

        echo "
            <table border='1' class='table table-striped table-hover table-sm w-100'>
                <thead>
                    <tr>
                        <th class='text-center'>Data</th>
                        <th>Tipo</th>
                        <th>Alerta</th>
                        <th>Qtd</th>
                        <th>Batidas</th>
                        <th>Almoço</th>
                        <th class='text-center'>Trabalhado</th>
                        <th class='text-center'>Jornada</th>                        
                        <th class='text-center'>HE.50</th>
                        <th class='text-center'>HE.100</th>
                        <th class='text-center'>A.N.</th>
                        <th class='text-center'>Saldo</th>
                        <th class='text-center'>CRE</th>
                        <th class='text-center'>DEB</th>
                        <th class='text-center'>BH</th>
                        <th>Diagnóstico</th>
                    </tr>
                </thead>
                <tbody>";
    //
    //- PERCORRE CADA DIA A PARTIR DO 1º DIA DO PERÍODO DE APURAÇÃO
    //
    $bh = 0; // inicializa banco de horas
    while ($dataAtual <= $dataFim) {
        //
        $dataRef = date('Y-m-d', $dataAtual);

        // Se a data for hoje → pula o dia
        if ($dataRef === date('Y-m-d')) {
            $dataAtual = strtotime('+1 day', $dataAtual);
            continue;
        }

        //
        // Consulta feriado, DSR ou expediente especial
        //
            $vetor_data = verifica_data($dataRef, $cidade_id, $uf, $conn);
            $tipoDia = $vetor_data['tipo'];

        //
        // Define tipo de dia e jornada prevista
        //
            $dsTipoDia = 'Dia Útil';
            $isFeriado = false;
            $isFacultativo = false;
            $isCompensado = false;
            $isDSR = false;
            $fator = 1;

            if ($tipoDia != 'UTIL') {
                $dsTipoDia = $tipoDia == 'FERIADO' ? "➡️ {$vetor_data['tipo']} ({$vetor_data['motivo']})" : "➡️ {$vetor_data['tipo']}";

                switch ($tipoDia) {
                    case 'FERIADO':
                        $isFeriado = true;
                        $jornada = 0;
                        $fator = 2;
                        break;

                    case 'COMPENSADO':
                        $isCompensado = true;
                        $jornada = 0;
                        break;

                    case 'FACULTATIVO':
                        $isFacultativo = true;
                        $jornada = 0;
                        break;

                    case 'DSR':
                        $isDSR = true;
                        $jornada = 0;
                        $fator = 2;
                        break;

                    case 'REDUZIDO':
                        $jornada = strtotime($vetor_data['hora_fim']) - strtotime($vetor_data['hora_ini']);
                        break;

                    default:
                        $jornada = (strtotime($colab['horario_fim']) - strtotime($colab['horario_ini'])) - 3600;
                }
            } else {
                $jornada = (strtotime($colab['horario_fim']) - strtotime($colab['horario_ini'])) - 3600;
            }

        //
        //- SOLICITAÇÕES DE AJUSTE DE PONTO (Aprovadas e ainda não aplicadas)
        //
            $sql = "SELECT id as solicitacao_id, data_hora, tipo, batida_id
                    FROM rh_ponto_solicitacoes
                    WHERE   colaborador_id = :id AND 
                            DATE(data_hora) = :data AND 
                            aplicado_em is null AND 
                            status = 'APROVADO'
                    ORDER BY data_hora ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $idColab, ':data' => $dataRef]);
            $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //
            foreach ($solicitacoes as $solicitacao) {
                //
                $tipo = $solicitacao['tipo']; // - ABO | ALT | DEL | INC
                $solicitacao_id = $solicitacao['solicitacao_id'];
                $data_hora = $solicitacao['data_hora'];
                $batida_id = $solicitacao['batida_id'];
                //
                $res = false;
                if( $tipo == 'INC') $res = f_inclui_ponto($conn, $solicitacao_id, $data_hora, $idColab);               
                if( $tipo == 'ALT') $res = f_altera_ponto($conn, $batida_id, $data_hora, $idColab);
                if( $tipo == 'DEL') $res = f_exclui_ponto($conn, $batida_id, $data_hora, $idColab);
                if( $tipo == 'ABO') $res = f_abonar_ponto($conn, $solicitacao_id, $data_hora, $idColab, $vetor_data, $horario_ini, $horario_fim);                
                //
                if( $res ){
                    $sql = "UPDATE rh_ponto_solicitacoes 
                                SET aplicado_em = :aplicado_em 
                                WHERE id = :solicitacao_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':aplicado_em' => date('Y-m-d H:i:s'),
                        ':solicitacao_id' => $solicitacao_id
                    ]);
                }
            }

        //
        // Busca batidas do dia
        //
            $stmt_batidas = $conn->prepare("
                SELECT id, data_hora, origem 
                    FROM rh_ponto_registros
                    WHERE colaborador_id = :id AND DATE(data_hora) = :data
                    ORDER BY data_hora
            ");
            $stmt_batidas->execute([':id' => $idColab, ':data' => $dataRef]);
            $batidas = $stmt_batidas->fetchAll(PDO::FETCH_ASSOC);

        //
        // Situação do dia - Inicializa variáveis
        //
            $alerta = 0;
            $obs = '';
            $intervalo_almoco = 0;
            $he_50 = 0;
            $he_100 = 0;
            $adicional_noturno = 0;
            //
            $totalTrabalhado = 0;
            $saldo = 0;
            $credito = 0;
            $debito = 0;

            $vetorTrabalhado = [
                // metadados
                'tipoDia' => 'UTIL',
                'dataBase' => $dataRef,

                // valores brutos (segundos)
                'total_trabalhado' => 0,
                'jornada_util'     => 0,
                'intrajornada'     => 0,
                'adicional_noturno'=> 0,
                'n_almoco'         => 0,

                // extras / saldo (em segundos)
                'extras_totais'    => 0,
                'extras_50'        => 0,
                'extras_100'       => 0,
                'saldo'            => 0,
                'credito'          => 0,
                'debito'           => 0,

                // formatados
                'fmt' => [
                    'total_trabalhado' => "00:00",
                    'jornada_bruta'    => "00:00",
                    'jornada_util'     => "00:00",
                    'intrajornada'     => "00:00",
                    'adicional_noturno'=> "00:00",
                    'extras_totais'    => "00:00",
                    'extras_50'        => "00:00",
                    'extras_100'       => "00:00",
                    'saldo'            => "00:00",
                    'credito'          => "00:00",
                    'debito'           => "00:00",
                    'almoco'           => "00:00"
                ]
            ];
            //
            $qtdBatidas = count($batidas);

        //
        //- Não houve batidas
        if ($qtdBatidas === 0) {
            if ($isFeriado || $isDSR || $isFacultativo || $isCompensado) {
                $obs = "Dia sem batidas — $tipoDia";
                $saldo = 0;
            } else {
                $obs = "⚠️ Sem batidas — FALTA ou ausência";
                $saldo = -$jornada;
                $credito = 0;
                $debito = abs($saldo);
                $alerta = 1;
            }
        //
        //- Batidas incorretas
        } elseif ($qtdBatidas % 2 !== 0) {
            $obs = "⚠️ Número de batidas incorreto";
            notificarDivergencia($idColab, $dataRef, $conn, $obs);
            $alerta = 1;
            $saldo = $credito = $debito = 0; //- não processa
        } else {
        //
        //- Batidas corretas
            $vetorTrabalhado = calcularJornada( $batidas, $tipoDia, $horario_ini, $horario_fim );
                //
                $totalTrabalhado = $vetorTrabalhado['total_trabalhado'];
                $jornada = $vetorTrabalhado['jornada_util'];                
                $intervalo_almoco = $vetorTrabalhado['n_almoco'];
                $he_50 = $vetorTrabalhado['extras_50'];
                $he_100 = $vetorTrabalhado['extras_100'];
                $adicional_noturno = $vetorTrabalhado['adicional_noturno'];
                //
                $saldo = $vetorTrabalhado['saldo'];
                $credito = $vetorTrabalhado['credito'];
                $debito = $vetorTrabalhado['debito'];
        }
        $bh += $credito - $debito;

        if ($vetorTrabalhado['n_almoco'] < 3600 && $vetorTrabalhado['n_almoco'] > 0) {
            $obs = "⚠️ Intervalo de Almoço irregular";
            notificarDivergencia($idColab, $dataRef, $conn, $obs);
            $alerta = 1;
        }

        //
        //- Ajusta Entradas x Saídas em rh_ponto_registros
        //
            
            $dsBatidas = '';
            $i = 1;
            foreach ($batidas as $b) {

                $tipo = ($i % 2 === 1) ? 'Entrada' : 'Saida';

                $stmt = $conn->prepare("
                    UPDATE rh_ponto_registros
                        SET tipo = :tipo
                        WHERE id = :id
                ");
                $stmt->execute([
                    ':tipo' => $tipo,
                    ':id'   => $b['id']
                ]);
                //
                $origem = 'X';
                if( $b['origem']=='web'  ) $origem = 'W'; //- Batido na Web (navegador)
                if( $b['origem']=='app'  ) $origem = 'A'; //- Batido no App (celular)
                if( $b['origem']=='cron' ) $origem = 'M'; //- ajuste manuais (aplicado pelo CRON)
                //
                $dsBatidas .= date('H:i', strtotime($b['data_hora'])) . " | " . $origem . " | " . $tipo . "<br>";
                $i++;
            }

        //
        // Insere apuração (mesmo sem batidas)
        //
            $stmtIns = $conn->prepare("
                    INSERT INTO rh_ponto_banco_horas 
                        (colaborador_id, data_ref, qtd_batidas, horas_trabalhadas, horas_previstas, 
                         saldo_dia, observacao, alerta, intervalo_almoco, he_50, he_100, adicional_noturno,
                         credito, debito, bh, tipo_dia, cidade_id, colaborador_uf)
                    VALUES (:id, :data, :qtd_batidas, :trab, :prev, :saldo, :obs, :alerta, 
                        :intervalo_almoco, :he_50, :he_100, :adicional_noturno, :credito, :debito, :bh,
                        :tipo_dia, :cidade_id, :colaborador_uf)
                ");
            $stmtIns->execute([
                ':id'    => $idColab,
                ':data'  => $dataRef,
                ':trab'  => $totalTrabalhado,
                ':prev'  => $jornada,
                ':saldo' => $saldo,
                ':obs'   => $obs,
                ':alerta' => $alerta,
                ':qtd_batidas' => $qtdBatidas,
                ':intervalo_almoco' => $intervalo_almoco,
                ':he_50' => $he_50,
                ':he_100' => $he_100,
                ':adicional_noturno' => $adicional_noturno,
                ':credito' => $credito,
                ':debito' => $debito,
                ':bh' => $bh,
                ':tipo_dia' => $tipoDia,
                ':cidade_id' => $cidade_id,
                ':colaborador_uf' => $uf
            ]);

        $obs_final = "➡️ Registro inserido (Fator: $fator)"; 
        $dataAtual = strtotime('+1 day', $dataAtual);

    //
    //- IMPRIME LINHA DETALHE DA TABELA
    //
        $dsBatidas = $dsBatidas ? $dsBatidas : 'Sem batidas';
        $tipoDia = $tipoDia == 'UTIL' ? $tipoDia : "<kbd>$tipoDia</kbd>";
        $dsTotalTrabalhado = $vetorTrabalhado['fmt']['total_trabalhado'];
        $dsAlmoco = $vetorTrabalhado['fmt']['almoco'];
        $ds_he_50 = $vetorTrabalhado['fmt']['extras_50'];
        $ds_he_100 = $vetorTrabalhado['fmt']['extras_100'];
        $ds_adicional_noturno = $vetorTrabalhado['fmt']['adicional_noturno'];
        $dsJornada = formata_hora( $jornada );
        $dsCredito = formata_hora( $credito );
        $dsDebito = formata_hora( $debito );
        $dsBH = formata_hora( $bh );
        $dsSaldo = formata_hora( $saldo );
        //
        if( $intervalo_almoco < 3600 && $intervalo_almoco > 0 ) $dsAlmoco = "<kbd class='bg-danger'>$dsSaldo</kbd>";
        if( $credito > 0 ) $dsCredito = "<kbd class='bg-success'>$dsCredito</kbd>";
        if( $debito > 0 ) $dsDebito = "<kbd class='bg-danger'>$dsDebito</kbd>";
        //
        $diaDaSemana = date('w', strtotime($dataRef));
        $estilo = "";
        if( $diaDaSemana == 0 ) $estilo = "background-color: #f7c2c2ff;";
        if( $diaDaSemana == 6 ) $estilo = "background-color: #c4caf1ff;";
        echo "
            <tr>
                <td style='$estilo' class='text-center'>$dataRef</td>
                <td style='$estilo' class=''>$tipoDia</td>
                <td style='$estilo' class='text-center'>$alerta</td>
                <td style='$estilo' class='text-center'>$qtdBatidas</td>
	            <td style='$estilo' class=''>$dsBatidas</td>
	            <td style='$estilo' class='text-center'>$intervalo_almoco<br>$dsAlmoco</td>
                <td style='$estilo' class='text-center'>$totalTrabalhado<br>$dsTotalTrabalhado</td>
                <td style='$estilo' class='text-center'>$jornada<br>$dsJornada</td>
                <td style='$estilo' class='text-center'>$he_50<br>$ds_he_50</td>
                <td style='$estilo' class='text-center'>$he_100<br>$ds_he_100</td>
                <td style='$estilo' class='text-center'>$adicional_noturno<br>$ds_adicional_noturno</td>
                <td style='$estilo' class='text-center'>$saldo<br>$dsSaldo</td>
                <td style='$estilo' class='text-center'>$credito<br>$dsCredito</td>
                <td style='$estilo' class='text-center'>$debito<br>$dsDebito</td>
                <td style='$estilo' class='text-center'>$bh<br>$dsBH</td>
                <td style='$estilo' class=''>$obs</td>
            </tr>";

    } //- fim do loop das datas
    echo "</tbody>
        </table>";
    //
    // Atualiza ou BANCO DE HORAS
    //
        $stmtInsert = $conn->prepare("
            INSERT INTO rh_ponto_banco_saldo
                    (banco_id, colaborador_id, saldo_total, status, idLogin)
                VALUES (:banco_id, :id, :saldo, 'ABERTO', :idLogin)
        ");
        $stmtInsert->execute([
            ':banco_id' => $banco_id,
            ':id'    => $idColab,
            ':saldo' => $bh,
            ':idLogin' => $idLogin
        ]);        
}

//
//- Funções auxiliares
//
    function notificarDivergencia($idColab, $dataRef, $conn, $obs)
    {
        //
        $idTipo = 2;    //- Aviso 
        $idEvento = 7;  //- Evento de Ponto electrônico divergente
        if( empty($obs) ) $mensagem = "⚠️ Divergência nas batidas do ponto em $dataRef"; else $mensagem = $obs;
        $criado_por = "CRON";
        $sql = "INSERT INTO rh_notificacoes (colaborador_id, criado_por, mensagem, lido_em, link, idTipo, idEvento) 
                VALUES (:colaborador_id, :criado_por, :mensagem, NULL, NULL, :idTipo, :idEvento)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':colaborador_id', $idColab, PDO::PARAM_INT);
        $stmt->bindParam(':idTipo', $idTipo, PDO::PARAM_INT);
        $stmt->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
        $stmt->bindParam(':criado_por', $criado_por, PDO::PARAM_STR);
        $stmt->bindParam(':mensagem', $mensagem, PDO::PARAM_STR);
        $stmt->execute();
        //echo $mensagem . "<br>";
    }

    function calcularJornada(array $batidas, string $tipoDia, string $horario_ini, string $horario_fim): array
    {
        // intervalo contratual obrigatório (1h)
        $intervaloContratual = 3600;

        // normaliza entrada: remove duplicatas, ordena
        // extrai apenas os valores únicos de data_hora
        $datasUnicas = array_unique(array_column($batidas, 'data_hora'));
        $batidas = array_values($datasUnicas);
        sort($batidas);

        // converte para timestamps
        $ts = array_map(fn($b) => strtotime($b), $batidas);

        // se número de batidas é ímpar, descarta última (sem par)
        if (count($ts) % 2 !== 0) {
            array_pop($ts);
        }

        // -----------------------
        // pares entrada -> saída
        // -----------------------
        $pares = [];
        for ($i = 0; $i < count($ts); $i += 2) {
            $entrada = $ts[$i];
            $saida   = $ts[$i+1];

            // corrige caso de saída <= entrada (ex.: atravessou meia-noite)
            if ($saida <= $entrada) {
                $saida = strtotime('+1 day', $saida);
            }

            $pares[] = [
                'entrada' => $entrada,
                'saida'   => $saida,
                'duracao' => max(0, $saida - $entrada)
            ];
        }

        // -----------------------
        // intervalos fora (saída -> próxima entrada)
        // -----------------------
        $intervalos_fora = [];
        for ($i = 0; $i < count($pares) - 1; $i++) {
            $saida = $pares[$i]['saida'];
            $entradaSeguinte = $pares[$i+1]['entrada'];

            // já garantimos que entradaSeguinte > saida (por construção), mas só por segurança:
            if ($entradaSeguinte <= $saida) {
                $entradaSeguinte = strtotime('+1 day', $entradaSeguinte);
            }

            $intervalos_fora[] = [
                'saida'   => $saida,
                'entrada' => $entradaSeguinte,
                'duracao' => max(0, $entradaSeguinte - $saida)
            ];
        }

        // -----------------------
        // base de data para jornada contratual (usa a data da primeira batida)
        // -----------------------
        if (!empty($ts)) {
            $dataBase = date('Y-m-d', $ts[0]);
        } else {
            $dataBase = date('Y-m-d');
        }

        // cria timestamps da jornada contratual (pode atravessar meia-noite)
        $inicioJornada = strtotime("$dataBase $horario_ini");
        $fimJornada    = strtotime("$dataBase $horario_fim");
        if ($fimJornada <= $inicioJornada) {
            $fimJornada = strtotime('+1 day', $fimJornada);
        }

        // jornada bruta e jornada útil (descontando 1h contratual)
        $jornadaBruta = max(0, $fimJornada - $inicioJornada);
        $jornadaUtil  = max(0, $jornadaBruta - $intervaloContratual);

        // -----------------------
        // identificar intervalo de almoço (janela 11:00 - 15:00)
        // - preferimos gap com maior sobreposição com a janela 11-15
        // - se nenhum overlap, escolhemos o maior gap com inicio >= 11:00
        // -----------------------
        $inicioJanela = strtotime("$dataBase 11:00:00");
        $fimJanela    = strtotime("$dataBase 15:00:00");

        $almoco = null;
        $bestOverlap = 0;

        foreach ($intervalos_fora as $g) {
            // calculamos overlap do gap com a janela (considerando horários reais)
            $a1 = max($g['saida'], $inicioJanela);
            $a2 = min($g['entrada'], $fimJanela);
            $overlap = max(0, $a2 - $a1);

            if ($overlap > $bestOverlap) {
                $bestOverlap = $overlap;
                $almoco = $g;
                // armazenar o overlap para referência (opcional)
                $almoco['overlap_com_janela'] = $overlap;
            }
        }

        // fallback: se não houver overlap, escolher maior gap cujo início >= 11:00
        if (!$almoco) {
            $maxDur = 0;
            foreach ($intervalos_fora as $g) {
                if ($g['saida'] >= $inicioJanela && $g['duracao'] > $maxDur) {
                    $maxDur = $g['duracao'];
                    $almoco = $g;
                    $almoco['overlap_com_janela'] = 0;
                }
            }
        }

        // se ainda não encontrou (pouco provável), pega o maior gap de todos
        if (!$almoco && !empty($intervalos_fora)) {
            $maxDur = 0;
            foreach ($intervalos_fora as $g) {
                if ($g['duracao'] > $maxDur) {
                    $maxDur = $g['duracao'];
                    $almoco = $g;
                    $almoco['overlap_com_janela'] = 0;
                }
            }
        }

        // -----------------------
        // total trabalhado (SOMA das durações dos PARES)
        // Observação importante (conforme você pediu): NÃO subtrair o almoço aqui.
        // total_trabalhado = soma(E1-S1, E2-S2, ...)
        // -----------------------
        $totalTrabalhado = 0;
        foreach ($pares as $p) {
            $totalTrabalhado += $p['duracao'];
        }

        // -----------------------
        // intrajornada = maior trecho contínuo de trabalho (maior duracao entre pares)
        // -----------------------
        $intrajornada = 0;
        foreach ($pares as $p) {
            if ($p['duracao'] > $intrajornada) $intrajornada = $p['duracao'];
        }

        // -----------------------
        // adicional noturno: somar overlap de cada par com janelas [22:00 dia N -> 05:00 dia N+1]
        // estratégia:
        //  - para cada par, considere dias entre date(entrada)-1 e date(saida) (cobertura suficiente)
        //  - para cada dia, compute overlap entre par e [day 22:00, day+1 05:00]
        // -----------------------
        $adicionalNoturno = 0;
        foreach ($pares as $p) {
            $start = $p['entrada'];
            $end   = $p['saida'];

            // dia inicial e final (Y-m-d)
            $dayStart = strtotime(date('Y-m-d', $start) . ' 00:00:00');
            $dayEnd   = strtotime(date('Y-m-d', $end) . ' 00:00:00');

            // varre dias de dayStart-1 até dayEnd (inclui janela que começa no dia anterior)
            $d = $dayStart - 86400; // dayStart - 1
            while ($d <= $dayEnd) {
                $nInicio = $d + 22 * 3600;            // d 22:00
                $nFim    = $d + 24 * 3600 + 5 * 3600; // next day 05:00 -> d + 29:00 (equiv.)
                // overlap entre [start,end] e [nInicio,nFim]
                $ini = max($start, $nInicio);
                $fim = min($end, $nFim);
                if ($fim > $ini) {
                    $adicionalNoturno += ($fim - $ini);
                }
                $d += 86400;
            }
        }

        // -----------------------
        // calcular extras conforme tipo de dia
        // regra:
        //  - FERIADO / DSR / FACULTATIVO: tudo vira horas extras (tudo 100% por regra de negocio)
        //  - UTIL: excedente sobre jornada útil (jornada úteis já considera 1h contratual)
        //  - REDUZIDO: usa jornada especial (aqui usamos jornadaBruta - intervalocontratual por padrão)
        // -----------------------
        if (in_array($tipoDia, ['FERIADO','DSR','FACULTATIVO'], true)) {
            // tudo extra
            $extrasTotais = $totalTrabalhado;
            $jornadaUtil = 0;
        } elseif ($tipoDia === 'REDUZIDO') {
            // por padrão, considerar jornadaUtil como já calculada; se houver regra diferente, ajustar aqui
            $extrasTotais = max(0, $totalTrabalhado - $jornadaUtil);
        } else {
            // UTIL
            $extrasTotais = max(0, $totalTrabalhado - $jornadaUtil);
        }

        // quebra em 50% / 100% (até 2h => 50%)
        $extras50  = min($extrasTotais, 2 * 3600);
        $extras100 = max(0, $extrasTotais - 2 * 3600);

        // -----------------------
        // saldo banco de horas (credito / debito)
        // credito = +, debito = - (em segundos)
        // Aplica Tolerância de 10 minutos (para cima e para baixo)
        // -----------------------
            $credito = $debito = 0;
            $saldo = $totalTrabalhado - $jornadaUtil;
            if( abs($saldo)> (10*60) ){
                $credito = $saldo > 0 ? $saldo : 0;
                $debito  = $saldo < 0 ? abs($saldo) : 0;
            }

        // -----------------------
        // formatadores
        // -----------------------
        $fmtHMS = fn($s) => sprintf("%02d:%02d:%02d", floor($s/3600), floor(($s%3600)/60), $s%60);
        //$fmtHM  = fn($s) => sprintf("%02d:%02d", floor($s/3600), floor(($s%3600)/60));

        // -----------------------
        // montagem do retorno
        // -----------------------
        return [
            // metadados
            'tipoDia' => $tipoDia,
            'dataBase' => $dataBase,

            // detalhamento de pares e gaps
            'pares' => $pares,
            'intervalos_fora' => $intervalos_fora,
            'almoco' => $almoco, // pode ser null

            // valores brutos (segundos)
            'total_trabalhado' => $totalTrabalhado,   // soma das duracoes dos pares (NÃO subtrai almoco)
            'jornada_bruta'    => $jornadaBruta,
            'jornada_util'     => $jornadaUtil,
            'intrajornada'     => $intrajornada,
            'adicional_noturno'=> $adicionalNoturno,

            // extras / saldo (em segundos)
            'extras_totais'    => $extrasTotais,
            'extras_50'        => $extras50,
            'extras_100'       => $extras100,
            'saldo'            => $saldo,
            'credito'          => $credito,
            'debito'           => $debito,
            'n_almoco' => $almoco ? $almoco['duracao'] : 0,

            // formatados
            'fmt' => [
                'total_trabalhado' => $fmtHMS($totalTrabalhado),
                'jornada_bruta'    => $fmtHMS($jornadaBruta),
                'jornada_util'     => $fmtHMS($jornadaUtil),
                'intrajornada'     => $fmtHMS($intrajornada),
                'adicional_noturno'=> $fmtHMS($adicionalNoturno),
                'extras_totais'    => $fmtHMS($extrasTotais),
                'extras_50'        => $fmtHMS($extras50),
                'extras_100'       => $fmtHMS($extras100),
                'saldo'            => $fmtHMS(abs($saldo)),
                'credito'          => $fmtHMS($credito),
                'debito'           => $fmtHMS($debito),
                'almoco'           => $almoco ? $fmtHMS($almoco['duracao']) : null
            ]
        ];
    }

    function formata_hora( $segundos ) {
        // Guarda se é negativo
        $neg = $segundos < 0;

        // Trabalha sempre com valor absoluto
        $segundos = abs($segundos);

        // Converte para hh:mm:ss
        $hh = floor($segundos / 3600);
        $mm = floor(($segundos % 3600) / 60);
        $ss = $segundos % 60;

        // Monta string
        $out = sprintf("%02d:%02d:%02d", $hh, $mm, $ss);

        // Se era negativo → prefixa com "-"
        return $neg ? "-" . $out : $out;
    }

