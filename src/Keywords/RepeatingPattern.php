<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\Tokens;

class RepeatingPattern
{
    public static function getValue(
        &$startFrom,
        &$repeating,
        $tokens,
        $pToken,
        $namedPatterns
    ) {
        $analyzedPattern = PatternParser::tokenize($namedPatterns[Finder::isRepeatingPattern($pToken)]);
        if (! Tokens::compareTokens($analyzedPattern, $tokens, $startFrom)) {
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
            $isMatch = Tokens::compareTokens($analyzedPattern, $tokens, $startFrom, []);

            if (! $isMatch) {
                break;
            }

            $end = $isMatch[0];
            [, $startFrom] = Tokens::getNextToken($tokens, $end);
            $repeatingMatches[] = $isMatch[1];
        }

        return [$repeatingMatches, $end];
    }
}
