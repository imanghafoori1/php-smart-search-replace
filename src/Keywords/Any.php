<?php

namespace Imanghafoori\SearchReplace\Keywords;

class Any
{
    public static function is($string)
    {
        return $string === '<any>';
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $placeholderValues[] = $tokens[$startFrom];
    }
}
