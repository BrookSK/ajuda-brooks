<?php
/** @var string $systemName */
/** @var string $systemAiName */
/** @var string $systemSubtitle */
/** @var string $accentColor */
/** @var string $accentSoftColor */
/** @var string $logoPath */
/** @var string $faviconPath */
/** @var string $newsRssFeeds */
/** @var string $blockedDomains */
/** @var string $blockedSources */
/** @var bool $saved */
/** @var string|null $error */
?>
<div style="max-width: 720px; margin: 0 auto;">
    <h1 style="font-size: 24px; margin-bottom: 6px; font-weight: 650;">Personalização do sistema</h1>
    <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 14px; line-height: 1.55;">
        Redefina o nome do sistema, as cores, logos e configure as fontes de notícias.
    </p>

    <?php if (!empty($saved)): ?>
        <div style="background:#14361f; border-radius:10px; padding:10px 14px; color:#c1ffda; font-size:13px; margin-bottom:16px; border:1px solid #2ecc71;">
            ✓ Personalização salva com sucesso. Recarregue a página para ver as mudanças aplicadas no menu.
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="background:#311; border-radius:10px; padding:10px 14px; color:#ffbaba; font-size:13px; margin-bottom:16px; border:1px solid #a33;">
            ⚠ <?= htmlspecialchars((string)$error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/admin/personalizacao" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:16px;">

        <!-- ── Identidade ───────────────────────────────────────────── -->
        <div style="padding:16px 18px; border-radius:14px; border:1px solid var(--border-subtle); background:var(--surface-card);">
            <div style="font-size:12px; text-transform:uppercase; letter-spacing:.1em; color:var(--text-secondary); margin-bottom:14px; font-weight:600;">
                Identidade do sistema
            </div>
            <div style="display:flex; flex-direction:column; gap:12px;">

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">
                        Nome do sistema
                        <span style="font-weight:400; opacity:.7;">(aparece no topo do menu lateral)</span>
                    </label>
                    <input name="system_name"
                           value="<?= htmlspecialchars($systemName ?? 'Resenha 2.0') ?>"
                           style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                           placeholder="Ex: Resenha 2.0">
                </div>

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">
                        Nome da IA
                        <span style="font-weight:400; opacity:.7;">(substitui "Tuquinha" no sistema)</span>
                    </label>
                    <input name="system_ai_name"
                           value="<?= htmlspecialchars($systemAiName ?? 'Tuquinha') ?>"
                           style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                           placeholder="Ex: Tuquinha">
                    <small style="color:#777; font-size:11px;">Usado em botões de chat, seções do menu e cabeçalhos.</small>
                </div>

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">
                        Subtítulo da marca
                        <span style="font-weight:400; opacity:.7;">(linha abaixo do nome no menu)</span>
                    </label>
                    <input name="system_subtitle"
                           value="<?= htmlspecialchars($systemSubtitle ?? '') ?>"
                           style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                           placeholder="Ex: Branding vivo na veia">
                </div>

            </div>
        </div>

        <!-- ── Cores ────────────────────────────────────────────────── -->
        <div style="padding:16px 18px; border-radius:14px; border:1px solid var(--border-subtle); background:var(--surface-card);">
            <div style="font-size:12px; text-transform:uppercase; letter-spacing:.1em; color:var(--text-secondary); margin-bottom:14px; font-weight:600;">
                Cores do sistema
            </div>
            <div style="display:flex; gap:16px; flex-wrap:wrap;">

                <div style="flex:1; min-width:220px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Cor primária (accent)
                    </label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="color" id="accent_picker"
                               value="<?= htmlspecialchars($accentColor ?? '#e53935') ?>"
                               style="width:44px; height:38px; padding:2px 3px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); cursor:pointer; flex:0 0 auto;">
                        <input type="text" name="accent_color" id="accent_text"
                               value="<?= htmlspecialchars($accentColor ?? '#e53935') ?>"
                               style="flex:1; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                               placeholder="#e53935" maxlength="9">
                    </div>
                    <small style="color:#777; font-size:11px;">Botões primários, destaques e gradientes do menu.</small>
                </div>

                <div style="flex:1; min-width:220px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Cor secundária (accent-soft)
                    </label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="color" id="soft_picker"
                               value="<?= htmlspecialchars($accentSoftColor ?? '#ff6f60') ?>"
                               style="width:44px; height:38px; padding:2px 3px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); cursor:pointer; flex:0 0 auto;">
                        <input type="text" name="accent_soft_color" id="soft_text"
                               value="<?= htmlspecialchars($accentSoftColor ?? '#ff6f60') ?>"
                               style="flex:1; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                               placeholder="#ff6f60" maxlength="9">
                    </div>
                    <small style="color:#777; font-size:11px;">Gradientes e highlights suaves.</small>
                </div>

            </div>

            <!-- Preview de cores -->
            <div style="margin-top:14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div style="font-size:12px; color:var(--text-secondary);">Pré-visualização:</div>
                <div id="color_preview_btn" style="padding:7px 16px; border-radius:999px; font-size:13px; font-weight:600; color:#050509; cursor:default; background:linear-gradient(135deg, <?= htmlspecialchars($accentColor ?? '#e53935') ?>, <?= htmlspecialchars($accentSoftColor ?? '#ff6f60') ?>);">
                    Botão primário
                </div>
                <div id="color_preview_dot" style="width:14px; height:14px; border-radius:50%; background:<?= htmlspecialchars($accentColor ?? '#e53935') ?>;"></div>
                <div id="color_preview_badge" style="padding:4px 10px; border-radius:999px; font-size:11px; border:1px solid <?= htmlspecialchars($accentColor ?? '#e53935') ?>; color:<?= htmlspecialchars($accentSoftColor ?? '#ff6f60') ?>;">
                    Badge
                </div>
            </div>
        </div>

        <!-- ── Logo e Favicon ────────────────────────────────────────── -->
        <div style="padding:16px 18px; border-radius:14px; border:1px solid var(--border-subtle); background:var(--surface-card);">
            <div style="font-size:12px; text-transform:uppercase; letter-spacing:.1em; color:var(--text-secondary); margin-bottom:14px; font-weight:600;">
                Logo e Favicon
            </div>
            <div style="display:flex; flex-direction:column; gap:16px;">

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Logo do sistema
                        <span style="font-weight:400; opacity:.7;">(ícone circular no menu lateral)</span>
                    </label>
                    <?php if (!empty($logoPath)): ?>
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                            <img src="<?= htmlspecialchars($logoPath) ?>"
                                 alt="Logo atual"
                                 style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:1px solid var(--border-subtle);">
                            <span style="font-size:12px; color:var(--text-secondary);">Logo atual</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo_upload" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml"
                           style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;">
                    <small style="color:#777; font-size:11px;">PNG, JPG, SVG ou WebP. Recomendado: quadrado, mínimo 128 × 128 px.</small>
                </div>

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Favicon
                        <span style="font-weight:400; opacity:.7;">(ícone na aba do navegador)</span>
                    </label>
                    <?php if (!empty($faviconPath)): ?>
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                            <img src="<?= htmlspecialchars($faviconPath) ?>"
                                 alt="Favicon atual"
                                 style="width:32px; height:32px; object-fit:contain; border:1px solid var(--border-subtle); border-radius:4px; padding:2px; background:var(--input-bg);">
                            <span style="font-size:12px; color:var(--text-secondary);">Favicon atual</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="favicon_upload" accept="image/png,image/x-icon,image/gif,image/webp,image/svg+xml"
                           style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;">
                    <small style="color:#777; font-size:11px;">PNG ou ICO recomendado. Tamanho ideal: 64 × 64 px ou 512 × 512 px.</small>
                </div>

            </div>
        </div>

        <!-- ── Fontes de notícias ─────────────────────────────────── -->
        <div style="padding:16px 18px; border-radius:14px; border:1px solid var(--border-subtle); background:var(--surface-card);">
            <div style="font-size:12px; text-transform:uppercase; letter-spacing:.1em; color:var(--text-secondary); margin-bottom:4px; font-weight:600;">
                Fontes de notícias
            </div>
            <p style="font-size:13px; color:var(--text-secondary); margin-bottom:14px; line-height:1.5;">
                Links (RSS feeds) dos quais o sistema e a API do Perplexity buscam notícias de marketing. Uma URL por linha.
            </p>
            <div style="display:flex; flex-direction:column; gap:12px;">

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">
                        Feeds RSS
                        <span style="font-weight:400; opacity:.7;">(uma URL por linha)</span>
                    </label>
                    <textarea name="news_rss_feeds" rows="8"
                              style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px; resize:vertical; font-family:monospace; line-height:1.5;"
                    ><?= htmlspecialchars($newsRssFeeds ?? '') ?></textarea>
                    <small style="color:#777; font-size:11px;">Esses feeds são lidos junto com as buscas do Perplexity para compor o painel de notícias.</small>
                </div>

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">
                        Domínios bloqueados
                        <span style="font-weight:400; opacity:.7;">(um por linha)</span>
                    </label>
                    <textarea name="news_blocked_domains" rows="3"
                              style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px; resize:vertical; font-family:monospace; line-height:1.5;"
                    ><?= htmlspecialchars($blockedDomains ?? '') ?></textarea>
                    <small style="color:#777; font-size:11px;">Notícias vindas desses domínios serão ignoradas (ex: jornaldocomercio.com.br).</small>
                </div>

                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">
                        Fontes / veículos bloqueados
                        <span style="font-weight:400; opacity:.7;">(um nome por linha)</span>
                    </label>
                    <textarea name="news_blocked_sources" rows="3"
                              style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px; resize:vertical; font-family:monospace; line-height:1.5;"
                    ><?= htmlspecialchars($blockedSources ?? '') ?></textarea>
                    <small style="color:#777; font-size:11px;">Notícias cujo campo "source_name" contenha esses termos serão ignoradas.</small>
                </div>

            </div>
        </div>

        <!-- ── Botão salvar ──────────────────────────────────────── -->
        <div style="display:flex; align-items:center; gap:12px; padding-bottom:8px;">
            <button type="submit"
                    style="padding:11px 26px; border-radius:999px; border:none; background:linear-gradient(135deg,var(--accent),var(--accent-soft)); color:#050509; font-size:14px; font-weight:700; cursor:pointer; letter-spacing:.01em;">
                Salvar personalização
            </button>
            <span style="font-size:12px; color:var(--text-secondary);">As alterações de nome e cor entram em vigor na próxima carga de página.</span>
        </div>

    </form>
</div>

<script>
(function () {
    var accentPicker = document.getElementById('accent_picker');
    var accentText   = document.getElementById('accent_text');
    var softPicker   = document.getElementById('soft_picker');
    var softText     = document.getElementById('soft_text');
    var previewBtn   = document.getElementById('color_preview_btn');
    var previewDot   = document.getElementById('color_preview_dot');
    var previewBadge = document.getElementById('color_preview_badge');

    function isValidHex(v) {
        return /^#[0-9a-fA-F]{6}$/.test(v);
    }

    function updatePreview() {
        var a = accentText ? accentText.value.trim() : '#e53935';
        var s = softText   ? softText.value.trim()   : '#ff6f60';
        if (!isValidHex(a)) a = '#e53935';
        if (!isValidHex(s)) s = '#ff6f60';
        if (previewBtn)   previewBtn.style.background   = 'linear-gradient(135deg,' + a + ',' + s + ')';
        if (previewDot)   previewDot.style.background   = a;
        if (previewBadge) { previewBadge.style.borderColor = a; previewBadge.style.color = s; }
    }

    if (accentPicker && accentText) {
        accentPicker.addEventListener('input', function () {
            accentText.value = this.value;
            updatePreview();
        });
        accentText.addEventListener('input', function () {
            if (isValidHex(this.value.trim())) {
                accentPicker.value = this.value.trim();
            }
            updatePreview();
        });
    }

    if (softPicker && softText) {
        softPicker.addEventListener('input', function () {
            softText.value = this.value;
            updatePreview();
        });
        softText.addEventListener('input', function () {
            if (isValidHex(this.value.trim())) {
                softPicker.value = this.value.trim();
            }
            updatePreview();
        });
    }
})();
</script>
