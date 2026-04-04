<?php
/** @var array $user */
/** @var array|null $onboarding */
/** @var string $toolName */
/** @var int $conversationId */
/** @var array $messages */
/** @var bool $voiceEnabled */
$userName = htmlspecialchars($onboarding['preferred_name'] ?? $user['name'] ?? '');
$safeToolName = htmlspecialchars($toolName);
?>

<!-- Header -->
<div style="position:fixed; top:0; left:0; right:0; z-index:20; background:rgba(5,5,9,0.92); backdrop-filter:blur(20px); border-bottom:1px solid var(--border); padding-top:var(--safe-top);">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px;">
        <a href="/m/historico" style="color:var(--text-dim); text-decoration:none; display:flex; align-items:center; gap:6px; font-size:14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div style="text-align:center;">
            <div style="font-weight:700; font-size:16px;"><?= $safeToolName ?></div>
            <div id="status-text" style="font-size:11px; color:var(--text-dim);">Online</div>
        </div>
        <a href="/m/chat?new=1" style="color:var(--accent); text-decoration:none;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
        </a>
    </div>
</div>

<!-- Messages area -->
<div id="messages" style="flex:1; overflow-y:auto; padding:80px 16px 160px; min-height:100dvh;">
    <?php if (empty($messages)): ?>
        <div id="empty-state" style="display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:60vh; text-align:center;">
            <!-- Orb animado -->
            <div style="width:100px; height:100px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 3s ease-in-out infinite; margin-bottom:24px;">
                <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                    <div id="idle-orb-inner" style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); opacity:0.7; transition:all 0.3s;"></div>
                </div>
            </div>
            <h2 style="font-size:20px; font-weight:700; margin-bottom:8px;">Olá, <?= $userName ?>!</h2>
            <p style="color:var(--text-dim); font-size:15px;">Como posso te ajudar hoje?</p>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="msg msg-<?= $msg['role'] === 'user' ? 'user' : 'ai' ?>" style="margin-bottom:16px; display:flex; <?= $msg['role'] === 'user' ? 'justify-content:flex-end' : 'justify-content:flex-start' ?>;">
                <div style="max-width:85%; padding:12px 16px; border-radius:18px; <?= $msg['role'] === 'user'
                    ? 'background:linear-gradient(135deg, var(--accent), var(--accent-soft)); color:#fff; border-bottom-right-radius:4px;'
                    : 'background:var(--bg-card); border:1px solid var(--border); border-bottom-left-radius:4px;' ?> font-size:15px; line-height:1.6; word-break:break-word;">
                    <?= nl2br(htmlspecialchars($msg['content'])) ?>
                    <?php if ($msg['role'] === 'assistant' && $voiceEnabled): ?>
                        <button onclick="playTTS(this)" data-text="<?= htmlspecialchars($msg['content']) ?>" style="background:none; border:none; color:var(--text-dim); cursor:pointer; padding:4px; margin-top:4px; display:block;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Typing indicator -->
    <div id="typing" style="display:none; margin-bottom:16px;">
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:18px; border-bottom-left-radius:4px; padding:14px 20px; display:inline-flex; gap:4px; align-items:center;">
            <div class="typing-dot" style="width:8px; height:8px; border-radius:50%; background:var(--accent); animation:pulse-ring 1.4s ease-in-out infinite;"></div>
            <div class="typing-dot" style="width:8px; height:8px; border-radius:50%; background:var(--accent); animation:pulse-ring 1.4s ease-in-out 0.2s infinite;"></div>
            <div class="typing-dot" style="width:8px; height:8px; border-radius:50%; background:var(--accent); animation:pulse-ring 1.4s ease-in-out 0.4s infinite;"></div>
        </div>
    </div>
</div>

<!-- AI Speaking overlay -->
<div id="speaking-overlay" style="display:none; position:fixed; inset:0; z-index:50; background:rgba(5,5,9,0.95); flex-direction:column; align-items:center; justify-content:center;">
    <div style="width:120px; height:120px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 1.5s ease-in-out infinite; margin-bottom:24px;">
        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
            <!-- Wave bars -->
            <div id="wave-bars" style="display:flex; gap:3px; align-items:center; height:30px;">
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.1s infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.2s infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.3s infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.4s infinite;"></div>
            </div>
        </div>
    </div>
    <p id="speaking-text" style="color:var(--text); font-size:16px; text-align:center; padding:0 32px; max-width:320px; line-height:1.5;"></p>
    <button onclick="stopSpeaking()" style="margin-top:24px; background:rgba(255,255,255,0.1); border:1px solid var(--border); border-radius:999px; color:var(--text); padding:10px 24px; font-size:14px; cursor:pointer;">Parar</button>
</div>

<!-- Input area -->
<div style="position:fixed; bottom:0; left:0; right:0; z-index:20; background:rgba(5,5,9,0.95); backdrop-filter:blur(20px); border-top:1px solid var(--border); padding-bottom:var(--safe-bottom);">
    <div style="display:flex; align-items:flex-end; gap:8px; padding:12px 16px;">
        <!-- Mode toggle: text/voice -->
        <button id="mode-toggle" onclick="toggleInputMode()" style="background:var(--bg-card); border:1px solid var(--border); border-radius:50%; width:42px; height:42px; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; color:var(--text-dim); transition:color 0.2s;">
            <svg id="icon-keyboard" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h.01M12 12h.01M16 12h.01M7 16h10"/></svg>
            <svg id="icon-mic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
        </button>

        <!-- Text input -->
        <div id="text-input-area" style="flex:1; display:none;">
            <div style="display:flex; align-items:flex-end; gap:8px;">
                <textarea id="msg-input" rows="1" placeholder="Digite sua mensagem..." style="flex:1; resize:none; max-height:120px; border-radius:22px; padding:10px 16px; font-size:15px;" oninput="autoResize(this)"></textarea>
                <button onclick="sendTextMessage()" style="background:linear-gradient(135deg, var(--accent), var(--accent-soft)); border:none; border-radius:50%; width:42px; height:42px; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
        </div>

        <!-- Voice input (default) -->
        <div id="voice-input-area" style="flex:1; display:flex; align-items:center; justify-content:center;">
            <button id="voice-btn" ontouchstart="startRecording(event)" ontouchend="stopRecording(event)" onmousedown="startRecording(event)" onmouseup="stopRecording(event)"
                style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:transform 0.2s; position:relative;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                <!-- Pulse ring -->
                <div id="recording-ring" style="display:none; position:absolute; inset:-8px; border-radius:50%; border:2px solid var(--accent); animation:pulse-ring 1.5s ease-in-out infinite;"></div>
            </button>
            <p style="color:var(--text-dim); font-size:12px; position:absolute; bottom:calc(var(--safe-bottom) + 72px); text-align:center; width:100%; pointer-events:none;">Segure para falar</p>
        </div>
    </div>
</div>

<style>
    #messages { scroll-behavior: smooth; }
    .msg { animation: fadeInUp 0.3s ease; }
    #voice-btn:active { transform: scale(1.15); }
    #voice-btn.recording { animation: glow 1s ease-in-out infinite; }
    textarea { scrollbar-width: none; }
    textarea::-webkit-scrollbar { display: none; }
</style>

<script>
const conversationId = <?= (int)$conversationId ?>;
const voiceEnabled = <?= $voiceEnabled ? 'true' : 'false' ?>;
let isVoiceMode = voiceEnabled;
let isRecording = false;
let mediaRecorder = null;
let audioChunks = [];
let currentAudio = null;

// Init: show correct input mode
function updateInputMode() {
    document.getElementById('text-input-area').style.display = isVoiceMode ? 'none' : 'flex';
    document.getElementById('voice-input-area').style.display = isVoiceMode ? 'flex' : 'none';
    document.getElementById('icon-keyboard').style.display = isVoiceMode ? 'block' : 'none';
    document.getElementById('icon-mic').style.display = isVoiceMode ? 'none' : 'block';
}

function toggleInputMode() {
    isVoiceMode = !isVoiceMode;
    updateInputMode();
    if (!isVoiceMode) {
        setTimeout(() => document.getElementById('msg-input').focus(), 100);
    }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function scrollToBottom() {
    const el = document.getElementById('messages');
    el.scrollTop = el.scrollHeight;
}

function addMessage(role, content) {
    const empty = document.getElementById('empty-state');
    if (empty) empty.remove();

    const div = document.createElement('div');
    div.className = `msg msg-${role === 'user' ? 'user' : 'ai'}`;
    div.style.cssText = `margin-bottom:16px; display:flex; ${role === 'user' ? 'justify-content:flex-end' : 'justify-content:flex-start'};`;

    const isUser = role === 'user';
    const bubble = document.createElement('div');
    bubble.style.cssText = `max-width:85%; padding:12px 16px; border-radius:18px; font-size:15px; line-height:1.6; word-break:break-word; ${isUser
        ? 'background:linear-gradient(135deg, var(--accent), var(--accent-soft)); color:#fff; border-bottom-right-radius:4px;'
        : 'background:var(--bg-card); border:1px solid var(--border); border-bottom-left-radius:4px;'}`;
    bubble.innerHTML = content.replace(/\n/g, '<br>');

    if (!isUser && voiceEnabled) {
        const btn = document.createElement('button');
        btn.onclick = function() { playTTS(this); };
        btn.dataset.text = content;
        btn.style.cssText = 'background:none; border:none; color:var(--text-dim); cursor:pointer; padding:4px; margin-top:4px; display:block;';
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>';
        bubble.appendChild(btn);
    }

    div.appendChild(bubble);
    document.getElementById('typing').before(div);
    scrollToBottom();

    return bubble;
}

function showTyping() {
    document.getElementById('typing').style.display = 'block';
    document.getElementById('status-text').textContent = 'Digitando...';
    scrollToBottom();
}

function hideTyping() {
    document.getElementById('typing').style.display = 'none';
    document.getElementById('status-text').textContent = 'Online';
}

// Send text message
function sendTextMessage() {
    const input = document.getElementById('msg-input');
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    input.style.height = 'auto';
    addMessage('user', text);
    showTyping();

    fetch('/m/chat/enviar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `conversation_id=${conversationId}&message=${encodeURIComponent(text)}`
    })
    .then(r => r.json())
    .then(data => {
        hideTyping();
        if (data.ok) {
            const bubble = addMessage('assistant', data.reply);
            if (isVoiceMode && voiceEnabled) {
                autoPlayTTS(data.reply);
            }
        } else {
            addMessage('assistant', data.error || 'Erro ao processar mensagem.');
        }
    })
    .catch(() => {
        hideTyping();
        addMessage('assistant', 'Erro de conexão. Tente novamente.');
    });
}

// Voice recording
function startRecording(e) {
    e.preventDefault();
    if (isRecording) return;

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            isRecording = true;
            audioChunks = [];
            document.getElementById('voice-btn').classList.add('recording');
            document.getElementById('recording-ring').style.display = 'block';
            document.getElementById('status-text').textContent = 'Ouvindo...';

            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
            mediaRecorder.onstop = () => {
                stream.getTracks().forEach(t => t.stop());
                processVoiceInput();
            };
            mediaRecorder.start();
        })
        .catch(() => {
            alert('Permita o acesso ao microfone para usar o chat por voz.');
        });
}

function stopRecording(e) {
    e.preventDefault();
    if (!isRecording || !mediaRecorder) return;
    isRecording = false;
    document.getElementById('voice-btn').classList.remove('recording');
    document.getElementById('recording-ring').style.display = 'none';
    mediaRecorder.stop();
}

function processVoiceInput() {
    const blob = new Blob(audioChunks, { type: 'audio/webm' });
    if (blob.size < 1000) { // Too short
        document.getElementById('status-text').textContent = 'Online';
        return;
    }

    document.getElementById('status-text').textContent = 'Processando...';

    const fd = new FormData();
    fd.append('audio', blob, 'voice.webm');
    fd.append('conversation_id', conversationId);

    // Use existing audio endpoint for transcription, then send as text
    // For now, we'll use the Web Speech API for transcription
    transcribeAndSend(blob);
}

function transcribeAndSend(blob) {
    // Use Web Speech API if available
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        // Already handled by continuous recognition below
    }
    // Fallback: send audio to server
    const fd = new FormData();
    fd.append('audio', blob, 'voice.webm');
    fd.append('conversation_id', conversationId);

    fetch('/chat/audio', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.transcription || data.text) {
                const text = data.transcription || data.text;
                addMessage('user', text);
                showTyping();
                return fetch('/m/chat/enviar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `conversation_id=${conversationId}&message=${encodeURIComponent(text)}`
                });
            }
            throw new Error('No transcription');
        })
        .then(r => r.json())
        .then(data => {
            hideTyping();
            if (data.ok) {
                addMessage('assistant', data.reply);
                if (voiceEnabled) autoPlayTTS(data.reply);
            }
        })
        .catch(() => {
            hideTyping();
            document.getElementById('status-text').textContent = 'Online';
        });
}

// TTS via ElevenLabs
function playTTS(btn) {
    const text = btn.dataset.text;
    if (!text) return;

    const overlay = document.getElementById('speaking-overlay');
    overlay.style.display = 'flex';
    document.getElementById('speaking-text').textContent = text.substring(0, 200) + (text.length > 200 ? '...' : '');

    const fd = new FormData();
    fd.append('text', text);

    fetch('/m/chat/tts', { method: 'POST', body: fd })
        .then(r => {
            if (!r.ok) throw new Error('TTS failed');
            return r.blob();
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            currentAudio = new Audio(url);
            currentAudio.onended = () => { overlay.style.display = 'none'; };
            currentAudio.play();
        })
        .catch(() => {
            overlay.style.display = 'none';
        });
}

function autoPlayTTS(text) {
    const overlay = document.getElementById('speaking-overlay');
    overlay.style.display = 'flex';
    document.getElementById('speaking-text').textContent = text.substring(0, 200) + (text.length > 200 ? '...' : '');

    const fd = new FormData();
    fd.append('text', text);

    fetch('/m/chat/tts', { method: 'POST', body: fd })
        .then(r => {
            if (!r.ok) throw new Error('TTS failed');
            return r.blob();
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            currentAudio = new Audio(url);
            currentAudio.onended = () => { overlay.style.display = 'none'; };
            currentAudio.play();
        })
        .catch(() => {
            overlay.style.display = 'none';
        });
}

function stopSpeaking() {
    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }
    document.getElementById('speaking-overlay').style.display = 'none';
}

// Enter to send in text mode
document.getElementById('msg-input').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendTextMessage();
    }
});

// Init
updateInputMode();
scrollToBottom();
</script>
