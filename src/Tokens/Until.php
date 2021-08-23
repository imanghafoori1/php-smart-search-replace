<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\Stringify;
use Imanghafoori\SearchReplace\TokenCompare;

class Until
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<until>');
    }

    public static function getValue(
        $tToken,
        $tokens,
        &$startFrom,
        &$placeholderValues,
        $pToken,
        $pattern,
        $pi,
        $j
    ) {
        [$_value, $startFrom] = self::readUntil($pi, $tokens, $pattern[$j + 1]);
        $placeholderValues[] = $_value;
    }

    public static function readUntil($pi, $tokens, $pattern)
    {
        $untilTokens = [];
        $line = 1;
        for ($k = $pi + 1; $tokens[$k] !== $pattern; $k++) {
            ! $line && isset($tokens[$k][2]) && $line = $tokens[$k][2];
            $untilTokens[] = $tokens[$k];
        }
        $placeholderValue = [T_STRING, Stringify::fromTokens($untilTokens), $line];

        return [$placeholderValue, $k - 1];
    }

}