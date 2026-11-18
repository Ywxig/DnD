<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Справочник классов персонажа.
 *
 * @package App\Models
 */
final class CharClass extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'classes';
}
