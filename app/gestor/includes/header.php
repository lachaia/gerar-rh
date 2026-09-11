<!doctype html>

<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Módulo RH — Dashboard do Colaborador</title>

    <!-- BOOTSTRAP CORE -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ÍCONES -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- JQUERY E JQUERY UI -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

    <!-- SUMMERNOTE -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- BOOTSTRAP BUNDLE (inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CSS LOCAL -->
    <link href="css/index.css" rel="stylesheet" />
</head>


<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(90deg,#6f42c1,#3b82f6);display:flex;align-items:center;justify-content:center;font-weight:700;">RH</div>
            <div>
                <h4>Módulo RH</h4>
                <div style="font-size:.8rem;color:var(--muted)"><?= $_SESSION['nmLogin'] ?></div>
            </div>
        </div>

        <br>
        <!-- Large navigation buttons -->
        <div class="d-grid">
            <button class="nav-btn <?= ($modulo == 'Home') ? "active" : "" ?>" onclick='go("index.php")'>
                <i class="fa-solid fa-home"></i> Home
            </button>

            <button class="nav-btn <?= ($modulo == 'Equipe') ? "active" : "" ?>" onclick='go("equipe.php")'>
                <i class="fa-solid fa-users"></i> Equipe
            </button>

            <button class="nav-btn <?= ($modulo == 'Férias') ? "active" : "" ?>" onclick='go("ferias.php")'>
                <i class="fa-solid fa-plane"></i> Férias
            </button>

            <button class="nav-btn <?= ($modulo == 'Afastamentos') ? "active" : "" ?>" onclick='go("afastamentos.php")'>
                <i class="fa-solid fa-file-medical"></i> Afastamentos
            </button>

            <button class="nav-btn <?= ($modulo == 'Ponto') ? "active" : "" ?>" onclick='go("ponto.php")'>
                <i class="fa-solid fa-clock"></i> Ponto eletrônico
            </button>


            <button class="nav-btn text-center text-primary"  onclick='go("../logout.php")'>
                <i class="fa-solid fa-right-from-bracket"></i> Sair
            </button>
        </div>

        <div class="mt-3">
            <button class="btn btn-ghost w-100" id="toggleSidebarMobile"><i class="bi bi-list"></i> Menu</button>
        </div>

        <footer class="small-note mt-4 text-center">
            Versão inicial • Tema escuro<br>
            <?= "ID Log: " . $_SESSION['idLogin'] ?>
        </footer>

    </aside>
    <script>

        function go( url ){
        window.location.href = url;
        }

    </script>