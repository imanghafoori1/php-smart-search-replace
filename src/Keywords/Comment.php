<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Comment
{
    public static function is($pToken)
    {
        return Finder::is($pToken, '<comment>');
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues, $pToken) {
        $tToken = $tokens[$startFrom] ?? '_';
        $result = Finder::compareIt($tToken, T_COMMENT, $pToken[1], $startFrom);
        if ($result === null) {
            return false;
        }
        $placeholderValues[] = $result;
    }
}
