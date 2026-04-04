<?php
/** @var array $conversations */
/** @var string $toolName */
$safeToolName = htmlspecialchars($toolName);
?>

<div style="min-height:100dvh; padding-top:var(--safe-top);">
    <!-- Header -->
    <div style="display:flex; align-items:center; justify-content:space-between; padding:16px; border-bottom:1px solid var(--border);">
        <h1 style="font-size:20px; font-weight:700;">Conversas</h1>
        <a href="/m/chat?new=1" style="background:linear-gradient(135deg, var(--accent), var(--accent-soft)); color:#fff; text-decoration:none; border-radius:999px; padding:8px 16px; font-size:14px; font-weight:600; display:flex; align-items:center; gap:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            Novo
        </a>
    </div>

    <!-- Lista -->
    <div style="padding:8px 16px;">
        <?php if (empty($conversations)): ?>
            <div style="text-align:center; padding:60px 20px; color:var(--text-dim);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px; opacity:0.4;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <p style="font-size:15px;">Nenhuma conversa ainda</p>
                <p style="font-size:13px; margin-top:4px;">Comece uma conversa com <?= $safeToolName ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($conversations as $conv): ?>
                <a href="/m/chat?c=<?= (int)$conv['id'] ?>" style="display:flex; align-items:center; gap:12px; padding:14px 0; border-bottom:1px solid var(--border); text-decoration:none; color:var(--text);">
                    <div style="width:40px; height:40px; border-radius:12px; background:var(--bg-card); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:15px; font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?= htmlspecialchars($conv['title'] ?? 'Conversa sem título') ?>
                        </div>
                        <div style="font-size:12px; color:var(--text-dim); margin-top:2px;">
                            <?= !empty($conv['created_at']) ? date('d/m/Y H:i', strtotime($conv['created_at'])) : '' ?>
                        </div>
                    </div>
                    <?php if (!empty($conv['is_favorite'])): ?>
                        <span style="color:var(--accent); font-size:14px;">★</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bottom nav -->
    <div style="position:fixed; bottom:0; left:0; right:0; background:rgba(5,5,9,0.95); backdrop-filter:blur(20px); border-top:1px solid var(--border); padding-bottom:var(--safe-bottom);">
        <div style="display:flex; justify-content:space-around; padding:10px 0;">
            <a href="/m/chat" style="display:flex; flex-direction:column; align-items:center; gap:4px; text-decoration:none; color:var(--text-dim); font-size:11px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Chat
            </a>
            <a href="/m/historico" style="display:flex; flex-direction:column; align-items:center; gap:4px; text-decoration:none; color:var(--accent); font-size:11px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Histórico
            </a>
            <a href="/m/logout" style="display:flex; flex-direction:column; align-items:center; gap:4px; text-decoration:none; color:var(--text-dim); font-size:11px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                Sair
            </a>
        </div>
    </div>
</div>
