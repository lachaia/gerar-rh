<?php
//
// - rh_pessoas.php  -  Grade de manutenção do Cadastro de ti_pessoas (Inclui, Altera, Exclui, Conculta)
// - (C) Chaia, 03/02/2025
//

$idModulo = 2; // rh_pessoas

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
} else {
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
    f_log("CON", "Consulta grade de Pessoas", "rh_pessoas", $idModulo, 0);
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tabela de ti_pessoas do Sistema" />
    <meta name="author" content="LAChaia" />
    <title>Gerar: ti_pessoas</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_pessoas.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <!-- 
                    Aqui COMEÇA o conteúdo da página 
                -->
            <main>
                <div class="container-fluid px-4">
                    <!-- GRID DOS DADOS -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white mt-2 h4">
                            <div class="d-flex">
                                <i class="fa-regular fa-address-book"></i>&nbsp; Cadastro de PESSOAS
                            </div>
                            <a href='rh_pessoa_frm.php?acao=incluir' class="btn btn-outline-success btn-sm">Incluir</a>
                        </div>
                        <div class="card-body">
                            <div id="msgAlert"></div>
                            <table id="example" class="table table-striped table-hover table-bordered table-sm w-100 nowrap" style="width:100%">
                                <thead>
                                    <tr class="bg-secondary">
                                        <th style='width: 100px; color: white'><sup>0</sup>ID</th>
                                        <th style='width: 300px; color: white'><sup>1</sup>Nome</th>
                                        <th style='width: 300px; color: white'><sup>2</sup>Nome Social</th>
                                        <th style='width: 200px; color: white'><sup>3</sup>CPF</th>
                                        <th style='width: 200px; color: white'><sup>4</sup>Telefone</th>
                                        <th style='width: 300px; color: white'><sup>5</sup>e-Mail</th>
                                        <th style='width: 100px; color: white'><sup>6</sup>Ativo</th>
                                        <th style='width: 100px; color: white'><sup>7</sup>Colab</th>
                                        <th style='width: 200px; color: white'><sup>8</sup>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- The Modal E-MAIL  -->
            <div class="modal fade modal-lg" id="modalEmail">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">Enviar E-Mail</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body fs-13p">
                            <form id='formEmail'>
                                <div class="row">
                                    <div class="col-sm-1">Para</div>
                                    <div class="col-sm-11 h6" id='para'>Aguarde...</div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-1">CC</div>
                                    <div class="col-sm-11"><input type="text" name='cc' id='cc' class='form-control form-control-sm' placeholder="com cópia para...."></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-1">CCO</div>
                                    <div class="col-sm-11"><input type="text" name='cco' id='cco' class='form-control form-control-sm' placeholder="com cópia oculta para...."></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-1">Título</div>
                                    <div class="col-sm-11"><input type="text" name='titulo' id='titulo' class='form-control form-control-sm' placeholder="Título..."></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="container fs-13p">
                                        <textarea name="mensagem" id="mensagem" class="form-control fs-13p" rows='5' placeholder="Sua mensagem aqui..."></textarea>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-1">Anexo</div>
                                    <div class="col-sm-11">
                                        <input type="file" name='arquivos[]' id='arquivos' class='form-control form-control-sm' multiple placeholder="Selecione os anexos">
                                    </div>
                                </div>
                                <input type="hidden" name='destino' id="destino" value="">
                                <input type="hidden" name='idPessoa' id="idPessoa" value="">

                            </form>
                        </div>
                        <!-- Modal footer -->
                        <div class="row container">
                            <div class='col-sm-6'></div>
                            <div class="col-sm-6 d-flex">
                                <button type='button' class='btn btn-sm btn-outline-danger w-100 m-2' data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Fechar</button>
                                <button type='reset' class='btn btn-sm btn-outline-secondary w-100 m-2'><i class="fa-solid fa-recycle"></i> Reset</button>
                                <button type='button' class='btn btn-sm btn-outline-success w-100 m-2' onclick="f_email_commit()"><i class="fa-solid fa-share"></i> Enviar</button>
                            </div>
                        </div>
                        <div class="col-sm-12 text-center invisivel" id='divMensagemEml'>
                            <h3>Aguarde...</h3>
                        </div>
                    </div>

                </div>
            </div>
            <!-- 
                    Aqui Termina o conteúdo da página 
            -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/rh_pessoas.js"></script>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/cpf.js"></script>
</body>

</html>