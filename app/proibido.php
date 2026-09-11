<?php
//
// - logout.php
//

session_start();

$agora = date('Y-m-d H:i:s');

if (isset($_SESSION['idLogin'])) {
    // Atualiza a data de logout
    $idLogin = $_SESSION["idLogin"];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    $stmt = $conn->prepare("UPDATE rh_logins SET dtLogout = '$agora' WHERE idLogin = $idLogin");
    $stmt->execute();
    $conn = null;
}

$_SESSION = array(); // Limpa todas as variáveis de sessão
session_destroy(); // Destruir a sessão

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GERAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body background="imagens/R.jpeg" class="bg-dark">
    <main>
        <div id='dash' class="container-fluid d-flex align-items-center justify-content-center vh-100">
            <div class="text-center">
                <h1 class="mt-4 text-white">ACESSO EXCLUSIVO AOS<br>COLABORADORES DO RH</h1>
                <a href='login.php'>Voltar</a>
            </div>
            
        </div>
    </main>
</body>

</html>