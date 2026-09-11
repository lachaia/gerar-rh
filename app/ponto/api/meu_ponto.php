<?php
//
//- meu_ponto.php | Lista das Batidas do Mês
//- (C)haia, 12/11/2025

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

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

//
//- PERÍODO DE APURAÇÃO
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

//
// Busca dados básicos do colaborador
//
    $sql = "
        SELECT 
            C.idColab, 
            C.horario_ini, 
            C.horario_fim, 
            S.cidade AS cidade_id,
            S.estado AS colaborador_uf
        FROM rh_colaboradores C
        LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
        WHERE C.data_rescisao IS NULL AND C.idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idColab' => $idColab]);
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    $horario_ini = $linha['horario_ini'];
    $horario_fim = $linha['horario_fim'];

//
// Busca últimos registros do banco de horas conforme o período de apuração aberto
//
    $sql = "
        SELECT 
            id,
            data_ref AS data,
            horas_trabalhadas,
            horas_previstas,
            saldo_dia,
            observacao,
            alerta,
            (
				select 1 
                from rh_ponto_solicitacoes S
                where date( S.data_hora ) = H.data_ref
                and H.colaborador_id = S.colaborador_id
                and S.status = 'AGUARDANDO' limit 1
            ) as solicitacao
        FROM rh_ponto_banco_horas H
        WHERE colaborador_id = :id
        AND data_ref BETWEEN :periodo_inicial AND :periodo_final
        ORDER BY data_ref DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id' => $idColab,
        ':periodo_inicial' => $periodo_inicial,
        ':periodo_final' => $periodo_final
    ]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ponto Eletrônico</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }

        .card {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        #horaAtual {
            color: #0a47ca;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <input type="hidden" id="idColab" value="<?= $idColab ?>">

    <!-- BOTÕES SUPERIORES -->
    <div class="relative flex items-center justify-center text-gray-600 mt-2 mb-8 text-xl font-bold">
        <a href="../index.php"
            class="fixed top-4 left-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-blue-100 transition"
            title="Início">
            <i class="fa-solid fa-house text-gray-500"></i>
        </a>

        <a href="../logout.php"
            class="fixed top-4 right-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-red-100 transition"
            title="Sair">
            <i class="fa-solid fa-right-from-bracket text-gray-500"></i>
        </a>
        Meu Ponto
    </div>

    <!-- BLOCO: HORÁRIO PADRÃO -->
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-2">
        <div class="text-center">
            <div class="flex justify-center items-center">
                Horário Padrão:<br>
                <?= $horario_ini . " - " . $horario_fim ?>
            </div>
            <h2 class="text-2xl font-bold text-gray-700" id="horaAtual"></h2>
        </div>
    </div>

    <!-- BLOCO: CARTÕES DO PONTO -->
    <?php
    foreach ($registros as $r) {
        $dia = $r['data'];

        if( $dia == date('Y-m-d') ) {
            continue; //- não processa cartão para hoje!
        }

        $diaSemana = ucfirst(utf8_encode(strftime('%A', strtotime($dia))));

        // Conversões
        $trabalhada = seg2hora($r['horas_trabalhadas']);
        $previstas  = seg2hora($r['horas_previstas']);
        $saldoSeg   = $r['saldo_dia'];

        // Define extra/faltante conforme saldo
        if ($saldoSeg > 0) {
            $extra     = seg2hora($saldoSeg);
            $faltante  = "00:00";
            $corSaldo  = "text-green-600 font-bold";
        } elseif ($saldoSeg < 0) {
            $extra     = "00:00";
            $faltante  = seg2hora($saldoSeg);
            $corSaldo  = "text-red-600 font-bold";
        } else {
            $extra     = "00:00";
            $faltante  = "00:00";
            $corSaldo  = "text-gray-700";
        }

        $saldo = seg2hora($saldoSeg);
        $obs   = $r['observacao'] ?? "";

        // Cor de fundo
        $iconeSolicitacao = "";
        if( $r['solicitacao'] == 1 ) {
            $corFundo = "bg-yellow-100";
            $iconeSolicitacao = "<i class='fa-solid fa-triangle-exclamation text-yellow-500'></i>";
        }else{
            $corFundo = $r['alerta'] == 0 ? "bg-white" : "bg-red-100";
        }
        
        echo "
            <div 
                class='max-w-md mx-auto $corFundo rounded-lg shadow-md p-3 mt-3 relative cursor-pointer hover:bg-gray-50 transition'
                onclick='f_edita_cartao(`{$dia}`)'>

                <!-- Ícone lateral direita -->
                <i class='fa-solid fa-chevron-right absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg'></i>

                <div class='text-center text-lg font-bold text-gray-700'>
                    " . date("d/m/Y", strtotime($dia)) . " | $diaSemana $iconeSolicitacao
                </div>

                <div class='mt-1 text-center'>
                    <div class='grid grid-cols-3 text-sm font-semibold text-gray-500'>
                        <div>H. Extra</div>
                        <div>H. Faltante</div>
                        <div>Saldo</div>
                    </div>

                    <div class='grid grid-cols-3 text-lg font-bold text-gray-700'>
                        <div>$extra</div>
                        <div>$faltante</div>
                        <div class='$corSaldo'>$saldo</div>
                    </div>

                    <div class='mt-2 text-xs text-gray-500'>
                        Trabalhada: $trabalhada<br>
                        $obs
                    </div>
                </div>
            </div>";
    }
    ?>

    <script>
        // Mostra o relógio em tempo real
        function atualizaHora() {
            const agora = new Date();
            const hora = agora.toLocaleTimeString('pt-BR', {
                hour12: false
            });
            $('#horaAtual').text(hora);
        }
        setInterval(atualizaHora, 1000);
        atualizaHora();

        // Função chamada ao clicar em um cartão
        function f_edita_cartao(dia) {
            const idColab = $('#idColab').val();
            window.location.href = "edita_cartao.php?idColab=" + idColab + "&data=" + dia;
        }
    </script>
</body>

</html>
<?php

// Função auxiliar: converte segundos em HH:MM
function seg2hora($segundos)
{
    if ($segundos === null || $segundos === '') return "00:00";
    $horas = floor(abs($segundos) / 3600);
    $minutos = floor((abs($segundos) % 3600) / 60);
    return sprintf("%02d:%02d", $horas, $minutos);
}
