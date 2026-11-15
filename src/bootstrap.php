<?php

declare(strict_types=1);

/**
 * Инициализация окружения приложения.
 * Подключается один раз из public/index.php до запуска роутера.
 */

// Базовые константы
define('BASE_PATH', dirname(__DIR__));
define('VIEWS_PATH', BASE_PATH . '/views');

mb_internal_encoding('UTF-8');

// Конфигурация: файл config/config.php (вне git) либо переменные окружения.
// Переменные окружения используются, например, в docker-compose.
function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $file = BASE_PATH . '/config/config.php';
        $config = is_file($file)
            ? require $file
            : [
                'app' => [
                    'name'    => getenv('APP_NAME') ?: 'D&D Персонажи',
                    'debug'   => getenv('APP_DEBUG') === '1',
                ],
                'db' => [
                    'host'     => getenv('DB_HOST') ?: 'localhost',
                    'port'     => (int)(getenv('DB_PORT') ?: 5432),
                    'name'     => getenv('DB_NAME') ?: 'dnd',
                    'user'     => getenv('DB_USER') ?: 'dnd',
                    'password' => getenv('DB_PASSWORD') ?: 'dnd_pass',
                ],
                'password_reset' => [
                    'log_file' => BASE_PATH . '/logs/password_reset.log',
                ],
            ];
    }
    return $config;
}

// Хелперы приложения (e(), flash, CSRF и т.д.)
require __DIR__ . '/Helpers/functions.php';

// Режим ошибок: при debug показываем на экран, иначе только в лог.
if (app_config()['app']['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
}

// Сессия: httpOnly-кука, SameSite=Lax.
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on'),
]);
session_name('dnd_session');
session_start();
