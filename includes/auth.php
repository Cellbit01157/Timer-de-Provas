<?php
/**
 * Módulo de Autenticação e Sessões Nativas
 * Timer de Provas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/logger.php';

/**
 * Verifica se há um usuário autenticado na sessão
 *
 * @return bool
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['usuario_id']);
}

/**
 * Protege páginas restritas redirecionando para login caso não autenticado
 *
 * @param string|null $returnUrl
 */
function requireAuth(?string $returnUrl = null): void {
    if (!isLoggedIn()) {
        setToast('warning', 'Faça login para acessar esta página.', 'Acesso Restrito');
        $redirect = defined('BASE_URL') ? BASE_URL . '/login.php' : 'login.php';
        if ($returnUrl) {
            $redirect .= '?retorno=' . urlencode($returnUrl);
        }
        header("Location: " . $redirect);
        exit;
    }
}

/**
 * Retorna os dados do usuário atual na sessão
 *
 * @return array|null
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['usuario_id'],
        'nome'  => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'cpf'   => $_SESSION['usuario_cpf'] ?? '',
    ];
}

/**
 * Registra o login na sessão e gera log de auditoria
 *
 * @param array $usuario Dados do usuário do banco
 */
function loginUser(array $usuario): void {
    // Previne Session Fixation regenerando o ID da sessão
    session_regenerate_id(true);

    $_SESSION['usuario_id']    = (int) $usuario['id'];
    $_SESSION['usuario_nome']  = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_cpf']   = $usuario['cpf'];
    $_SESSION['login_time']    = time();

    logAction($usuario['id'], 'LOGIN', 'Coordenador realizou login no sistema com sucesso.');
}

/**
 * Encerra a sessão do usuário e gera log de auditoria
 */
function logoutUser(): void {
    if (isLoggedIn()) {
        $userId = $_SESSION['usuario_id'];
        logAction($userId, 'LOGOUT', 'Coordenador encerrou a sessão.');
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Função utilitária para validação e limpeza de CPF brasileiro
 *
 * @param string $cpf
 * @return bool
 */
function validateCPF(string $cpf): bool {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Deve possuir 11 dígitos
    if (strlen($cpf) !== 11) {
        return false;
    }

    // Verifica sequências repetidas como 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Validação do 1º dígito verificador
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}

/**
 * Formata um CPF numérico para o padrão 000.000.000-00
 *
 * @param string $cpf
 * @return string
 */
function formatCPF(string $cpf): string {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
    return $cpf;
}
