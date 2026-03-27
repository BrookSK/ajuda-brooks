<?php
/** @var int $totalLearnings */
/** @var int $totalGlobal */
/** @var int $totalPersonality */
/** @var int $totalConsolidated */
/** @var array $categories */
/** @var array $qualityDist */
/** @var array $recentLearnings */
/** @var int $filteredTotal */
/** @var int $totalPages */
/** @var int $page */
/** @var string $filterCategory */
/** @var string $filterScope */
/** @var array $jobStats */
/** @var array $recentJobs */
/** @var array $rhythmByDay */
/** @var string $filterType */
/** @var array $typeStats */

$maxRhythm = max(1, max(array_values($rhythmByDay)));
$pendingJobs = (int)($jobStats['pending'] ?? 0) + (int)($jobStats['running'] ?? 0);
$cntFact       = (int)($typeStats['fact'] ?? 0);
$cntExperience = (int)($typeStats['experience'] ?? 0);
$cntWarning    = (int)($typeStats['warning'] ?? 0);
?>
<div style="max-width:1080px; margin:0 auto;">

    <!-- Header -->
    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
            <h1 style="font-size:24px; font-weight:650; margin:0 0 4px;">Logs de Aprendizado da IA</h1>
            <p style="color:#b0b0b0; font-size:13px; margin:0;">
                Monitoramento em tempo real do que a IA está aprendendo.
                <?php if ($pendingJobs > 0): ?>
                    <span id="queue-badge" style="background:#3a1a3a; color:#c084fc; padding:2px 10px; border-radius:20px; font-size:11px; margin-left:6px;">
                        <?= $pendingJobs ?> processando…
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <div id="live-indicator" style="display:flex; align-items:center; gap:6px; font-size:12px; color:#4ade80;">
                <span style="width:8px; height:8px; border-radius:50%; background:#4ade80; display:inline-block; animation:pulse-green 2s infinite;"></span>
                Monitorando
            </div>
            <span style="color:#333; font-size:12px;">|</span>
            <span style="font-size:12px; color:#555;" id="last-update">agora</span>
        </div>
    </div>

    <!-- Tipo breakdown strip -->
    <div style="display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap;">
        <a href="/admin/ia-logs<?= $filterCategory !== '' ? '?categoria='.urlencode($filterCategory) : '' ?>" style="
            flex:1; min-width:120px; padding:10px 14px; border-radius:8px; text-decoration:none;
            background:<?= $filterType === '' ? '#1a1a2e' : '#0d0d14' ?>;
            border:1px solid <?= $filterType === '' ? '#818cf8' : '#1e1e2e' ?>; text-align:center;">
            <div style="font-size:20px; font-weight:700; color:#f5f5f5;"><?= $totalLearnings ?></div>
            <div style="font-size:11px; color:#888; margin-top:2px;">📖 Todos</div>
        </a>
        <a href="/admin/ia-logs?ltype=fact<?= $filterCategory !== '' ? '&categoria='.urlencode($filterCategory) : '' ?>" style="
            flex:1; min-width:120px; padding:10px 14px; border-radius:8px; text-decoration:none;
            background:<?= $filterType === 'fact' ? '#111' : '#0d0d14' ?>;
            border:1px solid <?= $filterType === 'fact' ? '#555' : '#1e1e2e' ?>; text-align:center;">
            <div style="font-size:20px; font-weight:700; color:#aaa;"><?= $cntFact ?></div>
            <div style="font-size:11px; color:#666; margin-top:2px;">📚 Fatos</div>
        </a>
        <a href="/admin/ia-logs?ltype=experience<?= $filterCategory !== '' ? '&categoria='.urlencode($filterCategory) : '' ?>" style="
            flex:1; min-width:120px; padding:10px 14px; border-radius:8px; text-decoration:none;
            background:<?= $filterType === 'experience' ? '#0a1a2a' : '#0d0d14' ?>;
            border:1px solid <?= $filterType === 'experience' ? '#60a5fa' : '#1e1e2e' ?>; text-align:center;">
            <div style="font-size:20px; font-weight:700; color:#60a5fa;"><?= $cntExperience ?></div>
            <div style="font-size:11px; color:#60a5fa80; margin-top:2px;">💡 Experiências</div>
        </a>
        <a href="/admin/ia-logs?ltype=warning<?= $filterCategory !== '' ? '&categoria='.urlencode($filterCategory) : '' ?>" style="
            flex:1; min-width:120px; padding:10px 14px; border-radius:8px; text-decoration:none;
            background:<?= $filterType === 'warning' ? '#2a1a00' : '#0d0d14' ?>;
            border:1px solid <?= $filterType === 'warning' ? '#fbbf24' : '#1e1e2e' ?>; text-align:center;">
            <div style="font-size:20px; font-weight:700; color:#fbbf24;"><?= $cntWarning ?></div>
            <div style="font-size:11px; color:#fbbf2480; margin-top:2px;">⚠️ Alertas</div>
        </a>
    </div>

    <!-- Stats cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:10px; margin-bottom:20px;">
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:14px;">
            <div style="font-size:28px; font-weight:700; color:#818cf8;" id="stat-total"><?= number_format($totalLearnings) ?></div>
            <div style="font-size:11px; color:#666; margin-top:3px;">Aprendizados totais</div>
        </div>
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:14px;">
            <div style="font-size:28px; font-weight:700; color:#60a5fa;"><?= count($categories) ?></div>
            <div style="font-size:11px; color:#666; margin-top:3px;">Categorias</div>
        </div>
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:14px;">
            <div style="font-size:28px; font-weight:700; color:#4ade80;"><?= $totalGlobal ?></div>
            <div style="font-size:11px; color:#666; margin-top:3px;">Conhecimento global</div>
        </div>
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:14px;">
            <div style="font-size:28px; font-weight:700; color:#fbbf24;"><?= $totalConsolidated ?></div>
            <div style="font-size:11px; color:#666; margin-top:3px;">Consolidados</div>
        </div>
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:14px;">
            <div style="font-size:28px; font-weight:700; color:<?= $pendingJobs > 0 ? '#c084fc' : '#333' ?>;" id="stat-queue"><?= $pendingJobs ?></div>
            <div style="font-size:11px; color:#666; margin-top:3px;">Na fila agora</div>
        </div>
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:14px;">
            <div style="font-size:28px; font-weight:700; color:#f87171;"><?= (int)($jobStats['error'] ?? 0) ?></div>
            <div style="font-size:11px; color:#666; margin-top:3px;">Erros de extração</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:14px; margin-bottom:20px;">

        <!-- Gráfico de ritmo de aprendizado (14 dias) -->
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:16px;">
            <div style="font-size:13px; font-weight:600; color:#888; margin-bottom:12px;">📈 Ritmo de aprendizado — últimos 14 dias</div>
            <div style="display:flex; align-items:flex-end; gap:3px; height:80px;">
                <?php foreach ($rhythmByDay as $day => $cnt): ?>
                    <?php
                    $pct    = $maxRhythm > 0 ? round(($cnt / $maxRhythm) * 100) : 0;
                    $height = max(2, $pct);
                    $isToday = $day === date('Y-m-d');
                    $color  = $isToday ? '#818cf8' : '#1e2a4a';
                    $label  = date('d/M', strtotime($day));
                    ?>
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px; cursor:default;"
                         title="<?= $label ?>: <?= $cnt ?> aprendizados">
                        <div style="width:100%; height:<?= $height ?>%; background:<?= $color ?>; border-radius:3px 3px 0 0; min-height:2px; transition:background 0.2s;"
                             onmouseover="this.style.background='#818cf8'" onmouseout="this.style.background='<?= $color ?>'"></div>
                        <?php if ($cnt > 0): ?>
                            <div style="font-size:9px; color:#555;"><?= $cnt ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:4px;">
                <span style="font-size:10px; color:#333;"><?= array_key_first($rhythmByDay) ?></span>
                <span style="font-size:10px; color:#333;">hoje</span>
            </div>
        </div>

        <!-- Distribuição de qualidade -->
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:16px;">
            <div style="font-size:13px; font-weight:600; color:#888; margin-bottom:12px;">⭐ Distribuição de qualidade</div>
            <?php if (!empty($qualityDist)): ?>
                <?php
                $totalQ = array_sum(array_column($qualityDist, 'cnt'));
                foreach ($qualityDist as $q):
                    $score = (int)$q['quality_score'];
                    $pct   = $totalQ > 0 ? round(($q['cnt'] / $totalQ) * 100) : 0;
                    $color = $score >= 9 ? '#4ade80' : ($score >= 7 ? '#818cf8' : '#f87171');
                ?>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
                    <span style="font-size:11px; color:#888; width:20px; text-align:right;"><?= $score ?></span>
                    <div style="flex:1; background:#111; border-radius:4px; height:14px; overflow:hidden;">
                        <div style="width:<?= $pct ?>%; height:100%; background:<?= $color ?>; border-radius:4px;"></div>
                    </div>
                    <span style="font-size:11px; color:#666; width:28px;"><?= $q['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:#444; font-size:12px;">Nenhum dado ainda</div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Filtros + Feed ao vivo -->
    <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:16px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div style="font-size:13px; font-weight:600; color:#e0e0e0;">
                🧠 Novos conhecimentos
                <span id="live-new-badge" style="display:none; background:#4ade80; color:#000; border-radius:20px; padding:1px 8px; font-size:11px; margin-left:6px; font-weight:700;"></span>
            </div>
            <!-- Filtros -->
            <form method="get" action="/admin/ia-logs" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <select name="categoria" onchange="this.form.submit()" style="
                    padding:5px 10px; border-radius:6px; border:1px solid #272727;
                    background:#050509; color:#f5f5f5; font-size:12px;">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars((string)($cat['category'] ?? '')) ?>"
                            <?= $filterCategory === ($cat['category'] ?? '') ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)($cat['category'] ?? '')) ?>
                            (<?= (int)($cat['total'] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="scope" onchange="this.form.submit()" style="
                    padding:5px 10px; border-radius:6px; border:1px solid #272727;
                    background:#050509; color:#f5f5f5; font-size:12px;">
                    <option value="">Global + Personalidades</option>
                    <option value="global" <?= $filterScope === 'global' ? 'selected' : '' ?>>Só Global</option>
                    <option value="personality" <?= $filterScope === 'personality' ? 'selected' : '' ?>>Só Personalidade</option>
                </select>
                <select name="ltype" onchange="this.form.submit()" style="
                    padding:5px 10px; border-radius:6px; border:1px solid #272727;
                    background:#050509; color:#f5f5f5; font-size:12px;">
                    <option value="">Todos os tipos</option>
                    <option value="fact" <?= $filterType === 'fact' ? 'selected' : '' ?>>📚 Fatos</option>
                    <option value="experience" <?= $filterType === 'experience' ? 'selected' : '' ?>>💡 Experiências</option>
                    <option value="warning" <?= $filterType === 'warning' ? 'selected' : '' ?>>⚠️ Alertas</option>
                </select>
                <?php if ($filterCategory !== '' || $filterScope !== '' || $filterType !== ''): ?>
                    <a href="/admin/ia-logs" style="font-size:11px; color:#f87171; text-decoration:none;">✕ Limpar filtro</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Feed de novos aprendizados (injetado via JS) -->
        <div id="live-feed" style="display:flex; flex-direction:column; gap:4px; margin-bottom:10px;"></div>

        <!-- Tabela de aprendizados paginada -->
        <div id="learnings-list" style="display:flex; flex-direction:column; gap:4px;">
            <?php foreach ($recentLearnings as $lr): ?>
                <?php
                $q     = (int)($lr['quality_score'] ?? 0);
                $qColor = $q >= 9 ? '#4ade80' : ($q >= 7 ? '#818cf8' : '#888');
                $scopeLabel = $lr['scope'] === 'personality'
                    ? '🎭 ' . htmlspecialchars((string)($lr['personality_name'] ?? 'persona'))
                    : '🌐 global';
                $isConsolidated = !empty($lr['is_consolidated']);
                $ltype = (string)($lr['learning_type'] ?? 'fact');
                $typeIcon  = $ltype === 'warning' ? '⚠️' : ($ltype === 'experience' ? '💡' : '📚');
                $typeLabel = $ltype === 'warning' ? 'Alerta' : ($ltype === 'experience' ? 'Experiência' : 'Fato');
                $typeBg    = $ltype === 'warning' ? '#2a1a00' : ($ltype === 'experience' ? '#0a1a2a' : '#111');
                $typeColor = $ltype === 'warning' ? '#fbbf24' : ($ltype === 'experience' ? '#60a5fa' : '#555');
                ?>
                <div style="display:flex; gap:10px; align-items:flex-start; padding:8px 10px; border-radius:7px; background:#070710; border:1px solid #12121e;">
                    <!-- Quality badge -->
                    <div style="flex-shrink:0; width:24px; height:24px; border-radius:50%; background:#111; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:<?= $qColor ?>; border:1px solid <?= $qColor ?>20;">
                        <?= $q > 0 ? $q : '?' ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; color:#d0d0d0; line-height:1.5;"><?= htmlspecialchars((string)($lr['content'] ?? '')) ?></div>
                        <div style="display:flex; gap:8px; margin-top:4px; flex-wrap:wrap; font-size:11px; align-items:center;">
                            <span style="padding:1px 9px; border-radius:20px; background:<?= $typeBg ?>; color:<?= $typeColor ?>; border:1px solid <?= $typeColor ?>30;">
                                <?= $typeIcon ?> <?= $typeLabel ?>
                            </span>
                            <?php if (!empty($lr['category'])): ?>
                                <a href="/admin/ia-logs?categoria=<?= urlencode((string)$lr['category']) ?>"
                                   style="padding:1px 8px; border-radius:20px; background:#111128; color:#818cf8; text-decoration:none;">
                                    <?= htmlspecialchars((string)$lr['category']) ?>
                                </a>
                            <?php endif; ?>
                            <span style="color:#444;"><?= $scopeLabel ?></span>
                            <?php if ($isConsolidated): ?>
                                <span style="padding:1px 8px; border-radius:20px; background:#1a2a0a; color:#4ade80;">consolidado</span>
                            <?php endif; ?>
                            <?php if (($lr['usage_count'] ?? 0) > 0): ?>
                                <span style="color:#444;">usado <?= (int)$lr['usage_count'] ?>×</span>
                            <?php endif; ?>
                            <span style="color:#333;"><?= htmlspecialchars(substr((string)($lr['created_at'] ?? ''), 0, 16)) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($recentLearnings)): ?>
                <div style="text-align:center; padding:40px 20px; color:#444; font-size:13px;">
                    Nenhum aprendizado encontrado<?= $filterCategory !== '' ? ' na categoria "' . htmlspecialchars($filterCategory) . '"' : '' ?>.
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginação -->
        <?php if ($totalPages > 1): ?>
            <div style="display:flex; justify-content:center; gap:6px; margin-top:14px; flex-wrap:wrap;">
                <?php for ($i = 1; $i <= min($totalPages, 20); $i++): ?>
                    <?php
                    $params = [];
                    if ($filterCategory !== '') $params['categoria'] = $filterCategory;
                    if ($filterScope !== '')    $params['scope']     = $filterScope;
                    $params['page'] = $i;
                    $url = '/admin/ia-logs?' . http_build_query($params);
                    ?>
                    <a href="<?= $url ?>" style="
                        padding:5px 12px; border-radius:6px; font-size:12px; text-decoration:none;
                        background:<?= $page === $i ? '#818cf8' : '#111' ?>;
                        color:<?= $page === $i ? '#fff' : '#666' ?>;
                        border:1px solid <?= $page === $i ? '#818cf8' : '#1e1e2e' ?>;
                    "><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Status da fila + jobs recentes -->
    <div style="display:grid; grid-template-columns:1fr 2fr; gap:14px; margin-bottom:20px;">

        <!-- Status dos jobs -->
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:16px;">
            <div style="font-size:13px; font-weight:600; color:#888; margin-bottom:12px;">⚙️ Fila de extração</div>
            <?php
            $statusColors = ['pending'=>'#fbbf24','running'=>'#60a5fa','done'=>'#4ade80','error'=>'#f87171'];
            $statusLabels = ['pending'=>'Aguardando','running'=>'Processando','done'=>'Concluídos','error'=>'Erros'];
            foreach ($statusColors as $st => $color):
                $cnt = (int)($jobStats[$st] ?? 0);
                if ($cnt === 0 && !in_array($st, ['pending','running'])) continue;
            ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #111;">
                    <span style="font-size:12px; color:#888;"><?= $statusLabels[$st] ?></span>
                    <span style="font-size:14px; font-weight:700; color:<?= $color ?>;" id="job-<?= $st ?>"><?= $cnt ?></span>
                </div>
            <?php endforeach; ?>
            <div style="margin-top:10px; font-size:11px; color:#333;">Atualizado a cada 10s</div>
        </div>

        <!-- Últimos jobs processados -->
        <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:16px;">
            <div style="font-size:13px; font-weight:600; color:#888; margin-bottom:12px;">📋 Últimas extrações</div>
            <div style="display:flex; flex-direction:column; gap:5px;">
                <?php foreach ($recentJobs as $job): ?>
                    <?php
                    $st    = (string)($job['status'] ?? '');
                    $col   = $statusColors[$st] ?? '#888';
                    $icon  = $st === 'done' ? '✓' : ($st === 'error' ? '✗' : ($st === 'running' ? '↻' : '…'));
                    $time  = substr((string)($job['done_at'] ?? $job['started_at'] ?? $job['created_at'] ?? ''), 0, 16);
                    ?>
                    <div style="display:flex; gap:8px; align-items:center; font-size:12px; padding:4px 0; border-bottom:1px solid #0a0a14;">
                        <span style="color:<?= $col ?>; font-weight:700; width:14px;"><?= $icon ?></span>
                        <span style="color:#555; flex:1;">Conversa #<?= (int)($job['conversation_id'] ?? 0) ?></span>
                        <span style="color:#333;"><?= $time ?></span>
                        <?php if ($st === 'error' && !empty($job['error_text'])): ?>
                            <span style="color:#f87171; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                  title="<?= htmlspecialchars((string)$job['error_text']) ?>">
                                <?= htmlspecialchars(substr((string)$job['error_text'], 0, 40)) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($recentJobs)): ?>
                    <div style="color:#444; font-size:12px; text-align:center; padding:20px;">Nenhum job ainda</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Biblioteca de categorias -->
    <?php if (!empty($categories)): ?>
    <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#888; margin-bottom:12px;">📚 Biblioteca de categorias</div>
        <div style="display:flex; flex-wrap:wrap; gap:6px;">
            <?php
            $maxCat = max(1, max(array_column($categories, 'total')));
            foreach ($categories as $cat):
                $pct   = round(($cat['total'] / $maxCat) * 100);
                $alpha = max(40, $pct);
            ?>
                <a href="/admin/ia-logs?categoria=<?= urlencode((string)($cat['category'] ?? '')) ?>"
                   style="padding:4px 12px; border-radius:20px; font-size:12px; text-decoration:none;
                          background:rgba(129,140,248,<?= $alpha / 255 ?>); color:#e0e0ff; border:1px solid #2a2a4a;
                          <?= $filterCategory === ($cat['category'] ?? '') ? 'outline:2px solid #818cf8;' : '' ?>">
                    <?= htmlspecialchars((string)($cat['category'] ?? '')) ?>
                    <span style="opacity:0.6; margin-left:4px;"><?= (int)($cat['total'] ?? 0) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
@keyframes pulse-green {
    0%, 100% { opacity:1; }
    50%       { opacity:0.3; }
}
</style>

<script>
// ── Polling em tempo real ─────────────────────────────────────────────────────
let lastSince = '';
let newCount  = 0;

async function pollLearnings() {
    try {
        const url = '/admin/ia-logs/live' + (lastSince ? '?since=' + encodeURIComponent(lastSince) : '');
        const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) return;
        const data = await res.json();

        // Atualiza total
        const statTotal = document.getElementById('stat-total');
        if (statTotal) statTotal.textContent = data.total.toLocaleString('pt-BR');

        // Atualiza fila
        const statQueue = document.getElementById('stat-queue');
        if (statQueue) statQueue.textContent = data.pendingJobs;
        statQueue && (statQueue.style.color = data.pendingJobs > 0 ? '#c084fc' : '#333');

        const queueBadge = document.getElementById('queue-badge');
        if (queueBadge) {
            if (data.pendingJobs > 0) {
                queueBadge.style.display = 'inline';
                queueBadge.textContent = data.pendingJobs + ' processando…';
            } else {
                queueBadge.style.display = 'none';
            }
        }

        // Injeta novos itens no feed ao vivo
        if (data.items && data.items.length > 0) {
            const feed = document.getElementById('live-feed');
            if (feed) {
                data.items.forEach(item => {
                    // Evita duplicatas
                    if (document.getElementById('live-item-' + item.id)) return;

                    newCount++;
                    const qColor = item.quality_score >= 9 ? '#4ade80'
                                 : item.quality_score >= 7 ? '#818cf8' : '#888';

                    const typeMap = {
                        warning:    { icon: '⚠️', label: 'Alerta',      bg: '#2a1a00', color: '#fbbf24' },
                        experience: { icon: '💡', label: 'Experiência',  bg: '#0a1a2a', color: '#60a5fa' },
                        fact:       { icon: '📚', label: 'Fato',         bg: '#111',    color: '#555' },
                    };
                    const tm = typeMap[item.learning_type] || typeMap['fact'];

                    const el = document.createElement('div');
                    el.id = 'live-item-' + item.id;
                    el.style.cssText = 'display:flex;gap:10px;align-items:flex-start;padding:8px 10px;border-radius:7px;background:#0a0a18;border:1px solid #818cf820;animation:fadeInDown 0.4s ease;';
                    el.innerHTML = `
                        <div style="flex-shrink:0;width:24px;height:24px;border-radius:50%;background:#111;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:${qColor};border:1px solid ${qColor}30;">
                            ${item.quality_score || '?'}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;color:#d0d0d0;line-height:1.5;">${escapeHtml(item.content)}</div>
                            <div style="display:flex;gap:8px;margin-top:4px;font-size:11px;align-items:center;">
                                <span style="padding:1px 9px;border-radius:20px;background:${tm.bg};color:${tm.color};border:1px solid ${tm.color}30;">${tm.icon} ${tm.label}</span>
                                ${item.category ? `<span style="padding:1px 8px;border-radius:20px;background:#111128;color:#818cf8;">${escapeHtml(item.category)}</span>` : ''}
                                <span style="color:#333;">${item.created_at ? item.created_at.substring(0,16) : ''}</span>
                                <span style="padding:1px 7px;border-radius:20px;background:#0a2a0a;color:#4ade80;font-weight:700;">NOVO</span>
                            </div>
                        </div>`;
                    feed.prepend(el);

                    if (lastSince < item.created_at) lastSince = item.created_at;
                });

                const badge = document.getElementById('live-new-badge');
                if (badge && newCount > 0) {
                    badge.style.display = 'inline';
                    badge.textContent = '+' + newCount + ' novo' + (newCount > 1 ? 's' : '');
                }
            }
        }

        const lastUpd = document.getElementById('last-update');
        if (lastUpd) {
            const now = new Date();
            lastUpd.textContent = 'atualizado ' + now.toLocaleTimeString('pt-BR', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
        }

    } catch(e) {
        // Silencia falhas de rede
    }
}

function escapeHtml(t) {
    return String(t)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Inicia polling
pollLearnings();
setInterval(pollLearnings, 10000);
</script>

<style>
@keyframes fadeInDown {
    from { opacity:0; transform:translateY(-8px); }
    to   { opacity:1; transform:translateY(0); }
}
</style>
