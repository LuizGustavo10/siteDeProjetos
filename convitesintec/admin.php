<?php
/**
 * SINTEC 2.0 — Painel Admin com Login
 * /admin.php
 */

session_start();
require_once __DIR__ . '/db.php';

// ─── AUTH HELPERS ─────────────────────────────────────────────
function isLogged(): bool {
    return !empty($_SESSION['admin_id']);
}

function attemptLogin(string $user, string $pass): bool {
    $st = getDB()->prepare('SELECT id, password FROM admins WHERE username = :u LIMIT 1');
    $st->execute([':u' => $user]);
    $row = $st->fetch();
    if ($row && password_verify($pass, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $row['id'];
        $_SESSION['admin_user'] = $user;
        return true;
    }
    return false;
}

// ─── LOGOUT ──────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ─── LOGIN POST ───────────────────────────────────────────────
$loginError = '';
if (!isLogged() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (!attemptLogin($u, $p)) {
        $loginError = 'Usuário ou senha incorretos.';
    } else {
        header('Location: admin.php');
        exit;
    }
}

// ─── DADOS (só se logado) ─────────────────────────────────────
$stats = ['total' => 0, 'confirmed' => 0, 'declined' => 0];
$rows  = [];
$search = '';
$filterAction = '';
$page  = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

if (isLogged()) {
    $db = getDB();
    $search       = trim($_GET['q'] ?? '');
    $filterAction = $_GET['filter'] ?? '';

    // Stats
    $st = $db->query('SELECT action, COUNT(*) AS n FROM rsvp_responses GROUP BY action');
    foreach ($st->fetchAll() as $r) {
        $stats['total'] += $r['n'];
        if ($r['action'] === 'confirm')  $stats['confirmed'] = $r['n'];
        if ($r['action'] === 'decline')  $stats['declined']  = $r['n'];
    }

    // Build WHERE
    $where = '1=1';
    $params = [];
    if ($search !== '') {
        $where .= ' AND name LIKE :q';
        $params[':q'] = '%' . $search . '%';
    }
    if (in_array($filterAction, ['confirm','decline'], true)) {
        $where .= ' AND action = :act';
        $params[':act'] = $filterAction;
    }

    // Total filtered
    $stCount = $db->prepare("SELECT COUNT(*) FROM rsvp_responses WHERE $where");
    $stCount->execute($params);
    $totalFiltered = (int) $stCount->fetchColumn();
    $totalPages = max(1, (int) ceil($totalFiltered / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    // Rows
    $stRows = $db->prepare(
        "SELECT id, name, action, ip, created_at
         FROM rsvp_responses
         WHERE $where
         ORDER BY created_at DESC
         LIMIT :lim OFFSET :off"
    );
    foreach ($params as $k => $v) $stRows->bindValue($k, $v);
    $stRows->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stRows->bindValue(':off', $offset,  PDO::PARAM_INT);
    $stRows->execute();
    $rows = $stRows->fetchAll();

    // CSV download
    if (isset($_GET['download'])) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sintec2_rsvp_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($out, ['ID','Nome','Resposta','IP','Data/Hora']);
        $stAll = $db->prepare("SELECT id,name,action,ip,created_at FROM rsvp_responses WHERE $where ORDER BY created_at DESC");
        $stAll->execute($params);
        foreach ($stAll->fetchAll() as $r) {
            fputcsv($out, [
                $r['id'], $r['name'],
                $r['action'] === 'confirm' ? 'Confirmado' : 'Recusou',
                $r['ip'],
                $r['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}

// ─── HTML ─────────────────────────────────────────────────────
function esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function qStr(array $extra = []): string {
    $p = array_merge($_GET, $extra);
    unset($p['logout'], $p['download']);
    return http_build_query($p);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SINTEC 2.0 — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

:root {
  --dark:   #020b1a;
  --dark2:  #05152e;
  --blue:   #0070ff;
  --cyan:   #00c8ff;
  --orange: #ff9500;
  --green:  #00e07a;
  --red:    #ff5050;
  --glass:  rgba(255,255,255,0.04);
  --border: rgba(100,180,255,0.16);
  --text:   rgba(200,230,255,0.88);
  --dim:    rgba(140,180,230,0.55);
}

html, body { min-height:100vh; background:var(--dark); color:var(--text); font-family:'Exo 2',sans-serif; }

/* ── STARS ── */
#stars { position:fixed; inset:0; pointer-events:none; z-index:0; overflow:hidden; }
.star { position:absolute; border-radius:50%; background:#fff; animation:twk var(--d) ease-in-out infinite alternate; }
@keyframes twk { from{opacity:.08;} to{opacity:.9;} }
.orb { position:fixed; border-radius:50%; filter:blur(90px); pointer-events:none; z-index:0; }
.orb1 { width:380px;height:380px;background:rgba(0,50,180,.28);top:-120px;left:-80px; }
.orb2 { width:260px;height:260px;background:rgba(255,140,0,.10);bottom:-80px;right:-60px; }

/* ── LAYOUT ── */
.wrap { position:relative; z-index:10; min-height:100vh; }

/* ════ LOGIN PAGE ════════════════════════════════════════ */
.login-page {
  display:flex; align-items:center; justify-content:center;
  min-height:100vh; padding:24px;
}
.login-card {
  width:100%; max-width:400px;
  background: rgba(5,21,46,.7);
  border:1px solid var(--border);
  border-radius:24px; padding:40px 36px;
  backdrop-filter:blur(16px);
  box-shadow:0 24px 80px rgba(0,0,30,.6), inset 0 1px 0 rgba(255,255,255,.07);
}
.login-logo { text-align:center; margin-bottom:28px; }
.login-logo .brand {
  font-family:'Orbitron',monospace; font-size:1.9rem; font-weight:900;
  color:#fff; text-shadow:0 0 28px rgba(0,120,255,.7); letter-spacing:.1em;
}
.login-logo .ver { color:var(--orange); font-size:1.1rem; }
.login-logo .sub {
  font-family:'Orbitron',monospace; font-size:.52rem; letter-spacing:.35em;
  color:var(--dim); text-transform:uppercase; margin-top:6px;
}
.form-group { margin-bottom:18px; }
.form-group label {
  display:block; font-family:'Orbitron',monospace; font-size:.54rem;
  letter-spacing:.28em; color:var(--cyan); text-transform:uppercase; margin-bottom:8px;
}
.form-input {
  width:100%; padding:13px 18px;
  background:rgba(0,60,160,.18); border:1px solid rgba(0,150,255,.28);
  border-radius:50px; color:#fff;
  font-family:'Exo 2',sans-serif; font-size:.95rem; font-weight:600;
  outline:none; transition:border-color .2s, box-shadow .2s;
}
.form-input::placeholder { color:rgba(120,170,220,.4); font-weight:400; }
.form-input:focus {
  border-color:var(--cyan);
  box-shadow:0 0 0 3px rgba(0,200,255,.14), 0 0 18px rgba(0,200,255,.08);
}
.btn-login {
  width:100%; padding:14px; border:none; border-radius:50px;
  background:linear-gradient(135deg,#0070ff,#00aaff); color:#fff;
  font-family:'Orbitron',monospace; font-size:.78rem; font-weight:700;
  letter-spacing:.12em; cursor:pointer;
  box-shadow:0 4px 24px rgba(0,100,255,.45);
  transition:transform .15s, box-shadow .15s; margin-top:6px;
}
.btn-login:hover { transform:translateY(-2px); box-shadow:0 8px 32px rgba(0,100,255,.7); }
.login-error {
  background:rgba(255,50,50,.12); border:1px solid rgba(255,80,80,.3);
  border-radius:12px; padding:10px 16px; margin-bottom:16px;
  color:#ff9090; font-size:.85rem; text-align:center;
}

/* ════ ADMIN PANEL ═══════════════════════════════════════ */
.topbar {
  position:sticky; top:0; z-index:100;
  background:rgba(2,11,26,.88); backdrop-filter:blur(14px);
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
  padding:14px 28px; gap:16px;
}
.topbar-brand { font-family:'Orbitron',monospace; font-weight:900; font-size:1rem; color:#fff; letter-spacing:.1em; }
.topbar-brand span { color:var(--orange); }
.topbar-user { font-size:.82rem; color:var(--dim); }
.btn-logout {
  padding:8px 20px; border:1px solid rgba(255,80,80,.3); border-radius:50px;
  background:rgba(255,50,50,.08); color:#ff9090;
  font-family:'Orbitron',monospace; font-size:.62rem; letter-spacing:.1em;
  cursor:pointer; text-decoration:none; transition:background .2s;
}
.btn-logout:hover { background:rgba(255,50,50,.18); }

.main { padding:28px 28px 50px; max-width:1200px; margin:0 auto; }

/* Stats */
.stats { display:flex; gap:14px; margin-bottom:26px; flex-wrap:wrap; }
.stat-card {
  flex:1; min-width:130px;
  background:var(--glass); border:1px solid var(--border);
  border-radius:16px; padding:18px 22px;
  backdrop-filter:blur(8px);
}
.stat-n { font-size:2.2rem; font-weight:900; color:#fff; line-height:1; }
.stat-l { font-family:'Orbitron',monospace; font-size:.52rem; letter-spacing:.28em; color:var(--dim); text-transform:uppercase; margin-top:5px; }
.stat-card.green .stat-n { color:var(--green); }
.stat-card.red   .stat-n { color:var(--red); }

/* Toolbar */
.toolbar {
  display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:20px;
}
.search-wrap { position:relative; flex:1; min-width:200px; }
.search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--dim); font-size:.9rem; pointer-events:none; }
.search-input {
  width:100%; padding:11px 14px 11px 38px;
  background:rgba(0,60,160,.15); border:1px solid var(--border);
  border-radius:50px; color:#fff;
  font-family:'Exo 2',sans-serif; font-size:.9rem; outline:none;
  transition:border-color .2s;
}
.search-input:focus { border-color:var(--cyan); }
.search-input::placeholder { color:var(--dim); }

.filter-btn {
  padding:10px 18px; border-radius:50px; border:1px solid var(--border);
  background:var(--glass); color:var(--text); cursor:pointer;
  font-family:'Exo 2',sans-serif; font-size:.85rem; text-decoration:none;
  transition:background .2s, border-color .2s; white-space:nowrap;
}
.filter-btn:hover { background:rgba(255,255,255,.08); }
.filter-btn.active-confirm { border-color:var(--green); color:var(--green); background:rgba(0,220,120,.08); }
.filter-btn.active-decline { border-color:var(--red);   color:var(--red);   background:rgba(255,80,80,.08); }
.filter-btn.active-all { border-color:var(--cyan); color:var(--cyan); background:rgba(0,200,255,.08); }

.btn-dl {
  padding:10px 20px; border-radius:50px; border:none;
  background:linear-gradient(135deg,#0070ff,#00aaff); color:#fff;
  font-family:'Orbitron',monospace; font-size:.62rem; letter-spacing:.1em;
  cursor:pointer; text-decoration:none; white-space:nowrap;
  box-shadow:0 2px 14px rgba(0,100,255,.35);
  transition:transform .15s, box-shadow .15s;
}
.btn-dl:hover { transform:translateY(-2px); box-shadow:0 6px 22px rgba(0,100,255,.55); }

/* Table */
.table-wrap {
  background:var(--glass); border:1px solid var(--border);
  border-radius:18px; overflow:hidden; backdrop-filter:blur(8px);
}
table { width:100%; border-collapse:collapse; }
thead tr { border-bottom:1px solid var(--border); }
th {
  font-family:'Orbitron',monospace; font-size:.52rem; letter-spacing:.25em;
  color:var(--dim); text-transform:uppercase; text-align:left;
  padding:14px 18px; white-space:nowrap;
}
td { padding:13px 18px; border-bottom:1px solid rgba(255,255,255,.04); font-size:.9rem; vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,.03); }

.badge {
  display:inline-flex; align-items:center; gap:5px;
  padding:4px 14px; border-radius:50px; font-size:.74rem; font-weight:700;
}
.badge.confirm { background:rgba(0,220,120,.12); color:var(--green); border:1px solid rgba(0,220,120,.28); }
.badge.decline { background:rgba(255,80,80,.1);  color:var(--red);   border:1px solid rgba(255,80,80,.24); }

.name-cell { font-weight:700; color:#fff; }
.ip-cell   { font-family:monospace; font-size:.78rem; color:var(--dim); }
.date-cell { color:var(--dim); font-size:.82rem; white-space:nowrap; }
.id-cell   { color:var(--dim); font-size:.8rem; }

.empty-row td { text-align:center; padding:40px; color:var(--dim); font-style:italic; }

/* Pagination */
.pagination { display:flex; gap:8px; justify-content:center; margin-top:22px; flex-wrap:wrap; }
.page-btn {
  min-width:38px; height:38px; display:flex; align-items:center; justify-content:center;
  border-radius:10px; border:1px solid var(--border); background:var(--glass);
  color:var(--text); text-decoration:none; font-size:.85rem; font-weight:600;
  transition:background .2s, border-color .2s;
}
.page-btn:hover { background:rgba(255,255,255,.08); }
.page-btn.active { background:rgba(0,112,255,.3); border-color:var(--blue); color:#fff; }
.page-btn.disabled { opacity:.3; pointer-events:none; }

.page-info { text-align:center; color:var(--dim); font-size:.8rem; margin-top:10px; }
</style>
</head>
<body>
<div id="stars"></div>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="wrap">

<?php if (!isLogged()): ?>
<!-- ════ LOGIN ════════════════════════════════════════════════ -->
<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <div class="brand">SIN<span style="color:#fff">TEC</span> <span class="ver">2.0</span></div>
      <div class="sub">Painel Administrativo</div>
    </div>

    <?php if ($loginError): ?>
      <div class="login-error">⚠ <?= esc($loginError) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label for="username">Usuário</label>
        <input class="form-input" type="text" id="username" name="username"
               placeholder="seu usuário" required autofocus
               value="<?= esc($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Senha</label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="••••••••" required>
      </div>
      <button class="btn-login" type="submit">Entrar →</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ════ PAINEL ═══════════════════════════════════════════════ -->
<div class="topbar">
  <div class="topbar-brand">SIN<span>TEC</span> 2.0 · Admin</div>
  <div style="display:flex;align-items:center;gap:14px;">
    <span class="topbar-user">👤 <?= esc($_SESSION['admin_user']) ?></span>
    <a class="btn-logout" href="?logout=1">Sair</a>
  </div>
</div>

<div class="main">

  <!-- Stats -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-n"><?= $stats['total'] ?></div>
      <div class="stat-l">Total Respostas</div>
    </div>
    <div class="stat-card green">
      <div class="stat-n"><?= $stats['confirmed'] ?></div>
      <div class="stat-l">✓ Confirmados</div>
    </div>
    <div class="stat-card red">
      <div class="stat-n"><?= $stats['declined'] ?></div>
      <div class="stat-l">✕ Recusaram</div>
    </div>
  </div>

  <!-- Toolbar -->
  <form method="GET" class="toolbar" id="filterForm">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input class="search-input" type="text" name="q"
             placeholder="Buscar por nome..."
             value="<?= esc($search) ?>"
             oninput="debounceSubmit()">
    </div>

    <?php
      $noFilter  = $filterAction === '';
      $isConfirm = $filterAction === 'confirm';
      $isDecline = $filterAction === 'decline';
    ?>
    <a class="filter-btn <?= $noFilter ? 'active-all' : '' ?>"
       href="?<?= esc(qStr(['filter'=>'','page'=>1])) ?>">Todos</a>
    <a class="filter-btn <?= $isConfirm ? 'active-confirm' : '' ?>"
       href="?<?= esc(qStr(['filter'=>'confirm','page'=>1])) ?>">✓ Confirmados</a>
    <a class="filter-btn <?= $isDecline ? 'active-decline' : '' ?>"
       href="?<?= esc(qStr(['filter'=>'decline','page'=>1])) ?>">✕ Recusaram</a>

    <input type="hidden" name="filter" value="<?= esc($filterAction) ?>">
    <input type="hidden" name="page"   value="1">

    <a class="btn-dl" href="?<?= esc(qStr(['download'=>1])) ?>">⬇ CSV</a>
  </form>

  <!-- Table -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Resposta</th>
          <th>IP</th>
          <th>Data / Hora</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr class="empty-row"><td colspan="5">Nenhum registro encontrado.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td class="id-cell"><?= $r['id'] ?></td>
            <td class="name-cell"><?= esc($r['name']) ?></td>
            <td>
              <span class="badge <?= $r['action'] ?>">
                <?= $r['action'] === 'confirm' ? '✓ Confirmado' : '✕ Recusou' ?>
              </span>
            </td>
            <td class="ip-cell"><?= esc($r['ip']) ?></td>
            <td class="date-cell"><?= date('d/m/Y H:i:s', strtotime($r['created_at'])) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <a class="page-btn <?= $page<=1?'disabled':'' ?>" href="?<?= esc(qStr(['page'=>$page-1])) ?>">‹</a>
    <?php
      $range = 2;
      for ($p = max(1,$page-$range); $p <= min($totalPages,$page+$range); $p++):
    ?>
      <a class="page-btn <?= $p===$page?'active':'' ?>"
         href="?<?= esc(qStr(['page'=>$p])) ?>"><?= $p ?></a>
    <?php endfor; ?>
    <a class="page-btn <?= $page>=$totalPages?'disabled':'' ?>" href="?<?= esc(qStr(['page'=>$page+1])) ?>">›</a>
  </div>
  <div class="page-info">Página <?= $page ?> de <?= $totalPages ?> · <?= $totalFiltered ?> registros</div>
  <?php endif; ?>

</div><!-- /main -->
<?php endif; ?>

</div><!-- /wrap -->

<script>
// Stars
(function(){
  const c=document.getElementById('stars');
  for(let i=0;i<100;i++){
    const s=document.createElement('div');
    s.className='star';
    const z=Math.random()*2.2+.4;
    s.style.cssText=`width:${z}px;height:${z}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--d:${(Math.random()*3+1).toFixed(1)}s;animation-delay:${(Math.random()*4).toFixed(1)}s`;
    c.appendChild(s);
  }
})();

// Debounce search
let _t;
function debounceSubmit(){
  clearTimeout(_t);
  _t = setTimeout(()=>document.getElementById('filterForm').submit(), 450);
}
</script>
</body>
</html>
