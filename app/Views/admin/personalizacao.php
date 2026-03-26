<?php
/** @var string $systemName */
/** @var string $systemAiName */
/** @var string $systemSubtitle */
/** @var string $accentColor */
/** @var string $accentSoftColor */
/** @var string $btnTextColor */
/** @var string $btnStyle */
/** @var string $btnBorderColor */
/** @var int $btnBorderWidth */
/** @var string $iconColor */
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

                <div style="flex:1; min-width:220px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Cor do texto dos botões
                    </label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="color" id="btn_text_picker"
                               value="<?= htmlspecialchars($btnTextColor ?? '#050509') ?>"
                               style="width:44px; height:38px; padding:2px 3px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); cursor:pointer; flex:0 0 auto;">
                        <input type="text" name="btn_text_color" id="btn_text_text"
                               value="<?= htmlspecialchars($btnTextColor ?? '#050509') ?>"
                               style="flex:1; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                               placeholder="#050509" maxlength="9">
                    </div>
                    <small style="color:#777; font-size:11px;">Texto sobre o fundo dos botões primários. Use branco (#ffffff) para cores escuras.</small>
                </div>

                <div style="flex:1 1 100%; margin-top:4px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:8px;">
                        Estilo do fundo dos botões
                    </label>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <label id="btn_style_label_gradient" style="display:flex; align-items:center; gap:8px; padding:10px 16px; border-radius:10px; border:2px solid <?= ($btnStyle ?? 'gradient') === 'gradient' ? 'var(--accent)' : 'var(--border-subtle)' ?>; background:<?= ($btnStyle ?? 'gradient') === 'gradient' ? 'var(--surface-card)' : 'transparent' ?>; cursor:pointer; transition:border-color .15s;">
                            <input type="radio" name="btn_style" id="btn_style_gradient" value="gradient"
                                   <?= ($btnStyle ?? 'gradient') === 'gradient' ? 'checked' : '' ?>
                                   style="accent-color:var(--accent);">
                            <span style="font-size:13px; font-weight:600;">Gradiente</span>
                            <span style="display:inline-block; width:60px; height:22px; border-radius:999px; background:linear-gradient(135deg,<?= htmlspecialchars($accentColor ?? '#e53935') ?>,<?= htmlspecialchars($accentSoftColor ?? '#ff6f60') ?>);"></span>
                        </label>
                        <label id="btn_style_label_solid" style="display:flex; align-items:center; gap:8px; padding:10px 16px; border-radius:10px; border:2px solid <?= ($btnStyle ?? 'gradient') === 'solid' ? 'var(--accent)' : 'var(--border-subtle)' ?>; background:<?= ($btnStyle ?? 'gradient') === 'solid' ? 'var(--surface-card)' : 'transparent' ?>; cursor:pointer; transition:border-color .15s;">
                            <input type="radio" name="btn_style" id="btn_style_solid" value="solid"
                                   <?= ($btnStyle ?? 'gradient') === 'solid' ? 'checked' : '' ?>
                                   style="accent-color:var(--accent);">
                            <span style="font-size:13px; font-weight:600;">Sólido</span>
                            <span id="btn_style_solid_preview" style="display:inline-block; width:60px; height:22px; border-radius:999px; background:<?= htmlspecialchars($accentColor ?? '#e53935') ?>;"></span>
                        </label>
                    </div>
                    <small style="color:#777; font-size:11px; margin-top:4px; display:block;">
                        No modo <strong>Gradiente</strong> as duas cores são usadas. No modo <strong>Sólido</strong> apenas a cor primária é usada.
                    </small>
                </div>

                <!-- Borda dos botões -->
                <div style="flex:1 1 100%; margin-top:4px; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
                    <div style="flex:1; min-width:180px;">
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                            Cor da borda dos botões
                        </label>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="color" id="btn_border_color_picker"
                                   value="<?= htmlspecialchars(($btnBorderColor ?? 'transparent') === 'transparent' ? '#e53935' : ($btnBorderColor ?? '#e53935')) ?>"
                                   style="width:44px; height:38px; padding:2px 3px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); cursor:pointer; flex:0 0 auto;">
                            <input type="text" name="btn_border_color" id="btn_border_color_text"
                                   value="<?= htmlspecialchars($btnBorderColor ?? 'transparent') ?>"
                                   style="flex:1; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                                   placeholder="transparent ou #hex" maxlength="20">
                        </div>
                        <small style="color:#777; font-size:11px;">Use <code>transparent</code> para sem borda.</small>
                    </div>
                    <div style="flex:0 0 140px;">
                        <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                            Espessura (px)
                        </label>
                        <input type="number" name="btn_border_width" id="btn_border_width"
                               value="<?= (int)($btnBorderWidth ?? 0) ?>"
                               min="0" max="10"
                               style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;">
                        <small style="color:#777; font-size:11px;">0 = sem borda, máx 10px.</small>
                    </div>
                </div>

                <!-- Cor dos ícones -->
                <div style="flex:1 1 100%; margin-top:4px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:6px;">
                        Cor dos ícones do menu
                    </label>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <input type="color" id="icon_color_picker"
                               value="<?= htmlspecialchars(!empty($iconColor) ? $iconColor : ($accentColor ?? '#e53935')) ?>"
                               style="width:44px; height:38px; padding:2px 3px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); cursor:pointer; flex:0 0 auto;">
                        <input type="text" name="icon_color" id="icon_color_text"
                               value="<?= htmlspecialchars($iconColor ?? '') ?>"
                               style="flex:1; min-width:140px; padding:9px 11px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-primary); font-size:13px;"
                               placeholder="Deixe vazio para herdar a cor primária" maxlength="9">
                        <?php if (!empty($iconColor)): ?>
                            <button type="button" id="icon_color_clear" style="border:none; border-radius:8px; padding:9px 12px; background:var(--surface-subtle); color:var(--text-secondary); font-size:12px; cursor:pointer;">Limpar (herdar)</button>
                        <?php endif; ?>
                    </div>
                    <small style="color:#777; font-size:11px;">Controla o fundo e a cor do ícone em cada item do menu. Vazio = usa a cor primária.</small>
                    <!-- Preview de ícone -->
                    <div style="margin-top:8px; display:flex; align-items:center; gap:8px;">
                        <div id="icon_preview_pill" style="
                            width:32px; height:32px; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:16px;
                            background:<?= !empty($iconColor) ? 'rgba(' . implode(',', array_map('hexdec', str_split(ltrim(!empty($iconColor) ? $iconColor : ($accentColor ?? '#e53935'), '#'), 2))) . ',0.15)' : _tuqRgba($accentColor ?? '#e53935', 0.15) ?>;
                            color:<?= htmlspecialchars(!empty($iconColor) ? $iconColor : ($accentColor ?? '#e53935')) ?>;
                        ">⭐</div>
                        <div id="icon_preview_pill2" style="
                            width:32px; height:32px; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:16px;
                            background:<?= !empty($iconColor) ? 'rgba(' . implode(',', array_map('hexdec', str_split(ltrim(!empty($iconColor) ? $iconColor : ($accentColor ?? '#e53935'), '#'), 2))) . ',0.15)' : _tuqRgba($accentColor ?? '#e53935', 0.15) ?>;
                            color:<?= htmlspecialchars(!empty($iconColor) ? $iconColor : ($accentColor ?? '#e53935')) ?>;
                        ">🎨</div>
                        <div id="icon_preview_pill3" style="
                            width:32px; height:32px; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:16px;
                            background:<?= !empty($iconColor) ? 'rgba(' . implode(',', array_map('hexdec', str_split(ltrim(!empty($iconColor) ? $iconColor : ($accentColor ?? '#e53935'), '#'), 2))) . ',0.15)' : _tuqRgba($accentColor ?? '#e53935', 0.15) ?>;
                            color:<?= htmlspecialchars(!empty($iconColor) ? $iconColor : ($accentColor ?? '#e53935')) ?>;
                        ">⚙</div>
                        <span style="font-size:11px; color:var(--text-secondary);">Pré-visualização dos ícones</span>
                    </div>
                </div>

            </div>

            <!-- Preview de cores -->
            <div style="margin-top:14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div style="font-size:12px; color:var(--text-secondary);">Pré-visualização:</div>
                <div id="color_preview_btn" style="padding:7px 16px; border-radius:999px; font-size:13px; font-weight:600; cursor:default; background:linear-gradient(135deg, <?= htmlspecialchars($accentColor ?? '#e53935') ?>, <?= htmlspecialchars($accentSoftColor ?? '#ff6f60') ?>); color:<?= htmlspecialchars($btnTextColor ?? '#050509') ?>; border-style:solid; border-color:<?= htmlspecialchars(($btnBorderColor ?? 'transparent') === 'transparent' ? 'transparent' : ($btnBorderColor ?? 'transparent')) ?>; border-width:<?= (int)($btnBorderWidth ?? 0) ?>px;">
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
    var accentPicker  = document.getElementById('accent_picker');
    var accentText    = document.getElementById('accent_text');
    var softPicker    = document.getElementById('soft_picker');
    var softText      = document.getElementById('soft_text');
    var btnTextPicker      = document.getElementById('btn_text_picker');
    var btnTextText        = document.getElementById('btn_text_text');
    var btnBorderColorPick = document.getElementById('btn_border_color_picker');
    var btnBorderColorText = document.getElementById('btn_border_color_text');
    var btnBorderWidthInp  = document.getElementById('btn_border_width');
    var iconColorPick  = document.getElementById('icon_color_picker');
    var iconColorText  = document.getElementById('icon_color_text');
    var iconClearBtn   = document.getElementById('icon_color_clear');
    var iconPills      = [document.getElementById('icon_preview_pill'), document.getElementById('icon_preview_pill2'), document.getElementById('icon_preview_pill3')];
    var previewBtn    = document.getElementById('color_preview_btn');
    var previewDot    = document.getElementById('color_preview_dot');
    var previewBadge  = document.getElementById('color_preview_badge');

    function isValidHex(v) {
        return /^#[0-9a-fA-F]{6}$/.test(v);
    }

    var styleGradientRadio  = document.getElementById('btn_style_gradient');
    var styleSolidRadio     = document.getElementById('btn_style_solid');
    var styleLabelGradient  = document.getElementById('btn_style_label_gradient');
    var styleLabelSolid     = document.getElementById('btn_style_label_solid');
    var solidSwatch         = document.getElementById('btn_style_solid_preview');

    function isGradient() {
        return !styleSolidRadio || !styleSolidRadio.checked;
    }

    function updatePreview() {
        var a  = accentText        ? accentText.value.trim()        : '#e53935';
        var s  = softText          ? softText.value.trim()          : '#ff6f60';
        var t  = btnTextText       ? btnTextText.value.trim()       : '#050509';
        var bc = btnBorderColorText ? btnBorderColorText.value.trim() : 'transparent';
        var bw = btnBorderWidthInp  ? parseInt(btnBorderWidthInp.value, 10) || 0 : 0;
        if (!isValidHex(a)) a = '#e53935';
        if (!isValidHex(s)) s = '#ff6f60';
        if (!isValidHex(t)) t = '#050509';
        if (bc !== 'transparent' && !isValidHex(bc)) bc = 'transparent';

        var bg = isGradient() ? 'linear-gradient(135deg,' + a + ',' + s + ')' : a;

        if (previewBtn) {
            previewBtn.style.background   = bg;
            previewBtn.style.color        = t;
            previewBtn.style.borderStyle  = 'solid';
            previewBtn.style.borderColor  = bc;
            previewBtn.style.borderWidth  = bw + 'px';
        }
        if (previewDot)   previewDot.style.background   = a;
        if (previewBadge) { previewBadge.style.borderColor = a; previewBadge.style.color = s; }
        if (solidSwatch)  solidSwatch.style.background  = a;

        // Icon preview
        var ic = iconColorText && iconColorText.value.trim() !== '' ? iconColorText.value.trim() : a;
        if (!isValidHex(ic)) ic = a;
        function hexToRgb(h) { h = h.replace('#',''); return [parseInt(h.slice(0,2),16), parseInt(h.slice(2,4),16), parseInt(h.slice(4,6),16)]; }
        var rgb = hexToRgb(ic);
        var iconBg = 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',0.15)';
        iconPills.forEach(function(p) {
            if (!p) return;
            p.style.background = iconBg;
            p.style.color = ic;
        });

        var accent = 'var(--accent)';
        var border = 'var(--border-subtle)';
        var card   = 'var(--surface-card)';
        if (styleLabelGradient) {
            styleLabelGradient.style.borderColor = isGradient() ? accent : border;
            styleLabelGradient.style.background  = isGradient() ? card   : 'transparent';
        }
        if (styleLabelSolid) {
            styleLabelSolid.style.borderColor = !isGradient() ? accent : border;
            styleLabelSolid.style.background  = !isGradient() ? card   : 'transparent';
        }
    }

    function bindPair(picker, text) {
        if (!picker || !text) return;
        picker.addEventListener('input', function () { text.value = this.value; updatePreview(); });
        text.addEventListener('input', function () {
            if (isValidHex(this.value.trim())) picker.value = this.value.trim();
            updatePreview();
        });
    }

    bindPair(accentPicker, accentText);
    bindPair(softPicker, softText);
    bindPair(btnTextPicker, btnTextText);
    bindPair(btnBorderColorPick, btnBorderColorText);

    // Icon color — picker drives text, text can be empty (inherit)
    if (iconColorPick) {
        iconColorPick.addEventListener('input', function () {
            if (iconColorText) iconColorText.value = this.value;
            updatePreview();
        });
    }
    if (iconColorText) {
        iconColorText.addEventListener('input', function () {
            var v = this.value.trim();
            if (isValidHex(v) && iconColorPick) iconColorPick.value = v;
            updatePreview();
        });
    }
    if (iconClearBtn) {
        iconClearBtn.addEventListener('click', function () {
            if (iconColorText) iconColorText.value = '';
            updatePreview();
        });
    }

    if (styleGradientRadio) styleGradientRadio.addEventListener('change', updatePreview);
    if (styleSolidRadio)    styleSolidRadio.addEventListener('change', updatePreview);
    if (btnBorderWidthInp)  btnBorderWidthInp.addEventListener('input', updatePreview);
})();
</script>
