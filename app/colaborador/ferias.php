<?php
//
// ferias.php | Módulo de Férias do Portal do Colaborador
// (C)haia, 14/10/2025

session_start();

$idModulo = 13; // Colaborador

$modulo = "Férias";
include 'header.php';

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include "../includes/conexao_gerar.php";

$idColab = $_SESSION['idColab'];

//- RECUPERA DADOS DO COLABORADOR
//
    $sql = "SELECT C.*, P.*, CV.*, EC.categoria as dsEstadoCivil, ET.categoria as dsEtnia, CG.nome as dsCargo, F.nome as dsFuncao,
		O.idOrgao, O.descricao as dsOrgao, O.idSupervisor, O.nivel,
        (SELECT nivel FROM rh_organograma WHERE idOrgao = O.idSupervisor) as nivelSupervisor
                FROM rh_colaboradores C 
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                LEFT OUTER JOIN rh_estadoCivil EC on EC.idEstadoCivil = P.idEstadoCivil
                LEFT OUTER JOIN rh_etnias ET on ET.idEtnia = P.idEtnia
				LEFT OUTER JOIN rh_cv CV on CV.idPessoa = C.idPessoa
				LEFT OUTER JOIN rh_cargos CG on CG.idCargo = C.idCargo
				LEFT OUTER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
				LEFT OUTER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                WHERE idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($linha);

//
//- Busca o Supervisor
//
    if ($nivel == $nivelSupervisor) {
        $sql = "SELECT C.idColab as idColabSupervisor, P.nome as nmSupervisor, P.email_corporativo as emailSupervisor
                from rh_colaboradores C
                INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                where idOrgao = :idOrgao AND dcLider = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idOrgao', $idOrgao, PDO::PARAM_INT);
        //
    } else {
        $sql = "SELECT C.idColab as idCSuper, P.nome as nmSupervisor, P.email_corporativo as emailSupervisor
                from rh_colaboradores C
                INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                where idOrgao = :idOrgao";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idOrgao', $idSupervisor, PDO::PARAM_INT);
    }
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($linha) {
        extract($linha);
    } else {
        die("FALTA O CADASTRO DO SUPERVISOR DO COLABORADOR - INFORME O RH...");
    }


?>
<style>
    .check-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .check-item p {
        margin: 0;
    }

    .col-form-label
    {
        font-weight: bold;
        font-size: 13px;
        color: black;
    }
</style>
<main class="main" data-bs-theme="dark">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="fa-solid fa-plane"></i>    
                Férias do Colaborador</h2>
            <small style="color:var(--muted)">Fique atento para as regras de solicitação de agendamento de férias</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Férias (skeleton) -->
    <div class="big-card">

        <div class="container align-items-center justify-content-center vh-100 text-white mt-4">
            <div class="d-flex justify-content-center position-relative">
                <h3 class="text-center w-100">
                    Minhas Férias
                </h3>
            </div>


            <table class="table table-striped table-hover table-sm table-responsive text-center" id='tabFerias'>
                <thead>
                    <tr>
                        <th colspan='4'></th>
                        <th colspan='2' class="text-center">Período (1)</th>
                        <th colspan='2' class="text-center">Período (2)</th>
                        <th colspan='2' class="text-center">Período (3)</th>
                        <th colspan='2'></th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Aquisitivo</th>
                        <th>Concessivo</th>
                        <th>Direito</th>
                        <th>Agenda</th>
                        <th>Dias</th>
                        <th>Agenda</th>
                        <th>Dias</th>
                        <th>Agenda</th>
                        <th>Dias</th>
                        <th>Dias tirados</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?PHP
                    $sql = "SELECT F.*,
                                FLOOR(LEAST(12, TIMESTAMPDIFF(MONTH, F.inicio_aquisitivo, CURDATE())) * 2.5) AS dias_adquiridos,
                                DATEDIFF(F.fim_concessivo, CURDATE()) AS dias_para_vencer 
                            FROM rh_ferias F 
                            WHERE idColab = :idColab 
                            ORDER BY inicio_aquisitivo DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idColab', $idColab, PDO::PARAM_INT);
                    $stmt->execute();
                    //
                    //- LER OS Registros e preencher o array
                    //
                    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($linha);
                        //                                
                        $d1 = (int) $dias_parte1;
                        $d2 = (int) $dias_parte2;
                        $d3 = (int) $dias_parte3;
                        //
                        $total_usufruido = 0;
                        if (! empty($data_parte1)) $total_usufruido += $d1;
                        if (! empty($data_parte2)) $total_usufruido += $d2;
                        if (! empty($data_parte3)) $total_usufruido += $d3;
                        //
                        if ($dias_adquiridos >= 30) {
                            $saldo = $dias_adquiridos - $total_usufruido;
                        } else {
                            $saldo = "-";
                        }
                        if ($total_usufruido >= 30) {
                            $saldo = "<kbd class='bg-secondary'>-</kbd>";
                        }
                        //- Formata as datas
                        $inicio_aquisitivo = empty($inicio_aquisitivo) ? "-" : date("d/m/Y", strtotime($inicio_aquisitivo));
                        $fim_aquisitivo    = empty($fim_aquisitivo)    ? "-" : date("d/m/Y", strtotime($fim_aquisitivo));
                        $inicio_concessivo = empty($inicio_concessivo) ? "-" : date("d/m/Y", strtotime($inicio_concessivo));
                        $fim_concessivo    = empty($fim_concessivo)    ? "-" : date("d/m/Y", strtotime($fim_concessivo));
                        //
                        $data_aquisitivo = "$inicio_aquisitivo a $fim_aquisitivo";
                        $data_concessivo = "$inicio_concessivo a $fim_concessivo";
                        //
                        //-- FRUÍDAS
                        //
                        $agenda_parte1 = formatarAgenda($agenda_parte1, $dias_parte1, $data_parte1, $aprova_1_em, $aprova_rh_1_em);
                        $agenda_parte2 = formatarAgenda($agenda_parte2, $dias_parte2, $data_parte2, $aprova_2_em, $aprova_rh_2_em);
                        $agenda_parte3 = formatarAgenda($agenda_parte3, $dias_parte3, $data_parte3, $aprova_3_em, $aprova_rh_3_em);

                        //
                        $link = "<button class='btn btn-outline-primary btn-sm w-100' onclick='agendar($id)'>agendar</button>";
                        if ($dias_adquiridos >= 30 && $total_usufruido < 30) {
                            //-permite o agendamento
                            if ($agenda_parte1 == '-') $agenda_parte1 = $link;
                            elseif ($agenda_parte2 == '-' && $d1 < 30) $agenda_parte2 = $link;
                            else {
                                if (($d1 + $d2) < 30 && $agenda_parte3 == '-') {
                                    $agenda_parte3 = $link;
                                }
                            }
                        }
                        //- Preenche a tabela
                        echo "<tr>";
                        echo "<td>$id</td>";
                        echo "<td>$data_aquisitivo</td>";
                        echo "<td>$data_concessivo</td>";
                        echo "<td>$dias_adquiridos</td>";
                        echo "<td>$agenda_parte1</td>";
                        echo "<td>$dias_parte1</td>";
                        echo "<td>$agenda_parte2</td>";
                        echo "<td>$dias_parte2</td>";
                        echo "<td>$agenda_parte3</td>";
                        echo "<td>$dias_parte3</td>";
                        echo "<td>$total_usufruido</td>";
                        echo "<td>$saldo</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-end">
                <kbd class="bg-secondary m-1">Fruída</kbd>
                <kbd class="bg-primary m-1">Aguardando RH</kbd>
                <kbd class="bg-warning m-1">Aguardando Gestor</kbd>
                <kbd class="bg-success m-1">Agendada(RH)</kbd>
            </div>
            <div class="m-3" style="font-size: 18px;">
                <p class="text-success">Pedimos que, ao agendar as férias, você se atente às seguintes questões:</p>

                <div class="check-item"><i class="fa-solid fa-check text-success me-2 mt-1"></i>
                    <p>As férias só podem ser agendadas após o colaborador completar 12 meses trabalhados;</p>
                </div>
                <div class="check-item"><i class="fa-solid fa-check text-success me-2 mt-1"></i>
                    <p>As férias poderão ser agendadas em até 3 períodos de no mínimo 5 dias, porém um deles, obrigatoriamente deverá ter 14 dias;</p>
                </div>
                <div class="check-item"><i class="fa-solid fa-check text-success me-2 mt-1"></i>
                    <p>As férias não podem iniciar em Sextas-Feiras ou dois dias antes de feriados;</p>
                </div>
                <div class="check-item"><i class="fa-solid fa-check text-success me-2 mt-1"></i>
                    <p>A solicitação de férias deve ser agendada com no mínimo 45 dias de antecedência;</p>
                </div>
                <div class="check-item"><i class="fa-solid fa-check text-success me-2 mt-1"></i>
                    <p>Alterações de férias devem ser encaminhadas com no mínimo 30 dias de antecedência constando o motivo para análise;</p>
                </div>
                <div class="check-item"><i class="fa-solid fa-check text-success me-2 mt-1"></i>
                    <p>Sua solicitação será submetida a aprovação pelo gestor da área e confirmada pelo RH.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- The Modal EDITAR AGENDAMENTO -->
    <div class="modal fade" id="modalAgenda" tabindex="-1" aria-labelledby="agendaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header" style='background-color: #a59e9eff '>
                    <h4 class="modal-title">
                        <h4><i class="fa-regular fa-pen-to-square"></i> Agendamento de Férias</h4>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->

                <form id="formEditar">
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="fruido1" name="fruido1">
                    <input type="hidden" id="fruido2" name="fruido2">
                    <input type="hidden" id="fruido3" name="fruido3">
                    <input type="hidden" id="dtLimite" name="dtLimite">
                    <input type="hidden" id="idCSuper" name="idCSuper" value='<?= $idCSuper ?>'>
                    <input type="hidden" id="nmSupervisor" name="nmSupervisor" value='<?= $nmSupervisor ?>'>
                    <input type="hidden" id="emailSupervisor" name="emailSupervisor" value='<?= $emailSupervisor ?>'>
                    <input type="hidden" id="orgao" name="orgao" value='<?= "$idOrgao - $dsOrgao" ?>'>
                    <div class="modal-body">

                        <div class="row mb-3">
                            <div class="col-sm-6 text-center">
                                <label class="col-form-label">Período Aquisitivo</label>
                                <p id="aquisitivo" class="form-control-plaintext visCampo text-center h5"></p>
                            </div>
                            <div class="col-sm-6 text-center">
                                <label class="col-form-label">Período Agendamento</label>
                                <p id="concessivo" class="form-control-plaintext visCampo text-center h5"></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <!-- Agenda 1 -->
                            <div class="col-sm-2">
                                <label class="col-form-label">Agenda:1</label>
                                <input type="date" class="form-control text-center obrigatorio" id="e_agenda1" name="e_agenda1">
                            </div>
                            <div class="col-sm-2">
                                <label class="col-form-label">Dias:1</label>
                                <input type="number" class="form-control text-center obrigatorio" id="e_dias1" name="e_dias1" min="0" onchange='dias(this)'>
                            </div>
                            <!-- Agenda 2 -->
                            <div class="col-sm-2">
                                <label class="col-form-label">Agenda:2</label>
                                <input type="date" class="form-control text-center" id="e_agenda2" name="e_agenda2">
                            </div>
                            <div class="col-sm-2">
                                <label class="col-form-label">Dias:2</label>
                                <input type="number" class="form-control text-center" id="e_dias2" name="e_dias2" min="0"" onchange='dias(this)'>
                                </div>
                                <!-- Agenda 3 -->
                                <div class=" col-sm-2">
                                <label class="col-form-label">Agenda:3</label>
                                <input type="date" class="form-control text-center" id="e_agenda3" name="e_agenda3">
                            </div>
                            <div class="col-sm-2">
                                <label class="col-form-label">Dias:3</label>
                                <input type="number" class="form-control text-center" id="e_dias3" name="e_dias3" min="0"" onchange='dias(this)'    >
                                </div>
                            </div>
                            <div class=" row mb-3">
                                <div class="col-sm-12">
                                    <label for="_descricao" class="col-form-label">Observações</label>
                                    <textarea class="form-control" id="obs" name="obs" rows="4" placeholder="Suas observações sobre a solicitação:"></textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="btn-group" id='botoes_editar'>
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded m-1" data-bs-dismiss="modal">
                                        <i class="fa fa-close"></i> Cancelar
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1" id="e_btnReset" onclick='f_reset()'>
                                        <i class="fa-solid fa-recycle"></i> Reset
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm rounded m-1" id="e_btnSalvar" onclick='f_agendar_commit()'>
                                        <i class="fa fa-save"></i> Salvar
                                    </button>
                                </div>
                                <div id='msgAlertaAgenda' class="text-center"></div>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>


</main>
<script src="js/ferias.js"></script>
<?php

include 'footer.php';

//
// Formata saída das datas na grit
function formatarAgenda($agenda, $dias, $data, $aprovSup, $aprovRh)
{

    if (empty($agenda)) return "-";
    else $agenda = date("d/m/Y", strtotime($agenda));

    if (!empty($data)) {
        return "<kbd class='bg-secondary'>" . date("d/m/Y", strtotime($data)) . "</kbd>";
    }

    if (!empty($aprovRh) && !empty($agenda)) {
        return "<kbd class='bg-success'>$agenda</kbd>";
    }

    if (!empty($aprovSup) && !empty($agenda)) {
        return "<kbd class='bg-primary'>$agenda</kbd>";
    }

    if (!empty($agenda)) {
        return "<kbd class='bg-warning'>$agenda</kbd>";
    }

    return "-";
}
