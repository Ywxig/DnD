<?php

declare(strict_types=1);

/**
 * Глобальные хелперы: экранирование вывода, ссылки, flash-сообщения,
 * CSRF-токены, доступ к текущему пользователю, Twig.
 */

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Экранирование строки для безопасного вывода в HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Строит ссылку на маршрут приложения: index.php?r=...&params...
 *
 * @param string               $route  Имя маршрута
 * @param array<string, mixed> $params GET-параметры
 */
function url(string $route, array $params = []): string
{
    return 'index.php?' . http_build_query(array_merge(['r' => $route], $params));
}

/* ------------------------------- Flash ---------------------------------- */

/**
 * Сохраняет flash-сообщение для показа на следующей странице.
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Возвращает и очищает накопленные flash-сообщения.
 *
 * @return list<array{type: string, message: string}>
 */
function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($messages) ? $messages : [];
}

/* -------------------------------- CSRF ---------------------------------- */

/**
 * Возвращает (при необходимости генерирует) CSRF-токен сессии.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

/**
 * Скрытое поле формы с CSRF-токеном.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Проверяет CSRF-токен из POST-запроса.
 */
function verify_csrf(mixed $token): bool
{
    return is_string($token)
        && $token !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals((string)$_SESSION['csrf_token'], $token);
}

/**
 * Проверяет CSRF-токен текущего POST-запроса; при ошибке — 403.
 */
function assert_csrf(): void
{
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Недействительный CSRF-токен. Вернитесь и отправьте форму ещё раз.');
    }
}

/* ------------------------------ Пользователь ---------------------------- */

/**
 * Текущий авторизованный пользователь (данные из БД, кэш на запрос).
 *
 * @return array<string, mixed>|null
 */
function current_user(): ?array
{
    static $user = null;
    static $resolved = false;

    if (!$resolved) {
        $resolved = true;
        $id = $_SESSION['user_id'] ?? null;
        $user = $id !== null ? \App\Models\User::find((int)$id) : null;
        // Если пользователь из сессии удалён из БД — сбрасываем сессию.
        if ($user === null && $id !== null) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

/* -------------------------------- Прочее -------------------------------- */

/**
 * Доступ только для авторизованных; иначе редирект на вход.
 */
function require_login(): void
{
    if (current_user() === null) {
        flash('error', 'Требуется вход в систему.');
        header('Location: ' . url('login'));
        exit;
    }
}

/**
 * Форматирует дату из БД для вывода.
 */
function format_date(mixed $timestamp): string
{
    $time = is_string($timestamp) ? strtotime($timestamp) : false;
    return $time ? date('d.m.Y H:i', $time) : '—';
}

/**
 * Отмечает select-опцию выбранной.
 */
function selected_option(mixed $current, mixed $value): string
{
    return ($current !== null && $current !== '' && (string)$current === (string)$value) ? ' selected' : '';
}

/**
 * HTML ошибки поля формы (или пустая строка).
 *
 * @param array<string, string> $errors
 */
function field_error(array $errors, string $field): string
{
    return isset($errors[$field])
        ? '<div class="field-error" data-error-for="' . e($field) . '">' . e($errors[$field]) . '</div>'
        : '<div class="field-error" data-error-for="' . e($field) . '"></div>';
}

/**
 * Класс-модификатор для поля с ошибкой.
 *
 * @param array<string, string> $errors
 */
function error_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' is-invalid' : '';
}

/**
 * Модификатор характеристики D&D: floor((значение - 10) / 2).
 */
function stat_modifier(int $score): int
{
    return (int)floor(($score - 10) / 2);
}

/**
 * Подпись модификатора со знаком: +2 / -1 / +0.
 */
function signed_modifier(int $score): string
{
    $mod = stat_modifier($score);
    return ($mod > 0 ? '+' : '') . $mod;
}

/* --------------------------------- Twig --------------------------------- */

/**
 * Единый экземпляр Twig: шаблоны из views/, общие переменные и хелперы.
 */
function twig(): Environment
{
    static $twig = null;
    if ($twig !== null) {
        return $twig;
    }
    if (!class_exists(Environment::class)) {
        throw new RuntimeException('Twig не установлен. Выполните: composer install');
    }

    $twig = new Environment(new FilesystemLoader(VIEWS_PATH), [
        'cache'            => false,
        'debug'            => (bool)app_config()['app']['debug'],
        'autoescape'       => 'html',
        'strict_variables' => false,
    ]);

    // Общие переменные всех шаблонов.
    $twig->addGlobal('app_name', app_config()['app']['name']);
    $twig->addGlobal('current_user', current_user());
    $twig->addGlobal('flashes', get_flashes());
    $twig->addGlobal('stats', \App\Models\Character::STATS);
    $twig->addGlobal('stat_labels', \App\Models\Character::STAT_LABELS);
    $twig->addGlobal('alignments', \App\Validators\CharacterValidator::ALIGNMENTS);
    $twig->addGlobal('post_formats', \App\Models\Post::FORMAT_LABELS);

    // Хелперы из этого файла, доступные шаблонам.
    $twig->addFunction(new TwigFunction('url', 'url'));
    $twig->addFunction(new TwigFunction('format_date', 'format_date'));
    $twig->addFunction(new TwigFunction('signed_modifier', 'signed_modifier'));
    $twig->addFunction(new TwigFunction('selected_option', 'selected_option', ['is_safe' => ['html']]));
    $twig->addFunction(new TwigFunction('error_class', 'error_class'));
    $twig->addFunction(new TwigFunction('field_error', 'field_error', ['is_safe' => ['html']]));
    $twig->addFunction(new TwigFunction('csrf_field', 'csrf_field', ['is_safe' => ['html']]));

    return $twig;
}

/* --------------------------- Совместимость Twig ------------------------- */

/**
 * Twig опционально использует trigger_deprecation() из symfony/deprecation-contracts.
 * Пакет Symfony в проекте не установлен, поэтому даём минимальную замену.
 */
if (!function_exists('trigger_deprecation')) {
    function trigger_deprecation(string $package, string $version, string $message, mixed ...$args): void
    {
        $message = $args === [] ? $message : vsprintf($message, $args);
        @trigger_error(sprintf('%s (%s %s)', $message, $package, $version), E_USER_DEPRECATED);
    }
}
