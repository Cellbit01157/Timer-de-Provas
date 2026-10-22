<?php
/**
 * Painel Principal: Timer de Provas, Alertas e Registro de Posições
 * Timer de Provas
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

// Exige autenticação do coordenador
requireAuth();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Carrega as configurações salvas do coordenador
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM provas_configuracoes WHERE usuario_id = ? LIMIT 1");
$stmt->execute([$userId]);
$config = $stmt->fetch();

// Se não houver configuração salva, define valores padrão
if (!$config) {
    $config = [
        'titulo'                   => 'Avaliação Oficial',
        'tempo_total_minutos'      => 120,
        'tempo_alerta_minutos'     => 15,
        'intervalo_alerta_minutos' => 5,
        'emitir_som'               => 1,
        'emitir_aviso_visual'      => 1,
        'tipo_som'                 => 'beep_duplo'
    ];
}

$pageTitle = "Timer da Prova";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Barra Superior de Controle e Status -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight" id="display-exam-title">
                    <?= htmlspecialchars($config['titulo']) ?>
                </h1>
                <span id="timer-status-badge" class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                    Pronto para Iniciar
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Tempo Total: <span class="text-slate-200 font-medium"><?= (int)$config['tempo_total_minutos'] ?> min</span> | 
                Alerta aos: <span class="text-slate-200 font-medium"><?= (int)$config['tempo_alerta_minutos'] ?> min restantes</span> (Repete a cada <?= (int)$config['intervalo_alerta_minutos'] ?> min)
            </p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" id="btn-open-config" 
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs sm:text-sm font-semibold transition">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Configurar Alertas
            </button>
            <button type="button" id="btn-fullscreen" 
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs sm:text-sm font-semibold transition">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                Tela Cheia
            </button>
        </div>
    </div>

    <!-- Banner de Alerta Visual Ativo (Disparado quando atinge o tempo de alerta) -->
    <div id="timer-alert-banner" class="hidden mb-6 p-4 rounded-2xl bg-rose-600/20 border-2 border-rose-500 text-rose-200 flex items-center justify-between gap-3 animate-pulse-alert">
        <div class="flex items-center gap-3">
            <span class="text-2xl animate-bounce">⚠️</span>
            <div>
                <h4 class="font-bold text-sm sm:text-base text-rose-100 uppercase tracking-wide">Aviso de Prova em Andamento</h4>
                <p id="timer-alert-text" class="text-xs sm:text-sm font-medium text-rose-200">Atenção candidatos: faltam poucos minutos para a entrega final!</p>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('timer-alert-banner').classList.add('hidden')" class="text-rose-300 hover:text-white p-1">
            ✕
        </button>
    </div>

    <!-- Layout Grid Principal (Mobile-First: coluna única no mobile, 2 colunas no desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coluna da Esquerda / Principal: O Display Gigante do Timer e Controles -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Card Principal do Display Digital -->
            <div id="timer-card" class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 sm:p-10 text-center shadow-2xl backdrop-blur relative overflow-hidden">
                
                <!-- Barra de Progresso do Tempo -->
                <div class="w-full bg-slate-900/80 rounded-full h-3 mb-8 overflow-hidden p-0.5 border border-slate-700/60">
                    <div id="timer-progress" class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: 100%;"></div>
                </div>

                <!-- Dígitos Gigantes do Timer -->
                <div class="timer-digits text-5xl sm:text-7xl md:text-8xl lg:text-9xl font-black text-white tracking-tight flex items-center justify-center gap-1 sm:gap-3 select-none py-4">
                    <div class="flex flex-col items-center">
                        <span id="timer-hours">02</span>
                        <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400 mt-1">Horas</span>
                    </div>
                    <span class="text-blue-500 pb-5">:</span>
                    <div class="flex flex-col items-center">
                        <span id="timer-minutes">00</span>
                        <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400 mt-1">Minutos</span>
                    </div>
                    <span class="text-blue-500 pb-5">:</span>
                    <div class="flex flex-col items-center">
                        <span id="timer-seconds">00</span>
                        <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400 mt-1">Segundos</span>
                    </div>
                </div>

                <!-- Barra de Ajustes Rápidos de Tempo (+5m, +1m, -5m) -->
                <div class="flex items-center justify-center gap-2 mt-4 pt-4 border-t border-slate-700/60">
                    <span class="text-xs text-slate-400 mr-1 hidden sm:inline">Ajuste Rápido:</span>
                    <button type="button" data-time-adjust="-300" class="px-2.5 py-1 rounded-lg bg-slate-900/60 hover:bg-slate-700 text-xs font-medium text-slate-300 border border-slate-700 transition">
                        -5 min
                    </button>
                    <button type="button" data-time-adjust="-60" class="px-2.5 py-1 rounded-lg bg-slate-900/60 hover:bg-slate-700 text-xs font-medium text-slate-300 border border-slate-700 transition">
                        -1 min
                    </button>
                    <button type="button" data-time-adjust="60" class="px-2.5 py-1 rounded-lg bg-slate-900/60 hover:bg-slate-700 text-xs font-medium text-slate-300 border border-slate-700 transition">
                        +1 min
                    </button>
                    <button type="button" data-time-adjust="300" class="px-2.5 py-1 rounded-lg bg-slate-900/60 hover:bg-slate-700 text-xs font-medium text-slate-300 border border-slate-700 transition">
                        +5 min
                    </button>
                </div>

                <!-- Botões de Ação Principais -->
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    
                    <!-- Botão Iniciar -->
                    <button type="button" id="btn-timer-start" 
                            class="flex-1 sm:flex-initial px-8 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-base sm:text-lg shadow-xl shadow-emerald-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                        <span>Iniciar Prova</span>
                    </button>

                    <!-- Botão Pausar (Oculto no início) -->
                    <button type="button" id="btn-timer-pause" 
                            class="hidden flex-1 sm:flex-initial px-8 py-4 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-base sm:text-lg shadow-xl shadow-amber-600/30 transition flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span>Pausar</span>
                    </button>

                    <!-- Botão Retomar (Oculto no início) -->
                    <button type="button" id="btn-timer-resume" 
                            class="hidden flex-1 sm:flex-initial px-8 py-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-base sm:text-lg shadow-xl shadow-blue-600/30 transition flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                        <span>Retomar Contagem</span>
                    </button>

                    <!-- Botão Reiniciar -->
                    <button type="button" id="btn-timer-reset" 
                            class="px-5 py-4 rounded-2xl bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold text-sm transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span class="hidden sm:inline">Reiniciar</span>
                    </button>

                    <!-- Botão Finalizar Prova -->
                    <button type="button" id="btn-timer-finish" 
                            class="px-5 py-4 rounded-2xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/40 font-semibold text-sm transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Finalizar</span>
                    </button>

                </div>

            </div>

        </div>

        <!-- Coluna da Direita: Painel de Registro de Posições e Entregas (Regra CORE) -->
        <div class="space-y-6">
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 shadow-2xl backdrop-blur flex flex-col h-full">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-700">
                    <div>
                        <h3 class="font-bold text-white text-base flex items-center gap-2">
                            <span>🏆</span> Ordem de Entrega / Posições
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Registro oficial de saída e entrega de cadernos</p>
                    </div>
                </div>

                <div class="py-4">
                    <button type="button" id="btn-add-position" 
                            class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Registrar Entrega de Prova</span>
                    </button>
                </div>

                <!-- Lista de Candidatos e Posições -->
                <div class="flex-1 overflow-y-auto max-h-[380px] space-y-2.5 pr-1" id="positions-list">
                    <!-- Gerado dinamicamente pelo timer.js -->
                </div>

                <!-- Estado Vazio -->
                <div id="positions-empty" class="text-center py-10 px-4">
                    <span class="text-3xl block mb-2 opacity-50">📝</span>
                    <p class="text-sm font-medium text-slate-300">Nenhuma entrega registrada ainda</p>
                    <p class="text-xs text-slate-500 mt-1">Conforme os candidatos forem entregando as provas, clique no botão acima para registrar a ordem de conclusão.</p>
                </div>

            </div>
        </div>

    </div>

</div>

<!-- Modal de Configuração do Timer e Alertas -->
<div id="modal-config" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-slate-800 border border-slate-700 rounded-3xl shadow-2xl p-6 sm:p-8">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-6">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400 text-xl">⚙️</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Configurar Timer e Alertas</h3>
                    <p class="text-xs text-slate-400">Defina os parâmetros da prova e notificações</p>
                </div>
            </div>
            <button type="button" id="btn-close-config" class="text-slate-400 hover:text-white p-1 rounded-lg">
                ✕
            </button>
        </div>

        <form action="actions/timer_save_config.php" method="POST" class="space-y-4">
            
            <!-- Título da Prova -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Título / Identificação da Prova
                </label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($config['titulo']) ?>" required
                       class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500">
            </div>

            <!-- Tempo Total e Tempo de Início do Alerta -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Tempo Total (minutos)
                    </label>
                    <input type="number" name="tempo_total_minutos" value="<?= (int)$config['tempo_total_minutos'] ?>" min="1" max="600" required
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Iniciar Alerta aos (minutos restantes)
                    </label>
                    <input type="number" name="tempo_alerta_minutos" value="<?= (int)$config['tempo_alerta_minutos'] ?>" min="1" max="300" required
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <!-- Intervalo entre alertas e tipo de som -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Tempo entre cada alerta (minutos)
                    </label>
                    <input type="number" name="intervalo_alerta_minutos" value="<?= (int)$config['intervalo_alerta_minutos'] ?>" min="1" max="60" required
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Tom de Alerta Sonoro
                    </label>
                    <select name="tipo_som" id="select-sound-type" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="beep_duplo" <?= $config['tipo_som'] === 'beep_duplo' ? 'selected' : '' ?>>Beep Duplo (Padrão)</option>
                        <option value="sino_escola" <?= $config['tipo_som'] === 'sino_escola' ? 'selected' : '' ?>>Sino Escolar</option>
                        <option value="sirene_aviso" <?= $config['tipo_som'] === 'sirene_aviso' ? 'selected' : '' ?>>Sirene Suave</option>
                        <option value="curto" <?= $config['tipo_som'] === 'curto' ? 'selected' : '' ?>>Ping Sutil</option>
                    </select>
                </div>
            </div>

            <!-- Opções de Alerta Sonoro e Visual (Checkbox) -->
            <div class="pt-2 border-t border-slate-700/60 space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="emitir_som" value="1" <?= $config['emitir_som'] ? 'checked' : '' ?>
                           class="w-4 h-4 rounded text-blue-600 bg-slate-900 border-slate-700 focus:ring-0">
                    <span class="text-sm text-slate-300">Emitir alerta sonoro (áudio sintetizado de alta nitidez)</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="emitir_aviso_visual" value="1" <?= $config['emitir_aviso_visual'] ? 'checked' : '' ?>
                           class="w-4 h-4 rounded text-blue-600 bg-slate-900 border-slate-700 focus:ring-0">
                    <span class="text-sm text-slate-300">Emitir aviso visual e banner na tela</span>
                </label>
            </div>

            <!-- Botão para Testar Áudio -->
            <div class="pt-1">
                <button type="button" id="btn-test-sound" 
                        class="w-full py-2 px-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold transition flex items-center justify-center gap-2">
                    <span>🔊 Testar Alerta Sonoro no Navegador</span>
                </button>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" id="btn-cancel-config" 
                        class="w-1/2 py-3 px-4 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold text-sm transition">
                    Cancelar
                </button>
                <button type="submit" 
                        class="w-1/2 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition shadow-lg shadow-blue-600/30">
                    Salvar Parâmetros
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Modal de Finalização e Salvamento da Prova -->
<div id="modal-save-run" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 border border-slate-700 rounded-3xl shadow-2xl p-6 sm:p-8">
        
        <div class="text-center mb-6">
            <span class="inline-flex p-3 rounded-2xl bg-emerald-500/10 text-emerald-400 mb-2 text-3xl">🏁</span>
            <h3 class="text-xl font-bold text-white">Finalização da Prova</h3>
            <p class="text-xs text-slate-400 mt-1">Grave os resultados e posições no histórico institucional</p>
        </div>

        <form action="actions/timer_save_run.php" method="POST" class="space-y-4">
            <input type="hidden" name="titulo" id="save-run-title" value="">
            <input type="hidden" name="tempo_total_segundos" id="save-run-total-seconds" value="0">
            <input type="hidden" name="tempo_decorrido_segundos" id="save-run-elapsed-seconds" value="0">
            <input type="hidden" name="status" id="save-run-status" value="concluida">
            <input type="hidden" name="resultado_posicoes" id="save-run-positions-json" value="[]">

            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-700/80 text-xs space-y-1 text-slate-300">
                <p><strong>Entregas registradas:</strong> <span id="save-run-positions-count" class="text-blue-400">0</span></p>
                <p><strong>Auditoria:</strong> Registro oficial de horários e ocorrências da sala.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Observações da Aplicação (Opcional)
                </label>
                <textarea name="observacoes" rows="3" placeholder="Ex: Sala 04, todos os cadernos recolhidos sem intercorrências..."
                          class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="document.getElementById('modal-save-run').classList.add('hidden')" 
                        class="w-1/2 py-3 px-4 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold text-sm transition">
                    Fechar
                </button>
                <button type="submit" 
                        class="w-1/2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm transition shadow-lg shadow-emerald-600/30">
                    Salvar no Histórico
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Script de Inicialização da Aplicação do Timer -->
<script src="assets/js/timer.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializa a instância do ExamTimer com os parâmetros do PHP
        const examTimer = new ExamTimer({
            examTitle: <?= json_encode($config['titulo']) ?>,
            totalMinutes: <?= (int)$config['tempo_total_minutos'] ?>,
            alertRemainingMinutes: <?= (int)$config['tempo_alerta_minutos'] ?>,
            alertIntervalMinutes: <?= (int)$config['intervalo_alerta_minutos'] ?>,
            soundEnabled: <?= $config['emitir_som'] ? 'true' : 'false' ?>,
            visualEnabled: <?= $config['emitir_aviso_visual'] ? 'true' : 'false' ?>,
            soundType: <?= json_encode($config['tipo_som']) ?>
        });

        // Controle dos Modais
        const modalConfig = document.getElementById('modal-config');
        const btnOpenConfig = document.getElementById('btn-open-config');
        const btnCloseConfig = document.getElementById('btn-close-config');
        const btnCancelConfig = document.getElementById('btn-cancel-config');
        const btnTestSound = document.getElementById('btn-test-sound');
        const selectSound = document.getElementById('select-sound-type');

        if (btnOpenConfig && modalConfig) {
            btnOpenConfig.addEventListener('click', () => modalConfig.classList.remove('hidden'));
        }
        if (btnCloseConfig && modalConfig) {
            btnCloseConfig.addEventListener('click', () => modalConfig.classList.add('hidden'));
        }
        if (btnCancelConfig && modalConfig) {
            btnCancelConfig.addEventListener('click', () => modalConfig.classList.add('hidden'));
        }

        // Teste de som imediato no modal de configuração
        if (btnTestSound) {
            btnTestSound.addEventListener('click', () => {
                const sound = selectSound ? selectSound.value : 'beep_duplo';
                examTimer.playSound(sound);
                if (window.showToast) {
                    window.showToast('info', 'Reproduzindo tom de alerta selecionado.', 'Teste de Som');
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
