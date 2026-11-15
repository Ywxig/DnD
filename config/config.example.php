<?php

/**
 * Пример конфигурации приложения.
 *
 * Скопируйте этот файл в config/config.php и подставьте свои значения.
 * config/config.php не попадает в git (см. .gitignore).
 * В docker-окружении можно не создавать файл — параметры берутся
 * из переменных окружения DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD.
 */

return [
    'app' => [
        'name'  => 'D&D Персонажи',
        'debug' => true,
    ],

    'db' => [
        'host'     => 'localhost',
        'port'     => 5432,
        'name'     => 'dnd',
        'user'     => 'dnd',
        'password' => 'dnd_pass',
    ],

    'password_reset' => [
        // Куда на время разработки писать ссылки восстановления пароля
        // (вместо отправки письма).
        'log_file' => __DIR__ . '/../logs/password_reset.log',
    ],
];
