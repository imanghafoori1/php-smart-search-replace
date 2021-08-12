<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\SearchReplace\Filters\InArray;

class Filters
{
    public static $filters = [
        'in_array' => InArray::class,
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
                if (! $filterClass::check($placeholderVals[$i - 1], $values, $tokens)) {
                    return false;
                }
            }
        }

        return true;
    }
}
