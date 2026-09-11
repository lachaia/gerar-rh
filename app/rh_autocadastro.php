<?PHP
//
//- rh_autocadastro.php | Envia formulário de autocadastro para novo colaborador
// (C)haia, 23/10/2025;
//

session_start();

include_once "includes/conexao_gerar.php";

$idModulo = 21; //-Autocadastro

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-image: url('imagens/abstrato_tzuru_fundo.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: "Segoe UI", sans-serif;
        }

        .form-control:focus {
            background-color: #1e1e1e;
            color: #fff;
            border-color: #0dcaf0;
            box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
        }

        .btn:hover {
            background-color: #0dcaf0;
            color: #000;
            transition: all 0.3s ease;
        }

        .card {
            backdrop-filter: blur(8px);
            background: rgba(30, 30, 30, 0.85);
        }

        #dash {
            background-image: url('imagens/wall1.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .sb-sidenav {
            border-right: 1px solid rgba(0, 0, 0, 0.3);
            box-shadow: 2px 0 4px rgba(111, 111, 111, 0.4);
        }

        .cartao_dash {
            height: 640px;
            background-color: rgba(0, 0, 0, 0.38);
            border-right: 2px solid rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background-color: #4c00ffff;
            color: #fff;
        }

        .modal-body,
        .modal-footer {
            background-color: gainsboro;
            color: #202020ff;
        }

        .btn-enviar {
            background-color: #45a29e;
            border: none;
            color: #fff;
            font-weight: 600;
        }

        .btn-enviar:hover {
            background-color: #66fcf1;
            color: #000;
        }

        .viscampo {
            border: 1px solid #ccc;
            /* Borda suave e discreta */
            border-radius: 5px;
            /* Cantos levemente arredondados */
            display: block;
            /* Ocupa a largura total disponível */
            width: 100%;
            /* Garante alinhamento correto */
            padding: 4px 10px;
            /* Espaçamento interno para melhor visualização */
            background-color: #cecbcbff;
            /* Fundo levemente acinzentado para destacar */
            height: 40px;
        }

        form label {
            font-weight: 500;
            color: #2a2052ff;
            /* opcional, combina bem com tema escuro */
            margin-bottom: 4px;
            display: block;
            /* deixa o label em linha separada do input */
        }

        .botao {
            min-width: 200px;
        }

        .status{
            min-height: 22px;
        }
    </style>

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
                        <div class="col-md-8">
                            <h1 class="fw-bold text-white">
                                <i class="fa-solid fa-user-tag"></i> Autocadastro
                            </h1>
                            <p class="text-muted mb-0">Formulários de autopreenchimento pelos novos colaboradores</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 cartao_dash">

                            <div class="container mt-4 d-flex justify-content-center">
                                <div class="card bg-dark text-light shadow-lg p-4 rounded-4" style="width: 100%; max-width: 500px;">
                                    <h5 class="fw-bold text-center mb-4">Formulário de Envio</h5>
                                    <div>
                                        <div class="mb-3">
                                            <label for="nome" class="form-label text-secondary">Nome completo</label>
                                            <input type="text" class="form-control bg-dark text-light border-secondary" id="nome" name="nome" placeholder="Digite o nome do colaborador" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label text-secondary">E-mail</label>
                                            <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" placeholder="Digite o e-mail para envio" required>
                                        </div>

                                        <div class="d-grid mt-4">
                                            <button type="button" class="btn btn-outline-light rounded-pill py-2 fw-semibold" onclick='enviar()'>
                                                Enviar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-center m-4">
                                        O link do formulário para autocadastro será enviado para o e-mail acima, e terá validade de 48 horas.
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-6 cartao_dash">
                            <div class="h5 text-center" id="msgAlerta"></div>
                            <div class="container mt-4">
                                <h5 class="fw-bold text-white text-center m-4">Formulários Enviados</h5>
                            </div>
                            <table class="table table-dark table-hover table-striped nowrap w-100" id='tabela'>
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Nome</th>
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
            </main>

            <!-- The Modal -->
            <div class="modal fade" id="modalAprovar">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">Formulário de Autocadastro</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <!-------------------------------------------------------------------------------------- -->
                            <div class="container">
                                <div class="h5 text-center" id='msgImporta'></div>
                                <form id="meuForm" onchange="toggleBotoes()">
                                    <input type="hidden" id='id' name="id">
                                    <h2><i class="fa-solid fa-id-card-clip me-2 mt-3"></i> Dados Pessoais</h2>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label>Nome Completo</label>
                                            <input type="text" name="nome_completo" id='nome_completo' class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Nome Social</label>
                                            <input type="text" name="nome_social" id='nome_social' class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">
                                            <label>Data de Nascimento</label>
                                            <input type="date" name="data_nascimento" id='data_nascimento' class="form-control" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Sexo</label>
                                            <select name="sexo" id='sexo' class="form-select" required>
                                                <option value="" selected disabled>Selecione</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Feminino</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Estado Civil</label>
                                            <?= f_estadoCivil(); ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Nacionalidade</label>
                                            <input type="text" name="nacionalidade" id='nacionalidade' class="form-control" required value="Brasileira">
                                        </div>
                                    </div>
                                    <div class="row mt-2 mb-4">
                                        <div class="col-md-3">
                                            <label>Etnia</label>
                                            <?= f_etnias(); ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Camiseta (tamanho)</label>
                                            <input type="text" name="camiseta" id='camiseta' class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Nome da Mãe</label>
                                            <input type="text" name="mae" id='mae' class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row mt-2 mb-4">
                                        <div class="col-md-6">
                                            <label>Celular pessoal (whatsapp)</label>
                                            <input type="text" name="telefone" id='telefone' class="form-control" required placeholder="Informe o número...">
                                        </div>
                                        <div class="col-md-6">
                                            <label>e-Mail pessoal</label>
                                            <input type="text" name="email_pessoal" id='email_pessoal' class="form-control" required placeholder="seu melhor e-mail...">
                                        </div>
                                    </div>
                                    <h2><i class="fa-solid fa-passport me-2 mt-3"></i> Documentos</h2>
                                    <div class="row mb-2">
                                        <div class="col-md-4">
                                            <label>CPF</label>
                                            <input type="text" name="cpf" id='cpf' id='cpf' class="form-control" onchange='testar_cpf(this)'>
                                        </div>
                                        <div class="col-md-4">
                                            <label>RG e Órgão Emissor</label>
                                            <input type="text" name="rg" id='rg' class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>PIS/PASEP</label>
                                            <input type="text" name="pis" id='pis' class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4 mt-2">
                                            <label>CTPS (nº / série)</label>
                                            <input type="text" name="ctps" id='ctps' class="form-control">
                                        </div>
                                        <div class="col-md-4 mt-2">
                                            <label>Título de Eleitor</label>
                                            <input type="text" name="titulo" id='titulo' class="form-control">
                                        </div>
                                        <div class="col-md-4 mt-2">
                                            <label>Grau de Instrução</label>
                                            <?= seletor_grau_de_instrucao(); ?>
                                        </div>
                                    </div>

                                    <h2><i class="fa-solid fa-car me-2 mt-3"></i> CNH (opcional)</h2>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label>Número</label>
                                            <input type="text" name="cnh" id="cnh" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Categoria</label>
                                            <input type="text" name="categoria_cnh" id="categoria_cnh" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Vencimento</label>
                                            <input type="date" name="vencimento_cnh" id="vencimento_cnh" class="form-control">
                                        </div>
                                    </div>

                                    <h2><i class="fa-solid fa-house me-2 mt-3"></i> Endereço Residencial</h2>
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label>CEP</label>
                                            <input type="text" name="cep" id="cep" class="form-control">
                                        </div>
                                        <div class="col-md-7">
                                            <label>Logradouro</label>
                                            <input type="text" name="logradouro" id="logradouro" class="form-control">
                                        </div>

                                        <div class="col-md-2">
                                            <label>Número</label>
                                            <input type="text" name="numero" id="numero" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label>Complemento</label>
                                            <input type="text" name="complemento" id="complemento" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Bairro</label>
                                            <input type="text" name="bairro" id="bairro" class="form-control">
                                        </div>
                                        <div class="col-md-5">
                                            <label>Cidade</label>
                                            <input type="text" name="cidade" id="cidade" class="form-control">
                                        </div>
                                        <div class="col-md-1">
                                            <label>UF</label>
                                            <input type="text" name="uf" id="uf" class="form-control">
                                        </div>
                                    </div>

                                    <h2><i class="fa-solid fa-image me-2 mt-3"></i> Uploads</h2>
                                    <div class="row mb-2">
                                        <div class="col-md-6 mt-2">
                                            <label>Foto</label>
                                            <p class="viscampo" id='foto'></p>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label>Comprovante de Endereço</label>
                                            <p id='comprovante_endereco' class="viscampo"></p>
                                        </div>
                                    </div>
                                    <p class='text-secondary m-4' id="token">Token: 123</p>
                                </form>
                            </div>
                            <!-------------------------------------------------------------------------------------- -->
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer d-flex gap-2">
                            <button type="button" id="btnReset" class="btn btn-warning botao d-none text-dark" onclick='reset()'><i class="fa-solid fa-repeat"></i>Reset</button>
                            <button type="button" id="btnSalvar" class="btn btn-primary botao d-none" onclick='salvar()'><i class="fa-solid fa-floppy-disk"></i> Salvar Alterações</button>
                            <button type="button" id="btnImporta" class="btn btn-warning botao text-dark" onclick='importar_commit()'><i class="fa-solid fa-download"></i> Importar dados</button>
                            <button type="button" id="btnFechar" class="btn btn-danger  botao" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Close</button>
                        </div>

                    </div>
                </div>
            </div>


            <!-- 
                    Aqui TERMINA o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>

    <script data-cfasync="false" src="js/scripts.js"></script>
    <script>
        const modalAprovar = new bootstrap.Modal(document.getElementById("modalAprovar"));

        $(document).ready(function() {

            document.getElementById('nome').focus();

            dataTable = new DataTable('#tabela', {
                "processing": true,
                "serverSide": false,
                "order": [1, "asc"],
                "scrollX": true,
                "pageLength": 10, // Define a quantidade de linhas
                "ajax": {
                    "url": "includes/rh_autocadastro_aj1.php",
                    "type": "POST"
                },
                "columnDefs": [{
                    "targets": [0, 2, 3],
                    "className": "text-center"
                }],
                language: {
                    url: 'includes/pt-BR.json',
                },
            });

        });

        function enviar() {
            let nome = document.getElementById('nome').value;
            let email = document.getElementById('email').value;
            $.post('includes/rh_autocadastro_aj2.php', {
                'nome': nome,
                'email': email
            }, function(retorno) {
                alert(retorno);
                location.reload();
            });
        }

        function f_excluir(id) {
            if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
                //$("#msgAlerta").show();
                $.post("includes/rh_autocadastro_aj3.php", {
                    id: id
                }, function(response) {
                    const dados = JSON.parse(response);
                    //alert(response);
                    $("#msgAlerta").html(dados.msg);
                    //
                    setTimeout(function() {
                        document.location.reload(true);
                    }, 3000);

                }).fail(function() {
                    alert("Erro ao excluir o registro.");
                });
            }
        }

        function f_importar(id) {
            $.post("includes/rh_autocadastro_aj4.php", {
                id: id
            }, function(response) {
                const dados = JSON.parse(response);
                modalAprovar.show();
                document.getElementById('id').value = dados.id;
                document.getElementById('nome_completo').value = dados.nome;
                document.getElementById('nome_social').value = dados.nomeSocial;
                document.getElementById('data_nascimento').value = dados.dtNascimento;
                document.getElementById('sexo').value = dados.sexo;
                document.getElementById('email_pessoal').value = dados.email;
                document.getElementById('camiseta').value = dados.camiseta;
                document.getElementById('telefone').value = dados.telefone;
                document.getElementById('cep').value = dados.cep;
                document.getElementById('logradouro').value = dados.logradouro;
                document.getElementById('numero').value = dados.numero;
                document.getElementById('complemento').value = dados.complemento;
                document.getElementById('bairro').value = dados.bairro;
                document.getElementById('cidade').value = dados.cidade;
                document.getElementById('uf').value = dados.uf;
                document.getElementById('idEtnia').value = dados.idEtnia;
                document.getElementById('idGrauEscola').value = dados.idGrauEscola;
                document.getElementById('idEstadoCivil').value = dados.idEstadoCivil;
                document.getElementById('mae').value = dados.nome_mae;
                document.getElementById('cnh').value = dados.cnh;
                document.getElementById('categoria_cnh').value = dados.cnh_categoria;
                document.getElementById('vencimento_cnh').value = dados.cnh_vencimento;
                document.getElementById('cpf').value = dados.cpf;
                document.getElementById('rg').value = dados.rg;
                document.getElementById('pis').value = dados.pis;
                document.getElementById('ctps').value = dados.ctps;
                document.getElementById('titulo').value = dados.titulo_eleitor;
                document.getElementById('foto').innerHTML = dados.arquivo_foto;
                document.getElementById('comprovante_endereco').innerHTML = dados.arquivo_endereco;
                document.getElementById('token').innerHTML = "Token: " + dados.token;
            })
        }

        function reset() {
            let id = document.getElementById('id').value;
            $.post("includes/rh_autocadastro_aj5.php", {
                id: id
            }, function(response) {
                const dados = JSON.parse(response);
                document.getElementById('id').value = dados.id;
                document.getElementById('nome_completo').value = dados.nome;
                document.getElementById('nome_social').value = dados.nomeSocial;
                document.getElementById('data_nascimento').value = dados.dtNascimento;
                document.getElementById('sexo').value = dados.sexo;
                document.getElementById('email_pessoal').value = dados.email;
                document.getElementById('camiseta').value = dados.camiseta;
                document.getElementById('telefone').value = dados.telefone;
                document.getElementById('cep').value = dados.cep;
                document.getElementById('logradouro').value = dados.logradouro;
                document.getElementById('numero').value = dados.numero;
                document.getElementById('complemento').value = dados.complemento;
                document.getElementById('bairro').value = dados.bairro;
                document.getElementById('cidade').value = dados.cidade;
                document.getElementById('uf').value = dados.uf;
                document.getElementById('idEtnia').value = dados.idEtnia;
                document.getElementById('idGrauEscola').value = dados.idGrauEscola;
                document.getElementById('idEstadoCivil').value = dados.idEstadoCivil;
                document.getElementById('mae').value = dados.nome_mae;
                document.getElementById('cnh').value = dados.cnh;
                document.getElementById('categoria_cnh').value = dados.cnh_categoria;
                document.getElementById('vencimento_cnh').value = dados.cnh_vencimento;
                document.getElementById('cpf').value = dados.cpf;
                document.getElementById('rg').value = dados.rg;
                document.getElementById('pis').value = dados.pis;
                document.getElementById('ctps').value = dados.ctps;
                document.getElementById('titulo').value = dados.titulo_eleitor;
                document.getElementById('foto').innerHTML = dados.arquivo_foto;
                document.getElementById('comprovante_endereco').innerHTML = dados.arquivo_endereco;
                document.getElementById('token').innerHTML = "Token: " + dados.token;
                //
                document.getElementById('btnReset').classList.add('d-none');
                document.getElementById('btnSalvar').classList.add('d-none');
                document.getElementById('btnImporta').classList.remove('d-none');
            })
        }

        async function salvar() {
            const mensagem = $("#msgImporta");
            const formulario = document.getElementById('meuForm');
            const formData = new FormData(formulario);
            mensagem.html("enviando...");
            try {
                const resposta = await fetch('includes/rh_autocadastro_aj6.php', {
                    method: 'POST',
                    body: formData
                });

                const texto = await resposta.text();
                let d = JSON.parse(texto);
                mensagem.html( d.msg );
            } catch (erro) {
                mensagem.html('❌ Ocorreu um erro ao salvar os dados.');
            }
            //
            document.getElementById('btnReset').classList.add('d-none');
            document.getElementById('btnSalvar').classList.add('d-none');
            document.getElementById('btnImporta').classList.remove('d-none');
            //
            setTimeout(function() {
                mensagem.html('');
            }, 3000);
        }

        function importar_commit() {
            let id = document.getElementById('id').value;
            $.post("includes/rh_autocadastro_aj7.php", {
                id: id
            }, function(response) {
                const dados = JSON.parse(response);
                if( dados.status == true){
                    alert( "Dados transferidos com sucesso." );
                } else{
                    alert( "Ocorreu um erro ao transferir os dados." );
                }
                document.location.reload();
            })
        }

        function toggleBotoes() {
            document.getElementById('btnReset').classList.remove('d-none');
            document.getElementById('btnSalvar').classList.remove('d-none');
            document.getElementById('btnImporta').classList.add('d-none');
        }
    </script>
</body>

</html>
<?php
//- FUNÇÕES AUXILIARES
//

function seletor_grau_de_instrucao($_idGrau = 0)
{
    global $conn;
    $sql = "SELECT *
                FROM rh_graus_instrucao
                WHERE ativo = 1
                ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13 obrigatorio' id='idGrauEscola' name='idGrauEscola' required>";
    if ($_idGrau == 0) $select .= "<option value='0' selected disabled>Selecione um nivel</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        if ($_idGrau == $id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$id' $selected>$descricao</option>";
    }
    $select .= "</select>";
    echo $select;
}

function f_estadoCivil($_id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM rh_estadoCivil;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = "<select class='form-select fs-13' id='idEstadoCivil' name='idEstadoCivil' required>";
    if ($_id == 0) $select .= "<option value='0' selected disabled>Selecione um tipo</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $idEstadoCivil) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idEstadoCivil' $selected>$categoria</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

function f_etnias($_id = 0)
{
    global $conn;
    //
    $sql = "SELECT * FROM RH.rh_etnias;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = "<select class='form-select fs-13' id='idEtnia' name='idEtnia' required>";
    if ($_id == 0) $select .= "<option value='0' selected disabled>Selecione...</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $idEtnia) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idEtnia' $selected>$categoria</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}
