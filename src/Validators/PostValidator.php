<?php

declare(strict_types=1);

namespace App\Validators;

use App\Models\Post;

/**
 * Серверная валидация формы поста.
 *
 * @package App\Validators
 */
final class PostValidator
{
    public const MIN_TITLE_LENGTH = 3;
    public const MAX_TITLE_LENGTH = 100;
    public const MIN_LEVEL = 1;
    public const MAX_LEVEL = 20;
    public const MAX_DESCRIPTION_LENGTH = 2000;

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public static function validate(array $data): array
    {
        $errors = [];

        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            $errors['title'] = 'Введите название.';
        } elseif (mb_strlen($title) < self::MIN_TITLE_LENGTH || mb_strlen($title) > self::MAX_TITLE_LENGTH) {
            $errors['title'] = 'Название должно содержать от ' . self::MIN_TITLE_LENGTH
                . ' до ' . self::MAX_TITLE_LENGTH . ' символов.';
        }

        $format = trim((string)($data['format'] ?? ''));
        if (!in_array($format, Post::FORMATS, true)) {
            $errors['format'] = 'Выберите формат игры.';
        }

        $level = $data['start_level'] ?? null;
        if (!is_int($level)) {
            $errors['start_level'] = 'Укажите стартовый уровень.';
        } elseif ($level < self::MIN_LEVEL || $level > self::MAX_LEVEL) {
            $errors['start_level'] = 'Уровень должен быть от ' . self::MIN_LEVEL . ' до ' . self::MAX_LEVEL . '.';
        }

        $description = trim((string)($data['description'] ?? ''));
        if (mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            $errors['description'] = 'Описание — не более ' . self::MAX_DESCRIPTION_LENGTH . ' символов.';
        }

        return $errors;
    }
}
