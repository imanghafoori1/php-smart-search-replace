<?php

namespace Imanghafoori\SearchReplace\Keywords;

class BlackSpace
{
    public static function is($string)
    {
        return $string === '<not_whitespace>';
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues, $pToken)
    {
        $t = $tokens[$startFrom];

        if ($t[0] === T_WHITESPACE) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}