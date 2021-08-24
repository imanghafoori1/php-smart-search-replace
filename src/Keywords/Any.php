<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Any
{
    public static function is($pToken)
    {
        return Finder::is($pToken, '<any>');
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $placeholderValues[] = $tokens[$startFrom];
    }
}
