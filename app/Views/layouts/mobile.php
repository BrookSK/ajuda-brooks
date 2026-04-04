<?php
/** @var string $viewFile */
/** @var string|null $pageTitle */

$_brandAccentColor = '#e53935';
$_brandAccentSoft  = '#ff6f60';
$_brandAiName      = 'Tuquinha';
try {
    if (class_exists('App\\Models\\Setting')) {
        $_brandAccentColor = (string)(\App\Models\Setting::get('brand_accent_color', '#e53935') ?: '#e53935');
        $_brandAccentSoft  = (string)(\App\Models\Setting::get('brand_accent_soft', '#ff6f60') ?: '#ff6f60');
        $_brandAiName      = (string)(\App\Models\Setting::get('system_ai_name', 'Tuquinha') ?: 'Tuquinha');
    }
} catch (\Throwable $e) {}

$pageTitle = $pageTitle ?? $_brandAiName;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="<?= htmlspecialchars($_brandAccentColor) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="manifest" href="/public/manifest.webmanifest">
    <link rel="icon" type="image/png" href="/public/favicon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        :root {
            --bg: #050509;
            --bg-card: #111118;
            --accent: <?= htmlspecialchars($_brandAccentColor) ?>;
            --accent-soft: <?= htmlspecialchars($_brandAccentSoft) ?>;
            --text: #f5f5f5;
            --text-dim: #8a8a9a;
            --border: #1e1e2a;
            --radius: 16px;
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }
        html, body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            min-height: 100dvh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
</style>
    <style>
        /* Inputs */
        input, textarea, select {
            background: var(--bg-card);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 16px;
            width: 100%;
            outline: none;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        input:focus, textarea:focus {
            border-color: var(--accent);
        }
        input::placeholder, textarea::placeholder {
            color: var(--text-dim);
        }

        /* Buttons */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--accent), var(--accent-soft));
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            width: 100%;
            text-decoration: none;
        }
        .btn-primary:active { transform: scale(0.97); opacity: 0.9; }
        .btn-primary:disabled { opacity: 0.5; pointer-events: none; }

        .btn-ghost {
            background: transparent;
            color: var(--text-dim);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 12px 24px;
            font-size: 15px;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .btn-ghost:hover { border-color: var(--accent); color: var(--text); }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 0.2; }
            100% { transform: scale(0.8); opacity: 0.6; }
        }
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(229,57,53,0.3); }
            50% { box-shadow: 0 0 40px rgba(229,57,53,0.6); }
        }
        @keyframes wave-bar {
            0%, 100% { height: 4px; }
            50% { height: 20px; }
        }
        .fade-in { animation: fadeInUp 0.5s ease forwards; }
        .fade-in-delay-1 { animation-delay: 0.1s; opacity: 0; }
        .fade-in-delay-2 { animation-delay: 0.2s; opacity: 0; }
        .fade-in-delay-3 { animation-delay: 0.3s; opacity: 0; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 0; height: 0; }

        /* Safe areas */
        .safe-top { padding-top: var(--safe-top); }
        .safe-bottom { padding-bottom: var(--safe-bottom); }
    </style>
</head>
<body>
    <?php include $viewFile; ?>
    <script>
        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/public/service-worker.js').catch(() => {});
        }
    </script>
</body>
</html>
