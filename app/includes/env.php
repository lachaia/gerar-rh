<?php
//
// env.php | Carrega variáveis de ambiente do .env na raiz do projeto.
// O .env real (com os valores verdadeiros) nunca é versionado — ver .gitignore
// e .env.example. Em produção, crie o .env manualmente no servidor.
//

require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_ENV['DB_HOST'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->safeLoad();
}
