<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Модель пользователя.
 *
 * @package App\Models
 */
final class User extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'users';

    /**
     * Ищет пользователя по имени (точное совпадение, без учёта регистра).
     *
     * @return array<string, mixed>|null
     */
    public static function findByUsername(string $username): ?array
    {
        $row = Database::query(
            'SELECT * FROM users WHERE lower(username) = lower(:username)',
            ['username' => $username]
        )->fetch();
        return $row ?: null;
    }

    /**
     * Ищет пользователя по email (без учёта регистра).
     *
     * @return array<string, mixed>|null
     */
    public static function findByEmail(string $email): ?array
    {
        $row = Database::query(
            'SELECT * FROM users WHERE lower(email) = lower(:email)',
            ['email' => $email]
        )->fetch();
        return $row ?: null;
    }

    /**
     * Создаёт пользователя и возвращает его id.
     *
     * @param array{username: string, email: string, password_hash: string, role?: string} $data
     */
    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO users (username, email, password_hash, role)
             VALUES (:username, :email, :password_hash, :role)
             RETURNING id',
            [
                'username'      => $data['username'],
                'email'         => $data['email'],
                'password_hash' => $data['password_hash'],
                'role'          => $data['role'] ?? 'user',
            ]
        );
    }

    /**
     * Поиск пользователей для админки (по имени или email).
     *
     * @return list<array<string, mixed>>
     */
    public static function search(string $term, int $limit = 100): array
    {
        if ($term === '') {
            return Database::query(
                'SELECT * FROM users ORDER BY created_at DESC, id DESC LIMIT :limit',
                ['limit' => $limit]
            )->fetchAll();
        }
        $like = '%' . self::escapeLike($term) . '%';
        return Database::query(
            'SELECT * FROM users
             WHERE username ILIKE :like OR email ILIKE :like2
             ORDER BY created_at DESC, id DESC LIMIT :limit',
            ['like' => $like, 'like2' => $like, 'limit' => $limit]
        )->fetchAll();
    }

    /**
     * Последние зарегистрированные пользователи.
     *
     * @return list<array<string, mixed>>
     */
    public static function latest(int $limit = 5): array
    {
        return Database::query(
            'SELECT * FROM users ORDER BY created_at DESC, id DESC LIMIT :limit',
            ['limit' => $limit]
        )->fetchAll();
    }

    /**
     * Меняет роль пользователя.
     */
    public static function updateRole(int $id, string $role): bool
    {
        return Database::query(
            'UPDATE users SET role = :role WHERE id = :id',
            ['role' => $role, 'id' => $id]
        )->rowCount() > 0;
    }

    /**
     * Меняет пароль пользователя (хеш уже должен быть посчитан).
     */
    public static function updatePassword(int $id, string $passwordHash): bool
    {
        return Database::query(
            'UPDATE users SET password_hash = :hash WHERE id = :id',
            ['hash' => $passwordHash, 'id' => $id]
        )->rowCount() > 0;
    }

    /**
     * Экранирует служебные символы LIKE (% и _) в пользовательском вводе.
     */
    public static function escapeLike(string $value): string
    {
        return strtr($value, ['\\' => '\\\\', '%' => '\%', '_' => '\_']);
    }
}
