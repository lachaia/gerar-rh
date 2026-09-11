<?PHP
//
// autocadastro.php | Link para novo Colaborador fazer o autocadastro
// (C)haia, 22/10/2025
//

session_start();

$idModulo = 21; //-Autocadastro

include_once "includes/conexao_gerar.php";

$parametros = filter_input_array(INPUT_GET, FILTER_DEFAULT);
if ($parametros) extract($parametros);

if (empty($token)) $token = "não enviado";

//
// 🔒 Verifica se o token existe e se ainda está dentro do prazo
//
$sql = "SELECT * 
        FROM rh_autocadastro_ctr 
        WHERE token = :token";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':token', $token, PDO::PARAM_STR);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($row)) {
    echo "<h1 style='color:red;text-align:center;margin-top:40px;'>Link expirado ou inválido.</h1>";
    exit;
}

// Verifica se já passou da data/hora de expiração
$dataAtual = new DateTime();
$dataExpira = new DateTime($row['expira']);

if ($dataAtual > $dataExpira) {
    echo "<h1 style='color:red;text-align:center;margin-top:40px;'>Este link expirou em " . $dataExpira->format('d/m/Y H:i') . ".</h1>";
    exit;
}
if (!empty($data_resposta)) {
    $dt = new DateTime($data_resposta); // converte string em objeto DateTime
    echo "<h1 style='color:red;text-align:center;margin-top:40px;'>
            Este link já foi utilizado em " . $dt->format('d/m/Y H:i') . ".
          </h1>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados Iniciais - Formulário de Contratação</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0b0c10, #1f2833);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        header {
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(6px);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        header img {
            height: 45px;
            margin-right: 15px;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        h2 {
            color: #45a29e;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
            margin-bottom: 25px;
            font-weight: 600; 
        }

        label {
            font-weight: 500;
        }

        .form-control,
        .form-select {
            background-color: rgba(255, 255, 255, 0.08);
            border: none;
            color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid #66fcf1;
            box-shadow: none;
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

        #logomarca {
            height: 50px;
            margin-right: 15px;
        }

        option {
            color: white;
            background-color: #828595ff;
        }

        h2 {
            background-color: #0b4e753d;
            padding: 5px;
        }

        .cpf-valido {
            border: 2px solid #28a745;
            color: #28a745;
        }

        .is-invalid {
            border: 2px solid #dc3545 !important;
            background-color: #2a0000 !important;
        }
    </style>
</head>

<body>

    <header>
        <img src="imagens/logo.png" alt="Logomarca" id='logomarca'>
        <div>
            <h3 class="mb-0">Dados Iniciais</h3>
            <small>Formulário de dados para Contratação</small>
        </div>
    </header>

    <div class="container">
        <form id="formAutocadastro" class="form-container mt-4" action="autocadastro_aj.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="token" name="token" value="<?= $token ?>">
            <h2><i class="fa-solid fa-id-card-clip me-2 mt-3"></i> Dados Pessoais</h2>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label>Nome Completo</label>
                    <input type="text" name="nome_completo" id='nome_completo' class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Nome Social</label>
                    <input type="text" name="nome_social" class="form-control">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label>Data de Nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Sexo</label>
                    <select name="sexo" class="form-select" required>
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
                    <input type="text" name="nacionalidade" class="form-control" required value="Brasileira">
                </div>
            </div>
            <div class="row mt-2 mb-4">
                <div class="col-md-3">
                    <label>Etnia</label>
                    <?= f_etnias(); ?>
                </div>
                <div class="col-md-3">
                    <label>Camiseta (tamanho)</label>
                    <input type="text" name="camiseta" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Nome da Mãe</label>
                    <input type="text" name="mae" class="form-control" required>
                </div>
            </div>
            <div class="row mt-2 mb-4">
                <div class="col-md-6">
                    <label>Celular pessoal (whatsapp)</label>
                    <input type="text" name="telefone" class="form-control" required placeholder="Informe o número...">
                </div>
                <div class="col-md-6">
                    <label>e-Mail pessoal</label>
                    <input type="text" name="email" class="form-control" required placeholder="seu melhor e-mail...">
                </div>
            </div>
            <h2><i class="fa-solid fa-passport me-2 mt-3"></i> Documentos</h2>
            <div class="row mb-2">
                <div class="col-md-4">
                    <label>CPF</label>
                    <input type="text" name="cpf" id='cpf' class="form-control" onchange='testar_cpf(this)'>
                </div>
                <div class="col-md-4">
                    <label>RG e Órgão Emissor</label>
                    <input type="text" name="rg" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>PIS/PASEP</label>
                    <input type="text" name="pis" class="form-control">
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4 mt-2">
                    <label>CTPS (nº / série)</label>
                    <input type="text" name="ctps" class="form-control">
                </div>
                <div class="col-md-4 mt-2">
                    <label>Título de Eleitor</label>
                    <input type="text" name="titulo" class="form-control">
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
                    <input type="text" name="cnh" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Categoria</label>
                    <input type="text" name="categoria_cnh" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Vencimento</label>
                    <input type="date" name="vencimento_cnh" class="form-control">
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
                    <input type="text" name="numero" class="form-control">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label>Complemento</label>
                    <input type="text" name="complemento" class="form-control">
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
                    <input type="text" name="uf" id="uf" class="form-control" maxlength="2">
                </div>
            </div>

            <h2><i class="fa-solid fa-image me-2 mt-3"></i> Uploads</h2>
            <div class="row mb-2">
                <div class="col-md-6 mt-2">
                    <label>Foto</label>
                    <input type="file" name="foto" id='foto' class="form-control" accept="image/*">
                </div>
                <div class="col-md-6 mt-2">
                    <label>Comprovante de Endereço</label>
                    <input type="file" name="comprovante_endereco" id='comprovante_endereco' class="form-control" accept=".pdf,.jpg,.png">
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-enviar px-5 py-2 w-100 fs-5" onclick="f_enviar_dados()">
                    <i class="fa-solid fa-paper-plane me-2"></i>Enviar Dados
                </button>
            </div>
            <p class='text-secondary'>Token: <?php echo $token; ?></p>
        </form>
    </div>

    <script>
        // Autopreenchimento de endereço via CEP
        $('#cep').on('blur', function() {
            var cep = $(this).val().replace(/\D/g, '');
            if (cep.length === 8) {
                $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                    if (!('erro' in data)) {
                        $('#logradouro').val(data.logradouro);
                        $('#bairro').val(data.bairro);
                        $('#cidade').val(data.localidade);
                        $('#uf').val(data.uf);
                    }
                });
            }
        });

        $(document).ready(function() {
            document.getElementById('nome_completo').focus();
        });

        function testar_cpf(o) {
            if (validarCPF(o.value)) {
                o.classList.add('cpf-valido');
                return true;
            } else {
                alert('CPF inválido!');
                o.value = '';
                o.focus();
                return false;
            }
        }
    </script>
    <script>
        async function f_enviar_dados() {
            const form = document.querySelector('form.form-container');
            const formData = new FormData(form);

            // Campos obrigatórios (exceto CNH)
            const obrigatorios = [
                'nome_completo', 'data_nascimento', 'sexo', 'idEstadoCivil', 'nacionalidade',
                'idEtnia', 'camiseta', 'mae', 'cpf', 'rg', 'pis', 'ctps', 'titulo',
                'idGrauEscola', 'cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado',
                'foto', 'comprovante_endereco'
            ];

            let faltando = [];

            obrigatorios.forEach(campo => {
                const el = form.querySelector(`[name="${campo}"]`);
                if (!el) return;

                let valor = '';

                if (el.tagName === 'SELECT') {
                    valor = el.value;
                    if (!valor || valor === "0") valor = ""; // considera "0" como vazio
                } else if (el.type === 'file') {
                    // Para arquivos, verificamos se algum arquivo foi selecionado
                    valor = el.files.length > 0 ? "ok" : "";
                } else if (el.type === 'checkbox' || el.type === 'radio') {
                    valor = el.checked ? el.value : '';
                } else {
                    valor = el.value.trim();
                }

                if (!valor) {
                    el.classList.add('is-invalid');
                    faltando.push(campo);
                } else {
                    el.classList.remove('is-invalid');
                }
            });


            if (faltando.length > 0) {
                alert("⚠️ Por favor, preencha todos os campos obrigatórios.");
                return;
            }

            try {
                const resposta = await fetch('autocadastro_aj.php', {
                    method: 'POST',
                    body: formData
                });

                const texto = await resposta.text();
                //console.log("🔹 Resposta do servidor:", texto);

                //alert("✅ Dados enviados com sucesso!");
                //form.reset();
                window.location.href = 'autocadastro_ob.php';

            } catch (erro) {
                console.error("❌ Erro no envio:", erro);
                alert("❌ Ocorreu um erro ao enviar os dados. Tente novamente.");
            }
        }
    </script>

    <script src="js/cpf.js"></script>
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
