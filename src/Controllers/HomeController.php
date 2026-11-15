<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Character;

/**
 * Публичные страницы: главная и просмотр персонажа.
 *
 * @package App\Controllers
 */
final class HomeController extends Controller
{
    /**
     * Главная: счётчики, последние 5 персонажей, топ классов, случайный персонаж.
     */
    public function index(): void
    {
        $this->render('home', [
            'counters'   => Character::counters(),
            'latest'     => Character::latest(5),
            'topClasses' => Character::topClasses(6),
            'random'     => Character::random(),
        ]);
    }

    /**
     * Страница просмотра персонажа с модификаторами характеристик.
     */
    public function view(): void
    {
        $character = Character::findDetailed((int)($_GET['id'] ?? 0));
        if ($character === null) {
            $this->notFound();
            return;
        }
        $this->render('characters/view', [
            'character' => $character,
            'statLabels' => Character::STAT_LABELS,
        ]);
    }

    /**
     * Страница 404.
     */
    public function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404');
    }
}
