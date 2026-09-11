<?php
# login.php
# Filtra a entrada de usuários ao sistema
#
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Login - Gerar</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/login.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <!-- jQuery UI (CSS para o estilo e JS para o autocomplete) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body.bg-primary {
            min-height: 100vh;
            background:
                radial-gradient(900px 500px at 8% 18%, rgba(46, 122, 255, .32) 0%, rgba(46, 122, 255, 0) 60%),
                radial-gradient(700px 420px at 82% 80%, rgba(18, 214, 174, .22) 0%, rgba(18, 214, 174, 0) 65%),
                linear-gradient(135deg, #081a44 0%, #0a2d6b 46%, #07183e 100%) !important;
            position: relative;
            overflow-x: hidden;
        }

        body.bg-primary::before,
        body.bg-primary::after {
            content: "";
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            filter: blur(44px);
            z-index: 0;
            opacity: .35;
            pointer-events: none;
            animation: floatGlow 9s ease-in-out infinite;
        }

        body.bg-primary::before {
            top: -80px;
            left: -90px;
            background: #2f83ff;
        }

        body.bg-primary::after {
            right: -90px;
            bottom: -110px;
            background: #22d3ee;
            animation-delay: 1.2s;
        }

        #layoutAuthentication,
        #layoutAuthentication_content,
        main,
        .container,
        .login-shell {
            position: relative;
            z-index: 1;
        }

        .login-shell {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 24px;
            align-items: stretch;
            min-height: calc(100vh - 120px);
        }

        .login-panel,
        .login-card-wrap {
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 14px 40px rgba(0, 0, 0, .28);
            backdrop-filter: blur(3px);
        }

        .login-panel {
            position: relative;
            background: linear-gradient(165deg, rgba(11, 24, 53, .94), rgba(15, 46, 104, .9));
            color: #e9f0ff;
            padding: 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            animation: fadeUp .55s ease-out;
        }

        .home-link {
            position: absolute;
            top: 14px;
            left: 14px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .24);
            text-decoration: none;
            transition: background .2s ease, transform .2s ease;
        }

        .home-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .24);
            transform: translateY(-1px);
        }

        .login-panel h1 {
            font-size: clamp(1.8rem, 2.8vw, 2.6rem);
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
            letter-spacing: .2px;
        }

        .login-panel p {
            font-size: 1.05rem;
            line-height: 1.62;
            color: #d8e4ff;
            margin-bottom: 18px;
            max-width: 56ch;
        }

        .login-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .login-tags span {
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            padding: 7px 13px;
            font-size: .88rem;
            color: #f0f5ff;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: transform .2s ease, background .2s ease;
        }

        .login-tags span:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, .2);
        }

        .login-card-wrap {
            background: rgba(15, 19, 28, .94);
            color: #fff;
            animation: fadeUp .65s ease-out;
        }

        .login-card-wrap .card {
            margin: 0;
            border: 0;
            border-radius: 0;
            min-height: 100%;
            background: transparent !important;
        }

        .login-card-wrap .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            background: transparent;
        }

        .login-card-wrap .logo {
            height: 52px;
        }

        .login-card-wrap .form-control,
        .login-card-wrap .form-select {
            background: #f4f7ff;
        }

        /* Estilos adicionados para o botão de Mostrar Senha */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 48px; /* Dá espaço para o ícone não cobrir o texto */
        }

        .btn-toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #495057;
            cursor: pointer;
            z-index: 5;
            padding: 4px;
        }

        .btn-toggle-password:focus {
            outline: none;
            box-shadow: none;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatGlow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(16px);
            }
        }

        @media (max-width: 991px) {
            .login-shell {
                grid-template-columns: 1fr;
                min-height: auto;
                padding-top: 14px;
                padding-bottom: 14px;
            }

            .login-panel {
                position: relative;
                padding: 24px;
            }
        }
    </style>
</head>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main class="py-4">
                <div class="container">
                    <div class="login-shell">

                        <section class="login-panel">
                            <a href="../index.php" class="home-link" title="Página principal" aria-label="Página principal"><i class="fa-solid fa-house"></i></a>
                            <h1>Portal Interno GERAR</h1>
                            <p>Um &uacute;nico ponto de acesso para os ambientes institucionais. Aqui, cada colaborador entra com seu perfil para operar com seguran&ccedil;a e autonomia nas rotinas do dia a dia.</p>
                            <p>Voc&ecirc; pode navegar pelas &aacute;reas Administrativa, Colaborador, Gestor, Brigada e CIPA com uma experi&ecirc;ncia de acesso clara, r&aacute;pida e alinhada ao seu papel na organiza&ccedil;&atilde;o.</p>
                            <div class="login-tags">
                                <span><i class="fa-solid fa-building-user"></i>&Aacute;rea Administrativa</span>
                                <span><i class="fa-solid fa-id-badge"></i>Colaborador</span>
                                <span><i class="fa-solid fa-chart-line"></i>Gestor</span>
                                <span><i class="fa-solid fa-fire-extinguisher"></i>Brigada</span>
                                <span><i class="fa-solid fa-shield-heart"></i>CIPA</span>
                            </div>
                            
                            <div class="login-support mt-4">
                                <h4>Suporte ao RH</h4>
                                <p>D&uacute;vidas sobre o sistema ou processos internos?</p>
                                <button type="button" onclick="suporte()" class="btn btn-light btn-sm">Abrir Chamado</button>
                            </div>
                        </section>

                        <section class="login-card-wrap">
                            <div class="card shadow-lg border-0 rounded-lg bg-dark text-white p-4">
                                <div class="card-header">
                                    <img src="imagens/logo.png" alt="LogoMarca" class="logo m-2">
                                    <h3 id='cabecalho' class="text-center font-weight-light my-4">Acesso ao Sistema</h3>
                                </div>
                                <div class="card-body">

                                    <!-- FORMS LOGON -->
                                    <form id='formulario'>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputLogin" type="text" placeholder="Seu login..." onBlur='verifica(this)' />
                                            <label for="inputLogin">Usuário/CPF/e-Mail</label>
                                        </div>
                                        
                                        <!-- CAMPO DE SENHA ALTERADO -->
                                        <div class="form-floating mb-3 password-wrapper">
                                            <input class="form-control" id="inputPassword" type="password" placeholder="Password" />
                                            <label for="inputPassword">Senha</label>
                                            <button type="button" class="btn-toggle-password" id="togglePassword" aria-label="Mostrar ou ocultar senha">
                                                <i class="fa-solid fa-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>

                                        <div class="form-floating mb-3">
                                            <?php echo empresas("idEmpresa"); ?>
                                            <label for="idEmpresa">Selecione a Empresa</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <select name="idPerfil" id="idPerfil" class="form-select"></select>
                                            <label for="idPerfil">Selecione o Perfil</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small estilo_link" href="#" onClick="cadastro()">Cadastre-se</a>
                                            <a class="small estilo_link" href="#" onClick='esqueci()'>Esqueceu a Senha?</a>
                                            <a class="btn btn-primary" href="#" id="botaoLogin">Fazer Login</a>
                                        </div>
                                    </form>

                                    <!-- FORMS ESQUECI A SENHA -->
                                    <form id="form_esqueci" class='invisivel p-4'>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="x_email" type="text" placeholder="Seu e-Mail..." />
                                            <label for="x_email">e-mail GERAR para receber o link</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <?php echo empresas("x_idEmpresa"); ?>
                                            <label for="x_idEmpresa">Selecione a Empresa</label>
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="row">
                                                <div class="col-2">
                                                    <a class="btn btn-secondary w-100" href="login.php"><i class="fa-solid fa-rotate-left"></i></a>
                                                </div>
                                                <div class="col-10">
                                                    <a class="btn btn-primary w-100" href="#" onClick='enviar_email()' id="botao_enviar">Enviar o link</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- FORMS CADASTRAR-SE -->
                                    <form id="form_cadastro" class='invisivel p-4'>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="c_nome" type="text" placeholder="comece a digitar..." />
                                            <label for="c_nome">Colaborador</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="c_email" type="text" placeholder="Seu e-Mail..." />
                                            <label for="c_email">Informe o e-mail corporativo</label>
                                            <input type="hidden" id='idColab' value='0'>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <?php echo empresas("c_idEmpresa"); ?>
                                            <label for="c_idEmpresa">Selecione a Empresa</label>
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="row">
                                                <div class="col-2">
                                                    <a class="btn btn-secondary w-100" href="login.php"><i class="fa-solid fa-rotate-left"></i></a>
                                                </div>
                                                <div class="col-10">
                                                    <a class="btn btn-primary w-100" href="#" onClick='enviar_cadastro()' id="botao_enviar">Enviar o link</a>
                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                    <h3 id='aguarde' class="text-center">AGUARDE ...</h3>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </main>

        </div>
        <div id="layoutAuthentication_footer">
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Gerar 2023</div>
                        <div>
                            <a href="#">Política de Privacidade</a>
                            &middot;
                            <a href="#">Termos &amp; Condições</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="js/login.js"></script>
    <script>
        function suporte() {
            window.open('../suporte/index.html', '_blank');
        }

        // Script para alternar visibilidade da senha
        $(document).ready(function() {
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#inputPassword');
                const toggleIcon = $('#toggleIcon');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
</body>

</html>
<?php
//-- ROTINAS AUXILIARES EM PHP
//

function empresas($campo = 'idEmpresa')
{
    // Cria o SELECT para idEmpresa
    include "includes/conexao_gerar.php";
    //
    $sql = "SELECT idEmpresa, nome FROM rh_empresas ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    $html = "<select name='$campo' id='$campo' class='form-select form-control-sm fs-13'>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $html .= "<option value='$idEmpresa'>$nome</option>";
    }
    $html .= "</select>";
    return $html;
}
?>