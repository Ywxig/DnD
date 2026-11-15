<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Обёртка над PDO: единственное подключение к БД на запрос.
 *
 * Настройки: исключения вместо ошибок, FETCH_ASSOC по умолчанию,
 * нативные подготовленные выражения (EMULATE_PREPARES = false).
 */
final class Database
{
    /** Единственный на запрос экземпляр PDO. */
    private static ?PDO $pdo = null;

    /**
     * Возвращает подключение к БД (создаёт его при первом вызове).
     */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $db  = app_config()['db'];
            $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $db['host'], $db['port'], $db['name']);
            try {
                self::$pdo = new PDO($dsn, $db['user'], $db['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (\PDOException $e) {
                throw new RuntimeException('Не удалось подключиться к базе данных: ' . $e->getMessage(), 0, $e);
            }
        }
        return self::$pdo;
    }

    /**
     * Выполняет подготовленный запрос с параметрами.
     *
     * Тип параметра (int/bool/null/string) определяется автоматически.
     *
     * @param array<string, mixed> $params Именованные параметры запроса
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $type = match (true) {
                is_int($value)   => PDO::PARAM_INT,
                is_bool($value)  => PDO::PARAM_BOOL,
                is_null($value)  => PDO::PARAM_NULL,
                default          => PDO::PARAM_STR,
            };
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Выполняет INSERT ... RETURNING id и возвращает id новой записи.
     *
     * @param array<string, mixed> $params
     */
    public static function insert(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    /**
     * Закрывает соединение (используется в тестах).
     */
    public static function disconnect(): void
    {
        self::$pdo = null;
    }
}
