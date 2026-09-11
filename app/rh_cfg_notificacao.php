<?php
//
// - rh_config.php - Mostra Configurações da Seção do Usuário
// - (C) Chaia, 01/08/2023
//

$idModulo = 11; // rh_condfig.php

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: login.php');
    exit();
} else {
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
    f_log("CON", "Consulta Configurações", "*", $idModulo, 0);
}

$idUsuario = $_SESSION['idUsuario'];
$nmUsuario = $_SESSION['nmUsuario'];

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Minhas configurações" />
    <meta name="author" content="LAChaia" />
    <title>GERAR</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />

    <!-- Inclua os arquivos do DataTables -->
    <script data-cfasync="false" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .invisivel {
            display: none;
        }

        .cartao {
            height: 400px;
        }
    </style>

</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">

            <main class="container-fluid mt-1">
                <!-- Aqui COMEÇA o conteúdo da página -->
                <div class="bg-primary text-white d-flex align-items-center justify-content-center" style="min-height: 70px; width: 100%;">
                    <div class="h3 m-0">USUÁRIOS × Notificação de Eventos</div>
                </div>

                <div class="row m-4 justify-content-center">
                    <div class="col-sm-5">
                        <label><b>Selecione o Usuário a ser notificado</b></label>
                        <?= seletor_usuario() ?>
                    </div>
                </div>

                <!-- Cards lado a lado -->
                <div class="d-flex justify-content-center gap-4">
                    <!-- Card: Eventos disponíveis -->
                    <div class="card" style="width: 400px;">
                        <div class="card-header bg-secondary text-white text-center">
                            Eventos disponíveis
                        </div>
                        <div id="eventosDisponiveis" class="list-group p-2 min-vh-25" style="min-height: 400px;">
                            <!-- Itens inseridos dinamicamente -->
                        </div>
                    </div>

                    <!-- Card: Eventos atribuídos -->
                    <div class="card" style="width: 400px;">
                        <div class="card-header bg-primary text-white text-center">
                            Notificações atribuídas
                        </div>
                        <div id="eventosAtribuidos" class="list-group p-2 min-vh-25" style="min-height: 400px;">
                            <!-- Itens inseridos dinamicamente -->
                        </div>
                    </div>

                </div>

                <div class="row mt-4 justify-content-center">
                    <div class="col-sm-6">
                        <a href='index.php' class="btn btn-outline-primary w-100">Voltar</a>
                    </div>
                </div>

                <!-- Aqui Termina o conteúdo da página -->
            </main>

            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script data-cfasync="false" src="js/scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const disponiveis = document.getElementById('eventosDisponiveis');
            const atribuidos = document.getElementById('eventosAtribuidos');
            const seletor = document.getElementById('idUsuario');

            let usuarioAtual = null;

            // Inicializa o SortableJS nos dois cards
            [disponiveis, atribuidos].forEach((el, idx) => {
                Sortable.create(el, {
                    group: 'eventos',
                    animation: 150,
                    onAdd: function(evt) {
                        if (!usuarioAtual) return;

                        const eventoID = evt.item.dataset.id;
                        const origem = evt.from.id;
                        const destino = evt.to.id;
                        const acao = destino === 'eventosAtribuidos' ? 'adicionar' : 'remover';

                        // Envia atualização para o servidor
                        fetch('includes/rh_notificacoes_aj3.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    usuario: usuarioAtual,
                                    evento: eventoID,
                                    acao: acao
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (!data.sucesso) {
                                    alert('Erro ao atualizar!');
                                    evt.from.appendChild(evt.item); // desfaz
                                }
                            })
                            .catch(() => {
                                alert('Erro de conexão.');
                                evt.from.appendChild(evt.item);
                            });
                    }
                });
            });

            // Quando o usuário for selecionado
            seletor.addEventListener('change', () => {
                //
                usuarioAtual = seletor.value;
                if (!usuarioAtual) return;

                // Buscar eventos do usuário (simulação via fetch, troque por sua lógica)
                fetch('includes/rh_notificacoes_aj2.php?usuario=' + usuarioAtual)
                    .then(res => res.json())
                    .then(data => {
                        // Limpa os dois cards
                        disponiveis.innerHTML = '';
                        atribuidos.innerHTML = '';

                        // Popula os cards
                        data.disponiveis.forEach(ev => {
                            disponiveis.appendChild(criarItemEvento(ev));
                        });

                        data.atribuidos.forEach(ev => {
                            atribuidos.appendChild(criarItemEvento(ev));
                        });
                    });
            });

            function criarItemEvento(evento) {
                const div = document.createElement('div');
                div.className = 'list-group-item list-group-item-action mb-1';
                div.textContent = evento.nome;
                div.dataset.id = evento.id;
                return div;
            }
        });
    </script>
</body>

</html>
<?PHP

function seletor_usuario()
{
    include "includes/conexao_gerar.php";
    $sql = "SELECT idUsuario, login
                FROM rh_usuarios 
                WHERE idUsuarioGrupo in (1,3,9) and ativo=1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    $select = "<select class='form-select fs-13' id='idUsuario' name='idUsuario'>";
    $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $select .= "<option value='$idUsuario'>$login</option>";
    }
    $select .= "</select>";
    echo $select;
}
