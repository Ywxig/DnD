<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PasswordReset;
use App\Models\User;

/**
 * Восстановление пароля.
 *
 * На форме запроса ответ одинаковый независимо от того, существует ли email
 * (не раскрываем наличие аккаунта). Токен одноразовый, в БД — только хеш.
 * На время разработки вместо письма ссылка пишется в лог-файл.
 *
 * @package App\Controllers
 */
final class PasswordResetController extends Controller
{
    /**
     * Форма запроса ссылки (GET) и обработка запроса (POST).
     */
    public function request(): void
    {
        if (!$this->isPost()) {
            $this->render('password/request', ['errors' => [], 'title' => 'Восстановление пароля']);
            return;
        }

        assert_csrf();

        $email = $this->post('email');
        if ($email === '') {
            $this->render('password/request', ['errors' => ['email' => 'Введите email.'], 'title' => 'Восстановление пароля']);
            return;
        }

        $user = User::findByEmail($email);
        if ($user !== null) {
            // Токен существует только в ссылке; в БД — его SHA-256-хеш.
            $token = bin2hex(random_bytes(32));
            PasswordReset::createForUser((int)$user['id'], hash('sha256', $token));
            $this->logLink((string)$user['email'], (string)$user['username'], $token);
        }

        // Одинаковый ответ в любом случае.
        flash('success', 'Если такой email зарегистрирован, ссылка для восстановления отправлена.');
        $this->redirect('password_request');
    }

    /**
     * Форма нового пароля (GET) и установка нового пароля (POST).
     */
    public function reset(): void
    {
        $token = (string)($_REQUEST['token'] ?? $_POST['token'] ?? '');
        $reset = $token !== '' ? PasswordReset::findValid(hash('sha256', $token)) : null;

        if (!$this->isPost()) {
            if ($reset === null) {
                flash('error', 'Ссылка недействительна или устарела. Запросите новую.');
                $this->redirect('password_request');
                return;
            }
            $this->render('password/reset', [
                'token'  => $token,
                'errors' => [],
                'title'  => 'Новый пароль',
            ]);
            return;
        }

        assert_csrf();

        if ($reset === null) {
            // Токен одноразовый: повторное использование недопустимо.
            flash('error', 'Ссылка недействительна, устарела или уже использована. Запросите новую.');
            $this->redirect('password_request');
            return;
        }

        $password        = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $errors          = [];

        if (strlen($password) < 6) {
            $errors['password'] = 'Пароль — минимум 6 символов.';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Пароли не совпадают.';
        }

        if ($errors !== []) {
            $this->render('password/reset', ['token' => $token, 'errors' => $errors, 'title' => 'Новый пароль']);
            return;
        }

        User::updatePassword((int)$reset['user_id'], password_hash($password, PASSWORD_DEFAULT));
        PasswordReset::markUsed((int)$reset['id']);

        flash('success', 'Пароль обновлён. Войдите с новым паролем.');
        $this->redirect('login');
    }

    /**
     * Пишет ссылку восстановления в лог-файл (вместо письма на этапе разработки).
     */
    private function logLink(string $email, string $username, string $token): void
    {
        $logFile = (string)app_config()['password_reset']['log_file'];
        $dir     = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $link = absolute_url('password_reset', ['token' => $token]);
        $line = sprintf(
            "[%s] password reset for %s <%s>: %s\n",
            date('Y-m-d H:i:s'),
            $username,
            $email,
            $link
        );
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
