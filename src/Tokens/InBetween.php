<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Exception;
use Imanghafoori\SearchReplace\Stringify;
use Imanghafoori\SearchReplace\TokenCompare;

class InBetween
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<in_between>');
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
        if (! in_array($startingToken, ['(', '[', '{'], true)) {
            throw new Exception('pattern invalid');
        }

        if (self::getAnti($startingToken) !== $pattern[$j + 1]) {
            throw new Exception('pattern invalid');
        }

        [$_value, $startFrom] = self::readUntilMatch($pi, $tokens, $startingToken);
        $placeholderValues[] = $_value;
    }

    public static function readUntilMatch($i, $tokens, $startingToken)
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

    public static function getAnti(string $startingToken)
    {
        return [
            '(' => ')',
            '{' => '}',
            '[' => ']',
        ][$startingToken];
    }
}