<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Name
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<name>']);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] !== T_STRING) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
