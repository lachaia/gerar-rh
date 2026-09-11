<?php
// solicitacoes.php | Página | Lista Solicitações de Ajustes
// (C)haia, 17/11/2025

session_start();

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo'); // Ajusta fuso horário
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

include dirname(__DIR__) . '/../includes/conexao_gerar.php';
//include dirname(__DIR__) . '/../includes/debug.php';

// Quem sou eu?
$idColab = $_SESSION['idColab'];

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }

        .card {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <input type="hidden" id="idColab" value="<?= $idColab ?>">

    <!-- BOTÕES SUPERIORES -->
    <div class="relative flex items-center justify-center text-gray-600 mt-2 mb-8 text-xl font-bold">
        <!-- Botão Início -->
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

        <!-- Botão Sair -->
        <a href="../logout.php"
            class="fixed top-4 right-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-red-100 transition"
            title="Sair">
            <i class="fa-solid fa-right-from-bracket text-gray-500"></i>
        </a>
        Solicitações
    </div>
    <div id='divSolicitacoes'>
    <?PHP
    //
    //- Busca lista de solicitações do colaborador
    //
        $sql = "SELECT * 
                FROM rh_ponto_solicitacoes 
                WHERE colaborador_id = :idColab 
                ORDER BY data_hora DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idColab' => $idColab
        ]);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros as $registro) {
            $solicitacao_id = $registro['id'];
            $solicitado_em = date('d/m/Y H:i', strtotime($registro['solicitado_em']));
            $data_hora = $registro['data_hora'];
            $data = date('d/m/Y', strtotime($data_hora));
            $hora = date('H:i', strtotime($data_hora));
            $tipo = $registro['tipo'];
            $status = $registro['status'];
            $diaSemana = diaSemanaBR($data); 
            //
            $corFundo = "";
            switch ($status) {
                case 'AGUARDANDO':
                    $corFundo = "bg-yellow-100";
                    break;
                case 'APROVADO':
                    $corFundo = "bg-green-100";
                    break;
                case 'REJEITADO':
                    $corFundo = "bg-red-100";
                    break;
                default:
                    $corFundo = "bg-gray-100";
                    break;
            }
            //
            echo "
            <div 
                class='max-w-md mx-auto $corFundo rounded-lg shadow-md p-3 mt-3 relative cursor-pointer hover:bg-gray-50 transition'
                onclick='f_ver_cartao(`{$solicitacao_id}`)'>

                <!-- Ícone lateral direita -->
                <i class='fa-solid fa-chevron-right absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg'></i>

                <div class='grid grid-cols-2 text-sm font-semibold text-gray-500'>
                    <div>$tipo</div>
                    <div>$status</div>
                </div>
                <div class='grid grid-cols-2 text-sm font-semibold text-gray-500'>
                    <div>$data | $hora</div>
                    <div>$diaSemana</div>
                </div>
                <div class='grid grid-cols-1 text-sm font-semibold text-gray-500'>
                    <div>Solicitado em: $solicitado_em</div>
                </div>

            </div>";
        }
    ?>
    </div>
    
    <!-- Modal de Visualizar Decisão do Gestor -->
    <div id="modalDecisao"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">

        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">

            <!-- Título e botão Fechar -->
            <div class="flex justify-between items-center mb-4">
                <h2 id="modalTitulo" class="text-xl font-semibold text-gray-700">Decisão do Gestor</h2>
                <button onclick="f_fechar_modal('modalDecisao')" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Corpo da Decisão -->
            <div class="space-y-4">

                <div>
                    <span class="text-sm text-gray-500 font-semibold">Tipo da Solicitação</span>
                    <div id="tipoSolicitacao"
                        class="mt-1 border border-gray-300 rounded-md p-2 text-center bg-gray-100"></div>
                </div>
                <div>
                    <span class="text-sm text-gray-500 font-semibold">Solicitado em</span>
                    <div id="solicitado_em"
                        class="mt-1 border border-gray-300 rounded-md p-2 text-center bg-gray-100"></div>
                </div>

                <div>
                    <span class="text-sm text-gray-500 font-semibold">Data/Hora do Ponto</span>
                    <div id="decisaoDataHora"
                        class="mt-1 border border-gray-300 rounded-md p-2 text-center bg-gray-100"></div>
                </div>

                <div>
                    <span class="text-sm text-gray-500 font-semibold">Motivo da Solicitação</span>
                    <div id="decisaoMotivo"
                        class="mt-1 border border-gray-300 rounded-md p-3 bg-gray-100"></div>
                </div>

                <div>
                    <span class="text-sm text-gray-500 font-semibold">Decisão do Gestor</span>
                    <div id="decisaoStatus" class="mt-1 font-semibold px-3 py-2 rounded-md text-center">
                    </div>
                </div>

            </div>

            <!-- Rodapé -->
            <div class="flex justify-end mt-6">
                <button onclick="f_fechar_modal('modalDecisao')"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Fechar
                </button>
            </div>

        </div>
    </div>   

    <script>

        function f_ver_cartao(solicitacao_id) {
            $.post(
                "solicitacoes_aj.php",
                {
                    solicitacao_id: solicitacao_id
                },
                function (data) {
                    const linha = JSON.parse(data);
                    document.getElementById('tipoSolicitacao').innerHTML = linha.dsTipo;
                    document.getElementById('solicitado_em').innerHTML = linha.solicitado_em;
                    document.getElementById('decisaoDataHora').innerHTML = linha.data_hora;
                    document.getElementById('decisaoMotivo').innerHTML = linha.motivo;
                    document.getElementById('decisaoStatus').innerHTML = linha.status + "<br>" + linha.decisao_obs;
                    //
                    const modal = document.getElementById('modalDecisao');
                    modal.classList.remove('hidden');
                }
            );
        }

        function f_fechar_modal(janela) {
            document.getElementById(janela).classList.add('hidden');
        }

    </script>
</body>
</html>
<?php 

function diaSemanaBR($data) {
    $dias = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    return $dias[date('N', strtotime($data))];
}
