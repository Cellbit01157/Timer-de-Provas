<?php
/**
 * Página de Cadastro do Coordenador
 * Timer de Provas
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: timer.php');
    exit;
}

$pageTitle = "Cadastro de Coordenador";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-10rem)] flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="max-w-lg w-full bg-slate-800/90 border border-slate-700/80 rounded-2xl shadow-2xl p-6 sm:p-8 backdrop-blur">
        
        <!-- Cabeçalho do Card -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600/10 text-blue-400 mb-3 text-2xl border border-blue-500/20">
                📝
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Cadastro de Coordenador</h1>
            <p class="text-sm text-slate-400 mt-1">Preencha os dados oficiais para gerenciar os timers de avaliação</p>
        </div>

        <!-- Formulário de Cadastro -->
        <form action="actions/auth_register.php" method="POST" class="space-y-4" id="form-cadastro">
            
            <!-- Nome Completo -->
            <div>
                <label for="nome" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Nome Completo <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="nome" name="nome" required autofocus
                       placeholder="Ex: Prof. Carlos Eduardo Souza"
                       class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
            </div>

            <!-- E-mail e CPF em Linha (Responsivo) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        E-mail Institucional <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                           placeholder="coordenador@escola.sp.gov.br"
                           class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label for="cpf" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        CPF <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="cpf" name="cpf" required data-mask="cpf" maxlength="14"
                           placeholder="000.000.000-00"
                           class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
            </div>

            <!-- Senha e Confirmação -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="senha" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Senha de Acesso <span class="text-rose-400">*</span>
                    </label>
                    <input type="password" id="senha" name="senha" required minlength="6"
                           placeholder="Mínimo 6 dígitos"
                           class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label for="confirmar_senha" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Confirmar Senha <span class="text-rose-400">*</span>
                    </label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6"
                           placeholder="Repita a senha"
                           class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2">
                    <span>Finalizar Cadastro e Receber Confirmação</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>
            </div>
        </form>

        <!-- Informação sobre e-mail de confirmação -->
        <p class="mt-4 text-center text-xs text-slate-400">
            ✉️ Uma mensagem de confirmação de cadastro será enviada automaticamente para o seu e-mail.
        </p>

        <!-- Rodapé do Card -->
        <div class="mt-6 pt-4 border-t border-slate-700 text-center">
            <p class="text-xs text-slate-400">
                Já possui uma conta cadastrada? 
                <a href="login.php" class="text-blue-400 font-semibold hover:underline">
                    Fazer login agora
                </a>
            </p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
