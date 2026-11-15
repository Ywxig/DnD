<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CharClass;
use App\Models\Character;
use App\Models\Race;
use App\Models\User;
use App\Validators\CharacterValidator;

/**
 * Панель администратора: дашборд, управление пользователями,
 * персонажами, расами и классами. Роль проверяется в каждом действии.
 *
 * @package App\Controllers
 */
final class AdminController extends Controller
{
    /* ------------------------------ Дашборд -------------------------------- */

    /**
     * Дашборд: счётчики и последние события.
     */
    public function dashboard(): void
    {
        $this->requireAdmin();
        $this->render('admin/dashboard', [
            'counters' => Character::counters(),
            'users'    => User::latest(5),
            'latest'   => Character::latest(5),
            'title'    => 'Админка: дашборд',
        ]);
    }

    /* ---------------------------- Пользователи ----------------------------- */

    /**
     * Список пользователей с поиском.
     */
    public function users(): void
    {
        $this->requireAdmin();
        $query = $this->get('q');
        $this->render('admin/users', [
            'users' => User::search($query),
            'query' => $query,
            'title' => 'Админка: пользователи',
        ]);
    }

    /**
     * Создание пользователя/админа (GET форма, POST создание).
     */
    public function userCreate(): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->render('admin/user_form', [
                'errors' => [],
                'data'   => ['role' => 'user'],
                'title'  => 'Админка: новый пользователь',
            ]);
            return;
        }

        assert_csrf();

        $data = [
            'username' => $this->post('username'),
            'email'    => $this->post('email'),
            'role'     => in_array($this->post('role'), ['user', 'admin'], true) ? $this->post('role') : 'user',
        ];
        $password = (string)($_POST['password'] ?? '');
        $errors   = [];

        if ($data['username'] === '') {
            $errors['username'] = 'Введите имя пользователя.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $data['username'])) {
            $errors['username'] = 'Имя: 3–30 символов, только латиница, цифры и _';
        } elseif (User::findByUsername($data['username']) !== null) {
            $errors['username'] = 'Это имя уже занято.';
        }
        if ($data['email'] === '') {
            $errors['email'] = 'Введите email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email.';
        } elseif (User::findByEmail($data['email']) !== null) {
            $errors['email'] = 'Этот email уже зарегистрирован.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Пароль — минимум 6 символов.';
        }

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('admin/user_form', ['errors' => $errors, 'data' => $data, 'title' => 'Админка: новый пользователь']);
            return;
        }

        User::create([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => $data['role'],
        ]);
        flash('success', 'Пользователь «' . $data['username'] . '» создан.');
        $this->redirect('admin_users');
    }

    /**
     * Смена роли (POST). Свою роль менять нельзя.
     */
    public function userRole(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('admin_users');
            return;
        }
        assert_csrf();

        $id   = (int)($_POST['id'] ?? 0);
        $role = (string)($_POST['role'] ?? '');

        if ($id === (int)$_SESSION['user_id']) {
            flash('error', 'Нельзя менять собственную роль.');
            $this->redirect('admin_users');
            return;
        }
        if (!in_array($role, ['user', 'admin'], true) || User::find($id) === null) {
            flash('error', 'Некорректные данные.');
            $this->redirect('admin_users');
            return;
        }

        User::updateRole($id, $role);
        flash('success', 'Роль пользователя обновлена.');
        $this->redirect('admin_users');
    }

    /**
     * Удаление пользователя (POST). Себя удалить нельзя.
     */
    public function userDelete(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('admin_users');
            return;
        }
        assert_csrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$_SESSION['user_id']) {
            flash('error', 'Нельзя удалить собственный аккаунт.');
            $this->redirect('admin_users');
            return;
        }
        if (User::find($id) === null) {
            $this->notFound();
            return;
        }

        User::delete($id); // персонажи удалятся каскадно (ON DELETE CASCADE)
        flash('success', 'Пользователь удалён.');
        $this->redirect('admin_users');
    }

    /* ----------------------------- Персонажи ------------------------------- */

    /**
     * Список всех персонажей.
     */
    public function characters(): void
    {
        $this->requireAdmin();
        $filters = [
            'name'       => $this->get('name'),
            'race_id'    => 0,
            'class_id'   => 0,
            'level_from' => 0,
            'level_to'   => 0,
            'alignment'  => '',
        ];
        $result = Character::search($filters, max(1, (int)($_GET['page'] ?? 1)), 20);
        $this->render('admin/characters', [
            'rows'  => $result['rows'],
            'total' => $result['total'],
            'page'  => $result['page'],
            'pages' => $result['pages'],
            'name'  => $filters['name'],
            'title' => 'Админка: персонажи',
        ]);
    }

    /**
     * Редактирование любого персонажа (GET форма, POST сохранение).
     */
    public function characterEdit(): void
    {
        $this->requireAdmin();

        $character = Character::find((int)($_GET['id'] ?? 0));
        if ($character === null) {
            $this->notFound();
            return;
        }

        $races   = Race::all('name');
        $classes = CharClass::all('name');
        $action  = url('admin_character_edit', ['id' => (int)$character['id']]);

        if (!$this->isPost()) {
            $this->render('admin/character_edit', [
                'data'      => $character,
                'errors'    => [],
                'races'     => $races,
                'classes'   => $classes,
                'action'    => $action,
                'character' => $character,
                'title'     => 'Админка: персонаж ' . $character['name'],
            ]);
            return;
        }

        assert_csrf();
        $data = $this->collectCharacterInput();
        $errors = CharacterValidator::validate($data, $races, $classes);

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('admin/character_edit', [
                'data'      => $data,
                'errors'    => $errors,
                'races'     => $races,
                'classes'   => $classes,
                'action'    => $action,
                'character' => $character,
                'title'     => 'Админка: персонаж ' . $character['name'],
            ]);
            return;
        }

        Character::update((int)$character['id'], $data);
        flash('success', 'Персонаж «' . $data['name'] . '» обновлён.');
        $this->redirect('admin_characters');
    }

    /**
     * Удаление персонажа (POST + CSRF).
     */
    public function characterDelete(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('admin_characters');
            return;
        }
        assert_csrf();

        $character = Character::find((int)($_POST['id'] ?? 0));
        if ($character === null) {
            $this->notFound();
            return;
        }

        Character::delete((int)$character['id']);
        flash('success', 'Персонаж «' . $character['name'] . '» удалён.');
        $this->redirect('admin_characters');
    }

    /* -------------------------------- Расы --------------------------------- */

    /**
     * Список рас с числом использований.
     */
    public function races(): void
    {
        $this->requireAdmin();
        $this->render('admin/races', ['races' => Race::withUsage(), 'title' => 'Админка: расы']);
    }

    /**
     * Создание расы (POST со страницы списка).
     */
    public function raceCreate(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('admin_races');
            return;
        }
        assert_csrf();

        [$name, $description, $errors] = $this->validateDictionary('race');
        if ($errors !== []) {
            flash('error', implode(' ', $errors));
            $this->redirect('admin_races');
            return;
        }

        Race::create($name, $description);
        flash('success', 'Раса «' . $name . '» создана.');
        $this->redirect('admin_races');
    }

    /**
     * Форма редактирования расы (GET) и сохранение (POST).
     */
    public function raceEdit(): void
    {
        $this->requireAdmin();
        $race = Race::find((int)($_GET['id'] ?? 0));
        if ($race === null) {
            $this->notFound();
            return;
        }

        if (!$this->isPost()) {
            $this->render('admin/race_edit', ['race' => $race, 'errors' => [], 'title' => 'Админка: раса']);
            return;
        }

        assert_csrf();
        $data   = $this->dictionaryInput();
        $errors = $this->dictionaryErrors($data['name'], (int)$race['id'], 'race', 'раса');
        if ($errors !== []) {
            flash('error', implode(' ', $errors));
            $this->render('admin/race_edit', ['race' => array_merge($race, $data), 'errors' => $errors, 'title' => 'Админка: раса']);
            return;
        }

        Race::update((int)$race['id'], $data['name'], $data['description']);
        flash('success', 'Раса обновлена.');
        $this->redirect('admin_races');
    }

    /** Синоним сохранения расы (POST). */
    public function raceUpdate(): void
    {
        $this->raceEdit();
    }

    /**
     * Удаление расы (POST). Используемую расу удалить нельзя.
     */
    public function raceDelete(): void
    {
        $this->requireAdmin();
        $this->deleteDictionary(Race::class, 'race', 'Раса', 'раса', 'admin_races');
    }

    /* ------------------------------- Классы -------------------------------- */

    /**
     * Список классов с числом использований.
     */
    public function classes(): void
    {
        $this->requireAdmin();
        $this->render('admin/classes', ['classes' => CharClass::withUsage(), 'title' => 'Админка: классы']);
    }

    /**
     * Создание класса (POST со страницы списка).
     */
    public function classCreate(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('admin_classes');
            return;
        }
        assert_csrf();

        [$name, $description, $errors] = $this->validateDictionary('класс');
        if ($errors !== []) {
            flash('error', implode(' ', $errors));
            $this->redirect('admin_classes');
            return;
        }

        CharClass::create($name, $description);
        flash('success', 'Класс «' . $name . '» создан.');
        $this->redirect('admin_classes');
    }

    /**
     * Форма редактирования класса (GET) и сохранение (POST).
     */
    public function classEdit(): void
    {
        $this->requireAdmin();
        $class = CharClass::find((int)($_GET['id'] ?? 0));
        if ($class === null) {
            $this->notFound();
            return;
        }

        if (!$this->isPost()) {
            $this->render('admin/class_edit', ['class' => $class, 'errors' => [], 'title' => 'Админка: класс']);
            return;
        }

        assert_csrf();
        $data   = $this->dictionaryInput();
        $errors = $this->dictionaryErrors($data['name'], (int)$class['id'], 'class', 'класс');
        if ($errors !== []) {
            flash('error', implode(' ', $errors));
            $this->render('admin/class_edit', ['class' => array_merge($class, $data), 'errors' => $errors, 'title' => 'Админка: класс']);
            return;
        }

        CharClass::update((int)$class['id'], $data['name'], $data['description']);
        flash('success', 'Класс обновлён.');
        $this->redirect('admin_classes');
    }

    /** Синоним сохранения класса (POST). */
    public function classUpdate(): void
    {
        $this->classEdit();
    }

    /**
     * Удаление класса (POST). Используемый класс удалить нельзя.
     */
    public function classDelete(): void
    {
        $this->requireAdmin();
        $this->deleteDictionary(CharClass::class, 'class', 'Класс', 'класс', 'admin_classes');
    }

    /* ------------------------------ Приватные ------------------------------ */

    /**
     * Валидация справочника (раса/класс) при создании.
     *
     * @return array{0: string, 1: string, 2: list<string>}
     */
    private function validateDictionary(string $label): array
    {
        $data   = $this->dictionaryInput();
        $errors = $this->dictionaryErrors($data['name'], 0, $label === 'race' ? 'race' : 'class', $label);
        return [$data['name'], $data['description'], $errors];
    }

    /**
     * Значения полей справочника из POST.
     *
     * @return array{name: string, description: string}
     */
    private function dictionaryInput(): array
    {
        return [
            'name'        => $this->post('name'),
            'description' => $this->post('description'),
        ];
    }

    /**
     * Ошибки валидации справочника: имя обязательно и уникально.
     *
     * @return list<string>
     */
    private function dictionaryErrors(string $name, int $excludeId, string $kind, string $label): array
    {
        $errors = [];
        if ($name === '') {
            $errors[] = 'Введите название ' . $label . 'и.';
        } elseif (mb_strlen($name) > 50) {
            $errors[] = 'Название — не более 50 символов.';
        } else {
            $existing = $kind === 'race' ? Race::findByName($name) : CharClass::findByName($name);
            if ($existing !== null && (int)$existing['id'] !== $excludeId) {
                $errors[] = ucfirst($label) . 'я с таким названием уже существует.';
            }
        }
        return $errors;
    }

    /**
     * Удаление элемента справочника с запретом удаления используемых.
     *
     * @param class-string $modelClass Race::class или CharClass::class
     */
    private function deleteDictionary(string $modelClass, string $kind, string $label, string $genitive, string $redirectRoute): void
    {
        if (!$this->isPost()) {
            $this->redirect($redirectRoute);
            return;
        }
        assert_csrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($modelClass::find($id) === null) {
            $this->notFound();
            return;
        }

        $usage = $modelClass::usageCount($id);
        if ($usage > 0) {
            flash('error', $label . ' используется ' . $usage . ' персонаж(ами) — удалить нельзя.');
            $this->redirect($redirectRoute);
            return;
        }

        $modelClass::delete($id);
        flash('success', 'Справочник «' . $genitive . '» удалён.');
        $this->redirect($redirectRoute);
    }

    /**
     * Значения полей формы персонажа из POST.
     *
     * @return array<string, mixed>
     */
    private function collectCharacterInput(): array
    {
        $data = [
            'name'      => $this->post('name'),
            'race_id'   => (int)($_POST['race_id'] ?? 0),
            'class_id'  => (int)($_POST['class_id'] ?? 0),
            'level'     => (($_POST['level'] ?? '') !== '') ? (int)$_POST['level'] : null,
            'alignment' => $this->post('alignment'),
            'backstory' => trim((string)($_POST['backstory'] ?? '')),
        ];
        foreach (Character::STATS as $stat) {
            $data[$stat] = (($_POST[$stat] ?? '') !== '') ? (int)$_POST[$stat] : null;
        }
        return $data;
    }

    /**
     * Страница 404.
     */
    private function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404');
    }
}
