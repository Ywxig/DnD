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
     * @param array{username: string, email: string, password_hash: string} $data
     */
    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO users (username, email, password_hash)
             VALUES (:username, :email, :password_hash)
             RETURNING id',
            [
                'username'      => $data['username'],
                'email'         => $data['email'],
                'password_hash' => $data['password_hash'],
            ]
        );
    }
}
