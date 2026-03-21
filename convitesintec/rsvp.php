<?php
/**
 * SINTEC 2.0 — RSVP Backend
 * POST /rsvp.php  →  { success, message, action?, name? }
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db.php';

// ─── HELPERS ─────────────────────────────────────────────────
function jsonOut(bool $ok, string $msg, array $extra = []): never {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function sanitizeName(string $raw): string {
    $n = trim(strip_tags($raw));
    $n = preg_replace('/\s+/', ' ', $n);
    return mb_substr($n, 0, 80);
}

function getClientIp(): string {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    return trim(explode(',', $ip)[0]);
}

function alreadyConfirmed(string $name): bool {
    $st = getDB()->prepare(
        'SELECT id FROM rsvp_responses
         WHERE action = "confirm" AND LOWER(name) = LOWER(:n) LIMIT 1'
    );
    $st->execute([':n' => $name]);
    return (bool) $st->fetch();
}

function saveRSVP(string $name, string $action, string $ip): void {
    $st = getDB()->prepare(
        'INSERT INTO rsvp_responses (name, action, ip)
         VALUES (:name, :action, :ip)'
    );
    $st->execute([':name' => $name, ':action' => $action, ':ip' => $ip]);
}

// ─── GATE ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonOut(false, 'Método não permitido.');
}

$action = trim($_POST['action'] ?? '');
$name   = sanitizeName($_POST['name'] ?? '');
$ip     = getClientIp();

if (!in_array($action, ['confirm', 'decline'], true)) jsonOut(false, 'Ação inválida.');
if (mb_strlen($name) < 2) jsonOut(false, 'Informe seu nome completo.');

// ─── SALVAR ───────────────────────────────────────────────────
try {
    if ($action === 'confirm' && alreadyConfirmed($name)) {
        jsonOut(true, 'Presença já confirmada!');
    }
    saveRSVP($name, $action, $ip);
    jsonOut(true, $action === 'confirm' ? 'Presença confirmada!' : 'Resposta registrada.', [
        'action' => $action,
        'name'   => $name,
    ]);
} catch (PDOException $e) {
    error_log('[SINTEC] DB: ' . $e->getMessage());
    http_response_code(500);
    jsonOut(false, 'Erro interno. Tente novamente.');
}
