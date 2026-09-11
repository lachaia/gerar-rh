<?php
// confirma.php

session_start();

header('Content-Type: text/html; charset=utf-8');
include dirname(__DIR__) . '/../includes/conexao_gerar.php';
//include '../../includes/conexao_gerar.php';

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

date_default_timezone_set('America/Sao_Paulo');

$idColab = $_SESSION['idColab'] ?? null;
$lat     = $_SESSION['LAT'] ?? null;
$lon     = $_SESSION['LON'] ?? null;

$enderecoAtual = $_POST['enderecoReferencia'] ?? null;

//
//- ENDEREÇO DE REFERÊNCIA PADRÃO DO COLABORADOR
//
$sql = "SELECT 
                E.logradouro AS enderecoTrab,
                E.numero AS numeroTrab,
                E.bairro AS bairroTrab,
                E.cidade AS cidadeTrab,
                E.uf AS estadoTrab,
                S.endereco AS enderecoSub,
                S.numero AS numeroSub,
                S.bairro AS bairroSub,
                rh_cidades.nome AS cidadeSub,
                S.estado AS estadoSub
            FROM rh_colaboradores C 
            LEFT JOIN rh_enderecos E ON E.idEndereco = C.idEnderecoTrab
            LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
            LEFT JOIN rh_cidades ON rh_cidades.idCidade = S.cidade
            WHERE C.idColab = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idColab]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Define o endereço de trabalho de referência
    if (!empty($row['enderecoTrab'])) {
        $enderecoReferencia = "{$row['enderecoTrab']}, {$row['numeroTrab']} - {$row['bairroTrab']} - {$row['cidadeTrab']}/{$row['estadoTrab']}";
    } else {
        $enderecoReferencia = "{$row['enderecoSub']}, {$row['numeroSub']} - {$row['bairroSub']} - {$row['cidadeSub']}/{$row['estadoSub']}";
    }
} else {
    $enderecoReferencia = null;
}

//
//- LISTA DOS ENDEREÇOS SALVOS PELO COLABORADOR
//
$sqlUltimos = "SELECT id, nome_local, endereco, criado_em FROM rh_ponto_enderecos 
                    WHERE colaborador_id = ? ORDER BY criado_em DESC LIMIT 5";
$stmtUltimos = $conn->prepare($sqlUltimos);
$stmtUltimos->execute([$idColab]);
$ultimos = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);

$_SESSION['enderecoReferencia'] = $enderecoReferencia;
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        #icon {
            font-size: 3rem;
            color: #2563eb;
        }

        #horaAtual {
            color: #0a47caff;
            font-weight: bold;
        }

        .btn-excluir {
            color: #ef4444;
            cursor: pointer;
            transition: color 0.2s;
        }

        .btn-excluir:hover {
            color: #b91c1c;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <form action="#" method="POST">
        <div class="text-center text-gray-600 mt-4 mb-8 text-2xl">
            <h1 id="icon"><i class="fa-solid fa-location-dot"></i></h1>
        </div>

        <div class="relative w-full mb-4">
            <!-- Buscar endereço input -->
            <input type="text"
                id="endereco"
                class="w-full border border-gray-300 p-2 pr-10 text-center rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                placeholder="Buscar um endereço"
                onfocus='this.value=""'>

            <!-- Ícone de lupa -->
            <button type="button" id="btnBuscarEndereco"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                </svg>
            </button>

            <!-- Lista de sugestões -->
            <div id="sugestoes"
                class="absolute bg-white border border-gray-300 rounded-lg shadow-lg mt-1 w-full z-10 hidden">
            </div>
        </div>

        <div class="max-w-md mx-auto bg-white rounded-lg overflow-hidden shadow-lg">
            <div class="p-6 text-center">
                <h2 class="text-2xl font-bold mb-4">Salvar este endereço</h2>
                <input type="text" id='nomeEndereco' name='nomeEndereco'
                    class="w-full border border-gray-300 p-2 mb-4 text-center rounded-lg"
                    placeholder="Ex.: trabalho">
                <p class="card text-gray-600 mb-4 p-3 bg-gray-50 rounded-lg" id="enderecoAtual">
                    <strong><?= htmlspecialchars($enderecoAtual) ?></strong><br>
                </p>
                <button type="button" id="btnEditarLocalizacao" onclick='salvarLocalizacao()'
                    class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
                    Salvar Localização
                </button>
            </div>
        </div>

        <input type="hidden" id="idColab" name="idColab" value="<?= $idColab ?>">
        <input type="hidden" id="lat" name="lat" value="<?= $lat ?>">
        <input type="hidden" id="lon" name="lon" value="<?= $lon ?>">
        <input type="hidden" id="enderecoReferencia" name="enderecoReferencia" value="<?= htmlspecialchars($enderecoAtual) ?>">
    </form>

    <!-- Lista de Endereços -->
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg mt-6 p-4">
        <h3 class="text-center text-gray-700 font-semibold mb-3">Endereços salvos</h3>
        <div id="listaEnderecos" class="space-y-3">
            <?php
            foreach ($ultimos as $endereco): ?>
                <div class="card bg-blue-100 p-3 rounded-lg flex justify-between items-center">
                    <div>
                        <div class="flex justify-between text-gray-500 text-sm mb-1">
                            <small><?= htmlspecialchars($endereco['nome_local']) ?></small>
                            <small><?= date('d/m/Y H:i', strtotime($endereco['criado_em'])) ?></small>
                        </div>
                        <strong onclick="selecionarEndereco(<?= $endereco['id'] ?>)"><?= htmlspecialchars($endereco['endereco']) ?></strong><br>
                    </div>

                    <span class="btn-excluir" onclick="excluirEndereco(<?= $endereco['id'] ?>, this)">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                </div>
            <?php
            endforeach;
            if (! empty($enderecoReferencia)) { ?>
                <div class="card bg-gray-200 p-3 rounded-lg flex justify-between items-center">
                    <div>
                        <div class="flex justify-between text-gray-500 text-sm mb-1">
                            <small>Referência</small>
                        </div>
                        <strong onclick="selecionarEndereco( -1 )"><?= htmlspecialchars($enderecoReferencia) ?></strong><br>
                    </div>
                </div>
            <?PHP
            }
            ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="../index.php" class="text-blue-600 hover:underline">Cancelar</a>
    </div>

    <form action="confirma.php" method="POST" id="formConfirma">
        <input type="hidden" name='idColab' value='<?= $idColab ?>'>
        <input type="hidden" name='idEndereco' id='idEndereco' value=''>
        <input type="hidden" name='lat' value='<?= $lat ?>'>
        <input type="hidden" name='lon' value='<?= $lon ?>'>
    </form>


    <script>
        function selecionarEndereco(id) {
            $.post("reg_sessao.php",{ idEndereco: id }, function(data){
                document.getElementById('idEndereco').value = id;
                document.getElementById('formConfirma').submit();
            });
        }

        function salvarLocalizacao() {
            var nomeEndereco = $('#nomeEndereco').val();
            var lat = $('#lat').val();
            var lon = $('#lon').val();
            var idColab = $('#idColab').val();
            var enderecoReferencia = $('#enderecoReferencia').val();

            if (!nomeEndereco.trim()) {
                alert('Dê um nome para este endereço.');
                return;
            }

            var enderecoReferencia = $('#enderecoAtual').text().trim();
            $('#enderecoReferencia').val(enderecoReferencia);

            $.ajax({
                url: 'localizacao_aj.php',
                type: 'POST',
                data: {
                    nomeEndereco: nomeEndereco,
                    lat: lat,
                    lon: lon,
                    idColab: idColab,
                    enderecoReferencia: enderecoReferencia
                },
                success: function(response) {
                    console.log(response);

                    // Cria novo cartão dinamicamente
                    let dados = JSON.parse(response);
                    const novoCard = `
                        <div class="card bg-gray-200 p-3 rounded-lg flex justify-between items-center" onclick="selecionarEndereco(${dados.idEndereco})">
                            <div>
                                <strong>${dados.dsEndereco}</strong><br>
                                <small class="text-gray-500">Agora mesmo</small>
                            </div>
                            <span class="btn-excluir" onclick="this.closest('.card').remove()">
                                <i class="fa-solid fa-xmark"></i>
                            </span>
                        </div>
                    `;
                    $('#listaEnderecos').prepend(novoCard);
                    $('#nomeEndereco').val('');
                }
            });
        }

        function excluirEndereco(id, el) {
            if (!confirm('Deseja realmente excluir este endereço?')) return;

            $.ajax({
                url: 'localizacao_aj1.php',
                type: 'POST',
                data: {
                    id: id
                },
                success: function(response) {
                    console.log(response);
                    $(el).closest('.card').fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            });
        }

        $(document).ready(function() {

            // Função para buscar endereços na API do OpenStreetMap
            function buscarEnderecos() {
                let query = $('#endereco').val().trim();
                if (query.length < 5) {
                    $('#sugestoes').addClass('hidden').empty();
                    return;
                }

                $('#sugestoes').removeClass('hidden').html('<div class="p-2 text-gray-500 text-center">Buscando...</div>');

                $.getJSON('https://nominatim.openstreetmap.org/search', {
                    q: query,
                    format: 'json',
                    addressdetails: 1,
                    limit: 5,
                    countrycodes: 'br'
                }, function(data) {
                    $('#sugestoes').empty();

                    if (data.length === 0) {
                        $('#sugestoes').append('<div class="p-2 text-gray-500 text-center">Nenhum endereço encontrado</div>');
                        return;
                    }

                    data.forEach(function(item) {
                        let a = item.address || {};

                        // Monta o endereço formatado
                        let logradouro = a.road || a.pedestrian || a.footway || a.cycleway || '';
                        let numero = a.house_number ? `, ${a.house_number}` : '';
                        let bairro = a.suburb || a.neighbourhood || a.village || '';
                        let cidade = a.city || a.town || a.county || '';
                        let uf = a.state || '';
                        let estado = a.state_code ? a.state_code.toUpperCase() : '';

                        // Monta string limpa (sem campos vazios)
                        let enderecoFormatado = [
                            `${logradouro}${numero}`,
                            bairro,
                            `${cidade} - ${estado || uf}`
                        ].filter(Boolean).join(', ');

                        $('#sugestoes').append(`
                            <button type="button" 
                                class="w-full text-left px-3 py-2 hover:bg-blue-100 border-b last:border-0 sug-item"
                                data-lat="${item.lat}" 
                                data-lon="${item.lon}" 
                                data-endereco="${enderecoFormatado}">
                                ${enderecoFormatado}
                            </button>
                        `);
                    });

                });
            }

            // Clique na lupa dispara a busca
            $('#btnBuscarEndereco').on('click', buscarEnderecos);

            // Pressionar Enter no campo também dispara
            $('#endereco').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarEnderecos();
                }
            });

            // Ao clicar numa sugestão
            $('#sugestoes').on('click', '.sug-item', function() {
                let endereco = $(this).data('endereco');
                let lat = $(this).data('lat');
                let lon = $(this).data('lon');

                // Preenche os campos
                $('#endereco').val(endereco);
                $('#lat').val(lat);
                $('#lon').val(lon);

                $.post("api/reg_sessao.php",
                    {
                        lat:  lat,
                        lon:  lon,
                        endereco: endereco
                    }
                );

                // Atualiza o endereço atual mostrado no card
                $('#enderecoAtual').html(`<strong>${endereco}</strong>`);

                // Atualiza o campo oculto também
                $('#enderecoReferencia').val(endereco);

                // Esconde sugestões
                $('#sugestoes').addClass('hidden').empty();
            });


            // Esconde sugestões ao clicar fora
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#endereco, #sugestoes, #btnBuscarEndereco').length) {
                    $('#sugestoes').addClass('hidden');
                }
            });
        });
    </script>

</body>

</html>