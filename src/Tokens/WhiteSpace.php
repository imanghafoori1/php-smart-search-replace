<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class WhiteSpace {
    public static function is ($pToken) 
    {
        return TokenCompare::is($pToken, '<white_space>');
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues, $nameRepeatingClassRef, $pattern, $pi, $j, $pToken) {
        $result = TokenCompare::compareIt($tToken, T_WHITESPACE, $pToken[1], $startFrom);
            if ($result === null) {
                return false;
            }
        $placeholderValues[] = $result;
    }
}