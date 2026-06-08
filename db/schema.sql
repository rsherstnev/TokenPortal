SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS skzi_tokens
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE skzi_tokens;

CREATE TABLE departments (
    id          INT UNSIGNED  NOT NULL,
    name        VARCHAR(255)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_departments_name (name(128))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    comment         TEXT          NULL,
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
