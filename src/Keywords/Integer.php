<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Integer
{
    public static function is($string)
    {
        return in_array($string, ['<int>', '<integer>'], true);
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
