<?php

namespace Imanghafoori\SearchReplace;

class Filters
{
    public static $filters = [
        'in_array' => Filters\InArray::class,
        'same_name' => Filters\SameName::class,
    ];

    public static function apply($filterings, $data, $tokens)
    {
        $placeholderVals = $data['values'];
        foreach ($filterings as $i => $filters) {
            foreach ($filters as $filterName => $values) {
                if (! isset(self::$filters[$filterName])) {
                    continue;
                }

                $filterClass = self::$filters[$filterName];
                if (! $filterClass::check($placeholderVals[$i - 1], $values, $tokens, $placeholderVals, $i - 1)) {
                    return false;
                }
            }
        }

        return true;
    }
}
