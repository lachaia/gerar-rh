<?php
//
// - logout_solicitacao.php
//

session_start();

$agora = date('Y-m-d H:i:s');

if( isset( $_SESSION['idLogin'] ) ){
    $idLogin = $_SESSION["idLogin"];
    include_once "../app/includes/conexao_gerar.php";
    $stmt = $conn->prepare( "UPDATE rh_logins SET dtLogout = '$agora' WHERE idLogin = $idLogin" );
    $stmt->execute(); 
    $conn = null;     
}

$_SESSION = array(); // Limpa todas as variáveis de sessão
session_destroy(); // Destruir a sessão
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação Enviada com Sucesso</title>
    <!-- Bootstrap 5 CSS para um design limpo e responsivo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ícones do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100 justify-content-center align-items-center">

    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                    <div class="card-body">
                        <!-- Ícone de Sucesso -->
                        <div class="mb-3 text-success">
                            <i class="bi bi-check-circle-fill" style="font-size: 3.5rem;"></i>
                        </div>
                        
                        <!-- Título -->
                        <h2 class="h4 fw-bold text-dark mb-2">Solicitação Enviada!</h2>
                        
                        <!-- Mensagem -->
                        <p class="text-muted mb-4">
                            Tudo ocorreu bem! Sua solicitação e os e-mails foram processados com sucesso. 
                            Sua sessão foi encerrada por segurança.
                        </p>
                        
                        <!-- Botão de Ação -->
                        <div class="d-grid">
                            <a href="login.php" class="btn btn-primary rounded-pill py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Voltar ao Início
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé opcional -->
                <p class="text-muted small mt-4">
                    &copy; <?php echo date('Y'); ?> Sistema de RH. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>