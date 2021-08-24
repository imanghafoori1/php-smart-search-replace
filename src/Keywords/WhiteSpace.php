<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class WhiteSpace
{
    public static function is($pToken)
    {
        return Finder::is($pToken, '<white_space>');
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues, $pToken)
    {
        $tToken = $tokens[$startFrom] ?? '_';
        $result = Finder::compareIt($tToken, T_WHITESPACE, $pToken[1], $startFrom);
        if ($result === null) {
            return false;
        }
        $placeholderValues[] = $result;
    }
}
