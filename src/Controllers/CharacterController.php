<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CharClass;
use App\Models\Character;
use App\Models\Race;
use App\Validators\CharacterValidator;

/**
 * Персонажи пользователя: список своих, создание и просмотр.
 *
 * @package App\Controllers
 */
final class CharacterController extends Controller
{
    /** Список персонажей текущего пользователя. */
    public function my(): void
    {
        $this->requireLogin();
        $this->render('characters/my', [
            'characters' => Character::ownedBy((int)$_SESSION['user_id']),
            'title' => 'Мои персонажи',
        ]);
    }

    /** Форма создания (GET) и сохранение персонажа (POST). */
    public function create(): void
    {
        $this->requireLogin();
        $races   = Race::all('name');
        $classes = CharClass::all('name');

        if (!$this->isPost()) {
            $this->render('characters/create', [
                'data' => [],
                'errors' => [],
                'races' => $races,
                'classes' => $classes,
                'action' => url('characters_create'),
                'title' => 'Создание персонажа',
            ]);
            return;
        }

        assert_csrf();
        $data   = $this->collectInput();
        $errors = CharacterValidator::validate($data, $races, $classes);

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('characters/create', [
                'data' => $data,
                'errors' => $errors,
                'races' => $races,
                'classes' => $classes,
                'action' => url('characters_create'),
                'title' => 'Создание персонажа',
            ]);
            return;
        }

        $data['user_id'] = (int)$_SESSION['user_id'];
        $id = Character::create($data);

        flash('success', 'Персонаж «' . $data['name'] . '» создан.');
        $this->redirect('character_view', ['id' => $id]);
    }

    /** Карточка персонажа. */
    public function view(): void
    {
        $this->requireLogin();

        $character = Character::findDetailed((int)($_GET['id'] ?? 0));
        if ($character === null) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('characters/view', [
            'character' => $character,
            'title'     => $character['name'],
        ]);
    }

    /** Форма редактирования (GET) и сохранение изменений (POST). */
    public function edit(): void
    {
        $this->requireLogin();

        $id = (int)($this->isPost() ? ($_POST['id'] ?? 0) : ($_GET['id'] ?? 0));
        $character = $this->findOwnedCharacter($id);
        if ($character === null) {
            return;
        }

        $races   = Race::all('name');
        $classes = CharClass::all('name');

        if (!$this->isPost()) {
            $this->render('characters/edit', [
                'data'    => $character,
                'errors'  => [],
                'races'   => $races,
                'classes' => $classes,
                'action'  => url('character_edit'),
                'title'   => 'Редактирование персонажа',
            ]);
            return;
        }

        assert_csrf();
        $data   = $this->collectInput();
        $errors = CharacterValidator::validate($data, $races, $classes);

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('characters/edit', [
                'data'    => array_merge($character, $data),
                'errors'  => $errors,
                'races'   => $races,
                'classes' => $classes,
                'action'  => url('character_edit'),
                'title'   => 'Редактирование персонажа',
            ]);
            return;
        }

        Character::update($id, $data);
        flash('success', 'Персонаж «' . $data['name'] . '» обновлён.');
        $this->redirect('character_view', ['id' => $id]);
    }

    /** Удаление персонажа владельцем (только POST + CSRF). */
    public function delete(): void
    {
        $this->requireLogin();

        if (!$this->isPost()) {
            $this->redirect('characters_my');
        }

        assert_csrf();

        $character = $this->findOwnedCharacter((int)($_POST['id'] ?? 0));
        if ($character === null) {
            return;
        }

        Character::delete((int)$character['id']);
        flash('success', 'Персонаж «' . $character['name'] . '» удалён.');
        $this->redirect('characters_my');
    }

    /**
     * Находит персонажа по id и проверяет, что он принадлежит текущему игроку.
     *
     * @return array<string, mixed>|null
     */
    private function findOwnedCharacter(int $id): ?array
    {
        $character = Character::findDetailed($id);
        if ($character === null || (int)$character['user_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(404);
            $this->render('errors/404');
            return null;
        }
        return $character;
    }

    /**
     * Собирает значения полей формы из POST.
     *
     * @return array<string, mixed>
     */
    private function collectInput(): array
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

    /** Поиск персонажей по базе данных. */
    public function find(): void
    {
        $this->requireLogin();

        $query = $this->get('q');
        $characters = Character::search($query);

        $this->render('characters/find', [
            'query'      => $query,
            'characters' => $characters,
            'title'      => 'Поиск персонажей',
        ]);
    }
}
