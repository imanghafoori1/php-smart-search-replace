<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;
use Imanghafoori\SearchReplace\PatternParser;

class IsRepeatingPattern {
    public static function is ($pToken, $namedPatterns) 
    {
        return $namedPatterns && TokenCompare::isRepeatingPattern($pToken);
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues, $nameRepeatingClassRef, $pattern, $pi, $j, $pToken, $namedPatterns, &$repeatings) {
        $analyzedPattern = PatternParser::tokenize($namedPatterns[TokenCompare::isRepeatingPattern($pToken)]);
        if (! TokenCompare::compareTokens($analyzedPattern, $tokens, $startFrom)) {
            return false;
        }

        [$repeatingMatches, $startFrom] = TokenCompare::findRepeatingMatches($startFrom, $tokens, $analyzedPattern);

        $repeatings[] = $repeatingMatches;
    }
}