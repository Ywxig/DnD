<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\ErrorController;

/**
 * Простой роутер по query-параметру ?r=controller_action.
 *
 * Маршруты регистрируются как "имя => [класс контроллера, метод]".
 */
final class Router
{
    /** @var array<string, array{0: string, 1: string}> Карта маршрутов */
    private array $routes = [];

    /**
     * Регистрирует маршрут.
     *
     * @param string $route      Имя маршрута, например "characters_search"
     * @param string $controller Класс контроллера
     * @param string $method     Имя метода контроллера
     */
    public function add(string $route, string $controller, string $method): void
    {
        $this->routes[$route] = [$controller, $method];
    }

    /**
     * Разбирает текущий запрос и вызывает нужный контроллер.
     * Неизвестный маршрут — 404, исключение — 500.
     */
    public function run(): void
    {
        $route = trim((string)($_GET['r'] ?? 'home'), " /");

        try {
            if (!isset($this->routes[$route])) {
                http_response_code(404);
                (new ErrorController())->notFound();
                return;
            }
            [$controllerClass, $method] = $this->routes[$route];
            (new $controllerClass())->{$method}();
        } catch (\Throwable $e) {
            error_log('App error [' . $route . ']: ' . $e->getMessage());
            http_response_code(500);
            if (app_config()['app']['debug']) {
                echo '<pre>' . e($e->getMessage() . PHP_EOL . PHP_EOL . $e->getTraceAsString()) . '</pre>';
            } else {
                (new ErrorController())->serverError();
            }
        }
    }
}
