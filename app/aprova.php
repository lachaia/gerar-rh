<?PHP
//
// aprova.php | Aprovação/Reprovação de Férias
// (C)haia, 2025-05-27

session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acesso Inválido</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .mensagem {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.7);
        }
    </style>
</head>
<body>
    <div class="mensagem">
        <h2>ID INVÁLIDO!</h2>
        <p>Você não pode acessar esta página diretamente.</p>
    </div>
</body>
</html>
HTML;
    exit;
}

//
//- RECUPERA DADOS DO AGENDAMENTO
//

include "includes/conexao_gerar.php";

$sql = "SELECT P.nome, F.*
            FROM rh_ferias F 
            INNER JOIN rh_colaboradores C on C.idColab = F.idColab
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            WHERE F.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract($dados);

$periodo_concessivo = "de " . date('d/m/Y', strtotime($inicio_concessivo)) . " a " . date('d/m/Y', strtotime($fim_concessivo));


?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovação de Férias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery deve vir antes de tudo -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #f8f9fa;
            padding-top: 40px;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            background-color: rgba(164, 166, 157, 0.84);
            border: 1px solid #333;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
            width: 600px;
            margin: auto;
        }

        .btn-approve {
            background-color: #198754;
            border: none;
            color: white;
        }

        .btn-reject {
            background-color: #dc3545;
            border: none;
            color: white;
        }

        .modal-content {
            background-color: #2a2a2a;
            color: #f8f9fa;
        }

        .form-control {
            background-color: #2c2c2c;
            color: rgb(241, 230, 12);
            border: 1px solid #666;
        }

        h3,
        .modal-title,
        strong,
        p {
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container mt-5" id='divPrincipal'>
        <div class="card p-4">
            <input type="hidden" id="idColabSupervisor" value="<?= $idColabSupervisor ?>">
            <input type="hidden" id="idFerias" value="<?= $id ?>">
            <input type="hidden" id="parcelas_desmarcadas" name="parcelas_desmarcadas">
            <h3 class="mb-4 text-center text-dark">Solicitação de Férias</h3>
            <div id="dadosFerias" class="text-center">
                <!-- Dados do agendamento virão aqui via PHP ou JS -->
                <p><strong>Colaborador:</strong> <?= $nome ?></p>
                <p><strong>Período Concessivo: </strong> <?= $periodo_concessivo ?></p>
                <hr>

                <?php
                $parcelas = [];
                $html = "<h5>Agendamento</h5>";
                $html .= "<table class='table table-sm table-bordered table-hover mx-auto text-center' style='max-width:600px; text-align:left'>
                <thead class='table-light'>
                    <tr>
                        <th>Parcela</th>
                        <th>Período</th>
                        <th>Dias</th>
                        <th>Aprovar</th>
                    </tr>
                </thead>
                <tbody>";

                // Parcela 1
                if (!empty($agenda_parte1) && !empty($dias_parte1) && empty($data_parte1) && empty($aprova_1_por)) {
                    $inicio = DateTime::createFromFormat('Y-m-d', $agenda_parte1);
                    $fim = clone $inicio;
                    $fim->modify('+' . ($dias_parte1 - 1) . ' days');
                    $html .= "<tr>
                    <td>Parcela 1</td>
                    <td>{$inicio->format('d/m/Y')} a {$fim->format('d/m/Y')}</td>
                    <td>{$dias_parte1}</td>
                    <td><input type='checkbox' name='parcelas[]' value='1' checked onchange='toggleParcela(this)'></td>
                  </tr>";
                    $parcelas[] = 1;
                }

                // Parcela 2
                if (!empty($agenda_parte2) && !empty($dias_parte2) && empty($data_parte2) && empty($aprova_2_por)) {
                    $inicio = DateTime::createFromFormat('Y-m-d', $agenda_parte2);
                    $fim = clone $inicio;
                    $fim->modify('+' . ($dias_parte2 - 1) . ' days');
                    $html .= "<tr>
                    <td>Parcela 2</td>
                    <td>{$inicio->format('d/m/Y')} a {$fim->format('d/m/Y')}</td>
                    <td>{$dias_parte2}</td>
                    <td><input type='checkbox' name='parcelas[]' value='2' checked onchange='toggleParcela(this)'></td>
                  </tr>";
                    $parcelas[] = 2;
                }

                // Parcela 3
                if (!empty($agenda_parte3) && !empty($dias_parte3) && empty($data_parte3) && empty($aprova_3_por)) {
                    $inicio = DateTime::createFromFormat('Y-m-d', $agenda_parte3);
                    $fim = clone $inicio;
                    $fim->modify('+' . ($dias_parte3 - 1) . ' days');
                    $html .= "<tr>
                    <td>Parcela 3</td>
                    <td>{$inicio->format('d/m/Y')} a {$fim->format('d/m/Y')}</td>
                    <td>{$dias_parte3}</td>
                    <td><input type='checkbox' name='parcelas[]' value='3' checked onchange='toggleParcela(this)'></td>
                  </tr>";
                    $parcelas[] = 3;
                }

                $vetor = implode(',', $parcelas);

                $html .= "</tbody></table>";
                echo $html;
                ?>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-approve w-100" onclick="aprovar()">Aprovar</button>
                <!--<button class="btn btn-reject" onclick="abrirReprovacao()">Reprovar</button> -->
            </div>
        </div>
        <div class="text-center m-2 h4" id='divMensagem'></div>
    </div>

    <div id="divObrigadoA" class="d-none text-center p-5" style="background-color: #1e1e1e; color: #f8f9fa; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.6); margin-top: 60px;">
        <h1 class="display-4 mb-4" style="font-weight: bold;">✔️ Obrigado!</h1>
        <p class="lead">O agendamento de férias foi aprovado com sucesso.</p>
        <p class="mt-3">Você pode fechar esta janela ou retornar ao sistema.</p>
    </div>

    <div id="divObrigadoR" class="d-none text-center p-5" style="background-color: #1e1e1e; color: #f8f9fa; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.6); margin-top: 60px;">
        <h1 class="display-4 mb-4" style="font-weight: bold;">✔️ Obrigado!</h1>
        <p class="lead">O agendamento de férias foi RECUSADO com sucesso.</p>
        <p class="mt-3">Você pode fechar esta janela ou retornar ao sistema.</p>
    </div>

    <!-- Modal de Aprovação -->
    <div class="modal fade" id="modalAprovar" tabindex="-1" aria-hidden="false">
        <input type="hidden" id='vetorParcelas' value="<?= $vetor ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAprovarLabel">Aprovar Férias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <label for="senhaAprovacao" class="form-label">Digite sua senha para aprovar:</label>
                    <input type="password" class="form-control mb-3" id="senhaAprovacao" placeholder="Senha" />
                    <input type="hidden" id="idColabSupervisorConfirm" />

                    <!-- Seção para motivo da não aprovação -->
                    <div id="motivoNaoAprovacaoSection" class="d-none mt-3">
                        <label for="motivoNaoAprovacao" class="form-label">Motivo da não aprovação das parcelas desmarcadas:</label>
                        <textarea id="motivoNaoAprovacao" class="form-control" rows="3" placeholder="Digite o motivo aqui..."></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex w-100 p-0">
                    <button type="button" class="btn btn-outline-secondary flex-fill m-1" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-success flex-fill m-1" onclick="confirmarAprovacao()">Confirmar</button>
                </div>

                <div id='msgAprova' class="mt-2"></div>
            </div>
        </div>
    </div>

    <script>
        let modalAprova = new bootstrap.Modal(document.getElementById('modalAprovar'));
        //let modalReprova = new bootstrap.Modal(document.getElementById('modalReprovar'));

        function aprovar() {
            let idColabSupervisor = document.getElementById('idColabSupervisor').value;
            let parcelasDesmarcadas = document.getElementById('parcelas_desmarcadas').value;
            document.getElementById('idColabSupervisorConfirm').value = idColabSupervisor;
            document.getElementById('senhaAprovacao').value = ""; // Limpa o campo de senha 
            //
            if (parcelasDesmarcadas.length > 0) {
                document.getElementById('motivoNaoAprovacaoSection').classList.remove('d-none');
            }
            //           
            modalAprova.show();
        }

        function confirmarAprovacao() {
            let mensagem = $("#msgAprova");
            //
            const idFerias = document.getElementById('idFerias').value;
            const senha = document.getElementById('senhaAprovacao').value;
            const idColabSupervisor = document.getElementById('idColabSupervisorConfirm').value;
            const vetorParcelas = document.getElementById('vetorParcelas').value;

            if (!senha) {
                alert("Por favor, digite a senha.");
                document.getElementById('senhaAprovacao').focus();
                return;
            }
            //
            let desmarcadas = document.getElementById('parcelas_desmarcadas').value;
            if (desmarcadas.length > 0 && !document.getElementById('motivoNaoAprovacao').value.trim()) {
                alert("Por favor, informe o motivo da não aprovação das parcelas desmarcadas.");
                document.getElementById('motivoNaoAprovacao').focus();
                return;
            }            
            //
            const parcelasTotais = document.getElementById('vetorParcelas').value
                .split(',')
                .map(p => parseInt(p));

            const qtdParcelas = parcelasTotais.length; // número total de parcelas possíveis

            //
            // Pegar os valores dos checkboxes marcados
            let parcelasMarcadas = [];
            document.querySelectorAll('input[name="parcelas[]"]:checked').forEach(function(checkbox) {
                parcelasMarcadas.push(checkbox.value);
            });

            let qtdSelecionadas = parcelasMarcadas.length; // ✅ quantidade de parcelas marcadas (já é array)

            // Verifica se há pelo menos uma parcela aprovada
            if (qtdSelecionadas === 0) {
                alert("Selecione ao menos uma parcela para aprovar.");
                return;
            }
            //
            // Pegar as parcelas desmarcadas do hidden
            const parcelasDesmarcadas = document.getElementById('parcelas_desmarcadas').value;

            const body = `idFerias=${encodeURIComponent(idFerias)}` +
                `&idGestor=${encodeURIComponent(idColabSupervisor)}` +
                `&senha=${encodeURIComponent(senha)}` +
                `&parcelas=${encodeURIComponent(parcelasMarcadas.join(','))}` +
                `&parcelas_desmarcadas=${encodeURIComponent(parcelasDesmarcadas)}` +
                `&motivoNaoAprovacao=${encodeURIComponent(document.getElementById('motivoNaoAprovacao').value)}`;

            // Aqui você envia para o backend
            fetch('aprova_aj.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body
                })
                .then(response => response.json())
                .then(retorno => {
                    if (retorno.status) {
                        mensagem.html(retorno.msg);
                        // Fechar modal
                        document.activeElement.blur(); // ou: document.body.focus();
                        modalAprova.hide();
                        // Mostrar tela de obrigado
                        $("#divPrincipal").addClass("d-none");
                        $("#divObrigadoA").removeClass("d-none");
                        //
                    } else {

                        mensagem.html(retorno.msg);
                        setTimeout(() => {
                            mensagem.html('');
                            document.activeElement.blur(); // ou: document.body.focus();
                            modalAprova.hide();
                        }, 3000);
                    }
                });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleParcela(checkbox) {
            //alert("toggleParcela: " + checkbox.value + " | " + checkbox.checked);
            let hidden = document.getElementById("parcelas_desmarcadas");
            let valores = hidden.value ? hidden.value.split(",") : [];

            if (!checkbox.checked) {
                // adiciona se não existir
                if (!valores.includes(checkbox.value)) {
                    valores.push(checkbox.value);
                }
            } else {
                // remove se estava desmarcado e voltou a marcar
                valores = valores.filter(v => v !== checkbox.value);
            }

            hidden.value = valores.join(",");
        }
    </script>
</body>

</html>