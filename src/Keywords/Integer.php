<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Integer
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<int>', '<integer>']);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] !== T_LNUMBER) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
