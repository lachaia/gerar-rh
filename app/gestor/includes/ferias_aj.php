<?php
// equipe_aj.php | Grid de Consulta Colaboradores
// by (C)haia, 18/09/2025
//

$idModulo = 16; // Portal do Gestor

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../../includes/conexao_gerar.php";
    //include_once "../includes/debug.php";
} else {
    header("location: ../logout.php");
}

//- Obter dados a serem apresentados

$idOrgao = $_SESSION['idOrgao'] ?? 0;

$listaColabs = getSubordinados($idOrgao, $conn);

$pesquisa = "SELECT C.idStatus,
                    P.nome, 
                    F.*, 
                    FLOOR(LEAST(12, TIMESTAMPDIFF(MONTH, F.inicio_aquisitivo, CURDATE())) * 2.5) AS dias_adquiridos,
                    DATEDIFF(F.fim_concessivo, CURDATE()) AS dias_para_vencer
                FROM rh_ferias F
                INNER JOIN rh_colaboradores C ON C.idColab = F.idColab
                INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                WHERE C.idColab IN (". implode(',', $listaColabs) .")
                ORDER BY P.nome, F.inicio_aquisitivo";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    //
    $dias_adquiridos = intval($dias_adquiridos);
    //
    $aquisitivo = "$inicio_aquisitivo<br>$fim_aquisitivo";
    $concessivo = "$inicio_concessivo<br> $fim_concessivo";

    //----------------------------------
    // PREPARA BALÃO DE INFORMAÇÕES
    //----------------------------------

    $sSaldo = '-';
    $status = "";

    // soma total usufruído (considerando os 3 períodos)
    $total_usufruido  = !empty($data_parte1) ? (int) $dias_parte1 : 0;
    $total_usufruido += !empty($data_parte2) ? (int) $dias_parte2 : 0;
    $total_usufruido += !empty($data_parte3) ? (int) $dias_parte3 : 0;

    //
    $result = calcula_status(
                $id,
                $dias_adquiridos,
                $total_usufruido,
                $fim_concessivo,
                $dias_para_vencer,
                $agenda_parte1,
                $agenda_parte2,
                $agenda_parte3,
                $aprova_1_em,
                $aprova_2_em,
                $aprova_3_em,
                $aprova_rh_1_em,
                $aprova_rh_2_em,
                $aprova_rh_3_em,
                $idStatus
            );

    $status = $result['status'];
    $sSaldo = $result['saldo'];
    $y = $result['dias_vencer'];
    //
    $agenda_parte1 = empty($agenda_parte1) ? " - " : $agenda_parte1;
    $agenda_parte2 = empty($agenda_parte2) ? " - " : $agenda_parte2;
    $agenda_parte3 = empty($agenda_parte3) ? " - " : $agenda_parte3;
    //
    $dado[] = $id;              //- 0    
    $dado[] = $nome;            //- 1
    $dado[] = $aquisitivo;      //- 2
    $dado[] = $concessivo;      //- 3
    $dado[] = $dias_adquiridos; //- 4
    $dado[] = $status;          //- 5
    $dado[] = empty($agenda_parte1) ? $y : $agenda_parte1;     //- 6
    $dado[] = empty($dias_parte1)   ? "-" : $dias_parte1;       //- 7
    $dado[] = empty($agenda_parte2) ? "-" : $agenda_parte2;     //- 8
    $dado[] = empty($dias_parte2)   ? "-" : $dias_parte2;       //- 9
    $dado[] = empty($agenda_parte3) ? "-" : $agenda_parte3;     //- 10
    $dado[] = empty($dias_parte3)   ? "-" : $dias_parte3;       //- 11
    $dado[] = $sSaldo == 0 ? "-" : $sSaldo;  //- 12
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

/**
 * Calcula o status das férias de um colaborador considerando os 3 períodos
 */
function calcula_status(
    $id,
    $dias_adquiridos,
    $total_usufruido,
    $fim_concessivo,
    $dias_para_vencer,
    $agenda_parte1,
    $agenda_parte2,
    $agenda_parte3,
    $aprova_1_em,
    $aprova_2_em,
    $aprova_3_em,
    $aprova_rh_1_em,
    $aprova_rh_2_em,
    $aprova_rh_3_em,
    $idStatus
) {
    $hoje = date('Y-m-d');

    // flags de agendamento
    $hasAgenda1 = !empty($agenda_parte1);
    $hasAgenda2 = !empty($agenda_parte2);
    $hasAgenda3 = !empty($agenda_parte3);
    $anyAgendado = $hasAgenda1 || $hasAgenda2 || $hasAgenda3;

    // flags de pendência gestor (só se tiver agendamento)
    $pendenteGestor1 = $hasAgenda1 && empty($aprova_1_em);
    $pendenteGestor2 = $hasAgenda2 && empty($aprova_2_em);
    $pendenteGestor3 = $hasAgenda3 && empty($aprova_3_em);
    $anyPendenteGestor = $pendenteGestor1 || $pendenteGestor2 || $pendenteGestor3;

    // flags de pendência RH (só se tiver agendamento)
    $pendenteRH1 = $hasAgenda1 && !empty($aprova_1_em) && empty($aprova_rh_1_em);
    $pendenteRH2 = $hasAgenda2 && !empty($aprova_2_em) && empty($aprova_rh_2_em);
    $pendenteRH3 = $hasAgenda3 && !empty($aprova_3_em) && empty($aprova_rh_3_em);
    $anyPendenteRH = $pendenteRH1 || $pendenteRH2 || $pendenteRH3;

    // saldo
    $sSaldo = ($dias_adquiridos - $total_usufruido);
    $sSaldoBadge = $sSaldo <= 0 ? "<kbd>-</kbd>" : $sSaldo;

    // lógica principal corrigida
    if ($dias_adquiridos >= 30) {
        if ($total_usufruido >= 30) {
            $status = "<span class='badge bg-secondary text-white status w-100'>FRUÍDAS</span>";
        }
        // se não tem nenhum agendamento **e tem saldo suficiente**
        elseif (!$anyAgendado && $sSaldo > 0) {
            $status = "<span class='badge text-dark status w-100' style='background-color: orange'>AGENDAR</span>";
        }
        // prioridade: alguma parcela já aprovada pelo gestor e pendente no RH
        elseif ($anyPendenteRH) {
            $status = "<span class='badge text-white status w-100' style='background-color: SteelBlue'>
                      <i class='fa-solid fa-hourglass-start'></i> RH</span>";
        }
        // alguma parcela aguardando aprovação do gestor
        elseif ($anyPendenteGestor) {
            //$status = "<span class='badge text-white status' style='background-color: darkblue'>
            //          <i class='fa-solid fa-hourglass-start'></i> Gestor</span>";
            $status = "<button type='button' class='btn btn-sm btn-outline-primary w-100' onclick='fer_aprovar_grid($id)'>Aprovar</button>";
        }
        elseif ($anyAgendado && !($anyPendenteGestor || $anyPendenteRH) // ou seja, todos aprovados
        ) {
            $status = "<span class='badge bg-success text-white status w-100'>AGENDADO</span>";
            $xStatus = "Agendado";
        }        
        else {
            $status = "<span class='badge text-dark status w-100' style='background-color: LightGray'>Pendente</span>";
        }
    } else {
        $status = "<span class='badge text-dark status w-100' style='background-color: DarkKhaki'>Aguardar</span>";
    }

    if( $idStatus == 3) $status = "<span class='badge text-dark bg-warning status w-100'><i class='fa-solid fa-plane'></i> em Férias</span>";

    // indicador de dias para vencer
    if (!$anyAgendado && $dias_para_vencer <= 0) {
        $diasVencerBadge = "<span class='badge bg-danger text-white status'>VENCIDO</span>";
    } else {
        if ($dias_para_vencer <= 30) {
            $diasVencerBadge = "<span class='badge bg-danger text-white status'>$dias_para_vencer dias</span>";
        } elseif ($dias_para_vencer <= 90) {
            $diasVencerBadge = "<span class='badge text-dark status' style='background-color: orange'>$dias_para_vencer dias</span>";
        } elseif ($dias_para_vencer <= 180) {
            $diasVencerBadge = "<span class='badge text-white status' style='background-color: SteelBlue'>$dias_para_vencer dias</span>";
        } else {
            $diasVencerBadge = "<span class='badge bg-success text-white status'>$dias_para_vencer dias</span>";
        }
    }

    return [
        'status' => $status,
        'saldo' => $sSaldoBadge,
        'dias_vencer' => $diasVencerBadge
    ];
}

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