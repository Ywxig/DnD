<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

/**
 * Регистрация, вход и выход.
 *
 * @package App\Controllers
 */
final class AuthController extends Controller
{
    /** Форма входа (GET) и аутентификация (POST). */
    public function login(): void
    {
        if (!$this->isPost()) {
            $this->render('auth/login', ['errors' => [], 'data' => []]);
            return;
        }

        assert_csrf();

        $login    = $this->post('login');
        $password = (string)($_POST['password'] ?? '');
        $errors   = [];

        if ($login === '') {
            $errors['login'] = 'Введите имя пользователя или email.';
        }
        if ($password === '') {
            $errors['password'] = 'Введите пароль.';
        }
        if ($errors !== []) {
            $this->render('auth/login', ['errors' => $errors, 'data' => ['login' => $login]]);
            return;
        }

        // Разрешаем вход и по имени, и по email.
        $user = User::findByUsername($login) ?? User::findByEmail($login);

        if ($user === null || !password_verify($password, (string)$user['password_hash'])) {
            // Единый текст ошибки: не раскрываем, что именно неверно.
            $errors['login'] = 'Неверное имя пользователя или пароль.';
            $this->render('auth/login', ['errors' => $errors, 'data' => ['login' => $login]]);
            return;
        }

        // Защита от фиксации сессии: новый id после входа.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];

        flash('success', 'Вы вошли как ' . $user['username'] . '.');
        $this->redirect('characters_my');
    }

    /** Форма регистрации (GET) и создание аккаунта (POST). */
    public function register(): void
    {
        if (!$this->isPost()) {
            $this->render('auth/register', ['errors' => [], 'data' => []]);
            return;
        }

        assert_csrf();

        $data = [
            'username' => $this->post('username'),
            'email'    => $this->post('email'),
        ];
        $password        = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $errors          = [];

        // Имя: 3–30 символов, латиница/цифры/подчёркивание.
        if ($data['username'] === '') {
            $errors['username'] = 'Введите имя пользователя.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $data['username'])) {
            $errors['username'] = 'Имя: 3–30 символов, только латиница, цифры и _';
        } elseif (User::findByUsername($data['username']) !== null) {
            $errors['username'] = 'Это имя уже занято.';
        }

        // Email: формат и уникальность.
        if ($data['email'] === '') {
            $errors['email'] = 'Введите email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email.';
        } elseif (User::findByEmail($data['email']) !== null) {
            $errors['email'] = 'Этот email уже зарегистрирован.';
        }

        // Пароль: минимум 6 символов, совпадение с подтверждением.
        if (strlen($password) < 6) {
            $errors['password'] = 'Пароль — минимум 6 символов.';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Пароли не совпадают.';
        }

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('auth/register', ['errors' => $errors, 'data' => $data]);
            return;
        }

        User::create([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        flash('success', 'Регистрация успешна! Войдите в систему.');
        $this->redirect('login');
    }

    /** Выход: очистка сессии. */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: ' . url('login'));
        exit;
    }
}
