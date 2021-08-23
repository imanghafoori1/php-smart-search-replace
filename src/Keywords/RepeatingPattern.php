<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\TokenAnalyzer\Str;

class RepeatingPattern
{
    public static function is($pToken)
    {
        return $pToken[0] === T_CONSTANT_ENCAPSED_STRING && Finder::startsWith(trim($pToken[1], '\'\"'), '<repeating:');
    }

    public static function mustStart($tokens, $i, $pToken, $namedPatterns)
    {
        $isStartPoint = true;
        Finder::startsWith($pName = trim($pToken[1], '\'\"'), '<repeating:');
        $patternName = rtrim(Str::replaceFirst('<repeating:', '', $pName), '>');

        // We compare it like a normal pattern.
        if (! Finder::compareTokens(PatternParser::tokenize($namedPatterns[$patternName]), $tokens, $i)) {
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
        &$repeating
    ) {
        $analyzedPattern = PatternParser::tokenize($namedPatterns[Finder::isRepeatingPattern($pToken)]);
        if (! Finder::compareTokens($analyzedPattern, $tokens, $startFrom)) {
            return false;
        }

        [$repeatingMatches, $startFrom] = self::findRepeatingMatches($startFrom, $tokens, $analyzedPattern);

        $repeating[] = $repeatingMatches;
    }

    private static function findRepeatingMatches($startFrom, $tokens, $analyzedPattern)
    {
        $repeatingMatches = [];
        $end = $startFrom;
        while (true) {
            $isMatch = Finder::compareTokens($analyzedPattern, $tokens, $startFrom, []);

            if (! $isMatch) {
                break;
            }

            $end = $isMatch[0];
            [, $startFrom] = Finder::getNextToken($tokens, $end);
            $repeatingMatches[] = $isMatch[1];
        }

        return [$repeatingMatches, $end];
    }
}
