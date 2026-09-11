<?php
//- g_phpinfo.php - Módulo para mostrar a configuração da plataforma de instalação
//- (C) Chaia, 01/08/2023
//

$idModulo = 12;

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
}

include_once __DIR__ . "/includes/conexao_gerar.php";
include_once __DIR__ . "/includes/f_logs.php";
f_log("CON", "Consulta Configurações", "*", $idModulo, 0);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Minhas configurações" />
    <meta name="author" content="LAChaia" />
    <title>Gerar: Configurações</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <main class="container mt-2">
                <!-- Aqui COMEÇA o conteúdo da página -->
                <div class="phpinfo-container">
                    <iframe id="infoFrame" src="includes/f_phpinfopage.php" frameborder="0" style="width: 100%;"></iframe>
                </div>
                <!-- Aqui Termina o conteúdo da página -->
            </main>
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    
    <script>
        function ajustarIframe() {
            const alturaJanela = window.innerHeight; // Altura da viewport
            const alturaIframe = alturaJanela > 900 ? alturaJanela * 0.8 : alturaJanela * 0.8; 
            document.getElementById("infoFrame").style.height = alturaIframe + "px";
        }

        // Chama a função ao carregar a página
        ajustarIframe();

        // Redimensiona o iframe quando a janela for redimensionada
        window.onresize = ajustarIframe;
    </script>

    <script src="js/scripts.js"></script>
</body>


</html>