<?php

namespace Imanghafoori\SearchReplace\Keywords;

class MethodVisibility
{
    public static function is($string)
    {
        return in_array($string, ['<visibility>', '<vis>'], true);
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
