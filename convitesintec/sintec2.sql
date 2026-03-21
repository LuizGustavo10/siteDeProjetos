-- ============================================================
--  SINTEC 2.0 — Schema do Banco de Dados
--  MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS sintec2
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sintec2;

-- ─── RESPOSTAS DOS CONVIDADOS ────────────────────────────────
CREATE TABLE IF NOT EXISTS rsvp_responses (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(80)     NOT NULL,
    action      ENUM('confirm','decline') NOT NULL,
    ip          VARCHAR(45)     NOT NULL DEFAULT '',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_action     (action),
    INDEX idx_created_at (created_at),
    INDEX idx_name       (name(40))
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Respostas RSVP — SINTEC 2.0';

-- ─── ADMINISTRADORES ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    username     VARCHAR(40)   NOT NULL UNIQUE,
    password     VARCHAR(255)  NOT NULL,   -- bcrypt hash
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuários do painel admin — SINTEC 2.0';

-- ─── ADMIN PADRÃO: admin / sintec2025 ────────────────────────
-- Gere um novo hash em PHP com: password_hash('suasenha', PASSWORD_BCRYPT)
INSERT INTO admins (username, password) VALUES
  ('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;
-- senha padrão acima: "password"  ← TROQUE ANTES DE SUBIR!
