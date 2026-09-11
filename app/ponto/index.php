<?php
//
//- index.php | Módulo de Ponto Eletrônico - MOBILE
//- (C)haia, 30/10/2025
//
session_start();

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

$modulo = 22;

//
//- VERIFICA SE CELULAR ESTÁ LOGADO

    function isMobile() {
        return preg_match(
            '/android|iphone|ipad|ipod|blackberry|mini|windows ce|palm/i',
            $_SERVER['HTTP_USER_AGENT']
        );
    }

    if (!isMobile()) {
        // Se NÃO for mobile, redireciona para a versão web
        header("Location: index_web.php");
        exit;
    }

//
//- PROCESSAMENTO PARA CELULARES
//
include "../includes/conexao_gerar.php";
include "api/inc_verifica_data.php";

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    $idColab = $_SESSION['idColab'];
    $hoje = date('Y-m-d');
}

//$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
//die( json_encode($parametros) );

if (isset($_POST['ponto'])) $batido = "sim";
else $batido = "não";

$batido = "não";

//
//- CÁLCULO DA JORNADA DO TRABALHADOR
//

//
//- Busca o Horário de entrada e saída do colaborador
//
    $sql = "SELECT 
                C.horario_ini, 
                C.horario_fim, 
                S.cidade AS cidade_id,
                S.estado AS colaborador_uf
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
            LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
            where idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idColab' => $idColab]);
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    $horario_ini = $linha['horario_ini'] ?? '08:20'; //- Padrão Gerar
    $horario_fim = $linha['horario_fim'] ?? '18:05'; //- Padrão Gerar
    $cidade_id = $linha['cidade_id'] ?? 3281;  //- Curitiba
    $colaborador_uf = $linha['colaborador_uf'] ?? 'PR'; //- Curitiba-PR
//
//- REGISTRA SESSÃO
//
    $_SESSION['horario_ini'   ] = $horario_ini;
    $_SESSION['horario_fiM'   ] = $horario_fim;
    $_SESSION['cidade_id'     ] = $cidade_id;
    $_SESSION['colaborador_uf'] = $colaborador_uf;

//
//- Verifica se hoje é Feriado ou DSR
//
    $tipoHoje = verifica_data($hoje, $cidade_id, $colaborador_uf, $conn);
    $_SESSION['dsTipoDia'] = $tipoHoje['tipo'];
    $dsTipoDia = "➡️" . $_SESSION['dsTipoDia'];

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

$totalTrabalhado = tempoTotalTrabalhado($batidas);

// Jornada total do colaborador (em segundos), já descontando 1h de almoço
if( $tipoHoje == 'FERIADO' || $tipoHoje == 'FACULTATIVO' ) {
    $jornada = 0;
    $dsJornada = $tipoHoje;
}else{
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

    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#003366">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="icon" href="icons/android/android-launchericon-192-192.png" type="image/png">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }

        .card {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <!-- BOTÃO / ÍCONE DE LOGOUT -->
    <a href="logout.php"
       class="fixed top-4 right-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full 
              hover:bg-red-100 transition"
       title="Sair">

        <!-- Ícone SVG de logout -->
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="w-6 h-6 text-gray-500" 
             fill="none" 
             viewBox="0 0 24 24" 
             stroke="currentColor" 
             stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 
                     01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
        </svg>
    </a>    
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <input type="hidden" id="batido" name="batido" value="<?= $batido ?>">
        <div class="card w-full max-w-sm p-8 bg-white rounded-xl text-center">
            <!-- LOGOMARCA -->
            <img src="../imagens/logo.png" alt="Logo" class="w-32 mx-auto mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Meu Ponto</h1>

            <!-- RELÓGIO -->
            <div class="bg-indigo-100 text-indigo-900 p-4 rounded-lg mb-4">
                <span id="hora" class="text-5xl font-extrabold align-middle text-blue-800"></span>
                <span id="segundos" class="text-2xl align-top ml-1 text-blue-600"></span>
                <p id="dia-semana" class="text-lg font-semibold capitalize mt-2"></p>
                <p id="data-completa" class="text-lg mt-0 font-medium"></p>
                <p id="data-tipo-dia" class="text-lg mt-0 font-medium"><?= $dsTipoDia ?></p>
                <p id="nmColab" class="text-lg mt-0 font-bold"><?= $_SESSION['nmLogin'] ?></p>
            </div>

            <!-- JORNADA -->
            <div class="bg-indigo-100 text-indigo-900 p-4 rounded-lg mb-4">
                <p id="contadorJornada" class="text-sm font-bold text-blue-600 mt-2">
                    Sua jornada diária termina em<br>
                    <spam id='contador' class="text-lg text-gray-700">
                        <?= sprintf('%02d HORAS : %02d MINUTOS', $horas, $minutos) ?>
                        </span>
                </p>
            </div>

            <!-- BOTÃO BATER PONTO -->
            <button id="btn-ponto"
                onclick="registrarPonto()"
                class="w-full py-4 px-6 rounded-xl text-white font-semibold text-xl transition-all duration-300 transform hover:scale-[1.02] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-4 focus:ring-indigo-300">
                Registrar Ponto
            </button>

            <div class="mb-6 mt-6">
                <p class="text-lg font-semibold text-gray-600">Últimos Registros:</p>
                <p id="status-ponto" class="text-xl font-bold text-green-600 mt-2">Nenhum registro ainda.</p>
            </div>

            <div id="feedback-api" class="mt-4 p-3 rounded-lg text-sm hidden"></div>

            <div class="flex gap-4 justify-center">
                <button class="flex-1 py-3 px-4 border-2 border-indigo-600 text-indigo-600 font-semibold rounded-lg 
                   hover:bg-indigo-600 hover:text-white transition"
                    onclick="location.href='api/equipe.php'">
                    <i class="fa-solid fa-users"></i> Equipe
                </button>
                <button class="flex-1 py-3 px-4 border-2 border-indigo-600 text-indigo-600 font-semibold rounded-lg 
                   hover:bg-indigo-600 hover:text-white transition"
                    onclick="meu_rh()">
                    <i class="fa-solid fa-clock"></i> Meu RH
                </button>
            </div>

            <!-- 🔹 Formulário invisível para enviar dados a confirma.php -->
            <form id="formConfirma" method="POST" action="api/confirma.php" class="hidden">
                <input type="hidden" name="idColab" value="<?= $_SESSION['idColab'] ?>">
                <input type="hidden" name="hora" id="hora">
                <input type="hidden" name="lat" id="lat">
                <input type="hidden" name="lon" id="lon">
            </form>

        </div>
    </div>

    <script>
        const btnPonto = document.getElementById('btn-ponto');
        const statusPonto = document.getElementById('status-ponto');
        const feedbackApi = document.getElementById('feedback-api');

        const ultimasBatidas = document.getElementById('ultimasBatidas');
        let faltam = <?= $faltam ?> * 1000; // em ms
        let totalTrabalhado = <?= $totalTrabalhado ?>;

        function meu_rh() {
            const form = document.getElementById('formConfirma');
            form.action = 'api/meu_rh.php';
            form.submit();
        }

        function atualizarRelogio() {
            const agora = new Date();

            // Horas e minutos
            const hora = agora.toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            // Segundos
            const segundos = agora.getSeconds().toString().padStart(2, '0');

            // Atualiza na tela
            document.getElementById('hora').textContent = hora;
            document.getElementById('segundos').textContent = segundos;

            document.getElementById('dia-semana').textContent = agora.toLocaleDateString('pt-BR', {
                weekday: 'long'
            });
            document.getElementById('data-completa').textContent = agora.toLocaleDateString('pt-BR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
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

        setInterval(atualizarRelogio, 1000);

        atualizarRelogio();

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
                        $.post("api/reg_sessao.php",
                            {
                                lat:  document.getElementById('lat').value,
                                lon:  document.getElementById('lon').value,
                                hora: document.getElementById('hora').value
                            }, function(data) {
                                form.submit(); // ✅ Envia para confirma.php
                            }
                        );                        
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

        function ultimos_registros() {
            $.post("api/ultimas_batidas.php", {
                idColab: '<?= $_SESSION['idColab'] ?>'
            }, function(data) {
                statusPonto.innerHTML = data;
            })
        }

        $(document).ready(function() {
            let batido = document.getElementById('batido').value;
            ultimos_registros();
            //
            if (batido == 'sim') {
                let tempo = 60; // segundos
                btnPonto.disabled = true;

                const contador = setInterval(() => {
                    btnPonto.textContent = `Aguarde ${tempo--}s`;
                    if (tempo < 0) {
                        clearInterval(contador);
                        btnPonto.disabled = false;
                        btnPonto.textContent = 'Registrar Ponto';
                    }
                }, 1000);
            }
        });

        if (totalTrabalhado > 0) {
            setInterval(atualizarContador, 1000);
            atualizarContador();
        } else {
            document.getElementById("contador").textContent = "<?= $dsJornada ?>";
        }
    </script>
    <script>
        //
        //- Service Worker do PWA
        //
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker
                .register("sw.js")
                .then(() => console.log("Service Worker registrado"))
                .catch(err => console.error("Erro ao registrar SW", err));
        }
    </script>
    <!-- INSTALL CARD -->
    <div id="install-card" class="hidden fixed bottom-4 left-1/2 -translate-x-1/2 
     bg-white shadow-2xl rounded-2xl p-6 w-80 z-50 flex flex-col items-center text-center">

        <img src="icons/android/android-launchericon-192-192.png" class="w-16 mb-3" />

        <h2 class="text-xl font-bold text-gray-800 mb-2">Instale o app</h2>
        <p class="text-gray-600 text-sm mb-4">
            Instale o aplicativo Gerar RH no seu dispositivo para acesso rápido e offline.
        </p>

        <button id="btn-install"
            class="w-full py-2 rounded-xl bg-indigo-600 text-white font-semibold text-lg hover:bg-indigo-700">
            Instalar Agora
        </button>

        <button onclick="document.getElementById('install-card').classList.add('hidden')"
            class="mt-3 text-gray-500 text-sm">
            Agora não
        </button>
    </div>

    <!-- INSTALAÇÃO DO APP NO DEVICE    -->

    <script>
        let deferredPrompt = null;

        // Detecta quando o PWA pode ser instalado
        window.addEventListener("beforeinstallprompt", (e) => {
            e.preventDefault();
            deferredPrompt = e;

            // Mostra o Install Card
            document.getElementById("install-card").classList.remove("hidden");
        });

        // Botão instalar
        document.getElementById("btn-install").addEventListener("click", async () => {
            if (!deferredPrompt) return;

            deferredPrompt.prompt();
            const choice = await deferredPrompt.userChoice;

            if (choice.outcome === "accepted") {
                console.log("Usuário instalou");
            } else {
                console.log("Usuário recusou");
            }

            deferredPrompt = null;
            document.getElementById("install-card").classList.add("hidden");
        });

        // iOS (não dispara beforeinstallprompt)
        const isIOS = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        const isStandalone = window.navigator.standalone === true;

        if (isIOS && !isStandalone) {
            document.getElementById("install-card").classList.remove("hidden");
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
