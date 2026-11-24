<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

/**
 * Админка: список пользователей, создание и удаление.
 *
 * @package App\Controllers
 */
final class AdminController extends Controller
{
    /** Список всех пользователей. */
    public function users(): void
    {
        require_admin();

        $this->render('admin/users', [
            'users' => User::allDetailed(),
            'title' => 'Админка — пользователи',
        ]);
    }

    /** Форма создания пользователя (GET) и сохранение (POST). */
    public function userCreate(): void
    {
        require_admin();

        if (!$this->isPost()) {
            $this->render('admin/user_create', [
                'data'   => [],
                'errors' => [],
                'title'  => 'Админка — новый пользователь',
            ]);
            return;
        }

        assert_csrf();

        $data = [
            'username' => $this->post('username'),
            'email'    => $this->post('email'),
            'is_admin' => !empty($_POST['is_admin']),
        ];
        $password        = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $errors          = [];

        if ($data['username'] === '') {
            $errors['username'] = 'Введите имя пользователя.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $data['username'])) {
            $errors['username'] = 'Имя: 3–30 символов, только латиница, цифры и _';
        } elseif (User::findByUsername($data['username']) !== null) {
            $errors['username'] = 'Это имя уже занято.';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Введите email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email.';
        } elseif (User::findByEmail($data['email']) !== null) {
            $errors['email'] = 'Этот email уже зарегистрирован.';
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Пароль — минимум 6 символов.';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Пароли не совпадают.';
        }

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('admin/user_create', [
                'data'   => $data,
                'errors' => $errors,
                'title'  => 'Админка — новый пользователь',
            ]);
            return;
        }

        User::create([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_admin'      => $data['is_admin'],
        ]);

        flash('success', 'Пользователь «' . $data['username'] . '» создан.');
        $this->redirect('admin_users');
    }

    /** Удаление пользователя (только POST + CSRF). */
    public function userDelete(): void
    {
        require_admin();

        if (!$this->isPost()) {
            $this->redirect('admin_users');
        }

        assert_csrf();

        $id     = (int)($_POST['id'] ?? 0);
        $target = User::find($id);

        if ($target === null) {
            flash('error', 'Пользователь не найден.');
            $this->redirect('admin_users');
        }

        if ($id === (int)current_user()['id']) {
            flash('error', 'Нельзя удалить собственный аккаунт.');
            $this->redirect('admin_users');
        }

        if ((bool)$target['is_admin'] && User::countAdmins() <= 1) {
            flash('error', 'Нельзя удалить последнего администратора.');
            $this->redirect('admin_users');
        }

        User::delete($id);
        flash('success', 'Пользователь «' . $target['username'] . '» и все его персонажи удалены.');
        $this->redirect('admin_users');
    }
}
