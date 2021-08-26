<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Name
{
    public static function is($string)
    {
        return $string === '<name>';
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if ($t[0] !== T_STRING) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
