<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Any
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<any>');
    }

    public static function mustStart()
    {
        return true;
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $placeholderValues[] = $tokens[$startFrom];
    }
}
