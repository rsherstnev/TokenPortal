# Учёт токенов СКЗИ

Веб-приложение для учёта токенов СКЗИ: модели токенов, сами токены, сотрудники,
передачи токенов между сотрудниками, общая история передач и soft delete.

**Важно:** в приложении **нет входа пользователей и разграничения прав**. Любой,
кто может открыть сайт в сети, получает полный доступ ко всем данным и операциям
(CRUD, передачи, удаление). Размещайте только во внутренней сети или за VPN /
reverse proxy с аутентификацией.

## Стек

| Компонент   | Версия в проекте | Примечание |
|-------------|------------------|------------|
| PHP         | 8.1+ (проверено на 8.5) | Расширения: `mysqli`, `mbstring`, `json`, `intl` (желательно). |
| CodeIgniter | 3.1.13           | Ядро в каталоге `system/`, Composer для runtime не нужен. |
| MariaDB     | 10.5+ / 11.x     | InnoDB, `utf8mb4`. |
| Apache      | 2.4+             | `mod_rewrite` обязателен. Nginx — см. ниже. |
| Frontend    | Bootstrap 4.6.2, jQuery 3.7.1, Bootstrap Icons 1.11.3, Tom Select 2.4.3 | Всё локально в `assets/vendor/`, CDN не используется. |

## Требования

- Linux с Apache (или Nginx + php-fpm).
- MariaDB/MySQL 10.5+ (InnoDB).
- Права на запись для веб-сервера в `application/logs/` (в т.ч. журнал аудита) и `application/cache/`.

## Установка

Ниже — типовой сценарий для Ubuntu/Debian. Пути можно заменить; далее
`APP_ROOT` — каталог с `index.php` (рекомендуется `/var/www/skzi-tokens`).

### 1. Системные пакеты

```bash
sudo apt update
sudo apt install -y apache2 mariadb-server \
    php php-cli libapache2-mod-php \
    php-mysql php-mbstring php-xml php-intl php-json php-curl php-zip \
    unzip curl git
sudo a2enmod rewrite
sudo systemctl enable --now apache2 mariadb
```

### 2. Код приложения

```bash
sudo mkdir -p /var/www/skzi-tokens
sudo chown "$USER":www-data /var/www/skzi-tokens
git clone <URL-репозитория> /var/www/skzi-tokens
# или скопируйте архив/каталог в APP_ROOT
cd /var/www/skzi-tokens
```

### 3. База данных

Задайте **свой** пароль вместо примера `CHANGE_ME`:

```bash
read -rsp 'Пароль БД для пользователя skzi: ' DB_PASS; echo
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS skzi_tokens
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'skzi'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT SELECT, INSERT, UPDATE, DELETE ON skzi_tokens.* TO 'skzi'@'localhost';
FLUSH PRIVILEGES;
SQL

mysql -u skzi -p"${DB_PASS}" skzi_tokens < db/schema.sql
```

Файл `db/schema.sql` создаёт только таблицы (без начальных данных).

Настройте подключение в `application/config/database.php` (`hostname`, `username`,
`password`, `database`). Не коммитьте реальные пароли в git.

### 4. Права на каталоги

```bash
export APP_ROOT=/var/www/skzi-tokens
sudo chown -R www-data:www-data "$APP_ROOT/application/logs" "$APP_ROOT/application/cache"
sudo chmod -R 775 "$APP_ROOT/application/logs" "$APP_ROOT/application/cache"
sudo find "$APP_ROOT" -type f -exec chmod 644 {} \;
sudo find "$APP_ROOT" -type d -exec chmod 755 {} \;
```

### 5. Apache (VirtualHost)

```apache
# /etc/apache2/sites-available/skzi-tokens.conf
<VirtualHost *:80>
    ServerName skzi.example.local
    DocumentRoot /var/www/skzi-tokens

    <Directory /var/www/skzi-tokens>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Продакшен: не показывать PHP-ошибки в браузере
    SetEnv CI_ENV production

    ErrorLog ${APACHE_LOG_DIR}/skzi_error.log
    CustomLog ${APACHE_LOG_DIR}/skzi_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite skzi-tokens
# при необходимости: sudo a2dissite 000-default
sudo apache2ctl configtest
sudo systemctl reload apache2
```

Корень сайта — каталог с `index.php` и `.htaccess` (clean URLs).

### 6. Nginx (альтернатива)

```nginx
server {
    listen 80;
    server_name skzi.example.local;
    root /var/www/skzi-tokens;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param CI_ENV production;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 7. Проверка

Откройте в браузере URL виртуального хоста. Должна загрузиться страница «Токены».
Если 500 — смотрите `application/logs/` и лог Apache.

## Конфигурация CodeIgniter

| Файл | Назначение |
|------|------------|
| `index.php` | `ENVIRONMENT`: по умолчанию `development`; в продакшене задайте `CI_ENV=production` (Apache `SetEnv`, php-fpm `env`). |
| `application/config/config.php` | `base_url` из `HTTP_HOST` (удобно в dev; в prod лучше зафиксировать явный URL); `index_page = ''`; CSRF включён (`csrf_token`). |
| `application/config/database.php` | Параметры MariaDB. |
| `application/config/autoload.php` | `database`, `session`, `form_validation`; хелперы `url`, `form`, `id`, `audit_log`. |
| `application/config/audit_log.php` | Журнал действий: путь к JSON-файлу (`audit_log_file`), включение (`audit_log_enabled`). |
| `application/config/routes.php` | Маршруты API и страниц. |
| `application/core/MY_Controller.php` | JSON-ответы, обновление CSRF в AJAX, запись в журнал аудита. |

### Журнал действий (аудит)

Успешные изменения данных (токены, модели, передачи, скачивание акта) пишутся в
файл на сервере в формате **JSON Lines** (одна строка — одно событие). Путь задаётся
в `application/config/audit_log.php` (`audit_log_file`, по умолчанию
`/var/log/skzi-tokens/audit_actions.jsonl`). Каталог должен существовать и быть
доступен для **записи пользователю PHP/Apache** (часто `www-data`). Запись в
`application/logs/` из-под Apache в домашнем каталоге (`/home/…`) нередко
запрещена политикой ОС, даже при `chmod 777`.

```bash
sudo mkdir -p /var/log/skzi-tokens
sudo chown www-data:www-data /var/log/skzi-tokens
sudo chmod 750 /var/log/skzi-tokens
```

Пример строки:

```json
{"timestamp":"2026-06-05T14:30:00+07:00","ip":"10.0.1.42","message":"Токен (ID 12, модель «Рутокен», серийный номер «ABC123») был удалён","context":{"action":"token.delete","entity_id":12}}
```

IP берётся из запроса (`$this->input->ip_address()`); за reverse proxy настройте
`proxy_ips` в `application/config/config.php`.

### Composer

`composer.json` — шаблон CodeIgniter для разработки/тестов. Каталог `vendor/` в
`.gitignore` и **не используется** при обычном запуске сайта.

## Структура проекта

```
tokens/
├── application/
│   ├── config/
│   ├── controllers/
│   │   ├── Tokens.php
│   │   ├── Token_models.php
│   │   ├── Token_transfers.php
│   │   ├── Employees.php
│   │   └── Welcome.php          # заглушка CI, не используется в меню
│   ├── core/MY_Controller.php
│   ├── helpers/id_helper.php
│   ├── models/
│   └── views/
│       ├── templates/
│       ├── tokens/
│       ├── employees/
│       └── transfer_history/
├── assets/
│   ├── css/app.css
│   ├── js/app.js
│   └── vendor/
├── db/schema.sql
├── system/                      # CodeIgniter 3.1.13
├── .htaccess
├── index.php
└── README.md
```

## Маршруты

### Страницы (HTML)

| URI | Описание |
|-----|----------|
| `/`, `/tokens` | Главная: токены и модели |
| `/employees` | Сотрудники |
| `/transfer_history` | Общая история передач (поиск, фильтр по датам) |
| `/welcome` | Стандартная заглушка CodeIgniter (не в навигации) |

### API (JSON)

| Метод | URI | Назначение |
|-------|-----|------------|
| GET | `/tokens/list` | Список токенов. Query: `q`, `status` (`all` \| `issued` \| `not_issued` \| `broken` \| `lost`). По умолчанию фильтр `not_issued`. |
| GET | `/tokens/get/{id}` | Один токен |
| POST | `/tokens/create` | Создание |
| POST | `/tokens/update/{id}` | Редактирование |
| POST | `/tokens/delete/{id}` | Soft delete |
| GET | `/token_models/list` | Список моделей (`q`) |
| GET | `/token_models/options` | Активные модели для select |
| GET | `/token_models` | То же, что `list` (JSON, без HTML-страницы) |
| GET | `/token_models/get/{id}` | Одна модель |
| POST | `/token_models/create` \| `update` \| `delete` | CRUD моделей |
| POST | `/token_transfers/create/{token_id}` | Передача; пустой `to_employee_id` — возврат на склад |
| GET | `/token_transfers/get/{id}` | Одна запись передачи |
| POST | `/token_transfers/update/{id}` | Редактирование комментария передачи |
| GET | `/token_transfers/history/{token_id}` | История передач одного токена |
| GET | `/transfer_history/list` | Вся история. Query: `q`, `date_from`, `date_to` (`Y-m-d` или `Y-m-d H:i:s`, UTC). Поиск по слову «склад» находит записи с `NULL` в from/to. |
| GET | `/employees/list` | Все неудалённые сотрудники (`q`), включая неактивных |
| GET | `/employees/options` | Только активные (для выдачи токена) |
| GET | `/employees/get/{id}` | Один сотрудник |
| POST | `/employees/create` \| `update` \| `delete` | CRUD сотрудников |

Все **POST**-запросы требуют поле CSRF (`csrf_token` по умолчанию). Клиент
(`assets/js/app.js`) подставляет токен и обновляет его из поля `csrf` в ответе.

## Возможности UI

- Главная: две колонки «Токены» и «Модели токенов».
- Поиск по сотруднику, модели, серийному номеру; фильтр статуса.
- Флаги «Сломан» / «Утерян» (`tokens.is_broken`, `tokens.is_lost`).
- Модальные формы токенов, моделей, передачи; история передач по токену.
- Редактирование комментария записи передачи (история по токену и общая история).
- Страница «История передач» с фильтром по периоду.
- Soft delete (`deleted_at`) для токенов, моделей и сотрудников.

### Схема БД

`db/schema.sql` — DDL для MariaDB (конвертация из PostgreSQL).

Дополнительные поля в `tokens`: `is_broken`, `is_lost` (см. комментарии в SQL).

В таблице `employees` есть столбцы `department_id`, `position_id`, `address_id`
из исходной схемы — **в текущем UI не используются**.

| PostgreSQL (исходник) | MariaDB |
|-----------------------|---------|
| `serial` / `integer` | `INT UNSIGNED` (AUTO_INCREMENT для PK) |
| `text` | `TEXT` |
| `boolean` | `TINYINT(1)` |
| `timestamp with time zone` | `DATETIME` (UTC) |

## Безопасность

Результат ручного просмотра кода приложения (`application/`, `assets/js/`,
маршруты, конфиги). **Скрытых бэкдоров, eval/shell в прикладном коде и
исходящих запросов на сторонние хосты не обнаружено.** Хуки CI отключены
(`enable_hooks = FALSE`).

### Зависимости

| Компонент | Риск | Рекомендация |
|-----------|------|----------------|
| CodeIgniter 3.1.13 | Фреймворк EOL; известны исторические CVE | Следить за патчами; не выставлять `system/` в интернет; при возможности планировать миграцию. |
| Bootstrap 4.6.2 | Ветка EOL | Локальная копия; обновлять при редизайне. jQuery 3.7.1 и Tom Select 2.4.3 — актуальные минорные релизы. |
| Composer `vendor/` | Не подключается в runtime | Не устанавливать на боевом сервере без необходимости. |

Поиск по шаблонам (`eval`, `exec`, `shell_exec`, `base64_decode`, `curl` к
внешним URL) в `application/` — без совпадений. В `assets/js/app.js` все
запросы идут только на `SKZI_BASE_URL` (тот же origin).

### Уязвимости и ограничения (не бэкдоры, но критично для эксплуатации)

1. **Нет аутентификации** — единственная защита write-операций — CSRF от
   произвольного сайта; чтение и изменение данных доступны любому клиенту в сети.
2. **Учётные данные БД по умолчанию** в репозитории (`skzi`/`skzi`) — сменить
   до выхода в сеть.
3. **`encryption_key` в `config.php`** — статический ключ в git; для prod
   сгенерировать свой и не публиковать.
4. **`ENVIRONMENT = development`** по умолчанию — включены отображение ошибок;
   задать `production` и `db_debug = FALSE`.
5. **`base_url` из `HTTP_HOST`** — при неправильном прокси возможны проблемы с
   URL/CSRF; в prod задать фиксированный `base_url`.
6. **Контроллер `Welcome`** — доступен по `/welcome`, в меню нет (остаток CI).

### Чеклист для продакшена

- [ ] `SetEnv CI_ENV production` (или аналог для php-fpm)
- [ ] Сильный пароль БД, отдельный пользователь с минимальными правами
- [ ] Сеть: VPN, firewall, Basic Auth / SSO на reverse proxy
- [ ] HTTPS
- [ ] Отключить листинг каталогов (`Options -Indexes`)
- [ ] Не коммитить реальные секреты; ограничить доступ к `application/logs/`
- [ ] Удалить локальный `sudoers` для установки, если создавался (см. ниже)

Файл `/etc/sudoers.d/norden-nopasswd` (если создавался при локальной установке)
удалить на продакшене:

```bash
sudo rm -f /etc/sudoers.d/norden-nopasswd
```

## Лицензия

Код CodeIgniter — MIT (`license.txt`). Прикладной код — MIT.
