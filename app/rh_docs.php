<?PHP
//
//- rh_docs.php | GED - Documentos do Sistema RH
//- (C)haia, 30/05/2025 | 10/12/2025

session_start();

$idModulo = 14; // RH-GED  
$hoje = date('Y-m-d');

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    include_once "includes/conexao_gerar.php";
    include_once "includes/f_logs.php";
    f_log("CON", "Visualiza Grid de Empresas", "empresas", $idModulo, 0);
}

$idUsuario = $_SESSION['idUsuario'];
$idSubSede = $_SESSION['idSubSede'];
$dsSubSede = $_SESSION['dsSubSede'];
$login     = $_SESSION['nmLogin'];

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tabela de ti_logins no Sistema" />
    <meta name="author" content="LAChaia" />
    <title>Gerar: Docs</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_docs.css" rel="stylesheet" />

    <!-- jQuery (DEVE vir antes de tudo que depende dele) -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- jQuery UI (depois do jQuery) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables Buttons -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>

    <!-- PDF-lib -->
    <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>

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
                    <div class="clearfix">
                        <div class="float-start mt-2 mb-2">
                            <h4><i class="fa-regular fa-folder-open"></i> DOCUMENTOS</h4>
                        </div>

                    </div>
                    <div class="card mb-4">
                        <div class="card-header">

                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-table me-2"></i>
                                    <strong>Gestão Eletrônica de Documentos</strong>
                                </div>

                                <div class="d-flex align-items-center flex-nowrap gap-2 mt-2 mt-md-0">
                                        <?= seletor_pessoas() ?>
                                        <?= seletor_documentos() ?>

                                    <div class="input-group input-group-sm flex-nowrap" style="min-width: 250px;">
                                        <input type="text" class="form-control" placeholder="Busca interna" id="buscaInterna">
                                        <span class="input-group-text">
                                            <a href="#" onClick="reconstroi_tabela()">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </a>
                                        </span>
                                    </div>

                                    <button type="button" class="btn btn-outline-success btn-sm flex-shrink-0" onClick="incluir()">
                                        <i class="fa-solid fa-file-circle-plus"></i> Incluir Documento
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id='msgAlerta'></div>
                        <table id="example" class="table table-striped table-hover table-bordered table-sm fb-8 nowrap w-100" style="width:100%">
                            <thead class="gb-gray">
                                <tr>
                                    <th><sup>0.</sup>ID</th>
                                    <th><sup>1.</sup>Data</th>
                                    <th><sup>2.</sup>Pessoa/Colab</th>
                                    <th><sup>3.</sup>Tipo</th>
                                    <th><sup>4.</sup>Descrição</th>
                                    <th><sup>5.</sup>Arquivo</th>
                                    <th><sup>6.</sup>Extensão</th>                                    
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
        </main>

        <!--
                ---- MODAL - MOSTRAR DOCUMENTO
        -->
        <div class="modal fade modal-lg" id="modalMostraDocumento">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header fs-13">
                        <div class="col-sm-12">
                            <div class="clearfix">
                                <div class="float-start modal-title h5">
                                    <i class="fa-solid fa-file-import"></i> Informações do Documento
                                </div>
                                <div class="float-end d-flex">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body fs-13p" id='divModalVisDoc'>
                        <input type='hidden' id='_nomeArquivo'>
                        <input type='hidden' id='_tipoArquivo'>
                        <input type='hidden' id='_idPessoa'>
                        <input type='hidden' id='_origem'>
                        <div class="row">
                            <div class="col-12">
                                <label for="vis_nome" class="fs-13 ms-2 pb-1">Pessoa/Colaborador</label>
                                <div 
                                    id='vis_nome' 
                                    class="border border-success rounded-3 ms-2 text-center form-control h4"
                                    style="font-weight: bold">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label for="vis_data" class="fs-13 ms-2 pb-1">Data Anexo</label>
                                <div id='vis_data' class="border border-success rounded-3 ms-2 text-center fs-13 form-control"></div>
                            </div>
                            <div class="col-sm-6">
                                <label for="divVisTipoDoc" class="fs-13 ms-2 pb-1">Tipo do Documento</label>
                                <div id='divVisTipoDoc' class="border border-success rounded-3 text-center fs-13 form-control me-2"></div>
                            </div>
                            <div class="col-sm-2">
                                <label for="divVisID" class="fs-13 ms-2 pb-1">ID Doc</label>
                                <div id='divVisID' class="border border-success rounded-3 text-center fs-13 form-control me-2"></div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-6">
                                <label for="divVisNomeOri" class="fs-13 ms-2 pb-1">Nome Original</label>
                                <div id='divVisNomeOri' class="border border-success rounded-3 ms-2 text-center fs-13 form-control"></div>
                            </div>
                            <div class="col-sm-6">
                                <label for="divVisNomeFis" class="fs-13 ms-2 pb-1">Nome Armazenado</label>
                                <div id='divVisNomeFis' class="border border-success rounded-3 text-center fs-13 form-control"></div>
                            </div>
                        </div>
                        <div class="row mt-2 p-2">
                            <div class="col-sm-12">
                                <label for="vis_doc_descricao">Descrição do Arquivo</label>
                                <textarea name="vis_doc_descricao" id="vis_doc_descricao" disabled rows="3" class="form-control fs-13 me-2"></textarea>
                            </div>
                        </div>
                        <div class="row mt-2 p-2">
                            <div class="col-sm-12">
                                <label for="vis_tags">Tags de indexação</label>
                                <textarea name="vis_tags" id="vis_tags" disabled rows="3" class="form-control fs-13 me-2"></textarea>
                            </div>
                        </div>
                        <div class="row mt-2 p-2" id='divBotoesMostraDoc'>
                            <div class="col-sm-6">
                                <div class="fs-13 border border-secondary rounded-3 text-center fs-13 form-control" id='divInseridoPor'>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end col-sm-6">
                                <a class="btn btn-outline-success btn-sm w-100 me-1" onClick='visualizar_documento()'><i class="fa-solid fa-magnifying-glass"></i> Documento</a>
                                <a class="btn btn-outline-success btn-sm w-100 me-1" onClick='mostrar_div_ocr()'><i class="fa-solid fa-magnifying-glass"></i> OCR</a>
                                <a class="btn btn-outline-danger btn-sm w-100" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Fechar</a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body fs-13p invisivel" id='divVisOCR'>
                        <div class="row">
                            <label for="vis_ocr">Texto Extraído do Documento</label>
                            <textarea id='vis_ocr' disabled rows='15' class='form-control fs-13'></textarea>
                        </div>
                        <div class="row mt-2 p-2">
                            <div class="col-sm-6"></div>
                            <div class="d-flex justify-content-end col-sm-6">
                                <a class="btn btn-outline-danger btn-sm w-100" onClick='mostrar_div_documento()'><i class="fa-solid fa-right-left"></i> Voltar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--
                ---- MODAL - ANEXAR DOCUMENTO 
        -->
        <div class="modal fade modal-lg" id="modalIncluirDocumento">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header fs-13">
                        <div class="col-sm-12">
                            <div class="clearfix">
                                <div class="float-start modal-title h5">
                                    <i class="fa-solid fa-file-import"></i> Anexação de Documentos
                                </div>
                                <div class="float-end d-flex">
                                    <span class="me-5 fs-13"><i class="fa-solid fa-user-tag"></i> <?php echo $login; ?></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body fs-13p" id='incPrincipal'>
                        <form name='formAnexo' id='formAnexo' method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label for="pessoa" class="ms-2 pb-1">Pessoa/Colaborador (opcional)</label>
                                    <input type="text" class="form-control mb-3" id="nmPessoa" name='nmPessoa' placeholder="comece a digitar o nome">
                                    <input type="hidden" id='idPessoa' name='idPessoa' value="0">
                                </div>
                            </div>
                            <div class="row mt-2 p-2">
                                <div class="col-sm-6">
                                    <label for="divDataDoc" class="ms-2 pb-1">Data do Documento</label>
                                    <div id='divDataDoc' class="me-2">
                                        <input type='date' name='dataDoc' id='dataDoc' class="form-control text-center fs-13 ms-2 obrigatorio" value='<?php echo $hoje; ?>'>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="divTipoDoc" class="ms-2 pb-1 obrigatorio">Tipo do Doc</label>
                                    <div id='divTipoDoc' class="me-2">
                                        <?= seletor_tipo_doc("formAnexo") ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2 p-2">
                                <div class="col-sm-12">
                                    <label for="divArquivo" class="fs-13 ms-1">Arquivo para upload</label>
                                    <div id='divArquivo' class="me-2">
                                        <input type="file" name='doc_arquivo' id='doc_arquivo' class='form-control form-control-sm obrigatorio'
                                            placeholder="Selecione o documento" onChange='inc_mudou_arquivo(this)'>
                                        <input type="hidden" id='inc_paginas'>
                                    </div>
                                </div>
                                <div class="col-sm-11 fs-13 ms-2 pe-2 text-center text-success" id='listaDocArquivos'></div>
                            </div>
                            <div class="row mt-2 p-2">
                                <div class="col-sm-12">
                                    <label for="tags">Descrição do Arquivo</label>
                                    <textarea name="doc_descricao" id="doc_descricao" rows="3" class="form-control fs-13 me-2"></textarea>
                                </div>
                                <div class="col-sm-12">
                                    <label for="tags">Tags para Indexação</label>
                                    <textarea name="tags" id="tags" rows="3" class="form-control fs-13 me-2"></textarea>
                                </div>
                            </div>
                            <div class="row mt-2 p-2" id='divBotoesIncluiDoc'>
                                <div class="clearfix">

                                    <div class="float-start col-6">
                                    </div>

                                    <div class="float-end col-6">
                                        <div class="d-flex">
                                            <a class="btn btn-sm btn-outline-secondary text-center w-100 me-2" onClick='troca_inc_ocr()'>OCR <i class="fa-regular fa-hand"></i></a>
                                            <a class="btn btn-outline-danger btn-sm w-100" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                                            <button class="btn btn-outline-warning btn-sm me-2 ms-2 w-100" type='reset'><i class="fa-solid fa-recycle"></i> Reset</button>
                                            <a class="btn btn-outline-success btn-sm w-100" onClick='incluir_commit()'><i class="fa-solid fa-share"></i> Enviar</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="invisivel text-center h2" id='msgIncluiDoc'></div>
                            </div>
                        </form>
                    </div>
                    <div class="container fs-13p invisivel" id='incOCR'>
                        <div class="row mt-2 p-2">
                            <div class="col">
                                <label for="_ocr" class="fs-13pb ms-2">Texto OCR</label>
                                <textarea name="inc_ocr" id="inc_ocr" class='form-control fs-13 h-100' placeholder="Cole aqui o texto extraído do OCR"></textarea>
                            </div>
                        </div>
                        <div class="row mt-4 p-2">
                            <div class="col">
                                <a href='#' class="btn btn-outline-success w-100" onClick='troca_inc_principal()'>voltar</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!--
                ---- MODAL - EDITAR DOCUMENTO 
        -->
        <div class="modal fade modal-lg" id="modalEditarDocumento">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header fs-13">
                        <div class="col-sm-12">
                            <div class="clearfix">
                                <div class="float-start modal-title h5">
                                    <i class="fa-solid fa-file-import"></i> Editar Registro de Documentos
                                </div>
                                <div class="float-end d-flex">
                                    <span class="me-5 fs-13"><i class="fa-solid fa-user-tag"></i> <?php echo $login; ?></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal body - Campos Principais -->
                    <div class="modal-body fs-13p" id='altPrincipal'>
                        <form name='formEdit' id='formEdit' method="post" enctype="multipart/form-data">
                            <input type="hidden" id='idDoc' name='idDoc'>
                            <div class="row">
                                <div class="col-sm-12">
                                    <label for="pessoa" class="ms-2 pb-1">Pessoa/Colaborador (opcional)</label>
                                    <input type="text" class="form-control mb-3" id="nmPessoa" name='nmPessoa' placeholder="comece a digitar o nome">
                                    <input type="hidden" id='idPessoa' name='idPessoa'>
                                </div>
                            </div>                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="divDataDoc" class="ms-2 pb-1">Data do Documento</label>
                                    <div id='divDataDoc' class="me-2">
                                        <input type='date' name='ed_dataDoc' id='ed_dataDoc' class="form-control text-center fs-13 ms-2 obrigatorio">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="divTipoDoc" class="ms-2 pb-1 obrigatorio">Tipo do Doc</label>
                                    <div id='ed_divTipoDoc' class="me-2">
                                        <?= seletor_tipo_doc("formEdit") ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2 p-2">
                                <div class="col-sm-12">
                                    <label for="divArquivo" class="fs-13 ms-1">Arquivo para substituir</label>
                                    <div id='divArquivo' class="me-2">
                                        <input type="file" name='doc_arquivo' id='doc_arquivo' class='form-control form-control-sm obrigatorio' placeholder="Selecione o documento" onChange='alt_mudou_arquivo(this)'>
                                    </div>
                                    <span id='alt_dsArquivo' class="fs-13 text-primary ms-2"></span>
                                </div>
                                <div class="col-sm-11 fs-13 ms-2 pe-2 text-center text-success" id='listaDocArquivos'></div>
                            </div>
                            <div class="row p-2">
                                <div class="col-sm-12">
                                    <label for="tags">Descrição do Arquivo</label>
                                    <textarea name="doc_descricao" id="doc_descricao" rows="3" class="form-control fs-13 me-2"></textarea>
                                </div>
                                <div class="col-sm-12">
                                    <label for="tags">Tags de Indexação</label>
                                    <textarea name="tags" id="tags" rows="3" class="form-control fs-13 me-2"></textarea>
                                </div>
                            </div>
                            <div class="row mt-2 p-2" id='divBotoesEditaDoc'>

                                <div class="float-start col-5"></div>

                                <div class="float-end col-7">
                                    <div class="d-flex">
                                        <a class="btn btn-sm btn-outline-secondary text-center w-100 me-2" onClick='troca_alt_ocr()'>OCR <i class="fa-regular fa-hand"></i></a>
                                        <a class="btn btn-outline-danger btn-sm w-100" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                                        <button class="btn btn-outline-warning btn-sm me-2 ms-2 w-100" type='reset'><i class="fa-solid fa-recycle"></i> Reset</button>
                                        <a class="btn btn-outline-success btn-sm w-100" onClick='edit_commit()'><i class="fa-solid fa-share"></i> Enviar</a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="invisivel text-center h2" id='msgaAltDoc'></div>
                            </div>
                        </form>
                    </div>
                    <div class="container fs-13p invisivel" id='altOCR'>
                        <div class="row mt-2 p-2">
                            <div class="col">
                                <label for="_ocr" class="fs-13pb ms-2">Texto OCR</label>
                                <textarea name="alt_ocr" id="alt_ocr" class='form-control fs-13 h-100' placeholder="Cole aqui o texto extraído do OCR"></textarea>
                            </div>
                        </div>
                        <div class="row mt-4 p-2">
                            <div class="col">
                                <a href='#' class="btn btn-outline-success w-100" onClick='troca_alt_principal("alt")'>voltar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--
                ---- MODAL - EMAIL
            -->
        <div class="modal fade modal-lg" id="modalEnviarEmail">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header fs-13">
                        <div class="col-sm-12">
                            <div class="clearfix">
                                <div class="float-start modal-title h5">
                                    <i class="fa-regular fa-envelope"></i> Enviar documento por e-Mail
                                </div>
                                <div class="float-end d-flex">
                                    <span class="me-5 fs-13"><i class="fa-solid fa-user-tag"></i> <?php echo $login; ?></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body fs-13p">
                        <form name='formEmail' id='formEmail' method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-sm-1">Para</div>
                                <div class="col-sm-11">
                                    <input type="email" name='para' id='para' class='form-control form-control-sm' placeholder="e-mail" onkeyup="carregar_emails(this.value)">
                                    <input type='hidden' name='nome' id='nome'>
                                    <input type='hidden' name='_idDoc' id='_idDoc'>
                                    <input type='hidden' name='idPessoa' id='idPessoa'>
                                    <span id="resultado_pesquisa_emails"></span>
                                </div>

                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-1">CC</div>
                                <div class="col-sm-11"><input type="email" name='cc' id='cc' class='form-control form-control-sm' placeholder="com cópia para...."></div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-1">CCO</div>
                                <div class="col-sm-11"><input type="email" name='cco' id='cco' class='form-control form-control-sm' placeholder="com cópia oculta para...."></div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-1">Título</div>
                                <div class="col-sm-11"><input type="text" name='titulo' id='titulo' class='form-control form-control-sm' placeholder="Título do e-Mail..."></div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-1">Anexo</div>
                                <div class="col-sm-11">
                                    <input type="text" name='arquivo_original' id='arquivo_original' disabled class='form-control form-control-sm'>
                                    <input type='hidden' name='arquivo' id='arquivo'>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="container fs-13p">
                                    <textarea name="corpoEmail" id="corpoEmail" class="form-control fs-13p" rows='5' placeholder="Sua mensagem aqui..."></textarea>
                                </div>
                            </div>

                            <div class="row mt-2 p-2" id='divBotoesEmail'>
                                <div class="col-sm-6"></div>
                                <div class="d-flex justify-content-end col-sm-6">
                                    <a class="btn btn-outline-danger btn-sm w-100" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                                    <button class="btn btn-outline-warning btn-sm me-2 ms-2 w-100" type='reset'><i class="fa-solid fa-recycle"></i> Reset</button>
                                    <a type="button" class="btn btn-outline-success btn-sm rounded w-100" onclick="envia_email()"><i class="fa-solid fa-share"></i> Enviar</a>
                                </div>
                            </div>

                            <div id="listaArquivos" class="text-center fs-13"></div>
                            <div class="col-sm-12 text-center invisivel" id='divMensagemEml'>
                                <h3>Aguarde...</h3>
                            </div>
                        </form>
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
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_docs.js"></script>
    <script src="js/f_ocr.js"></script>
</body>

</html>
<?php
//
//- ROTINAS AUXILIARES
//

function seletor_documentos()
{
    global $conn;
    $sql = "SELECT D.idTipoDoc, T.nome as nmTipoDoc, count( idDoc ) as qtd
                FROM rh_documentos as D
                INNER JOIN rh_docs_tipo as T on T.idTipoDoc = D.idTipoDoc
                group by D.idTipodoc
                order by nmTipoDoc";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $select = "<select class='form-select form-select-sm fs-13 w-100' id='idTipo' name='idTipo' onChange='reconstroi_tabela()'>";

    $select .= "<option value='0'>Tipo doc</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $select .= "<option value='$idTipoDoc'>$nmTipoDoc ($qtd)</option>";
    }
    $select .= "</select>";
    return $select;
}

function seletor_tipo_doc( $formulario )
{
    global $conn;
    $sql = "SELECT *,
	        (select count(1) from rh_documentos D where D.idTipoDoc = T.idTipoDoc  ) as qtd
            FROM rh_docs_tipo T
            ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $html  = "<div class='input-group input-group-sm'>";
    $html .= "<select class='form-select fs-13' id='idTipo' name='idTipo'>";
    $html .= "<option value='0'>Selecione...</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $html .= "<option value='$idTipoDoc'>$nome ($qtd)</option>";
    }

    $html .= "</select>";
    $html .= "<button class='btn btn-outline-secondary' type='button' onclick='adicionarNovoTipo(`$formulario`)'>
                <i class='fa fa-plus'></i>
              </button>";
    $html .= "</div>";

    return $html;
}

function seletor_pessoas()
{
    global $conn;
    $sql = "SELECT ifnull(P.nome, 'Sem Proprietário') as nome, 
                ifnull(P.idPessoa, -1) as idPessoa, 
                count(D.idPessoa) as qtd
            FROM rh_documentos D
            LEFT OUTER JOIN rh_pessoas P on P.idPessoa = D.idPessoa
            GROUP BY P.nome, P.idPessoa
            ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $html = "<select class='form-select fs-13' id='x_idPessoa' name='x_idPessoa' onChange='reconstroi_tabela()'>";
    $html .= "<option value='0'>Pessoa/Colaborador...</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $html .= "<option value='$idPessoa'>$nome ($qtd)</option>";
    }

    $html .= "</select>";

    return $html;
}

$conn = null;
