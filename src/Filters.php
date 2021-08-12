<?php

namespace Imanghafoori\SearchReplace;

class Filters
{
    public static function apply($filters, $data, $tokens)
    {
        $placeholderVals = $data['values'];
        foreach ($filters as $i => $values) {
            if (isset($values['in_array']) && ! in_array($placeholderVals[$i - 1][1] ?? null, $values['in_array'])) {
                return false;
            }
        }
        return true;
    }
}
