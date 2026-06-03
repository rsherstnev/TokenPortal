-- Тестовое наполнение таблицы users (сотрудники).
-- Только для dev/test. Не запускать на production без проверки.
--
-- Создаёт 100 сотрудников, равномерно распределённых по 16 отделам
-- (отделы 1–4: по 7 человек, отделы 5–16: по 6 человек).
--
-- Использование:
--   mysql -u USER -p skzi_tokens < db/seed_users_test.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

USE skzi_tokens;

CREATE TABLE IF NOT EXISTS users (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    person_name         VARCHAR(255)    NOT NULL,
    person_dolj         INT             NOT NULL DEFAULT 0,
    person_department   INT             NOT NULL DEFAULT 0,
    city_id             TINYINT UNSIGNED NOT NULL DEFAULT 1,
    cabinet             VARCHAR(6)      NOT NULL DEFAULT '',
    sogl_ruk            TINYINT(1)      NOT NULL DEFAULT 0,
    needcrypto          TINYINT(1)      NOT NULL DEFAULT 0,
    pos                 TINYINT(1)      NOT NULL DEFAULT 0,
    sd                  INT             NOT NULL DEFAULT 0,
    n_type              VARCHAR(32)     NOT NULL DEFAULT '',
    id_num              VARCHAR(6)      NOT NULL DEFAULT '',
    id_printed          DATETIME        NULL,
    not_print           TINYINT(1)      NOT NULL DEFAULT 0,
    is_fired            TINYINT(1)      NOT NULL DEFAULT 0,
    cr_date             DATETIME        NULL DEFAULT CURRENT_TIMESTAMP,
    updated             DATETIME        NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_users_department (person_department),
    KEY idx_users_name (person_name(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Удаляем только ранее созданные тестовые записи (по префиксу id_num).
DELETE FROM users WHERE id_num LIKE 'T%';

INSERT INTO users (
    person_name,
    person_dolj,
    person_department,
    city_id,
    cabinet,
    sogl_ruk,
    needcrypto,
    pos,
    sd,
    n_type,
    id_num,
    id_printed,
    not_print,
    is_fired
)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 100
),
generated AS (
    SELECT
        n,
        CASE
            WHEN n <= 28 THEN 1 + (n - 1) DIV 7
            ELSE 5 + (n - 29) DIV 6
        END AS dept,
        ELT(
            ((n - 1) MOD 12) + 1,
            'Иванов', 'Петров', 'Сидоров', 'Козлов', 'Новиков', 'Морозов',
            'Волков', 'Соколов', 'Лебедев', 'Кузнецов', 'Попов', 'Смирнов'
        ) AS surname,
        ELT(
            ((n - 1) MOD 10) + 1,
            'Алексей', 'Дмитрий', 'Сергей', 'Андрей', 'Михаил',
            'Елена', 'Ольга', 'Наталья', 'Татьяна', 'Ирина'
        ) AS first_name,
        ELT(
            ((n - 1) MOD 8) + 1,
            'Иванович', 'Петрович', 'Сергеевич', 'Андреевич',
            'Ивановна', 'Петровна', 'Сергеевна', 'Андреевна'
        ) AS patronymic,
        ELT(
            ((n - 1) MOD 5) + 1,
            'пром', 'энергонадзор', 'стройнадзор', 'ГТС', ''
        ) AS n_type_val
    FROM seq
)
SELECT
    CONCAT(surname, ' ', first_name, ' ', patronymic) AS person_name,
    1 + ((n - 1) MOD 8) AS person_dolj,
    dept AS person_department,
    1 + ((n - 1) MOD 3) AS city_id,
    LPAD(100 + ((n - 1) MOD 50), 3, '0') AS cabinet,
    (n MOD 3 = 0) AS sogl_ruk,
    (n MOD 5 = 0) AS needcrypto,
    (n MOD 7 = 0) AS pos,
    1000 + n AS sd,
    n_type_val AS n_type,
    CONCAT('T', LPAD(n, 5, '0')) AS id_num,
    CASE
        WHEN n MOD 4 = 0 THEN DATE_SUB(NOW(), INTERVAL (n MOD 365) DAY)
        ELSE NULL
    END AS id_printed,
    (n MOD 11 = 0) AS not_print,
    (n MOD 17 = 0) AS is_fired
FROM generated;

-- Проверка распределения по отделам (должно быть 16 строк, сумма = 100).
-- SELECT person_department, COUNT(*) AS cnt
-- FROM users
-- WHERE id_num LIKE 'T%'
-- GROUP BY person_department
-- ORDER BY person_department;
