<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class Token {
    public static function is () 
    {
        return true;
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues, $nameRepeatingClassRef, $pattern, $pi, $j, $pToken) {
        $same = TokenCompare::areTheSame($pToken, $tToken);

        if (! $same) {
            return false;
        }

        $same === 'placeholder' && $placeholderValues[] = $tToken;
    }
}