<?php
//
//- ponto.php | Meu Ponto - Versão Colaborador (WEB)
// (C)haia, 04/12/2025
//

session_start();
header('Content-Type: text/html; charset=utf-8');

$modulo = "Ponto";
$idModulo = 13; // Colaborador
include 'header.php';

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
}

//include $_SERVER['DOCUMENT_ROOT'] . "/rh/includes/conexao_gerar.php";
include "../includes/conexao_gerar.php";

$idColab = $_SESSION['idColab'];
$idPessoa = $_SESSION['idPessoa'];
$_nome = $_SESSION['nmUsuario'];

$dias = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

//
//- VERIFICA PERÍODO DE APURAÇÃO
//
    $sql = "SELECT * FROM rh_ponto_banco WHERE status = 'ABERTO'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $periodo_ini = $linha['periodo_inicial'];
    $periodo_fim = $linha['periodo_final'];

//
//- BUSCA APURAÇÕES DO PONTO NO PERÍODO ABERTO
//
    $sql = "SELECT *, id as ponto_id
                FROM rh_ponto_banco_horas
                WHERE colaborador_id = :idColab
                AND data_ref BETWEEN :periodo_ini AND :periodo_fim
                ORDER BY data_ref DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab);
    $stmt->bindParam(':periodo_ini', $periodo_ini);
    $stmt->bindParam(':periodo_fim', $periodo_fim);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

//
//- BUSCA SOLICITAÇÕES DE AJUSTE-PONTO NO PERÍODO ABERTO
//
    $sql = "SELECT *, id as solicitacao_id 
                FROM rh_ponto_solicitacoes
                WHERE colaborador_id = :idColab
                AND date(data_hora) BETWEEN :periodo_ini AND :periodo_fim
                ORDER BY data_hora DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab);
    $stmt->bindParam(':periodo_ini', $periodo_ini);
    $stmt->bindParam(':periodo_fim', $periodo_fim);
    $stmt->execute();
    $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

//
//- BUSCA LISTA DE ESPELHOS PONTO
//
    $sql = "SELECT *, id as espelho_id
                FROM rh_ponto_espelhos
                WHERE colaborador_id = :idColab
                ORDER BY ano, mes DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $idColab);
    $stmt->execute();
    $espelhos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<main class="main">
    <link rel="stylesheet" href="css/ponto.css" />

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"> <i class="fa-solid fa-clock"></i> Meu Ponto</h2>
            <p style="color:var(--muted)">Área onde suas batidas de ponto podem ser consultadas.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button type='button' id='botao_espelho' class='btn btn-outline-primary' onclick='f_espelhos_do_ponto()'>Espelhos do Ponto</button>
            <button type='button' id='botao_ponto'   class='btn btn-outline-primary d-none' onclick='f_ver_meu_ponto()'>Meu Ponto</button>
            <button type='button' id='botao_voltar'  class='btn btn-outline-primary d-none' onclick='f_voltar_ao_espelho()'><i class="fa-solid fa-rotate-left"></i> Voltar</button>
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <div class="big-card" id='area_ponto'>
        <div class="container vh-100 text-white mt-4">
            <div class="row justify-content-center">
                <div class="col-7">

                    <div class="d-flex justify-content-center position-relative mb-3">
                        <h3 class="text-center w-100 m-0">
                            Resumo do Ponto no Período
                        </h3>
                    </div>

                    <table class="table table-striped table-hover table-responsive w-100 nowrap table-dark" id="tabPontos">
                        <thead class="text-center">
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Qtd</th>
                                <th>Prev</th>
                                <th>Trab</th>
                                <th>Saldo</th>
                                <th>Alerta</th>
                                <th><i class="fa-solid fa-bolt-lightning"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registros as $linha) {
                                extract($linha);
                                $botao = "<a href='#' class='btn btn-outline-primary btn-sm me-1' onclick='f_ver_ponto($ponto_id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
                                //
                                $data = date('d/m', strtotime($data_ref));
                                $diaSemana = $dias[date('N', strtotime($data_ref)) - 1];
                                //
                                $dsAlerta = '<i class="fa-solid fa-square-check text-success"></i>';
                                if ($alerta == "1") $dsAlerta = "<i class='fa-solid fa-triangle-exclamation text-warning'></i>";
                                //
                                $dsTipo = $tipo_dia;
                                if ($tipo_dia == "FERIADO") $dsTipo = "<spam class='badge bg-danger w-100'>Feriado</spam>";
                                if ($tipo_dia == "COMPENSADO") $dsTipo = "<spam class='badge bg-primary w-100'>Compensado</spam>";
                                //if( $tipo_dia == "DSR" ) $dsTipo = "<spam class='badge bg-success w-100'>DSR</spam>";
                                //
                                $classe_trabalhada = $classe_saldo = "";
                                $qtd = $qtd_batidas == 0 ? "-" : $qtd_batidas;
                                if ($credito - $debito < 0) $classe_saldo = "text-danger";
                                //
                                $classe_dia = "";
                                if ($diaSemana == 'Sáb') $classe_dia = "text-primary";
                                if ($diaSemana == 'Dom') $classe_dia = "text-danger";
                                //
                                echo "
                                <tr>
                                    <td class='$classe_dia'>$data $diaSemana</td>
                                    <td>$dsTipo</td>
                                    <td class='text-center'>" . $qtd . "</td>
                                    <td class='text-center'>" . seg2hora($horas_previstas) . "</td>
                                    <td class='$classe_saldo text-center'>" . seg2hora($horas_trabalhadas) . "</td>
                                    <td class='$classe_saldo text-center'>" . seg2hora($credito - $debito) . "</td>
                                    <td class='text-center'>$dsAlerta</td>
                                    <td class='text-center'>$botao</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                </div>
                <div class="col-5">
                    <div class="d-flex justify-content-center position-relative mb-3">
                        <h3 class="text-center w-100 m-0">
                            Ajustes do Ponto no Período
                        </h3>
                    </div>

                    <table class="table table-striped table-hover table-responsive w-100 nowrap table-dark" id="tabAjustes">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th class='text-center'>Hora</th>
                                <th class='text-center'>Tipo</th>
                                <th class='text-center'>Status</th>
                                <th class="text-center"><i class="fa-solid fa-bolt-lightning"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($solicitacoes as $linha) {
                                extract($linha);
                                $botao = "<a href='#!' 
                                            class='btn btn-outline-primary btn-sm me-1' 
                                            onclick='f_ver_solicitacao($solicitacao_id)'>
                                            <i class='fa-solid fa-magnifying-glass'></i>
                                          </a>";
                                //
                                $data = date('d/m', strtotime($data_hora));
                                $hora = date('H:i', strtotime($data_hora));
                                $diaSemana = $dias[date('N', strtotime($data_hora)) - 1];
                                //
                                $classe_dia = "";
                                if ($diaSemana == 'Sáb') $classe_dia = "text-primary";
                                if ($diaSemana == 'Dom') $classe_dia = "text-danger";
                                //
                                $dsTipo = $tipo_dia;
                                if ($tipo_dia == "FERIADO") $dsTipo = "<spam class='badge bg-danger w-100'>Feriado</spam>";
                                if ($tipo_dia == "COMPENSADO") $dsTipo = "<spam class='badge bg-primary w-100'>Compensado</spam>";
                                //if( $tipo_dia == "DSR" ) $dsTipo = "<spam class='badge bg-success w-100'>DSR</spam>";
                                //
                                $dsStatus = $status;
                                if ($status == "AGUARDANDO") $dsStatus = "<spam class='badge bg-warning w-100 text-dark'>Aguardando</spam>";
                                if ($status == "APROVADO") $dsStatus = "<spam class='badge bg-success w-100'>Aprovado</spam>";
                                if ($status == "REJEITADO") $dsStatus = "<spam class='badge bg-danger w-100'>Rejeitado</spam>";
                                //
                                echo "
                                <tr>
                                    <td class='$classe_dia'>$data $diaSemana</td>
                                    <td class='text-center'>$hora</td>
                                    <td class='text-center'>$tipo</td>
                                    <td class='text-center'>$dsStatus</td>
                                    <td class='text-center'>$botao</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="big-card d-none" id='area_espelho'>
        <div class="container vh-100 text-white mt-4">
            <div class="row justify-content-center">
                <div class="col-7">

                    <div class="d-flex justify-content-center position-relative mb-3">
                        <h3 class="text-center w-100 m-0">
                            Espelhos de Ponto
                        </h3>
                    </div>

                    <table class="table table-striped table-hover table-responsive w-100 nowrap table-dark" id="tabPontos">
                        <thead class="text-center">
                            <tr>
                                <th>Ano</th>
                                <th>Mês</th>
                                <th>H.Normais</th>
                                <th>H.Extras</th>
                                <th>H.Faltas</th>
                                <th>Status</th>
                                <th><i class="fa-solid fa-bolt-lightning"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($espelhos as $linha) {
                                extract($linha);
                                if( $status != "ALERTA"){
                                    $botao = "<a href='#!' 
                                                class='btn btn-outline-primary btn-sm me-1' 
                                                onclick='f_ver_espelho($espelho_id, `$status`)'>
                                                <i class='fa-solid fa-magnifying-glass'></i>
                                            </a>";
                                } else {
                                    $botao = "<a href='#!' 
                                                class='btn btn-outline-secondary btn-sm me-1' >
                                                <i class='fa-solid fa-magnifying-glass'></i>
                                            </a>";                                    
                                }
                                //
                                $dsStatus = $status;
                                if ($status == "ALERTA"  ) $dsStatus = "<spam class='badge bg-warning w-100 text-dark'>Alerta</spam>";
                                if ($status == "GERADO"  ) $dsStatus = "<spam class='badge bg-primary w-100'>Assinar</spam>";
                                if ($status == "ASSINADO") $dsStatus = "<spam class='badge bg-success w-100'>Assinado</spam>";
                                //
                                echo "
                                <tr>
                                    <td class='text-center'>$ano</td>
                                    <td class='text-center'>$dsMes</td>
                                    <td class='text-center'>" . seg2hora($horas_normal) . "</td>
                                    <td class='text-center'>" . seg2hora($extras) . "</td>
                                    <td class='text-center'>" . seg2hora($faltas) . "</td>
                                    <td class='text-center'>$dsStatus</td>
                                    <td class='text-center'>$botao</td>
                                </tr>";
                            }?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <div class="big-card d-none" id='area_arquivo'></div>

    <!-- The Modal EDITAR PONTO -->
    <div class="modal fade" id="modalPonto" tabindex="-1" aria-labelledby="editarPontoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-regular fa-pen-to-square"></i> Meu Ponto</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body" id='pontoConteudo'></div>
            </div>
        </div>
    </div>

    <!-- The Modal EDITAR SOLICITAÇÃO -->
    <div class="modal fade" id="modalSolicitacao" tabindex="-1" aria-labelledby="editarSolicitacaoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-regular fa-pen-to-square"></i> Minha Solicitação</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body" id='pontoConteudo'></div>
            </div>
        </div>
    </div>

    <script src="js/ponto.js"></script>
</main>
<?php include 'footer.php';

// Função auxiliar: converte segundos em HH:MM
function seg2hora($segundos)
{
    if ($segundos === null || $segundos === '') return "00:00";
    $horas = floor(abs($segundos) / 3600);
    $minutos = floor((abs($segundos) % 3600) / 60);
    return sprintf("%02d:%02d", $horas, $minutos);
}
