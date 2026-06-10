-- Исправляет ошибочное имя таблицы tokens_certificates → token_certificates.
-- Запускать только если в БД есть таблица tokens_certificates.
-- Использование:
--   mysql -u USER -p skzi_tokens < db/migrate_tokens_certificates_to_token_certificates.sql

SET NAMES utf8mb4;

USE skzi_tokens;

RENAME TABLE tokens_certificates TO token_certificates;

ALTER TABLE token_certificates
    DROP FOREIGN KEY fk_tokens_certificates_user_id;

ALTER TABLE token_certificates
    RENAME INDEX idx_tokens_certificates_user_id TO idx_token_certificates_user_id;

ALTER TABLE token_certificates
    ADD CONSTRAINT fk_token_certificates_user_id
        FOREIGN KEY (user_id) REFERENCES token_users (id);
