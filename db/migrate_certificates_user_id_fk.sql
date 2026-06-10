-- Приводит certificates.user_id к INT UNSIGNED и добавляет FK на token_users.id.
-- Для БД, где таблица уже создана со старой схемой (user_id VARCHAR).
-- Использование:
--   mysql -u USER -p skzi_tokens < db/migrate_certificates_user_id_fk.sql

SET NAMES utf8mb4;

USE skzi_tokens;

ALTER TABLE certificates
    DROP INDEX idx_certificates_user_id,
    MODIFY user_id INT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_certificates_user_id
        FOREIGN KEY (user_id) REFERENCES token_users (id);
