<?php
/**
 * Ação de Salvamento das Configurações do Timer
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

$titulo           = trim($_POST['titulo'] ?? 'Avaliação Oficial');
$tempoTotal       = max(1, (int) ($_POST['tempo_total_minutos'] ?? 120));
$tempoAlerta      = max(1, (int) ($_POST['tempo_alerta_minutos'] ?? 15));
$intervaloAlerta  = max(1, (int) ($_POST['intervalo_alerta_minutos'] ?? 5));
$emitirSom        = isset($_POST['emitir_som']) ? 1 : 0;
$emitirAviso      = isset($_POST['emitir_aviso_visual']) ? 1 : 0;
$tipoSom          = trim($_POST['tipo_som'] ?? 'beep_duplo');

// Validação lógica: tempo de alerta não pode ser maior que o tempo total
if ($tempoAlerta >= $tempoTotal) {
    $tempoAlerta = max(1, (int) ($tempoTotal / 2));
}

try {
    $pdo = getDBConnection();

    // Compatível com MySQL e SQLite de forma 100% segura
    $stmtCheck = $pdo->prepare("SELECT id FROM provas_configuracoes WHERE usuario_id = ? LIMIT 1");
    $stmtCheck->execute([$userId]);
    $existe = $stmtCheck->fetch();

    if ($existe) {
        $stmt = $pdo->prepare("
            UPDATE provas_configuracoes 
            SET titulo = :titulo,
                tempo_total_minutos = :total,
                tempo_alerta_minutos = :alerta,
                intervalo_alerta_minutos = :intervalo,
                emitir_som = :som,
                emitir_aviso_visual = :aviso,
                tipo_som = :tipo_som,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE usuario_id = :uid
        ");
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO provas_configuracoes 
                (usuario_id, titulo, tempo_total_minutos, tempo_alerta_minutos, intervalo_alerta_minutos, emitir_som, emitir_aviso_visual, tipo_som)
            VALUES 
                (:uid, :titulo, :total, :alerta, :intervalo, :som, :aviso, :tipo_som)
        ");
    }

    $stmt->execute([
        ':uid'       => $userId,
        ':titulo'    => $titulo,
        ':total'     => $tempoTotal,
        ':alerta'    => $tempoAlerta,
        ':intervalo' => $intervaloAlerta,
        ':som'       => $emitirSom,
        ':aviso'     => $emitirAviso,
        ':tipo_som'  => $tipoSom
    ]);

    // Regra de auditoria
    logAction($userId, 'CONFIGURACAO_TIMER', "Parâmetros alterados: Total {$tempoTotal}min, Alerta aos {$tempoAlerta}min, Intervalo {$intervaloAlerta}min, Som: {$emitirSom}, Visual: {$emitirAviso}");

    setToast('success', 'Configurações do timer salvas com sucesso!', 'Timer Atualizado');
    header('Location: ../timer.php');
    exit;

} catch (Exception $e) {
    error_log("Erro ao salvar configuração: " . $e->getMessage());
    setToast('error', 'Não foi possível salvar as configurações do timer.', 'Erro');
    header('Location: ../timer.php');
    exit;
}
