<?php
/**
 * Ação de Login do Coordenador
 * Timer de Provas
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$loginIdentificador = trim($_POST['identificador'] ?? ''); // Pode ser E-mail ou CPF
$senha              = $_POST['senha'] ?? '';

if (empty($loginIdentificador) || empty($senha)) {
    setToast('error', 'Por favor, informe seu E-mail ou CPF e sua senha de acesso.', 'Dados Incompletos');
    header('Location: ../login.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Se o identificador contiver números e não for e-mail, testa também o CPF formatado e desformatado
    $cpfLimpo = preg_replace('/[^0-9]/', '', $loginIdentificador);
    $cpfFormatado = (strlen($cpfLimpo) === 11) ? formatCPF($cpfLimpo) : $loginIdentificador;

    $stmt = $pdo->prepare("
        SELECT id, nome, email, cpf, senha, status 
        FROM usuarios 
        WHERE email = :identificador 
           OR cpf = :identificador 
           OR cpf = :cpf_formatado
        LIMIT 1
    ");
    $stmt->execute([
        ':identificador'   => $loginIdentificador,
        ':cpf_formatado'   => $cpfFormatado
    ]);

    $usuario = $stmt->fetch();

    if (!$usuario) {
        setToast('error', 'Credenciais inválidas. Verifique seu e-mail/CPF e tente novamente.', 'Acesso Negado');
        header('Location: ../login.php');
        exit;
    }

    // Verificação de status do usuário
    if ($usuario['status'] !== 'ativo') {
        setToast('warning', 'Sua conta de coordenador está inativa ou pendente.', 'Conta Inativa');
        header('Location: ../login.php');
        exit;
    }

    // Regra CORE: Validação de senha criptografada com bcrypt
    if (!password_verify($senha, $usuario['senha'])) {
        // Log de tentativa frustrada para auditoria de segurança
        logAction($usuario['id'], 'LOGIN_FALHA', "Tentativa com senha incorreta para: {$loginIdentificador}");
        
        setToast('error', 'Senha incorreta. Por favor, tente novamente.', 'Acesso Negado');
        header('Location: ../login.php');
        exit;
    }

    // Login efetuado com sucesso
    loginUser($usuario);

    setToast('success', "Olá, {$usuario['nome']}! Seja bem-vindo ao painel do timer.", 'Login Autorizado');

    // Regra CORE: "Após a validação dos dados, o sistema deve direcioná-lo para o timer da prova."
    header('Location: ../timer.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro no login: " . $e->getMessage());
    setToast('error', 'Ocorreu um erro de comunicação com a base de dados. Tente novamente.', 'Erro no Servidor');
    header('Location: ../login.php');
    exit;
}
