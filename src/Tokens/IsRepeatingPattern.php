<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\TokenCompare;

class IsRepeatingPattern
{
    public static function is($pToken, $namedPatterns)
    {
        return $namedPatterns && TokenCompare::isRepeatingPattern($pToken);
    }

    public static function getValue(
        $tToken,
        $tokens,
        &$startFrom,
        &$placeholderValues,
        $pToken,
        $pattern,
        $pi,
        $j,
        $namedPatterns,
        &$repeatings
    ) {
        $analyzedPattern = PatternParser::tokenize($namedPatterns[TokenCompare::isRepeatingPattern($pToken)]);
        if (! TokenCompare::compareTokens($analyzedPattern, $tokens, $startFrom)) {
            return false;
        }

        [$repeatingMatches, $startFrom] = self::findRepeatingMatches($startFrom, $tokens, $analyzedPattern);

        $repeatings[] = $repeatingMatches;
    }

    public static function findRepeatingMatches($startFrom, $tokens, $analyzedPattern)
    {
        $repeatingMatches = [];
        $end = $startFrom;
        while (true) {
            $isMatch = TokenCompare::compareTokens($analyzedPattern, $tokens, $startFrom, []);

            if (! $isMatch) {
                break;
            }

            $end = $isMatch[0];
            [, $startFrom] = TokenCompare::getNextToken($tokens, $end);
            $repeatingMatches[] = $isMatch[1];
        }

        return [$repeatingMatches, $end];
    }

}