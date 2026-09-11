<?php
//
//- rh_ponto.php | Controle de Ponto dos Colaboradores
//- (C)haia, 16/12/2025
//

session_start();
$modulo = 22; // Controle de Ponto

include "includes/conexao_gerar.php";

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Programa Principal" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- jQuery -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>

    <!-- jQuery UI (necessário para autocomplete) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>

    <!-- CSS do sistema -->
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_ponto.css" rel="stylesheet" />
</head>


<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">

            <!-- Aqui COMEÇA o conteúdo da página -->

            <main class="text-light min-vh-100 p-4" style='background-color: rgba(0, 0, 0, 0.71);'>
                <div class="container-fluid">
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-6">
                            <h1 class="fw-bold text-white">
                                <i class="fa-solid fa-clock"></i> Controle Eletrônico de Ponto
                            </h1>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-outline-primary btn-sm botao" id="botao_ajustes" onclick="f_ajustes()">Ajustes</button>
                            <button type="button" class="btn btn-outline-primary btn-sm botao" id="botao_calendario" onclick="f_calendario()">Calendário</button>
                            <button type="button" class="btn btn-outline-primary btn-sm botao" id="botao_espelho" onclick="f_espelhos_do_ponto()">Espelhos</button>
                        </div>
                    </div>
                    <div class="row">

                        <!-- COLUNA 1 -->
                        <div class="col-md-6 cartao_dash" id="">

                            <!-- DASHBOARD (Coluna 1) -->
                            <div class=' container mt-4' id='divDashboard'>
                                <div class="h4">DASHBOARD</div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card text-center w-100 bg-success">
                                            <div class="card-body">
                                                <h5 class="card-title">Inconsistências</h5>
                                                <p class="card-text">
                                                    <span class="viscampo text-dark text-center h3" id="dash_inconsistencias">342</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card text-center w-100 bg-primary">
                                            <div class="card-body">
                                                <h5 class="card-title">Solicitações Pendentes</h5>
                                                <p class="card-text">
                                                    <span class="viscampo text-dark text-center h3" id="dash_pendentes">123</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ESPELHO DO CARTÃO PONTO (Coluna 1) -->
                            <div class='container mt-4 d-none' id='divVerEspelho'>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="h4">ESPELHO DO CARTÃO PONTO</div>
                                    <button type='button' class='btn btn-outline-primary btn-sm botao' onclick='f_dashboard()'>Voltar</button>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="form-label">Mês Ref.</label>
                                                <strong><span class="viscampo text-dark text-center h3" id="esp_mes_ref"></span></strong>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Nome</label>
                                                <span class="viscampo text-dark text-center" id="esp_nome"></span>
                                            </div>
                                            <div class="col-md-6 mt-2">
                                                <label class="form-label">Período Inicial</label>
                                                <span class="viscampo text-dark text-center" id="esp_periodo_inicial"></span>
                                            </div>
                                            <div class="col-md-6 mt-2">
                                                <label class="form-label">Período Final</label>
                                                <span class="viscampo text-dark text-center" id="esp_periodo_final"></span>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <label class="form-label">Hs.Normal</label>
                                                <span class="viscampo text-dark text-center" id="esp_horas_normais"></span>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <label class="form-label">Hs.Extras</label>
                                                <span class="viscampo text-dark text-center" id="esp_horas_extras"></span>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <label class="form-label">Hs.Faltas</label>
                                                <span class="viscampo text-dark text-center" id="esp_horas_faltas"></span>
                                            </div>
                                            <div class="col-md-12 mt-2">
                                                <label class="form-label">Status</label>
                                                <span class="text-center" id="esp_status"></span>
                                            </div>
                                            <div class="col-md-12 mt-2 d-none" id="divAssinadoEm">
                                                <label class="form-label">Assinado em:</label>
                                                <span class="viscampo text-dark text-center" id="esp_assinado_em"></span>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="text-center">Visualizar</h5>
                                        <div class="text-center">
                                            <label class="fw-bold">Espelho anexado</label>
                                            <div id="view_documento" class="border rounded p-2" style="min-height: 200px;">
                                                <!-- Aqui você pode injetar via JS:
                                                            <embed src="arquivo.pdf" type="application/pdf" width="100%" height="400px" />
                                                            ou <img src="imagem.jpg" class="img-fluid" />
                                                            ou até um link -->
                                            </div>
                                            <input type="hidden" id="url_arquivo_espelho">
                                            <button type="button" class="btn btn-outline-primary w-100 mt-3" onclick="f_preview_documento()">Ver</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DETALHE DO REGISTRO DO PONTO -->
                            <div class='container mt-4 d-none' id='divDetalhePonto'>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="h4">SOLICITAÇÃO DE AJUSTE DO PONTO</div>
                                    <button type='button' class='btn btn-outline-primary btn-sm botao' onclick='f_dashboard()'>Voltar</button>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row mt-3">
                                            <div class="col-4"><spam class='text-primary'>Data Solicitação </spam><br><span class='text-light' id="data_solicitacao"></span></div>
                                            <div class="col-4"><spam class='text-primary'>Tipo Solicitação </spam><br><span class='text-light' id="tipo_solicitacao"></span></div>
                                            <div class="col-4"><spam class='text-primary'>Ajuste Solicitado</spam><br><span class='text-light' id="ajuste_solicitado"></span></div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-4"><spam class='text-primary'>Status </spam><br><span class='text-light' id="status_solicitacao"></span></div>
                                            <div class="col-4"><spam class='text-primary'>Aplicado no Ponto </spam><br><span class='text-light' id="aplicado_solicitacao"></span></div>
                                            <div class="col-4"><spam class='text-primary'>Motivo</spam><br><span class='text-light' id="motivo_solicitado"></span></div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-4"><spam class='text-primary'>Gestor </spam><br><span class='text-light' id="gestor_solicitacao"></span></div>
                                            <div class="col-4"><spam class='text-primary'>Obs Status </spam><br><span class='text-light' id="decisao_solicitacao"></span></div>
                                            <!--<div class="col-4"><spam class='text-primary'>Motivo</spam><br><span class='text-light' id="motivo_solicitado"></span></div> -->
                                        </div>
                                    </div>
                                </div>
                            </div>      

                        </div>

                        <!-- COLUNA 2 -->
                        <div class="col-md-6 cartao_dash">
                            <div class="text-end mt-1">

                            </div>
                            <div class="h5 text-center" id="msgAlerta"></div>

                            <!-- Calendário -->
                            <div class="container mt-4" id="divCalendario">

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h4 class="fw-bold text-white m-2">
                                        Calendário
                                    </h4>

                                    <button
                                        type="button"
                                        class="btn btn-outline-light btn-sm botao"
                                        id="botao_incluir_calendario"
                                        onclick="f_incluir_calendario()">
                                        <i class="fa-solid fa-calendar-plus"></i> Incluir data
                                    </button>
                                </div>

                                <table
                                    class="table table-dark table-hover table-striped nowrap w-100 table-sm"
                                    id='tabCalendario'>
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Cidade</th>
                                            <th>UF</th>
                                            <th><i class="fa-solid fa-bolt-lightning"></i></th>
                                            <th>Descricao</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Espelho do Ponto -->
                            <div class="container mt-4 d-none" id="divEspelho">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h4 class="fw-bold text-white m-2">
                                        Espelhos do Ponto
                                    </h4>
                                    <select name="seletorStatus" id="seletorStatus" class="form-select form-select-sm" onchange='f_selecionouStatus()'>
                                        <option value="">Status: Todos</option>
                                        <option value="ALERTA">Alerta</option>
                                        <option value="GERADO">Assinar</option>
                                        <option value="ASSINADO">Assinado</option>
                                    </select>
                                </div>

                                <table
                                    class="table table-dark table-hover table-striped nowrap w-100 table-sm"
                                    id='tabEspelhos'>
                                    <thead>
                                        <tr>
                                            <th>Data Fim</th>
                                            <th>Mês</th>
                                            <th>Colaborador</th>
                                            <th>Status</th>
                                            <th><i class="fa-solid fa-bolt-lightning"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <!-- AJUSTES DO PONTO -->
                            <div class="container mt-4 d-none" id="divAjustes">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h4 class="fw-bold text-white m-2">
                                        Ajustes do Cartão Ponto
                                    </h4>
                                    <select name="seletorStatus2" id="seletorStatus2" class="form-select form-select-sm" onchange='f_selecionouStatus2()'>
                                        <option value="">Status: Todos</option>
                                        <option value="APROVADO">Status: Aprovado</option>
                                        <option value="REJEITADO" class="text-danger">Status: Rejeitado</option>
                                        <option value="AGUARDANDO" selected>Status: Aguardando</option>
                                    </select>
                                </div>

                                <table
                                    class="table table-dark table-hover table-striped nowrap w-100 table-sm"
                                    id='tabAjustes'>
                                    <thead>
                                        <tr>
                                            <th>Colaborador</th>
                                            <th>Data</th>
                                            <th><i class="fa-solid fa-calendar"></i></th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th><i class="fa-solid fa-bolt-lightning"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </main>

            <!-- MODAL: Incluir Data no Calendário -->
            <div class="modal fade" id="modalCalendario" tabindex="-1" data-bs-theme="dark"
                aria-labelledby="modalCalendarioLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <!-- Header -->
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-calendar-plus"></i> Nova Data no Calendário
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <form id="formCalendario">

                                <div class="row g-3">

                                    <!-- Data -->
                                    <div class="col-md-5">
                                        <label class="form-label">Data</label>
                                        <input type="date" class="form-control" name="data_calendario" id='data_calendario' required>
                                    </div>

                                    <!-- Tipo -->
                                    <div class="col-md-7">
                                        <label class="form-label">Tipo do Evento</label>
                                        <select class="form-select" name="tipo" id="tipo_calendario" required>
                                            <option value="">Selecione</option>
                                            <option value="FERIADO">Feriado</option>
                                            <option value="DSR">DSR</option>
                                            <option value="FACULTATIVO">Ponto Facultativo</option>
                                            <option value="COMPENSADO">Compensado</option>
                                            <option value="REDUZIDO">Horário Reduzido</option>
                                        </select>
                                    </div>

                                    <!-- Descrição -->
                                    <div class="col-12">
                                        <label class="form-label">Descrição</label>
                                        <input type="text" class="form-control" name="descricao" required>
                                    </div>

                                    <!-- UF (feriado estadual) -->
                                    <div class="col-md-4 d-none" id="grupo_uf">
                                        <label class="form-label">UF (se estadual)</label>
                                        <?= f_uf($conn); ?>
                                    </div>

                                    <!-- Cidade (feriado municipal) -->
                                    <div class="col-md-8 d-none" id="grupo_cidade">
                                        <label class="form-label">Cidade (se municipal)</label>
                                        <input type="text" class="form-control" name="cidade" id="cidade" placeholder="comece digitando...">
                                        <input type="hidden" id="cidade_id" name="cidade_id">
                                    </div>

                                    <!-- Horário especial -->
                                    <div class="col-md-6 d-none" id="grupo_hora_ini">
                                        <label class="form-label">Hora Inicial</label>
                                        <input type="time" class="form-control text-center" name="hora_ini">
                                    </div>

                                    <div class="col-md-6 d-none" id="grupo_hora_fim">
                                        <label class="form-label">Hora Final</label>
                                        <input type="time" class="form-control text-center" name="hora_fim">
                                    </div>

                                </div>

                            </form>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer d-flex gap-2" id='botoes_calendario'>
                            <button class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark"></i> Cancelar
                            </button>
                            <button class="btn btn-outline-success flex-fill" onclick="f_salvarCalendario()">
                                <i class="fa fa-save"></i> Salvar
                            </button>
                        </div>
                        <div class="h5 text-center" id='msgAlertaCalendario'></div>
                    </div>
                </div>
            </div>

            <!-- MODAL: Ver/Editar/Excluir Evento do Calendário -->
            <div class="modal fade" id="modalCalendarioEvento" tabindex="-1" data-bs-theme="dark"
                aria-labelledby="modalCalendarioLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <!-- Header -->
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-calendar-plus"></i> Evento do Calendário
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <form id="formCalendarioEvento">
                                <input type="hidden" id="id_calendario_evento" name="id_calendario_evento">
                                <div class="row g-3">

                                    <!-- Data -->
                                    <div class="col-md-5">
                                        <label class="form-label">Data</label>
                                        <input type="date" class="form-control" name="data_calendario" id='data_calendario' readonly>
                                    </div>

                                    <!-- Tipo -->
                                    <div class="col-md-7">
                                        <label class="form-label">Tipo do Evento</label>
                                        <select class="form-select" name="tipo" id="tipo_calendario" required>
                                            <option value="">Selecione</option>
                                            <option value="FERIADO">Feriado</option>
                                            <option value="DSR">DSR</option>
                                            <option value="FACULTATIVO">Ponto Facultativo</option>
                                            <option value="COMPENSADO">Compensado</option>
                                            <option value="REDUZIDO">Horário Reduzido</option>
                                        </select>
                                    </div>

                                    <!-- Descrição -->
                                    <div class="col-12">
                                        <label class="form-label">Descrição</label>
                                        <input type="text" class="form-control" name="descricao" id="descricao_calendario" required>
                                    </div>

                                    <!-- UF (feriado estadual) -->
                                    <div class="col-md-4 d-none" id="grupo_uf_calendario">
                                        <label class="form-label">UF (se estadual)</label>
                                        <?= f_uf($conn); ?>
                                    </div>

                                    <!-- Cidade (feriado municipal) -->
                                    <div class="col-md-8 d-none" id="grupo_cidade_calendario">
                                        <label class="form-label">Cidade (se municipal)</label>
                                        <input type="text" class="form-control" name="cidade" id="cidade_calendario" placeholder="comece digitando...">
                                        <input type="hidden" id="cidade_id" name="cidade_id">
                                    </div>

                                    <!-- Horário especial -->
                                    <div class="col-md-6 d-none" id="grupo_hora_ini">
                                        <label class="form-label">Hora Inicial</label>
                                        <input type="time" class="form-control text-center" name="hora_ini" id="hora_ini">
                                    </div>

                                    <div class="col-md-6 d-none" id="grupo_hora_fim">
                                        <label class="form-label">Hora Final</label>
                                        <input type="time" class="form-control text-center" name="hora_fim" id="hora_fim">
                                    </div>

                                </div>

                            </form>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer d-flex gap-2" id='botoes_calendario_evento'>
                            <button class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark"></i> Cancelar
                            </button>
                            <button class="btn btn-outline-danger flex-fill" onclick="f_excluirCalendarioEvento()">
                                <i class="fa fa-trash"></i> Excluir
                            </button>
                            <button class="btn btn-outline-success flex-fill" onclick="f_salvarCalendarioEvento()">
                                <i class="fa fa-save"></i> Salvar
                            </button>
                        </div>
                        <div class="h5 text-center" id='msgAlertaCalendarioEvento'></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_ponto.js"></script>
</body>

</html>

<?PHP // - ROTINAS AUXILIARES

function f_uf($conn)
{
    $sql = "SELECT * FROM rh_uf ORDER BY uf";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //
    $html = "<select class='form-select' name='uf' id='uf' onchange='mudou_uf()'>";
    $html .= "<option value=''>Selecione</option>";
    foreach ($estados as $linha) {
        $cor = "text-dark";
        if ($linha['uf'] == 'PR') $cor = 'text-primary';
        $html .= "<option class='$cor' value='" . $linha['uf'] . "'>" . $linha['nome'] . "</option>";
    }
    $html .= "</select>";
    return $html;
}
