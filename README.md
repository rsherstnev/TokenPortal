# Учёт токенов СКЗИ

Веб-приложение для учёта токенов СКЗИ: модели токенов, сами токены, сотрудники,
передачи токенов между сотрудниками, история передач и soft delete.

## Стек

| Компонент    | Версия в этом проекте     | Примечание |
|--------------|---------------------------|------------|
| PHP          | 8.5 (Ubuntu 26.04 LTS)    | Изначально запрашивалась PHP 8.0, но она EOL с ноября 2023 и недоступна в стандартных репозиториях Ubuntu 26.04. Установлена последняя доступная стабильная версия. |
| CodeIgniter  | 3.1.13                    |            |
| MariaDB      | 11.8+                     |            |
| Bootstrap    | 4.6.2                     | + jQuery 3.7.1 + Bootstrap Icons 1.11.3, всё лежит локально в `assets/vendor/`. |
| Apache       | 2.4                       | mod_rewrite обязателен.  |

## Установка ПО (Ubuntu 26.04)

```bash
sudo apt update
sudo apt install -y apache2 mariadb-server \
    php php-cli php-mysql php-mbstring php-xml php-intl php-curl php-zip libapache2-mod-php \
    unzip curl
sudo a2enmod rewrite
sudo systemctl enable --now apache2 mariadb
```

## База данных

```bash
sudo mysql <<'SQL'
CREATE DATABASE IF NOT EXISTS skzi_tokens CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'skzi'@'localhost' IDENTIFIED BY 'skzi';
GRANT ALL PRIVILEGES ON skzi_tokens.* TO 'skzi'@'localhost';
FLUSH PRIVILEGES;
SQL

mysql -u skzi -pskzi skzi_tokens < db/schema.sql
```

Файл `db/schema.sql` содержит полностью преобразованную из PostgreSQL схему
(см. ниже про правила преобразования) и сидирует:

- 3 модели токенов (Рутокен ЭЦП 2.0, JaCarta-2 SE, eToken PRO 72К)
- 3 тестовых сотрудников.

### Преобразование схемы PostgreSQL → MariaDB

| PostgreSQL                     | MariaDB                   |
|--------------------------------|---------------------------|
| `uuid`                         | `UUID`                    |
| `text`                         | `TEXT`                    |
| `boolean`                      | `TINYINT(1)`              |
| `timestamp with time zone`     | `DATETIME` (UTC)          |
| `text DEFAULT ''::text`        | `TEXT NOT NULL DEFAULT ('')` |

### Дополнения относительно исходной схемы

Скриншоты UI содержат тогглы «Неисправен (сломан)» и «Утерян», которых
**не было в присланной схеме**. Поэтому в таблицу `tokens` добавлены:

```sql
is_broken TINYINT(1) NOT NULL DEFAULT 0
is_lost   TINYINT(1) NOT NULL DEFAULT 0
```

Эти поля участвуют в фильтре «Сломанные / Утерянные» и в логике вычисления
статуса токена в `Token_m::compute_status`.

## Apache vhost

Используется vhost, отдающий `DocumentRoot = /home/norden/tokens`:

```apache
# /etc/apache2/sites-available/skzi-tokens.conf
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /home/norden/tokens

    <Directory /home/norden/tokens>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/skzi_error.log
    CustomLog ${APACHE_LOG_DIR}/skzi_access.log combined
</VirtualHost>
```

Активация:

```bash
sudo a2dissite 000-default
sudo a2ensite skzi-tokens
sudo systemctl reload apache2
```

Для доступа Apache к файлам домашнего каталога:

```bash
sudo usermod -a -G norden www-data
sudo chmod 750 /home/norden
sudo chmod 755 /home/norden/tokens
sudo chown -R norden:www-data application/logs application/cache
sudo chmod -R 775 application/logs application/cache
```

После этого открыть в браузере: <http://localhost/>.

## Конфигурация CodeIgniter

Основные параметры:

- `application/config/config.php`
  - `base_url` определяется автоматически из `HTTP_HOST`;
  - `index_page = ''` (clean URLs через `.htaccess`);
  - `csrf_protection = TRUE`, имя поля — `csrf_token`.
- `application/config/database.php` — `skzi`/`skzi`/`skzi_tokens`, кодировка `utf8mb4`.
- `application/config/autoload.php` — автозагружены `database`, `session`, `form_validation`, хелперы `url`, `form`, `uuid`.
- `application/config/routes.php` — REST-подобные маршруты, дефолтный контроллер `tokens`.
- `application/core/MY_Controller.php` — базовый контроллер с `json_ok` / `json_error`.

## Структура проекта

```
tokens/
├── application/
│   ├── config/                  настройки CodeIgniter
│   ├── controllers/
│   │   ├── Tokens.php
│   │   ├── Token_models.php
│   │   ├── Token_transfers.php
│   │   └── Employees.php
│   ├── core/MY_Controller.php
│   ├── helpers/uuid_helper.php  uuid_v4(), is_uuid()
│   ├── models/
│   │   ├── Token_m.php
│   │   ├── Token_model_m.php
│   │   ├── Token_transfer_m.php
│   │   └── Employee_m.php
│   └── views/
│       ├── templates/{header.php, footer.php}
│       ├── tokens/{index.php, _modals.php}
│       └── employees/index.php
├── assets/
│   ├── css/app.css
│   ├── js/app.js
│   └── vendor/{bootstrap, jquery, bootstrap-icons}
├── db/schema.sql                DDL + сид
├── system/                      ядро CodeIgniter (vendored)
├── .htaccess
├── index.php
└── README.md
```

## Маршруты

| Метод | URI                                       | Назначение                                |
|-------|-------------------------------------------|-------------------------------------------|
| GET   | `/` или `/tokens`                          | главная страница                          |
| GET   | `/tokens/list?q=&status=`                  | JSON-список токенов с фильтрами           |
| GET   | `/tokens/get/{id}`                         | один токен                                |
| POST  | `/tokens/create`                           | создание токена                           |
| POST  | `/tokens/update/{id}`                      | редактирование                            |
| POST  | `/tokens/delete/{id}`                      | soft delete                               |
| GET   | `/token_models/list?q=`                    | JSON-список моделей                       |
| GET   | `/token_models/options`                    | селект-список                             |
| POST  | `/token_models/create|update|delete`       | CRUD моделей                              |
| POST  | `/token_transfers/create/{token_id}`       | передача токена                           |
| GET   | `/token_transfers/history/{token_id}`      | история передач                           |
| GET   | `/employees`                               | страница сотрудников                      |
| GET   | `/employees/list`, `/employees/options`    | списки                                    |
| POST  | `/employees/create|update|delete`          | CRUD сотрудников                          |

Все POST-эндпоинты защищены CSRF-токеном CodeIgniter, AJAX автоматически
получает свежий хэш в JSON-ответе и подменяет его в DOM (`meta[name=csrf-hash]`).

## Возможности UI

- Двухколоночная главная: «Токены» + «Модели токенов».
- Живой поиск по сотруднику / модели / серийному номеру / словам «сломан»/«утерян».
- Фильтр статуса: Все / Выданные / Невыданные / Сломанные / Утерянные.
- Модальные формы добавления и редактирования токенов и моделей.
- Модальная форма передачи токена (с возвратом на склад при пустом получателе).
- История передач для конкретного токена.
- Soft delete для токенов, моделей и сотрудников (`deleted_at`).
- Отдельная страница `/employees` с полным CRUD сотрудников.

## Замечания по безопасности

Файл `/etc/sudoers.d/norden-nopasswd` создавался только для удобства локальной
установки. В продакшене удалите его:

```bash
sudo rm /etc/sudoers.d/norden-nopasswd
```

## Лицензия

Код CodeIgniter — MIT (см. `license.txt`). Прикладной код — MIT.
