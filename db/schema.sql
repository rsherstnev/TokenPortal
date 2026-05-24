-- =============================================================================
-- Учёт токенов СКЗИ. Схема БД для MariaDB.
-- Преобразовано из исходной схемы PostgreSQL с учётом особенностей MariaDB:
--   uuid                       -> UUID  (нативный тип MariaDB 10.7+)
--   text                       -> TEXT
--   boolean                    -> TINYINT(1)
--   timestamp with time zone   -> DATETIME (UTC)
--
-- Дополнения относительно исходной схемы:
--   tokens.is_broken (TINYINT)  -- требуется тогглом «Неисправен (сломан)» в UI
--   tokens.is_lost   (TINYINT)  -- требуется тогглом «Утерян» в UI
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS skzi_tokens
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE skzi_tokens;

DROP TABLE IF EXISTS token_transfers;
DROP TABLE IF EXISTS tokens;
DROP TABLE IF EXISTS token_models;
DROP TABLE IF EXISTS employees;

CREATE TABLE employees (
    id              UUID         NOT NULL,
    firstname       TEXT         NOT NULL,
    lastname        TEXT         NOT NULL,
    patronymic      TEXT         NOT NULL DEFAULT (''),
    department_id   UUID         NULL,
    position_id     UUID         NULL,
    address_id      UUID         NULL,
    cabinet         TEXT         NULL,
    email           TEXT         NULL,
    is_active       TINYINT(1)   NULL DEFAULT 1,
    dismissal_date  DATETIME     NULL,
    created_at      DATETIME     NULL,
    updated_at      DATETIME     NULL,
    deleted_at      DATETIME     NULL,
    PRIMARY KEY (id),
    KEY idx_employees_active (is_active),
    KEY idx_employees_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE token_models (
    id          UUID      NOT NULL,
    name        TEXT      NOT NULL,
    created_at  DATETIME  NULL,
    updated_at  DATETIME  NULL,
    deleted_at  DATETIME  NULL,
    PRIMARY KEY (id),
    KEY idx_token_models_deleted (deleted_at),
    KEY idx_token_models_name (name(128))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tokens (
    id              UUID        NOT NULL,
    token_model_id  UUID        NOT NULL,
    serial_number   TEXT        NOT NULL,
    employee_id     UUID        NULL,
    is_broken       TINYINT(1)  NOT NULL DEFAULT 0,
    is_lost         TINYINT(1)  NOT NULL DEFAULT 0,
    created_at      DATETIME    NULL,
    updated_at      DATETIME    NULL,
    deleted_at      DATETIME    NULL,
    PRIMARY KEY (id),
    KEY idx_tokens_model     (token_model_id),
    KEY idx_tokens_employee  (employee_id),
    KEY idx_tokens_deleted   (deleted_at),
    KEY idx_tokens_model_serial (token_model_id, serial_number(128))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE token_transfers (
    id                UUID      NOT NULL,
    token_id          UUID      NULL,
    from_employee_id  UUID      NULL,
    to_employee_id    UUID      NULL,
    comment           TEXT      NULL,
    transferred_at    DATETIME  NULL,
    created_at        DATETIME  NULL,
    PRIMARY KEY (id),
    KEY idx_transfers_token (token_id),
    KEY idx_transfers_date  (transferred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
