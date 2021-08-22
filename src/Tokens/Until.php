<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class Until {
    public static function is ($pToken) 
    {
        return TokenCompare::is($pToken, '<until>');
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues, $nameRepeatingClassRef, $pattern, $pi, $j) 
    {
        [$_value, $startFrom] = TokenCompare::readUntil($pi, $tokens, $pattern[$j + 1]);
        $placeholderValues[] = $_value;
    }
}