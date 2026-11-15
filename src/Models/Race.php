<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Модель расы.
 *
 * @package App\Models
 */
final class Race extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'races';

    /**
     * Создаёт расу и возвращает её id.
     */
    public static function create(string $name, string $description = ''): int
    {
        return Database::insert(
            'INSERT INTO races (name, description) VALUES (:name, :description) RETURNING id',
            ['name' => $name, 'description' => $description]
        );
    }

    /**
     * Обновляет расу.
     */
    public static function update(int $id, string $name, string $description): bool
    {
        return Database::query(
            'UPDATE races SET name = :name, description = :description WHERE id = :id',
            ['name' => $name, 'description' => $description, 'id' => $id]
        )->rowCount() > 0;
    }

    /**
     * Раса по имени (для проверки уникальности).
     *
     * @return array<string, mixed>|null
     */
    public static function findByName(string $name): ?array
    {
        $row = Database::query(
            'SELECT * FROM races WHERE lower(name) = lower(:name)',
            ['name' => $name]
        )->fetch();
        return $row ?: null;
    }

    /**
     * Все расы с числом использующих их персонажей.
     *
     * @return list<array<string, mixed>>
     */
    public static function withUsage(): array
    {
        return Database::query(
            'SELECT r.*, COUNT(c.id) AS characters_count
             FROM races r
             LEFT JOIN characters c ON c.race_id = r.id
             GROUP BY r.id
             ORDER BY r.name'
        )->fetchAll();
    }

    /**
     * Число персонажей этой расы (запрет удаления используемых).
     */
    public static function usageCount(int $id): int
    {
        return (int)Database::query(
            'SELECT COUNT(*) FROM characters WHERE race_id = :id',
            ['id' => $id]
        )->fetchColumn();
    }
}
