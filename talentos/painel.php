<?php
//
//- painel.php | Portal do Candidato | Tela intermediária pós-login: minhas candidaturas
//- (C)haia, 2026-08-27
//

session_start();

if (empty($_SESSION['candidato_idPessoa'])) {
    header('Location: index.php');
    exit;
}

include "../app/includes/conexao_gerar.php";

$pessoa_id = (int) $_SESSION['candidato_idPessoa'];

$stmt = $conn->prepare("SELECT nome FROM rh_pessoas WHERE idPessoa = :idPessoa");
$stmt->bindParam(':idPessoa', $pessoa_id, PDO::PARAM_INT);
$stmt->execute();
$nome = $stmt->fetchColumn() ?: '';
$primeiroNome = trim(strtok((string) $nome, ' '));

$sql = "SELECT VC.criado_em, V.id, V.identificador, C.nome AS cargo_ds
        FROM rs_vagas_candidaturas VC
        INNER JOIN rs_vagas V ON V.id = VC.vaga_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        WHERE VC.pessoa_id = :pessoa_id
        ORDER BY VC.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
$stmt->execute();
$candidaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talentos GERAR | Meu Painel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bg-talentos {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">

    <header class="bg-talentos py-8 px-4 shadow-sm">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div>
                <span class="bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">Portal do Candidato</span>
                <h1 class="text-3xl font-extrabold text-gray-900 mt-3">
                    Olá, <?= htmlspecialchars($primeiroNome ?: 'candidato') ?>!
                </h1>
                <p class="text-gray-600 mt-1">Acompanhe suas candidaturas ou atualize seu currículo.</p>
            </div>
            <a href="logout.php" class="text-sm text-gray-500 hover:text-gray-800 transition" title="Sair">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Sair
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-10">

        <!-- Ação principal: atualizar currículo -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-file-pen"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Meu Currículo</h3>
                    <p class="text-sm text-gray-600">Mantenha seus dados, experiências e formação em dia.</p>
                </div>
            </div>
            <a href="candidatos.php" class="bg-gray-900 text-white font-bold px-6 py-3 rounded-xl hover:bg-black transition-all shrink-0">
                Atualizar Currículo
            </a>
        </div>

        <!-- Minhas candidaturas -->
        <h2 class="text-lg font-bold text-gray-800 mb-4">Minhas Candidaturas</h2>

        <?php if (empty($candidaturas)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
            <i class="fa-solid fa-briefcase text-3xl text-gray-300 mb-3 block"></i>
            <p class="text-gray-600 mb-4">Você ainda não se candidatou a nenhuma vaga.</p>
            <a href="../vagas/index.php" class="inline-block bg-green-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-green-700 transition-all">
                Ver Vagas Abertas
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($candidaturas as $c):
                $titulo = $c['identificador'] ?: ($c['cargo_ds'] ?: 'Vaga #' . $c['id']);
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h4 class="font-bold text-gray-800"><?= htmlspecialchars($titulo) ?></h4>
                    <?php if (!empty($c['cargo_ds']) && $c['cargo_ds'] !== $titulo): ?>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($c['cargo_ds']) ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fa-solid fa-calendar-days me-1"></i>Candidatura enviada em <?= date('d/m/Y', strtotime($c['criado_em'])) ?>
                    </p>
                </div>
                <a href="../vagas/vaga_perfil.php?id=<?= (int) $c['id'] ?>&from=painel" class="text-green-600 font-semibold text-sm hover:underline shrink-0">
                    Ver vaga <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </main>

</body>

</html>
