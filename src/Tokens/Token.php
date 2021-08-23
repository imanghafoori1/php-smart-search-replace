<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class Token
{
    public static function is()
    {
        return true;
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues, $pToken)
    {
        $tToken = $tokens[$startFrom] ?? '_';
        $same = TokenCompare::areTheSame($pToken, $tToken);

        if (! $same) {
            return false;
        }

        $same === 'placeholder' && $placeholderValues[] = $tToken;
    }
}