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

// Composer-зависимости (Twig). Классы App\ по-прежнему грузятся своим автозагрузчиком.
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

// Конфигурация приложения.
// Приоритет источников:
//   1) переменные окружения (DB_HOST, DB_PORT, ...) — режим docker;
//   2) файл config/config.php — локальный запуск;
//   3) значения по умолчанию.
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
                    'name'  => 'D&D Персонажи',
                    'debug' => false,
                ],
                'db' => [
                    'host' => 'localhost',
                    'port' => 5432,
                    'name' => 'dnd',
                    'user' => 'dnd',
                    'password' => 'dnd_pass',
                ],
            ];

        // Переменные окружения переопределяют файл (docker-контейнер
        // видит смонтированный config.php, но должен подключаться к сервису db).
        if (getenv('DB_HOST') !== false) {
            $config['db']['host'] = getenv('DB_HOST');
            $config['db']['port'] = (int)(getenv('DB_PORT') ?: $config['db']['port']);
            $config['db']['name'] = getenv('DB_NAME') ?: $config['db']['name'];
            $config['db']['user'] = getenv('DB_USER') ?: $config['db']['user'];
            if (getenv('DB_PASSWORD') !== false) {
                $config['db']['password'] = getenv('DB_PASSWORD');
            }
        }
        if (getenv('APP_DEBUG') !== false) {
            $config['app']['debug'] = getenv('APP_DEBUG') === '1';
        }
        if (getenv('APP_NAME') !== false) {
            $config['app']['name'] = getenv('APP_NAME');
        }
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
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (($_SERVER['HTTPS'] ?? '') === 'on'),
]);
session_name('dnd_session');
session_start();
