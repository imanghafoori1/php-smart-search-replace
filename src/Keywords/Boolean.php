<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Boolean
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<bool>', '<boolean>']);
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] === T_STRING && in_array(strtolower($t[1]), ['true', 'false'], true)) {
            $placeholderValues[] = $t;
        }
    }
}
