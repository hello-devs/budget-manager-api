<?php

namespace App\Trait;

use UnitEnum;

/**
 * @method static cases()
 */
trait EnumToArray
{
    /**
     * @return string[]
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * @return int[] | string []
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, int|string>
     */
    public static function array(): array
    {
        return array_combine(self::names(), self::values());
    }
}
