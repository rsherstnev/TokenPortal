-- Добавляет признак «Токен нужен» в таблицу сотрудников (token_users).
-- По умолчанию токен нужен всем сотрудникам.
-- Использование:
--   mysql -u USER -p skzi_tokens < db/migrate_users_is_token_needed.sql

SET NAMES utf8mb4;

USE skzi_tokens;

ALTER TABLE token_users
    ADD COLUMN is_token_needed TINYINT(1) NOT NULL DEFAULT 1 AFTER is_fired,
    ADD KEY idx_token_users_is_token_needed (is_token_needed);
