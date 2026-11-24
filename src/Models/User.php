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
     * Ищет пользователя по имени (без учёта регистра).
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
     * @param array{username: string, email: string, password_hash: string, is_admin?: bool} $data
     */
    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO users (username, email, password_hash, is_admin)
             VALUES (:username, :email, :password_hash, :is_admin)
             RETURNING id',
            [
                'username'      => $data['username'],
                'email'         => $data['email'],
                'password_hash' => $data['password_hash'],
                'is_admin'      => $data['is_admin'] ?? false,
            ]
        );
    }

    /**
     * Все пользователи с количеством их персонажей и постов (для админки).
     *
     * @return list<array<string, mixed>>
     */
    public static function allDetailed(): array
    {
        return Database::query(
            'SELECT u.id, u.username, u.email, u.is_admin, u.created_at,
                    (SELECT count(*) FROM characters c WHERE c.user_id = u.id) AS characters_count,
                    (SELECT count(*) FROM posts p WHERE p.user_id = u.id) AS posts_count
             FROM users u
             ORDER BY u.id'
        )->fetchAll();
    }

    /** Количество администраторов. */
    public static function countAdmins(): int
    {
        return (int)Database::query(
            'SELECT count(*) FROM users WHERE is_admin = TRUE'
        )->fetchColumn();
    }
}
