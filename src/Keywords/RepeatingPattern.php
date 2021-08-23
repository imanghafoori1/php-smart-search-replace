<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\TokenCompare;
use Imanghafoori\TokenAnalyzer\Str;

class RepeatingPattern
{
    public static function is($pToken)
    {
        return $pToken[0] === T_CONSTANT_ENCAPSED_STRING && TokenCompare::startsWith(trim($pToken[1], '\'\"'), '<repeating:');
    }

    public static function mustStart($tokens, $i, $pToken, $namedPatterns)
    {
        $isStartPoint = true;
        TokenCompare::startsWith($pName = trim($pToken[1], '\'\"'), '<repeating:');
        $patternName = rtrim(Str::replaceFirst('<repeating:', '', $pName), '>');

        // We compare it like a normal pattern.
        if (! TokenCompare::compareTokens(PatternParser::tokenize($namedPatterns[$patternName]), $tokens, $i)) {
            $isStartPoint = false;
        }

        return $isStartPoint;
    }

    public static function getValue(
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

    private static function findRepeatingMatches($startFrom, $tokens, $analyzedPattern)
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
