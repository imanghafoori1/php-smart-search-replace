<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Comparison
{
    public static function is($string)
    {
        return in_array($string, ['<compare>', '<comparison>'], true);
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
