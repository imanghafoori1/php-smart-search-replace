<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Variable
{
    public static function is($string)
    {
        return in_array($string, ['<var>', '<variable>'], true);
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
