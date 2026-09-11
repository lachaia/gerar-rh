<?php
// - SISTEMA: RH
// - reset_senha.php | Programa para RESETAR A SENHA DO USUÁRIO
// - (C)haia, 12/02/2025
//

session_start();

include "conexao_gerar.php"; // Arquivo de conexão com o banco

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (!$token) {
    die("Token inválido ou ausente!");
}

// Busca o idUsuario na tabela rh_token
$sql = "SELECT idUsuario FROM rh_token WHERE idToken = :token";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":token", $token, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Token inválido ou expirado!");
}

$idUsuario = $result['idUsuario'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 400px;
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .form-control {
            background-color: #333;
            color: #fff;
            border: 1px solid #555;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #0056b3;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-control {
            background-color: #333;
            /* Fundo mais escuro */
            color: #f8f9fa;
            /* Texto mais claro */
            border: 1px solid #777;
            /* Borda mais visível */
        }

        .form-control::placeholder {
            color: #ccc;
            /* Cor do placeholder mais clara */
            opacity: 1;
            /* Garante que a cor do placeholder seja visível */
        }

        .form-control:focus {
            background-color: #444;
            /* Fundo mais claro ao focar */
            color: #fff;
            /* Texto ainda mais visível */
            border-color: #007bff;
            /* Realce na borda ao focar */
        }
    </style>
</head>

<body>

    <div class="container">
        <h3 class="text-center">Redefinir Senha</h3>
        <div id="msg" class="alert d-none"></div>
        <input type="hidden" id="idUsuario" value="<?= $idUsuario ?>">

        <div class="mb-3">
            <label for="senha1" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" id="senha1" placeholder="Digite a nova senha">
        </div>

        <div class="mb-3">
            <label for="senha2" class="form-label">Confirmar Senha</label>
            <input type="password" class="form-control" id="senha2" placeholder="Confirme a nova senha">
        </div>

        <button class="btn btn-primary w-100" onclick="resetSenha()">Redefinir Senha</button>
        <input type="hidden" id='token' name='token' value='<?php echo $token; ?>'>
    </div>

    <script>
        function resetSenha() {
            let senha1 = $("#senha1").val();
            let senha2 = $("#senha2").val();
            let idUsuario = $("#idUsuario").val();
            let token = $("#token").val();

            if (senha1.length < 6) {
                showMessage("A senha deve ter pelo menos 6 caracteres!", "danger");
                return;
            }

            if (senha1 !== senha2) {
                showMessage("As senhas não coincidem!", "danger");
                return;
            }

            $.post("reset_senha_aj.php", {
                idUsuario: idUsuario,
                senha: senha1,
                token: token
            }, function(retorno) {
                let dados = JSON.parse(retorno);
                showMessage(dados.msg, dados.status ? "success" : "danger");

                if (dados.status) {
                    setTimeout(() => {
                        window.location.href = "../login.php";
                    }, 3000);
                }
            });
        }

        function showMessage(msg, type) {
            $("#msg").removeClass("d-none alert-success alert-danger").addClass("alert-" + type).html(msg);
        }
    </script>

</body>

</html>