<?php
//
// - rh_config.php - Mostra Configurações da Seção do Usuário
// - (C) Chaia, 01/08/2023
//

$idModulo = 11; // rh_condfig.php

session_start();

if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcCIPA']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    header('Location: login.php');
    exit();
} else {
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    f_log("CON", "Consulta Configurações", "*", $idModulo, 0);
}

$idUsuario = $_SESSION['idUsuario'];
$nmUsuario = $_SESSION['nmUsuario'];

//
//- Recupera assinatura antiga
//
$sql = "SELECT assinatura FROM rh_usuarios WHERE idUsuario = :idUsuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT); // Segurança contra SQL Injection
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém o resultado como um array associativo
$txt_assinatura = $row['assinatura'] ?? ''; // Atribui o valor à variável ou uma string vazia se não houver resultado

if (empty($txt_assinatura)) {
    $txt_assinatura = "<p style='font-size: 14px'><b>$nmUsuario<b></p>
        <p>Cargo / Setor</p> <p><b>Disclaimer:</b><br></p>
        <p>Este e-mail e quaisquer arquivos anexos são confidenciais e destinados exclusivamente ao uso da pessoa ou entidade a quem se destinam. 
        Se você recebeu este e-mail por engano, por favor, notifique o remetente imediatamente e exclua a mensagem de forma permanente. 
        É proibida a disseminação, distribuição ou cópia não autorizada deste e-mail. 
        O conteúdo desta mensagem reflete unicamente a opinião do autor e pode não representar a opinião oficial da empresa.</p>";
}

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

    <!-- Summernote CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">

    <!-- Summernote JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>


    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .invisivel {
            display: none;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#txtAssinatura').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture']],
                    ['view', ['codeview']]
                ]
            });
        });


        function copyToClipboard(texto) {
            navigator.clipboard.writeText(texto)
                .then(() => {
                    alert("Texto copiado para a área de transferência!");
                })
                .catch(err => {
                    console.error('Erro ao copiar texto: ', err);
                });
        }

        function salvar_email() {
            var txt_assinatura = $('#txtAssinatura').summernote('code');

            const mensagem = $("#divMensagem");
            $.post("../includes/rh_config_aj1.php", {
                    texto: txt_assinatura
                },
                function(retorno) {
                    //alert( retorno );
                    mensagem.show();
                    mensagem.html(retorno);
                    const myTimeout = setTimeout(function() {
                        location.href = 'config.php';
                    }, 2000);
                }
            );
        }
    </script>
</head>

<body class="sb-nav-fixed">
    <div id="layoutSidenav">
        <div id="layoutSidenav_content">
            <main class="container">
                <!-- Aqui COMEÇA o conteúdo da página -->
                <div class="row mt-2">
                    <!-- PRIMEIRA COLUNA -->
                    <div class="col-sm-6">
                        <!-- ASSINATURA DO E-MAIL -->
                        <div class="card mb-3" style='height: 460px'>
                            <div class="card-header h5">Assinatura do meu e-Mail</div>
                            <div class="card-body">
                                <textarea name="txtAssinatura" id="txtAssinatura" class='form-control mb-1' rows='8'><?php echo $txt_assinatura; ?></textarea>
                                <button class="btn btn-outline-primary w-100" onClick='salvar_email()'><i class="fa-regular fa-floppy-disk"></i> Salvar</button>
                            </div>
                        </div>
                        <div class="invisivel text-center h4" id='divMensagem'></div>
                        <!-- CONTEÚDO DA SESSÃO -->
                        <div class="card">
                            <div class="card-header h5">Conteúdo da Sessão</div>
                            <div class="mt-3">
                                <table class="table table-bordered table-sm table-striped">
                                    <th>Variável</th>
                                    <th>Conteúdo</th>
                                    <?php
                                    foreach ($_SESSION as $key => $value) {
                                        $wrappedValue = wordwrap($value, 30, "<br>", true); // Quebra o texto a cada 30 caracteres
                                        $link = " <a onClick='copyToClipboard( " . '"' . $value . '"' . " )'><i class='fa-regular fa-clipboard text-warning cursor-pointer'></i></a>";
                                        echo "<tr><td class='p-2'>" . $key . '</td><td with="50%" class="p-2">'  . $wrappedValue . $link . "</td></tr>";
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- SEGUNDA COLUNA -->
                    <div class=" col-sm-6">
                        <!-- CONFIGURAÇÕES DA HOSPEDAGEM -->
                        <div class="card">
                            <div class="card-header h5">Configurações da Hospedagem</div>
                            <div class="mt-3">
                                <table class="table table-bordered table-sm table-striped">
                                    <th>Variável</th>
                                    <th>Conteúdo</th>
                                    <?php
                                    foreach ($_SERVER as $key => $value) {
                                        $wrappedValue = wordwrap($value, 30, "<br>", true); // Quebra o texto a cada 30 caracteres
                                        echo "<tr><td class='p-2'>" . $key . '</td><td with="50%" class="p-2">'  . $wrappedValue . "</td></tr>";
                                    } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Aqui Termina o conteúdo da página -->
            </main>
        </div>
    </div>
    <script src="js/scripts.js"></script>
</body>

</html>