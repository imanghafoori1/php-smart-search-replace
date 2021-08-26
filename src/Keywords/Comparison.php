<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Comparison
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<compare>', '<comparison>']);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if (! in_array($t[0], [
            T_IS_EQUAL,
            T_IS_GREATER_OR_EQUAL,
            T_IS_IDENTICAL,
            T_IS_NOT_EQUAL,
            T_IS_NOT_IDENTICAL,
            T_SPACESHIP,
            T_IS_SMALLER_OR_EQUAL,
        ])) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
