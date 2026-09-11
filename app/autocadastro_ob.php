<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .card-thankyou {
            background-color: #1e1e1e;
            border-radius: 15px;
            padding: 40px 30px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 0 20px rgba(255,255,255,0.1);
            animation: fadeIn 0.8s ease;
        }
        .card-thankyou img {
            width: 100px;
            margin-bottom: 20px;
        }
        .btn-back {
            background-color: #4e79a7;
            border: none;
            padding: 10px 25px;
            font-size: 1rem;
            border-radius: 8px;
            color: #fff;
            transition: 0.3s;
        }
        .btn-back:hover {
            background-color: #3b5a75;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-check {
            font-size: 3rem;
            color: #4e79a7;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="card-thankyou">
        <img src="imagens/logo.png" alt="Logomarca">
        <div class="icon-check">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2>Obrigado!</h2>
        <p class="mb-4">Seu formulário foi enviado com sucesso. 🎉<br>
        Em breve, entraremos em contato.</p>
    </div>

</body>
</html>
