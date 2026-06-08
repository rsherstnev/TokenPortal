-- Добавляет поле «Комментарий» в таблицу токенов.
-- Использование:
--   mysql -u USER -p skzi_tokens < db/migrate_tokens_comment.sql

SET NAMES utf8mb4;

USE skzi_tokens;

ALTER TABLE tokens
    ADD COLUMN comment TEXT NULL AFTER is_lost;
