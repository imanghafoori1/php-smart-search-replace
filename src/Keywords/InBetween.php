<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Exception;
use Imanghafoori\SearchReplace\Stringify;

class InBetween
{
    public static function is($string)
    {
        return $string === '<in_between>';
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
        $startingToken = $pattern[$j - 1]; // may use getPreviousToken()
        self::validate($startingToken, $pattern[$j + 1]);

        [$_value, $startFrom] = self::readUntilMatch($pi, $tokens, $startingToken);
        $placeholderValues[] = $_value;
    }

    private static function readUntilMatch($i, $tokens, $startingToken)
    {
        $anti = self::getAnti($startingToken);
        $untilTokens = [];
        $line = 1;
        $level = 0;
        for ($k = $i + 1; true; $k++) {
            if ($tokens[$k] === $anti && $level === 0) {
                break;
            }

            $tokens[$k] === $startingToken && $level--;
            $tokens[$k] === $anti && $level++;

            ! $line && isset($tokens[$k][2]) && $line = $tokens[$k][2];
            $untilTokens[] = $tokens[$k];
        }

        $startFrom = $k - 1;
        $value = [T_STRING, Stringify::fromTokens($untilTokens), $line];

        return [$value, $startFrom];
    }

    private static function getAnti($startingToken)
    {
        return [
            '(' => ')',
            '{' => '}',
            '[' => ']',
        ][$startingToken];
    }

    private static function validate($startingToken, $pattern)
    {
        if (! in_array($startingToken, ['(', '[', '{'], true)) {
            throw new Exception('pattern invalid');
        }

        if (self::getAnti($startingToken) !== $pattern) {
            throw new Exception('pattern invalid');
        }
    }
}