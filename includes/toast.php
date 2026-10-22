<?php
/**
 * Gerenciador de Mensagens Toast (Alertas)
 * Timer de Provas
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Adiciona uma notificação toast na sessão
 *
 * @param string $tipo 'success', 'error', 'warning', 'info'
 * @param string $mensagem Conteúdo da notificação
 * @param string|null $titulo Título opcional
 */
function setToast(string $tipo, string $mensagem, ?string $titulo = null): void {
    if (!isset($_SESSION['toasts']) || !is_array($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }

    $tituloPadrao = match ($tipo) {
        'success' => 'Sucesso',
        'error'   => 'Atenção',
        'warning' => 'Alerta',
        'info'    => 'Informação',
        default   => 'Notificação'
    };

    $_SESSION['toasts'][] = [
        'tipo'     => $tipo,
        'titulo'   => $titulo ?? $tituloPadrao,
        'mensagem' => $mensagem,
        'id'       => uniqid('toast_')
    ];
}

/**
 * Recupera e remove as notificações toast acumuladas na sessão
 *
 * @return array
 */
function getToasts(): array {
    if (!isset($_SESSION['toasts']) || !is_array($_SESSION['toasts'])) {
        return [];
    }
    $toasts = $_SESSION['toasts'];
    $_SESSION['toasts'] = [];
    return $toasts;
}

/**
 * Renderiza o contêiner de toasts e os scripts para exibição
 */
function renderToastContainer(): void {
    $toasts = getToasts();
    $toastsJson = htmlspecialchars(json_encode($toasts), ENT_QUOTES, 'UTF-8');
    ?>
    <!-- Contêiner de Toasts Flutuantes -->
    <div id="toast-container" 
         class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none p-4 sm:p-0"
         data-toasts="<?= $toastsJson ?>">
    </div>
    <?php
}
