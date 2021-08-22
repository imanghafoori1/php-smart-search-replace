<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class ClassRef {
    public static function is ($pToken) 
    {
        return TokenCompare::is($pToken, '<class_ref>');
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues, $nameRepeatingClassRef) 
    {
        if ($tToken[0] === T_NS_SEPARATOR) {
            $matches = TokenCompare::compareTokens($repeatingClassRef, $tokens, $startFrom, $classRef);

            if (! $matches) {
                return false;
            }
            $startFrom = $matches[0];
            $placeholderValues[] = TokenCompare::extractValue($matches[2][0]);
        } elseif ($tToken[0] === T_STRING) {
            $matches = TokenCompare::compareTokens($nameRepeatingClassRef, $tokens, $startFrom, $classRef);
            if (! $matches) {
                $placeholderValues[] = $tToken;
            } else {
                $startFrom = $matches[0];
                $placeholderValues[] = TokenCompare::extractValue($matches[2][0], $matches[1][0][1]);
            }
        } else {
            return false;
        }
    }
}