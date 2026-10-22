<?php
/**
 * Ação para Salvar Resultado e Posições da Prova Concluída
 * Timer de Provas
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../timer.php');
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

$titulo          = trim($_POST['titulo'] ?? 'Avaliação Oficial');
$totalSegundos   = (int) ($_POST['tempo_total_segundos'] ?? 7200);
$decorridoSeg    = (int) ($_POST['tempo_decorrido_segundos'] ?? 0);
$status          = in_array($_POST['status'] ?? '', ['concluida', 'interrompida', 'em_andamento']) ? $_POST['status'] : 'concluida';
$posicoesJson    = $_POST['resultado_posicoes'] ?? '[]';
$observacoes     = trim($_POST['observacoes'] ?? '');

// Valida JSON de posições
$posicoesArray = json_decode($posicoesJson, true);
if (!is_array($posicoesArray)) {
    $posicoesJson = '[]';
}

// Data de início calculada no PHP (compatível com MySQL e SQLite)
$iniciadoEm = date('Y-m-d H:i:s', time() - $decorridoSeg);
$finalizadoEm = date('Y-m-d H:i:s');

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        INSERT INTO provas_historico 
            (usuario_id, titulo, tempo_total_segundos, tempo_decorrido_segundos, status, resultado_posicoes, iniciado_em, finalizado_em, observacoes, criado_em)
        VALUES 
            (:uid, :titulo, :total_sec, :elapsed_sec, :status, :posicoes, :iniciado_em, :finalizado_em, :obs, CURRENT_TIMESTAMP)
    ");

    $stmt->execute([
        ':uid'           => $userId,
        ':titulo'        => $titulo,
        ':total_sec'     => $totalSegundos,
        ':elapsed_sec'   => $decorridoSeg,
        ':status'        => $status,
        ':posicoes'      => $posicoesJson,
        ':iniciado_em'   => $iniciadoEm,
        ':finalizado_em' => $finalizadoEm,
        ':obs'           => $observacoes
    ]);

    $provaId = (int) $pdo->lastInsertId();
    $totalCandidatos = is_array($posicoesArray) ? count($posicoesArray) : 0;

    // Regra de auditoria
    logAction(
        $userId, 
        'FINALIZOU_PROVA', 
        "Prova ID #{$provaId} ('{$titulo}') finalizada com status '{$status}'. {$totalCandidatos} entregas/posições registradas."
    );

    setToast('success', "A prova foi finalizada e o relatório com {$totalCandidatos} entrega(s) foi salvo no histórico!", 'Prova Registrada');
    header('Location: ../provas.php');
    exit;

} catch (Exception $e) {
    error_log("Erro ao salvar histórico de prova: " . $e->getMessage());
    setToast('error', 'Ocorreu um erro ao salvar o resultado da prova.', 'Erro');
    header('Location: ../timer.php');
    exit;
}
