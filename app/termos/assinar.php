<?php
// assinar.php | Tela publica de assinatura de termo

session_start();

$idModulo = 19; // Equipamentos

include_once "../includes/conexao_gerar.php";

$token = filter_input(INPUT_GET, 'token', FILTER_DEFAULT);

if (empty($token)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger"><strong>Erro!</strong> Faltou parametros!</div>'
    ];
    die(json_encode($retorno, JSON_UNESCAPED_UNICODE));
}

$sql = "SELECT * FROM rh_equip_termos WHERE token = :token LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':token', $token, PDO::PARAM_STR);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-warning"><strong>Atenção!</strong> Token inválido ou não encontrado.</div>'
    ];
    die(json_encode($retorno, JSON_UNESCAPED_UNICODE));
}

$termo = $row['termo_html'];
$idTermo = $row['id'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Assinatura do Termo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .custom-width {
            max-width: 400px;
        }
    </style>

</head>

<body class="bg-light">

    <div class="container my-5" id='divPrincipal'>
        <div class="card shadow-lg">
            <div class="card-header text-center bg-primary text-white">
                <h4>Termo de Responsabilidade</h4>
            </div>
            <div class="card-body" style="max-height:600px; overflow-y:auto;">
                <?= html_entity_decode($termo) ?>
            </div>
            <div class="card-footer text-center">
                <div class="form-check my-3 d-flex justify-content-center align-items-center">
                    <input class="form-check-input me-2" type="checkbox" id="concordo" onchange='marquei_lido()'>
                    <label class="form-check-label" for="concordo">
                        Declaro que li e concordo com o termo de responsabilidade.
                    </label>
                </div>
                <div class="d-flex justify-content-center my-3">
                    <div class="col-sm-6">
                        <button class="btn btn-outline-primary w-100" id="btnAssinar" disabled onclick='clicou_assinar()'>
                            Assinar
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div id="divObrigado" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; 
    background: rgba(0,0,0,0.5); z-index: 1050; text-align:center;">
        <div style="position: absolute; top:50%; left:50%; transform: translate(-50%, -50%);
        background:#fff; padding: 30px 50px; border-radius:10px; box-shadow: 0 0 15px rgba(0,0,0,0.3);">
            <h2>Obrigado!</h2>
            <p>O termo de responsabilidade foi assinado com sucesso.</p>
            <button class="btn btn-primary mt-3" onclick="window.location.href='https://www.google.com'">
                Fechar
            </button>
        </div>
    </div>

    <div class="modal fade" id="modalAssinar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-width">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-center">Confirmar Assinatura</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <form id="formLogin">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control text-center" id="usuario" required>
                            <input type="hidden" id='idTermo' name='idTermo' value='<?= $idTermo ?>'>
                            <input type="hidden" id='token' name='token' value='<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>'>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="senha" class="form-label">Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control text-center" id="senha" required>
                                <button type="button" class="btn btn-outline-secondary" id="toggleSenha">
                                    <i class="fa fa-eye" id="iconSenha"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id='msgAlerta' class="h5 text-center"></div>
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-success flex-fill" id="btnConfirmar" onclick='confirma_assinatura()'>Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function marquei_lido() {
            const check = document.getElementById("concordo");
            document.getElementById("btnAssinar").disabled = !check.checked;
        }

        function clicou_assinar() {
            $("#modalAssinar").modal("show");
        }

        document.getElementById("toggleSenha").addEventListener("click", function() {
            const senha = document.getElementById("senha");
            const icon = document.getElementById("iconSenha");

            if (senha.type === "password") {
                senha.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                senha.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });

        function confirma_assinatura() {
            let mensagem = $("#msgAlerta");
            let usuario = $("#usuario").val();
            let senha = $("#senha").val();
            let idTermo = $("#idTermo").val();
            let token = $("#token").val();

            if (!usuario || !senha) {
                mensagem.html("<div class='alert alert-warning'>Informe usuário e senha.</div>");
                return;
            }

            const btnConfirmar = $("#btnConfirmar");
            btnConfirmar.prop("disabled", true);

            $.ajax({
                url: "index_aj11.php",
                method: "POST",
                dataType: "json",
                data: {
                    idTermo: idTermo,
                    usuario: usuario,
                    senha: senha,
                    token: token
                }
            })
            .done(function(dados) {
                if (!dados || typeof dados !== "object") {
                    mensagem.html("<div class='alert alert-danger'>Resposta inválida do servidor.</div>");
                    return;
                }

                mensagem.html(dados.msg || "");

                if (dados.status) {
                    setTimeout(() => {
                        $("#modalAssinar").modal("hide");
                        $("#divPrincipal").hide();
                        $("#divObrigado").show();
                    }, 1200);
                }
            })
            .fail(function(xhr) {
                const detalhe = xhr && xhr.responseText ? xhr.responseText : "";
                mensagem.html("<div class='alert alert-danger'>Falha ao assinar termo.</div>");
                if (detalhe) {
                    console.error("index_aj11.php response:", detalhe);
                }
            })
            .always(function() {
                btnConfirmar.prop("disabled", false);
            });
        }
    </script>

</body>

</html>
