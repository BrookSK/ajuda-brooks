<?php
/** @var array $user */
/** @var array $apiKeys */
/** @var string|null $success */
/** @var string|null $error */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar API Keys - <?= htmlspecialchars($user['name'] ?? 'Parceiro') ?></title>
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
            max-width: 800px;
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
        
        .form-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        
        .form-card h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #ccc;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
        
        .btn-secondary {
            background: #374151;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        
        .btn-danger:hover {
            background: #b91c1c;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .api-keys-list {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 24px;
        }
        
        .api-keys-list h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        .api-key-item {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 16px;
        }
        
        .api-key-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .api-key-title {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .api-key-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
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
        
        .api-key-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 14px;
            color: #ccc;
        }
        
        .api-key-actions {
            display: flex;
            gap: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .api-key-details {
                grid-template-columns: 1fr;
            }
            
            .api-key-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>API Keys</h1>
            <p>Configure suas chaves de API para acessar diferentes provedores de IA.</p>
        </div>
        
        <div class="nav-menu">
            <a href="/painel-externo/config" class="active">Visão Geral</a>
            <a href="/painel-externo/config/api">API Keys</a>
            <a href="/painel-externo/config/personalidades">Personalidades</a>
            <a href="/painel-externo/chat">Chat</a>
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
        
        <div class="form-card">
            <h2>Adicionar Nova API Key</h2>
            <form method="POST" action="/painel-externo/config/api/salvar">
                <div class="form-row">
                    <div class="form-group">
                        <label for="provider">Provedor</label>
                        <select id="provider" name="provider" required>
                            <option value="">Selecione...</option>
                            <option value="openai">OpenAI</option>
                            <option value="anthropic">Anthropic (Claude)</option>
                            <option value="perplexity">Perplexity</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="model">Modelo (opcional)</label>
                        <input type="text" id="model" name="model" placeholder="Ex: gpt-4, claude-3-sonnet">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="api_key">API Key</label>
                    <input type="password" id="api_key" name="api_key" required placeholder="Digite sua API key">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" checked>
                        <label for="is_active">Ativar esta API Key</label>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary">
                        <span>💾</span> Salvar API Key
                    </button>
                    <a href="/painel-externo/config" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
        
        <div class="api-keys-list">
            <h2>Suas API Keys</h2>
            
            <?php if (empty($apiKeys)): ?>
                <div class="empty-state">
                    <h3>Nenhuma API Key configurada</h3>
                    <p>Configure sua primeira API key acima para começar a usar o chat.</p>
                    <a href="/painel-externo/config/api" class="btn btn-primary" disabled>
                        <span>💬</span> Chat Indisponível
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($apiKeys as $apiKey): ?>
                    <div class="api-key-item">
                        <div class="api-key-header">
                            <div class="api-key-title"><?= htmlspecialchars(ucfirst($apiKey['provider'])) ?></div>
                            <div class="api-key-status">
                                <div class="status-dot <?= !empty($apiKey['is_active']) ? 'active' : 'inactive' ?>"></div>
                                <span><?= !empty($apiKey['is_active']) ? 'Ativa' : 'Inativa' ?></span>
                            </div>
                        </div>
                        
                        <div class="api-key-details">
                            <div>
                                <strong>Modelo:</strong> 
                                <?= htmlspecialchars($apiKey['model'] ?? 'Padrão') ?>
                            </div>
                            <div>
                                <strong>Criada em:</strong> 
                                <?= date('d/m/Y H:i', strtotime($apiKey['created_at'])) ?>
                            </div>
                        </div>
                        
                        <div class="api-key-actions">
                            <a href="/painel-externo/config/api/toggle?id=<?= $apiKey['id'] ?>" class="btn btn-sm <?= !empty($apiKey['is_active']) ? 'btn-secondary' : 'btn-primary' ?>">
                                <?= !empty($apiKey['is_active']) ? 'Desativar' : 'Ativar' ?>
                            </a>
                            <a href="/painel-externo/config/api/delete?id=<?= $apiKey['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta API key?')">
                                Excluir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #333;">
                    <a href="/painel-externo/chat" class="btn btn-primary">
                        <span>💬</span> Testar Chat
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
