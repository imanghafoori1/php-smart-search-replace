<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Boolean
{
    public static function is($string)
    {
        return in_array($string, ['<bool>', '<boolean>'], true);
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        if (self::isBoolean($tokens, $startFrom)) {
            $placeholderValues[] = $tokens[$startFrom];
        } else {
            return false;
        }
    }

    public static function isBoolean($tokens, $startFrom)
    {
        $t = $tokens[$startFrom];
        [$next] = Finder::getNextToken($tokens, $startFrom);

        return $next !== '(' && $t[0] === T_STRING && in_array(strtolower($t[1]), ['true', 'false'], true);
    }
}
