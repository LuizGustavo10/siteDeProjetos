<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SINTEC 2.0 — Convite Especial</title>
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

  :root {
    --blue: #0070ff;
    --blue-glow: rgba(0, 112, 255, 0.6);
    --cyan: #00c8ff;
    --orange: #ff9500;
    --orange-glow: rgba(255, 149, 0, 0.7);
    --dark: #020b1a;
    --glass: rgba(255,255,255,0.04);
    --glass-border: rgba(100,180,255,0.18);
    --text: rgba(200,230,255,0.88);
    --text-dim: rgba(150,190,240,0.65);
  }

  html, body {
    min-height: 100vh;
    background: var(--dark);
    font-family: 'Exo 2', sans-serif;
    color: var(--text);
    overflow-x: hidden;
  }

  #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
  .star {
    position: absolute; border-radius: 50%; background: #fff;
    animation: twinkle var(--d, 2s) ease-in-out infinite alternate;
  }
  @keyframes twinkle { from { opacity: .1; } to { opacity: 1; } }

  .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
  .orb1 { width:420px;height:420px;background:rgba(0,60,180,.32);top:-120px;left:-100px;animation:floatOrb 9s ease-in-out infinite alternate; }
  .orb2 { width:320px;height:320px;background:rgba(255,140,0,.13);bottom:-80px;right:-70px;animation:floatOrb 7s ease-in-out infinite alternate;animation-delay:-3s; }
  .orb3 { width:220px;height:220px;background:rgba(0,120,255,.18);top:40%;right:4%;animation:floatOrb 6s ease-in-out infinite alternate;animation-delay:-5s; }
  @keyframes floatOrb { from{transform:translate(0,0);}to{transform:translate(18px,26px);} }

  /* ─── MAIN LAYOUT: TWO COLUMNS ─── */
  .layout {
    position: relative; z-index: 10;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 28px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 18px 50px;
    min-height: 100vh;
  }

  /* ─── LEFT: INVITE ─── */
  .page {
    flex: 0 0 620px;
    max-width: 620px;
    display: flex; flex-direction: column; align-items: center;
    padding-top: 20px;
  }

  /* ─── RIGHT: SCHEDULE ─── */
  .schedule-panel {
    flex: 0 0 320px;
    max-width: 320px;
    position: sticky;
    top: 30px;
  }

  .schedule-title {
    font-family: 'Orbitron', monospace;
    font-size: .62rem;
    letter-spacing: .32em;
    color: var(--orange);
    text-transform: uppercase;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .schedule-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(255,149,0,.4), transparent);
  }

  .sched-day {
    background: var(--glass);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 10px;
    backdrop-filter: blur(10px);
    transition: border-color .2s;
  }
  .sched-day:hover { border-color: rgba(0,180,255,.32); }

  .sched-day-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: rgba(0,60,180,.15);
    border-bottom: 1px solid var(--glass-border);
    cursor: pointer;
  }
  .sched-day-header.always-open { cursor: default; }

  .sched-day-dot {
    width: 8px; height: 8px; border-radius: 50%;
    flex-shrink: 0;
  }
  .dot-seg { background: #0070ff; box-shadow: 0 0 6px rgba(0,112,255,.7); }
  .dot-ter { background: #00c8ff; box-shadow: 0 0 6px rgba(0,200,255,.7); }
  .dot-qua { background: #a855f7; box-shadow: 0 0 6px rgba(168,85,247,.7); }
  .dot-qui { background: var(--orange); box-shadow: 0 0 6px var(--orange-glow); }
  .dot-all { background: #22d3ee; box-shadow: 0 0 6px rgba(34,211,238,.7); }

  .sched-day-label {
    font-family: 'Orbitron', monospace;
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .1em;
    color: #fff;
    flex: 1;
  }
  .sched-day-date {
    font-size: .68rem;
    color: var(--text-dim);
  }
  .sched-chevron {
    font-size: .6rem;
    color: var(--text-dim);
    transition: transform .25s;
  }
  .sched-day.open .sched-chevron { transform: rotate(180deg); }

  .sched-items {
    max-height: 0;
    overflow: hidden;
    transition: max-height .35s ease;
  }
  .sched-day.open .sched-items { max-height: 500px; }

  .sched-item {
    padding: 10px 14px;
    border-bottom: 1px solid rgba(100,180,255,.07);
    display: flex;
    gap: 10px;
    align-items: flex-start;
  }
  .sched-item:last-child { border-bottom: none; }

  .sched-period {
    font-family: 'Orbitron', monospace;
    font-size: .5rem;
    letter-spacing: .15em;
    padding: 2px 7px;
    border-radius: 20px;
    flex-shrink: 0;
    margin-top: 1px;
  }
  .period-manha { background:rgba(255,149,0,.15); color:var(--orange); border:1px solid rgba(255,149,0,.3); }
  .period-tarde { background:rgba(0,112,255,.15); color:#60aeff; border:1px solid rgba(0,112,255,.3); }
  .period-noite { background:rgba(168,85,247,.15); color:#c084fc; border:1px solid rgba(168,85,247,.3); }
  .period-semana { background:rgba(34,211,238,.12); color:#22d3ee; border:1px solid rgba(34,211,238,.3); }

  .sched-desc {
    font-size: .78rem;
    line-height: 1.5;
    color: var(--text);
  }

  /* continuous badge */
  .continuous-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(34,211,238,.08);
    border: 1px solid rgba(34,211,238,.25);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: .78rem;
    color: rgba(180,240,255,.9);
    line-height: 1.5;
    margin-bottom: 10px;
    width: 100%;
  }
  .continuous-badge .cb-icon { font-size: .85rem; flex-shrink:0; }

  /* ─── DOME ─── */
  .dome-wrap {
    width: 115px; height: 68px; margin-bottom: 14px;
    animation: floatDome 4s ease-in-out infinite alternate;
  }
  @keyframes floatDome { from{transform:translateY(0);}to{transform:translateY(-9px);} }

  .brand {
    font-family: 'Orbitron', monospace;
    font-size: 2.3rem; font-weight: 900; letter-spacing: .15em;
    color: #fff; text-shadow: 0 0 32px var(--blue-glow); line-height: 1;
  }
  .brand-version {
    font-family: 'Orbitron', monospace;
    font-size: 1.5rem; color: var(--orange);
    text-shadow: 0 0 22px var(--orange-glow); letter-spacing: .12em;
  }
  .tagline {
    font-family: 'Orbitron', monospace; font-size: .55rem;
    letter-spacing: .4em; color: rgba(100,180,255,.65);
    margin: 5px 0 20px; text-transform: uppercase;
  }

  h1 {
    font-size: 2rem; font-weight: 900; text-transform: uppercase;
    letter-spacing: .08em; color: #fff;
    text-shadow: 0 2px 22px rgba(80,160,255,.4);
    text-align: center; margin-bottom: 16px;
  }

  .glass {
    background: var(--glass); border: 1px solid var(--glass-border);
    border-radius: 16px; padding: 22px 26px; width: 100%;
    backdrop-filter: blur(12px); margin-bottom: 14px;
    box-shadow: 0 8px 40px rgba(0,0,40,.5), inset 0 1px 0 rgba(255,255,255,.07);
  }
  .intro { font-size: .95rem; line-height: 1.7; text-align: center; }

  .info-row { display:flex; gap:12px; width:100%; margin-bottom:14px; }
  .info-card {
    flex: 1; background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-border); border-radius: 12px;
    padding: 14px 16px; backdrop-filter: blur(8px);
  }
  .info-label {
    font-family: 'Orbitron', monospace; font-size: .54rem;
    letter-spacing: .28em; color: var(--orange); text-transform: uppercase; margin-bottom: 4px;
  }
  .info-value { color:#fff; font-size:.9rem; font-weight:700; }
  .info-sub { color: var(--text-dim); font-size:.76rem; margin-top:2px; }

  .format-box {
    width:100%; background: rgba(0,80,200,.1);
    border: 1px solid rgba(0,120,255,.22); border-radius:12px;
    padding: 16px 20px; margin-bottom:14px; backdrop-filter:blur(8px);
  }
  .format-title {
    font-family: 'Orbitron', monospace; font-size:.58rem;
    letter-spacing:.28em; color:rgba(100,180,255,.9); text-transform:uppercase; margin-bottom:8px;
  }
  .format-text { font-size:.88rem; line-height:1.65; }
  .format-text strong { color:#fff; }

  .deadline { width:100%; text-align:center; font-size:.88rem; color:var(--text-dim); margin-bottom:26px; }
  .deadline strong { color:var(--orange); }

  .rsvp-box { width:100%; }
  .rsvp-question {
    font-family: 'Orbitron', monospace; font-size:.88rem;
    color:rgba(150,200,255,.9); text-align:center; letter-spacing:.1em; margin-bottom:16px;
  }

  .input-wrap { position:relative; width:100%; margin-bottom:18px; }
  .input-wrap label {
    display:block; font-family:'Orbitron',monospace; font-size:.55rem;
    letter-spacing:.3em; color:var(--cyan); text-transform:uppercase; margin-bottom:8px;
  }
  .name-input {
    width:100%; padding:14px 18px;
    background: rgba(0,60,160,.18); border: 1px solid rgba(0,150,255,.3);
    border-radius: 50px; color:#fff;
    font-family:'Exo 2',sans-serif; font-size:1rem; font-weight:600;
    outline:none; transition: border-color .25s, box-shadow .25s;
    backdrop-filter: blur(8px);
  }
  .name-input::placeholder { color:rgba(120,170,220,.45); font-weight:400; }
  .name-input:focus {
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(0,200,255,.15), 0 0 20px rgba(0,200,255,.1);
  }
  .input-error {
    position:absolute; bottom:-20px; left:18px;
    font-size:.75rem; color:#ff6060;
    opacity:0; transition:opacity .2s;
    pointer-events:none;
  }
  .input-error.show { opacity:1; }

  .btn-row { display:flex; gap:14px; justify-content:center; width:100%; }

  .btn {
    flex:1; max-width:210px; padding:15px 22px;
    border-radius: 50px; border:none;
    font-family:'Orbitron',monospace; font-size:.8rem;
    font-weight:700; letter-spacing:.1em; cursor:pointer;
    position:relative; overflow:hidden; user-select:none;
    transition: transform .15s, box-shadow .15s;
  }
  .btn::after {
    content:''; position:absolute; inset:0; border-radius:inherit;
    background:rgba(255,255,255,.14); opacity:0; transition:opacity .2s;
  }
  .btn:hover::after { opacity:1; }
  .btn:active { transform:scale(.96) !important; }

  .btn-yes {
    background: linear-gradient(135deg, #0070ff, #00aaff); color:#fff;
    box-shadow: 0 4px 24px var(--blue-glow), 0 0 0 1px rgba(0,150,255,.3);
  }
  .btn-yes:hover { transform:translateY(-3px); box-shadow:0 8px 32px rgba(0,120,255,.75); }

  .btn-no {
    background: rgba(255,255,255,.055); color:rgba(180,210,255,.7);
    border:1px solid rgba(100,150,255,.25);
  }
  .btn-no:hover { color:rgba(255,100,100,.9); border-color:rgba(255,100,100,.4); }

  .declined-msg {
    display:none; flex-direction:column; align-items:center;
    gap:12px; padding:20px; text-align:center;
  }
  .declined-icon { font-size:3rem; }
  .declined-text { font-size:.95rem; line-height:1.7; }
  .declined-text strong { color:#fff; }

  #celebration {
    display:none; position:fixed; inset:0; z-index:1000;
    background:rgba(2,11,26,.93); backdrop-filter:blur(8px);
    flex-direction:column; align-items:center; justify-content:center;
    text-align:center; padding:28px;
  }
  #celebration.active { display:flex; }

  .confetti-canvas { position:fixed; inset:0; pointer-events:none; z-index:999; }

  .celebrate-dome { width:120px;height:70px;margin-bottom:16px;animation:pulseDome 1s ease-in-out infinite alternate; }
  @keyframes pulseDome {
    from{transform:scale(1);filter:drop-shadow(0 0 10px rgba(0,150,255,.5));}
    to{transform:scale(1.08);filter:drop-shadow(0 0 30px rgba(0,200,255,.95));}
  }

  .celebrate-name { font-family:'Orbitron',monospace; font-size:1rem; color:var(--cyan); letter-spacing:.1em; margin-bottom:4px; animation:slideUp .5s ease both; }
  .celebrate-title { font-family:'Orbitron',monospace; font-size:1.9rem; font-weight:900; color:#fff; text-shadow:0 0 30px rgba(0,180,255,.85); margin-bottom:8px; animation:slideUp .6s .1s ease both; }
  .celebrate-sub { font-size:1rem; color:rgba(180,230,255,.9); line-height:1.75; max-width:400px; animation:slideUp .6s .2s ease both; margin-bottom:22px; }
  .celebrate-date { font-family:'Orbitron',monospace; font-size:1.05rem; color:var(--orange); text-shadow:0 0 14px var(--orange-glow); animation:slideUp .6s .3s ease both; margin-bottom:28px; }
  @keyframes slideUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

  .rings { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none; }
  .ring { position:absolute; border-radius:50%; border:1px solid rgba(0,180,255,.4); width:var(--s); height:var(--s); animation:expandRing var(--t) ease-out infinite; }
  @keyframes expandRing{0%{transform:scale(0);opacity:.8;}100%{transform:scale(4);opacity:0;}}

  .btn-close {
    background:linear-gradient(135deg,#0070ff,#00aaff); color:#fff;
    border:none; border-radius:50px; padding:14px 38px;
    font-family:'Orbitron',monospace; font-size:.78rem; font-weight:700;
    letter-spacing:.12em; cursor:pointer;
    box-shadow:0 4px 24px var(--blue-glow);
    transition:transform .15s,box-shadow .15s;
    animation:slideUp .6s .45s ease both;
  }
  .btn-close:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(0,120,255,.8);}

  #toast {
    position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px);
    background:rgba(20,60,20,.9); border:1px solid rgba(0,200,80,.4);
    color:#80ffb0; padding:12px 28px; border-radius:50px;
    font-family:'Orbitron',monospace; font-size:.7rem; letter-spacing:.1em;
    opacity:0; transition:opacity .3s,transform .3s; pointer-events:none; z-index:2000;
    backdrop-filter:blur(8px);
  }
  #toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
  #toast.error-toast { background:rgba(60,10,10,.9); border-color:rgba(255,80,80,.4); color:#ff9090; }

  .confirmed-badge {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,rgba(0,180,80,.2),rgba(0,100,60,.1));
    border:1px solid rgba(0,200,100,.35); border-radius:50px;
    padding:12px 24px; color:#60ffaa;
    font-family:'Orbitron',monospace; font-size:.78rem; font-weight:700;
    letter-spacing:.1em;
  }

  .btn-no.is-escaping {
    position: fixed !important;
    transition: left .35s cubic-bezier(.22,.61,.36,1), top .35s cubic-bezier(.22,.61,.36,1) !important;
    z-index: 500;
  }

  /* ─── RESPONSIVE ─── */
  @media (max-width: 960px) {
    .layout {
      flex-direction: column;
      align-items: center;
    }
    .page { flex: none; max-width: 620px; width: 100%; }
    .schedule-panel {
      flex: none; max-width: 620px; width: 100%;
      position: static;
    }
  }
</style>
</head>
<body>

<div id="stars"></div>
<div class="orb orb1"></div><div class="orb orb2"></div><div class="orb orb3"></div>

<canvas class="confetti-canvas" id="confettiCanvas"></canvas>

<div id="celebration">
  <div class="rings">
    <div class="ring" style="--s:100px;--t:2s;animation-delay:0s"></div>
    <div class="ring" style="--s:100px;--t:2s;animation-delay:.7s"></div>
    <div class="ring" style="--s:100px;--t:2s;animation-delay:1.4s"></div>
  </div>
  <?= domeSVG('celebrate-dome') ?>
  <div class="celebrate-name" id="celebrateName"></div>
  <div class="celebrate-title">✦ Confirmado! ✦</div>
  <div class="celebrate-sub">Sua presença foi registrada com sucesso.<br>Nos vemos no <strong>SINTEC 2.0</strong>!</div>
  <div class="celebrate-date">📅 19 a 23 de Outubro &nbsp;·&nbsp; Semana Toda</div>
  <button class="btn-close" onclick="closeCelebration()">Incrível! ✓</button>
</div>

<div id="toast"></div>

<!-- TWO-COLUMN LAYOUT -->
<div class="layout">

  <!-- ─── LEFT: INVITE ─── -->
  <div class="page">

    <div class="dome-wrap"><?= domeSVG('') ?></div>
    <div class="brand">SIN<span style="color:#fff">TEC</span></div>
    <div class="brand-version">2.0</div>
    <div class="tagline">Semana da Tecnologia</div>

    <h1>Convite Especial</h1>

    <div class="glass">
      <p class="intro">Temos o prazer de convidar você para a <strong style="color:#fff">SINTEC 2.0</strong>, um evento dedicado à apresentação de projetos, ideias e tecnologias desenvolvidas pelos nossos instrutores e alunos.</p>
    </div>

    <div class="info-row">
      <div class="info-card">
        <div class="info-label">📅 Período</div>
        <div class="info-value">19 – 23 Out</div>
        <div class="info-sub">Semana completa</div>
      </div>
      <div class="info-card">
        <div class="info-label">🕐 Horário</div>
        <div class="info-value">Tarde &amp; Noite</div>
        <div class="info-sub">Múltiplos períodos</div>
      </div>
    </div>

    <div class="format-box">
      <div class="format-title">⚙ Formato do Evento</div>
      <div class="format-text">
        Apresentações em <strong>formato rotativo</strong> — 20 minutos cada, repetidas ao longo do evento para que todos possam acompanhar todos os projetos.<br><br>
        <strong>Apresentadores:</strong> Instrutores da instituição
      </div>
    </div>

    <div class="deadline"><strong>Confirmação até 31 de março.</strong> Garanta sua participação.</div>

    <div class="rsvp-box" id="rsvpSection">
      <div class="rsvp-question">Confirme sua presença abaixo 👇</div>

      <div class="input-wrap">
        <label for="guestName">✦ Seu nome completo</label>
        <input
          type="text"
          id="guestName"
          class="name-input"
          placeholder="Digite seu nome..."
          maxlength="80"
          autocomplete="off"
        />
        <span class="input-error" id="nameError">Por favor, informe seu nome antes de confirmar.</span>
      </div>

      <div class="btn-row">
        <button class="btn btn-yes" id="btnYes" onclick="confirmPresence()">✓ Confirmar presença</button>
        <button class="btn btn-no" id="btnNo" onclick="tryDecline(event)">✕ Não posso ir</button>
      </div>
    </div>

    <div class="declined-msg" id="declinedMsg">
      <div class="declined-icon">😔</div>
      <div class="declined-text">Sentiremos sua falta!<br><strong>Esperamos te ver em futuros eventos.</strong></div>
      <button class="btn btn-yes" style="max-width:230px;margin-top:10px;" onclick="resetRSVP()">↩ Mudar resposta</button>
    </div>

  </div><!-- /page -->

  <!-- ─── RIGHT: SCHEDULE ─── -->
  <div class="schedule-panel">

    <div class="schedule-title">📋 Programação</div>

    <!-- Continuous badge -->
    <div class="continuous-badge">
      <span class="cb-icon">🖥️</span>
      <span><strong style="color:#fff">Durante toda a semana</strong> — Museu da Tecnologia: exposição, linha do tempo e QR Codes interativos.</span>
    </div>

    <!-- Segunda -->
    <div class="sched-day open" id="day-seg">
      <div class="sched-day-header" onclick="toggleDay('day-seg')">
        <div class="sched-day-dot dot-seg"></div>
        <div class="sched-day-label">Segunda-feira</div>
        <div class="sched-day-date">19/10</div>
        <div class="sched-chevron">▼</div>
      </div>
      <div class="sched-items">
        <div class="sched-item">
          <div class="sched-period period-manha">ABERTURA</div>
          <div class="sched-desc">Abertura oficial, apresentação cultural e minipalestras com profissionais de TI.</div>
        </div>
      </div>
    </div>

    <!-- Terça -->
    <div class="sched-day" id="day-ter">
      <div class="sched-day-header" onclick="toggleDay('day-ter')">
        <div class="sched-day-dot dot-ter"></div>
        <div class="sched-day-label">Terça-feira</div>
        <div class="sched-day-date">20/10</div>
        <div class="sched-chevron">▼</div>
      </div>
      <div class="sched-items">
        <div class="sched-item">
          <div class="sched-period period-tarde">TARDE</div>
          <div class="sched-desc">Maratona de Informática — Passa ou Repassa em competição em grupos.</div>
        </div>
        <div class="sched-item">
          <div class="sched-period period-noite">NOITE</div>
          <div class="sched-desc">Ideathon — Maratona de ideias: criação de soluções, pitch e premiação.</div>
        </div>
      </div>
    </div>

    <!-- Quarta -->
    <div class="sched-day" id="day-qua">
      <div class="sched-day-header" onclick="toggleDay('day-qua')">
        <div class="sched-day-dot dot-qua"></div>
        <div class="sched-day-label">Quarta-feira</div>
        <div class="sched-day-date">21/10</div>
        <div class="sched-chevron">▼</div>
      </div>
      <div class="sched-items">
        <div class="sched-item">
          <div class="sched-period period-tarde">TARDE</div>
          <div class="sched-desc">Campeonato de jogos eletrônicos e de tabuleiro.</div>
        </div>
        <div class="sched-item">
          <div class="sched-period period-noite">NOITE</div>
          <div class="sched-desc">Painel com profissionais de mercado, debate e networking.</div>
        </div>
      </div>
    </div>

    <!-- Quinta -->
    <div class="sched-day" id="day-qui">
      <div class="sched-day-header" onclick="toggleDay('day-qui')">
        <div class="sched-day-dot dot-qui"></div>
        <div class="sched-day-label">Quinta-feira</div>
        <div class="sched-day-date">Último dia</div>
        <div class="sched-chevron">▼</div>
      </div>
      <div class="sched-items">
        <div class="sched-item">
          <div class="sched-period period-tarde">DIA TODO</div>
          <div class="sched-desc">Feira de tecnologia, exposição de projetos e oficinas temáticas.</div>
        </div>
      </div>
    </div>

  </div><!-- /schedule-panel -->

</div><!-- /layout -->

<script>
(function(){
  const c = document.getElementById('stars');
  for(let i=0;i<130;i++){
    const s = document.createElement('div');
    s.className='star';
    const sz = Math.random()*2.5+.5;
    s.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--d:${(Math.random()*3+1).toFixed(1)}s;animation-delay:${(Math.random()*4).toFixed(1)}s;opacity:${(Math.random()*.7+.1).toFixed(2)}`;
    c.appendChild(s);
  }
})();

function toggleDay(id){
  const el = document.getElementById(id);
  el.classList.toggle('open');
}

const canvas = document.getElementById('confettiCanvas');
const ctx = canvas.getContext('2d');
let pieces = [], raf;
function resizeCanvas(){ canvas.width=innerWidth; canvas.height=innerHeight; }
resizeCanvas(); addEventListener('resize',resizeCanvas);

function spawnConfetti(){
  const colors=['#0080ff','#00c8ff','#ff9500','#fff','#ffd700','#ff4080','#00ff99','#ff70c0'];
  for(let i=0;i<220;i++) pieces.push({
    x:Math.random()*canvas.width, y:-20,
    w:Math.random()*11+4, h:Math.random()*5+2,
    color:colors[Math.floor(Math.random()*colors.length)],
    vx:(Math.random()-.5)*4.5, vy:Math.random()*5+3,
    angle:Math.random()*360, spin:(Math.random()-.5)*9, opacity:1
  });
}
function drawConfetti(){
  ctx.clearRect(0,0,canvas.width,canvas.height);
  pieces = pieces.filter(p=>p.opacity>.01);
  pieces.forEach(p=>{
    p.x+=p.vx; p.y+=p.vy; p.angle+=p.spin; p.vy+=.12;
    if(p.y>canvas.height*.72) p.opacity-=.022;
    ctx.save();
    ctx.translate(p.x,p.y); ctx.rotate(p.angle*Math.PI/180);
    ctx.globalAlpha=p.opacity; ctx.fillStyle=p.color;
    ctx.fillRect(-p.w/2,-p.h/2,p.w,p.h);
    ctx.restore();
  });
  if(pieces.length>0) raf=requestAnimationFrame(drawConfetti);
  else ctx.clearRect(0,0,canvas.width,canvas.height);
}

let toastTimer;
function showToast(msg,isError=false){
  const t=document.getElementById('toast');
  t.textContent=msg;
  t.className='show'+(isError?' error-toast':'');
  clearTimeout(toastTimer);
  toastTimer=setTimeout(()=>t.className='',3000);
}

function getNameValue(){ return document.getElementById('guestName').value.trim(); }
function showNameError(show){
  document.getElementById('nameError').classList.toggle('show',show);
  document.getElementById('guestName').style.borderColor = show ? '#ff6060' : '';
}

async function confirmPresence(){
  const name = getNameValue();
  if(!name){ showNameError(true); document.getElementById('guestName').focus(); return; }
  showNameError(false);
  const btn = document.getElementById('btnYes');
  btn.disabled=true; btn.textContent='Enviando...';
  try {
    const fd = new FormData();
    fd.append('action','confirm');
    fd.append('name', name);
    const res = await fetch('rsvp.php', { method:'POST', body:fd });
    const data = await res.json();
    if(data.success){
      document.getElementById('celebrateName').textContent = '✨ Olá, ' + name + '!';
      spawnConfetti(); spawnConfetti(); drawConfetti();
      document.getElementById('celebration').classList.add('active');
    } else {
      showToast(data.message || 'Erro ao salvar. Tente novamente.', true);
      btn.disabled=false; btn.textContent='✓ Confirmar presença';
    }
  } catch(e){
    showToast('Erro de conexão com o servidor.', true);
    btn.disabled=false; btn.textContent='✓ Confirmar presença';
  }
}

function closeCelebration(){
  document.getElementById('celebration').classList.remove('active');
  cancelAnimationFrame(raf); ctx.clearRect(0,0,canvas.width,canvas.height); pieces=[];
  const section = document.getElementById('rsvpSection');
  section.innerHTML = `<div style="display:flex;justify-content:center;">
    <div class="confirmed-badge">✓ Presença Confirmada &nbsp;·&nbsp; Até 19–23 de Outubro!</div>
  </div>`;
}

let declineClicks = 0;
let escaping = false;

function tryDecline(e){
  declineClicks++;
  if(declineClicks >= 5){
    stopEscaping();
    declineConfirmed();
    return;
  }
  escapeButton(e);
}

function escapeButton(e){
  const btn = document.getElementById('btnNo');
  if(escaping) return;
  escaping = true;
  const margin = 20;
  const bw = btn.offsetWidth, bh = btn.offsetHeight;
  const maxX = window.innerWidth - bw - margin;
  const maxY = window.innerHeight - bh - margin;
  const rect = btn.getBoundingClientRect();
  btn.classList.add('is-escaping');
  btn.style.left = rect.left + 'px';
  btn.style.top  = rect.top  + 'px';
  btn.style.width  = bw + 'px';
  btn.style.height = bh + 'px';
  btn.getBoundingClientRect();
  const newX = Math.min(Math.max(margin, Math.random() * maxX), maxX);
  const newY = Math.min(Math.max(margin, Math.random() * maxY), maxY);
  btn.style.left = newX + 'px';
  btn.style.top  = newY + 'px';
  setTimeout(()=>{ escaping = false; }, 380);
  const msgs = ['Ei, não foge não! 😏','Tenta pegar! 😂','Quase... 🤭','Mais um clique e desisto! 😅'];
  if(declineClicks <= msgs.length){ showToast(msgs[declineClicks-1]); }
}

function stopEscaping(){
  const btn = document.getElementById('btnNo');
  btn.classList.remove('is-escaping');
  btn.style.left=''; btn.style.top=''; btn.style.width=''; btn.style.height='';
  declineClicks=0; escaping=false;
}

async function declineConfirmed(){
  const name = getNameValue();
  if(name){
    try {
      const fd=new FormData(); fd.append('action','decline'); fd.append('name',name);
      await fetch('rsvp.php',{method:'POST',body:fd});
    } catch(_){}
  }
  document.getElementById('rsvpSection').style.display='none';
  document.getElementById('declinedMsg').style.display='flex';
}

function resetRSVP(){
  stopEscaping();
  document.getElementById('rsvpSection').style.display='block';
  document.getElementById('declinedMsg').style.display='none';
  const btn=document.getElementById('btnYes');
  btn.disabled=false; btn.textContent='✓ Confirmar presença';
}

document.getElementById('guestName').addEventListener('input', function(){
  if(this.value.trim()) showNameError(false);
});
document.getElementById('guestName').addEventListener('keydown', function(e){
  if(e.key==='Enter') confirmPresence();
});

function alignSchedule(){
  const panel = document.querySelector('.schedule-panel');
  const glass = document.querySelector('.glass');
  if(!panel || !glass) return;
  if(window.innerWidth <= 960){ panel.style.paddingTop = ''; return; }
  const layoutTop = document.querySelector('.layout').getBoundingClientRect().top + window.scrollY;
  const glassTop  = glass.getBoundingClientRect().top  + window.scrollY;
  panel.style.paddingTop = (glassTop - layoutTop) + 'px';
}
window.addEventListener('load', alignSchedule);
window.addEventListener('resize', alignSchedule);
</script>

</body>
</html>

<?php
function domeSVG(string $class): string {
  $c = $class ? " class=\"$class\"" : '';
  return <<<SVG
<svg{$c} viewBox="0 0 120 70" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <radialGradient id="dg" cx="50%" cy="30%" r="60%">
      <stop offset="0%" stop-color="#a0d8ff"/>
      <stop offset="50%" stop-color="#2090ff"/>
      <stop offset="100%" stop-color="#003080"/>
    </radialGradient>
  </defs>
  <ellipse cx="60" cy="70" rx="58" ry="8" fill="rgba(0,100,255,0.15)"/>
  <path d="M10 68 Q10 10 60 6 Q110 10 110 68 Z" fill="url(#dg)" opacity="0.9"/>
  <path d="M10 68 Q10 10 60 6 Q110 10 110 68 Z" fill="none" stroke="rgba(120,200,255,0.4)" stroke-width="1"/>
  <line x1="60" y1="6" x2="60" y2="68" stroke="rgba(120,200,255,0.25)" stroke-width="0.8"/>
  <line x1="35" y1="10" x2="35" y2="68" stroke="rgba(120,200,255,0.18)" stroke-width="0.8"/>
  <line x1="85" y1="10" x2="85" y2="68" stroke="rgba(120,200,255,0.18)" stroke-width="0.8"/>
  <path d="M13 45 Q60 42 107 45" stroke="rgba(120,200,255,0.25)" stroke-width="0.8" fill="none"/>
  <path d="M10 68 Q60 65 110 68" stroke="rgba(120,200,255,0.25)" stroke-width="0.8" fill="none"/>
  <path d="M18 28 Q60 22 102 28" stroke="rgba(120,200,255,0.25)" stroke-width="0.8" fill="none"/>
</svg>
SVG;
}
?>