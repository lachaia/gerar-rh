<?PHP
//
// cadastrar_senha.php | Tela para Auto-Cadastro de Usuário Colaborador no Sistema de RH
// (C)haia, 07/05/2025, (RH)

session_start();

include_once("conexao_gerar.php");

if (isset($_GET['id']) && isset($_GET['token'])) {
    $id = $_GET['id'];
    $token = $_GET['token'];
} else {
    die("<h1>Faltou parâmetros</h1>");
}

if (!ctype_xdigit($token) || strlen($token) !== 64) {
    die("<h1>Token inválido ou ausente</h1>");
}

// Valida o token: existe, é do tipo "solicitação de cadastro", não expirou (60 min) e não foi usado.
$sqlTok = "SELECT idToken FROM rh_token
           WHERE token = :token AND tipo = 2 AND data_reset IS NULL
           AND data_solicitacao >= (NOW() - INTERVAL 60 MINUTE)";
$stmtTok = $conn->prepare($sqlTok);
$stmtTok->bindParam(':token', $token, PDO::PARAM_STR);
$stmtTok->execute();
if (!$stmtTok->fetch(PDO::FETCH_ASSOC)) {
    die("<h1>Token inválido, expirado ou já utilizado</h1>");
}

$sql = "SELECT *
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
        where idColab = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

extract($dados);

$cpf_formatado = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf);

$nome = trim($nome); // exemplo: "José de Toledo"
$partes = explode(' ', $nome);
$primeiro = removerAcentos($partes[0]);
$ultimo = removerAcentos(end($partes));
$login = $primeiro . '.' . $ultimo;


?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar-Formulário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <style>
        .full-background {
            background-image: url('../imagens/gato.jpg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100% 100%;
            /* distorce a imagem para ocupar tudo */
            height: 100vh;
            margin: 0;
        }

        #logomarca {
            z-index: 100;
        }

        .ver {
            background-color: #DCDCDC;
        }

        .extra-margin-top {
            margin-top: 12rem;
            /* ou qualquer valor maior que 3rem */
        }
    </style>
</head>

<body class="bg-dark full-background text-white">

    <main>
        <form id='formulario' class="mt-5">

            <input type="hidden" name='idColab' id='idColab' value='<?= $id ?>'>
            <input type="hidden" name='token' id='token' value='<?= $token ?>'>

            <div class="container extra-margin-top border-1 col-sm-6 offset-sm-3">

                <img src="../imagens/logo_resized.png" alt="" id="logomarca" width="100" height="90" class='position-absolute ms-2'>
                <div class="h4 text-center">GERAR - FORMULÁRIO DE CADASTRO DE USUÁRIOS - RH</div>
                <div class="text-end" id='alerta'></div>
                <div class="card border border-success rounded-3 position-relative">
                    <div class="card-header h4 text-center">IDENTIFICAÇÃO</div>
                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-sm-4">
                                <label for="_emp_cnpj" class="ms-2 pb-1">CPF</label>
                                <div id='cpf' class="me-2">
                                    <input type='text' name='cpf' id='cpf' maxlength='20' class="form-control fs-16 text-center ver" value='<?= $cpf_formatado; ?>' readonly>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <label for="_emp_razao" class="ms-2 pb-1">Nome do Colaborador</label>
                                <div id='_emp_razao' class="me-2">
                                    <input type='text' name='nome' id='nome' class="form-control fs-16 ver" value='<?= $nome; ?>' readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-sm-4">
                                <label for="login" class="ms-2 pb-1">Login</label>
                                <div class="me-2">
                                    <input type='text' name='login' id='login' class="form-control fs-16 text-center" placeholder="Nome de Fantasia" value='<?php echo $login; ?>'>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="senha1" class="ms-2 pb-1">Senha</label>
                                <div class="input-group me-2">
                                    <input type="password" name="senha1" id="senha1" class="form-control fs-16 text-center" placeholder="Informe a senha">
                                    <span class="input-group-text" style="cursor:pointer">
                                        <i class="fa-solid fa-eye" onclick="toggleSenha('senha1', this)"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="senha2" class="ms-2 pb-1">Confirme a Senha</label>
                                <div class="input-group me-2">
                                    <input type="password" name="senha2" id="senha2" class="form-control fs-16 text-center" placeholder="confirme a senha">
                                    <span class="input-group-text" onclick="toggleSenha('senha2', this)" style="cursor:pointer">
                                        <i class="fa-solid fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4" id='botoes'>
                            <div class="col-sm-12 d-flex">
                                <button type='reset' class='btn btn-outline-secondary w-100 m-2'>Reset</button>
                                <button type='button' class='btn btn-outline-success w-100 m-2' onclick='envia()'>Envia</button>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div id='msgAlerta' class="text-center h5"></div>
                        </div>
                    </div>
                </div>
            </div>            
        </form>
    </main>
    <script>
        function toggleSenha(idCampo, el) {
            const campo = document.getElementById(idCampo);
            if (campo.type === 'password') {
                campo.type = 'text';
            } else {
                campo.type = 'password';
            }
        }


        function envia() {
            //
            let idColab = $("#idColab");
            let token = $("#token");
            let login = $("#login");
            let senha1 = $("#senha1");
            let senha2 = $("#senha2");
            let mensagem = $("#msgAlerta");
            let botoes = $("#botoes");
            //
            if (senha1.val() == '' || senha2.val() == '') {
                alert("campos senha é necessário");
                senha1.focus();
                return false;
            }
            if (senha1.val() != senha2.val()) {
                alert("campos senhas devem ser iguais");
                senha1.val("");
                senha2.val("");
                senha1.focus();
                return false;
            }
            botoes.hide();
            mensagem.html("Aguarde ...");
            $.post("cadastrar_senha_aj.php", {
                    idColab: idColab.val(),
                    login: login.val(),
                    senha: senha1.val(),
                    token: token.val()
                },
                function(retorno) {
                    let dados = JSON.parse(retorno);
                    mensagem.html( dados.msg );
                    setTimeout(function() {
                        //$("#aguarde").fadeOut();
                        location.href = "../login.php";
                    }, 3000);
                }
            );
        }
    </script>

</body>

</html>
<?php

function removerAcentos($string)
{
    return preg_replace(
        ['/[áàãâä]/u', '/[éèêë]/u', '/[íìîï]/u', '/[óòõôö]/u', '/[úùûü]/u', '/[ç]/u'],
        ['a', 'e', 'i', 'o', 'u', 'c'],
        strtolower($string)
    );
}
