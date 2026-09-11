<?php
// GERAR 2023 - rh_altsenha.php - Programa para Alterar a senha do usuáro - Autocadastro
// (C) Chaia, 01/08/2023
//
session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
}

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
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">

            <!-- Aqui COMEÇA o conteúdo da página -->

            <main>
                <div class="container px-4">
                    <h1 class="mt-4">Configurações</h1>
                    <ol class="breadcrumb mb-6">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Configurações</li>
                    </ol>
                    <div class="row">
                        <div class="alert alert-success d-none" id="alertaSucesso">
                            <strong>Success!</strong> This alert box could indicate a successful or positive action.
                        </div>
                        <div class="alert alert-danger d-none" id="alertaErro">
                            <strong>Danger!</strong> This alert box could indicate a dangerous or potentially negative action.
                        </div>
                    </div>
                    <div class="row justify-content-center"> <!-- Adicionada a classe 'justify-content-center' para centralizar horizontalmente -->
                        <div class="card col-xl-4 my-4"> <!-- Adicionadas as classes 'my-4' para centralizar verticalmente e 'col-xl-6' para ajustar a largura -->
                            <div class="card-header text-center">Forneça a nova senha</div>
                            <div class="card-body">
                            <form id="formulario">
                                    <div class="form-floating mb-3">
                                        <label for="senha1">Senha</label>
                                        <div class="input-group">
                                            <input class="form-control" id="senha1" type="password" placeholder="Sua senha" />
                                            <span class="input-group-text" id="olho1">
                                                <i class="fas fa-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <label for="senha2">Repita a Senha</label>
                                        <div class="input-group">
                                            <input class="form-control" id="senha2" type="password" placeholder="Repita a senha" />
                                            <span class="input-group-text" id="olho2">
                                                <i class="fas fa-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                        <a class="btn btn-primary d-grid w-100" href="#" id="botaoLogin">Salvar</a>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer">
                                <span id="mensagem"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>


            <!-- 
                    Aqui TERMINA o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
        <script>
            $(document).ready(function() {

                const mensagemSpan = $("#mensagem");

                function checkCapsLock(event) {
                    if (event.originalEvent.getModifierState && event.originalEvent.getModifierState("CapsLock")) {
                        mensagemSpan.text("Caps Lock está ativado.");
                    } else {
                        mensagemSpan.text("");
                    }
                }

                $("#senha1, #senha2").on("keydown", checkCapsLock);

                const olho1 = $("#olho1");
                const senha1 = $("#senha1");

                olho1.click(function() {
                    if (senha1.attr("type") === "password") {
                        senha1.attr("type", "text");
                        olho1.html('<i class="fas fa-eye"></i>');
                    } else {
                        senha1.attr("type", "password");
                        olho1.html('<i class="fas fa-eye-slash"></i>');
                    }
                });

                const olho2 = $("#olho2");
                const senha2 = $("#senha2");

                olho2.click(function() {
                    if (senha2.attr("type") === "password") {
                        senha2.attr("type", "text");
                        olho2.html('<i class="fas fa-eye"></i>');
                    } else {
                        senha2.attr("type", "password");
                        olho2.html('<i class="fas fa-eye-slash"></i>');
                    }
                });

                $("#botaoLogin").click(function() {
                    //
                    if ($("#senha1").val() != $("#senha2").val()) {
                        alert("as senhas devem ser iguais! ");
                        return false;
                    }
                    //
                    $.post("rh_altsenha_aj.php", {
                            senha1: $("#senha1").val(),
                            senha2: $("#senha2").val()
                        },
                        function(dados, status) {
                            var retorno = JSON.parse(dados);
                            if (retorno.status == 1) {

                                $("#alertaSucesso").html("<strong>Sucesso!</strong> " + retorno.mensagem);
                                $("#alertaSucesso").removeClass("d-none");
                                // Configurar temporizador para ocultar a mensagem após 3 segundos
                                setTimeout(function() {
                                    $("#alertaSucesso").addClass("d-none");
                                    $("#formulario").each(function() {
                                        this.reset();
                                    });
                                }, 3000);
                                return true;
                            } else {
                                $("#alertaErro").html("<strong>Problema!</strong> " + retorno.mensagem);
                                $("#alertaErro").removeClass("d-none");
                                // Configurar temporizador para ocultar a mensagem após 3 segundos
                                setTimeout(function() {
                                    $("#alertaErro").addClass("d-none");
                                }, 3000);
                                return false;
                            }
                        });

                });


            });
        </script>
    </div>
    <script src="js/scripts.js"></script>
</body>

</html>