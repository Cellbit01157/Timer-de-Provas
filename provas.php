<?php
/**
 * Página de Provas Executadas & Registro de Posições
 * Timer de Provas
 *
 * Exibe as avaliações salvas, durações e a classificação/ordem
 * de entrega de cadernos dos alunos.
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

$pdo = getDBConnection();
$currentUser = getCurrentUser();

// Consulta provas registradas no histórico
$stmt = $pdo->prepare("
    SELECT p.*, u.nome as coordenador_nome 
    FROM provas_historico p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.iniciado_em DESC, p.id DESC
");
$stmt->execute();
$provas = $stmt->fetchAll();

$pageTitle = "Histórico de Provas e Posições";
require_once __DIR__ . '/includes/header.php';

function formatarSegundos(int $segundos): string {
    $h = floor($segundos / 3600);
    $m = floor(($segundos % 3600) / 60);
    $s = $segundos % 60;
    return sprintf('%02dh %02dm %02ds', $h, $m, $s);
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>📋</span> Histórico de Provas & Resultados
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Acompanhe o tempo de duração e a ordem de entrega registrada dos candidatos
            </p>
        </div>
        <a href="timer.php" 
           class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs sm:text-sm transition shadow-lg shadow-blue-600/30 flex items-center gap-2">
            <span>+ Iniciar Nova Prova</span>
        </a>
    </div>

    <?php if (empty($provas)): ?>
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-12 text-center backdrop-blur">
            <span class="text-5xl block mb-3 opacity-40">🏁</span>
            <h3 class="text-lg font-bold text-white">Nenhuma prova finalizada ainda</h3>
            <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">
                Quando você iniciar uma prova no Timer e clicar em "Finalizar Prova", o resultado e todas as posições dos alunos aparecerão aqui.
            </p>
            <div class="mt-6">
                <a href="timer.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition">
                    Ir para o Timer Agora
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($provas as $prova): ?>
                <?php 
                    $posicoes = json_decode($prova['resultado_posicoes'] ?? '[]', true) ?: [];
                    $totalCandidatos = count($posicoes);
                ?>
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-xl backdrop-blur transition hover:border-slate-600">
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-700">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($prova['titulo']) ?></h3>
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $prova['status'] === 'concluida' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' ?>">
                                    <?= htmlspecialchars($prova['status']) ?>
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                Coordenador: <strong class="text-slate-300"><?= htmlspecialchars($prova['coordenador_nome']) ?></strong> • 
                                Início: <?= date('d/m/Y H:i', strtotime($prova['iniciado_em'])) ?>
                            </p>
                        </div>

                        <div class="text-left sm:text-right">
                            <span class="text-xs text-slate-400 block">Tempo Decorrido:</span>
                            <span class="text-sm font-bold font-mono text-blue-400">
                                <?= formatarSegundos((int)$prova['tempo_decorrido_segundos']) ?>
                            </span>
                            <span class="text-[11px] text-slate-500 block">
                                (Previsto: <?= formatarSegundos((int)$prova['tempo_total_segundos']) ?>)
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($prova['observacoes'])): ?>
                        <div class="mt-4 p-3 rounded-xl bg-slate-900/60 border border-slate-700/60 text-xs text-slate-300">
                            <strong>Observações da Sala:</strong> <?= nl2br(htmlspecialchars($prova['observacoes'])) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Posições e Entregas Registradas -->
                    <div class="mt-5">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                            <span>🏆</span> Ordem de Entrega dos Alunos (<?= $totalCandidatos ?> registros)
                        </h4>

                        <?php if (empty($posicoes)): ?>
                            <p class="text-xs text-slate-500 italic">Nenhum candidato individual registrado nesta sessão.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                <?php foreach ($posicoes as $p): ?>
                                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-700/60 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-[11px] <?= (int)$p['posicao'] === 1 ? 'bg-amber-400 text-amber-950 font-extrabold' : ((int)$p['posicao'] === 2 ? 'bg-slate-300 text-slate-900' : ((int)$p['posicao'] === 3 ? 'bg-amber-600 text-white' : 'bg-slate-700 text-slate-300')) ?>">
                                                <?= (int)$p['posicao'] ?>º
                                            </span>
                                            <div>
                                                <p class="text-xs font-semibold text-white leading-tight"><?= htmlspecialchars($p['nome']) ?></p>
                                                <span class="text-[10px] text-slate-400"><?= htmlspecialchars($p['horarioEntrega'] ?? '') ?></span>
                                            </div>
                                        </div>
                                        <span class="font-mono text-[11px] text-blue-400 font-medium">
                                            <?= htmlspecialchars($p['tempoDecorrido'] ?? '') ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
