<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Базовый контроллер: рендер видов, редиректы, проверка доступа.
 */
abstract class Controller
{
    /**
     * Рендерит вид внутри общего layout.
     *
     * @param string               $view  Имя вида относительно views/, например "home"
     * @param array<string, mixed> $data  Переменные, доступные в виде
     */
    protected function render(string $view, array $data = []): void
    {
        // Вид сам наследует views/layout.twig через {% extends %}.
        echo twig()->render($view . '.twig', $data);
    }

    /**
     * Редирект на маршрут приложения.
     *
     * @param string               $route  Имя маршрута
     * @param array<string, mixed> $params Дополнительные GET-параметры
     */
    protected function redirect(string $route, array $params = []): void
    {
        header('Location: ' . url($route, $params));
        exit;
    }

    /** Доступ только для авторизованных. */
    protected function requireLogin(): void
    {
        require_login();
    }

    /** true, если текущий запрос — POST. */
    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Строковое значение из GET с обрезкой пробелов.
     */
    protected function get(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? $default;
        return is_scalar($value) ? trim((string)$value) : $default;
    }

    /**
     * Строковое значение из POST с обрезкой пробелов.
     */
    protected function post(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $default;
        return is_scalar($value) ? trim((string)$value) : $default;
    }
}
