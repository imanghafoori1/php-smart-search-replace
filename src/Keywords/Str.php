<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Str
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<str>', '<string>']);
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
