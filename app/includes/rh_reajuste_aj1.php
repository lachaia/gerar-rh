<?PHP
//
//- rh_reajuste_aj1.php - Devolve Vetor JSON com nomes de colaboradores
// (C)haia, 06/10/2025
//

include_once "conexao_gerar.php";
global $conn;

if (isset($_GET['tipo'])) $tipo = $_GET['tipo'];
else $tipo = 'nenhum';
if (isset($_GET['idOrgao'])) $idOrgao = $_GET['idOrgao'];
else $idOrgao = 0;

//
//- CARREGA TODOS OS COLABORADORES ATIVOS
if ($tipo == 'todos') {
    $sql = "SELECT C.idColab, P.nome 
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
        WHERE C.data_rescisao IS NULL
        ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($registros);
    //
//
//- CARREGA TODOS OS COLABORADORES do ORGÁO ESPECÍFICO
} elseif ($tipo == 'orgao' && ! empty($idOrgao)) {
    $sql = "SELECT C.idColab, P.nome
        FROM RH.rh_colaboradores C 
        INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
        WHERE C.idOrgao = $idOrgao";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($registros);        
    //
//
//- CARREGA TODOS OS COLABORADORES DO ORGÃO E SUBORDINADOS    
} elseif( $tipo == 'suborgao' && ! empty($idOrgao) ) {
    //
    $listaColabs = getSubordinados($idOrgao, $conn);
    $sql = "SELECT C.idColab, P.nome
                FROM RH.rh_colaboradores C 
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                WHERE C.idColab IN (". implode(',', $listaColabs) .")
                ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($registros);        
}
exit();

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

    // 4. O próximo nível precisa ser > 0 (subordinados)
    $proximoNivel = $nivelGestor + 1;
    $whereSubordinados = implode(" AND ", $conds);
    if ($proximoNivel <= 7) {
        $whereSubordinados .= " AND O.nivel_$proximoNivel > 0";
    }

    // 5. Montar SQL final — inclui o próprio órgão também
    $sql = "
        SELECT DISTINCT C.idColab
        FROM rh_colaboradores C
        INNER JOIN rh_organograma O ON O.idOrgao = C.idOrgao
        WHERE ($whereSubordinados)
           OR C.idOrgao = :idOrgaoAtual
    ";

    $params[":idOrgaoAtual"] = $idOrgao;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 6. Extrair apenas os IDs em um vetor simples
    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row["idColab"];
    }

    return $ids;
}
