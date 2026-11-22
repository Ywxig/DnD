# Как устроено приложение и куда писать код

Эта шпаргалка для случая «я вижу файлы, но не понимаю, зачем они».
В конце — пошаговый пример добавления новой сущности **«Ваншоты»** и точное
место, куда писать обработку формы.

---

## 1. Главная идея: MVC

Приложение разделено на три слоя:

| Слой | Простыми словами | Где лежит |
|------|------------------|-----------|
| **Model** (модель) | Работа с базой данных: сохранить, найти, выбрать | `src/Models/` |
| **View** (представление) | HTML, который видит пользователь | `views/` |
| **Controller** (контроллер) | «Диспетчер»: принимает запрос, решает, что вызвать, и какой шаблон показать | `src/Controllers/` |

Плюс вспомогательные вещи:

| Что | Зачем нужно | Где лежит |
|-----|-------------|-----------|
| Роутер | Сопоставляет `?r=...` с контроллером и методом | `src/Core/Router.php` |
| Точка входа | Принимает **все** HTTP-запросы | `public/index.php` |
| Bootstrap | Конфиг, сессия, ошибки, подключение Twig | `src/bootstrap.php` |
| Базовый контроллер | Готовые методы `render()`, `redirect()`, `isPost()` | `src/Core/Controller.php` |
| Базовая модель | Готовые `find()`, `all()`, `delete()` | `src/Core/Model.php` |
| Database | Обёртка над PDO (подключение к PostgreSQL) | `src/Core/Database.php` |
| Валидаторы | Проверка данных формы до сохранения | `src/Validators/` |
| Хелперы | `url()`, `flash()`, CSRF, `current_user()`, `twig()` | `src/Helpers/functions.php` |
| Шаблоны Twig | HTML с подстановками `{{ ... }}` | `views/` |
| Статика | CSS и JS, доступные браузеру | `public/assets/` |

> Важно: браузер видит только папку `public/`. Поэтому CSS/JS лежат в
> `public/assets/`, а не в корне проекта.

---

## 2. Как проходит запрос

Пример: пользователь открывает создание персонажа.

```
Браузер
  │  GET /index.php?r=characters_create
  ▼
public/index.php
  │  регистрирует маршруты и запускает Router
  ▼
src/Core/Router.php
  │  по ключу "characters_create" находит:
  │  CharacterController::create()
  ▼
src/Controllers/CharacterController.php
  │  если GET  → берёт расы/классы из моделей и рендерит форму
  │  если POST → проверяет CSRF, валидирует, сохраняет через модель
  ▼
views/characters/create.twig
  │  extends layout.twig, подставляет данные
  ▼
HTML в браузер
```

Ключевая мысль: **всё начинается с маршрута в `public/index.php`**, затем
попадает в метод контроллера.

---

## 3. Что где писать

### Роутер — `public/index.php`

Здесь только список маршрутов. Логики тут нет.

```php
$router->add('characters_create', CharacterController::class, 'create');
//             ^ имя в URL          ^ класс                    ^ метод
```

Такой маршрут открывается по адресу:

```
index.php?r=characters_create
```

### Контроллер — `src/Controllers/`

Контроллер — это место, где **обрабатывается форма**:

1. принять GET и показать форму;
2. принять POST;
3. проверить CSRF;
4. собрать данные;
5. вызвать валидатор;
6. если ошибки — показать форму снова;
7. если всё ок — сохранить через модель и сделать redirect.

Пример из проекта — `CharacterController::create()`.

### Модель — `src/Models/`

Только SQL и работа с БД. Никакого HTML и `$_POST`.

```php
final class Character extends Model
{
    protected const TABLE = 'characters';

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO characters (...) VALUES (...) RETURNING id',
            $data
        );
    }
}
```

### Валидатор — `src/Validators/`

Проверяет данные и возвращает массив ошибок:

```php
$errors = CharacterValidator::validate($data, $races, $classes);
// [] — всё хорошо
// ['name' => 'Введите имя', ...] — есть ошибки
```

### Шаблон — `views/`

Только HTML. Вся логика уже должна быть подготовлена контроллером.

```twig
{% extends 'layout.twig' %}

{% block content %}
    <h1>{{ character.name }}</h1>
{% endblock %}
```

### Хелперы — `src/Helpers/functions.php`

Общие функции, доступные везде: `url()`, `flash()`, `csrf_field()`,
`current_user()`, `require_login()`, `twig()`.

---

## 4. Куда писать обработку формы (короткий ответ)

| Что нужно сделать | Куда писать |
|-------------------|-------------|
| Нарисовать форму | `views/<сущность>/create.twig` |
| Зарегистрировать URL | `public/index.php` → `$router->add(...)` |
| Принять GET/POST | метод контроллера в `src/Controllers/` |
| Проверить поля | `src/Validators/...Validator.php` |
| Сохранить в БД | метод модели в `src/Models/` |
| Показать ошибки/успех | снова `render()` или `flash()` + `redirect()` |

То есть **логика обработки формы — это метод контроллера**. Модель только
сохраняет, шаблон только показывает.

---

## 5. Пример: добавляем «Ваншоты»

Допустим, ваншот — это короткая игра на один вечер. Нужны:
список своих ваншотов, создание, просмотр.

### Шаг 1. Таблица в БД

Добавьте в `database/schema.sql`:

```sql
CREATE TABLE oneshots (
    id          BIGSERIAL   PRIMARY KEY,
    user_id     BIGINT      NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title       VARCHAR(100) NOT NULL,
    description TEXT         NOT NULL DEFAULT '',
    level       INTEGER      NOT NULL CHECK (level BETWEEN 1 AND 20),
    max_players INTEGER      NOT NULL CHECK (max_players BETWEEN 1 AND 10),
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE INDEX idx_oneshots_user_id ON oneshots(user_id);
```

Затем примените схему к базе (или пересоздайте контейнер с томом):

```bash
docker compose down -v
docker compose up -d --build
```

### Шаг 2. Модель

Файл `src/Models/Oneshot.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Oneshot extends Model
{
    protected const TABLE = 'oneshots';

    /** Создаёт ваншот и возвращает его id. */
    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO oneshots (user_id, title, description, level, max_players)
             VALUES (:user_id, :title, :description, :level, :max_players)
             RETURNING id',
            $data
        );
    }

    /** Список ваншотов конкретного пользователя. */
    public static function ownedBy(int $userId): array
    {
        return Database::query(
            'SELECT * FROM oneshots WHERE user_id = :user_id ORDER BY created_at DESC',
            ['user_id' => $userId]
        )->fetchAll();
    }

    /** Один ваншот с именем владельца. */
    public static function findDetailed(int $id): ?array
    {
        $row = Database::query(
            'SELECT o.*, u.username AS owner_name
             FROM oneshots o
             JOIN users u ON u.id = o.user_id
             WHERE o.id = :id',
            ['id' => $id]
        )->fetch();

        return $row ?: null;
    }
}
```

### Шаг 3. Валидатор (необязательно, но правильно)

Файл `src/Validators/OneshotValidator.php`:

```php
<?php

declare(strict_types=1);

namespace App\Validators;

final class OneshotValidator
{
    /** @return array<string, string> */
    public static function validate(array $data): array
    {
        $errors = [];

        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            $errors['title'] = 'Введите название.';
        } elseif (mb_strlen($title) > 100) {
            $errors['title'] = 'Название — не более 100 символов.';
        }

        $level = $data['level'] ?? null;
        if (!is_int($level) || $level < 1 || $level > 20) {
            $errors['level'] = 'Уровень должен быть от 1 до 20.';
        }

        $maxPlayers = $data['max_players'] ?? null;
        if (!is_int($maxPlayers) || $maxPlayers < 1 || $maxPlayers > 10) {
            $errors['max_players'] = 'Игроков должно быть от 1 до 10.';
        }

        return $errors;
    }
}
```

### Шаг 4. Контроллер — вот здесь обрабатывается форма

Файл `src/Controllers/OneshotController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Oneshot;
use App\Validators\OneshotValidator;

final class OneshotController extends Controller
{
    /** Список своих ваншотов. */
    public function my(): void
    {
        $this->requireLogin();

        $this->render('oneshots/my', [
            'oneshots' => Oneshot::ownedBy((int)$_SESSION['user_id']),
            'title'    => 'Мои ваншоты',
        ]);
    }

    /** GET — показать форму, POST — обработать и сохранить. */
    public function create(): void
    {
        $this->requireLogin();

        // 1. GET: просто показываем пустую форму.
        if (!$this->isPost()) {
            $this->render('oneshots/create', [
                'data'   => [],
                'errors' => [],
                'title'  => 'Новый ваншот',
            ]);
            return;
        }

        // 2. POST: защита от CSRF.
        assert_csrf();

        // 3. Собираем данные из формы.
        $data = [
            'title'       => $this->post('title'),
            'description' => trim((string)($_POST['description'] ?? '')),
            'level'       => (($_POST['level'] ?? '') !== '') ? (int)$_POST['level'] : null,
            'max_players' => (($_POST['max_players'] ?? '') !== '') ? (int)$_POST['max_players'] : null,
        ];

        // 4. Валидация.
        $errors = OneshotValidator::validate($data);

        // 5. Ошибки: показываем форму снова с введёнными значениями.
        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('oneshots/create', [
                'data'   => $data,
                'errors' => $errors,
                'title'  => 'Новый ваншот',
            ]);
            return;
        }

        // 6. Сохранение.
        $data['user_id'] = (int)$_SESSION['user_id'];
        $id = Oneshot::create($data);

        // 7. Redirect + flash-сообщение.
        flash('success', 'Ваншот «' . $data['title'] . '» создан.');
        $this->redirect('oneshot_view', ['id' => $id]);
    }

    /** Просмотр своего ваншота. */
    public function view(): void
    {
        $this->requireLogin();

        $oneshot = Oneshot::findDetailed((int)($_GET['id'] ?? 0));
        if ($oneshot === null || (int)$oneshot['user_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('oneshots/view', [
            'oneshot' => $oneshot,
            'title'   => $oneshot['title'],
        ]);
    }
}
```

**Это и есть ответ на вопрос «куда писать логику обработки формы»:**
в метод контроллера, в ветку после `if (!$this->isPost())`.
Сначала `assert_csrf()`, потом сбор `$_POST`, потом валидатор, потом модель,
потом `redirect()`.

### Шаг 5. Маршруты

`public/index.php`:

```php
use App\Controllers\OneshotController;

$router->add('oneshots_my', OneshotController::class, 'my');
$router->add('oneshots_create', OneshotController::class, 'create');
$router->add('oneshot_view', OneshotController::class, 'view');
```

Теперь доступны адреса:

```
index.php?r=oneshots_my
index.php?r=oneshots_create
index.php?r=oneshot_view&id=1
```

### Шаг 6. Шаблоны

`views/oneshots/create.twig` — форма:

```twig
{% extends 'layout.twig' %}

{% block content %}
<section class="panel form-panel">
    <h1>Новый ваншот</h1>

    <form method="post" action="{{ url('oneshots_create') }}">
        {{ csrf_field() }}

        <div class="form-row">
            <label for="title">Название *</label>
            <input id="title" type="text" name="title" value="{{ data.title }}"
                   class="{{ error_class(errors, 'title') }}">
            {{ field_error(errors, 'title') }}
        </div>

        <div class="form-row">
            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="4">{{ data.description }}</textarea>
        </div>

        <div class="form-grid">
            <div class="form-row">
                <label for="level">Уровень (1–20) *</label>
                <input id="level" type="number" name="level" min="1" max="20"
                       value="{{ data.level }}" class="{{ error_class(errors, 'level') }}">
                {{ field_error(errors, 'level') }}
            </div>

            <div class="form-row">
                <label for="max_players">Игроков (1–10) *</label>
                <input id="max_players" type="number" name="max_players" min="1" max="10"
                       value="{{ data.max_players }}" class="{{ error_class(errors, 'max_players') }}">
                {{ field_error(errors, 'max_players') }}
            </div>
        </div>

        <button type="submit" class="btn">Создать ваншот</button>
    </form>
</section>
{% endblock %}
```

`views/oneshots/my.twig` — список:

```twig
{% extends 'layout.twig' %}

{% block content %}
<section class="panel">
    <div class="panel-head">
        <h1>Мои ваншоты</h1>
        <a class="btn" href="{{ url('oneshots_create') }}">+ Создать</a>
    </div>

    {% if oneshots is empty %}
        <p class="muted">Пока нет ваншотов.</p>
    {% else %}
        <table>
            <thead>
            <tr><th>Название</th><th>Уровень</th><th>Игроков</th><th>Создан</th><th></th></tr>
            </thead>
            <tbody>
            {% for oneshot in oneshots %}
                <tr>
                    <td>{{ oneshot.title }}</td>
                    <td>{{ oneshot.level }}</td>
                    <td>{{ oneshot.max_players }}</td>
                    <td>{{ format_date(oneshot.created_at) }}</td>
                    <td><a href="{{ url('oneshot_view', {id: oneshot.id}) }}">Посмотреть</a></td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    {% endif %}
</section>
{% endblock %}
```

`views/oneshots/view.twig` — карточка:

```twig
{% extends 'layout.twig' %}

{% block content %}
<section class="panel">
    <div class="panel-head">
        <h1>{{ oneshot.title }}</h1>
        <a class="btn btn-secondary" href="{{ url('oneshots_my') }}">← Назад</a>
    </div>

    <dl class="character-meta">
        <div><dt>Уровень</dt><dd>{{ oneshot.level }}</dd></div>
        <div><dt>Игроков</dt><dd>{{ oneshot.max_players }}</dd></div>
        <div><dt>Мастер</dt><dd>{{ oneshot.owner_name }}</dd></div>
        <div><dt>Создан</dt><dd>{{ format_date(oneshot.created_at) }}</dd></div>
    </dl>

    {% if oneshot.description %}
        <h2>Описание</h2>
        <p>{{ oneshot.description|nl2br }}</p>
    {% endif %}
</section>
{% endblock %}
```

### Шаг 7. Ссылка в меню

`views/layout.twig`, внутри `{% if current_user %}`:

```twig
<a href="{{ url('oneshots_my') }}">Ваншоты</a>
```

---

## 6. Чек-лист добавления новой сущности

1. [ ] Таблица в `database/schema.sql`.
2. [ ] Модель в `src/Models/` (SQL + `Database::...`).
3. [ ] Валидатор в `src/Validators/` (если есть форма).
4. [ ] Контроллер в `src/Controllers/`:
   - `GET` → `render()` формы;
   - `POST` → `assert_csrf()`, сбор данных, валидация, сохранение, `redirect()`.
5. [ ] Маршруты в `public/index.php`.
6. [ ] Шаблоны в `views/<сущность>/`:
   - `create.twig` — форма;
   - `my.twig` — список;
   - `view.twig` — карточка.
7. [ ] Ссылка в `views/layout.twig`.
8. [ ] Проверить в браузере: создание, ошибки валидации, просмотр.

---

## 7. Правила, которые лучше не нарушать

| Правило | Почему |
|---------|--------|
| В модель не передавать `$_POST` целиком | Модель должна получать уже проверенные данные |
| В шаблоне не писать SQL | Шаблон — только HTML |
| В контроллере не писать SQL | Для этого есть модели |
| Всегда `assert_csrf()` в POST | Защита от подделки запросов |
| После успешного POST — `redirect()` | Защита от повторной отправки формы (F5) |
| Выводить через `{{ ... }}` | Twig сам экранирует HTML → защита от XSS |
| Проверять владельца при просмотре/изменении | Чтобы чужой `id` не открывал данные |
| Числа приводить к `(int)` | Данные из `$_GET`/`$_POST` — строки |

---

## 8. Если что-то не работает

1. Откройте `?r=...` напрямую — есть ли маршрут в `public/index.php`.
2. Проверьте имя класса и метода — регистр важен.
3. Включите `debug` в `config/config.php` (`'debug' => true`) — ошибки будут на экране.
4. Смотрите лог контейнера:

   ```bash
   docker compose logs -f app
   ```

5. Если ошибка `Call to undefined function ...` — проверьте, что хелпер объявлен
   в `src/Helpers/functions.php` и файл подключён в `src/bootstrap.php`.
6. Если не грузятся стили — файлы должны лежать в `public/assets/`.
