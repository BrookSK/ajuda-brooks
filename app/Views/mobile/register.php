<?php /** @var string|null $error */ ?>
<div style="min-height:100dvh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px;">
    <div class="fade-in" style="text-align:center; margin-bottom:32px;">
        <div style="width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--accent-soft)); margin:0 auto 16px; display:flex; align-items:center; justify-content:center;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
        </div>
        <h1 style="font-size:24px; font-weight:700; margin-bottom:4px;">Criar conta</h1>
        <p style="color:var(--text-dim); font-size:14px;">Vamos configurar tudo pra você</p>
    </div>

    <?php if ($error): ?>
        <div class="fade-in" style="background:rgba(229,57,53,0.12); border:1px solid rgba(229,57,53,0.3); border-radius:12px; padding:12px 16px; margin-bottom:16px; width:100%; max-width:360px; font-size:14px; color:#ff8a80;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/m/registrar" style="width:100%; max-width:360px;" class="fade-in fade-in-delay-1">
        <div style="margin-bottom:12px;">
            <input type="text" name="name" placeholder="Seu nome" required autocomplete="name">
        </div>
        <div style="margin-bottom:12px;">
            <input type="email" name="email" placeholder="Seu e-mail" required autocomplete="email" inputmode="email">
        </div>
        <div style="margin-bottom:20px;">
            <input type="password" name="password" placeholder="Crie uma senha" required autocomplete="new-password" minlength="6">
        </div>
        <button type="submit" class="btn-primary">Criar conta</button>
    </form>

    <div class="fade-in fade-in-delay-2" style="margin-top:20px; text-align:center;">
        <a href="/m/login" style="color:var(--accent); text-decoration:none; font-size:14px;">Já tenho uma conta</a>
    </div>
</div>
