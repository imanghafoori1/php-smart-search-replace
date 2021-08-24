<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Variable
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<var>', '<variable>']);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] !== T_VARIABLE) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
