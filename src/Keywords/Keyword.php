<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class Keyword
{
    public static function is()
    {
        return true;
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues, $pToken)
    {
        $tToken = $tokens[$startFrom] ?? '_';
        $same = Finder::areTheSame($pToken, $tToken);

        if (! $same) {
            return false;
        }

        $same === 'placeholder' && $placeholderValues[] = $tToken;
    }
}
