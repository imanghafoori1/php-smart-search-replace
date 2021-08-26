<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Cast
{
    public static function is($string)
    {
        return $string === '<cast>';
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if (! in_array($t[0], [
            T_STRING_CAST,
            T_OBJECT_CAST,
            T_DOUBLE_CAST,
            T_BOOL_CAST,
            T_ARRAY_CAST,
            T_INT_CAST,
        ])) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
