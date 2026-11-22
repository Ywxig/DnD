<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Модель поста: объявление об игре (ваншоте или кампании).
 *
 * @package App\Models
 */
final class Post extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'posts';

    /** Допустимые форматы игры. */
    public const FORMATS = ['oneshot', 'campaign'];

    /** Русские подписи форматов. */
    public const FORMAT_LABELS = [
        'oneshot'  => 'Ваншот (1 сессия)',
        'campaign' => 'Кампания',
    ];

    /**
     * Создаёт пост и возвращает его id.
     *
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO posts (user_id, title, format, start_level, description)
             VALUES (:user_id, :title, :format, :start_level, :description)
             RETURNING id',
            $data
        );
    }

    /**
     * Пост с именем автора.
     *
     * @return array<string, mixed>|null
     */
    public static function findDetailed(int $id): ?array
    {
        $row = Database::query(
            'SELECT p.*, u.username AS owner_name
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.id = :id',
            ['id' => $id]
        )->fetch();

        return $row ?: null;
    }

    /**
     * Все посты, новые сверху, с именем автора.
     *
     * @return list<array<string, mixed>>
     */
    public static function allDetailed(): array
    {
        return Database::query(
            'SELECT p.*, u.username AS owner_name
             FROM posts p
             JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC, p.id DESC'
        )->fetchAll();
    }

    /**
     * Посты конкретного пользователя.
     *
     * @return list<array<string, mixed>>
     */
    public static function ownedBy(int $userId): array
    {
        return Database::query(
            'SELECT p.*, u.username AS owner_name
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.user_id = :user_id
             ORDER BY p.created_at DESC, p.id DESC',
            ['user_id' => $userId]
        )->fetchAll();
    }
}
