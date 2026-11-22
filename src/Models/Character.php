<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Модель персонажа D&D.
 *
 * @package App\Models
 */
final class Character extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'characters';

    /** Шесть характеристик D&D. */
    public const STATS = ['strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma'];

    /** Русские подписи характеристик. */
    public const STAT_LABELS = [
        'strength'     => 'Сила',
        'dexterity'    => 'Ловкость',
        'constitution' => 'Телосложение',
        'intelligence' => 'Интеллект',
        'wisdom'       => 'Мудрость',
        'charisma'     => 'Харизма',
    ];

    /**
     * Создаёт персонажа и возвращает его id.
     *
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO characters
                (user_id, race_id, class_id, name, level, strength, dexterity,
                 constitution, intelligence, wisdom, charisma, alignment, backstory)
             VALUES
                (:user_id, :race_id, :class_id, :name, :level, :strength, :dexterity,
                 :constitution, :intelligence, :wisdom, :charisma, :alignment, :backstory)
             RETURNING id',
            $data
        );
    }

    /**
     * Персонаж с именами расы, класса и владельца.
     *
     * @return array<string, mixed>|null
     */
    public static function findDetailed(int $id): ?array
    {
        $row = Database::query(
            'SELECT c.*, r.name AS race_name, cl.name AS class_name, u.username AS owner_name
             FROM characters c
             JOIN races r    ON r.id  = c.race_id
             JOIN classes cl ON cl.id = c.class_id
             JOIN users u    ON u.id  = c.user_id
             WHERE c.id = :id',
            ['id' => $id]
        )->fetch();
        return $row ?: null;
    }

    /**
     * Персонажи конкретного пользователя («Мои персонажи»).
     *
     * @return list<array<string, mixed>>
     */
    public static function ownedBy(int $userId): array
    {
        return Database::query(
            'SELECT c.*, r.name AS race_name, cl.name AS class_name
             FROM characters c
             JOIN races r    ON r.id  = c.race_id
             JOIN classes cl ON cl.id = c.class_id
             WHERE c.user_id = :user_id
             ORDER BY c.created_at DESC, c.id DESC',
            ['user_id' => $userId]
        )->fetchAll();
    }

    /**
     * Поиск персонажей по всей базе: имя, раса, класс, мировоззрение, игрок.
     * Пустой запрос возвращает всех персонажей.
     *
     * @return list<array<string, mixed>>
     */
    public static function search(string $query = ''): array
    {
        $sql = 'SELECT c.*, r.name AS race_name, cl.name AS class_name, u.username AS owner_name
                FROM characters c
                JOIN races r    ON r.id  = c.race_id
                JOIN classes cl ON cl.id = c.class_id
                JOIN users u    ON u.id  = c.user_id';

        if ($query === '') {
            return Database::query($sql . ' ORDER BY c.id DESC')->fetchAll();
        }

        $sql .= ' WHERE c.name ILIKE :name
                     OR r.name ILIKE :race
                     OR cl.name ILIKE :class
                     OR c.alignment ILIKE :alignment
                     OR u.username ILIKE :owner
                  ORDER BY c.id DESC';

        $like = '%' . $query . '%';

        return Database::query($sql, [
            'name'      => $like,
            'race'      => $like,
            'class'     => $like,
            'alignment' => $like,
            'owner'     => $like,
        ])->fetchAll();
    }
}
