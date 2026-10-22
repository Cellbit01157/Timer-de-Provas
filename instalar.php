<?php
/**
 * Assistente de Instalação e Criação do Banco de Dados
 * Timer de Provas
 */

require_once __DIR__ . '/config/config.php';

$mensagemSucesso = null;
$mensagemErro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['executar_instalacao'])) {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = trim($_POST['db_name'] ?? 'timer_provas');

    try {
        // Conexão inicial com o servidor MySQL sem selecionar o banco
        $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Criação do banco de dados
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");

        // Execução do script SQL
        $sqlPath = __DIR__ . '/database.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            $pdo->exec($sqlContent);
            $mensagemSucesso = "Banco de dados '{$dbName}' e tabelas criadas com sucesso! Você já pode acessar o sistema.";
        } else {
            $mensagemErro = "Arquivo database.sql não encontrado na pasta raiz.";
        }
    } catch (PDOException $e) {
        $mensagemErro = "Erro ao conectar ao MySQL: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação do Banco de Dados - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8">
        <div class="text-center mb-6">
            <span class="inline-flex p-3 rounded-2xl bg-blue-500/10 text-blue-400 mb-3 text-3xl">⏱️</span>
            <h1 class="text-2xl font-bold text-white"><?= APP_NAME ?></h1>
            <p class="text-sm text-slate-400 mt-1">Assistente de Instalação do Banco de Dados</p>
        </div>

        <?php if ($mensagemSucesso): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
                <p class="font-semibold mb-2">🎉 Sucesso!</p>
                <p><?= htmlspecialchars($mensagemSucesso) ?></p>
                <div class="mt-4 pt-3 border-t border-emerald-500/20 flex gap-3">
                    <a href="login.php" class="w-full text-center py-2 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm transition">
                        Ir para Login
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($mensagemErro): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <p class="font-semibold mb-1">⚠️ Falha na Conexão</p>
                <p><?= htmlspecialchars($mensagemErro) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="instalar.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Host MySQL</label>
                <input type="text" name="db_host" value="127.0.0.1" required
                       class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:border-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Porta</label>
                    <input type="text" name="db_port" value="3306" required
                           class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nome do Banco</label>
                    <input type="text" name="db_name" value="timer_provas" required
                           class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Usuário</label>
                    <input type="text" name="db_user" value="root" required
                           class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Senha</label>
                    <input type="password" name="db_pass" placeholder="(Em branco)"
                           class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" name="executar_instalacao" value="1"
                        class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition shadow-lg shadow-blue-600/30">
                    Instalar e Inicializar Banco de Dados
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-700 text-center">
            <a href="login.php" class="text-xs text-slate-400 hover:text-white transition">
                ← Voltar para a tela de login
            </a>
        </div>
    </div>
</body>
</html>
