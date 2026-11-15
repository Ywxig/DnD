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
     * Обновляет персонажа.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        return Database::query(
            'UPDATE characters SET
                race_id = :race_id, class_id = :class_id, name = :name, level = :level,
                strength = :strength, dexterity = :dexterity, constitution = :constitution,
                intelligence = :intelligence, wisdom = :wisdom, charisma = :charisma,
                alignment = :alignment, backstory = :backstory
             WHERE id = :id',
            $data
        )->rowCount() > 0;
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
     * Последние N созданных персонажей (для главной страницы).
     *
     * @return list<array<string, mixed>>
     */
    public static function latest(int $limit = 5): array
    {
        return Database::query(
            'SELECT c.id, c.name, c.level, c.alignment, c.created_at,
                    r.name AS race_name, cl.name AS class_name, u.username AS owner_name
             FROM characters c
             JOIN races r    ON r.id  = c.race_id
             JOIN classes cl ON cl.id = c.class_id
             JOIN users u    ON u.id  = c.user_id
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT :limit',
            ['limit' => $limit]
        )->fetchAll();
    }

    /**
     * Топ классов по числу персонажей (GROUP BY + COUNT + AVG).
     *
     * @return list<array<string, mixed>>
     */
    public static function topClasses(int $limit = 6): array
    {
        return Database::query(
            'SELECT cl.id, cl.name, COUNT(c.id) AS characters_count, ROUND(AVG(c.level), 1) AS avg_level
             FROM classes cl
             LEFT JOIN characters c ON c.class_id = cl.id
             GROUP BY cl.id, cl.name
             ORDER BY characters_count DESC, cl.name
             LIMIT :limit',
            ['limit' => $limit]
        )->fetchAll();
    }

    /**
     * Случайный персонаж (ORDER BY random()).
     *
     * @return array<string, mixed>|null
     */
    public static function random(): ?array
    {
        $row = Database::query(
            'SELECT c.id, c.name, c.level, c.alignment, r.name AS race_name, cl.name AS class_name
             FROM characters c
             JOIN races r    ON r.id  = c.race_id
             JOIN classes cl ON cl.id = c.class_id
             ORDER BY random()
             LIMIT 1'
        )->fetch();
        return $row ?: null;
    }

    /**
     * Счётчики для главной страницы и дашборда.
     *
     * @return array{characters: int, users: int, races: int, classes: int}
     */
    public static function counters(): array
    {
        $row = Database::query(
            'SELECT
                (SELECT COUNT(*) FROM characters) AS characters,
                (SELECT COUNT(*) FROM users)      AS users,
                (SELECT COUNT(*) FROM races)      AS races,
                (SELECT COUNT(*) FROM classes)    AS classes'
        )->fetch();
        return [
            'characters' => (int)($row['characters'] ?? 0),
            'users'      => (int)($row['users'] ?? 0),
            'races'      => (int)($row['races'] ?? 0),
            'classes'    => (int)($row['classes'] ?? 0),
        ];
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
     * Поиск персонажей с динамической сборкой WHERE и пагинацией.
     *
     * Значения LIKE-условий экранируются (% и _), все параметры — через PDO.
     *
     * @param array{name: string, race_id: int, class_id: int, level_from: int, level_to: int, alignment: string} $filters
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public static function search(array $filters, int $page, int $perPage = 10): array
    {
        $where  = [];
        $params = [];

        if ($filters['name'] !== '') {
            $where[]         = 'c.name ILIKE :name';
            $params['name']  = '%' . User::escapeLike($filters['name']) . '%';
        }
        if ($filters['race_id'] > 0) {
            $where[]           = 'c.race_id = :race_id';
            $params['race_id'] = $filters['race_id'];
        }
        if ($filters['class_id'] > 0) {
            $where[]            = 'c.class_id = :class_id';
            $params['class_id'] = $filters['class_id'];
        }
        if ($filters['level_from'] > 0) {
            $where[]              = 'c.level >= :level_from';
            $params['level_from'] = $filters['level_from'];
        }
        if ($filters['level_to'] > 0) {
            $where[]            = 'c.level <= :level_to';
            $params['level_to'] = $filters['level_to'];
        }
        if ($filters['alignment'] !== '') {
            $where[]             = 'c.alignment = :alignment';
            $params['alignment'] = $filters['alignment'];
        }

        $base = 'FROM characters c
                 JOIN races r    ON r.id  = c.race_id
                 JOIN classes cl ON cl.id = c.class_id
                 JOIN users u    ON u.id  = c.user_id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '');

        $total = (int)Database::query('SELECT COUNT(*) ' . $base, $params)->fetchColumn();

        $page     = max(1, $page);
        $pages    = max(1, (int)ceil($total / $perPage));
        $page     = min($page, $pages);
        $offset   = ($page - 1) * $perPage;

        $rows = Database::query(
            'SELECT c.*, r.name AS race_name, cl.name AS class_name, u.username AS owner_name '
            . $base
            . ' ORDER BY c.created_at DESC, c.id DESC
               LIMIT :limit OFFSET :offset',
            $params + ['limit' => $perPage, 'offset' => $offset]
        )->fetchAll();

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }
}
