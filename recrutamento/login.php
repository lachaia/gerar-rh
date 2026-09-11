<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Vagas - Login</title>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #121212;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            background-color: #1e1e1e;
            border: 1px solid #2d2d2d;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .landing-section {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            border-right: 1px solid #2d2d2d;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .logomarca {
            width: 80px;
            height: 80px;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="row g-0 login-container">

                    <!-- Esquerda: Landing / Informativo -->
                    <div class="col-md-6 p-4 p-lg-5 d-flex flex-column justify-content-between landing-section">
                        <div>
                            <div class="d-flex align-items-center mb-4">
                                <i class="bi bi-person-plus-fill fs-2 text-primary me-2"></i>
                                <h4 class="m-0 fw-bold">Solicitação de Vagas</h4>
                            </div>

                            <h2 class="fw-bold mb-3">A contratação de seu novo colaborador começa aqui.</h2>
                            <p class="text-secondary mb-4">
                                Acesse o módulo para cadastrar requisições de vagas de forma simples e rápida.
                            </p>

                            <div class="p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-3 me-3"></i>
                                    <div>
                                        <small class="d-block text-secondary">Antes de solicitar, consulte:</small>
                                        <a href="#" class="text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNormativa">
                                            Normativa de Solicitação de Vagas <i class="bi bi-box-arrow-up-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-secondary small">
                            &copy; <?= date('Y') ?> Sistema de Gestão. Todos os direitos reservados.
                        </div>
                    </div>

                    <!-- Direita: Formulário de Login -->
                    <div class="col-md-6 p-4 p-lg-5 d-flex flex-column justify-content-center">

                        <!-- Container da Logo Centralizado -->
                        <div class="d-flex align-items-center justify-content-center mb-4">
                            <img src="img/loginho.png" alt="Logomarca" class="logomarca img-fluid">
                        </div>

                        <h3 class="fw-bold mb-1 text-center">Acessar o Módulo</h3>
                        <p class="text-secondary mb-4 text-center">Informe suas credenciais para continuar.</p>

                        <!-- Div para Alertas do JS -->
                        <div id="login-alert"></div>

                        <form id="formLogin" onsubmit="return false;" novalidate>
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuário ou E-mail</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Seu usuário ou e-mail" required onchange="testar_usuario(this)">
                                    <input type="hidden" id='status' value='0'>

                                    <!-- Feedback visual (Bootstrap 5) -->
                                    <div class="valid-feedback" id="usuario-feedback-valid">
                                        <i class="bi bi-check-circle-fill me-1"></i> Usuário válido!
                                    </div>
                                    <div class="invalid-feedback" id="usuario-feedback-invalid">
                                        <i class="bi bi-x-circle-fill me-1"></i> Usuário não encontrado.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="senha" class="form-label">Senha</label>
                                    <a href="#" class="text-secondary text-decoration-none small" id="btnEsqueciSenha">Esqueceu a senha?</a>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-end-0" id="senha" name="senha" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary border-secondary border-opacity-50 border-start-0 text-secondary" type="button" id="toggleSenha">
                                        <i class="bi bi-eye" id="iconeOlho"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary w-100 py-2 fw-semibold" id="btnLogin" onclick="envia()">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" id="btnSpinner"></span>
                                Entrar no Sistema
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal da Normativa (PDF) -->
    <div class="modal fade" id="modalNormativa" tabindex="-1" aria-labelledby="modalNormativaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="height: 85vh;">
            <div class="modal-content h-100">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalNormativaLabel">
                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>Normativa de Solicitação de Vagas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0 h-100">
                    <!-- Substitua 'docs/normativa.pdf' pelo caminho real do seu arquivo PDF -->
                    <iframe src="docs/normativa.pdf" class="w-100 h-100" style="border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>

</html>