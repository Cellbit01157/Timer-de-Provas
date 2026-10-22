<?php
/**
 * Endpoint de Registro de Eventos em Tempo Real para Auditoria
 * Timer de Provas
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser ? $currentUser['id'] : null;

$acao = trim($_POST['acao'] ?? '');
$detalhes = trim($_POST['detalhes'] ?? '');

if (empty($acao)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ação não informada']);
    exit;
}

$sucesso = logAction($userId, $acao, $detalhes);

echo json_encode([
    'success' => $sucesso,
    'timestamp' => date('Y-m-d H:i:s')
]);
