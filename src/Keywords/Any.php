<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

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
