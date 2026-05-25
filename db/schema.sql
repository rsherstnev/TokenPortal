-- =============================================================================
-- Учёт токенов СКЗИ. Схема БД для MariaDB.
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
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS employees;

CREATE TABLE `users` (
    `id`                INT NOT NULL AUTO_INCREMENT,
    `person_name`       VARCHAR(150) NOT NULL COLLATE 'utf8mb3_general_ci',
    `person_dolj`       INT NOT NULL,
    `person_department` INT NOT NULL,
    `city_id`           TINYINT UNSIGNED NOT NULL,
    `cabinet`           VARCHAR(6) NOT NULL COLLATE 'utf8mb3_general_ci',
    `sogl_ruk`          TINYINT(1) NOT NULL,
    `needcrypto`        TINYINT(1) NOT NULL,
    `pos`               TINYINT(1) NOT NULL,
    `sd`                INT NOT NULL,
    `cr_date`           DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    `updated`           DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP,
    `n_type`            ENUM('','пром','энергонадзор','стройнадзор','ГТС') NOT NULL COLLATE 'utf8mb3_general_ci',
    `id_num`            VARCHAR(6) NOT NULL COLLATE 'utf8mb3_general_ci',
    `id_printed`        DATETIME NULL DEFAULT NULL,
    `not_print`         TINYINT NULL DEFAULT '0',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `id` (`id`) USING BTREE,
    INDEX `dept_idx` (`person_department`) USING BTREE
) COLLATE='utf8mb3_general_ci' ENGINE=InnoDB;

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
    employee_id     INT         NULL,
    is_broken       TINYINT(1)  NOT NULL DEFAULT 0,
    is_lost         TINYINT(1)  NOT NULL DEFAULT 0,
    created_at      DATETIME    NULL,
    updated_at      DATETIME    NULL,
    deleted_at      DATETIME    NULL,
    PRIMARY KEY (id),
    KEY idx_tokens_model        (token_model_id),
    KEY idx_tokens_employee     (employee_id),
    KEY idx_tokens_deleted      (deleted_at),
    KEY idx_tokens_model_serial (token_model_id, serial_number(128))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE token_transfers (
    id                UUID      NOT NULL,
    token_id          UUID      NULL,
    from_employee_id  INT       NULL,
    to_employee_id    INT       NULL,
    comment           TEXT      NULL,
    transferred_at    DATETIME  NULL,
    created_at        DATETIME  NULL,
    PRIMARY KEY (id),
    KEY idx_transfers_token (token_id),
    KEY idx_transfers_date  (transferred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
