<?php
// equipe_aj.php | Grid de Consulta Colaboradores
// by (C)haia, 18/09/2025
//

$idModulo = 16; // Portal do Gestor

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../../includes/conexao_gerar.php";
} else{
    header("location: ../logout.php");
}

//- Obter dados a serem apresentados

$idOrgao = $_SESSION['idOrgao'] ?? 0;

$listaColabs = getSubordinados($idOrgao, $conn);

$pesquisa = "SELECT C.*, P.nome, P.email, P.telefone, F.nome as dsFuncao, O.descricao as dsOrgao, TC.descricao as dsTipoContrato,
                (SELECT 1
                    FROM rh_afastamentos
                    WHERE NOW() BETWEEN data_inicio AND data_retorno AND idColab = C.idColab and status ='Aprovado'
                ) as afastado,
                (
                SELECT 1
                    FROM rh_ferias
                    WHERE (
                            (NOW() BETWEEN data_parte1 AND DATE_ADD(data_parte1, INTERVAL dias_parte1 - 1 DAY)
                            AND aprova_rh_1_em IS NOT NULL)
                        OR (NOW() BETWEEN data_parte2 AND DATE_ADD(data_parte2, INTERVAL dias_parte2 - 1 DAY)
                            AND aprova_rh_2_em IS NOT NULL)
                        OR (NOW() BETWEEN data_parte3 AND DATE_ADD(data_parte3, INTERVAL dias_parte3 - 1 DAY)
                            AND aprova_rh_3_em IS NOT NULL)
                        ) AND idColab = C.idColab
                ) as ferias
                FROM rh_colaboradores C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                INNER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
                INNER JOIN rh_contratos_tipo TC ON TC.idContratoTipo = C.idContratoTipo
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
    if(empty($data_rescisao) || $data_rescisao == '0000-00-00'){
        $status = "<span class='badge bg-success w-100'>Ativo</span>";
        if( $afastado == 1 ){
            $status = "<span class='badge bg-warning w-100 text-dark'>Afastado</span>";
        }
        if( $ferias == 1 ){
            $status = "<span class='badge bg-primary w-100'>Férias</span>";
        }
    } else{
        $status = "<span class='badge bg-danger w-100'>Desligado</span>";
    }
    //
    $dado[] = $nome;                                      
    $dado[] = $email;
    $dado[] = $dsOrgao;
    $dado[] = $dsFuncao;
    $dado[] = date('d/m/Y', strtotime($data_admissao));
    $dado[] = $dsTipoContrato;
    $dado[] = $status;
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