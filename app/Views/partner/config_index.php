<?php
/** @var array $user */
/** @var array $apiKeys */
/** @var bool $hasApiKey */
/** @var string|null $success */
/** @var string|null $error */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - <?= htmlspecialchars($user['name'] ?? 'Parceiro') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #999;
            font-size: 16px;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .card {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 24px;
            transition: border-color 0.2s;
        }
        
        .card:hover {
            border-color: #444;
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .card-icon {
            width: 48px;
            height: 48px;
            background: #2563eb;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 20px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
        }
        
        .card-description {
            color: #999;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .card-status {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .status-dot.active {
            background: #10b981;
        }
        
        .status-dot.inactive {
            background: #ef4444;
        }
        
        .card-action {
            display: inline-block;
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .card-action:hover {
            background: #1d4ed8;
        }
        
        .card-action.secondary {
            background: #374151;
        }
        
        .card-action.secondary:hover {
            background: #4b5563;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert.success {
            background: #065f46;
            border: 1px solid #047857;
            color: #10b981;
        }
        
        .alert.error {
            background: #7f1d1d;
            border: 1px solid #dc2626;
            color: #ef4444;
        }
        
        .quick-actions {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 24px;
        }
        
        .quick-actions h2 {
            font-size: 20px;
            margin-bottom: 16px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .action-btn {
            padding: 12px 16px;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn:hover {
            background: #374151;
            border-color: #4b5563;
        }
        
        .nav-menu {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 30px;
        }
        
        .nav-menu a {
            color: #999;
            text-decoration: none;
            margin-right: 20px;
            font-size: 14px;
            transition: color 0.2s;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Configurações</h1>
            <p>Gerencie suas configurações de API, personalidades e preferências do sistema.</p>
        </div>
        
        <div class="nav-menu">
            <a href="/parceiro/configuracoes" class="active">Visão Geral</a>
            <a href="/parceiro/configuracoes/api">API Keys</a>
            <a href="/parceiro/configuracoes/personalidades">Personalidades</a>
            <a href="/parceiro/chat">Chat</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="cards-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🔑</div>
                    <div>
                        <div class="card-title">API Keys</div>
                    </div>
                </div>
                <div class="card-description">
                    Configure suas chaves de API para acessar os modelos de IA como OpenAI, Anthropic e outros.
                </div>
                <div class="card-status">
                    <div class="status-dot <?= $hasApiKey ? 'active' : 'inactive' ?>"></div>
                    <span><?= $hasApiKey ? 'Configurado' : 'Não configurado' ?></span>
                </div>
                <a href="/parceiro/configuracoes/api" class="card-action">
                    <?= $hasApiKey ? 'Gerenciar' : 'Configurar' ?>
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🎭</div>
                    <div>
                        <div class="card-title">Personalidades</div>
                    </div>
                </div>
                <div class="card-description">
                    Crie e gerencie personalidades exclusivas para seu chat, com diferentes especialidades e estilos.
                </div>
                <div class="card-status">
                    <div class="status-dot inactive"></div>
                    <span><?= count($apiKeys) ?> personalidades</span>
                </div>
                <a href="/parceiro/configuracoes/personalidades" class="card-action">
                    Gerenciar
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">💬</div>
                    <div>
                        <div class="card-title">Chat IA</div>
                    </div>
                </div>
                <div class="card-description">
                    Acesse a interface de chat com suas personalidades configuradas e API keys personalizadas.
                </div>
                <div class="card-status">
                    <div class="status-dot <?= $hasApiKey ? 'active' : 'inactive' ?>"></div>
                    <span><?= $hasApiKey ? 'Disponível' : 'API necessária' ?></span>
                </div>
                <a href="/parceiro/chat" class="card-action <?= !$hasApiKey ? 'secondary' : '' ?>">
                    <?= $hasApiKey ? 'Abrir Chat' : 'Configurar API' ?>
                </a>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>Ações Rápidas</h2>
            <div class="actions-grid">
                <a href="/parceiro/configuracoes/api" class="action-btn">
                    <span>➕</span> Nova API Key
                </a>
                <a href="/parceiro/personalidades/novo" class="action-btn">
                    <span>🎭</span> Nova Personalidade
                </a>
                <a href="/parceiro/chat" class="action-btn">
                    <span>💬</span> Testar Chat
                </a>
                <a href="/parceiro/configuracoes/personalidades" class="action-btn">
                    <span>📋</span> Ver Todas
                </a>
            </div>
        </div>
    </div>
</body>
</html>
