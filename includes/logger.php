<?php
/**
 * Módulo de Logs de Auditoria
 * Timer de Provas
 *
 * Registra cada ação importante executada no sistema por qualquer usuário,
 * garantindo rastreabilidade e histórico para auditorias futuras.
 */

require_once __DIR__ . '/../config/bd.php';

/**
 * Registra um evento de auditoria no banco de dados
 *
 * @param int|null $usuarioId ID do usuário ou null caso anônimo
 * @param string $acao Nome da ação realizada (ex: LOGIN, INICIO_TIMER, etc)
 * @param string|null $detalhes Informações complementares ou JSON
 * @return bool
 */
function logAction(?int $usuarioId, string $acao, ?string $detalhes = null): bool {
    try {
        $pdo = getDBConnection();
        
        $usuarioNome = null;
        if (!empty($_SESSION['usuario_nome'])) {
            $usuarioNome = $_SESSION['usuario_nome'];
        } elseif ($usuarioId !== null) {
            $stmtUser = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
            $stmtUser->execute([$usuarioId]);
            $u = $stmtUser->fetch();
            $usuarioNome = $u ? $u['nome'] : null;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }

        $userAgent = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Navegador Local', 0, 250);

        $sql = "INSERT INTO logs_auditoria (usuario_id, usuario_nome, acao, detalhes, ip, user_agent, criado_em)
                VALUES (:usuario_id, :usuario_nome, :acao, :detalhes, :ip, :user_agent, CURRENT_TIMESTAMP)";
                
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':usuario_id'   => $usuarioId,
            ':usuario_nome' => $usuarioNome,
            ':acao'         => mb_strtoupper($acao),
            ':detalhes'     => $detalhes,
            ':ip'           => $ip,
            ':user_agent'   => $userAgent
        ]);
    } catch (Exception $e) {
        error_log(sprintf("[AUDIT_LOG_ERROR] Falha ao registrar log: %s | Detalhes: %s", $acao, $e->getMessage()));
        return false;
    }
}
