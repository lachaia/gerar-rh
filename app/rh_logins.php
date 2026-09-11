<?php
session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['status'])) {
    $_SESSION['status'] = $_GET['status'];
} else {
    $_SESSION['status'] = 'Geral';
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tabela de ti_logins no Sistema" />
    <meta name="author" content="LAChaia" />
    <title>Gerar: Logins</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        table {
            font-size: 13px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <!-- 
                    Aqui COMEÇA o conteúdo da página 
                -->
            <main>
                <div class="container-fluid px-4">
                    <div class="card mt-2 bg-dark text-white">
                        <h4 class="m-4">
                            <i class="fa-solid fa-person-circle-exclamation"></i> LOGINS
                        </h4>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header clearfix">
                            <div class="float-start">
                                <i class="fas fa-table me-1"></i>
                                Registro das Atividades no Sistema | <?php echo $_SESSION['status']; ?>
                            </div>
                            <div class="float-end" id='seletores'>
                                <select name="" id="" class="form-select form-control-sm">
                                    <option value="0">Qual usuário??</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example" class="table nowrap table-striped table-hover table-bordered table-sm fb-8" style="width:100%">
                                <thead class="gb-gray">
                                    <tr>
                                        <th>Login</th>
                                        <th>Usuario</th>
                                        <th>ID</th>
                                        <th>Entrada</th>
                                        <th>Saída</th>
                                        <th>IP</th>
                                        <th>SisOper</th>
                                        <th>Browser</th>
                                        <th>Hardware</th>
                                        <th>Operações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <!-- 
                    Aqui Termina o conteúdo da página 
                -->
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script src="js/rh_logins.js"></script>
</body>

</html>