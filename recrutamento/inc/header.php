<?php
$logo = "../app/imagens/logo.png";
$avatar = "../app/fotos/" . $_SESSION['perfil'];
$baseDir = $_SESSION['baseDir'];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GERAR|R&S</title>
    <link rel="icon" type="image/x-icon" href="img/logo.ico">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <link href="css/styles.css" rel="stylesheet" />

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables Buttons -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>

    <!-- ADICIONE ESTAS LINHAS: -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

</head>

<body>

    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <a href="index.php">
                    <img src="<?= $logo ?>" alt="GERAR" class="sidebar-logo">
                </a>
                <span class="brand-text">GERAR</span>
            </div>

            <div class="nav flex-column mt-2">
                <a href="#" class="nav-link" id="sidebarCollapse">
                    <i class="fas fa-bars"></i>
                    <span class="menu-text text-secondary">Recolher</span>
                </a>

                <a href="<?= $baseDir . '/index.php' ?>" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span class="menu-text">Home</span>
                </a>

                <a href="<?= $baseDir . '/fluxo.php' ?>" class="nav-link">
                    <i class="fa-brands fa-trello text-info"></i>
                    <span class="menu-text">Fluxo Vagas</span>
                </a>

                <a href="#submenuCadastros" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuCadastros">
                    <i class="fa-solid fa-folder-tree text-secondary"></i>
                    <span class="menu-text">Cadastros</span>
                    <i class="fa-solid fa-chevron-down ms-auto menu-text chevron-icon"></i>
                </a>
                <div class="collapse" id="submenuCadastros">
                    <a href="<?= $baseDir . '/motivos.php' ?>" class="nav-link submenu-link">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span class="menu-text">Motivos</span>
                    </a>
                    <a href="<?= $baseDir . '/vagas_status.php' ?>" class="nav-link submenu-link">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span class="menu-text">Status da Vaga</span>
                    </a>
                    <a href="<?= $baseDir . '/vagas_fluxo.php' ?>" class="nav-link submenu-link">
                        <i class="fa-brands fa-trello"></i>
                        <span class="menu-text">Etapas do Fluxo</span>
                    </a>
                    <a href="<?= $baseDir . '/superintendentes.php' ?>" class="nav-link submenu-link">
                        <i class="fa-solid fa-user-tie"></i>
                        <span class="menu-text">Superintendentes</span>
                    </a>
                    <a href="<?= $baseDir . '/competencias_tecnicas.php' ?>" class="nav-link submenu-link">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span class="menu-text">Competências Técnicas</span>
                    </a>
                    <a href="<?= $baseDir . '/competencias_comportamentais.php' ?>" class="nav-link submenu-link">
                        <i class="fa-solid fa-people-arrows"></i>
                        <span class="menu-text">Competências Comportamentais</span>
                    </a>
                    <a href="<?= $baseDir . '/candidatos_origem.php' ?>" class="nav-link submenu-link">
                        <i class="fa-solid fa-signs-post"></i>
                        <span class="menu-text">Origens de Candidato</span>
                    </a>
                </div>

                <a href="logout.php" class="nav-link">
                    <i class="fa-solid fa-right-from-bracket text-danger"></i>
                    <span class="menu-text">Sair</span>
                </a>
            </div>
        </nav>

        <div id="content">
            <header class="top-navbar d-flex align-items-center justify-content-between px-4">

                <div class="page-indicator">
                    <h4 class="m-0 text-uppercase fw-bold text-secondary" style="letter-spacing: 1px;">
                        <?php echo $titulo_pagina ?? 'DASHBOARD'; ?>
                    </h4>
                </div>

                <div class="d-flex align-items-center">

                    <!--
                    <div class="input-group input-group-sm me-3" style="max-width: 250px;">
                        <input type="text" class="form-control bg-dark border-secondary text-white shadow-none" placeholder="Busca rápida...">
                        <span class="input-group-text bg-dark border-secondary text-secondary">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    -->
                    <div class="dropdown me-3">
                        <a href="#" class="text-secondary" id="dropdownMenuSettings" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog" style="font-size: 1.4rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" aria-labelledby="dropdownMenuSettings">
                            <li>
                                <h6 class="dropdown-header text-uppercase" style="font-size: 0.65rem;">Sistema</h6>
                            </li>
                            <li><a class="dropdown-item" href="reset_senha.php"><i class="fas fa-key me-2 fa-fw"></i> Reset Senha</a></li>
                            <li><a class="dropdown-item" href="config.php"><i class="fas fa-sliders-h me-2 fa-fw"></i> Configurações</a></li>
                            <li>
                                <hr class="dropdown-divider border-secondary">
                            </li>
                            <li><a class="dropdown-item" href="sys_logins.php"><i class="fas fa-user-shield me-2 fa-fw"></i> Logins</a></li>
                            <li><a class="dropdown-item" href="sys_logs.php"><i class="fas fa-list-ul me-2 fa-fw"></i> Logs</a></li>
                            <?php
                            if( $_SESSION['idGrupo'] == 1 ){
                                echo '<li><a class="dropdown-item" href="usuarios.php"><i class="fas fa-users me-2 fa-fw"></i> Usuários</a></li>';
                            }
                            ?>
                        </ul>
                    </div>

                    <div class="user-area d-flex align-items-center border-start border-secondary ps-3">
                        <div id='avatar'>
                            <img src="<?= $avatar; ?>" class="rounded-circle me-2" width="35" height="35" style="object-fit: cover;">
                        </div>
                        <a href="logout.php" class="btn btn-link text-primary p-0 ms-2">
                            <i class="fas fa-power-off" style='font-size: 1.6rem'></i>
                        </a>
                    </div>

                </div>
            </header>
