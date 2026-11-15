<?php

declare(strict_types=1);

namespace App\Validators;

use App\Models\Character;

/**
 * Серверная валидация формы персонажа.
 *
 * Возвращает массив ошибок по именам полей: ['name' => 'текст ошибки', ...].
 * Пустой массив означает, что данные валидны.
 *
 * @package App\Validators
 */
final class CharacterValidator
{
    /** Допустимые мировоззрения (осей 2, поэтому 9 значений). */
    public const ALIGNMENTS = [
        'Lawful Good', 'Neutral Good', 'Chaotic Good',
        'Lawful Neutral', 'True Neutral', 'Chaotic Neutral',
        'Lawful Evil', 'Neutral Evil', 'Chaotic Evil',
    ];

    public const MIN_LEVEL = 1;
    public const MAX_LEVEL = 20;
    public const MIN_STAT  = 3;
    public const MAX_STAT  = 18;
    public const MAX_BACKSTORY_LENGTH = 2000;

    /**
     * Валидирует данные формы персонажа.
     *
     * @param array<string, mixed>      $data    Значения полей формы
     * @param list<array<string,mixed>> $races   Список рас из БД
     * @param list<array<string,mixed>> $classes Список классов из БД
     * @return array<string, string> Ошибки по полям
     */
    public static function validate(array $data, array $races, array $classes): array
    {
        $errors = [];

        // Имя: обязательное, 2–50 символов.
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = 'Введите имя персонажа.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
            $errors['name'] = 'Имя должно содержать от 2 до 50 символов.';
        }

        // Раса: выбранная и существующая.
        $raceIds = array_column($races, 'id');
        $raceId  = (int)($data['race_id'] ?? 0);
        if ($raceId <= 0) {
            $errors['race_id'] = 'Выберите расу.';
        } elseif (!in_array($raceId, $raceIds, true)) {
            $errors['race_id'] = 'Указана неизвестная раса.';
        }

        // Класс: выбранный и существующий.
        $classIds = array_column($classes, 'id');
        $classId  = (int)($data['class_id'] ?? 0);
        if ($classId <= 0) {
            $errors['class_id'] = 'Выберите класс.';
        } elseif (!in_array($classId, $classIds, true)) {
            $errors['class_id'] = 'Указан неизвестный класс.';
        }

        // Уровень: целое число 1–20.
        $level = $data['level'] ?? null;
        if ($level === null || $level === '' || !is_int($level)) {
            $errors['level'] = 'Укажите уровень персонажа.';
        } elseif ($level < self::MIN_LEVEL || $level > self::MAX_LEVEL) {
            $errors['level'] = 'Уровень должен быть от ' . self::MIN_LEVEL . ' до ' . self::MAX_LEVEL . '.';
        }

        // Характеристики: целые числа 3–18.
        foreach (Character::STATS as $stat) {
            $value = $data[$stat] ?? null;
            if ($value === null || !is_int($value)) {
                $errors[$stat] = 'Укажите значение характеристики.';
            } elseif ($value < self::MIN_STAT || $value > self::MAX_STAT) {
                $errors[$stat] = 'Значение должно быть от ' . self::MIN_STAT . ' до ' . self::MAX_STAT . '.';
            }
        }

        // Мировоззрение: только из разрешённого списка.
        $alignment = trim((string)($data['alignment'] ?? ''));
        if ($alignment === '') {
            $errors['alignment'] = 'Выберите мировоззрение.';
        } elseif (!in_array($alignment, self::ALIGNMENTS, true)) {
            $errors['alignment'] = 'Недопустимое мировоззрение.';
        }

        // Предыстория: необязательная, до 2000 символов.
        $backstory = trim((string)($data['backstory'] ?? ''));
        if (mb_strlen($backstory) > self::MAX_BACKSTORY_LENGTH) {
            $errors['backstory'] = 'Предыстория — не более ' . self::MAX_BACKSTORY_LENGTH . ' символов.';
        }

        return $errors;
    }
}
