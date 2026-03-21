<?php
/**
 * SINTEC 2.0 — Conexão PDO centralizada
 * Inclua este arquivo em todos os scripts que precisam do banco.
 */

define('DB_HOST', 'localhost');   // ← seu host MySQL (geralmente 'localhost')
define('DB_NAME', 'u822474892_sintec');
define('DB_USER', 'u822474892_sintec');        // ← seu usuário MySQL
define('DB_PASS', 'Sintec4848');   // ← sua senha MySQL
define('DB_PORT', 3306);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST, DB_PORT, DB_NAME
        );
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
