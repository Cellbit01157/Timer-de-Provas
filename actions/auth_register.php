<?php
/**
 * Ação de Cadastro de Novo Coordenador
 * Timer de Provas
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cadastro.php');
    exit;
}

$nome            = trim($_POST['nome'] ?? '');
$email           = trim($_POST['email'] ?? '');
$cpfRaw          = trim($_POST['cpf'] ?? '');
$senha           = $_POST['senha'] ?? '';
$confirmarSenha  = $_POST['confirmar_senha'] ?? '';

// Validação dos Campos Obrigatórios
if (empty($nome) || empty($email) || empty($cpfRaw) || empty($senha)) {
    setToast('error', 'Por favor, preencha todos os campos obrigatórios.', 'Campos Incompletos');
    header('Location: ../cadastro.php');
    exit;
}

// Validação de E-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setToast('error', 'O endereço de e-mail informado não é válido.', 'E-mail Inválido');
    header('Location: ../cadastro.php');
    exit;
}

// Validação e formatação de CPF
if (!validateCPF($cpfRaw)) {
    setToast('error', 'O CPF informado é inválido. Verifique os dígitos digitados.', 'CPF Inválido');
    header('Location: ../cadastro.php');
    exit;
}
$cpfFormatado = formatCPF($cpfRaw);

// Validação de Senha e Confirmação
if (strlen($senha) < 6) {
    setToast('error', 'A senha de acesso deve ter pelo menos 6 caracteres.', 'Senha Fraca');
    header('Location: ../cadastro.php');
    exit;
}

if ($senha !== $confirmarSenha) {
    setToast('error', 'A confirmação de senha não coincide com a senha digitada.', 'Senhas Divergentes');
    header('Location: ../cadastro.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Verifica se e-mail ou CPF já estão cadastrados
    $stmtCheck = $pdo->prepare("SELECT id, email, cpf FROM usuarios WHERE email = :email OR cpf = :cpf LIMIT 1");
    $stmtCheck->execute([':email' => $email, ':cpf' => $cpfFormatado]);
    $existente = $stmtCheck->fetch();

    if ($existente) {
        if (strtolower($existente['email']) === strtolower($email)) {
            setToast('error', 'Este e-mail já está cadastrado no sistema.', 'E-mail Duplicado');
        } else {
            setToast('error', 'Este CPF já está cadastrado no sistema.', 'CPF Duplicado');
        }
        header('Location: ../cadastro.php');
        exit;
    }

    // Regra CORE: Tratar senhas de usuários com hash bcrypt
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

    // Inserção com Prepared Statement seguro via PDO
    $stmtInsert = $pdo->prepare("
        INSERT INTO usuarios (nome, email, cpf, senha, status, criado_em)
        VALUES (:nome, :email, :cpf, :senha, 'ativo', CURRENT_TIMESTAMP)
    ");
    $stmtInsert->execute([
        ':nome'  => $nome,
        ':email' => $email,
        ':cpf'   => $cpfFormatado,
        ':senha' => $senhaHash
    ]);

    $novoUsuarioId = (int) $pdo->lastInsertId();

    // Cria configuração padrão de timer para o coordenador
    $stmtConfig = $pdo->prepare("
        INSERT INTO provas_configuracoes (usuario_id, titulo, tempo_total_minutos, tempo_alerta_minutos, intervalo_alerta_minutos, emitir_som, emitir_aviso_visual, tipo_som)
        VALUES (:uid, 'Avaliação Padrão', 120, 15, 5, 1, 1, 'beep_duplo')
    ");
    $stmtConfig->execute([':uid' => $novoUsuarioId]);

    // Regra CORE: Disparo de mensagem de confirmação por e-mail
    sendWelcomeEmail($nome, $email, $cpfFormatado);

    // Regra CORE: Log de auditoria
    logAction($novoUsuarioId, 'CADASTRO', "Coordenador cadastrado com sucesso. E-mail: {$email}, CPF: {$cpfFormatado}");

    setToast('success', 'Cadastro realizado com sucesso! Enviamos a confirmação por e-mail. Faça seu login.', 'Bem-vindo!');
    header('Location: ../login.php');
    exit;

} catch (Exception $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    setToast('error', 'Ocorreu um erro no servidor ao processar o cadastro. Tente novamente mais tarde.', 'Erro Interno');
    header('Location: ../cadastro.php');
    exit;
}
