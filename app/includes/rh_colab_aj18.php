<?php
//
//- rh_colab_aj18.php | Reconstroi Tabela de Dependentes
//- (C)haia, 15/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_GET, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

require_once("conexao_gerar.php");

if ( empty($idColab) ) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

$sql = "SELECT D.idDependente, P.nome, TIMESTAMPDIFF(YEAR, D.dataNascimento, CURDATE()) AS idade, X.dsParentesco , 
                        D.ir, D.usaPlanoSaude, D.usaPlanoOdonto, D.usaCreche
                FROM rh_dependentes D
                INNER JOIN rh_pessoas P ON P.idPessoa = D.idPessoaDep
                INNER JOIN rh_parentescos X on X.idParentesco = D.idParentesco
        WHERE D.idColab = :idColab";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
$stmt->execute();

$dependentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Começa a montar o HTML do <tbody>
$html = "<thead>
            <tr>
                <th>Nome</th>
                <th>Parentesco</th>
                <th>Idade</th>
                <th>IR</th>
                <th>P.Saúde</th>
                <th>P.Odonto</th>
                <th>Creche</th>
                <th><i class='fa-solid fa-circle-down'></i></th>
            </tr>
        </thead>";

foreach ($dependentes as $dep) {
    $acoes = "<a href='#' onclick='excluir_dependente(".$dep['idDependente'].")'><i class='fa-regular fa-trash-can text-danger'></i></a>";
    $html .= "<tr>";
    $html .= "<td>" . htmlspecialchars($dep['nome']) . "</td>";
    $html .= "<td>" . htmlspecialchars($dep['dsParentesco']) . "</td>";
    $html .= "<td>" . htmlspecialchars($dep['idade']) . "</td>";
    $html .= "<td>" . ($dep['ir'] ? 'Sim' : 'Não') . "</td>";
    $html .= "<td>" . ($dep['usaPlanoSaude'] ? 'Sim' : 'Não') . "</td>";
    $html .= "<td>" . ($dep['usaPlanoOdonto'] ? 'Sim' : 'Não') . "</td>";
    $html .= "<td>" . ($dep['usaCreche'] ? 'Sim' : 'Não') . "</td>";
    $html .= "<td>$acoes</td>";
    $html .= "</tr>";
}

echo $html;
