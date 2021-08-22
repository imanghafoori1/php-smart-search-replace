<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class FullClassRef {
    public static function is ($pToken) 
    {
        return TokenCompare::is($pToken, '<full_class_ref>');
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues) 
    {
        if ($tToken[0] !== T_NS_SEPARATOR) {
            return false;
        }

        $isMatch = TokenCompare::compareTokens($repeatingClassRef, $tokens, $startFrom, $classRef);

        if (! $isMatch) {
            return false;
        }

        $placeholderValues[] = TokenCompare::extractValue($isMatch[2][0]);
        $startFrom = $isMatch[0];
    }
}