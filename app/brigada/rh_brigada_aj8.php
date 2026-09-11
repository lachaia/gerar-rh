<?php
// rh_brigada_aj8.php | Dados para a Grid de Atendimentos da Brigada 
// by (C)haia, 23/06/2025
//

$idModulo = 10; // Brigada de Emergência

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: ../logout.php");
}

f_log("CON", "Consulta grade dos Atendimentos da Brigada", "rh_atendimentos", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT 
                        S.identificador AS dsSubSede,
                        A.*,
                        O.descricao AS dsOcorrencia,
                        (
                            SELECT GROUP_CONCAT(P2.nome ORDER BY P2.nome SEPARATOR ', ')
                            FROM rh_brigada_atend_membros M2
                            INNER JOIN rh_brigadistas B2 ON B2.id = M2.idBrigadista
                            INNER JOIN rh_pessoas P2 ON P2.idPessoa = B2.idPessoa
                            WHERE M2.idAtendimento = A.id
                        ) AS membros
                    FROM rh_atendimentos A
                    INNER JOIN rh_subsedes S ON S.subsede_id = A.idSubSede
                    INNER JOIN rh_brigada_tipo_ocorrencia O ON O.id = A.tipo_ocorrencia";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    //
    $cor = !empty($data_final) ? "text-danger" : "text-dark";
    //
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm'    onClick='f_ver_atende($id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
    $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar_atende($id)'><i class='fa-solid fa-pen'></i></a>";
    $acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir_atende($id)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    $membros_primeiros = extrairPrimeirosNomes($membros);
    //
    $dado[] = "<span class='$cor'>$dsSubSede</span>";
    $dado[] = "<span class='$cor'>$data_ocorrencia</span>";
    $dado[] = "<span class='$cor'>$membros_primeiros</span>";
    $dado[] = "<span class='$cor'>$nome_paciente</span>";
    $dado[] = "<span class='$cor'>$dsOcorrencia</span>";
    $dado[] = $acoes;
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript
//

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
$conteudo = json_encode($output);
echo $conteudo;
exit;

function extrairPrimeirosNomes(string $listaNomes): string
{
    // transforma em array
    $nomes = array_map('trim', explode(',', $listaNomes));

    // se tiver apenas um nome, retorna o nome completo
    if (count($nomes) == 1) {
        return $nomes[0];
    }

    // para múltiplos nomes, pegar somente o primeiro nome de cada
    $primeiros = [];

    foreach ($nomes as $nome) {
        // quebra em partes
        $partes = preg_split('/\s+/', trim($nome));
        $primeiros[] = $partes[0]; // primeiro nome
    }

    // devolve unidos por vírgula
    return implode(', ', $primeiros);
}
