<?php
#
# INCLUDE - CONFIGURAÇÃO DE IA (extração de currículo)
#
// Credenciais vêm de variáveis de ambiente (.env na raiz) — ver env.php e .env.example
require_once __DIR__ . '/env.php';

$anthropic_api_key = $_ENV['ANTHROPIC_API_KEY'];
$anthropic_model    = $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-5';

// Gemini deixado configurado, mas sem créditos - ver conversa anterior. Não está em uso.
$gemini_api_key = $_ENV['GEMINI_API_KEY'] ?? '';
$gemini_model   = $_ENV['GEMINI_MODEL'] ?? 'gemini-3.6-flash';
