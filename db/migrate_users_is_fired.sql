-- Добавляет признак «Уволен» в таблицу сотрудников (users).
-- Использование:
--   mysql -u USER -p skzi_tokens < db/migrate_users_is_fired.sql

SET NAMES utf8mb4;

USE skzi_tokens;

ALTER TABLE users
    ADD COLUMN is_fired TINYINT(1) NOT NULL DEFAULT 0 AFTER not_print,
    ADD KEY idx_users_is_fired (is_fired);
