<?php
/** @var array $user */
/** @var array|null $personality */
/** @var bool $isEdit */
/** @var string|null $success */
/** @var string|null $error */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Nova' ?> Personalidade - <?= htmlspecialchars($user['name'] ?? 'Parceiro') ?></title>
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
        .form-group textarea,
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
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
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
        
        .help-text {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
        }
        
        .character-count {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-top: 4px;
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
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        
        .preview-section {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
        }
        
        .preview-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #ccc;
        }
        
        .preview-content {
            font-size: 14px;
            color: #999;
            line-height: 1.5;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $isEdit ? 'Editar' : 'Nova' ?> Personalidade</h1>
            <p><?= $isEdit ? 'Modifique os dados da personalidade existente.' : 'Crie uma nova personalidade para seu chat.' ?></p>
        </div>
        
        <div class="nav-menu">
            <a href="/parceiro/configuracoes">Visão Geral</a>
            <a href="/parceiro/configuracoes/api">API Keys</a>
            <a href="/parceiro/configuracoes/personalidades" class="active">Personalidades</a>
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
        
        <div class="form-card">
            <form method="POST" action="<?= $isEdit ? '/parceiro/personalidades/atualizar' : '/parceiro/personalidades/salvar' ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $personality['id'] ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nome *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?= htmlspecialchars($personality['name'] ?? '') ?>" 
                            required 
                            placeholder="Ex: Dr. Silva, Especialista em Marketing"
                        >
                        <div class="help-text">Nome como aparecerá no chat para os usuários.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="area">Área de Especialidade *</label>
                        <input 
                            type="text" 
                            id="area" 
                            name="area" 
                            value="<?= htmlspecialchars($personality['area'] ?? '') ?>" 
                            required 
                            placeholder="Ex: Marketing, Vendas, Suporte"
                        >
                        <div class="help-text">Área de conhecimento da personalidade.</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug *</label>
                    <input 
                        type="text" 
                        id="slug" 
                        name="slug" 
                        value="<?= htmlspecialchars($personality['slug'] ?? '') ?>" 
                        required 
                        placeholder="ex: dr-silva-marketing"
                        pattern="[a-z0-9-]+"
                        title="Use apenas letras minúsculas, números e hífens"
                    >
                    <div class="help-text">Identificador único (URL amigável). Use apenas letras minúsculas, números e hífens.</div>
                </div>
                
                <div class="form-group">
                    <label for="prompt">Prompt de Sistema *</label>
                    <textarea 
                        id="prompt" 
                        name="prompt" 
                        required 
                        placeholder="Descreva como esta personalidade deve se comportar, seu tom de voz, conhecimentos e como deve responder aos usuários..."
                        maxlength="4000"
                        oninput="updateCharCount()"
                    ><?= htmlspecialchars($personality['prompt'] ?? '') ?></textarea>
                    <div class="character-count">
                        <span id="charCount">0</span> / 4000 caracteres
                    </div>
                    <div class="help-text">
                        Este é o prompt principal que define o comportamento da personalidade. Seja específico sobre tom, estilo, conhecimento e limitações.
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_default" name="is_default" <?= !empty($personality['is_default']) ? 'checked' : '' ?>>
                            <label for="is_default">Definir como personalidade padrão</label>
                        </div>
                        <div class="help-text">Será selecionada automaticamente quando os usuários acessarem o chat.</div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="active" name="active" <?= !isset($personality['active']) || !empty($personality['active']) ? 'checked' : '' ?>>
                            <label for="active">Personalidade ativa</label>
                        </div>
                        <div class="help-text">Apenas personalidades ativas aparecem no chat.</div>
                    </div>
                </div>
                
                <div class="preview-section">
                    <div class="preview-title">Prévia da Personalidade</div>
                    <div class="preview-content" id="previewContent">
                        Preencha os campos acima para ver uma prévia de como esta personalidade aparecerá no chat.
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <span>💾</span> <?= $isEdit ? 'Atualizar' : 'Criar' ?> Personalidade
                    </button>
                    <a href="/parceiro/configuracoes/personalidades" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <?php if ($isEdit): ?>
                        <a href="/parceiro/chat" class="btn btn-secondary">
                            <span>💬</span> Testar no Chat
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Contador de caracteres
        function updateCharCount() {
            const textarea = document.getElementById('prompt');
            const charCount = document.getElementById('charCount');
            charCount.textContent = textarea.value.length;
        }
        
        // Gerar slug automático a partir do nome
        document.getElementById('name').addEventListener('input', function() {
            const slugField = document.getElementById('slug');
            if (!slugField.value || slugField.dataset.autoGenerated) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                slugField.value = slug;
                slugField.dataset.autoGenerated = 'true';
            }
        });
        
        // Remover marcação de auto-gerado quando usuário editar o slug manualmente
        document.getElementById('slug').addEventListener('input', function() {
            delete this.dataset.autoGenerated;
        });
        
        // Atualizar prévia
        function updatePreview() {
            const name = document.getElementById('name').value || 'Nome da Personalidade';
            const area = document.getElementById('area').value || 'Área';
            const prompt = document.getElementById('prompt').value;
            const isActive = document.getElementById('active').checked;
            const isDefault = document.getElementById('is_default').checked;
            
            const preview = document.getElementById('previewContent');
            
            let previewHTML = `
                <strong>Nome:</strong> ${name}<br>
                <strong>Área:</strong> ${area}<br>
                <strong>Status:</strong> ${isActive ? 'Ativa' : 'Inativa'}<br>
                <strong>Padrão:</strong> ${isDefault ? 'Sim' : 'Não'}
            `;
            
            if (prompt) {
                const shortPrompt = prompt.length > 200 ? prompt.substring(0, 200) + '...' : prompt;
                previewHTML += `<br><br><strong>Prompt:</strong><br>${shortPrompt.replace(/\n/g, '<br>')}`;
            }
            
            preview.innerHTML = previewHTML;
        }
        
        // Adicionar listeners para atualizar prévia
        document.getElementById('name').addEventListener('input', updatePreview);
        document.getElementById('area').addEventListener('input', updatePreview);
        document.getElementById('prompt').addEventListener('input', updatePreview);
        document.getElementById('active').addEventListener('change', updatePreview);
        document.getElementById('is_default').addEventListener('change', updatePreview);
        
        // Inicializar
        updateCharCount();
        updatePreview();
    </script>
</body>
</html>
