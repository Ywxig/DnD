<?php

declare(strict_types=1);

/**
 * Глобальные хелперы: экранирование вывода, ссылки, flash-сообщения,
 * CSRF-токены, доступ к текущему пользователю.
 */

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

/**
 * Абсолютный URL маршрута (для ссылок в «письмах» восстановления пароля).
 *
 * @param array<string, mixed> $params
 */
function absolute_url(string $route, array $params = []): string
{
    $scheme = (($_SERVER['HTTPS'] ?? '') === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    return $scheme . '://' . $host . '/' . url($route, $params);
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
            unset($_SESSION['user_id'], $_SESSION['role']);
        }
    }
    return $user;
}

/** true, если текущий пользователь — администратор. */
function is_admin(): bool
{
    $user = current_user();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

/** Доступ только для авторизованных; иначе редирект на вход. */
function require_login(): void
{
    if (current_user() === null) {
        flash('error', 'Требуется вход в систему.');
        header('Location: ' . url('login'));
        exit;
    }
}

/** Доступ только для администраторов; иначе 403 / редирект. */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        flash('error', 'Доступ запрещён: требуются права администратора.');
        header('Location: ' . url('home'));
        exit;
    }
}

/* -------------------------------- Прочее -------------------------------- */

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
