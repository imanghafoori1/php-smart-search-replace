<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class Comment
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<comment>');
    }

    public static function mustStart(
        $tToken,
        $tokens,
        &$startFrom,
        &$placeholderValues,
        $pToken
    ) {
        $result = TokenCompare::compareIt($tToken, T_COMMENT, $pToken[1], $startFrom);
        if ($result === null) {
            return false;
        }
        $placeholderValues[] = $result;
    }
}