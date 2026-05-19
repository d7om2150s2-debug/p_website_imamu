
CREATE DATABASE IF NOT EXISTS vision2030_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vision2030_db;

-- ── USERS TABLE ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(120)  NOT NULL,
  username    VARCHAR(60)   NOT NULL UNIQUE,
  email       VARCHAR(160)  NOT NULL UNIQUE,
  password    VARCHAR(255)  NOT NULL,          -- bcrypt hash
  gender      ENUM('male','female','prefer_not') NOT NULL DEFAULT 'prefer_not',
  nationality VARCHAR(80)   NOT NULL DEFAULT '',
  created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── FEEDBACK TABLE ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS feedback (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT           NULL,              -- NULL = guest
  name        VARCHAR(120)  NOT NULL,
  email       VARCHAR(160)  NOT NULL,
  topic       VARCHAR(60)   NOT NULL,
  message     TEXT          NOT NULL,
  created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
