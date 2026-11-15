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
use App\Controllers\HomeController;
use App\Controllers\PasswordResetController;
use App\Core\Router;

$router = new Router();

// Публичные страницы
$router->add('home', HomeController::class, 'index');
$router->add('character_view', HomeController::class, 'view');
$router->add('characters_search', CharacterController::class, 'search');

// Аутентификация
$router->add('login', AuthController::class, 'login');
$router->add('register', AuthController::class, 'register');
$router->add('logout', AuthController::class, 'logout');

// Восстановление пароля
$router->add('password_request', PasswordResetController::class, 'request');
$router->add('password_reset', PasswordResetController::class, 'reset');

// Кабинет пользователя
$router->add('characters_my', CharacterController::class, 'my');
$router->add('characters_create', CharacterController::class, 'create');
$router->add('characters_edit', CharacterController::class, 'edit');
$router->add('characters_delete', CharacterController::class, 'delete');

// Админка
$router->add('admin', AdminController::class, 'dashboard');
$router->add('admin_users', AdminController::class, 'users');
$router->add('admin_user_create', AdminController::class, 'userCreate');
$router->add('admin_user_role', AdminController::class, 'userRole');
$router->add('admin_user_delete', AdminController::class, 'userDelete');
$router->add('admin_characters', AdminController::class, 'characters');
$router->add('admin_character_edit', AdminController::class, 'characterEdit');
$router->add('admin_character_delete', AdminController::class, 'characterDelete');
$router->add('admin_races', AdminController::class, 'races');
$router->add('admin_race_create', AdminController::class, 'raceCreate');
$router->add('admin_race_edit', AdminController::class, 'raceEdit');
$router->add('admin_race_update', AdminController::class, 'raceUpdate');
$router->add('admin_race_delete', AdminController::class, 'raceDelete');
$router->add('admin_classes', AdminController::class, 'classes');
$router->add('admin_class_create', AdminController::class, 'classCreate');
$router->add('admin_class_edit', AdminController::class, 'classEdit');
$router->add('admin_class_update', AdminController::class, 'classUpdate');
$router->add('admin_class_delete', AdminController::class, 'classDelete');

$router->run();
