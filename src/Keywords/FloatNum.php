<?php

namespace Imanghafoori\SearchReplace\Keywords;

class FloatNum
{
    public static function is($string)
    {
        return in_array($string, ['<float>', '<float_num>'], true);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] !== T_DNUMBER) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
