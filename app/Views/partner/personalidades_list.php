<?php
/** @var array $user */
/** @var array $personalities */
/** @var string|null $success */
/** @var string|null $error */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalidades - <?= htmlspecialchars($user['name'] ?? 'Parceiro') ?></title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 700;
        }
        
        .header p {
            color: #999;
            font-size: 16px;
            margin-top: 8px;
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
        
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 10px 16px;
            width: 300px;
        }
        
        .search-box input {
            background: none;
            border: none;
            color: #fff;
            font-size: 14px;
            width: 100%;
            outline: none;
        }
        
        .search-box input::placeholder {
            color: #666;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
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
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        
        .btn-danger:hover {
            background: #b91c1c;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .personalities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .personality-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 20px;
            transition: border-color 0.2s;
        }
        
        .personality-card:hover {
            border-color: #444;
        }
        
        .personality-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .personality-info {
            flex: 1;
        }
        
        .personality-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .personality-area {
            color: #999;
            font-size: 14px;
        }
        
        .personality-status {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-badge.active {
            background: #065f46;
            color: #10b981;
        }
        
        .status-badge.inactive {
            background: #7f1d1d;
            color: #ef4444;
        }
        
        .status-badge.default {
            background: #1e3a8a;
            color: #3b82f6;
        }
        
        .personality-description {
            color: #ccc;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .personality-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            font-size: 12px;
            color: #666;
        }
        
        .personality-slug {
            font-family: monospace;
            background: #1a1a1a;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .personality-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 12px;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            
            .actions-bar {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
            }
            
            .personalities-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Personalidades</h1>
                <p>Crie e gerencie personalidades exclusivas para seu chat.</p>
            </div>
        </div>
        
        <div class="nav-menu">
            <a href="/painel-externo/config">Visão Geral</a>
            <a href="/painel-externo/config/api">API Keys</a>
            <a href="/painel-externo/config/personalidades" class="active">Personalidades</a>
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
        
        <div class="actions-bar">
            <div class="search-box">
                <span>🔍</span>
                <input type="text" placeholder="Buscar personalidades..." id="searchInput">
            </div>
            <a href="/painel-externo/personalidades/novo" class="btn btn-primary">
                <span>➕</span> Nova Personalidade
            </a>
        </div>
        
        <?php if (empty($personalities)): ?>
            <div class="empty-state">
                <h3>Nenhuma personalidade criada</h3>
                <p>Crie sua primeira personalidade para começar a personalizar o chat com diferentes estilos e especialidades.</p>
                <a href="/painel-externo/personalidades/novo" class="btn btn-primary">
                    <span>➕</span> Criar Primeira Personalidade
                </a>
            </div>
        <?php else: ?>
            <div class="personalities-grid" id="personalitiesGrid">
                <?php foreach ($personalities as $personality): ?>
                    <div class="personality-card" data-name="<?= htmlspecialchars(strtolower($personality['name'])) ?>" data-area="<?= htmlspecialchars(strtolower($personality['area'])) ?>">
                        <div class="personality-header">
                            <div class="personality-info">
                                <div class="personality-name"><?= htmlspecialchars($personality['name']) ?></div>
                                <div class="personality-area"><?= htmlspecialchars($personality['area']) ?></div>
                            </div>
                            <div class="personality-status">
                                <?php if (!empty($personality['is_default'])): ?>
                                    <span class="status-badge default">Padrão</span>
                                <?php endif; ?>
                                <span class="status-badge <?= !empty($personality['active']) ? 'active' : 'inactive' ?>">
                                    <?= !empty($personality['active']) ? 'Ativa' : 'Inativa' ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="personality-description">
                            <?= htmlspecialchars(substr($personality['prompt'], 0, 150)) ?>...
                        </div>
                        
                        <div class="personality-meta">
                            <div class="personality-slug"><?= htmlspecialchars($personality['slug']) ?></div>
                            <div>Criada em <?= date('d/m/Y', strtotime($personality['created_at'])) ?></div>
                        </div>
                        
                        <div class="personality-actions">
                            <a href="/painel-externo/personalidades/editar?id=<?= $personality['id'] ?>" class="btn btn-sm btn-secondary">
                                Editar
                            </a>
                            
                            <?php if (empty($personality['is_default'])): ?>
                                <a href="/painel-externo/personalidades/<?= $personality['id'] ?>/default" class="btn btn-sm btn-success">
                                    Definir Padrão
                                </a>
                            <?php endif; ?>
                            
                            <a href="/painel-externo/personalidades/<?= $personality['id'] ?>/toggle" class="btn btn-sm <?= !empty($personality['active']) ? 'btn-secondary' : 'btn-success' ?>">
                                <?= !empty($personality['active']) ? 'Desativar' : 'Ativar' ?>
                            </a>
                            
                            <a href="/painel-externo/personalidades/<?= $personality['id'] ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta personalidade?')">
                                Excluir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Funcionalidade de busca
        const searchInput = document.getElementById('searchInput');
        const personalitiesGrid = document.getElementById('personalitiesGrid');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = personalitiesGrid.querySelectorAll('.personality-card');
            
            cards.forEach(card => {
                const name = card.dataset.name;
                const area = card.dataset.area;
                
                if (name.includes(searchTerm) || area.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
