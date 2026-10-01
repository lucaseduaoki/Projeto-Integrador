<?php

// ============================================================================
// CONFIGURAÇÕES DE AMBIENTE
// ============================================================================

define('DEV_ENVIRONMENT', true);

// Fuso do sistema (Brasil). Sem isto o PHP usa UTC e "hoje" vira amanhã à noite,
// o que quebra regras como "a data não pode ser anterior a hoje".
date_default_timezone_set('America/Sao_Paulo');

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
// Cookie "secure" só é enviado em HTTPS; em http://localhost ele seria descartado
ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0);
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// CONFIGURAÇÃO DO SISTEMA
// ============================================================================

define('APP_NAME', 'FreelaJá');
// Segue o host/porta da requisição, para funcionar em qualquer porta (8000, 8080...)
$esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('URL_BASE', getenv('URL_BASE') ?: $esquema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8080'));

// ============================================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'freelaja');
define('DB_USER', 'freelaja');
define('DB_PASS', 'root');
