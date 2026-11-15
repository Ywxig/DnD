<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Модель класса персонажа (fighter, wizard, ...).
 *
 * @package App\Models
 */
final class CharClass extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'classes';

    /**
     * Создаёт класс и возвращает его id.
     */
    public static function create(string $name, string $description = ''): int
    {
        return Database::insert(
            'INSERT INTO classes (name, description) VALUES (:name, :description) RETURNING id',
            ['name' => $name, 'description' => $description]
        );
    }

    /**
     * Обновляет класс.
     */
    public static function update(int $id, string $name, string $description): bool
    {
        return Database::query(
            'UPDATE classes SET name = :name, description = :description WHERE id = :id',
            ['name' => $name, 'description' => $description, 'id' => $id]
        )->rowCount() > 0;
    }

    /**
     * Класс по имени (для проверки уникальности).
     *
     * @return array<string, mixed>|null
     */
    public static function findByName(string $name): ?array
    {
        $row = Database::query(
            'SELECT * FROM classes WHERE lower(name) = lower(:name)',
            ['name' => $name]
        )->fetch();
        return $row ?: null;
    }

    /**
     * Все классы с числом персонажей.
     *
     * @return list<array<string, mixed>>
     */
    public static function withUsage(): array
    {
        return Database::query(
            'SELECT cl.*, COUNT(c.id) AS characters_count
             FROM classes cl
             LEFT JOIN characters c ON c.class_id = cl.id
             GROUP BY cl.id
             ORDER BY cl.name'
        )->fetchAll();
    }

    /**
     * Число персонажей этого класса (запрет удаления используемых).
     */
    public static function usageCount(int $id): int
    {
        return (int)Database::query(
            'SELECT COUNT(*) FROM characters WHERE class_id = :id',
            ['id' => $id]
        )->fetchColumn();
    }
}
