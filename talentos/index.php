<?php
//
// index.php | Portal do Candidato | Login na Manutenção do Currículo
// (C)haia, 23/07/2026
//

//- Se veio de "Candidatar-se" numa vaga (vaga_perfil.php), carrega o vaga_id
//- pelo login/cadastro pra voltar direto candidatando após autenticar.
$vaga_id = filter_input(INPUT_GET, 'vaga_id', FILTER_VALIDATE_INT);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talentos GERAR | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .divider {
            width: 1px;
            background: linear-gradient(to bottom, transparent, #e5e7eb, transparent);
        }

        .bg-talentos {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div
        class="max-w-6xl w-full mx-auto flex flex-col md:flex-row min-h-[650px] shadow-2xl rounded-3xl overflow-hidden border border-gray-100 bg-white">

        <div class="md:w-1/2 p-12 bg-talentos flex flex-col justify-center relative">
            <a href="/rh"
                class="absolute top-5 left-5 w-10 h-10 rounded-xl bg-white text-green-700 shadow-sm hover:shadow-md flex items-center justify-center transition"
                title="Página principal" aria-label="Página principal"><i class="fa-solid fa-house"></i></a>
            <div class="mb-10">
                <span
                    class="bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">Oportunidades</span>
                <h1 class="text-4xl font-extrabold text-gray-900 mt-4 leading-tight">Construa sua <span
                        class="text-green-600">carreira</span> na GERAR.</h1>
                <p class="text-gray-600 mt-4 text-lg">Junte-se a um time que acredita no impacto social e no
                    desenvolvimento humano.</p>
            </div>

            <div class="space-y-8">
                <div class="flex gap-4">
                    <div
                        class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-green-600 text-xl shrink-0">
                        <i class="fa-solid fa-rocket"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Crescimento Constante</h3>
                        <p class="text-sm text-gray-600">Programas de treinamento e planos de carreira estruturados para
                            você evoluir.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div
                        class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-green-600 text-xl shrink-0">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Cultura Inclusiva</h3>
                        <p class="text-sm text-gray-600">Valorizamos a diversidade e um ambiente de trabalho acolhedor
                            para todos.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div
                        class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-green-600 text-xl shrink-0">
                        <i class="fa-solid fa-earth-americas"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Impacto Social</h3>
                        <p class="text-sm text-gray-600">Seu trabalho contribui diretamente para transformar a vida de
                            milhares de jovens.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="hidden md:block divider"></div>

        <div class="md:w-1/2 p-12 flex flex-col justify-center">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Portal do Candidato</h2>
                <p class="text-gray-500 text-sm mt-1">Acompanhe suas candidaturas e currículo</p>
            </div>

            <form action="auth.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">CPF ou E-mail</label>
                    <input type="text" placeholder="Digite seu acesso" name="login" id="login"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition">
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <label class="text-sm font-semibold text-gray-700">Senha</label>
                    </div>
                    <div class="relative">
                        <input type="password" placeholder="••••••••" name="senha" id="senha"
                            class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition">
                        <button type="button" onclick="toggleSenha()" tabindex="-1" aria-label="Mostrar senha"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <i class="fa-solid fa-eye" id="iconeSenha"></i>
                        </button>
                    </div>
                    <div class="text-end">
                        <a href="javascript:abrirModal()"
                            class="text-xs text-green-600 hover:underline font-semibold">Esqueci minha senha</a>
                    </div>

                </div>

                <button type="button" onclick="valida_login()"
                    class="w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-black transition-all shadow-lg transform active:scale-95">
                    Entrar
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-gray-100 text-center">
                <p class="text-gray-600 text-sm mb-4">Ainda não tem cadastro?</p>
                <a href="new.php<?= $vaga_id ? '?vaga_id=' . $vaga_id : '' ?>"
                    class="inline-block w-full border-2 border-green-600 text-green-600 font-bold py-3 rounded-xl hover:bg-green-600 hover:text-white transition-all text-center">
                    Criar meu Currículo agora
                </a>
                <a href="/rh" class="mt-6 text-xs text-gray-400 hover:text-gray-600 block transition">
                    <i class="fa-solid fa-arrow-left"></i> Voltar para a página inicial
                </a>
            </div>
        </div>
    </div>

    <div id="modalEsqueci"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl transform transition-all">
            <div class="text-center mb-6">
                <div
                    class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Recuperar Senha</h3>
                <p class="text-gray-500 text-sm mt-2">Digite o e-mail cadastrado e enviaremos as instruções de
                    recuperação.</p>
            </div>

            <form id="formRecuperar" onsubmit="validarERecuperar(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">E-mail</label>
                    <input type="email" id="emailRecuperacao" required placeholder="seuemail@exemplo.com"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition">
                    <span id="erroEmail" class="text-xs text-red-500 hidden mt-1 italic">Por favor, insira um e-mail
                        válido.</span>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit"
                        class="w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-black transition-all">
                        Enviar Instruções
                    </button>
                    <button type="button" onclick="fecharModal()"
                        class="w-full bg-gray-100 text-gray-600 font-semibold py-3 rounded-xl hover:bg-gray-200 transition-all">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>

        // Abre a modal
        function abrirModal() {
            document.getElementById('modalEsqueci').classList.remove('hidden');
            document.getElementById('emailRecuperacao').focus();
        }

        // Fecha a modal
        function fecharModal() {
            document.getElementById('modalEsqueci').classList.add('hidden');
            document.getElementById('formRecuperar').reset();
            document.getElementById('erroEmail').classList.add('hidden');
        }

        // Validação e Chamada da Função
        function validarERecuperar(event) {
            event.preventDefault();
            const email = document.getElementById('emailRecuperacao').value;
            const erro = document.getElementById('erroEmail');

            // Validação simples de Regex
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (regex.test(email)) {
                erro.classList.add('hidden');
                enviar_email(email);
            } else {
                erro.classList.remove('hidden');
            }
        }

        // Função que chama o seu PHP
        function enviar_email(email) {
            // Aqui você pode usar Fetch API para chamar o seu PHP futuramente
            console.log("Chamando esqueci_senha.php para o e-mail: " + email);

            // Exemplo de como você fará a chamada:

            fetch('esqueci_senha.php', {
                method: 'POST',
                body: JSON.stringify({ email: email })
            }).then(response => {
                alert('Se o e-mail existir em nossa base, você receberá um link em breve.');
                fecharModal();
            });

            alert('Solicitação enviada! Verifique sua caixa de entrada em alguns instantes.');
            fecharModal();
        }

        // Mostra/esconde a senha digitada
        function toggleSenha() {
            const campo = document.getElementById('senha');
            const icone = document.getElementById('iconeSenha');
            const vaiMostrar = campo.type === 'password';
            campo.type = vaiMostrar ? 'text' : 'password';
            icone.classList.toggle('fa-eye', !vaiMostrar);
            icone.classList.toggle('fa-eye-slash', vaiMostrar);
        }

        // Fechar modal ao clicar fora dela
        window.onclick = function (event) {
            const modal = document.getElementById('modalEsqueci');
            if (event.target == modal) {
                fecharModal();
            }
        }

        function valida_login() {
            var login = $("#login").val();
            var senha = $("#senha").val();

            if (login == "" || senha == "") {
                alert("Por favor, preencha todos os campos!");
                return false;
            }

            $.ajax({
                url: "auth.php",
                type: "POST",
                dataType: "json", // <--- O SEGREDO ESTÁ AQUI
                data: {
                    login: login,
                    senha: senha
                },
                success: function (response) {
                    // Se o dataType for 'json', o response já é um objeto
                    if (response.status === true) {
                        // auth.php já abriu a sessão do candidato (cookie) - não precisa
                        // mais repassar pessoa_id, que era o que permitia acessar o
                        // currículo de qualquer um só sabendo o ID.
                        <?php if ($vaga_id): ?>
                        window.location.href = '../vagas/vaga_perfil.php?id=<?= $vaga_id ?>&auto=1';
                        <?php else: ?>
                        window.location.href = 'painel.php';
                        <?php endif; ?>
                    } else {
                        alert(response.msg || "Login ou senha incorretos!");
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert("Erro na comunicação com o servidor.");
                }
            });
        }

    </script>

</body>

</html>