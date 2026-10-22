<?php
/**
 * Página de Históricos e Logs de Auditoria
 * Timer de Provas
 *
 * Atende ao requisito de auditoria: manter histórico e logs de qualquer
 * alteração feita por qualquer usuário para auditorias futuras.
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

$pdo = getDBConnection();

// Filtros de Pesquisa
$filtroAcao    = trim($_GET['acao'] ?? '');
$filtroTermo   = trim($_GET['termo'] ?? '');
$pagina        = max(1, (int) ($_GET['p'] ?? 1));
$itensPorPag   = 20;
$offset        = ($pagina - 1) * $itensPorPag;

// Construção da consulta com PDO seguro
$sqlWhere = ["1=1"];
$params = [];

if (!empty($filtroAcao)) {
    $sqlWhere[] = "acao = :acao";
    $params[':acao'] = $filtroAcao;
}

if (!empty($filtroTermo)) {
    $sqlWhere[] = "(detalhes LIKE :termo OR usuario_nome LIKE :termo OR ip LIKE :termo)";
    $params[':termo'] = "%{$filtroTermo}%";
}

$whereClause = implode(" AND ", $sqlWhere);

// Total de registros para paginação
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM logs_auditoria WHERE {$whereClause}");
$stmtCount->execute($params);
$totalRegistros = (int) $stmtCount->fetchColumn();
$totalPaginas = max(1, ceil($totalRegistros / $itensPorPag));

// Registros paginados
$stmtLogs = $pdo->prepare("
    SELECT id, usuario_id, usuario_nome, acao, detalhes, ip, user_agent, criado_em 
    FROM logs_auditoria 
    WHERE {$whereClause} 
    ORDER BY criado_em DESC, id DESC 
    LIMIT :limit OFFSET :offset
");

foreach ($params as $k => $v) {
    $stmtLogs->bindValue($k, $v);
}
$stmtLogs->bindValue(':limit', $itensPorPag, PDO::PARAM_INT);
$stmtLogs->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtLogs->execute();
$logs = $stmtLogs->fetchAll();

// Lista de ações distintas para o filtro
$stmtAcoes = $pdo->query("SELECT DISTINCT acao FROM logs_auditoria ORDER BY acao ASC");
$listaAcoes = $stmtAcoes->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Logs de Auditoria e Histórico";
require_once __DIR__ . '/includes/header.php';

function getBadgeAcao(string $acao): string {
    return match ($acao) {
        'LOGIN'             => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'LOGOUT'            => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        'CADASTRO'          => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
        'INICIO_TIMER'      => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'PAUSA_TIMER'       => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'RETOMADA_TIMER'    => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
        'ALERTA_DISPARADO'  => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
        'FINALIZOU_PROVA'   => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
        'REGISTRO_ENTREGA'  => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        default             => 'bg-slate-500/10 text-slate-300 border-slate-500/20'
    };
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Cabeçalho da Página -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>🛡️</span> Histórico de Auditoria & Logs
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Registro inalterável de todas as ações e alterações realizadas no sistema
            </p>
        </div>
        <div class="px-3.5 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-300">
            Total de Registros: <span class="text-blue-400"><?= number_format($totalRegistros, 0, ',', '.') ?></span>
        </div>
    </div>

    <!-- Filtros de Busca (Mobile-First) -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 mb-6 backdrop-blur">
        <form method="GET" action="historico.php" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipo de Ação</label>
                <select name="acao" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:outline-none focus:border-blue-500">
                    <option value="">Todas as Ações</option>
                    <?php foreach ($listaAcoes as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>" <?= $filtroAcao === $a ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pesquisar Detalhes / Usuário / IP</label>
                <input type="text" name="termo" value="<?= htmlspecialchars($filtroTermo) ?>" placeholder="Buscar palavra-chave..."
                       class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:outline-none focus:border-blue-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-blue-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Filtrar
                </button>
                <?php if (!empty($filtroAcao) || !empty($filtroTermo)): ?>
                    <a href="historico.php" class="py-2 px-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs transition">
                        Limpar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela / Cards de Registros -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl backdrop-blur">
        
        <?php if (empty($logs)): ?>
            <div class="p-12 text-center">
                <span class="text-4xl block mb-2 opacity-40">🔍</span>
                <h3 class="text-base font-semibold text-white">Nenhum registro de log encontrado</h3>
                <p class="text-xs text-slate-400 mt-1">Tente ajustar os filtros de pesquisa para visualizar outros eventos.</p>
            </div>
        <?php else: ?>
            <!-- Versão Desktop (Tabela Completa) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900/60 border-b border-slate-700 uppercase tracking-wider text-slate-400 font-semibold">
                        <tr>
                            <th class="py-3.5 px-4">Data & Horário</th>
                            <th class="py-3.5 px-4">Ação</th>
                            <th class="py-3.5 px-4">Coordenador / Usuário</th>
                            <th class="py-3.5 px-4">Detalhes do Evento</th>
                            <th class="py-3.5 px-4">Endereço IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60 text-slate-300">
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-slate-700/20 transition">
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    <?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold border <?= getBadgeAcao($log['acao']) ?>">
                                        <?= htmlspecialchars($log['acao']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-200 whitespace-nowrap">
                                    <?= htmlspecialchars($log['usuario_nome'] ?: 'Sistema / Convidado') ?>
                                </td>
                                <td class="py-3 px-4 max-w-md truncate text-slate-300" title="<?= htmlspecialchars($log['detalhes'] ?? '') ?>">
                                    <?= htmlspecialchars($log['detalhes'] ?: '—') ?>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    <?= htmlspecialchars($log['ip'] ?: '127.0.0.1') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Versão Mobile (Cards Responsivos) -->
            <div class="block md:hidden divide-y divide-slate-700/60">
                <?php foreach ($logs as $log): ?>
                    <div class="p-4 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold border <?= getBadgeAcao($log['acao']) ?>">
                                <?= htmlspecialchars($log['acao']) ?>
                            </span>
                            <span class="text-[10px] font-mono text-slate-400">
                                <?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?>
                            </span>
                        </div>
                        <p class="text-xs text-white font-medium">
                            <?= htmlspecialchars($log['usuario_nome'] ?: 'Sistema / Convidado') ?>
                        </p>
                        <p class="text-xs text-slate-300 break-words">
                            <?= htmlspecialchars($log['detalhes'] ?: 'Sem detalhes adicionais.') ?>
                        </p>
                        <div class="text-[10px] font-mono text-slate-500 pt-1">
                            IP: <?= htmlspecialchars($log['ip'] ?: '127.0.0.1') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginação -->
            <?php if ($totalPaginas > 1): ?>
                <div class="p-4 border-t border-slate-700/80 bg-slate-900/40 flex items-center justify-between text-xs text-slate-400">
                    <div>
                        Página <?= $pagina ?> de <?= $totalPaginas ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($pagina > 1): ?>
                            <a href="?p=<?= $pagina - 1 ?>&acao=<?= urlencode($filtroAcao) ?>&termo=<?= urlencode($filtroTermo) ?>"
                               class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                                ← Anterior
                            </a>
                        <?php endif; ?>

                        <?php if ($pagina < $totalPaginas): ?>
                            <a href="?p=<?= $pagina + 1 ?>&acao=<?= urlencode($filtroAcao) ?>&termo=<?= urlencode($filtroTermo) ?>"
                               class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                                Próxima →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
