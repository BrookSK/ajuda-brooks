<?php
/** @var array $suggestions */
/** @var int $pendingCount */
/** @var int $totalLearnings */
/** @var array $categories */
/** @var int $suggestionInterval */
?>
<div style="max-width: 960px; margin: 0 auto;">

    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:24px; font-weight:650; margin:0 0 4px;">Sugestões de Melhoria do Prompt</h1>
            <p style="color:#b0b0b0; font-size:13px; margin:0;">
                Geradas automaticamente pela IA ao analisar padrões acumulados.
                <?php if ($pendingCount > 0): ?>
                    <span style="background:#3a2a00; color:#fbbf24; padding:2px 10px; border-radius:20px; font-size:12px; margin-left:6px;">
                        <?= $pendingCount ?> pendente<?= $pendingCount !== 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>

        <form method="post" action="/admin/ia-sugestoes-prompt/intervalo" style="display:flex; align-items:center; gap:8px;">
            <label style="font-size:12px; color:#888;">Gerar sugestão a cada</label>
            <input type="number" name="ai_suggestion_interval" min="10" max="1000" value="<?= (int)$suggestionInterval ?>"
                style="width:70px; padding:5px 8px; border-radius:6px; border:1px solid #272727; background:#050509; color:#f5f5f5; font-size:13px;">
            <span style="font-size:12px; color:#888;">aprendizados</span>
            <button type="submit" style="padding:5px 12px; border-radius:6px; border:none; background:#1a1a2e; color:#a78bfa; font-size:12px; cursor:pointer;">Salvar</button>
        </form>
    </div>

    <!-- Biblioteca de categorias -->
    <?php if (!empty($categories)): ?>
    <div style="background:#0a0a14; border:1px solid #1e1e2e; border-radius:10px; padding:14px; margin-bottom:18px;">
        <div style="font-size:12px; color:#888; margin-bottom:8px;">
            📚 Biblioteca de conhecimento — <strong style="color:#f5f5f5;"><?= $totalLearnings ?></strong> aprendizados em <strong style="color:#f5f5f5;"><?= count($categories) ?></strong> categorias
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:6px;">
            <?php foreach ($categories as $cat): ?>
                <a href="/admin/ia-aprendizados?categoria=<?= urlencode((string)($cat['category'] ?? '')) ?>" style="
                    padding:3px 10px; border-radius:20px; font-size:11px; text-decoration:none;
                    background:#111128; color:#818cf8; border:1px solid #1e1e3a;
                ">
                    <?= htmlspecialchars((string)($cat['category'] ?? '')) ?>
                    <span style="color:#555; margin-left:4px;"><?= (int)($cat['total'] ?? 0) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($suggestions)): ?>
        <div style="text-align:center; padding:60px 20px; color:#555; font-size:14px;">
            Nenhuma sugestão ainda. Elas aparecem automaticamente a cada <?= (int)$suggestionInterval ?> aprendizados acumulados.
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <?php foreach ($suggestions as $sg): ?>
                <?php
                $status = (string)($sg['status'] ?? 'pending');
                $statusColor = $status === 'approved' ? '#4ade80' : ($status === 'rejected' ? '#f87171' : '#fbbf24');
                $statusBg = $status === 'approved' ? '#0a1e0a' : ($status === 'rejected' ? '#1e0a0a' : '#1e1600');
                $statusLabel = $status === 'approved' ? 'Aprovado' : ($status === 'rejected' ? 'Rejeitado' : 'Pendente');
                ?>
                <div style="background:#0d0d14; border:1px solid #1e1e2e; border-radius:10px; padding:14px;">
                    <div style="display:flex; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; color:#e0e0e0; line-height:1.6; white-space:pre-wrap; word-break:break-word;"><?= htmlspecialchars((string)($sg['suggestion'] ?? '')) ?></div>
                            <?php if (!empty($sg['rationale'])): ?>
                                <div style="margin-top:8px; font-size:12px; color:#666; font-style:italic;">
                                    Justificativa: <?= htmlspecialchars((string)$sg['rationale']) ?>
                                </div>
                            <?php endif; ?>
                            <div style="margin-top:8px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; font-size:11px; color:#555;">
                                <span style="padding:2px 10px; border-radius:20px; background:<?= $statusBg ?>; color:<?= $statusColor ?>;">
                                    <?= $statusLabel ?>
                                </span>
                                <span><?= htmlspecialchars(substr((string)($sg['created_at'] ?? ''), 0, 16)) ?></span>
                                <?php if (!empty($sg['applied_at'])): ?>
                                    <span style="color:#4ade80;">✓ Aplicado ao prompt em <?= htmlspecialchars(substr((string)$sg['applied_at'], 0, 16)) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:6px; flex-shrink:0;">
                            <?php if ($status === 'pending'): ?>
                                <form method="post" action="/admin/ia-sugestoes-prompt/aplicar">
                                    <input type="hidden" name="id" value="<?= (int)($sg['id'] ?? 0) ?>">
                                    <button type="submit" style="
                                        padding:6px 14px; border-radius:7px; border:none; cursor:pointer; font-size:12px; width:100%;
                                        background:#0a2a0a; color:#4ade80;
                                    " title="Aprova e adiciona ao system_prompt_extra">✓ Aplicar ao Prompt</button>
                                </form>
                                <form method="post" action="/admin/ia-sugestoes-prompt/aprovar">
                                    <input type="hidden" name="id" value="<?= (int)($sg['id'] ?? 0) ?>">
                                    <button type="submit" style="
                                        padding:6px 14px; border-radius:7px; border:none; cursor:pointer; font-size:12px; width:100%;
                                        background:#0a1a2a; color:#60a5fa;
                                    " title="Marca como aprovado sem aplicar automaticamente">Aprovar</button>
                                </form>
                                <form method="post" action="/admin/ia-sugestoes-prompt/rejeitar">
                                    <input type="hidden" name="id" value="<?= (int)($sg['id'] ?? 0) ?>">
                                    <button type="submit" style="
                                        padding:6px 14px; border-radius:7px; border:none; cursor:pointer; font-size:12px; width:100%;
                                        background:#2a0a0a; color:#f87171;
                                    ">Rejeitar</button>
                                </form>
                            <?php elseif ($status === 'approved' && empty($sg['applied_at'])): ?>
                                <form method="post" action="/admin/ia-sugestoes-prompt/aplicar">
                                    <input type="hidden" name="id" value="<?= (int)($sg['id'] ?? 0) ?>">
                                    <button type="submit" style="
                                        padding:6px 14px; border-radius:7px; border:none; cursor:pointer; font-size:12px;
                                        background:#0a2a0a; color:#4ade80;
                                    ">Aplicar ao Prompt</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="/admin/ia-sugestoes-prompt/deletar">
                                <input type="hidden" name="id" value="<?= (int)($sg['id'] ?? 0) ?>">
                                <button type="submit" style="
                                    background:none; border:none; cursor:pointer; color:#444; font-size:12px; padding:4px;
                                " onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#444'">Remover</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
