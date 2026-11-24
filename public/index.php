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

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CharacterController;
use App\Controllers\PostController;
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
$router->add('character_edit', CharacterController::class, 'edit');
$router->add('character_delete', CharacterController::class, 'delete');

// Посты-объявления об играх.
$router->add('posts', PostController::class, 'index');
$router->add('posts_my', PostController::class, 'my');
$router->add('posts_create', PostController::class, 'create');
$router->add('post_view', PostController::class, 'view');

// Админка (только для администраторов).
$router->add('admin_users', AdminController::class, 'users');
$router->add('admin_user_create', AdminController::class, 'userCreate');
$router->add('admin_user_delete', AdminController::class, 'userDelete');

$router->run();
