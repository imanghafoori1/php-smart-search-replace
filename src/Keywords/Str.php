<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Str
{
    public static function is($string)
    {
        return in_array($string, ['<str>', '<string>'], true);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
