-- Первичное наполнение БД тестовыми данными (по 50 записей каждого типа).
-- Только для dev/test. Не запускать на production.
--
-- Заполняет:
--   departments, dolj, token_models, token_users,
--   tokens, token_transfers, token_certificates
--
-- ВНИМАНИЕ: очищает перечисленные таблицы (TRUNCATE) перед вставкой.
-- Скрипт рассчитан на фактическую схему живой БД (departments/dolj/token_users
-- из внешней системы + таблицы приложения).
--
-- Использование:
--   mysql -u USER -p skzi_tokens < db/seed_test.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

USE skzi_tokens;

-- ---------------------------------------------------------------------------
-- Очистка (с учётом FK)
-- ---------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE token_transfers;
TRUNCATE TABLE token_certificates;
TRUNCATE TABLE tokens;
TRUNCATE TABLE token_models;
TRUNCATE TABLE token_users;
TRUNCATE TABLE departments;
TRUNCATE TABLE dolj;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- departments
--   id, name, is_print_visible, rp, is_otr
-- ---------------------------------------------------------------------------

INSERT INTO departments (id, name, is_print_visible, rp, is_otr)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 50
)
SELECT
    n,
    CONCAT(
        ELT(
            ((n - 1) MOD 10) + 1,
            'Отдел информационной безопасности',
            'Отдел информационных технологий',
            'Юридический отдел',
            'Бухгалтерия',
            'Отдел кадров',
            'Производственный отдел',
            'Отдел энергонадзора',
            'Отдел стройнадзора',
            'Административно-хозяйственный отдел',
            'Секретариат'
        ),
        ' №',
        n
    ) AS name,
    CASE WHEN n MOD 5 = 0 THEN 0 ELSE 1 END AS is_print_visible,
    CONCAT(
        ELT(
            ((n - 1) MOD 10) + 1,
            'отдела информационной безопасности',
            'отдела информационных технологий',
            'юридического отдела',
            'бухгалтерии',
            'отдела кадров',
            'производственного отдела',
            'отдела энергонадзора',
            'отдела стройнадзора',
            'административно-хозяйственного отдела',
            'секретариата'
        ),
        ' №',
        n
    ) AS rp,
    CASE WHEN n MOD 7 = 0 THEN 1 ELSE 0 END AS is_otr
FROM seq;

-- ---------------------------------------------------------------------------
-- dolj
--   id (tinyint), name, rp, sort_index, print
-- ---------------------------------------------------------------------------

INSERT INTO dolj (id, name, rp, sort_index, print)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 50
)
SELECT
    n,
    CONCAT(
        ELT(
            ((n - 1) MOD 12) + 1,
            'Инженер',
            'Ведущий инженер',
            'Специалист',
            'Главный специалист',
            'Начальник отдела',
            'Заместитель начальника отдела',
            'Инспектор',
            'Юрисконсульт',
            'Бухгалтер',
            'Системный администратор',
            'Аналитик',
            'Делопроизводитель'
        ),
        ' (тест ',
        n,
        ')'
    ) AS name,
    CONCAT(
        ELT(
            ((n - 1) MOD 12) + 1,
            'инженера',
            'ведущего инженера',
            'специалиста',
            'главного специалиста',
            'начальника отдела',
            'заместителя начальника отдела',
            'инспектора',
            'юрисконсульта',
            'бухгалтера',
            'системного администратора',
            'аналитика',
            'делопроизводителя'
        ),
        ' (тест ',
        n,
        ')'
    ) AS rp,
    n AS sort_index,
    CASE WHEN n MOD 4 = 0 THEN 0 ELSE 1 END AS print
FROM seq;

-- ---------------------------------------------------------------------------
-- token_models
-- ---------------------------------------------------------------------------

INSERT INTO token_models (id, name, created_at, updated_at, deleted_at)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 50
)
SELECT
    n,
    CONCAT(
        ELT(
            ((n - 1) MOD 8) + 1,
            'Рутокен ЭЦП 2.0',
            'Рутокен ЭЦП 3.0',
            'Рутокен Lite',
            'Рутокен ЭЦП Flash',
            'JaCarta ГОСТ',
            'JaCarta-2 ГОСТ',
            'eToken ГОСТ',
            'ESMART Token ГОСТ'
        ),
        ' / образец ',
        LPAD(n, 2, '0')
    ),
    DATE_SUB(NOW(), INTERVAL (60 - n) DAY),
    DATE_SUB(NOW(), INTERVAL (60 - n) DAY),
    CASE WHEN n MOD 25 = 0 THEN DATE_SUB(NOW(), INTERVAL 3 DAY) ELSE NULL END
FROM seq;

-- ---------------------------------------------------------------------------
-- token_users
-- ---------------------------------------------------------------------------

INSERT INTO token_users (
    id,
    person_name,
    person_dolj,
    person_department,
    city_id,
    cabinet,
    person_phone,
    person_email,
    person_address,
    sogl_ruk,
    needcrypto,
    pos,
    sd,
    n_type,
    id_num,
    id_printed,
    not_print,
    is_fired,
    is_token_needed,
    cr_date,
    updated
)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 50
),
generated AS (
    SELECT
        n,
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
    n AS id,
    CONCAT(surname, ' ', first_name, ' ', patronymic) AS person_name,
    n AS person_dolj,
    n AS person_department,
    1 + ((n - 1) MOD 3) AS city_id,
    LPAD(100 + ((n - 1) MOD 50), 3, '0') AS cabinet,
    CONCAT('+7-900-', LPAD(1000000 + n, 7, '0')) AS person_phone,
    CONCAT('user', n, '@example.test') AS person_email,
    CONCAT('г. Тест, ул. Примерная, д. ', n) AS person_address,
    (n MOD 3 = 0) AS sogl_ruk,
    (n MOD 5 = 0) AS needcrypto,
    (n MOD 7 = 0) AS pos,
    2000 + n AS sd,
    n_type_val AS n_type,
    CONCAT('T', LPAD(n, 5, '0')) AS id_num,
    CASE
        WHEN n MOD 4 = 0 THEN DATE_SUB(NOW(), INTERVAL (n MOD 365) DAY)
        ELSE NULL
    END AS id_printed,
    (n MOD 11 = 0) AS not_print,
    (n MOD 17 = 0) AS is_fired,
    (n MOD 13 <> 0) AS is_token_needed,
    DATE_SUB(NOW(), INTERVAL (90 - n) DAY) AS cr_date,
    DATE_SUB(NOW(), INTERVAL (90 - n) DAY) AS updated
FROM generated;

-- ---------------------------------------------------------------------------
-- tokens
--   1–30  — выданы сотрудникам 1–30
--   31–40 — на складе
--   41–45 — сломаны
--   46–50 — утеряны
-- ---------------------------------------------------------------------------

INSERT INTO tokens (
    id,
    token_model_id,
    serial_number,
    employee_id,
    is_broken,
    is_lost,
    comment,
    created_at,
    updated_at,
    deleted_at
)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 50
)
SELECT
    n,
    1 + ((n - 1) MOD 50) AS token_model_id,
    CONCAT('SN-TEST-', LPAD(n, 6, '0')) AS serial_number,
    CASE
        WHEN n <= 30 THEN n
        ELSE NULL
    END AS employee_id,
    CASE WHEN n BETWEEN 41 AND 45 THEN 1 ELSE 0 END AS is_broken,
    CASE WHEN n BETWEEN 46 AND 50 THEN 1 ELSE 0 END AS is_lost,
    CASE
        WHEN n BETWEEN 41 AND 45 THEN 'Тестовый комментарий: не исправен'
        WHEN n BETWEEN 46 AND 50 THEN 'Тестовый комментарий: утерян'
        WHEN n MOD 6 = 0 THEN 'Тестовый комментарий'
        ELSE NULL
    END AS comment,
    DATE_SUB(NOW(), INTERVAL (120 - n) DAY) AS created_at,
    DATE_SUB(NOW(), INTERVAL (120 - n) DAY) AS updated_at,
    CASE WHEN n = 50 THEN DATE_SUB(NOW(), INTERVAL 1 DAY) ELSE NULL END AS deleted_at
FROM seq;

-- ---------------------------------------------------------------------------
-- token_transfers
--   1–30  — выдача со склада сотруднику n
--   31–40 — возврат на склад (от сотрудника n-20)
--   41–50 — передача между сотрудниками
-- ---------------------------------------------------------------------------

INSERT INTO token_transfers (
    id,
    token_id,
    from_employee_id,
    to_employee_id,
    comment,
    transferred_at,
    created_at
)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 50
)
SELECT
    n,
    CASE
        WHEN n <= 40 THEN n
        ELSE n - 20
    END AS token_id,
    CASE
        WHEN n <= 30 THEN NULL
        WHEN n <= 40 THEN n - 20
        ELSE 1 + ((n - 41) MOD 30)
    END AS from_employee_id,
    CASE
        WHEN n <= 30 THEN n
        WHEN n <= 40 THEN NULL
        ELSE 1 + ((n - 40) MOD 30)
    END AS to_employee_id,
    CASE
        WHEN n <= 30 THEN 'Первичная выдача (тест)'
        WHEN n <= 40 THEN 'Возврат на склад (тест)'
        ELSE 'Передача между сотрудниками (тест)'
    END AS comment,
    DATE_SUB(NOW(), INTERVAL (100 - n) DAY) AS transferred_at,
    DATE_SUB(NOW(), INTERVAL (100 - n) DAY) AS created_at
FROM seq;

-- ---------------------------------------------------------------------------
-- token_certificates (user_id — VARCHAR в живой БД)
-- ---------------------------------------------------------------------------

INSERT INTO token_certificates (
    id,
    user_id,
    number,
    start,
    end,
    CA,
    key_valid_to,
    memo,
    real_fio,
    updated
)
WITH RECURSIVE seq AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 50
)
SELECT
    n,
    CAST(n AS CHAR) AS user_id,
    CONCAT('CERT-TEST-', LPAD(n, 6, '0')) AS number,
    DATE_SUB(CURDATE(), INTERVAL (400 - n * 2) DAY) AS start,
    DATE_ADD(CURDATE(), INTERVAL (365 - n) DAY) AS end,
    ELT(
        ((n - 1) MOD 5) + 1,
        'УЦ ФНС России',
        'УЦ Казначейства России',
        'УЦ Контур',
        'УЦ Такском',
        'УЦ СКБ Контур'
    ) AS CA,
    DATE_ADD(CURDATE(), INTERVAL (300 - n) DAY) AS key_valid_to,
    CASE
        WHEN n MOD 4 = 0 THEN 'Тестовая пометка к сертификату'
        ELSE ''
    END AS memo,
    u.person_name AS real_fio,
    DATE_SUB(NOW(), INTERVAL (n MOD 30) DAY) AS updated
FROM seq
JOIN token_users u ON u.id = seq.n;

-- Сброс AUTO_INCREMENT
ALTER TABLE departments AUTO_INCREMENT = 51;
ALTER TABLE dolj AUTO_INCREMENT = 51;
ALTER TABLE token_models AUTO_INCREMENT = 51;
ALTER TABLE tokens AUTO_INCREMENT = 51;
ALTER TABLE token_transfers AUTO_INCREMENT = 51;

-- Проверка количества
-- SELECT 'departments' AS tbl, COUNT(*) AS cnt FROM departments
-- UNION ALL SELECT 'dolj', COUNT(*) FROM dolj
-- UNION ALL SELECT 'token_models', COUNT(*) FROM token_models
-- UNION ALL SELECT 'token_users', COUNT(*) FROM token_users
-- UNION ALL SELECT 'tokens', COUNT(*) FROM tokens
-- UNION ALL SELECT 'token_transfers', COUNT(*) FROM token_transfers
-- UNION ALL SELECT 'token_certificates', COUNT(*) FROM token_certificates;
