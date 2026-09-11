<?PHP 

include_once "../includes/conexao_gerar.php";

//- Testando RONNY.

$dados = getSubordinados(8, $conn); // Colaborador 8 = Ronny

foreach ($dados as $colab) {
    extract($colab);
    echo "<p>$idColab | $nome | $dsOrgao| $nivel_1 | $nivel_2 | $nivel_3 | $nivel_4 | $nivel_5 | $nivel_6 | $nivel_7</p>";
}


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
        SELECT P.nome, C.idColab, O.*, O.descricao as dsOrgao
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
        INNER JOIN rh_organograma O ON O.idOrgao = C.idOrgao
        WHERE C.data_rescisao IS NULL
          AND $where
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
