<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Number
{
    public static function is($string)
    {
        return in_array($string, ['<num>', '<number>'], true);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] !== T_LNUMBER && $t[0] !== T_DNUMBER) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
