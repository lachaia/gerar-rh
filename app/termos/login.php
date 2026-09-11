<?php
// login.php
session_start();

// Se já estiver logado, manda pro index
if (isset($_SESSION['idLogin'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Termos de Responsabilidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }
        .login-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-card {
            background-color: #1e1e1e;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
        }
        .form-control {
            background-color: #2b2b2b;
            color: #fff;
            border: 1px solid #444;
        }
        .form-control:focus {
            background-color: #333;
            color: #fff;
            border-color: #0d6efd;
            box-shadow: none;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card text-center">
            <img src="../imagens/logo.png" alt="Logo Gerar" class="img-fluid mb-3" style="max-height: 80px;">
            <h3 class="mb-4 text-light fw-bold">Módulo: Termos</h3>
            

                <div class="mb-3 text-start">
                    <label for="usuario" class="form-label text-light">Usuário</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                </div>
                <div class="mb-3 text-start">
                    <label for="senha" class="form-label text-light">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>
                <button class="btn btn-primary w-100" id='botaoLogin' onclick='login()'>Entrar</button>

        </div>
        <div id='msgAlerta'></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>

       function login(){
            //- Testa se forneceu o Login ou cpf ou email
            if ($("#usuario").val() == "") {
                alert("Usuário ou CPF ou e-Mail deve ser informado");
                $("#usuario").focus();
                return false;
            }

            //- Testa se digitou a senha
            if ($("#senha").val() == "") {
                alert("A senha de acesso deve ser informada");
                $("#senha").focus();
                return false;
            }

            //- Faz a autenticação
            $.post("login_auth.php", {
                login: $("#usuario").val(),
                senha: $("#senha").val()
            },
                function (dados, status) {
                    var retorno = JSON.parse(dados);
                    if (retorno.status == 1) {
                        $("#formulario").hide();
                        $("#aguarde").show();
                        window.location.href = "index.php";
                        return true;
                    } else {
                        alert(retorno.mensagem);
                        return false;
                    }
                }
            );
        }


    </script>
</body>
</html>
