<?php
/** @var array $learnings */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */
/** @var bool $enabled */
/** @var int $pendingJobs */
/** @var string $cronBaseUrl */
/** @var string $cronTokenParam */
?>
<div style="max-width: 960px; margin: 0 auto;">

    <!-- I4/I5: Ações em batch -->
    <div style="background:#0a0a14; border:1px solid #1e1e2e; border-radius:10px; padding:14px; margin-bottom:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <span style="font-size:12px; color:#888; flex-shrink:0;">Ações em batch:</span>
        <?php if ($cronBaseUrl !== '' && $cronTokenParam !== ''): ?>
            <?php
            $b = htmlspecialchars($cronBaseUrl);
            $t = htmlspecialchars($cronTokenParam);
            ?>
            <a href="<?= $b ?>/cron/learning/mine-history?days=30&batch=20<?= $t ?>" target="_blank" style="
                padding:6px 14px; border-radius:7px; font-size:12px; text-decoration:none;
                background:#0a1a2a; color:#60a5fa; border:1px solid #1a2a3a;
            ">⛏ Minerar histórico (30 dias)</a>
            <a href="<?= $b ?>/cron/learning/consolidate?batch=20<?= $t ?>" target="_blank" style="
                padding:6px 14px; border-radius:7px; font-size:12px; text-decoration:none;
                background:#1a1a0a; color:#fbbf24; border:1px solid #2a2a1a;
            ">🗜 Consolidar por categoria</a>
            <a href="<?= $b ?>/cron/learning/embed-backfill?batch=50<?= $t ?>" target="_blank" style="
                padding:6px 14px; border-radius:7px; font-size:12px; text-decoration:none;
                background:#0a1a0a; color:#4ade80; border:1px solid #1a2a1a;
            ">🔢 Gerar embeddings pendentes</a>
            <a href="<?= $b ?>/cron/learning/process?batch=10<?= $t ?>" target="_blank" style="
                padding:6px 14px; border-radius:7px; font-size:12px; text-decoration:none;
                background:#1a0a1a; color:#c084fc; border:1px solid #2a1a2a;
            ">▶ Processar fila manualmente</a>
        <?php else: ?>
            <span style="color:#555; font-size:12px;">Configure <strong>app_public_url</strong> e <strong>news_cron_token</strong> nas configurações para habilitar as ações.</span>
        <?php endif; ?>
        <?php if ($pendingJobs > 0): ?>
            <span style="background:#3a1a3a; color:#c084fc; padding:3px 10px; border-radius:20px; font-size:11px; margin-left:auto;">
                <?= $pendingJobs ?> job<?= $pendingJobs !== 1 ? 's' : '' ?> na fila
            </span>
        <?php endif; ?>
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
        <div>
            <h1 style="font-size:24px; font-weight:650; margin:0 0 4px;">Aprendizados da IA</h1>
            <p style="color:#b0b0b0; font-size:13px; margin:0;">
                Biblioteca permanente de conhecimento. Total ativo: <strong style="color:#f5f5f5;"><?= $total ?></strong>
                <?php if ($total > 0): ?>
                    <?php
                    try {
                        $cats = \App\Models\AiLearning::distinctCategories();
                        if (!empty($cats)) {
                            echo ' &mdash; <span style="color:#818cf8;">' . count($cats) . ' categorias</span>';
                        }
                    } catch (\Throwable $e) {}
                    ?>
                <?php endif; ?>
            </p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <form method="post" action="/admin/ia-aprendizados/toggle">
                <button type="submit" style="
                    padding:7px 14px; border-radius:8px; border:none; cursor:pointer; font-size:13px;
                    background:<?= $enabled ? '#1e3a1e' : '#3a1e1e' ?>; color:<?= $enabled ? '#4ade80' : '#f87171' ?>;
                ">
                    <?= $enabled ? '✓ Aprendizado ativo' : '✗ Aprendizado desativado' ?>
                </button>
            </form>
            <form method="post" action="/admin/ia-aprendizados/limpar" onsubmit="return confirm('Apagar TODOS os aprendizados? Esta ação não pode ser desfeita.');">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" style="
                    padding:7px 14px; border-radius:8px; border:none; cursor:pointer; font-size:13px;
                    background:#2a1a1a; color:#f87171;
                ">Limpar todos</button>
            </form>
        </div>
    </div>

    <?php if (empty($learnings)): ?>
        <div style="text-align:center; padding:60px 20px; color:#666; font-size:14px;">
            Nenhum aprendizado registrado ainda. Eles aparecem automaticamente após as primeiras interações no chat.
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:6px;">
            <?php foreach ($learnings as $lr): ?>
                <div style="
                    background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px;
                    padding:12px 14px; display:flex; align-items:flex-start; gap:12px;
                ">
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; color:#e0e0e0; line-height:1.5; word-break:break-word;">
                            <?= htmlspecialchars((string)($lr['content'] ?? '')) ?>
                        </div>
                        <div style="margin-top:6px; display:flex; gap:10px; flex-wrap:wrap; font-size:11px; color:#555;">
                            <span style="
                                padding:2px 8px; border-radius:20px;
                                background:<?= ($lr['scope'] ?? '') === 'personality' ? '#1a1a3a' : '#1a2a1a' ?>;
                                color:<?= ($lr['scope'] ?? '') === 'personality' ? '#818cf8' : '#4ade80' ?>;
                            ">
                                <?= ($lr['scope'] ?? '') === 'personality'
                                    ? ('Personalidade: ' . htmlspecialchars((string)($lr['personality_name'] ?? 'ID '.$lr['scope_id'])))
                                    : 'Global' ?>
                            </span>
                            <span>Uso: <strong style="color:#aaa;"><?= (int)($lr['usage_count'] ?? 0) ?>×</strong></span>
                            <span><?= htmlspecialchars(substr((string)($lr['created_at'] ?? ''), 0, 16)) ?></span>
                            <?php if (!empty($lr['last_used_at'])): ?>
                                <span>Último uso: <?= htmlspecialchars(substr((string)$lr['last_used_at'], 0, 16)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="post" action="/admin/ia-aprendizados/deletar" style="flex-shrink:0;">
                        <input type="hidden" name="id" value="<?= (int)($lr['id'] ?? 0) ?>">
                        <button type="submit" title="Remover" style="
                            background:none; border:none; cursor:pointer; color:#555; font-size:16px; padding:2px 4px;
                        " onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#555'">✕</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div style="display:flex; gap:6px; margin-top:20px; justify-content:center; flex-wrap:wrap;">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="/admin/ia-aprendizados?page=<?= $p ?>" style="
                        padding:5px 12px; border-radius:6px; font-size:13px; text-decoration:none;
                        background:<?= $p === $page ? '#252540' : '#111' ?>;
                        color:<?= $p === $page ? '#f5f5f5' : '#777' ?>;
                        border:1px solid <?= $p === $page ? '#3a3a60' : '#1e1e1e' ?>;
                    "><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
