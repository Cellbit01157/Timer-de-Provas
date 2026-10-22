<?php
/**
 * Página Inicial / Apresentação
 * Timer de Provas
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Se o coordenador já estiver autenticado, direciona imediatamente para o timer
if (isLoggedIn()) {
    header('Location: timer.php');
    exit;
}

$pageTitle = "Controle de Tempo e Alertas para Provas";
require_once __DIR__ . '/includes/header.php';
?>

<div class="relative overflow-hidden py-12 sm:py-20 lg:py-28">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        
        <!-- Badge Destaque -->
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold mb-6 animate-pulse-subtle">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            Sistema Oficial para Avaliações e Concursos
        </div>

        <!-- Título Principal -->
        <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight leading-tight">
            Gerencie o tempo das suas provas com <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">precisão e alertas sonoros</span>.
        </h1>

        <!-- Descrição -->
        <p class="mt-6 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto font-normal leading-relaxed">
            Configure o tempo total, defina avisos sonoros e visuais customizados para os estudantes, registre as posições de entrega e mantenha a auditoria completa de cada segundo.
        </p>

        <!-- Botões de Ação (Mobile-First) -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto">
            <a href="login.php" 
               class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-base shadow-xl shadow-blue-600/30 transition transform hover:-translate-y-0.5 text-center">
                Acessar Timer da Prova →
            </a>
            <a href="cadastro.php" 
               class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-base transition text-center">
                Cadastrar Coordenador
            </a>
        </div>

        <!-- Recursos em Cards -->
        <div class="mt-16 sm:mt-24 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700/60 backdrop-blur">
                <span class="text-3xl mb-4 block">🔔</span>
                <h3 class="text-lg font-bold text-white mb-1">Alertas Audiovisuais</h3>
                <p class="text-sm text-slate-400">
                    Defina avisos sonoros para quando faltarem 15, 10 ou 5 minutos, com bipes sintetizados ou sinos e avisos visuais na tela.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700/60 backdrop-blur">
                <span class="text-3xl mb-4 block">📋</span>
                <h3 class="text-lg font-bold text-white mb-1">Registro de Posições</h3>
                <p class="text-sm text-slate-400">
                    Registre a ordem de entrega dos cadernos de prova (1º, 2º, 3º...) e armazene o histórico oficial da aplicação.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700/60 backdrop-blur">
                <span class="text-3xl mb-4 block">🛡️</span>
                <h3 class="text-lg font-bold text-white mb-1">Auditoria & Logs</h3>
                <p class="text-sm text-slate-400">
                    Rastreabilidade completa: cada início, pausa, ajuste e término fica registrado para conformidade institucional.
                </p>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
