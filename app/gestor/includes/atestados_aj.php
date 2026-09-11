<?php
// equipe_aj.php | Grid de Consulta Colaboradores
// by (C)haia, 18/09/2025
//

$idModulo = 16; // Portal do Gestor

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
} else{
    header("location: logout.php");
}

//- Obter dados a serem apresentados

$idOrgao = $_SESSION['idOrgao'] ?? 0;

$listaColabs = getSubordinados($idOrgao, $conn);

$pesquisa = "SELECT A.*,  P.nome, T.descricao
                FROM RH.rh_afastamentos A
                INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_afastamento_tipos T on T.id = A.idTipo
                WHERE C.idColab IN (". implode(',', $listaColabs) .")
                ORDER BY P.nome";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($idColab)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    //
    $dsStatus = '';
    if( $status == 'Gestor' ){
        $dsStatus = "<span class='badge bg-warning text-dark px-2 py-1'><i class='fa-solid fa-hourglass-end'></i> Gestor</span>";
    } elseif( $status == 'RH' ){
        $dsStatus = "<span class='badge bg-warning text-dark px-2 py-1'><i class='fa-solid fa-hourglass-end'></i> RH</span>";
    } elseif( $status == 'Rejeitado' ){
        $dsStatus = "<span class='badge bg-danger text-white px-2 py-1'>Rejeitado</span>";
    } elseif( $status == 'Aprovado'){
        $dsStatus = "<span class='badge bg-success text-white px-2 py-1'>Aprovado</span>";
    } else{
        $dsStatus = "<span class='badge bg-secondary text-white px-2 py-1'>Desconhecido</span>";
    }
    //
    $descricao = strip_tags($descricao);
    //
    $dado[] = $id;
    $dado[] = $nome;
    $dado[] = $descricao;
    $dado[] = $data_inicio;
    $dado[] = $data_retorno;
    $dado[] = $dias_afastado;
    $dado[] = $dsStatus;
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

//-----------------------------------------------------------------------------

function getSubordinados($idOrgao, $pdo) {
    // 1. Buscar a linha do organograma do gestor
    $sql = "SELECT * FROM rh_organograma WHERE idOrgao = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idOrgao]);
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gestor) {
        return [];
    }

    // 2. Descobrir em qual nível o gestor está (último nível > 0)
    $nivelGestor = 0;
    for ($i = 1; $i <= 7; $i++) {
        if (!empty($gestor["nivel_$i"]) && $gestor["nivel_$i"] > 0) {
            $nivelGestor = $i;
        }
    }

    // 3. Montar condição dinâmica para os níveis anteriores
    $conds = [];
    $params = [];

    for ($i = 1; $i <= $nivelGestor; $i++) {
        $conds[] = "O.nivel_$i = :n$i";
        $params[":n$i"] = $gestor["nivel_$i"];
    }

    // 4. O próximo nível precisa ser > 0
    $proximoNivel = $nivelGestor + 1;
    if ($proximoNivel <= 7) {
        $conds[] = "O.nivel_$proximoNivel > 0";
    }

    // 5. Montar SQL final
    $where = implode(" AND ", $conds);

    $sql = "
        SELECT C.idColab
        FROM rh_colaboradores C
        INNER JOIN rh_organograma O ON O.idOrgao = C.idOrgao
        WHERE $where
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 6. Extrair apenas os IDs em um vetor simples
    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row["idColab"];
    }

    return $ids;
}