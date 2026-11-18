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
            'title'      => 'Мои персонажи',
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
        $id = Character::create($data);

        flash('success', 'Персонаж «' . $data['name'] . '» создан.');
        $this->redirect('character_view', ['id' => $id]);
    }

    /** Карточка своего персонажа. Чужие персонажи недоступны. */
    public function view(): void
    {
        $this->requireLogin();

        $character = Character::findDetailed((int)($_GET['id'] ?? 0));
        if ($character === null || (int)$character['user_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('characters/view', [
            'character' => $character,
            'title'     => $character['name'],
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
}
