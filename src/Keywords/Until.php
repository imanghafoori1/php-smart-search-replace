<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Stringify;

class Until
{
    public static function is($string)
    {
        return $string === '<until>';
    }

    public static function getValue(
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

    private static function readUntil($pi, $tokens, $pattern)
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