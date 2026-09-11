<?PHP
//
//- edita_cartao.php | Detalhe da Jornada - Edita o cartão
// (C)haia, 12/11/2025
//

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include dirname(__DIR__) . '/../includes/conexao_gerar.php';
include "inc_verifica_data.php";

if (isset($_GET['data'])) $dia = $_GET['data'];
if (isset($_GET['idColab'])) $idColab = $_GET['idColab'];

//
// Busca dados básicos do colaborador
//
    $sql = "SELECT 
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

    $horario_ini    = $linha['horario_ini'];
    $horario_fim    = $linha['horario_fim'];
    $cidade_id      = $linha['cidade_id'];
    $colaborador_uf = $linha['colaborador_uf'];

$dataFormatada = strftime('%d/%m/%Y | %A', strtotime($dia));
$dataFormatada = ucfirst(utf8_encode($dataFormatada));

//
//- VERIFICA SE A DATA É DIA UTIL OU NÃO
//
    $vetor = verifica_data( $dia, $cidade_id, $colaborador_uf, $conn );
    $dia_util = $vetor['tipo'];

    $dataFormatada .= " |&nbsp;<span class='text-blue-600'>$dia_util</span>";

//
//- VERIFICA SE TEM BATIDAS NO DIA SELECIONADO
//
    $sql = "SELECT *
            FROM rh_ponto_registros
            WHERE colaborador_id = :idColab
            AND DATE(data_hora) = :dia
            ORDER BY data_hora
        ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':idColab' => $idColab,
        ':dia' => $dia
    ]);
    $temBatidas = ($stmt->rowCount() > 0);
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
        <a href="meu_ponto.php"
            class="fixed top-4 left-16 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-blue-100 transition"
            title="Voltar">
            <i class="fa-solid fa-chevron-left text-gray-500"></i>
        </a>

        <!-- Botão Sair -->
        <a href="logout.php"
            class="fixed top-4 right-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-red-100 transition"
            title="Sair">
            <i class="fa-solid fa-right-from-bracket text-gray-500"></i>
        </a>
        Detalhes
    </div>

    <!-- BLOCO: DATA FORMATADA -->
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mt-2">
        <div class="text-center">
            <div class="flex justify-center items-center text-bold text-gray-700 text-lg">
                <?= $dataFormatada ?>
            </div>
        </div>
    </div>    

    <!-- BLOCO: HORÁRIO PADRÃO -->
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mt-2">
        <div class="text-center">
            <div class="flex justify-center items-center">
                Horário Padrão:
                <?= $horario_ini . " - " . $horario_fim ?>
            </div>
        </div>
    </div>

    <!-- BLOCO: REGISTROS DO DIA -->
    <div class="relative flex items-center justify-center text-gray-600 mt-8 mb-4 text-xl font-bold">
        Registros do dia
    </div>
    <?PHP
    //
    if ($temBatidas) {
        echo "<table class='min-w-full divide-y divide-gray-200 border border-gray-300 text-sm'>";
        echo "<tbody class='divide-y divide-gray-200'>";
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $horaFormatada = strftime('%H:%M', strtotime($linha['data_hora']));
            $tipo = $linha['tipo'];
            $botoes = '
                <button class="px-2 py-1 border border-yellow-500 text-yellow-500 rounded-md hover:bg-yellow-500 hover:text-white transition" 
                    onclick="abrirModalEditar(' . $linha['id'] . ')">
                    <i class="fa-solid fa-pen"></i>
                </button>

                <button class="px-2 py-1 border border-red-500 text-red-500 rounded-md hover:bg-red-500 hover:text-white transition" 
                    onclick="abrirModalExcluir(' . $linha['id'] . ')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            ';
            echo "
            <tr class='hover:bg-gray-50'>
                <td class='px-4 py-2'>$tipo</td>
                <td class='px-4 py-2'>$horaFormatada</td>
                <td class='px-4 py-2'>$botoes</td>
            </tr>";
        }
        echo "
        </tbody>
        </table>";
    } else {
        echo "
            <div class='max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mt-2'>
                <div class='text-center'>
                    <div class='flex justify-center items-center text-bold text-gray-700 text-base'>
                        Nenhuma registro de ponto para hoje.
                    </div>
                </div>
            </div>
        ";
    }

    ?>
    <button
        type="button"
        class="w-full mt-2 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition"
        onclick="f_incluir('<?= $dia ?>')">
        Incluir acima novo registro
    </button>

    <!-- BLOCO: MINHAS SOLICITAÇÕES -->
    <div class="relative flex items-center justify-center text-gray-600 mt-8 mb-4 text-xl font-bold">
        Solicitações
    </div>
    <?PHP
    $sql = "SELECT * 
            FROM rh_ponto_solicitacoes
            WHERE colaborador_id = :idColab
            AND DATE(data_hora) = :dia
            ORDER BY data_hora";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':idColab' => $idColab,
        ':dia' => $dia
    ]);
    //
    if ($stmt->rowCount() > 0) {
        echo "<table class='min-w-full divide-y divide-gray-200 border border-gray-300 text-sm'>";
        echo "<tbody class='divide-y divide-gray-200'>";
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $horaFormatada = strftime('%H:%M', strtotime($linha['data_hora']));
            $status = $linha['status'];
            $tipo = $linha['tipo'];
            $id = $linha['id'];
            //
            $dsStatus = $status;
            if ($status == 'AGUARDANDO')
                $dsStatus = "<span class='w-full px-2 py-1 text-xs font-semibold rounded bg-yellow-400 text-gray-900 flex items-center justify-center gap-1'>
                    <i class='fa-regular fa-hourglass'></i> Gestor
                 </span>";

            if ($status == 'APROVADO')
                $dsStatus = "<span class='w-full px-2 py-1 text-xs font-semibold rounded bg-green-500 text-white text-center'>
                    Aprovado
                 </span>";

            if ($status == 'REJEITADO')
                $dsStatus = "<span class='w-full px-2 py-1 text-xs font-semibold rounded bg-red-500 text-white text-center'>
                    Rejeitado
                 </span>";

            //
            $botoes = "
                <button class='px-2 py-1 border border-blue-500 text-blue-500 rounded-md hover:bg-yellow-500 hover:text-white transition' 
                    onclick='f_ver_cartao($id)'>
                    <i class='fa-solid fa-magnifying-glass'></i>
                </button>";

            if ($status == 'AGUARDANDO'){
                $botoes .= "
                    <button class='px-2 py-1 border border-red-500 text-red-500 rounded-md hover:bg-red-500 hover:text-white transition' 
                        onclick='excluir_solicitacao($id)'>
                        <i class='fa-solid fa-trash'></i>
                    </button>";
            }
            echo "
                <tr class='hover:bg-gray-50'>
                    <td class='px-4 py-2'>$tipo</td>
                    <td class='px-4 py-2'>$horaFormatada</td>
                    <td class='px-4 py-2'>$dsStatus</td>
                    <td class='px-4 py-2'>$botoes</td>
                </tr>";
        }
    } else {
        // nenhum registro encontrado
        echo "<div class='text-center'>Nenhuma solicitação encontrada.</div>";
        if( $dia_util == 'UTIL' && ! $temBatidas ) {
            ?>
            <button
                type="button"
                class="w-full mt-4 px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition"
                onclick="abrirModalAbono('<?= $dia ?>')">
                Solicitar Abono de Falta
            </button>
            <?PHP            
        }
    }
    //    

    ?>
    <!-- Modal de Batidas -->
    <div id="modalBatida"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">

            <!-- Título e botão Fechar -->
            <div class="flex justify-between items-center mb-4">
                <h2 id="modalTitulo" class="text-xl font-semibold text-gray-700">Nova Batida</h2>
                <button onclick="f_fechar_modal('modalBatida')" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Formulário -->
            <form id="formBatida" onsubmit="salvarBatida(event)">
                <input type="hidden" id="batidaId">
                <input type="hidden" id="tipoSolicitacao">

                <div class="mb-3">
                    <label for="dataBatida" class="block text-sm font-medium text-gray-600">Data</label>
                    <input type="date" id="dataBatida" name="dataBatida"
                        class="w-full mt-1 border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center"
                        required>
                </div>

                <div class="mb-3">
                    <label for="horaBatida" class="block text-sm font-medium text-gray-600">Hora</label>
                    <input type="time" id="horaBatida" name="horaBatida"
                        class="w-full mt-1 border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center"
                        required>
                </div>

                <div class="mb-3">
                    <label for="motivo" class="block text-sm font-medium text-gray-600">Motivo</label>
                    <textarea id="motivo" name="motivo" rows="2"
                        class="w-full mt-1 border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Descreva o motivo do ajuste..." required></textarea>
                </div>

                <!-- Rodapé -->
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="f_fechar_modal('modalBatida')"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>

                    <button type="submit" id="btnSalvar"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        Salvar
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal de Abono de Falta -->
    <div id="modalAbono" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">

            <!-- Título e botão Fechar -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-700">Solicitar Abono de Falta</h2>
                <button onclick="f_fechar_modal('modalAbono')" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Formulário -->
            <form id="formAbono" onsubmit="salvarAbono(event)">
                <input type="hidden" id="idColabAbono" value="<?= $idColab ?>">

                <div class="mb-3">
                    <label for="dataFalta" class="block text-sm font-medium text-gray-600">Data da Falta</label>
                    <input type="date" id="dataFalta" name="dataFalta"
                        class="w-full mt-1 border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center"
                        required>
                </div>

                <div class="mb-3">
                    <label for="motivoAbono" class="block text-sm font-medium text-gray-600">Motivo / Justificativa</label>
                    <textarea id="motivoAbono" name="motivoAbono" rows="3"
                        class="w-full mt-1 border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Descreva o motivo do abono..." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="arquivoAbono" class="block text-sm font-medium text-gray-600">Anexo (opcional)</label>
                    <input type="file" id="arquivoAbono" name="arquivoAbono"
                        class="w-full mt-1 border border-gray-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Rodapé -->
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="f_fechar_modal('modalAbono')"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>

                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        Enviar Solicitação
                    </button>
                </div>
            </form>

        </div>
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

        function abrirModal(tipo, id = null, data = '', hora = '', motivo = '') {
            const modal = document.getElementById('modalBatida');
            const titulo = document.getElementById('modalTitulo');
            const form = document.getElementById('formBatida');
            const btnSalvar = document.getElementById('btnSalvar');

            // Limpa campos
            document.getElementById('batidaId').value = id || '';
            document.getElementById('dataBatida').value = data || '';
            document.getElementById('horaBatida').value = hora || '';
            document.getElementById('motivo').value = motivo || '';

            // Define modo
            if (tipo === 'novo') {
                titulo.textContent = 'Nova Batida';
                form.classList.remove('hidden');
                btnSalvar.textContent = 'Incluir';
                btnSalvar.className = "px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition";
            } else if (tipo === 'editar') {
                titulo.textContent = 'Editar Batida';
                form.classList.remove('hidden');
                btnSalvar.textContent = 'Salvar Alterações';
                btnSalvar.className = "px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition";
            } else if (tipo === 'excluir') {
                titulo.textContent = 'Excluir Batida';
                form.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
        }

        // Chamada dos botões externos

        function f_incluir(dia) {
            abrirModal('novo');
            let data = document.getElementById('dataBatida');
            let tipo = document.getElementById('tipoSolicitacao');
            data.value = dia;
            tipo.value = 'INC';
            data.focus();
        }

        function abrirModalEditar(id) {
            let tipo = document.getElementById('tipoSolicitacao');
            tipo.value = 'ALT';
            $.ajax({
                url: 'edita_cartao_aj2.php',
                type: 'POST',
                data: {
                    id: id
                },
                dataType: 'json', // jQuery já converte automaticamente
                success: function(resposta) {
                    console.log('Resposta recebida:', resposta);

                    if (resposta.status) {
                        abrirModal(
                            'editar',
                            id,
                            resposta.data.data,
                            resposta.data.hora,
                            ''
                        );
                    } else {
                        alert('Erro: ' + (resposta.mensagem || 'Não foi possível carregar os dados.'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                    alert('Erro na requisição: ' + error);
                }
            });
        }

        function abrirModalExcluir(id) {
            let tipo = document.getElementById('tipoSolicitacao');
            let data = document.getElementById('dataBatida');
            let hora = document.getElementById('horaBatida');
            //
            data.readOnly = true;
            hora.readOnly = true;
            tipo.value = 'DEL';
            $.ajax({
                url: 'edita_cartao_aj2.php',
                type: 'POST',
                data: {
                    id: id
                },
                dataType: 'json', // jQuery já converte automaticamente
                success: function(resposta) {
                    console.log('Resposta recebida:', resposta);

                    if (resposta.status) {
                        abrirModal(
                            'excluir',
                            id,
                            resposta.data.data,
                            resposta.data.hora,
                            ''
                        );
                    } else {
                        alert('Erro: ' + (resposta.mensagem || 'Não foi possível carregar os dados.'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                    alert('Erro na requisição: ' + error);
                }
            });
        }

        function salvarBatida(event) {
            event.preventDefault();
            const id = document.getElementById('batidaId').value;
            const data = document.getElementById('dataBatida').value;
            const hora = document.getElementById('horaBatida').value;
            const motivo = document.getElementById('motivo').value;
            const tipoSolicitacao = document.getElementById('tipoSolicitacao').value;
            //
            $.post('edita_cartao_aj1.php', {
                    id: id,
                    data: data,
                    hora: hora,
                    motivo: motivo,
                    tipo: tipoSolicitacao
                },
                function(data) {
                    if (data) {
                        let resposta = JSON.parse(data);
                        if (resposta.status) {
                            alert(resposta.msg);
                            location.reload();
                        }
                    }
                })
            console.log('Salvar batida:', {
                id,
                data,
                hora,
                motivo
            });
            f_fechar_modal('modalBatida');
        }

        function excluir_solicitacao(id) {
            if (confirm("Tem certeza que deseja excluir esta solicitação?")) {
                $.post('edita_cartao_aj3.php', {
                    id: id
                }, function(resposta) {
                    console.log(resposta);

                    // A resposta já é um objeto JS
                    if (resposta.status === true) {
                        alert(resposta.msg);
                        location.reload();
                    } else {
                        alert('Erro ao excluir: ' + (resposta.mensagem || 'tente novamente'));
                    }
                }, 'json'); // <-- importante: informa que o retorno é JSON
            }
        }

        // ---------- Modal Abono ----------

        function abrirModalAbono(dia) {
            const modal = document.getElementById('modalAbono');
            document.getElementById('dataFalta').value = dia;
            modal.classList.remove('hidden');
        }

        function salvarAbono(event) {
            event.preventDefault();

            const idColab = $('#idColabAbono').val();
            const data = $('#dataFalta').val();
            const motivo = $('#motivoAbono').val();
            const arquivo = $('#arquivoAbono')[0].files[0];

            // Usando FormData porque pode haver arquivo
            let formData = new FormData();
            formData.append('idColab', idColab);
            formData.append('data', data);
            formData.append('motivo', motivo);
            if (arquivo) formData.append('arquivo', arquivo);

            $.ajax({
                url: 'edita_cartao_aj4.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(resposta) {
                    if (resposta.status === true) {
                        alert(resposta.msg);
                        f_fechar_modal("modalAbono");
                        location.reload();
                    } else {
                        alert('Erro: ' + (resposta.mensagem || 'tente novamente'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro:', error);
                    alert('Falha ao enviar solicitação.');
                }
            });
        }

        function f_fechar_modal(janela) {
            document.getElementById(janela).classList.add('hidden');
        }

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


    </script>

</body>
</html>
<?PHP 
