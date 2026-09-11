<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Altura da Tela</title>
</head>
<body>
    <h1 id="tela">Carregando...</h1>

    <script>
        function obterAlturaTela() {
            const alturaTotal = window.screen.height;
            const alturaViewport = window.innerHeight;

            document.getElementById("tela").innerText = 
                `Altura da Tela: ${alturaTotal}px | Altura da Viewport: ${alturaViewport}px`;
        }

        obterAlturaTela(); // Chama a função ao carregar a página
        window.onresize = obterAlturaTela; // Atualiza quando a tela for redimensionada
    </script>
</body>
</html>
