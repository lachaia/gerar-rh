<?php
//
//- ferias_aj1.php | Cria Grid para Aprovação de Férias
//- Módulo: Gestor
//- (C)haia, 2026-05-27
//

include_once "../../includes/conexao_gerar.php";
$idFerias = $_POST['idFerias'] ?? 0;

// Buscar dados no banco
$sql = "SELECT P.nome, F.*
            FROM rh_ferias F 
            INNER JOIN rh_colaboradores C on C.idColab = F.idColab
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            WHERE F.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $idFerias);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract($dados);

$periodo_concessivo = "de " . date('d/m/Y', strtotime($inicio_concessivo)) . " a " . date('d/m/Y', strtotime($fim_concessivo));

// Aqui você cola a lógica de montagem da tabela das parcelas
// (aquela que você já fez no formulário original)
$html = '
        <div class="card p-4" style="background-color: #d8dbddff; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.6);">
            <input type="hidden" id="idColabSupervisor" value="'.$idColabSupervisor.'">
            <input type="hidden" id="idFerias" value="'.$id.'">
            <input type="hidden" id="parcelas_desmarcadas" name="parcelas_desmarcadas">
            <h3 class="mb-4 text-center text-dark">Solicitação de Férias</h3>
            <div id="dadosFerias" class="text-center">
                <p><strong>Colaborador:</strong> '.$nome.'</p>
                <p><strong>Período Concessivo: </strong> '.$periodo_concessivo .'></p>
                <hr>';

                $parcelas = [];
                $html .= "<h5>Agendamento</h5>";
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

                $html .= "</tbody></table>
                  </div>
        </div>
        <div class='text-center m-2 h4' id='divMensagem'></div>";
echo $html;
exit;