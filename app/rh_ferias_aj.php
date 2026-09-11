<?php
// rh_ferias.php | Consulta FÉRIAS
// by (C)haia, 29/04/2025
//

$idModulo = 12; // férias

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else {
    header("location: logout.php");
}

f_log("CON", "Consulta grade de FÉRIAS", "rh_ferias", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT 
                P.nome, 
                F.*, 
                FLOOR(LEAST(12, TIMESTAMPDIFF(MONTH, F.inicio_aquisitivo, CURDATE())) * 2.5) AS dias_adquiridos,
                DATEDIFF(F.fim_concessivo, CURDATE()) AS dias_para_vencer,
                C.idStatus
             FROM rh_ferias F
             INNER JOIN rh_colaboradores C ON C.idColab = F.idColab
             INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
             ORDER BY P.nome, F.inicio_aquisitivo";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$hoje = date('Y-m-d');

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    //
    $dias_adquiridos = intval($dias_adquiridos);
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($id)'><i class='fa-solid fa-pen'></i></a>";
    $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    $aquisitivo = "$inicio_aquisitivo a $fim_aquisitivo";
    $concessivo = "$inicio_concessivo a $fim_concessivo";

    //
    //----------------------------------
    // PREPARA BALÃO DE INFORMAÇÕES
    //
    $sSaldo = '-';
    $status = "";

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
                $aprova_rh_3_em
            );

    $status = $result['status'];
    $sSaldo = $result['saldo'];
    $y = $result['dias_vencer'];

    //
    $dado[] = $id;
    $dado[] = $nome;
    $dado[] = $aquisitivo;
    $dado[] = $concessivo;
    $dado[] = $dias_adquiridos;

    $dado[] = $status;

    $dado[] = empty($agenda_parte1) ? $y : $agenda_parte1;
    $dado[] = empty($dias_parte1)   ? "-" : $dias_parte1;
    $dado[] = empty($agenda_parte2) ? "-" : $agenda_parte2;
    $dado[] = empty($dias_parte2)   ? "-" : $dias_parte2;
    $dado[] = empty($agenda_parte3) ? "-" : $agenda_parte3;
    $dado[] = empty($dias_parte3)   ? "-" : $dias_parte3;

    $dado[] = empty($data_parte1)   ? "-" : $data_parte1;
    $dado[] = empty($data_parte2)   ? "-" : $data_parte2; 
    $dado[] = empty($data_parte3)   ? "-" : $data_parte3; 

    $dado[] = $sSaldo;
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

//
//- Funções auxiliares: CALCULA STATUS
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
            $aprova_rh_3_em
        ) {
    global $idStatus;
    $xStatus = "Indefinido";
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
            $xStatus = "Fruídas";
        }
        // se não tem nenhum agendamento **e tem saldo suficiente**
        elseif (!$anyAgendado && $sSaldo > 0) {
            $status = "<span class='badge text-dark status w-100' style='background-color: orange'>AGENDAR</span>";
            $xStatus = "Agendar";
        }
        // prioridade: alguma parcela já aprovada pelo gestor e pendente no RH
        elseif ($anyPendenteRH) {
            //$status = "<span class='badge text-white status' style='background-color: SteelBlue'>
            //          <i class='fa-solid fa-hourglass-start'></i> RH</span>";
            $status = "<button type='button' class='btn btn-sm btn-outline-primary w-100' onclick='fer_aprovar_grid($id)'>Aprovar</button>";
            $xStatus = "Pendente RH";
        }
        // alguma parcela aguardando aprovação do gestor
        elseif ($anyPendenteGestor) {
            $status = "<span class='badge text-white status w-100' style='background-color: darkblue'><i class='fa-solid fa-hourglass-start'></i> Gestor</span>";
            $xStatus = "Pendente Gestor";
        }
        // <<<--- AQUI entra a verificação de "Agendado"
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
        $xStatus = "Aguardar";
    }

    // indicador de dias para vencer
    if (!$anyAgendado && $dias_para_vencer <= 0) {
        $diasVencerBadge = "<span class='badge bg-danger text-white status w-100'>VENCIDO</span>";
    } else {
        if ($dias_para_vencer <= 30) {
            $diasVencerBadge = "<span class='badge bg-danger text-white status w-100'>$dias_para_vencer dias</span>";
        } elseif ($dias_para_vencer <= 90) {
            $diasVencerBadge = "<span class='badge text-dark status w-100' style='background-color: orange'>$dias_para_vencer dias</span>";
        } elseif ($dias_para_vencer <= 180) {
            $diasVencerBadge = "<span class='badge text-white status w-100' style='background-color: SteelBlue'>$dias_para_vencer dias</span>";
        } else {
            $diasVencerBadge = "<span class='badge bg-success text-white status w-100'>$dias_para_vencer dias</span>";
        }
    }

    //
    // Está em pleno gozo neste instante?
    if ( $idStatus == 3 ) $status = "<span class='badge text-light bg-dark status w-100'>em Férias</span>";

    return [
        'status' => $status,
        'xStatus' => $xStatus,
        'saldo' => $sSaldoBadge,
        'dias_vencer' => $diasVencerBadge
    ];
}
