<?php
//
//- espelho.php | Rotina dos Espelhos do Ponto
// (C)haia, 25/11/2025
//

session_start();

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

header('Content-Type: text/html; charset=utf-8');

// Recebe os dados do POST
$idColab = $_SESSION['idColab'] ?? null;

if (empty($idColab)) {
    header('Location: ../index.php');
    exit();
}

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

//
//- LE DADOS DOS ESPELHOS PONTO DO COLABORADOR
//
$ano_atual = date('Y');
$mes_atual = date('m');
$sql = "SELECT * 
                FROM rh_ponto_espelhos 
                WHERE colaborador_id = :idColab 
                    AND year(periodo_ini) = $ano_atual
                    AND month(periodo_ini) < $mes_atual
                ORDER BY periodo_ini DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':idColab' => $idColab
]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
$qtd = count($registros);
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

    <div class="relative flex items-center justify-center text-gray-600 mt-2 mb-8 text-xl">
        <!-- BOTÃO / ÍCONE DE HOME -->
        <a href="../index.php"
            class="fixed top-4 left-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-blue-100 transition"
            title="Início">
            <i class="fa-solid fa-house text-gray-500"></i>
        </a>

        <!-- Botão Voltar -->
        <a href="meu_rh.php"
            class="fixed top-4 left-16 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-blue-100 transition"
            title="Voltar">
            <i class="fa-solid fa-chevron-left text-gray-500"></i>
        </a>

        <!-- BOTÃO / ÍCONE DE LOGOUT -->
        <a href="../logout.php"
            class="fixed top-4 right-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-red-100 transition"
            title="Sair">
            <i class="fa-solid fa-right-from-bracket text-gray-500"></i>
        </a>

        <!-- Título centralizado -->
        Espelhos Ponto
    </div>

    <!-- AQUI COMEÇA O CONTAINER MOBILE -->
    <div id="cartoes">
        <?php
        if ($qtd > 0) {
            foreach ($registros as $registro) {
                $id = $registro['id'];
                $periodo_ini = date("d/m/Y", strtotime($registro['periodo_ini']));
                $periodo_fim = date("d/m/Y", strtotime($registro['periodo_fim']));
                $horas_normais = formata_hora($registro['horas_normal']);
                $faltas = formata_hora($registro['faltas']);
                $extras = formata_hora($registro['extras']);
                $status = $registro['status'];
                $arquivo = $registro['arquivo'];
                $colaborador_id = $registro['colaborador_id'];
                //
                $dsStatus = '';
                if ($status == 'GERADO') $dsStatus = '<p class="text-right text-blue-600 font-semibold">Assinar</p>';
                if ($status == 'ASSINADO') $dsStatus = '<p class="text-right text-green-600 font-semibold">Assinado</p>';
                if ($status == 'ALERTA') $dsStatus = '<p class="text-right text-orange-600 font-semibold">Alerta ⚠</p>';
                //
                echo "<div class='max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mb-2' 
                            onclick='ver($id, $colaborador_id, `$arquivo`, `$status`)'>
                    <div class=''>
                        <p class='text-lg font-semibold text-gray-600'>" . $registro['dsMes'] . "</p>
                        <p class='text-center'><b>Período</b> | $periodo_ini - $periodo_fim</p>
                        <table class='w-full mx-auto text-center text-sm'>
                            <tr><th>H.Normais</th><th>Faltas</th><th>H.Extras</th></tr>
                            <tr><th>$horas_normais</th><th>$faltas</th><th>$extras</th></tr>
                        </table>
                        <p class='text-right text-green-600'>$dsStatus</p>
                    </div>  
                  </div>";
            }
        } else { ?>
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-2 text-center">
                Sem Registros!
            </div><?php
        }?>
    </div>
    <div id="assinar" class="hidden flex flex-col h-[100vh]">
        <input type="hidden" id="idCartao">
        <div class="p-3 bg-gray-100 border-t" id="botao"></div>

        <!-- Área do PDF (cresce e ocupa tudo abaixo do botão) -->
        <div id="pdf" class="flex-1 overflow-auto">
            <!-- PDF é renderizado aqui -->
        </div>
    </div>

    <script>
        function ver(id, colaborador_id, arquivo, status) {
            //
            let idCartao = document.getElementById('idCartao');
            idCartao.value = id;
            //
            const botao_assinar = '<button onclick = "assinar()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded">Assinar</button>';
            const botao_voltar = '<button onclick = "voltar()" class="w-full bg-gray-600 hover:bg-blue-700 text-white font-semibold py-3 rounded">Voltar</button>';
            //
            const div_cartoes = document.getElementById('cartoes');
            const div_pdf = document.getElementById('pdf');
            const div_assinar = document.getElementById('assinar');
            const div_botao = document.getElementById('botao');
            //
            div_cartoes.classList.add('hidden');
            div_assinar.classList.remove('hidden');
            //
            if( status == 'GERADO' ) { 
                div_botao.innerHTML = botao_assinar;
            } else {
                div_botao.innerHTML = botao_voltar;
            }
            // Renderiza o PDF
            let url = "";
            if (
                location.hostname === "localhost" ||
                location.hostname === "127.0.0.1" ||
                location.hostname === "::1"
            ) {
                url = `../../includes/pdfjs/web/viewer.html?file=/rh/ponto/docs/${colaborador_id}/${arquivo}`;
            } else{
                url = `../../includes/pdfjs/web/viewer.html?file=/ponto/docs/${colaborador_id}/${arquivo}`;
            }            
            //
            div_pdf.innerHTML = `
                <iframe 
                    src="${url}" 
                    class="w-full h-[90vh]" frameborder="0">
                </iframe>`;
        }

        function voltar(){
            const div_cartoes = document.getElementById('cartoes');
            const div_assinar = document.getElementById('assinar');
            //
            div_cartoes.classList.remove('hidden');
            div_assinar.classList.add('hidden');
        }

        function assinar() {
            const id = document.getElementById('idCartao').value;
            if (!id) {
                alert("ID do Cartão Ponto não encontrado!");
                return;
            }
            //
            if (confirm("Deseja realmente assinar o Cartão Ponto?")) {
                $.post("espelho_assina.php", { id: id })
                    .done(function (retorno) {
                        // Se você quiser apenas exibir mensagem:
                        alert("Cartão Ponto assinado com sucesso!");
                        // OU: atualizar lista
                        location.reload();
                    })
                    .fail(function () {
                        alert("Erro ao assinar o Cartão Ponto. Tente novamente.");
                    }, "json");
            }
        }

    </script>
</body>

</html>
<?php

function formata_hora($segundos)
{
    // Guarda se é negativo
    $neg = $segundos < 0;

    // Trabalha sempre com valor absoluto
    $segundos = abs($segundos);

    // Converte para hh:mm:ss
    $hh = floor($segundos / 3600);
    $mm = floor(($segundos % 3600) / 60);
    $ss = $segundos % 60;

    // Monta string
    $out = sprintf("%02d:%02d", $hh, $mm);

    // Se era negativo → prefixa com "-"
    return $neg ? "-" . $out : $out;
}
