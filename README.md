# Персонажи D&D — простое PHP MVC-приложение

Учебный проект на **чистом PHP 8 (MVC, PDO)** с шаблонизатором **Twig**.
Никаких фреймворков и Symfony-пакетов: Twig установлен без
`symfony/polyfill-*` и `symfony/deprecation-contracts` (см. раздел «Зависимости»).

Возможности:

* регистрация и вход;
* создание персонажа D&D;
* просмотр списка и карточки **своих** персонажей.

Подробный разбор архитектуры и инструкция «куда добавлять код» —
в файле [`GUIDE.md`](GUIDE.md).

## Быстрый старт (Docker)

```bash
docker compose up -d --build
```

Приложение: **http://localhost:8080**, PostgreSQL доступен на `localhost:5433`.
Схема и тестовые данные накатываются при первом создании тома БД.

### Запуск без Docker

```bash
createdb dnd
psql -d dnd -f database/schema.sql
psql -d dnd -f database/seed.sql

composer install
php -S localhost:8000 -t public
```

Настройки подключения — в `config/config.php` (пример: `config/config.example.php`).

### Тестовые аккаунты

| Логин | Пароль |
|-------|--------|
| `aragorn` | `password123` |
| `gimli`   | `password123` |

Можно также зарегистрировать нового пользователя.

## Маршруты

| URL | Страница | Доступ |
|-----|----------|--------|
| `?r=register` | Регистрация | публичный |
| `?r=login` | Вход | публичный |
| `?r=logout` | Выход | авторизованный |
| `?r=characters_my` | Мои персонажи (главная) | авторизованный |
| `?r=characters_create` | Создание персонажа | авторизованный |
| `?r=character_view&id=N` | Карточка своего персонажа | только владелец |

Чужой персонаж по `id` не открывается — отдаётся 404.

## Структура

```
.
├── public/
│   ├── index.php              # точка входа: автозагрузка, маршруты
│   └── assets/                # style.css, app.js
├── src/
│   ├── bootstrap.php          # конфиг, сессия, ошибки, composer autoload
│   ├── Core/                  # Database, Router, Controller, Model
│   ├── Controllers/           # AuthController, CharacterController, ErrorController
│   ├── Models/                # User, Character, Race, CharClass
│   ├── Validators/            # CharacterValidator
│   └── Helpers/functions.php  # url(), flash, CSRF, current_user(), twig()
├── views/
│   ├── layout.twig            # базовый шаблон ({% block content %})
│   ├── auth/                  # login.twig, register.twig
│   ├── characters/            # my, create, view, _form
│   └── errors/                # 404.twig, 500.twig
├── database/                  # schema.sql, seed.sql
├── config/                    # config.example.php, config.php (в git не входит)
├── Dockerfile
├── docker-compose.yml
└── composer.json
```

## База данных

Четыре таблицы:

* `users` — пользователи (`username`, `email`, `password_hash`);
* `races` — справочник рас;
* `classes` — справочник классов;
* `characters` — персонажи, привязанные к пользователю, расе и классу.

При удалении пользователя его персонажи удаляются каскадно.

## Зависимости (Twig без Symfony)

Единственная библиотека — `twig/twig`. В `composer.json` добавлен блок `replace`,
который сообщает Composer, что пакеты `symfony/polyfill-ctype`,
`symfony/polyfill-mbstring` и `symfony/deprecation-contracts` не нужны:

```json
"replace": {
    "symfony/deprecation-contracts": "*",
    "symfony/polyfill-ctype": "*",
    "symfony/polyfill-mbstring": "*"
}
```

Вместо polyfill используются нативные расширения PHP `mbstring` и `ctype`
(оба уже включены в базовый образ `php:8.2-apache`).
Функция `trigger_deprecation()` объявлена в `src/Helpers/functions.php`.

Проверить, что Symfony-пакетов нет:

```bash
composer show          # должен остаться только twig/twig
ls vendor              # autoload.php, composer, twig
```

## Безопасность

* SQL-инъекции — подготовленные выражения PDO;
* XSS — автоэкранирование Twig (`autoescape: html`);
* CSRF — токен в сессии и скрытое поле во всех POST-формах;
* пароли — `password_hash()` / `password_verify()`;
* сессия — `HttpOnly`, `SameSite=Lax`, `session_regenerate_id(true)` после входа;
* доступ к персонажу — только владельцу.
