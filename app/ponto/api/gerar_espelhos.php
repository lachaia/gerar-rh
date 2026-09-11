<?php
//
//- gera_espelhos.php | Espelho de Cartão Ponto - PDF
//- (C)haia, 17/11/2025 
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

ini_set('max_execution_time', 600); // 10 minutos
set_time_limit(600);

include dirname(__DIR__) . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

include dirname(__DIR__) . '/../includes/conexao_gerar.php';
//
//- LIMPA TABELA DOS ESPELHOS
//
    //$sql = "TRUNCATE rh_ponto_espelhos";
    $sql = "DELETE FROM rh_ponto_espelhos WHERE status = 'ALERTA'";
    $conn->exec($sql);
//
//- MONTA VETOR DE COLABORADORES
//
$sql = "SELECT distinct H.colaborador_id, P.nome, C.horario_ini, C.horario_fim, S.cidade AS cidade_id, S.estado AS colaborador_uf
            FROM rh_ponto_banco_horas H
            INNER JOIN rh_colaboradores C on C.idColab = H.colaborador_id
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa 
            LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede 
            WHERE C.data_rescisao IS NULL #AND H.colaborador_id = 1
            ORDER BY P.nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

//
//- LOOP - PERCORRE TODOS OS COLABORADORES (vetor)
//
foreach ($colaboradores as $colaborador) {
    //
    $colaborador_id = $colaborador['colaborador_id'];
    $nome           = $colaborador['nome'];
    $horario_ini    = $colaborador['horario_ini'] ?? '08:20';    //- Padrão Gerar
    $horario_fim    = $colaborador['horario_fim'] ?? '18:05';    //- Padrão Gerar
    $cidade_id      = $colaborador['cidade_id'] ?? 3281;       //- Curitiba
    $colaborador_uf = $colaborador['colaborador_uf'] ?? 'PR';       //- Curitiba-PR

    //
    //- MONTA VETOR COM OS MESES QUE HÁ MOVIMENTO
    //
    $sql = "SELECT 
                    distinct colaborador_id, year( data_ref ) as ano, month( data_ref ) as mes
                    FROM rh_ponto_banco_horas
                    WHERE colaborador_id = $colaborador_id #AND year( data_ref ) = 2025 AND month( data_ref ) = 1
                    ORDER BY 1,2,3";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $meses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //
    //- MONTA VETOR COM MESES EM QUE HÁ ALERTAS - ESPELHO REJEITADO
    //
        $sql = "SELECT 
                        distinct colaborador_id, year( data_ref ) as ano, month( data_ref ) as mes
                        FROM rh_ponto_banco_horas
                        WHERE alerta = 1 and colaborador_id = $colaborador_id
                        ORDER BY 1,2,3";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $meses_rejeitados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //
        $meses_rejeitados_idx = [];

        foreach ($meses_rejeitados as $row) {
            $chave = $row['ano'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT);
            $meses_rejeitados_idx[$chave] = true;
        }


    //
    //- MONTA VETOR COM MESES OK - PULAR
    //
        $sql = "SELECT colaborador_id, ano, mes 
                FROM rh_ponto_espelhos 
                WHERE status in ('GERADO','ASSINADO') and colaborador_id = $colaborador_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $meses_ja_ok = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //
        $meses_ja_ok_idx = [];

        foreach ($meses_ja_ok as $row) {
            $chave = $row['ano'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT);
            $meses_ja_ok_idx[$chave] = true;
        }


    // 
    // LOOP - PERCORRE TODOS OS MESES (vetor)
    //   
    //echo "Colaborador: {$nome}<br>";

    foreach ($meses as $m) {
        //
        $chave = $m['ano'] . '-' . str_pad($m['mes'], 2, '0', STR_PAD_LEFT);

        if (isset($meses_ja_ok_idx[$chave])) {
            continue;
        }

        if (isset($meses_rejeitados_idx[$chave])) {
            espelho_rejeitado($conn, $colaborador_id, $m['ano'], $m['mes']);
            continue;
        }

        //
        //- RENDERIZA O ESPELHO DO PONTO
        //
            $ano = $m['ano'];
            $mes = $m['mes'];
            //
            ob_start();
            $vetor = gerarEspelhoMensal($conn, $colaborador, $ano, $mes);
            $html = ob_get_clean();
            //continue;
        //
        //- GERA O PDF
        //
            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);   // <<< AQUI VOCÊ ESTÁ CRIANDO O OBJETO

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

        //
        //- SALVA O PDF NO DIRETÓRIO DOCS
        //
            $dir = "../docs/$colaborador_id/";
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = "espelho_{$colaborador_id}_{$ano}_{$mes}.pdf";
            file_put_contents($dir . $filename, $dompdf->output());

        //
        //- SALVA DADOS NO RH_PONTO_ESPELHOS
        //
            espelho_gerado($conn, $colaborador_id, $ano, $mes, $vetor['normal'], $vetor['faltas'], $vetor['extras'], $filename);

        echo "<p>Gerado o espelho de {$nome} - {$mes}/{$ano}</p>";
    }
            
}

die("FIM DO PROGRAMA");

function gerarEspelhoMensal($conn, $colaborador, $ano, $mes)
{
    $dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    $dsMes = dsMes($mes);
    //
    // GERA UM PDF PARA O COLABORADOR E MÊS ESPEFICICADO
    //
        $colaborador_id = $colaborador['colaborador_id'];
        $nome           = $colaborador['nome'];
        $horario_ini    = $colaborador['horario_ini'] ?? '08:20';
        $horario_fim    = $colaborador['horario_fim'] ?? '18:05';

        // 
        // 1 — Define o período
        // 
        $periodo_ini = "$ano-$mes-01";
        $periodo_fim = date("Y-m-t", strtotime($periodo_ini)); // último dia do mês
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="utf-8">
        <style>
            /* Ajustes de tabela para o Dompdf */
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
            }

            th,
            td {
                padding: 6px;
                border: 1px solid #444;
                font-size: 11px;
                font-family: Arial, Helvetica, sans-serif;
            }

            th {
                background: #eaeaea;
                text-align: center;
            }

            .header {
                width: 100%;
                margin-bottom: 15px;
                text-align: center;
            }

            .text-center{
                text-align: center;
            }

            .header img {
                width: 120px;
                float: left;
            }

            .titulo {
                font-size: 22px;
                margin-top: 20px;
                font-weight: bold;
            }

            .subtitulo {
                font-size: 14px;
                margin-top: 5px;
                margin-bottom: 20px;
            }

            .info-box {
                margin-top: 10px;
                font-size: 13px;
                clear: both; /* garante que não encavala com a logo */
            }

            .info-box strong {
                display: inline-block;
                width: 130px;
            }

            kbd{
                background-color: black;
                color: white;
            }

            .text-primary{
                color: blue;
            }

            .text-danger{
                color: red;
            }
        </style>            
        </head>
        <body>        
        <div class="container">

            <!-- Cabeçalho -->
            <div class="header">
                <img src="https://rh.gerar.org.br/imagens/logo.png">
                <div class="titulo">Espelho do Ponto</div>
            </div>

            <!-- Informações do colaborador -->
            <div class="info-box">
                <br>
                <div><strong>Colaborador:</strong> <?= "$colaborador_id - $nome" ?></div>
                <div><strong>Horário padrão:</strong> <?= $horario_ini ?> às <?= $horario_fim ?></div>
                <div><strong>Mês referência:</strong> <?= $dsMes ?>/<?= $ano ?></div>
                <div><strong>Período:</strong> <?= date('d/m/Y', strtotime($periodo_ini)) ?> a <?= date('d/m/Y', strtotime($periodo_fim)) ?></div>
                <br>
            </div>

            <!-- Tabela Principal -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Dia</th>
                        <th>Tipo</th>
                        <th>Batidas</th>
                        <th>Trabalhado</th>
                        <th>Extras</th>
                        <th>Faltas</th>
                        <th>Obs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * 
                            FROM rh_ponto_banco_horas H
                            WHERE colaborador_id = :colaborador_id and year( data_ref ) = $ano and month( data_ref ) = $mes
                            ORDER BY data_ref";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':colaborador_id', $colaborador_id);
                    $stmt->execute();
                    $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    //
                    $total_normal = 0;
                    $total_extras = 0;
                    $total_faltas = 0;
                    //
                    foreach ($linhas as $linha): 
                        $data_ref = $linha['data_ref'];
                        $tipo_dia = $linha['tipo_dia'];
                        $data = new DateTime($data_ref);
                        $dia_semana = $dias[$data->format('w')];
                        $batidas = "-";
                        //
                        if( $dia_semana == 'Dom') $dia_semana = '<b class="text-danger">Dom</b>';
                        if( $dia_semana == 'Sáb') $dia_semana = '<b class="text-primary">Sáb</b>';
                        if( $tipo_dia == 'FERIADO') $tipo_dia = '<kbd>Feriado</kbd>';
                        if( $tipo_dia == 'UTIL') $tipo_dia = 'Útil';
                        //
                        if( $linha['qtd_batidas'] > 0 ) {
                            $batidas = monta_batidas($conn, $colaborador_id, $data_ref); 
                        }
                        //
                        ?>
                        <tr>
                            <td class='text-center'><?= date('d/m/Y', strtotime($linha['data_ref'])) ?></td>
                            <td class='text-center'><?= $dia_semana ?></td>
                            <td class='text-center'><?= $tipo_dia ?></td>
                            <td class='text-center'><?= $batidas ?></td>
                            <td class='text-center'><?= segundosParaHora( $linha['horas_trabalhadas'] ) ?></td>
                            <td class='text-center'><?= segundosParaHora( $linha['credito'          ] ) ?></td>
                            <td class='text-center'><?= segundosParaHora( $linha['debito'           ] ) ?></td>
                            <td><?= $linha['observacao'] ?? '' ?></td>
                        </tr>
                    <?php
                    $total_normal += $linha['horas_trabalhadas'];
                    $total_extras += $linha['credito'];
                    $total_faltas += $linha['debito']; 
                    endforeach; 
                    $total_saldo = $total_extras - $total_faltas;
                    ?>
                </tbody>
            </table>

            <!-- Totais -->
            <div class="info-box" style="margin-top:20px;">
                <div><strong>Total Extras:</strong> <?= segundosParaHora( $total_normal ) ?></div>
                <div><strong>Total Extras:</strong> <?= segundosParaHora( $total_extras ) ?></div>
                <div><strong>Total Faltas:</strong> <?= segundosParaHora( $total_faltas ) ?></div>
                <div><strong>Saldo Final:</strong>  <?= segundosParaHora( $total_saldo  ) ?></div>
            </div>

        </div>

    <?php
    //
    //- retorna vetor com saldos
    //
        $saldos = [
            'normal' => $total_normal,
            'extras' => $total_extras,
            'faltas' => $total_faltas
        ];
        return $saldos;
}

function segundosParaHora($segundos) {
    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos % 3600) / 60);
    return sprintf('%02d:%02d', $horas, $minutos);
}

function monta_batidas($conn, $colaborador_id, $data_ref) {

    // Buscar registros ordenados pelo datetime
    $sql = "SELECT tipo, data_hora
            FROM RH.rh_ponto_registros
            WHERE colaborador_id = :colaborador_id 
              AND DATE(data_hora) = :data_ref
            ORDER BY data_hora";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':colaborador_id', $colaborador_id, PDO::PARAM_INT);
    $stmt->bindParam(':data_ref', $data_ref, PDO::PARAM_STR);
    $stmt->execute();

    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = "";
    $entrada = null;

    foreach ($registros as $reg) {

        // Formatando hora
        $hora = date('H:i', strtotime($reg['data_hora']));

        if ($reg['tipo'] === 'Entrada') {
            $entrada = $hora;
        } 
        else if ($reg['tipo'] === 'Saida' && $entrada !== null) {
            $resultado .= "{$entrada} às {$hora}<br>";
            $entrada = null; // limpa para o próximo par
        }
    }

    return $resultado;
}

function espelho_rejeitado($conn, $colaborador_id, $ano, $mes) {
    //
    $periodo_ini = "{$ano}-{$mes}-01";
    $periodo_fim = date('Y-m-t', strtotime($periodo_ini));
    $dsMes = dsMes($mes);
    //
    $sql = "INSERT INTO rh_ponto_espelhos 
            (colaborador_id, ano, mes, dsMes, periodo_ini, periodo_fim, horas_normal, faltas, extras, status, arquivo)
            VALUES 
            (:colab_id, :ano, :mes, :dsMes, :ini, :fim, :normal, :faltas, :extras, :status, :arquivo)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':colab_id'=> $colaborador_id,
        ':ano'     => $ano,
        ':mes'     => $mes,
        ':dsMes'   => $dsMes,
        ':ini'     => $periodo_ini,
        ':fim'     => $periodo_fim,
        ':normal'  => 0,
        ':faltas'  => 0,
        ':extras'  => 0,
        ':status'  => 'ALERTA',
        ':arquivo' => null
    ]);
    return;
}

function espelho_gerado($conn, $colaborador_id, $ano, $mes, $normal, $faltas, $extras, $arquivo) {
    //
    $periodo_ini = "{$ano}-{$mes}-01";
    $periodo_fim = date('Y-m-t', strtotime($periodo_ini));
    $dsMes = dsMes($mes);
    //
    $sql = "INSERT INTO rh_ponto_espelhos 
            (colaborador_id, ano, mes, dsMes, periodo_ini, periodo_fim, horas_normal, faltas, extras, status, arquivo)
            VALUES 
            (:colab_id, :ano, :mes, :dsMes, :ini, :fim, :normal, :faltas, :extras, :status, :arquivo)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':colab_id'=> $colaborador_id,
        ':ano'     => $ano,
        ':mes'     => $mes,
        ':dsMes'   => $dsMes,
        ':ini'     => $periodo_ini,
        ':fim'     => $periodo_fim,
        ':normal'  => $normal,
        ':faltas'  => $faltas,
        ':extras'  => $extras,
        ':status'  => 'GERADO',
        ':arquivo' => $arquivo
    ]);
    return;
}

function dsMes( $mes ) {
    $mes = (int) $mes; // garante inteiro
    switch ($mes) {
        case 1: return 'Janeiro';
        case 2: return 'Fevereiro';
        case 3: return 'Março';
        case 4: return 'Abril';
        case 5: return 'Maio';
        case 6: return 'Junho';
        case 7: return 'Julho';
        case 8: return 'Agosto';
        case 9: return 'Setembro';
        case 10: return 'Outubro';
        case 11: return 'Novembro';
        case 12: return 'Dezembro';
        default: return 'Inválido'; // ou "Mês inválido"
    }
}