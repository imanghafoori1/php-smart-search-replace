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

    public static function mustStart(
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

        [$repeatingMatches, $startFrom] = TokenCompare::findRepeatingMatches($startFrom, $tokens, $analyzedPattern);

        $repeatings[] = $repeatingMatches;
    }
}