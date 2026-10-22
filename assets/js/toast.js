/**
 * Sistema de Notificações Toast
 * Timer de Provas
 */

(function () {
    const icons = {
        success: `<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`,
        error: `<svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`,
        warning: `<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
        info: `<svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
    };

    const borders = {
        success: 'border-l-4 border-emerald-500',
        error: 'border-l-4 border-rose-500',
        warning: 'border-l-4 border-amber-500',
        info: 'border-l-4 border-sky-500'
    };

    const progressColors = {
        success: 'bg-emerald-500',
        error: 'bg-rose-500',
        warning: 'bg-amber-500',
        info: 'bg-sky-500'
    };

    window.showToast = function (tipo, mensagem, titulo) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none p-4 sm:p-0';
            document.body.appendChild(container);
        }

        const toastType = ['success', 'error', 'warning', 'info'].includes(tipo) ? tipo : 'info';
        const defaultTitle = {
            success: 'Sucesso',
            error: 'Atenção / Erro',
            warning: 'Aviso',
            info: 'Informação'
        }[toastType];

        const toast = document.createElement('div');
        toast.className = `pointer-events-auto transform transition-all duration-300 ease-out translate-x-full opacity-0 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden ${borders[toastType]}`;

        toast.innerHTML = `
            <div class="p-4 flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">${icons[toastType]}</div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">${titulo || defaultTitle}</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 break-words">${mensagem}</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1 -mr-1 -mt-1 rounded-lg" onclick="this.closest('.pointer-events-auto').remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="h-1 bg-slate-100 dark:bg-slate-700 w-full overflow-hidden">
                <div class="toast-progress h-full ${progressColors[toastType]} transition-all linear" style="width: 100%;"></div>
            </div>
        `;

        container.appendChild(toast);

        // Animação de entrada
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        // Barra de progresso e auto-remoção após 5 segundos
        const duration = 5000;
        const startTime = Date.now();
        const progressBar = toast.querySelector('.toast-progress');

        const interval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const remaining = Math.max(0, 100 - (elapsed / duration) * 100);
            if (progressBar) progressBar.style.width = remaining + '%';

            if (elapsed >= duration) {
                clearInterval(interval);
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }
        }, 50);
    };

    // Inicialização ao carregar o DOM
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('toast-container');
        if (container && container.dataset.toasts) {
            try {
                const toasts = JSON.parse(container.dataset.toasts);
                if (Array.isArray(toasts)) {
                    toasts.forEach((t, index) => {
                        setTimeout(() => {
                            window.showToast(t.tipo, t.mensagem, t.titulo);
                        }, index * 200);
                    });
                }
            } catch (e) {
                console.error('Falha ao processar toasts da sessão:', e);
            }
        }
    });
})();
