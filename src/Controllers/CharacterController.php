<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CharClass;
use App\Models\Character;
use App\Models\Character as CharacterModel;
use App\Models\Race;
use App\Validators\CharacterValidator;

/**
 * Персонажи пользователя: список «мои», создание, редактирование,
 * удаление и публичный поиск с пагинацией.
 *
 * @package App\Controllers
 */
final class CharacterController extends Controller
{
    /**
     * Кабинет: список персонажей текущего пользователя.
     */
    public function my(): void
    {
        $this->requireLogin();
        $this->render('characters/my', [
            'characters' => CharacterModel::ownedBy((int)$_SESSION['user_id']),
            'title'      => 'Мои персонажи',
        ]);
    }

    /**
     * Форма создания (GET) и создание персонажа (POST).
     */
    public function create(): void
    {
        $this->requireLogin();
        $races   = Race::all('name');
        $classes = CharClass::all('name');

        if (!$this->isPost()) {
            $this->render('characters/create', [
                'data'    => [],
                'errors'  => [],
                'races'   => $races,
                'classes' => $classes,
                'action'  => url('characters_create'),
                'title'   => 'Создание персонажа',
            ]);
            return;
        }

        assert_csrf();
        $data   = $this->collectInput();
        $errors = CharacterValidator::validate($data, $races, $classes);

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('characters/create', [
                'data'    => $data,
                'errors'  => $errors,
                'races'   => $races,
                'classes' => $classes,
                'action'  => url('characters_create'),
                'title'   => 'Создание персонажа',
            ]);
            return;
        }

        $data['user_id'] = (int)$_SESSION['user_id'];
        CharacterModel::create($data);

        flash('success', 'Персонаж «' . $data['name'] . '» создан.');
        $this->redirect('characters_my');
    }

    /**
     * Форма редактирования (GET) и сохранение (POST). Только владелец или админ.
     */
    public function edit(): void
    {
        $this->requireLogin();

        $character = CharacterModel::find((int)($_GET['id'] ?? 0));
        if ($character === null) {
            $this->notFound();
            return;
        }
        if (!$this->owns($character)) {
            $this->forbidden();
            return;
        }

        $races   = Race::all('name');
        $classes = CharClass::all('name');
        $action  = url('characters_edit', ['id' => (int)$character['id']]);

        if (!$this->isPost()) {
            $this->render('characters/edit', [
                'data'      => $character,
                'errors'    => [],
                'races'     => $races,
                'classes'   => $classes,
                'action'    => $action,
                'character' => $character,
                'title'     => 'Редактирование: ' . $character['name'],
            ]);
            return;
        }

        assert_csrf();
        $data   = $this->collectInput();
        $errors = CharacterValidator::validate($data, $races, $classes);

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('characters/edit', [
                'data'      => $data,
                'errors'    => $errors,
                'races'     => $races,
                'classes'   => $classes,
                'action'    => $action,
                'character' => $character,
                'title'     => 'Редактирование: ' . $character['name'],
            ]);
            return;
        }

        CharacterModel::update((int)$character['id'], $data);
        flash('success', 'Персонаж «' . $data['name'] . '» обновлён.');
        $this->redirect('characters_my');
    }

    /**
     * Удаление персонажа (только POST + CSRF). Только владелец или админ.
     */
    public function delete(): void
    {
        $this->requireLogin();
        if (!$this->isPost()) {
            $this->redirect('characters_my');
            return;
        }

        assert_csrf();
        $character = CharacterModel::find((int)($_POST['id'] ?? 0));
        if ($character === null) {
            $this->notFound();
            return;
        }
        if (!$this->owns($character)) {
            $this->forbidden();
            return;
        }

        CharacterModel::delete((int)$character['id']);
        flash('success', 'Персонаж «' . $character['name'] . '» удалён.');
        $this->redirect(is_admin() ? 'admin_characters' : 'characters_my');
    }

    /**
     * Публичный поиск персонажей (GET): имя, раса, класс, уровень, мировоззрение.
     */
    public function search(): void
    {
        $filters = [
            'name'       => $this->get('name'),
            'race_id'    => (int)($_GET['race_id'] ?? 0),
            'class_id'   => (int)($_GET['class_id'] ?? 0),
            'level_from' => (int)($_GET['level_from'] ?? 0),
            'level_to'   => (int)($_GET['level_to'] ?? 0),
            'alignment'  => $this->get('alignment'),
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = CharacterModel::search($filters, $page, 10);

        $this->render('characters/search', [
            'filters'    => $filters,
            'rows'       => $result['rows'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'races'      => Race::all('name'),
            'classes'    => CharClass::all('name'),
            'alignments' => CharacterValidator::ALIGNMENTS,
            'title'      => 'Поиск персонажей',
        ]);
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

    /**
     * true, если текущий пользователь — владелец персонажа или админ.
     *
     * @param array<string, mixed> $character
     */
    private function owns(array $character): bool
    {
        return (int)$character['user_id'] === (int)$_SESSION['user_id'] || is_admin();
    }

    /**
     * Страница 404.
     */
    private function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404');
    }

    /**
     * Доступ чужому персонажу запрещён.
     */
    private function forbidden(): void
    {
        http_response_code(403);
        flash('error', 'Это не ваш персонаж.');
        $this->redirect('characters_my');
    }
}
