<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class MethodVisibility
{
    public static function is($pToken)
    {
        return Finder::is($pToken, ['<visibility>', '<vis>']);
    }

    public static function getValue($tokens, $startFrom, &$placeholderValues)
    {
        $t = $tokens[$startFrom];

        if (! in_array($t[0], [T_PUBLIC, T_PROTECTED, T_PRIVATE])) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
