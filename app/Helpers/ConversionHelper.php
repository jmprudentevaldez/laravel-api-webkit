<?php

namespace App\Helpers;

use InvalidArgumentException;

class ConversionHelper
{
    /*
     * Convert an Enum Class to its array version
     */
    public function convertEnumToArray($enumClass, string $field = 'value'): array
    {
        if (! in_array($field, ['name', 'value'])) {
            throw new InvalidArgumentException('The `field` arg must either be `value` or `name`');
        }

        return array_column($enumClass::cases(), $field);
    }
}
