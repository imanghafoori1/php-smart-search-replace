<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Exception;
use Imanghafoori\SearchReplace\TokenCompare;

class InBetween
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<in_between>');
    }

    public static function mustStart(
        $tToken,
        $tokens,
        &$startFrom,
        &$placeholderValues,
        $pToken,
        $pattern,
        $pi,
        $j
    ) {
        $startingToken = $pattern[$j - 1]; // may use getPreviousToken()
        if (! in_array($startingToken, ['(', '[', '{'], true)) {
            throw new Exception('pattern invalid');
        }

        if (TokenCompare::getAnti($startingToken) !== $pattern[$j + 1]) {
            throw new Exception('pattern invalid');
        }

        [$_value, $startFrom] = TokenCompare::readUntilMatch($pi, $tokens, $startingToken);
        $placeholderValues[] = $_value;
    }
}