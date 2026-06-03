-- Миграция token_models, tokens, token_transfers: UUID -> INT UNSIGNED AUTO_INCREMENT.
-- ВНИМАНИЕ: удаляет все данные в этих таблицах. Для продакшена с UUID нужна
-- отдельная процедура сопоставления старых и новых ID.

USE skzi_tokens;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS token_transfers;
DROP TABLE IF EXISTS tokens;
DROP TABLE IF EXISTS token_models;

CREATE TABLE token_models (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name        TEXT          NOT NULL,
    created_at  DATETIME      NULL,
    updated_at  DATETIME      NULL,
    deleted_at  DATETIME      NULL,
    PRIMARY KEY (id),
    KEY idx_token_models_deleted (deleted_at),
    KEY idx_token_models_name (name(128))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tokens (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    token_model_id  INT UNSIGNED  NOT NULL,
    serial_number   TEXT          NOT NULL,
    employee_id     INT           NULL,
    is_broken       TINYINT(1)    NOT NULL DEFAULT 0,
    is_lost         TINYINT(1)    NOT NULL DEFAULT 0,
    created_at      DATETIME      NULL,
    updated_at      DATETIME      NULL,
    deleted_at      DATETIME      NULL,
    PRIMARY KEY (id),
    KEY idx_tokens_model        (token_model_id),
    KEY idx_tokens_employee     (employee_id),
    KEY idx_tokens_deleted      (deleted_at),
    KEY idx_tokens_model_serial (token_model_id, serial_number(128))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE token_transfers (
    id                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    token_id          INT UNSIGNED  NULL,
    from_employee_id  INT           NULL,
    to_employee_id    INT           NULL,
    comment           TEXT          NULL,
    transferred_at    DATETIME      NULL,
    created_at        DATETIME      NULL,
    PRIMARY KEY (id),
    KEY idx_transfers_token (token_id),
    KEY idx_transfers_date  (transferred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
