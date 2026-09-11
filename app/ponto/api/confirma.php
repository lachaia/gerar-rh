<?php
// confirma.php
// Recebe os dados via POST do app de ponto

session_start();

header('Content-Type: text/html; charset=utf-8');

include dirname(__DIR__) . '/../includes/conexao_gerar.php';
//include dirname(__DIR__) . '/../includes/debug.php';

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Recebe os dados do POST
$idColab = $_SESSION['idColab'] ?? null;
$lat     = $_SESSION['LAT'] ?? null;
$lon     = $_SESSION['LON'] ?? null;
$hora    = $_SESSION['HORA'] ?? null;

//
//- Endereço retornando de localizacao.php
    if (isset($_SESSION['IDENDERECO'])) $idEndereco = $_SESSION['IDENDERECO'];
    else $idEndereco = null;

if ($idEndereco>0) {
    //- Busca o endereço selecionado pelo colaborador
    $sql = "SELECT * FROM rh_ponto_enderecos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$idEndereco]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    $enderecoAtual = $dados['endereco'];
    //
} elseif($idEndereco == -1){
    //- Usa o Endereço Referência (trabalho)
    $enderecoAtual = $_SESSION['enderecoReferencia'];
}else{
    // Obtém endereço atual via OSM
    $enderecoAtual = obterEnderecoOSM($lat, $lon) ?? 'Não identificado';
}
$_SESSION['DSENDERECO'] = $enderecoAtual;
// Função para obter endereço via API do OpenStreetMap (Nominatim)
function obterEnderecoOSM($lat, $lon)
{
    // Garante que sejam números
    $lat = floatval($lat);
    $lon = floatval($lon);

    $url = "https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lon}&format=json&addressdetails=1&accept-language=pt-BR";

    // Nominatim exige User-Agent real (com nome e contato)
    $opts = [
        "http" => [
            "header" => "User-Agent: RH-Ponto/1.0 (chaia@empresa.com.br)\r\n"
        ]
    ];

    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);

    if ($json === false) {
        $error = error_get_last();
        return "Erro ao consultar OSM: " . ($error['message'] ?? 'sem detalhes');
    }

    $data = json_decode($json, true);
    if (!isset($data['address'])) {
        return "Endereço não encontrado";
    }

    $a = $data['address'];
    $logradouro = $a['road'] ?? '';
    $numero     = $a['house_number'] ?? '';
    $bairro     = $a['suburb'] ?? $a['neighbourhood'] ?? '';
    $cidade     = $a['city'] ?? $a['town'] ?? $a['village'] ?? '';
    $uf         = $a['state_code'] ?? '';

    if (!$uf && isset($a['ISO3166-2-lvl4']) && strpos($a['ISO3166-2-lvl4'], 'BR-') === 0) {
        $uf = substr($a['ISO3166-2-lvl4'], 3);
    }

    $partes = array_filter([$logradouro, $numero, $bairro, $cidade ? "$cidade-$uf" : $uf]);
    return implode(', ', $partes);
}
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
    <form action="localizacao.php" method="POST">
        <div class="text-center text-gray-600 mt-4 mb-8 text-2xl">
            Bater Ponto
        </div>
        <div class="text-center text-gray-900 mt-4 mb-8">
            <span id="hora" class="text-6xl font-extrabold align-middle text-blue-800"></span>
            <span id="segundos" class="text-2xl align-top ml-1 text-blue-600"></span>
        </div>

        <div class="max-w-md mx-auto bg-white rounded-lg overflow-hidden shadow-lg">
            <div class="p-6 text-center">
                <h1 id="icon"><i class="fa-solid fa-location-dot"></i></h1>
                <h2 class="text-2xl font-bold mb-4">Localização</h2>
                <p class="card text-gray-600 mb-4">
                    <strong><?= $enderecoAtual ?></strong><br>
                </p>
                <button type="submit" id="btnEditarLocalizacao"
                    class="w-full bg-blue-100 text-black py-2 rounded-lg hover:bg-blue-400 transition">
                    Editar Localização
                </button>
                <button type="button" id="btnConfirmaRegistro" onclick="confirmarRegistro()"
                    class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-700 transition mt-4">
                    Confirma Registro
                </button>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="../index.php">Cancelar</a>
        </div>
        <input type="hidden" id="idColab" name="idColab" value="<?= $idColab ?>">
        <input type="hidden" id="lat" name="lat" value="<?= $lat ?>">
        <input type="hidden" id="lon" name="lon" value="<?= $lon ?>">
        <input type="hidden" id="enderecoReferencia" name="enderecoReferencia" value="<?= $enderecoAtual ?>">
    </form>

    <form action="registrar_ponto.php" method="post" id='formBaterPonto'>
        <input type="hidden" name="colaborador_id" value="<?= $idColab ?>">
        <input type="hidden" name="lat" value="<?= $lat ?>">
        <input type="hidden" name="lon" value="<?= $lon ?>">
        <input type="hidden" name="idEndereco" value="<?= $idEndereco ?>">
        <input type="hidden" name="dsEndereco" value="<?= $enderecoAtual ?>">
        <input type="hidden" name="agora" value="<?= date("Y-m-d H:i:s") ?>">
    </form>

    <form action="../index.php" id="formIndex" method="POST">
        <input type="hidden" name='ponto' value='sim'>
    </form>

    <script>
        async function confirmarRegistro() {
            //
            let btnPonto = document.getElementById('btnConfirmaRegistro');
            btnPonto.disabled = true;
            btnPonto.textContent = 'processando...';
            //
            const form = document.getElementById('formBaterPonto');
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Erro na comunicação com o servidor.');

                const data = await response.json();

                if (data.sucesso) {
                    // Mostra mensagem de sucesso (pode trocar alert por um modal)
                    alert("Aqui: " + data.mensagem);

                    // Redireciona ou atualiza a tela
                    setTimeout(() => {
                        document.getElementById('formIndex').submit();
                    }, 1500);
                } else {
                    alert('Falha: ' + data.mensagem);
                }

            } catch (erro) {
                console.error('Erro:', erro);
                alert('Erro ao registrar ponto. Tente novamente.');
                btnPonto.disabled = false;
                btnPonto.textContent = 'Registrar Ponto';
            }
        }


        function atualizarHora() {
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
        }

        // Atualiza imediatamente e depois a cada segundo
        atualizarHora();
        setInterval(atualizarHora, 1000);
    </script>

</body>

</html>

