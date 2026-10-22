    </main>

    <!-- Rodapé -->
    <footer class="bg-slate-950 border-t border-slate-800/80 py-6 text-slate-500 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span>⏱️ <?= APP_NAME ?> &copy; <?= date('Y') ?></span>
                <span>•</span>
                <span>Desenvolvido para Gestão de Provas & Alertas</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <a href="instalar.php" class="hover:text-blue-400 transition">Diagnóstico do Banco</a>
                <span>•</span>
                <span class="text-slate-500">v<?= APP_VERSION ?></span>
            </div>
        </div>
    </footer>

    <!-- Scripts Globais -->
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/masks.js"></script>
    <script>
        // Controle de abertura do menu no Mobile
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
