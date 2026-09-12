<?php
//
//- rh_ouvidoria.php | Denúncias ao RH
// (C)haia, 23/07/2025

session_start();

$idModulo = 15; // Denúncias ao RH 

if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [3, 9], true)) {
    header('Location: logout.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tabela de ti_logins no Sistema" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_ouvidoria.css" rel="stylesheet" />

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
                    <div class="card mt-2 bg-dark text-white">
                        <h3 class="m-4">
                            <i class="fa-solid fa-heart-circle-bolt"></i> OUVIDORIA INTERNA
                        </h3>
                    </div>
                    <div id='divAlertaAcolhimento' class="text-center h5"></div>
                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Relação dos Relatos
                            </div>
                            <div class="float-end">
                                <a href='#!' onclick='incluir()' class='btn btn-sm btn-outline-success'>Incluir</a>
                            </div>
                            <!--<div class="float-end"><a href='#!' onclick='f_incluir()' class='btn btn-sm btn-outline-success'>Incluir</a></div> -->
                        </div>
                        <div class="card-body">
                            <table id="tabela" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th><sup>0</sup>ID</th>
                                        <th><sup>1</sup>Data</th>
                                        <th><sup>2</sup>Nome</th>
                                        <th><sup>3</sup>Tipo de Relato</th>
                                        <th><sup>4</sup>Acompanhamento</th>
                                        <th><sup>5</sup>Status</th>
                                        <th><sup>6</sup>TMA</th>
                                        <th style='width: 110px'><sup>6</sup>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- The Modal INCLUIR RELATO -->
                <div class="modal fade" id="modalIncluir" tabindex="-1" aria-labelledby="modalIncluirLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title" id="modalIncluirLabel">Canal de Ouvidoria do RH</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body" style='background-color: #dfe6eeff; padding: 20px; border-radius: 5px;'>
                                <form id="denunciaForm" method="post" action="rh_denuncia_aj.php">

                                    <!-- Identificação -->
                                    <div class="form-section">

                                        <h5>1. Idenficação do Relatante</h5>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="identificacao" id="anonimo" value="anonimo" checked onclick="mostrarIdentificacao(false)">
                                            <label class="form-check-label" for="anonimo">Será anônimo(a)</label>
                                        </div>

                                        <div class="form-check form-check-inline ">
                                            <input class="form-check-input" type="radio" name="identificacao" id="identificado" value="identificado" onclick="mostrarIdentificacao(true)">
                                            <label class="form-check-label" for="identificado">Será identificado(a)</label>
                                        </div>

                                        <div id="dadosIdentificacao" style="display: none;" class="mt-3">
                                            <input type="text" class="form-control mb-2" name="nome" placeholder="Nome completo" onkeyup="atualizaContato()">
                                            <input type="email" class="form-control mb-2" name="email" placeholder="E-mail (opcional)" onkeyup="atualizaContato()">
                                            <input type="text" class="form-control mb-2" name="telefone" placeholder="Telefone (opcional)" onkeyup="atualizaContato()">
                                        </div>
                                    </div>
                                    <hr>
                                    <!-- Tipo de assédio -->
                                    <div class="form-section row align-items-center mt-2">
                                        <h5>2. Tipo de Relato</h5>
                                        <div class="col-sm-3">
                                            <select class="form-select" name="tipoAssedio" required>
                                                <option value="">Selecione...</option>
                                                <option value="moral">Relato de Assédio Moral</option>
                                                <option value="sexual">Relato de Assédio Sexual</option>
                                                <option value="ambos">Ambos</option>
                                                <option value="outro">Outro Relato</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="tipoOutro" placeholder="Especificar (se aplicável)">
                                        </div>
                                    </div>


                                    <!-- Relato -->
                                    <div class="form-section mt-2">
                                        <h5>3. Relato do ocorrido</h5>
                                        <textarea id="relato" name="relato"></textarea>
                                        <span class='ms-3' style="font-size: 12px;">informe o que aconteceu, onde e quando, com quem e como se sentiu? Informe apelidos, cargos ...</span>
                                    </div>

                                    <!-- Envolvidos -->
                                    <div class="form-section">
                                        <h5>4. Nome(s) do(s) envolvido(s)</h5>
                                        <input type="text" class="form-control" name="envolvidos" placeholder="Informe nomes, apelidos ou cargos (opcional)">
                                    </div>

                                    <!-- Testemunhas -->
                                    <div class="form-section mt-2">
                                        <h5>5. Houve testemunhas?</h5>
                                        <select class="form-select" name="testemunhas" onchange="mostrarTestemunhas(this.value)">
                                            <option value="">Selecione...</option>
                                            <option value="sim">Sim</option>
                                            <option value="nao">Não</option>
                                            <option value="nsei">Não sei</option>
                                        </select>
                                        <div id="campoTestemunhas" style="display: none;" class="mt-2">
                                            <input type="text" class="form-control" name="nomesTestemunhas" placeholder="Nome(s) ou cargo(s) das testemunhas (opcional)">
                                        </div>
                                    </div>

                                    <!-- Comunicação prévia -->
                                    <div class="form-section mt-2">
                                        <h5>6. Você já comunicou esse fato a alguém?</h5>
                                        <select class="form-select" name="comunicado" onchange="mostrarComunicado(this.value)">
                                            <option value="">Selecione...</option>
                                            <option value="sim">Sim</option>
                                            <option value="nao">Não</option>
                                        </select>
                                        <div id="campoComunicado" style="display: none;" class="mt-2">
                                            <textarea class="form-control" name="respostaComunicado" rows="3" placeholder="Quem foi comunicado e qual foi a resposta..."></textarea>
                                        </div>
                                    </div>

                                    <!-- Acompanhamento -->
                                    <div class="form-section mt-2">
                                        <h5>7. Deseja ser contatado(a) para acompanhamento?</h5>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="acompanhamento" id="contatoSim" value="sim" onclick="requerContato(true)">
                                            <label class="form-check-label" for="contatoSim">Sim, aceita contato para acompanhamento</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="acompanhamento" id="contatoNao" value="nao" checked onclick="requerContato(false)">
                                            <label class="form-check-label" for="contatoNao">Não, prefire apenas registrar a denúncia</label>
                                        </div>
                                        <div id="dadosContato" style="display: none;" class="mt-3">
                                            <input type="text" class="form-control mb-2" name="nomeContato" id="nomeContato" placeholder="Nome (se ainda não preenchido)">
                                            <input type="email" class="form-control mb-2" name="emailContato" id="emailContato" placeholder="E-mail">
                                            <input type="text" class="form-control mb-2" name="telefoneContato" id="telefoneContato" placeholder="Telefone">
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="modal-footer"
                                style="background-color: #dfe6eeff; 
                                        padding: 20px; 
                                        border-radius: 5px; 
                                        border-top: 2px solid #555;">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cancelar</button>
                                <button type="button" class="btn btn-dark px-4" onClick="enviar()"> <i class="fa-solid fa-share"></i> Salvar</button>
                            </div>

                        </div>
                    </div>
                </div>


            </main>

            <!-- 
                    Aqui Termina o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_ouvidoria.js"></script>
</body>

</html>