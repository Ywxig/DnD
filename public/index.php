<?php

declare(strict_types=1);

/**
 * Точка входа приложения.
 *
 * Настраивает автозагрузку классов, инициализирует окружение,
 * регистрирует маршруты и запускает роутер.
 */

// PSR-4 автозагрузчик классов App\ из папки src/ (без Composer).
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\CharacterController;
use App\Controllers\GameController;
use App\Core\Router;

$router = new Router();

// Главная — список персонажей (гостя перенаправит на вход).
$router->add('home', CharacterController::class, 'my');

// Регистрация, вход, выход.
$router->add('login', AuthController::class, 'login');
$router->add('register', AuthController::class, 'register');
$router->add('logout', AuthController::class, 'logout');

// Персонажи пользователя.
$router->add('characters_my', CharacterController::class, 'my');
$router->add('characters_create', CharacterController::class, 'create');
$router->add('characters_find', CharacterController::class, 'find');
$router->add('character_view', CharacterController::class, 'view');

// игры
$router->add('games_create', GameController::class, 'create');

$router->run();
