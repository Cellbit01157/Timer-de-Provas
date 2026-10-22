<?php
/**
 * Página de Login do Coordenador
 * Timer de Provas
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Redireciona se já estiver autenticado
if (isLoggedIn()) {
    header('Location: timer.php');
    exit;
}

$pageTitle = "Login do Coordenador";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-10rem)] flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="max-w-md w-full bg-slate-800/90 border border-slate-700/80 rounded-2xl shadow-2xl p-6 sm:p-8 backdrop-blur">
        
        <!-- Cabeçalho do Card -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600/10 text-blue-400 mb-3 text-2xl border border-blue-500/20">
                🔐
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Login do Coordenador</h1>
            <p class="text-sm text-slate-400 mt-1">Informe seu E-mail ou CPF para acessar o timer da prova</p>
        </div>

        <!-- Formulário de Login -->
        <form action="actions/auth_login.php" method="POST" class="space-y-5">
            <div>
                <label for="identificador" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    E-mail ou CPF
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </span>
                    <input type="text" id="identificador" name="identificador" required autofocus
                           placeholder="nome@exemplo.com ou 000.000.000-00"
                           class="w-full pl-11 pr-4 py-3 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="senha" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Senha de Acesso
                    </label>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </span>
                    <input type="password" id="senha" name="senha" required
                           placeholder="••••••••"
                           class="w-full pl-11 pr-11 py-3 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    <button type="button" id="toggle-password" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2">
                    <span>Acessar Painel do Timer</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </form>

        <!-- Dica de Acesso Rápido para Avaliador / Professor -->
        <div class="mt-6 p-3 rounded-xl bg-blue-900/20 border border-blue-700/30 text-xs text-blue-200">
            <span class="font-semibold block mb-1">💡 Acesso Demonstrativo:</span>
            E-mail: <code class="bg-blue-950/60 px-1 py-0.5 rounded text-blue-300">coordenador@etec.sp.gov.br</code><br>
            Senha: <code class="bg-blue-950/60 px-1 py-0.5 rounded text-blue-300">Admin@123</code>
        </div>

        <!-- Rodapé do Card -->
        <div class="mt-6 pt-4 border-t border-slate-700 text-center">
            <p class="text-xs text-slate-400">
                Ainda não possui cadastro? 
                <a href="cadastro.php" class="text-blue-400 font-semibold hover:underline">
                    Criar conta de coordenador
                </a>
            </p>
        </div>

    </div>
</div>

<script>
    // Alternar visibilidade da senha
    const toggleBtn = document.getElementById('toggle-password');
    const pwdInput = document.getElementById('senha');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
