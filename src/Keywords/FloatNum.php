<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class FloatNum
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<float>', '<float_num>']);
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
