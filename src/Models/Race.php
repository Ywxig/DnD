<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Справочник рас.
 *
 * @package App\Models
 */
final class Race extends Model
{
    /** @inheritdoc */
    protected const TABLE = 'races';
}
