<?PHP
//
// equipamentos.php | Módulo de EQUIPAMENTOS do Colaborador para o Portal do Colaborador
// (C)haia, 16/10/2025

session_start();

$idModulo = 13; // Colaborador

$modulo = "Equipamentos";
include 'header.php';

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include "../includes/conexao_gerar.php";

$idColab = $_SESSION['idColab'];
$idPessoa = $_SESSION['idPessoa'];
$_nome = $_SESSION['nmUsuario'];

?>
<link rel="stylesheet" href="css/equipamentos.css" />
<main class="main">

    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"> <i class="fa-solid fa-computer"></i> Equipamentos</h2>
            <p style="color:var(--muted)">Lista de itens alocados ao colaborador</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php'><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- Equipamentos -->
    <div class="big-card">
        <hr>
        <div class="row justify-content-center">
            <div class="col-8">
                <div class="d-flex justify-content-center position-relative mb-3">
                    <h3 class="text-center w-100 m-0">
                        Meus Termos de Responsabilidade
                    </h3>
                </div>

                <table class="table table-striped table-hover table-sm table-responsive w-100 table-dark" id="tabAvaliacoes">
                    <thead>
                        <tr class='align-middle text-center text-nowrap'>
                            <th>ID</th>
                            <th>Entregue em</th>
                            <th>Entregue por</th>
                            <th>Devolvido em</th>
                            <th>Recebido por</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pesquisa = "SELECT S.*, P.nome 
                                        FROM rh_equip_termos S
                                        INNER JOIN rh_pessoas P on P.idPessoa = S.idPessoa
                                        WHERE S.idPessoa = $idPessoa";
                        $stmt = $conn->prepare($pesquisa);
                        $stmt->execute();
                        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            extract($linha);
                            $acoes =   "<a href='#' class='btn btn-outline-primary btn-sm me-1' onClick='f_equip_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
                            $criado_em = substr($criado_em, 0, 10);
                            if (empty($data_devolucao)) {
                                $data_devolucao = "Aberto";
                                $user_devolucao = "Aberto";
                            }
                            $dsStatus = "Indefinido";
                            if ($status == 'Assinado') $dsStatus = "<span class='badge bg-success w-100'>Assinado</span>";
                            if ($status == 'Pendente') $dsStatus = "<button class='btn btn-outline-primary btn-sm w-100' onClick='f_termo_assinar($id)'>Assinar</button>";
                            if ($status == 'Baixado') $dsStatus = "<span class='badge bg-danger w-100'>Baixado</span>";
                            echo "
                                <tr class='align-middle text-center text-nowrap'>
                                    <td>$id</td>
                                    <td>$criado_em</td>
                                    <td>$criado_por</td>
                                    <td>$data_devolucao</td>
                                    <td>$user_devolucao</td>
                                    <td>$dsStatus</td>
                                    <td>$acoes</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-6">
                <h3 class="text-center w-100 m-2 text-light">
                    <i class="fa-solid fa-laptop"></i> Lista de Equipamentos em Uso
                </h3>
                <?php
                $sql = "SELECT E.*, TP.descricao as dsTipo 
                FROM RH.rh_equip_termos_ld E
                INNER JOIN rh_equip_termos T on T.id = E.idEquipTermo
                INNER JOIN rh_equip_tipos TP ON TP.id = E.idTipo
                WHERE T.status = 'Assinado' AND T.idPessoa = :idPessoa";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
                $stmt->execute();

                echo "<div class='table-responsive'>";
                echo "<table class='table table-dark table-bordered table-striped align-middle text-center text-nowrap tabela-transparente'>";
                echo "<thead class='table-dark'>
                <tr>
                    <th>Tipo</th>
                    <th>Modelo</th>
                    <th>Patrimônio</th>
                </tr>
              </thead><tbody>";
                while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($linha);
                    echo "<tr><td>$dsTipo</td><td>$modelo</td><td>$patrimonio</td></tr>";
                }
                echo "</tbody></table>";
                echo "</div>";
                ?>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="alert alert-info border-0 py-2 px-3 mt-3 shadow-sm rounded-3" role="note" aria-live="polite">
                    <div class="d-flex align-items-start">
                        <div class="me-2">
                            <i class="fa-solid fa-pen-nib fa-lg text-primary" aria-hidden="true"></i>
                        </div>
                        <div>
                            <strong class="m-2">Observação:</strong>
                            <div class="small text-muted m-2" style="font-size: 16px;">
                                Caso exista algum <strong>Termo de Responsabilidade</strong> pendente de assinatura,
                                o colaborador deverá realizar a assinatura eletrônica clicando no botão
                                <strong><i class="fa-solid fa-signature text-primary"></i> Assinar</strong>
                                para regularizar a situação e confirmar o recebimento do equipamento.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- The Modal VISUALISAR TERMOS-->
    <div class="modal fade" id="modalVerTermo" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header text-white">
                    <h4 class="modal-title"><i class="fa-solid fa-eye"></i> Detalhes do Termo</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <div class="row">
                        <!-- Coluna esquerda (campos de visualização) -->
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label class="fw-bold">Responsável pelo termo</label>
                                    <p id="vw_termo_responsavel" class="form-control-plaintext visCampo border rounded px-2"></p>
                                </div>
                                <div class="col-sm-12">
                                    <label class="fw-bold">Endereço</label>
                                    <p id="vw_termo_endereco" class="form-control-plaintext  visCampo border rounded px-2"></p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="fw-bold">e-Mail</label>
                                    <p id="vw_termo_email" class="form-control-plaintext visCampo  border rounded px-2"></p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="fw-bold">Celular</label>
                                    <p id="vw_termo_celular" class="form-control-plaintext  visCampo border rounded px-2"></p>
                                </div>

                                <div class="col-sm-12 mt-3">
                                    <label class="fw-bold">Equipamentos entregues</label>
                                    <p id="vw_termo_equipamentos" class="form-control-plaintext  visCampo border rounded px-2"></p>
                                </div>

                                <div class="col-sm-4 mt-3">
                                    <label class="fw-bold">Criado em</label>
                                    <p id="vw_termo_criado_em" class="form-control-plaintext visCampo  border rounded px-2 text-center"></p>
                                </div>
                                <div class="col-sm-4 mt-3">
                                    <label class="fw-bold">Criado Por</label>
                                    <p id="vw_termo_criado_por" class="form-control-plaintext visCampo  border rounded px-2 text-center"></p>
                                </div>
                                <div class="col-sm-4 mt-3">
                                    <label class="fw-bold">Status</label>
                                    <p id="vw_termo_status" class="form-control-plaintext visCampo  border rounded px-2 text-center w-100"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna direita (preview do documento) -->
                        <div class="col-sm-4 text-center">
                            <label class="fw-bold">Documento Anexado</label>
                            <div id="view_documento" class="border rounded p-2" style="min-height: 200px;">
                                <!-- Aqui você pode injetar via JS:
                                            <embed src="arquivo.pdf" type="application/pdf" width="100%" height="400px" />
                                            ou <img src="imagem.jpg" class="img-fluid" />
                                            ou até um link -->
                            </div>
                            <input type="hidden" id="vw_termo_documento">
                            <button type="button" class="btn btn-outline-primary w-100 mt-3" onclick="f_preview_documento()">Ver</button>
                        </div>
                    </div>
                </div>


                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Fechar</button>
                </div>

            </div>
        </div>
    </div>

    <!-- The Modal ASSINAR TERMOS-->
    <div class="modal fade" id="modalAssinar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-secondary text-white">
                    <h4 class="modal-title"><i class="fa-solid fa-eye"></i> Detalhes do Termo</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <div class="container" id='divPrincipal'>
                        <div class="card shadow-lg">
                            <div class="card-body" style="max-height:500px; overflow-y:auto;" id='termoConteudo'>
                                <!-- Exibe o termo vindo do banco -->

                            </div>
                            <div class="card-footer text-center">
                                <div class="form-check my-3 d-flex justify-content-center align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" id="concordo" onchange='marquei_lido()'>
                                    <input type="hidden" id='assinar_id' value='0'>
                                    <label class="form-check-label" for="concordo">
                                        Declaro que li e concordo com o termo de responsabilidade.
                                    </label>
                                </div>
                                <div class="d-flex justify-content-center my-3" id='botaoAssinar'>
                                    <div class="col-sm-6">
                                        <button class="btn btn-outline-primary w-100" id="btnAssinar" disabled onclick='clicou_assinar()'>
                                            Assinar
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Fechar</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal para digitar usuário/senha -->
    <div class="modal fade" id="modalAssinatura" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-width">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-center">Confirmar Assinatura</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <form id="formLogin">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuário</label>
                            <input type="text" class="form-control text-center" id="usuario" required>
                            <input type="hidden" id='idTermo' name='idTermo' value='<?= $idTermo ?>'>
                            <input type="hidden" id='token' name='token' value='<?= $token ?>'>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="senha" class="form-label">Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control text-center" id="senha" required>
                                <button type="button" class="btn btn-outline-secondary" id="toggleSenha">
                                    <i class="fa fa-eye" id="iconSenha"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id='msgAlerta' class="h5 text-center"></div>
                </div>
                <div class="modal-footer d-flex gap-2" id='botoes_assinar'>
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-success flex-fill" id="btnConfirmar" onclick='confirma_assinatura()'>Confirmar</button>
                </div>
            </div>
        </div>
    </div>

</main>
<script src="js/equipamentos.js"></script>
<?php include 'footer.php'; ?>