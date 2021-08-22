<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class Any
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<any>');
    }

    public static function mustStart($tToken, $tokens, &$startFrom, &$placeholderValues)
    {
        $placeholderValues[] = $tToken;
    }
}