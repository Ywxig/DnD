<?php

declare(strict_types=1);

namespace App\Core;

use LogicException;

/**
 * Базовая модель: типовые операции над одной таблицей БД.
 */
abstract class Model
{
    /** Имя таблицы БД (переопределяется в наследниках). */
    protected const TABLE = '';

    /**
     * Находит запись по первичному ключу.
     *
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $row = Database::query(
            'SELECT * FROM ' . static::TABLE . ' WHERE id = :id',
            ['id' => $id]
        )->fetch();
        return $row ?: null;
    }

    /**
     * Все записи таблицы.
     *
     * @param string $orderBy Фрагмент ORDER BY (только из доверенного кода, не из запроса пользователя)
     * @return list<array<string, mixed>>
     */
    public static function all(string $orderBy = 'id'): array
    {
        return Database::query('SELECT * FROM ' . static::TABLE . ' ORDER BY ' . $orderBy)->fetchAll();
    }

    /**
     * Удаляет запись по id.
     */
    public static function delete(int $id): bool
    {
        return Database::query(
            'DELETE FROM ' . static::TABLE . ' WHERE id = :id',
            ['id' => $id]
        )->rowCount() > 0;
    }

    /** Защита от забытого TABLE у наследника. */
    protected static function assertTable(): void
    {
        if (static::TABLE === '') {
            throw new LogicException(static::class . ' должен определить константу TABLE');
        }
    }
}
