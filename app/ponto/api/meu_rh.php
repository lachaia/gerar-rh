<?PHP
//
//- meu_rh.php | Painel: Meu RH
//- (C)haia, 05/11/2025

session_start();

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

header('Content-Type: text/html; charset=utf-8');

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

// Recebe os dados do POST
$idColab = $_SESSION['idColab'] ?? null;

if (empty($idColab)) {
    header('Location: ../index.php');
    exit();
}

//
//- Busca o Horário de entrada e saída do colaborador
//
$sql = "SELECT horario_ini, horario_fim FROM rh_colaboradores where idColab = :idColab";
$stmt = $conn->prepare($sql);
$stmt->execute([':idColab' => $idColab]);

$linha = $stmt->fetch(PDO::FETCH_ASSOC);
$horario_ini = $linha['horario_ini'];
$horario_fim = $linha['horario_fim'];

//
//- Verifica se hoje é Feriado ou DSR
//
$sql = "SELECT * FROM rh_ponto_calendario WHERE data = CURDATE() LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
if ($linha) {
    $tipoHoje = $linha['tipo'];
    //
    if ($tipoHoje == 'REDUZIDO') {
        $horario_ini = $linha['hora_ini'];
        $horario_fim = $linha['hora_fim'];
    }
} else {
    $tipoHoje = 'NORMAL';
}

// Busca batidas do dia
$stmt = $conn->prepare("
        SELECT data_hora
        FROM rh_ponto_registros
        WHERE colaborador_id = :id
        AND data_hora BETWEEN CURDATE() AND (CURDATE() + INTERVAL 1 DAY)
        ORDER BY data_hora
    ");
$stmt->execute([':id' => $idColab]);
$batidas = $stmt->fetchAll(PDO::FETCH_COLUMN);

//
//- CALCULA O TEMPO TOTAL TRABALHADO
//
$totalTrabalhado = tempoTotalTrabalhado($batidas);

// Jornada total do colaborador (em segundos), já descontando 1h de almoço
if ($tipoHoje == 'FERIADO' || $tipoHoje == 'FACULTATIVO') {
    $jornada = 0;
    $dsJornada = $tipoHoje;
} else {
    $jornada = (strtotime($horario_fim) - strtotime($horario_ini)) - 3600;
    $dsJornada = gmdate('H:i', $jornada);
}

//
//- Nova lógica intercalada: evita valores incorretos em $faltam
//
if ($jornada <= 0) {
    // Caso horários estejam errados no cadastro
    $faltam = 0;
} else {
    $faltam = $jornada - $totalTrabalhado;

    // Se o colaborador já completou ou passou a jornada
    if ($faltam < 0) {
        $faltam = 0;
    }

    // Proteção adicional: se faltar tempo demais (ex: > 12h), considera anomalia
    if ($faltam > 12 * 3600) { // 12 horas
        $faltam = 0;
    }
}

//
//- Converte segundos restantes para horas e minutos
//
$horas   = floor($faltam / 3600);
$minutos = floor(($faltam % 3600) / 60);

//
//- BANCO DE HORAS
//
$sql = "SELECT * 
        FROM RH.rh_ponto_banco_saldo
        WHERE colaborador_id = :idColab
        AND status = 'ABERTO'";
$stmt = $conn->prepare($sql);
$stmt->execute([':idColab' => $idColab]);
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
$saldoSegundos = $linha['saldo_total'] ?? 0;
$bancoHoras = formatarSegundosParaHoraMin($saldoSegundos);

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
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        #icon {
            font-size: 5rem;
            color: #2563eb;
        }

        #horaAtual {
            color: #0a47caff;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <input type="hidden" id="idColab" value="<?= $idColab ?>">

    <div class="relative flex items-center justify-center text-gray-600 mt-2 mb-8 text-2xl">
        <!-- BOTÃO / ÍCONE DE HOME -->
        <a href="../index.php"
            class="fixed top-4 left-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-blue-100 transition"
            title="Início">
            <i class="fa-solid fa-house text-gray-500"></i>
        </a>

        <!-- BOTÃO / ÍCONE DE LOGOUT -->
        <a href="../logout.php"
            class="fixed top-4 right-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-red-100 transition"
            title="Sair">
            <i class="fa-solid fa-right-from-bracket text-gray-500"></i>
        </a>

        <!-- Título centralizado -->
        Meu RH
    </div>

    <!-- AQUI COMEÇA O CONTAINER MOBILE -->
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-2">

        <div class="mb-6 mt-6">
            <p class="text-lg font-semibold text-gray-600 text-center">Últimos Registros</p>
            <p id="ultimasBatidas" class="text-xl font-bold text-green-600 mt-2">Nenhum registro ainda.</p>
        </div>
        <div class='flex-none w-100 bg-white rounded-lg shadow-md p-3 border border-gray-300 text-center'>
            <p class="text-lg font-semibold text-gray-600 text-center mt-2 mb-2">
                <i class="fa-regular fa-clock text-green-600"></i>
                <span id="horaAtual"> <?= date("H:i") ?></span><?= " | " .  strftime("%d, %B, %Y", strtotime("now")) ?>
            </p>
            <!-- REGISTRAR O PONTO -->
            <button id="btn-ponto"
                onclick="registrarPonto()"
                class="w-full py-4 px-6 rounded-xl text-white font-semibold text-xl transition-all duration-300 transform hover:scale-[1.02] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-4 focus:ring-indigo-300">
                Registrar Ponto
            </button>

            <!-- JORNADA -->
            <p id="contadorJornada" class="text-base font-bold text-gray-600 mt-2">
                Sua jornada termina em
                <spam id='contador'>
                    <?= sprintf('%02dh%02d', $horas, $minutos) ?>
                    </span>
            </p>

        </div>
        <!-- BANCO DE HORAS -->
        <div class='flex-none text-base w-100 bg-white rounded-lg shadow-md p-3 border border-gray-300 text-center mt-3'>
            <i class="fa-regular fa-clock text-green-600 font-bold"></i>
            Banco de Horas:
            <span id='bancoHoras' class="font-bold text-gray-700"><?= $bancoHoras ?></spam>
        </div>
        <p class="text-lg font-semibold text-gray-600 text-center mt-6">Controle do Ponto</p>

        <div class="grid grid-cols-3 gap-4 w-full">
            <!-- Botão 1 -->
            <button
                class="bg-white rounded-lg shadow-md border border-gray-300 flex flex-col items-center justify-center hover:bg-gray-100 transition aspect-square"
                onclick="location.href='meu_ponto.php'">
                <i class="fa-solid fa-clock text-4xl mb-2 text-blue-600"></i>
                <span class="text-xs font-semibold text-gray-800">Meu Ponto</span>
            </button>

            <!-- Botão 2 -->
            <button
                class="bg-white rounded-lg shadow-md border border-gray-300 flex flex-col items-center justify-center hover:bg-gray-100 transition aspect-square"
                onclick="location.href='solicitacoes.php'">
                <i class="fa-solid fa-envelope-open-text text-4xl mb-2 text-green-600"></i>
                <span class="text-xs font-semibold text-gray-800">Solicitações</span>
            </button>

            <!-- Botão 3 -->
            <button
                class="bg-white rounded-lg shadow-md border border-gray-300 flex flex-col items-center justify-center hover:bg-gray-100 transition aspect-square"
                onclick="location.href='espelho.php'">
                <i class="fa-solid fa-file-lines text-4xl mb-2 text-purple-600"></i>
                <span class="text-xs font-semibold text-gray-800">Espelho</span>
            </button>
        </div>

        <!-- 🔹 Formulário invisível para enviar dados a confirma.php -->
        <form id="formConfirma" method="POST" action="confirma.php" class="hidden">
            <input type="hidden" name="idColab" value="<?= $_SESSION['idColab'] ?>">
            <input type="hidden" name="hora" id="hora">
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lon" id="lon">
        </form>

    </div>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>


    <script>
        const btnPonto = document.getElementById('btn-ponto');

        const ultimasBatidas = document.getElementById('ultimasBatidas');
        let faltam = <?= $faltam ?> * 1000; // em ms
        let totalTrabalhado = <?= $totalTrabalhado ?>;

        $(document).ready(function() {
            ultimos_registros();
        });

        function ultimos_registros() {
            let idColab = document.getElementById('idColab').value;
            $.post("ultimas_batidas.php", {
                idColab: idColab
            }, function(data) {
                ultimasBatidas.innerHTML = data;
            })
        }

        function atualizarContador() {
            faltam -= 1000;
            if (faltam <= 0) {
                document.getElementById("contador").textContent = "Jornada concluída!";
                return;
            }
            const horas = Math.floor(faltam / (1000 * 60 * 60));
            const minutos = Math.floor((faltam % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((faltam % (1000 * 60)) / 1000);
            document.getElementById("contador").textContent =
                `${horas.toString().padStart(2,'0')}h${minutos.toString().padStart(2,'0')}:${segundos.toString().padStart(2,'0')}s`;
        }

        function atualizarHora() {
            const agora = new Date();
            const hora = agora.toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            document.getElementById('horaAtual').innerHTML = hora;
        }

        if (totalTrabalhado > 0) {
            setInterval(atualizarContador, 1000);
            atualizarContador();
        } else {
            document.getElementById("contador").textContent = "<?= $dsJornada ?>";
        }

        setInterval(atualizarHora, 1000);

        async function registrarPonto() {
            //
            btnPonto.disabled = true;
            btnPonto.textContent = 'Obtendo localização...';

            const form = document.getElementById('formConfirma');
            document.getElementById('hora').value = new Date().toISOString();

            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        document.getElementById('lat').value = pos.coords.latitude;
                        document.getElementById('lon').value = pos.coords.longitude;
                        form.submit(); // ✅ Envia para confirma.php
                    },
                    err => {
                        alert('Não foi possível obter a localização. Verifique as permissões do GPS.');
                        btnPonto.disabled = false;
                        btnPonto.textContent = 'Registrar Ponto';
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    }
                );
            } else {
                alert('Geolocalização não é suportada neste navegador.');
                btnPonto.disabled = false;
                btnPonto.textContent = 'Registrar Ponto';
            }
        }
    </script>
</body>

</html>
<?php
//
//- Função para somar pares de batidas (entrada/saída)
//
function tempoTotalTrabalhado($batidas)
{
    $total = 0;
    $agora = time();
    $qtd = count($batidas);

    for ($i = 0; $i < $qtd; $i += 2) {
        $t1 = strtotime($batidas[$i]);
        if (isset($batidas[$i + 1])) {
            // Par completo: soma normalmente
            $t2 = strtotime($batidas[$i + 1]);
        } else {
            // Última batida sem par: colaborador ainda está trabalhando
            $t2 = $agora;
        }
        $total += ($t2 - $t1);
    }

    return $total; // em segundos
}

/**
 * Formata segundos em "[-]HH:MM" (horas podem exceder 24)
 *
 * @param int $segundos Valor em segundos (pode ser negativo)
 * @return string Ex.: "-02:02" ou "08:45"
 */
function formatarSegundosParaHoraMin($segundos)
{
    $sinal = $segundos < 0 ? '-' : '';
    $abs = abs((int) $segundos);

    $horas = floor($abs / 3600);
    $minutos = floor(($abs % 3600) / 60);

    return sprintf('%s%02d:%02d', $sinal, $horas, $minutos);
}
