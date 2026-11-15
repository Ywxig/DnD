<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Страницы ошибок 404 и 500.
 *
 * @package App\Controllers
 */
final class ErrorController extends Controller
{
    /** Страница «не найдено». */
    public function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404');
    }

    /** Страница внутренней ошибки. */
    public function serverError(): void
    {
        http_response_code(500);
        $this->render('errors/500');
    }
}
