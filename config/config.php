<?php
/**
 * Configurações Gerais da Aplicação
 * Timer de Provas
 */

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurações seguras de cookie de sessão
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Fuso horário padrão do Brasil
date_default_timezone_set('America/Sao_Paulo');

// Nome do Sistema
define('APP_NAME', 'Timer de Provas');
define('APP_VERSION', '1.0.0');

// Detecção dinâmica de URL Base
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Se estiver na pasta actions, ajusta o nível para a raiz
$baseDir = preg_replace('#/(actions|includes|views|assets)$#', '', $scriptDir);
$baseDir = rtrim($baseDir, '/');

define('BASE_URL', $protocol . $host . ($baseDir ? $baseDir : ''));

// Carregar helpers de toast e logger globalmente
require_once __DIR__ . '/../includes/toast.php';
