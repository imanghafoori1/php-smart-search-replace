<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class WhiteSpace
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<white_space>');
    }

    public static function mustStart($tokens, $i)
    {
        return $tokens[$i][0] === T_WHITESPACE;
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues, $pToken) {
        $tToken = $tokens[$startFrom] ?? '_';
        $result = TokenCompare::compareIt($tToken, T_WHITESPACE, $pToken[1], $startFrom);
        if ($result === null) {
            return false;
        }
        $placeholderValues[] = $result;
    }
}
