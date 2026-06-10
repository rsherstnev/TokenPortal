-- Таблица сертификатов СКЗИ (синхронизация из внешней системы).
-- Использование:
--   mysql -u USER -p skzi_tokens < db/migrate_certificates.sql

SET NAMES utf8mb4;

USE skzi_tokens;

CREATE TABLE IF NOT EXISTS token_certificates (
    id              INT UNSIGNED    NOT NULL,
    user_id         INT UNSIGNED    NOT NULL,
    number          VARCHAR(100)    NOT NULL,
    start           DATE            NOT NULL,
    end             DATE            NOT NULL,
    CA              VARCHAR(150)    NOT NULL,
    key_valid_to    DATE            DEFAULT NULL,
    memo            VARCHAR(300)    NOT NULL,
    real_fio        VARCHAR(150)    NOT NULL,
    updated         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_token_certificates_user_id (user_id),
    CONSTRAINT fk_token_certificates_user_id
        FOREIGN KEY (user_id) REFERENCES token_users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
