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
                if (is_int($filterName) && is_array($values)) {
                    if (count($values) === 2 && is_callable($values[0])) {
                        if (! call_user_func_array($values[0], [$placeholderVals[$i - 1], $values[1], $tokens, $placeholderVals, $i - 1])) {
                            return false;
                        }
                    }
                } elseif (isset(self::$filters[$filterName])) {
                    $filterClass = self::$filters[$filterName];
                    if (! $filterClass::check($placeholderVals[$i - 1], $values, $tokens, $placeholderVals, $i - 1)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
