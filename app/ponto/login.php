<?PHP
//
// login.php | Módulo PONTO - Página de LOGON
// (C)haia, 30/10/2025
//

$modulo = 22; //- Controle de Ponto

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Ponto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card text-center">
            <img src="../imagens/logo.png" alt="Logo Gerar" class="img-fluid mb-3" style="max-height: 80px;">
            <h3 class="mb-4 text-light fw-bold">Controle de Ponto</h3>
            

                <div class="mb-3 text-start">
                    <label for="usuario" class="form-label text-light">Usuário</label>
                    <input type="text" class="form-control text-center" id="usuario" name="usuario" required>
                </div>
                <div class="mb-3 text-start">
                    <label for="senha" class="form-label text-light">Senha</label>
                    <input type="password" class="form-control text-center" id="senha" name="senha" required>
                </div>
                <button class="btn btn-primary w-100" id='botaoLogin' onclick='login()'>Entrar</button>
                <h6 class="text-center mt-4">vr. 1.0</h6>
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
