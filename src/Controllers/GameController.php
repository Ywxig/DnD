<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
// use App\Models\CharClass;
// use App\Models\Character;
// use App\Models\Race;
use App\Validators\CharacterValidator;

class GameController extends Controller
{
    public function create()
    {
        // Проверяем, что пользователь авторизован
        $this->requireLogin();

        // Проверяем, что запрос пришел методом POST (форма была отправлена)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Получаем данные из полей формы по их атрибуту name=""
            $title       = $_POST['game_title'] ?? '';
            $format      = $_POST['game_format'] ?? 'oneshot';
            $startLevel  = $_POST['start_level'] ?? 1;
            $description = $_POST['description'] ?? '';

            // (Опционально) Очистка от пробелов по краям
            $title = trim($title);
            $description = trim($description);

            // Пример базовой валидации
            if (empty($title)) {
                // Обработка ошибки: название обязательно

            }

            // Дальше можно передать данные в модель или валидатор
            // Например:
            // CharacterValidator::validate($_POST);
            // Character::create([...]);

            // После успешного создания можно сделать редирект, чтобы избежать повторной отправки при обновлении страницы
            // header('Location: /games');
            // exit;
        }

        // Если это GET-запрос, обычно здесь подключается отображение (вьюха) с самой формой
    }
}
