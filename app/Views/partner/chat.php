<?php
/** @var array $user */
/** @var array $personalities */
/** @var array $selectedPersonality */
/** @var bool $hasApiKey */

function render_markdown_safe(string $text): string {
    $text = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $text);
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $escaped = preg_replace('/^#\s+(.+)$/m', '<h2>$1</h2>', $escaped);
    $escaped = preg_replace('/^##\s+(.+)$/m', '<h3>$1</h3>', $escaped);
    $escaped = preg_replace('/^#{3,6}\s+(.+)$/m', '<h4>$1</h4>', $escaped);
    $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
    $escaped = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $escaped);
    $escaped = preg_replace('/^\-\s+/m', '• ', $escaped);
    $escaped = preg_replace('/^\s*---+\s*$/m', '[[HR]]', $escaped);
    $escaped = preg_replace('/^\s*\[\[HR\]\]\s*$/m', '[[HR]]', $escaped);
    $escaped = preg_replace('/\n\s*\[\[HR\]\]\s*\n/u', "\n\n[[HR]]\n\n", "\n" . $escaped . "\n");
    $escaped = trim($escaped);
    if (strpos($escaped, "\n") !== false && !preg_match('/\n\s*\n/', $escaped)) {
        $escaped = preg_replace('/\n(?=[#*])/', "\n\n", $escaped);
    }
    $escaped = preg_replace('/\n{3,}/', "\n\n", $escaped);
    $escaped = str_replace('[[HR]]', '<hr style="margin: 1.5em 0; border: none; border-top: 1px solid #333;">', $escaped);
    $escaped = nl2br($escaped);
    $escaped = preg_replace('/<br>\s*<br>/', '</p><p>', $escaped);
    if (!preg_match('/^<p>/', $escaped)) {
        $escaped = '<p>' . $escaped;
    }
    if (!preg_match('/<\/p>$/', $escaped)) {
        $escaped = $escaped . '</p>';
    }
    return $escaped;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat IA - <?= htmlspecialchars($user['name'] ?? 'Parceiro') ?></title>
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
            height: 100vh;
            overflow: hidden;
        }
        
        .chat-container {
            display: flex;
            height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: #111;
            border-right: 1px solid #333;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #333;
        }
        
        .sidebar-header h2 {
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .personality-selector {
            margin-bottom: 15px;
        }
        
        .personality-selector select {
            width: 100%;
            padding: 8px 12px;
            background: #222;
            border: 1px solid #444;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
        }
        
        .new-chat-btn {
            width: 100%;
            padding: 10px;
            background: #2563eb;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .new-chat-btn:hover {
            background: #1d4ed8;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #333;
            background: #111;
        }
        
        .chat-header h1 {
            font-size: 20px;
            font-weight: 600;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .message {
            max-width: 80%;
            padding: 15px;
            border-radius: 12px;
            line-height: 1.5;
        }
        
        .message.user {
            align-self: flex-end;
            background: #2563eb;
            color: white;
        }
        
        .message.assistant {
            align-self: flex-start;
            background: #1a1a1a;
            border: 1px solid #333;
        }
        
        .message-content {
            white-space: pre-wrap;
        }
        
        .chat-input-container {
            padding: 20px;
            border-top: 1px solid #333;
            background: #111;
        }
        
        .chat-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .chat-input {
            flex: 1;
            padding: 12px 16px;
            background: #222;
            border: 1px solid #444;
            border-radius: 8px;
            color: #fff;
            font-size: 15px;
            resize: none;
            min-height: 44px;
            max-height: 120px;
        }
        
        .chat-input:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .send-btn {
            padding: 12px 20px;
            background: #2563eb;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .send-btn:hover:not(:disabled) {
            background: #1d4ed8;
        }
        
        .send-btn:disabled {
            background: #444;
            cursor: not-allowed;
        }
        
        .error-message {
            background: #dc2626;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px;
        }
        
        .typing-indicator {
            display: none;
            align-self: flex-start;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 15px;
        }
        
        .typing-indicator .dots {
            display: flex;
            gap: 4px;
        }
        
        .typing-indicator .dot {
            width: 8px;
            height: 8px;
            background: #666;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing-indicator .dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator .dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                opacity: 0.3;
            }
            30% {
                opacity: 1;
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Chat IA</h2>
                <div class="personality-selector">
                    <select id="personalitySelect">
                        <?php foreach ($personalities as $personality): ?>
                            <option value="<?= $personality['id'] ?>" <?= (int)$selectedPersonality['id'] === (int)$personality['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($personality['name']) ?> (<?= htmlspecialchars($personality['area']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="new-chat-btn" onclick="clearChat()">Nova Conversa</button>
            </div>
        </div>
        
        <div class="main-content">
            <div class="chat-header">
                <h1>
                    <?= htmlspecialchars($selectedPersonality['name'] ?? 'Assistente') ?>
                    <small style="color: #666; font-size: 14px; margin-left: 10px;">
                        <?= htmlspecialchars($selectedPersonality['area'] ?? 'IA') ?>
                    </small>
                </h1>
            </div>
            
            <?php if (!$hasApiKey): ?>
                <div class="error-message">
                    Configure sua API Key em <a href="/parceiro/configuracoes/api" style="color: #fff; text-decoration: underline;">Configurações</a> para usar o chat.
                </div>
            <?php endif; ?>
            
            <div class="chat-messages" id="chatMessages">
                <div class="empty-state">
                    <h3>Olá! Sou <?= htmlspecialchars($selectedPersonality['name'] ?? 'Assistente') ?></h3>
                    <p>Estou aqui para ajudar você. Como posso auxiliar hoje?</p>
                </div>
            </div>
            
            <div class="typing-indicator" id="typingIndicator">
                <div class="dots">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            </div>
            
            <div class="chat-input-container">
                <div class="chat-input-wrapper">
                    <textarea 
                        id="chatInput" 
                        class="chat-input" 
                        placeholder="Digite sua mensagem..." 
                        rows="1"
                        <?= !$hasApiKey ? 'disabled' : '' ?>
                    ></textarea>
                    <button id="sendBtn" class="send-btn" <?= !$hasApiKey ? 'disabled' : '' ?>>
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const personalitySelect = document.getElementById('personalitySelect');
        const typingIndicator = document.getElementById('typingIndicator');
        
        let currentPersonalityId = <?= (int)($selectedPersonality['id'] ?? 0) ?>;
        
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Enviar mensagem
        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message || sendBtn.disabled) return;
            
            const personalityId = parseInt(personalitySelect.value);
            
            // Adicionar mensagem do usuário
            addMessage(message, 'user');
            
            // Limpar input
            chatInput.value = '';
            chatInput.style.height = 'auto';
            
            // Desabilitar botão
            sendBtn.disabled = true;
            chatInput.disabled = true;
            
            // Mostrar indicador de digitação
            typingIndicator.style.display = 'block';
            
            // Enviar para API
            fetch('/parceiro/chat/enviar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}&personality_id=${personalityId}`
            })
            .then(response => response.json())
            .then(data => {
                typingIndicator.style.display = 'none';
                
                if (data.success) {
                    addMessage(data.response, 'assistant', data.personality);
                } else {
                    addMessage('Erro: ' + (data.error || 'Ocorreu um erro'), 'assistant');
                }
            })
            .catch(error => {
                typingIndicator.style.display = 'none';
                addMessage('Erro de conexão. Tente novamente.', 'assistant');
            })
            .finally(() => {
                sendBtn.disabled = false;
                chatInput.disabled = false;
                chatInput.focus();
            });
        }
        
        function addMessage(content, type, personalityName = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = render_markdown_safe(content);
            
            messageDiv.appendChild(contentDiv);
            
            // Remover empty state se existir
            const emptyState = chatMessages.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function clearChat() {
            chatMessages.innerHTML = `
                <div class="empty-state">
                    <h3>Olá! Sou ${personalitySelect.options[personalitySelect.selectedIndex].text.split('(')[0].trim()}</h3>
                    <p>Estou aqui para ajudar você. Como posso auxiliar hoje?</p>
                </div>
            `;
        }
        
        // Event listeners
        sendBtn.addEventListener('click', sendMessage);
        
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        personalitySelect.addEventListener('change', function() {
            currentPersonalityId = parseInt(this.value);
            clearChat();
        });
        
        // Foco no input
        chatInput.focus();
    </script>
</body>
</html>
