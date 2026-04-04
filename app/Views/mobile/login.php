<?php /** @var string|null $error */ ?>
<div style="min-height:100dvh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px;">
    <div class="fade-in" style="text-align:center; margin-bottom:32px;">
        <div style="width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); margin:0 auto 16px; display:flex; align-items:center; justify-content:center;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
        </div>
        <h1 style="font-size:24px; font-weight:700; margin-bottom:4px;">Bem-vindo de volta</h1>
        <p style="color:var(--text-dim); font-size:14px;">Entre para continuar</p>
    </div>

    <?php if ($error): ?>
        <div class="fade-in" style="background:rgba(229,57,53,0.12); border:1px solid rgba(229,57,53,0.3); border-radius:12px; padding:12px 16px; margin-bottom:16px; width:100%; max-width:360px; font-size:14px; color:#ff8a80;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/m/login" style="width:100%; max-width:360px;" class="fade-in fade-in-delay-1">
        <div style="margin-bottom:12px;">
            <input type="email" name="email" placeholder="Seu e-mail" required autocomplete="email" inputmode="email">
        </div>
        <div style="margin-bottom:20px;">
            <input type="password" name="password" placeholder="Sua senha" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn-primary">Entrar</button>
    </form>

    <div class="fade-in fade-in-delay-2" style="margin-top:20px; text-align:center;">
        <a href="/m/registrar" style="color:var(--accent); text-decoration:none; font-size:14px;">Criar uma conta</a>
        <span style="color:var(--text-dim); margin:0 8px;">·</span>
        <a href="/senha/esqueci" style="color:var(--text-dim); text-decoration:none; font-size:14px;">Esqueci a senha</a>
    </div>
</div>
