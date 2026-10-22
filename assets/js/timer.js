/**
 * Mecanismo de Controle do Timer de Provas, Alertas e Registro de Posições
 * Timer de Provas
 */

class ExamTimer {
    constructor(options = {}) {
        // Elementos DOM
        this.displayHours = document.getElementById('timer-hours');
        this.displayMinutes = document.getElementById('timer-minutes');
        this.displaySeconds = document.getElementById('timer-seconds');
        this.progressBar = document.getElementById('timer-progress');
        this.statusBadge = document.getElementById('timer-status-badge');
        this.timerContainer = document.getElementById('timer-card');
        this.alertBanner = document.getElementById('timer-alert-banner');
        this.alertBannerText = document.getElementById('timer-alert-text');
        this.positionsList = document.getElementById('positions-list');
        this.positionsEmpty = document.getElementById('positions-empty');

        // Botões de Ação
        this.btnStart = document.getElementById('btn-timer-start');
        this.btnPause = document.getElementById('btn-timer-pause');
        this.btnResume = document.getElementById('btn-timer-resume');
        this.btnReset = document.getElementById('btn-timer-reset');
        this.btnFinish = document.getElementById('btn-timer-finish');
        this.btnFullscreen = document.getElementById('btn-fullscreen');
        this.btnAddPosition = document.getElementById('btn-add-position');

        // Configurações Padrão
        this.examTitle = options.examTitle || 'Avaliação Oficial';
        this.totalDuration = (options.totalMinutes || 120) * 60; // em segundos
        this.remainingSeconds = this.totalDuration;
        this.elapsedSeconds = 0;

        // Configuração dos Alertas
        this.alertTriggerRemaining = (options.alertRemainingMinutes || 15) * 60; // tempo restante que ativa a fase de alerta
        this.alertInterval = (options.alertIntervalMinutes || 5) * 60; // a cada quantos segundos apitar no período de alerta
        this.soundEnabled = options.soundEnabled !== false;
        this.visualEnabled = options.visualEnabled !== false;
        this.soundType = options.soundType || 'beep_duplo';

        // Estado Interno
        this.timerInterval = null;
        this.state = 'IDLE'; // 'IDLE', 'RUNNING', 'PAUSED', 'FINISHED'
        this.positions = []; // Lista de entregas e posições dos candidatos
        this.lastAlertSecondTriggered = null;
        this.startTimeStamp = null;

        // Contexto de Áudio da Web API
        this.audioCtx = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.updateDisplay();
        this.updateButtons();
    }

    // Inicializa Web Audio API ao primeiro clique do usuário para respeitar políticas dos navegadores
    getAudioContext() {
        if (!this.audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.audioCtx = new AudioContext();
            }
        }
        if (this.audioCtx && this.audioCtx.state === 'suspended') {
            this.audioCtx.resume();
        }
        return this.audioCtx;
    }

    // Toca som sintetizado em tempo real (não requer dependência de arquivos de áudio externos)
    playSound(type = this.soundType) {
        if (!this.soundEnabled) return;
        const ctx = this.getAudioContext();
        if (!ctx) return;

        try {
            const now = ctx.currentTime;

            if (type === 'sino_escola') {
                // Efeito sonoro de sino escolar / carrilhão
                [523.25, 659.25, 783.99, 1046.50].forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(freq, now + (idx * 0.2));

                    gain.gain.setValueAtTime(0, now + (idx * 0.2));
                    gain.gain.linearRampToValueAtTime(0.4, now + (idx * 0.2) + 0.05);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + (idx * 0.2) + 0.8);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + (idx * 0.2));
                    osc.stop(now + (idx * 0.2) + 0.85);
                });
            } else if (type === 'sirene_aviso') {
                // Alerta tipo sirene modulada suave
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                
                osc.frequency.setValueAtTime(440, now);
                osc.frequency.linearRampToValueAtTime(880, now + 0.3);
                osc.frequency.linearRampToValueAtTime(440, now + 0.6);

                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.7);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.7);
            } else if (type === 'curto') {
                // Beep curto e sutil
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(600, now);

                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.25);
            } else {
                // Beep duplo padrão (beep_duplo)
                [0, 0.18].forEach((delay) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, now + delay);

                    gain.gain.setValueAtTime(0.35, now + delay);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + delay + 0.12);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + delay);
                    osc.stop(now + delay + 0.13);
                });
            }

            // Vibração para dispositivos móveis
            if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200]);
            }
        } catch (e) {
            console.warn('Erro ao emitir áudio via Web Audio API:', e);
        }
    }

    // Vincula os ouvintes de eventos da interface
    bindEvents() {
        if (this.btnStart) {
            this.btnStart.addEventListener('click', () => this.start());
        }
        if (this.btnPause) {
            this.btnPause.addEventListener('click', () => this.pause());
        }
        if (this.btnResume) {
            this.btnResume.addEventListener('click', () => this.resume());
        }
        if (this.btnReset) {
            this.btnReset.addEventListener('click', () => this.reset());
        }
        if (this.btnFinish) {
            this.btnFinish.addEventListener('click', () => this.finish());
        }
        if (this.btnFullscreen) {
            this.btnFullscreen.addEventListener('click', () => this.toggleFullscreen());
        }
        if (this.btnAddPosition) {
            this.btnAddPosition.addEventListener('click', () => this.promptAddPosition());
        }

        // Atalhos de ajuste rápido de tempo (+5min, -5min, +1min)
        document.querySelectorAll('[data-time-adjust]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const adjustSeconds = parseInt(e.currentTarget.dataset.timeAdjust, 10);
                this.adjustTime(adjustSeconds);
            });
        });
    }

    // Iniciar contagem do timer
    start() {
        if (this.state === 'RUNNING') return;
        this.getAudioContext();

        this.state = 'RUNNING';
        this.startTimeStamp = new Date().toISOString();
        this.updateButtons();
        this.updateBadge('Em Andamento', 'bg-emerald-500 text-white');

        this.logAuditEvent('INICIO_TIMER', `Início da prova '${this.examTitle}'. Duração total: ${Math.floor(this.totalDuration / 60)} min.`);

        if (window.showToast) {
            window.showToast('info', 'Contagem regressiva da prova iniciada.', 'Prova Iniciada');
        }

        this.runLoop();
    }

    // Pausar contagem
    pause() {
        if (this.state !== 'RUNNING') return;
        this.state = 'PAUSED';
        clearInterval(this.timerInterval);
        this.updateButtons();
        this.updateBadge('Pausado', 'bg-amber-500 text-white');

        this.logAuditEvent('PAUSA_TIMER', `Prova '${this.examTitle}' pausada aos ${this.formatTime(this.elapsedSeconds)}.`);

        if (window.showToast) {
            window.showToast('warning', 'O timer da prova foi pausado.', 'Pausado');
        }
    }

    // Retomar contagem
    resume() {
        if (this.state !== 'PAUSED') return;
        this.state = 'RUNNING';
        this.updateButtons();
        this.updateBadge('Em Andamento', 'bg-emerald-500 text-white');

        this.logAuditEvent('RETOMADA_TIMER', `Prova '${this.examTitle}' retomada.`);

        if (window.showToast) {
            window.showToast('info', 'Contagem retomada com sucesso.', 'Retomado');
        }

        this.runLoop();
    }

    // Reiniciar contagem
    reset() {
        if (confirm('Tem certeza de que deseja reiniciar o cronômetro desta prova?')) {
            clearInterval(this.timerInterval);
            this.remainingSeconds = this.totalDuration;
            this.elapsedSeconds = 0;
            this.state = 'IDLE';
            this.lastAlertSecondTriggered = null;

            this.updateDisplay();
            this.updateButtons();
            this.updateBadge('Pronto para Iniciar', 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300');
            this.hideAlertBanner();

            this.logAuditEvent('REINICIO_TIMER', `O timer da prova '${this.examTitle}' foi reiniciado.`);

            if (window.showToast) {
                window.showToast('info', 'O timer foi redefinido para o tempo inicial.', 'Reiniciado');
            }
        }
    }

    // Ajustar tempo dinamicamente (+1m, +5m, -5m)
    adjustTime(seconds) {
        if (this.remainingSeconds + seconds < 0) {
            this.remainingSeconds = 0;
        } else {
            this.remainingSeconds += seconds;
        }
        this.totalDuration = Math.max(this.totalDuration, this.remainingSeconds + this.elapsedSeconds);
        this.updateDisplay();

        const signal = seconds > 0 ? `+${seconds / 60}` : `${seconds / 60}`;
        this.logAuditEvent('AJUSTE_TEMPO', `Tempo da prova ajustado em ${signal} minutos.`);

        if (window.showToast) {
            window.showToast('info', `Tempo ajustado em ${signal} minutos.`, 'Tempo Atualizado');
        }
    }

    // Loop de contagem regressiva por segundo
    runLoop() {
        clearInterval(this.timerInterval);
        this.timerInterval = setInterval(() => {
            if (this.remainingSeconds <= 0) {
                this.timeOver();
                return;
            }

            this.remainingSeconds--;
            this.elapsedSeconds++;
            this.updateDisplay();
            this.checkAlerts();
        }, 1000);
    }

    // Verificação e disparo das regras de alertas configuradas
    checkAlerts() {
        // Verifica se entrou no período de alerta (tempo restante menor ou igual ao tempo de alerta)
        if (this.remainingSeconds <= this.alertTriggerRemaining && this.remainingSeconds > 0) {
            const minutesLeft = Math.ceil(this.remainingSeconds / 60);

            // Verifica se está no marco inicial do alerta ou no intervalo configurado
            const shouldAlertNow = (this.remainingSeconds === this.alertTriggerRemaining) || 
                                   (this.alertInterval > 0 && (this.alertTriggerRemaining - this.remainingSeconds) % this.alertInterval === 0);

            if (shouldAlertNow && this.lastAlertSecondTriggered !== this.remainingSeconds) {
                this.lastAlertSecondTriggered = this.remainingSeconds;
                this.triggerAlert(minutesLeft);
            }
        }
    }

    // Dispara alerta audiovisual
    triggerAlert(minutesLeft) {
        const message = `Atenção: Restam aproximadamente ${minutesLeft} minuto(s) para o término da prova!`;

        // Alerta sonoro
        if (this.soundEnabled) {
            this.playSound();
        }

        // Alerta visual
        if (this.visualEnabled) {
            this.showAlertBanner(message);
            document.body.classList.add('screen-flash');
            setTimeout(() => document.body.classList.remove('screen-flash'), 2500);
        }

        if (window.showToast) {
            window.showToast('warning', message, 'Alerta de Tempo');
        }

        this.logAuditEvent('ALERTA_DISPARADO', `Alerta de prova acionado: restam ${minutesLeft} min.`);
    }

    // Quando o tempo total se esgota
    timeOver() {
        clearInterval(this.timerInterval);
        this.state = 'FINISHED';
        this.remainingSeconds = 0;
        this.updateDisplay();
        this.updateButtons();
        this.updateBadge('Tempo Esgotado!', 'bg-rose-600 text-white animate-pulse');

        // Alerta de término sonoro prolongado
        if (this.soundEnabled) {
            this.playSound('sirene_aviso');
            setTimeout(() => this.playSound('sino_escola'), 1000);
        }

        this.showAlertBanner('TEMPO ESGOTADO! A avaliação encerrou. Solicite a entrega imediata das provas.');

        if (window.showToast) {
            window.showToast('error', 'O tempo limite da prova se esgotou completamente!', 'Término da Prova');
        }

        this.logAuditEvent('TEMPO_ESGOTADO', `O tempo total da prova '${this.examTitle}' terminou.`);

        // Abre modal para salvar resultado final
        setTimeout(() => this.openFinishModal('Tempo Esgotado'), 1500);
    }

    // Finalizar manualmente a prova
    finish() {
        if (confirm('Deseja encerrar esta prova agora e registrar o resultado e posições?')) {
            clearInterval(this.timerInterval);
            this.state = 'FINISHED';
            this.updateButtons();
            this.updateBadge('Concluída', 'bg-blue-600 text-white');

            this.logAuditEvent('FINALIZOU_PROVA', `Prova '${this.examTitle}' finalizada manualmente pelo coordenador.`);
            this.openFinishModal('Concluída');
        }
    }

    // Registro de entregas e posições dos candidatos (Regra CORE)
    promptAddPosition() {
        const currentCount = this.positions.length + 1;
        const candidateName = prompt(`Informe o nome ou número do ${currentCount}º candidato a entregar a prova:`);

        if (candidateName && candidateName.trim() !== '') {
            const positionItem = {
                posicao: currentCount,
                nome: candidateName.trim(),
                tempoDecorrido: this.formatTime(this.elapsedSeconds),
                horarioEntrega: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
            };

            this.positions.push(positionItem);
            this.renderPositions();

            if (window.showToast) {
                window.showToast('success', `${currentCount}º Lugar: ${positionItem.nome} entregou aos ${positionItem.tempoDecorrido}`, 'Entrega Registrada');
            }

            this.logAuditEvent('REGISTRO_ENTREGA', `Registro de entrega: ${currentCount}º colocado - ${positionItem.nome} (${positionItem.tempoDecorrido}).`);
        }
    }

    renderPositions() {
        if (!this.positionsList) return;

        if (this.positions.length === 0) {
            if (this.positionsEmpty) this.positionsEmpty.classList.remove('hidden');
            this.positionsList.innerHTML = '';
            return;
        }

        if (this.positionsEmpty) this.positionsEmpty.classList.add('hidden');

        this.positionsList.innerHTML = this.positions.map((p) => `
            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 flex items-center justify-center rounded-full font-bold text-xs ${
                        p.posicao === 1 ? 'bg-amber-400 text-amber-950 ring-2 ring-amber-300' :
                        p.posicao === 2 ? 'bg-slate-300 text-slate-800' :
                        p.posicao === 3 ? 'bg-amber-600 text-white' :
                        'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
                    }">${p.posicao}º</span>
                    <div>
                        <h4 class="font-medium text-slate-900 dark:text-slate-100 text-sm">${this.escapeHtml(p.nome)}</h4>
                        <span class="text-xs text-slate-500">Horário: ${p.horarioEntrega}</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        ${p.tempoDecorrido}
                    </span>
                </div>
            </div>
        `).join('');
    }

    openFinishModal(statusTexto = 'Concluída') {
        const modal = document.getElementById('modal-save-run');
        if (!modal) return;

        // Preenche campos do formulário de finalização
        const inputTitle = document.getElementById('save-run-title');
        const inputTotalSec = document.getElementById('save-run-total-seconds');
        const inputElapsedSec = document.getElementById('save-run-elapsed-seconds');
        const inputPositions = document.getElementById('save-run-positions-json');
        const inputStatus = document.getElementById('save-run-status');
        const previewPositions = document.getElementById('save-run-positions-count');

        if (inputTitle) inputTitle.value = this.examTitle;
        if (inputTotalSec) inputTotalSec.value = this.totalDuration;
        if (inputElapsedSec) inputElapsedSec.value = this.elapsedSeconds;
        if (inputPositions) inputPositions.value = JSON.stringify(this.positions);
        if (inputStatus) inputStatus.value = statusTexto === 'Concluída' ? 'concluida' : 'concluida';
        if (previewPositions) previewPositions.innerText = `${this.positions.length} candidato(s) registrado(s)`;

        modal.classList.remove('hidden');
    }

    // Tela Cheia (Fullscreen API) para projeção
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().then(() => {
                document.body.classList.add('fullscreen-mode');
                if (this.btnFullscreen) {
                    this.btnFullscreen.innerHTML = `
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Sair Tela Cheia
                    `;
                }
            }).catch(err => console.warn(err));
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen().then(() => {
                    document.body.classList.remove('fullscreen-mode');
                    if (this.btnFullscreen) {
                        this.btnFullscreen.innerHTML = `
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                            Tela Cheia
                        `;
                    }
                });
            }
        }
    }

    // Atualiza elementos visuais do timer
    updateDisplay() {
        const hours = Math.floor(this.remainingSeconds / 3600);
        const minutes = Math.floor((this.remainingSeconds % 3600) / 60);
        const seconds = this.remainingSeconds % 60;

        if (this.displayHours) this.displayHours.innerText = String(hours).padStart(2, '0');
        if (this.displayMinutes) this.displayMinutes.innerText = String(minutes).padStart(2, '0');
        if (this.displaySeconds) this.displaySeconds.innerText = String(seconds).padStart(2, '0');

        // Atualiza a barra de progresso
        if (this.progressBar && this.totalDuration > 0) {
            const percentage = Math.max(0, Math.min(100, (this.remainingSeconds / this.totalDuration) * 100));
            this.progressBar.style.width = percentage + '%';

            // Muda cor da barra conforme o tempo vai acabando
            if (percentage <= 15) {
                this.progressBar.className = 'h-3 rounded-full transition-all duration-500 bg-rose-500 animate-pulse';
            } else if (percentage <= 35) {
                this.progressBar.className = 'h-3 rounded-full transition-all duration-500 bg-amber-500';
            } else {
                this.progressBar.className = 'h-3 rounded-full transition-all duration-500 bg-blue-600';
            }
        }

        // Título da aba do navegador dinâmico
        document.title = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')} - Timer de Provas`;
    }

    updateButtons() {
        if (!this.btnStart || !this.btnPause || !this.btnResume) return;

        if (this.state === 'IDLE') {
            this.btnStart.classList.remove('hidden');
            this.btnPause.classList.add('hidden');
            this.btnResume.classList.add('hidden');
        } else if (this.state === 'RUNNING') {
            this.btnStart.classList.add('hidden');
            this.btnPause.classList.remove('hidden');
            this.btnResume.classList.add('hidden');
        } else if (this.state === 'PAUSED') {
            this.btnStart.classList.add('hidden');
            this.btnPause.classList.add('hidden');
            this.btnResume.classList.remove('hidden');
        } else if (this.state === 'FINISHED') {
            this.btnStart.classList.remove('hidden');
            this.btnPause.classList.add('hidden');
            this.btnResume.classList.add('hidden');
        }
    }

    updateBadge(text, classNames) {
        if (this.statusBadge) {
            this.statusBadge.innerText = text;
            this.statusBadge.className = `px-3 py-1 rounded-full text-xs font-semibold transition-all ${classNames}`;
        }
    }

    showAlertBanner(message) {
        if (this.alertBanner && this.alertBannerText) {
            this.alertBannerText.innerText = message;
            this.alertBanner.classList.remove('hidden');
        }
    }

    hideAlertBanner() {
        if (this.alertBanner) {
            this.alertBanner.classList.add('hidden');
        }
    }

    formatTime(totalSec) {
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        const s = totalSec % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }

    // Registra evento no backend de auditoria
    logAuditEvent(acao, detalhes) {
        try {
            fetch('actions/timer_log_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    acao: acao,
                    detalhes: detalhes
                })
            }).catch(e => console.warn('Falha silenciosa ao registrar log remoto:', e));
        } catch (e) {
            // falha tolerável
        }
    }
}

// Inicializador global
window.ExamTimer = ExamTimer;
