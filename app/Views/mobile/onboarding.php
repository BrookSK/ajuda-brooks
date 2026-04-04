<?php
/** @var array $user */
/** @var array|null $onboarding */
/** @var array $personalities */
?>
<div id="onboarding-app" style="min-height:100dvh; display:flex; flex-direction:column; position:relative; overflow:hidden;">

    <!-- Fundo tech sutil -->
    <div style="position:fixed; inset:0; pointer-events:none; z-index:0;">
        <div style="position:absolute; top:-120px; right:-120px; width:300px; height:300px; border-radius:50%; background:radial-gradient(circle, rgba(229,57,53,0.08) 0%, transparent 70%);"></div>
        <div style="position:absolute; bottom:-80px; left:-80px; width:250px; height:250px; border-radius:50%; background:radial-gradient(circle, rgba(229,57,53,0.05) 0%, transparent 70%);"></div>
    </div>

    <!-- Progress bar -->
    <div style="position:fixed; top:0; left:0; right:0; z-index:10; padding-top:var(--safe-top);">
        <div style="height:3px; background:var(--border);">
            <div id="progress-bar" style="height:100%; background:linear-gradient(90deg, var(--accent), var(--accent-soft)); width:0%; transition:width 0.6s cubic-bezier(0.4,0,0.2,1);"></div>
        </div>
    </div>

    <!-- Container dos steps -->
    <div id="steps-container" style="flex:1; display:flex; flex-direction:column; justify-content:center; padding:24px; padding-top:calc(var(--safe-top) + 20px); position:relative; z-index:1;">

        <!-- Step 1: Como quer ser chamado -->
        <div class="step active" data-step="1">
            <div class="step-content fade-in">
                <!-- Orb animado estilo Jarvis -->
                <div style="display:flex; justify-content:center; margin-bottom:28px;">
                    <div id="ai-orb" style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 3s ease-in-out infinite;">
                        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                            <div style="width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); opacity:0.8;"></div>
                        </div>
                    </div>
                </div>

                <div style="text-align:center; margin-bottom:24px;">
                    <p style="color:var(--text-dim); font-size:13px; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px;">Passo 1 de 6</p>
                    <h2 style="font-size:22px; font-weight:700; margin-bottom:8px;">Olá! 👋</h2>
                    <p style="color:var(--text-dim); font-size:15px; line-height:1.6;">Como você gostaria de ser chamado?</p>
                </div>

                <div style="max-width:340px; margin:0 auto; width:100%;">
                    <input type="text" id="input-name" placeholder="Ex: Lucas, Mari, João..." value="<?= htmlspecialchars($user['preferred_name'] ?? $user['name'] ?? '') ?>" autofocus style="text-align:center; font-size:18px; padding:16px;">
                    <button onclick="nextStep(1)" class="btn-primary" style="margin-top:16px;">Continuar</button>
                </div>
            </div>
        </div>

        <!-- Step 2: Nomear a ferramenta -->
        <div class="step" data-step="2">
            <div class="step-content">
                <div style="display:flex; justify-content:center; margin-bottom:28px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 3s ease-in-out infinite;">
                        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                    </div>
                </div>

                <div style="text-align:center; margin-bottom:24px;">
                    <p style="color:var(--text-dim); font-size:13px; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px;">Passo 2 de 6</p>
                    <h2 style="font-size:22px; font-weight:700; margin-bottom:8px;">Legal, <span id="display-name"></span>!</h2>
                    <p style="color:var(--text-dim); font-size:15px; line-height:1.6;">Agora, como quer chamar sua IA?<br><span style="font-size:13px;">Tipo Jarvis, Luna, Atlas... fica a seu critério.</span></p>
                </div>

                <div style="max-width:340px; margin:0 auto; width:100%;">
                    <input type="text" id="input-tool-name" placeholder="Ex: Jarvis, Luna, Atlas..." value="<?= htmlspecialchars($onboarding['tool_name'] ?? '') ?>" style="text-align:center; font-size:18px; padding:16px;">
                    <button onclick="nextStep(2)" class="btn-primary" style="margin-top:16px;">Continuar</button>
                    <button onclick="prevStep(2)" class="btn-ghost" style="margin-top:8px; width:100%;">Voltar</button>
                </div>
            </div>
        </div>

        <!-- Step 3: Tom de conversa -->
        <div class="step" data-step="3">
            <div class="step-content">
                <div style="display:flex; justify-content:center; margin-bottom:28px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 3s ease-in-out infinite;">
                        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
                        </div>
                    </div>
                </div>

                <div style="text-align:center; margin-bottom:20px;">
                    <p style="color:var(--text-dim); font-size:13px; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px;">Passo 3 de 6</p>
                    <h2 style="font-size:22px; font-weight:700; margin-bottom:8px;">Tom de conversa</h2>
                    <p style="color:var(--text-dim); font-size:15px; line-height:1.6;">Como <span id="tool-name-display"></span> deve falar com você?</p>
                </div>

                <!-- Slider horizontal de tons -->
                <div id="tone-slider" style="display:flex; gap:12px; overflow-x:auto; padding:8px 4px 16px; scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch;">
                    <div class="tone-card" data-tone="descontraido" onclick="selectTone(this, 'descontraido')"
                         style="min-width:150px; max-width:150px; scroll-snap-align:center; background:var(--bg-card); border:2px solid var(--border); border-radius:16px; padding:16px; text-align:center; cursor:pointer; transition:border-color 0.2s, transform 0.1s; flex-shrink:0;">
                        <div style="font-size:28px; margin-bottom:8px;">😄</div>
                        <div style="font-weight:600; font-size:14px; margin-bottom:4px;">Descontraído</div>
                        <div style="color:var(--text-dim); font-size:12px; line-height:1.4;">Leve, com humor e linguagem informal</div>
                    </div>
                    <div class="tone-card" data-tone="amigavel" onclick="selectTone(this, 'amigavel')"
                         style="min-width:150px; max-width:150px; scroll-snap-align:center; background:var(--bg-card); border:2px solid var(--border); border-radius:16px; padding:16px; text-align:center; cursor:pointer; transition:border-color 0.2s, transform 0.1s; flex-shrink:0;">
                        <div style="font-size:28px; margin-bottom:8px;">🤝</div>
                        <div style="font-weight:600; font-size:14px; margin-bottom:4px;">Amigável</div>
                        <div style="color:var(--text-dim); font-size:12px; line-height:1.4;">Próximo e acolhedor, como um amigo</div>
                    </div>
                    <div class="tone-card" data-tone="profissional" onclick="selectTone(this, 'profissional')"
                         style="min-width:150px; max-width:150px; scroll-snap-align:center; background:var(--bg-card); border:2px solid var(--border); border-radius:16px; padding:16px; text-align:center; cursor:pointer; transition:border-color 0.2s, transform 0.1s; flex-shrink:0;">
                        <div style="font-size:28px; margin-bottom:8px;">💼</div>
                        <div style="font-weight:600; font-size:14px; margin-bottom:4px;">Profissional</div>
                        <div style="color:var(--text-dim); font-size:12px; line-height:1.4;">Direto e objetivo, foco em resultados</div>
                    </div>
                    <div class="tone-card" data-tone="formal" onclick="selectTone(this, 'formal')"
                         style="min-width:150px; max-width:150px; scroll-snap-align:center; background:var(--bg-card); border:2px solid var(--border); border-radius:16px; padding:16px; text-align:center; cursor:pointer; transition:border-color 0.2s, transform 0.1s; flex-shrink:0;">
                        <div style="font-size:28px; margin-bottom:8px;">🎩</div>
                        <div style="font-weight:600; font-size:14px; margin-bottom:4px;">Formal</div>
                        <div style="color:var(--text-dim); font-size:12px; line-height:1.4;">Elegante e respeitoso, linguagem culta</div>
                    </div>
                    <div class="tone-card" data-tone="empresarial" onclick="selectTone(this, 'empresarial')"
                         style="min-width:150px; max-width:150px; scroll-snap-align:center; background:var(--bg-card); border:2px solid var(--border); border-radius:16px; padding:16px; text-align:center; cursor:pointer; transition:border-color 0.2s, transform 0.1s; flex-shrink:0;">
                        <div style="font-size:28px; margin-bottom:8px;">🏢</div>
                        <div style="font-weight:600; font-size:14px; margin-bottom:4px;">Empresarial</div>
                        <div style="color:var(--text-dim); font-size:12px; line-height:1.4;">Corporativo, técnico e estratégico</div>
                    </div>
                    <div class="tone-card" data-tone="motivacional" onclick="selectTone(this, 'motivacional')"
                         style="min-width:150px; max-width:150px; scroll-snap-align:center; background:var(--bg-card); border:2px solid var(--border); border-radius:16px; padding:16px; text-align:center; cursor:pointer; transition:border-color 0.2s, transform 0.1s; flex-shrink:0;">
                        <div style="font-size:28px; margin-bottom:8px;">🔥</div>
                        <div style="font-weight:600; font-size:14px; margin-bottom:4px;">Motivacional</div>
                        <div style="color:var(--text-dim); font-size:12px; line-height:1.4;">Energético, inspirador e encorajador</div>
                    </div>
                </div>

                <div style="max-width:340px; margin:0 auto; width:100%;">
                    <button onclick="nextStep(3)" class="btn-primary" id="btn-tone" disabled>Continuar</button>
                    <button onclick="prevStep(3)" class="btn-ghost" style="margin-top:8px; width:100%;">Voltar</button>
                </div>
            </div>
        </div>

        <!-- Step 4: Selecionar ou criar projeto -->
        <div class="step" data-step="4">
            <div class="step-content">
                <div style="display:flex; justify-content:center; margin-bottom:28px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 3s ease-in-out infinite;">
                        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        </div>
                    </div>
                </div>

                <div style="text-align:center; margin-bottom:24px;">
                    <p style="color:var(--text-dim); font-size:13px; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px;">Passo 4 de 6</p>
                    <h2 style="font-size:22px; font-weight:700; margin-bottom:8px;">Seu projeto</h2>
                    <p style="color:var(--text-dim); font-size:15px; line-height:1.6;">Selecione um projeto existente ou crie um novo.<br><span style="font-size:13px;">Os arquivos e conversas serão vinculados a ele.</span></p>
                </div>

                <div style="max-width:340px; margin:0 auto; width:100%;">
                    <?php if (!empty($projects)): ?>
                        <div style="margin-bottom:16px;">
                            <p style="font-size:13px; color:var(--text-dim); margin-bottom:8px;">Projetos existentes:</p>
                            <div id="project-list" style="display:flex; flex-direction:column; gap:8px; max-height:200px; overflow-y:auto;">
                                <?php foreach ($projects as $proj): ?>
                                    <div class="project-card" data-project-id="<?= (int)($proj['id'] ?? 0) ?>" onclick="selectProject(this, <?= (int)($proj['id'] ?? 0) ?>)"
                                         style="background:var(--bg-card); border:2px solid var(--border); border-radius:12px; padding:12px 14px; cursor:pointer; transition:border-color 0.2s;">
                                        <div style="font-weight:600; font-size:14px;"><?= htmlspecialchars((string)($proj['name'] ?? '')) ?></div>
                                        <?php if (!empty($proj['description'])): ?>
                                            <div style="color:var(--text-dim); font-size:12px; margin-top:4px;"><?= htmlspecialchars(mb_substr((string)$proj['description'], 0, 80)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="text-align:center; color:var(--text-dim); font-size:13px; margin-bottom:12px;">ou</div>
                    <?php endif; ?>

                    <input type="text" id="input-new-project" placeholder="Nome do novo projeto (ex: Brasiliana E-commerce)" style="text-align:center; font-size:16px; padding:14px;">

                    <button onclick="nextStep(4)" class="btn-primary" style="margin-top:16px;" id="btn-project">Continuar</button>
                    <button onclick="skipProject()" class="btn-ghost" style="margin-top:8px; width:100%;">Pular por agora</button>
                    <button onclick="prevStep(4)" class="btn-ghost" style="margin-top:8px; width:100%;">Voltar</button>
                </div>
            </div>
        </div>

        <!-- Step 5: Documentos -->
        <div class="step" data-step="5">
            <div class="step-content">
                <div style="display:flex; justify-content:center; margin-bottom:28px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 3s ease-in-out infinite;">
                        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                    </div>
                </div>

                <div style="text-align:center; margin-bottom:24px;">
                    <p style="color:var(--text-dim); font-size:13px; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px;">Passo 5 de 6</p>
                    <h2 style="font-size:22px; font-weight:700; margin-bottom:8px;">Documentos</h2>
                    <p style="color:var(--text-dim); font-size:15px; line-height:1.6;">Quer enviar documentos para <span class="tool-name-ref"></span> aprender?<br><span style="font-size:13px;">PDFs, textos, docs da sua empresa...</span></p>
                </div>

                <div style="max-width:340px; margin:0 auto; width:100%;">
                    <!-- Upload area -->
                    <div id="upload-area" style="border:2px dashed var(--border); border-radius:16px; padding:24px; text-align:center; cursor:pointer; transition:border-color 0.2s; margin-bottom:12px;" onclick="document.getElementById('file-input').click()">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--text-dim)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:8px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <p style="color:var(--text-dim); font-size:14px;">Toque para selecionar arquivos</p>
                        <p style="color:var(--text-dim); font-size:12px; margin-top:4px;">PDF, TXT, DOC — até 10MB</p>
                    </div>
                    <input type="file" id="file-input" accept=".pdf,.txt,.doc,.docx" multiple style="display:none;" onchange="handleFileUpload(this)">

                    <!-- Lista de arquivos enviados -->
                    <div id="uploaded-files" style="margin-bottom:16px;"></div>

                    <button onclick="nextStep(5)" class="btn-primary">Continuar</button>
                    <button onclick="skipStep(5)" class="btn-ghost" style="margin-top:8px; width:100%;">Pular por agora</button>
                    <button onclick="prevStep(5)" class="btn-ghost" style="margin-top:8px; width:100%;">Voltar</button>
                </div>
            </div>
        </div>

        <!-- Step 6: Tudo pronto -->
        <div class="step" data-step="6">
            <div class="step-content">
                <div style="display:flex; justify-content:center; margin-bottom:28px;">
                    <div style="width:100px; height:100px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); position:relative; animation:glow 2s ease-in-out infinite;">
                        <div style="position:absolute; inset:3px; border-radius:50%; background:var(--bg); display:flex; align-items:center; justify-content:center;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </div>
                </div>

                <div style="text-align:center; margin-bottom:32px;">
                    <p style="color:var(--text-dim); font-size:13px; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px;">Passo 6 de 6</p>
                    <h2 style="font-size:24px; font-weight:700; margin-bottom:8px;">Tudo pronto! 🚀</h2>
                    <p style="color:var(--text-dim); font-size:15px; line-height:1.6;">
                        <span class="tool-name-ref"></span> está configurado e pronto para conversar com você, <span class="user-name-ref"></span>.
                    </p>
                </div>

                <div style="max-width:340px; margin:0 auto; width:100%;">
                    <button onclick="completeOnboarding()" class="btn-primary" style="font-size:18px; padding:16px 32px;">
                        Começar a conversar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .step { display:none; }
    .step.active { display:block; }
    .step.active .step-content { animation: fadeInUp 0.5s ease forwards; }
    .tone-card.selected {
        border-color: var(--accent) !important;
        background: rgba(229,57,53,0.08) !important;
        transform: scale(1.02);
    }
    #upload-area:hover, #upload-area.dragover {
        border-color: var(--accent);
    }
    .uploaded-file {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 10px 14px;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .uploaded-file .file-icon { color: var(--accent); flex-shrink: 0; }
    .uploaded-file .file-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .uploaded-file .file-status { font-size: 12px; color: var(--text-dim); }
    .uploaded-file.success .file-status { color: #66bb6a; }
    .uploaded-file.error .file-status { color: #ff8a80; }
</style>

<script>
let currentStep = 1;
const totalSteps = 6;
let selectedPersonalityId = null;
let selectedTone = null;
let selectedProjectId = null;
let userName = '';
let toolName = '';

function updateProgress() {
    const pct = ((currentStep - 1) / (totalSteps - 1)) * 100;
    document.getElementById('progress-bar').style.width = pct + '%';
}

function showStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    const step = document.querySelector(`.step[data-step="${n}"]`);
    if (step) {
        step.classList.add('active');
        // Re-trigger animation
        const content = step.querySelector('.step-content');
        if (content) {
            content.style.animation = 'none';
            content.offsetHeight; // reflow
            content.style.animation = 'fadeInUp 0.5s ease forwards';
        }
    }
    currentStep = n;
    updateProgress();
}

function saveStep(step, value) {
    return fetch('/m/onboarding/salvar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `step=${encodeURIComponent(step)}&value=${encodeURIComponent(value)}`
    }).then(r => r.json());
}

function nextStep(from) {
    if (from === 1) {
        userName = document.getElementById('input-name').value.trim();
        if (!userName) { document.getElementById('input-name').focus(); return; }
        document.getElementById('display-name').textContent = userName;
        document.querySelectorAll('.user-name-ref').forEach(el => el.textContent = userName);
        saveStep('preferred_name', userName);
        showStep(2);
    } else if (from === 2) {
        toolName = document.getElementById('input-tool-name').value.trim();
        if (!toolName) { document.getElementById('input-tool-name').focus(); return; }
        document.getElementById('tool-name-display').textContent = toolName;
        document.querySelectorAll('.tool-name-ref').forEach(el => el.textContent = toolName);
        saveStep('tool_name', toolName);
        showStep(3);
    } else if (from === 3) {
        if (!selectedTone) return;
        saveStep('conversation_tone', selectedTone);
        showStep(4);
    } else if (from === 4) {
        // Projeto: criar novo ou usar selecionado
        var newProjectName = document.getElementById('input-new-project').value.trim();
        if (newProjectName) {
            fetch('/m/onboarding/salvar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'step=project&action=create&value=' + encodeURIComponent(newProjectName)
            }).then(r => r.json()).then(() => showStep(5));
        } else if (selectedProjectId) {
            fetch('/m/onboarding/salvar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'step=project&action=select&value=' + selectedProjectId
            }).then(r => r.json()).then(() => showStep(5));
        } else {
            document.getElementById('input-new-project').focus();
            return;
        }
    } else if (from === 5) {
        saveStep('preferences', JSON.stringify({ wants_projects: 1, wants_documents: 1 }));
        showStep(6);
    }
}

function prevStep(from) {
    showStep(from - 1);
}

function skipStep(from) {
    if (from === 5) {
        saveStep('preferences', JSON.stringify({ wants_projects: 0, wants_documents: 0 }));
        showStep(6);
    }
}

function skipProject() {
    fetch('/m/onboarding/salvar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'step=project&action=skip&value=0'
    }).then(r => r.json()).then(() => showStep(5));
}

function selectProject(el, id) {
    document.querySelectorAll('.project-card').forEach(c => {
        c.style.borderColor = 'var(--border)';
        c.style.background = 'var(--bg-card)';
    });
    el.style.borderColor = 'var(--accent)';
    el.style.background = 'rgba(229,57,53,0.08)';
    selectedProjectId = id;
    document.getElementById('input-new-project').value = '';
}

function selectTone(el, tone) {
    document.querySelectorAll('.tone-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedTone = tone;
    document.getElementById('btn-tone').disabled = false;
}

function handleFileUpload(input) {
    const files = input.files;
    for (let i = 0; i < files.length; i++) {
        uploadFile(files[i]);
    }
    input.value = '';
}

function uploadFile(file) {
    const container = document.getElementById('uploaded-files');
    const div = document.createElement('div');
    div.className = 'uploaded-file';
    div.innerHTML = `
        <span class="file-icon">📄</span>
        <span class="file-name">${file.name}</span>
        <span class="file-status">Enviando...</span>
    `;
    container.appendChild(div);

    const fd = new FormData();
    fd.append('document', file);

    fetch('/m/onboarding/upload', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                div.classList.add('success');
                div.querySelector('.file-status').textContent = '✓ Enviado';
            } else {
                div.classList.add('error');
                div.querySelector('.file-status').textContent = data.error || 'Erro';
            }
        })
        .catch(() => {
            div.classList.add('error');
            div.querySelector('.file-status').textContent = 'Erro de rede';
        });
}

function completeOnboarding() {
    saveStep('complete', '1').then(() => {
        window.location.href = '/m/chat?new=1';
    });
}

// Drag & drop no upload area
const uploadArea = document.getElementById('upload-area');
if (uploadArea) {
    uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        for (let i = 0; i < e.dataTransfer.files.length; i++) {
            uploadFile(e.dataTransfer.files[i]);
        }
    });
}

// Init
updateProgress();
</script>
