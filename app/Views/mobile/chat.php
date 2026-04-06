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
            <div style="width:100px; height:100px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 3s ease-in-out infinite; margin-bottom:24px;">
                <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                    <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); opacity:0.7;"></div>
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
            <div style="width:8px; height:8px; border-radius:50%; background:var(--accent); animation:pulse-ring 1.4s ease-in-out infinite;"></div>
            <div style="width:8px; height:8px; border-radius:50%; background:var(--accent); animation:pulse-ring 1.4s ease-in-out 0.2s infinite;"></div>
            <div style="width:8px; height:8px; border-radius:50%; background:var(--accent); animation:pulse-ring 1.4s ease-in-out 0.4s infinite;"></div>
        </div>
    </div>
</div>

<!-- Overlay unificado de voz: 3 estados (ouvindo / pensando / falando) -->
<div id="voice-overlay" style="display:none; position:fixed; inset:0; z-index:50; background:rgba(5,5,9,0.95); flex-direction:column; align-items:center; justify-content:center;">

    <!-- Orb central -->
    <div id="voice-orb" style="width:130px; height:130px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; margin-bottom:24px; transition:all 0.4s;">
        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
            <!-- Estado: Ouvindo (mic) -->
            <div id="orb-listening" style="display:flex;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
            </div>
            <!-- Estado: Pensando (dots) -->
            <div id="orb-thinking" style="display:none; gap:6px; align-items:center;">
                <div class="think-dot" style="width:10px; height:10px; border-radius:50%; background:var(--accent); animation:pulse-ring 1s ease-in-out infinite;"></div>
                <div class="think-dot" style="width:10px; height:10px; border-radius:50%; background:var(--accent); animation:pulse-ring 1s ease-in-out 0.2s infinite;"></div>
                <div class="think-dot" style="width:10px; height:10px; border-radius:50%; background:var(--accent); animation:pulse-ring 1s ease-in-out 0.4s infinite;"></div>
            </div>
            <!-- Estado: Falando (wave bars) -->
            <div id="orb-speaking" style="display:none; gap:3px; align-items:center; height:30px;">
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.1s infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.2s infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.3s infinite;"></div>
                <div style="width:3px; background:var(--accent); border-radius:2px; animation:wave-bar 0.8s ease-in-out 0.4s infinite;"></div>
            </div>
        </div>
        <!-- Pulse rings (ouvindo) -->
        <div id="orb-pulse-1" style="position:absolute; inset:-12px; border-radius:50%; border:2px solid var(--accent); opacity:0.3; animation:pulse-ring 2s ease-in-out infinite;"></div>
        <div id="orb-pulse-2" style="position:absolute; inset:-24px; border-radius:50%; border:1px solid var(--accent); opacity:0.15; animation:pulse-ring 2s ease-in-out 0.5s infinite;"></div>
    </div>

    <p id="voice-status" style="color:var(--text); font-size:16px; font-weight:600; margin-bottom:6px;">Ouvindo...</p>
    <p id="voice-subtitle" style="color:var(--text-dim); font-size:13px; text-align:center; padding:0 32px; max-width:300px; min-height:20px;"></p>

    <button onclick="stopVoiceSession()" style="margin-top:28px; background:rgba(229,57,53,0.15); border:1px solid rgba(229,57,53,0.3); border-radius:999px; color:var(--accent); padding:10px 24px; font-size:14px; cursor:pointer;">Encerrar</button>
</div>

<!-- Input area -->
<div style="position:fixed; bottom:0; left:0; right:0; z-index:20; background:rgba(5,5,9,0.95); backdrop-filter:blur(20px); border-top:1px solid var(--border); padding-bottom:var(--safe-bottom);">
    <div style="display:flex; align-items:flex-end; gap:8px; padding:12px 16px;">
        <!-- Mode toggle -->
        <button id="mode-toggle" onclick="toggleInputMode()" style="background:var(--bg-card); border:1px solid var(--border); border-radius:50%; width:42px; height:42px; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; color:var(--text-dim);">
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
            <button id="voice-btn" onclick="toggleListening()"
                style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:transform 0.2s;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
            </button>
            <p style="color:var(--text-dim); font-size:12px; position:absolute; bottom:calc(var(--safe-bottom) + 72px); text-align:center; width:100%; pointer-events:none;">Toque para falar</p>
        </div>
    </div>
</div>

<style>
    #messages { scroll-behavior: smooth; }
    .msg { animation: fadeInUp 0.3s ease; }
    textarea { scrollbar-width: none; }
    textarea::-webkit-scrollbar { display: none; }
    @keyframes orbit-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    #voice-orb.state-listening { animation: glow 2s ease-in-out infinite; }
    #voice-orb.state-thinking { animation: glow 1s ease-in-out infinite; }
    #voice-orb.state-speaking { animation: glow 1.5s ease-in-out infinite; }
    #orb-thinking { display: none; }
    #orb-speaking { display: none; }
    #orb-listening { display: flex; }
</style>

<script>
const conversationId = <?= (int)$conversationId ?>;
const voiceEnabled = <?= $voiceEnabled ? 'true' : 'false' ?>;
let isVoiceMode = voiceEnabled;
let currentAudio = null;
let recognition = null;
let voiceSessionActive = false;
let isBusy = false;
let hasSent = false;
let messageAbort = null;  // AbortController pro fetch da mensagem
let ttsAbort = null;      // AbortController pro fetch do TTS

// ========== Voice Overlay States ==========
function setVoiceState(state) {
    const orb = document.getElementById('voice-orb');
    const listening = document.getElementById('orb-listening');
    const thinking = document.getElementById('orb-thinking');
    const speaking = document.getElementById('orb-speaking');
    const pulse1 = document.getElementById('orb-pulse-1');
    const pulse2 = document.getElementById('orb-pulse-2');
    const status = document.getElementById('voice-status');
    const subtitle = document.getElementById('voice-subtitle');

    orb.className = '';
    listening.style.display = 'none';
    thinking.style.display = 'none';
    speaking.style.display = 'none';

    if (state === 'listening') {
        orb.classList.add('state-listening');
        listening.style.display = 'flex';
        pulse1.style.display = 'block';
        pulse2.style.display = 'block';
        status.textContent = 'Ouvindo...';
        subtitle.textContent = 'Fale normalmente';
    } else if (state === 'thinking') {
        orb.classList.add('state-thinking');
        thinking.style.display = 'flex';
        pulse1.style.display = 'none';
        pulse2.style.display = 'none';
        status.textContent = 'Pensando...';
        subtitle.textContent = '';
    } else if (state === 'speaking') {
        orb.classList.add('state-speaking');
        speaking.style.display = 'flex';
        pulse1.style.display = 'none';
        pulse2.style.display = 'none';
        status.textContent = 'Respondendo...';
        subtitle.textContent = 'Toque para interromper';
    }
}

function showVoiceOverlay() {
    document.getElementById('voice-overlay').style.display = 'flex';
}

function hideVoiceOverlay() {
    document.getElementById('voice-overlay').style.display = 'none';
}

// ========== Input Mode ==========
function updateInputMode() {
    document.getElementById('text-input-area').style.display = isVoiceMode ? 'none' : 'flex';
    document.getElementById('voice-input-area').style.display = isVoiceMode ? 'flex' : 'none';
    document.getElementById('icon-keyboard').style.display = isVoiceMode ? 'block' : 'none';
    document.getElementById('icon-mic').style.display = isVoiceMode ? 'none' : 'block';
}

function toggleInputMode() {
    isVoiceMode = !isVoiceMode;
    if (!isVoiceMode && voiceSessionActive) stopVoiceSession();
    updateInputMode();
    if (!isVoiceMode) setTimeout(() => document.getElementById('msg-input').focus(), 100);
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ========== Messages ==========
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
}

function showTyping() {
    document.getElementById('typing').style.display = 'block';
    document.getElementById('status-text').textContent = 'Pensando...';
    scrollToBottom();
}

function hideTyping() {
    document.getElementById('typing').style.display = 'none';
    document.getElementById('status-text').textContent = 'Online';
}

// ========== Cancel ==========
function cancelAllPending() {
    if (messageAbort) { messageAbort.abort(); messageAbort = null; }
    if (ttsAbort) { ttsAbort.abort(); ttsAbort = null; }
    if (currentAudio) {
        currentAudio.pause();
        currentAudio.onended = null;
        currentAudio.onerror = null;
        currentAudio = null;
    }
}

// ========== Send Message ==========
function sendMessage(text, fromVoice) {
    if (!text || !text.trim()) return;
    text = text.trim();

    cancelAllPending();
    destroyRecognition();

    addMessage('user', text);
    isBusy = true;

    if (fromVoice && voiceSessionActive) {
        setVoiceState('thinking');
    } else {
        showTyping();
    }

    let body = `conversation_id=${conversationId}&message=${encodeURIComponent(text)}`;
    if (fromVoice) body += '&voice_mode=1';

    messageAbort = new AbortController();
    const messageTimeout = setTimeout(() => messageAbort.abort(), 180000); // 3 min timeout

    fetch('/m/chat/enviar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body,
        signal: messageAbort.signal
    })
    .then(r => { clearTimeout(messageTimeout); return r.json(); })
    .then(data => {
        hideTyping();
        if (data.ok) {
            addMessage('assistant', data.reply);
            if (fromVoice && voiceSessionActive && voiceEnabled) {
                setVoiceState('speaking');
                doTTS(data.reply, true);
            } else {
                isBusy = false;
            }
        } else {
            addMessage('assistant', data.error || 'Erro ao processar mensagem.');
            isBusy = false;
            if (fromVoice && voiceSessionActive) resumeListening();
        }
    })
    .catch(err => {
        if (err.name === 'AbortError') return;
        hideTyping();
        addMessage('assistant', 'Erro de conexão.');
        isBusy = false;
        if (fromVoice && voiceSessionActive) resumeListening();
    });
}

function sendTextMessage() {
    const input = document.getElementById('msg-input');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    input.style.height = 'auto';
    sendMessage(text, false);
}

// ========== Voice Session ==========
function toggleListening() {
    if (voiceSessionActive) {
        stopVoiceSession();
    } else {
        startVoiceSession();
    }
}

let wakeLock = null;

async function requestWakeLock() {
    try {
        if ('wakeLock' in navigator) {
            wakeLock = await navigator.wakeLock.request('screen');
        }
    } catch(e) {}
}

function releaseWakeLock() {
    if (wakeLock) {
        try { wakeLock.release(); } catch(e) {}
        wakeLock = null;
    }
}

function startVoiceSession() {
    if (voiceSessionActive) return;

    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
        alert('Seu navegador não suporta reconhecimento de voz.');
        return;
    }

    voiceSessionActive = true;
    isBusy = false;
    requestWakeLock();
    showVoiceOverlay();
    setVoiceState('listening');
    startSingleListen();
}

function stopVoiceSession() {
    voiceSessionActive = false;
    isBusy = false;
    cancelAllPending();
    destroyRecognition();
    releaseWakeLock();
    hideVoiceOverlay();
}

function destroyRecognition() {
    if (recognition) {
        try { recognition.abort(); } catch(e) {}
        recognition = null;
    }
}

function startSingleListen() {
    if (!voiceSessionActive) return;

    destroyRecognition();

    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SR();
    recognition.lang = 'pt-BR';
    recognition.continuous = true;
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    hasSent = false;
    let accumulatedText = '';
    let silenceTimer = null;
    const SILENCE_DELAY = 5500; // 5.5s de silêncio = envia

    function resetSilenceTimer() {
        if (silenceTimer) clearTimeout(silenceTimer);
        silenceTimer = setTimeout(() => {
            if (hasSent || !voiceSessionActive) return;
            const finalText = accumulatedText.trim();
            if (finalText) {
                hasSent = true;
                try { recognition.stop(); } catch(e) {}
                sendMessage(finalText, true);
            }
        }, SILENCE_DELAY);
    }

    recognition.onresult = function(e) {
        if (hasSent) return;
        // Só pega resultados finais (isFinal), evita duplicação
        let newText = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            if (e.results[i].isFinal) {
                newText += e.results[i][0].transcript + ' ';
            }
        }
        if (newText.trim()) {
            accumulatedText += newText;
            resetSilenceTimer();
        }
    };

    recognition.onerror = function(e) {
        if (silenceTimer) clearTimeout(silenceTimer);
        if (e.error === 'not-allowed') {
            alert('Permita o acesso ao microfone.');
            stopVoiceSession();
            return;
        }
        if (!hasSent && accumulatedText.trim()) {
            hasSent = true;
            sendMessage(accumulatedText.trim(), true);
            return;
        }
        if (voiceSessionActive && !isBusy) {
            setTimeout(() => startSingleListen(), 500);
        }
    };

    recognition.onend = function() {
        if (silenceTimer) clearTimeout(silenceTimer);
        if (!hasSent && accumulatedText.trim()) {
            hasSent = true;
            sendMessage(accumulatedText.trim(), true);
            return;
        }
        if (!hasSent && voiceSessionActive && !isBusy) {
            setTimeout(() => startSingleListen(), 300);
        }
    };

    try {
        recognition.start();
    } catch(e) {
        setTimeout(() => startSingleListen(), 500);
    }
}

function resumeListening() {
    if (!voiceSessionActive) return;
    isBusy = false;
    setVoiceState('listening');
    startSingleListen();
}

// ========== TTS ==========
function playTTS(btn) {
    const text = btn.dataset.text;
    if (!text) return;
    showVoiceOverlay();
    setVoiceState('speaking');
    doTTS(text, false);
}

function doTTS(text, reopenMicAfter) {
    const fd = new FormData();
    fd.append('text', text);

    ttsAbort = new AbortController();

    fetch('/m/chat/tts', { method: 'POST', body: fd, signal: ttsAbort.signal })
        .then(r => {
            const ct = r.headers.get('content-type') || '';
            if (!r.ok || !ct.includes('audio')) throw new Error('Not audio');
            return r.blob();
        })
        .then(blob => {
            if (!blob || blob.size < 100) throw new Error('Empty');
            const url = URL.createObjectURL(blob);
            currentAudio = new Audio(url);

            const done = () => {
                URL.revokeObjectURL(url);
                currentAudio = null;
                isBusy = false;
                if (reopenMicAfter && voiceSessionActive) resumeListening();
                else hideVoiceOverlay();
            };

            currentAudio.onended = done;
            currentAudio.onerror = done;
            currentAudio.play().catch(() => done());
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            isBusy = false;
            if (reopenMicAfter && voiceSessionActive) resumeListening();
            else hideVoiceOverlay();
        });
}

// Interromper: tocar na tela enquanto IA fala
document.getElementById('voice-overlay').addEventListener('click', function(e) {
    if (!voiceSessionActive) return;
    if (e.target.tagName === 'BUTTON') return;

    const orb = document.getElementById('voice-orb');
    if (orb.classList.contains('state-speaking') || orb.classList.contains('state-thinking')) {
        cancelAllPending();
        isBusy = false;
        resumeListening();
    }
});

// ========== Keyboard ==========
document.getElementById('msg-input').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendTextMessage();
    }
});

// ========== Init ==========
updateInputMode();
scrollToBottom();
</script>
