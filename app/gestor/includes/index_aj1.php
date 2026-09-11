<?PHP 
//
//- index_aj1.php | Módulo GESTOR - Calculo do Dashboard
// (C)haia, 18/09/2025
//

session_start();

$idOrgao = $_SESSION['idOrgao'] ?? 0;

include_once "../../includes/conexao_gerar.php";

$dados = [];

$listaAtivos = getSubordinados($idOrgao, $conn);

//
//- QTD DE COLABORADORES ATIVOS
//
    $sql = "SELECT count(idColab) as qtd  
    FROM RH.rh_colaboradores C
    INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
    WHERE C.idColab in (" . implode(",", $listaAtivos) . ") AND C.data_rescisao is null";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados["qtdAtivos"] = $row["qtd"];

//
//- QTD DE COLABORADORES DESLIGADOS
//
    $sql = "SELECT count(idColab) as qtd  
    FROM RH.rh_colaboradores C
    INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
    WHERE C.idColab in (" . implode(",", $listaAtivos) . ") AND C.data_rescisao is not null";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados["qtdInativos"] = $row["qtd"];

    //
//- QTD DE COLABORADORES AFASTADOS (ATESTADO)
//
    $sql = "SELECT count(idColab) as qtd  
    FROM RH.rh_colaboradores C
    INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
    WHERE C.idColab in (" . implode(",", $listaAtivos) . ") AND C.data_rescisao is null and
    C.idColab in 
        (   SELECT idColab 
            FROM RH.rh_afastamentos 
            WHERE status = 'Aprovado' and now() >= data_inicio and now() <= data_retorno
        )";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados["qtdAfastados"] = $row["qtd"];

//
//- QTD DE COLABORADORES EM GOZO DE FÉRIAS
//
    $sql = "SELECT count(idColab) as qtd  
        FROM RH.rh_colaboradores C
        INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
        WHERE C.idColab in (" . implode(",", $listaAtivos) . ") AND C.data_rescisao is null AND
        C.idColab in 
        (SELECT idColab 
            FROM RH.rh_ferias
            WHERE (NOW() >= agenda_parte1 AND NOW() <= DATE_ADD(agenda_parte1, INTERVAL dias_parte1 DAY)) OR 
            (NOW() >= agenda_parte2 AND NOW() <= DATE_ADD(agenda_parte2, INTERVAL dias_parte2 DAY)) OR 
            (NOW() >= agenda_parte3 AND NOW() <= DATE_ADD(agenda_parte3, INTERVAL dias_parte3 DAY))
        )";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados["qtdFerias"] = $row["qtd"];

    die( json_encode($dados) );
//
//----------------------------------------------------------------------------------------------------------------
//
function getSubordinados($idGestor, $pdo) {
    // 1. Buscar a linha do organograma do gestor
    $sql = "SELECT * FROM rh_organograma WHERE idOrgao = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idGestor]);
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
