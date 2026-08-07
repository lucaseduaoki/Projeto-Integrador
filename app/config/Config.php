<?php

// ============================================================================
// CONFIGURAÇÕES DE AMBIENTE
// ============================================================================

define('DEV_ENVIRONMENT', true);

if (DEV_ENVIRONMENT == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

// ============================================================================
// CONFIGURAÇÕES DE SEGURANÇA - SESSION
// ============================================================================

// Configurar sessão ANTES de session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // Use 0 em desenvolvimento sem HTTPS
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// CONFIGURAÇÃO DO SISTEMA
// ============================================================================

define('APP_NAME', 'FreelaJá');
define('URL_BASE', 'http://localhost:8080');

// ============================================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================================================
function carregarEnv(string $arquivo): void
{
    if (!file_exists($arquivo)) {
        return;
    }

    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '' || str_starts_with($linha, '#')) {
            continue;
        }

        [$chave, $valor] = array_pad(explode('=', $linha, 2), 2, '');

        $chave = trim($chave);
        $valor = trim($valor);

        putenv("$chave=$valor");
    }
}

carregarEnv(__DIR__ . '/../../.env');
error_log("[CONFIG] DB_NAME antes: " . (getenv('DB_NAME') ?: 'VAZIO'));
error_log("[CONFIG] DB_HOST antes: " . (getenv('DB_HOST') ?: 'VAZIO'));
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('MYSQL_DATABASE'));
define('DB_USER', getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root'));
define('DB_PASS', getenv('DB_PASSWORD') ?: (getenv('MYSQL_PASSWORD')));
error_log("[CONFIG] DB_NAME depois: " . DB_NAME ?: 'VAZIO');
error_log("[CONFIG] DB_HOST depois: " . DB_HOST ?: 'VAZIO');
