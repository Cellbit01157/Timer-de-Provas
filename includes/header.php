<?php
/**
 * Cabeçalho Padrão do Sistema
 * Timer de Provas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/toast.php';

$usuarioLogado = getCurrentUser();
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Estilos Customizados -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-full flex flex-col antialiased">

    <!-- Renderizador de Mensagens Toast -->
    <?php renderToastContainer(); ?>

    <!-- Barra de Navegação Superior (Mobile-First) -->
    <header class="bg-slate-800/90 backdrop-blur border-b border-slate-700/80 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logotipo / Nome do Sistema -->
                <div class="flex items-center gap-3">
                    <a href="<?= $usuarioLogado ? 'timer.php' : 'index.php' ?>" class="flex items-center gap-2 group">
                        <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-xl shadow-lg shadow-blue-500/30 group-hover:scale-105 transition">⏱️</span>
                        <div>
                            <span class="font-bold text-lg text-white tracking-tight flex items-center gap-1.5">
                                <?= APP_NAME ?>
                            </span>
                            <span class="block text-[10px] text-slate-400 -mt-1 font-medium uppercase tracking-wider">Gestão e Alertas</span>
                        </div>
                    </a>
                </div>

                <!-- Menu Desktop -->
                <?php if ($usuarioLogado): ?>
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="timer.php" 
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition <?= $paginaAtual === 'timer.php' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' ?>">
                            ⏰ Timer da Prova
                        </a>
                        <a href="provas.php" 
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition <?= $paginaAtual === 'provas.php' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' ?>">
                            📋 Provas & Entregas
                        </a>
                        <a href="historico.php" 
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition <?= $paginaAtual === 'historico.php' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' ?>">
                            🛡️ Auditoria & Logs
                        </a>
                    </nav>

                    <!-- Identificação do Usuário e Logout -->
                    <div class="hidden md:flex items-center gap-3 pl-4 border-l border-slate-700">
                        <div class="text-right">
                            <span class="block text-xs font-semibold text-slate-200"><?= htmlspecialchars($usuarioLogado['nome']) ?></span>
                            <span class="block text-[11px] text-slate-400">Coordenador</span>
                        </div>
                        <a href="actions/auth_logout.php" 
                           title="Sair do Sistema"
                           class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="hidden md:flex items-center gap-2">
                        <a href="login.php" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700/50 transition">
                            Entrar
                        </a>
                        <a href="cadastro.php" class="px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-600/30 transition">
                            Cadastre-se
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Botão de Menu Mobile -->
                <div class="flex md:hidden">
                    <button type="button" id="mobile-menu-btn" 
                            class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition"
                            aria-label="Abrir menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Menu Mobile Expandível -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-700 px-4 pt-3 pb-4 space-y-2 bg-slate-800">
            <?php if ($usuarioLogado): ?>
                <div class="pb-2 border-b border-slate-700">
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($usuarioLogado['nome']) ?></p>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($usuarioLogado['email']) ?></p>
                </div>
                <a href="timer.php" class="block px-3 py-2 rounded-lg text-base font-medium <?= $paginaAtual === 'timer.php' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700' ?>">
                    ⏰ Timer da Prova
                </a>
                <a href="provas.php" class="block px-3 py-2 rounded-lg text-base font-medium <?= $paginaAtual === 'provas.php' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700' ?>">
                    📋 Provas & Entregas
                </a>
                <a href="historico.php" class="block px-3 py-2 rounded-lg text-base font-medium <?= $paginaAtual === 'historico.php' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700' ?>">
                    🛡️ Auditoria & Logs
                </a>
                <a href="actions/auth_logout.php" class="block px-3 py-2 rounded-lg text-base font-medium text-rose-400 hover:bg-rose-500/10">
                    🚪 Sair do Sistema
                </a>
            <?php else: ?>
                <a href="login.php" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-300 hover:bg-slate-700">
                    Entrar
                </a>
                <a href="cadastro.php" class="block px-3 py-2 rounded-lg text-base font-semibold bg-blue-600 text-white text-center">
                    Criar Conta
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="flex-1">
