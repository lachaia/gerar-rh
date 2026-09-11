<?php
//
// - rh_config.php - Mostra Configurações da Seção do Usuário
// - (C) Chaia, 01/08/2023 | 02/10/2025
//

$idModulo = 13; // Colaborador

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
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

$modulo = "Config";
include 'header.php';
?>
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
<main class="main" data-bs-theme="dark">
    <!-- Top controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Configurações Ativas</h2>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href='config.php' id="btnProfileSettings"><i class="fa-solid fa-gear"></i></a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>
    <div class="big-card">

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

    </div>
</main>
<?php include 'footer.php'; ?>