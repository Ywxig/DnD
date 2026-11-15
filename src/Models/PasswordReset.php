<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Модель одноразовых токенов восстановления пароля.
 *
 * В БД хранится только SHA-256-хеш токена и срок действия;
 * сам токен существует лишь в ссылке из «письма».
 *
 * @package App\Models
 */
final class PasswordReset extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'password_resets';

    /** Срок жизни токена, секунд. */
    public const LIFETIME = 3600;

    /**
     * Выдаёт токен восстановления пользователю:
     * инвалидирует старые токены и сохраняет хеш нового.
     */
    public static function createForUser(int $userId, string $tokenHash): bool
    {
        Database::query(
            'DELETE FROM password_resets WHERE user_id = :user_id',
            ['user_id' => $userId]
        );
        return Database::query(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, now() + :lifetime * interval \'1 second\')',
            ['user_id' => $userId, 'token_hash' => $tokenHash, 'lifetime' => self::LIFETIME]
        )->rowCount() > 0;
    }

    /**
     * Возвращает валидный (неиспользованный, не истёкший) токен вместе с пользователем.
     *
     * @return array<string, mixed>|null
     */
    public static function findValid(string $tokenHash): ?array
    {
        $row = Database::query(
            'SELECT pr.*, u.username, u.email
             FROM password_resets pr
             JOIN users u ON u.id = pr.user_id
             WHERE pr.token_hash = :token_hash
               AND pr.used = FALSE
               AND pr.expires_at > now()',
            ['token_hash' => $tokenHash]
        )->fetch();
        return $row ?: null;
    }

    /**
     * Помечает токен использованным (одноразовость).
     */
    public static function markUsed(int $id): bool
    {
        return Database::query(
            'UPDATE password_resets SET used = TRUE WHERE id = :id',
            ['id' => $id]
        )->rowCount() > 0;
    }
}
